<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
use App\Models\Category;
use App\Services\GeminiAIService;
use Carbon\Carbon;

class HomeController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiAIService $geminiService)
    {
        $this->middleware('auth');
        $this->geminiService = $geminiService;
    }

    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $currentDate = Carbon::parse($selectedMonth . '-01'); // Parse as first day of selected month
        $userId = auth()->id();

        // Define period (26th previous month to 25th current month)
        // Example: If user selects "December 2024", period is Nov 26, 2024 to Dec 25, 2024
        $startDate = $currentDate->copy()->subMonth()->setDay(26)->startOfDay();
        $endDate = $currentDate->copy()->setDay(25)->endOfDay();

        // ========== FETCH CURRENT PERIOD DATA ==========
        $expenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        $incomes = Income::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        // Combine all transactions with enhanced metadata
        // Note: amount is automatically decrypted by the model accessor
        $allTransactions = $expenses->map(function ($expense) {
            $expenseDate = Carbon::parse($expense->date);
            return [
                'id' => $expense->id,
                'date' => $expense->date->format('Y-m-d H:i:s'),
                'description' => $expense->description,
                'amount' => (float) $expense->amount, // Ensures decrypted amount is float
                'category' => $expense->category->name ?? 'Uncategorized',
                'category_id' => $expense->category_id,
                'type' => 'expense',
                'day_of_week' => $expenseDate->dayName,
                'week_number' => $expenseDate->weekOfMonth,
                'hour' => $expenseDate->hour,
                'is_weekend' => in_array($expenseDate->dayOfWeek, [0, 6])
            ];
        })->merge($incomes->map(function ($income) {
            $incomeDate = Carbon::parse($income->date);
            return [
                'id' => $income->id,
                'date' => $income->date->format('Y-m-d H:i:s'),
                'description' => $income->source ?? 'Income',
                'amount' => (float) $income->amount, // Ensures decrypted amount is float
                'category' => 'Income',
                'category_id' => null,
                'type' => 'income',
                'day_of_week' => $incomeDate->dayName,
                'week_number' => $incomeDate->weekOfMonth,
                'hour' => $incomeDate->hour,
                'is_weekend' => in_array($incomeDate->dayOfWeek, [0, 6])
            ];
        }))->sortByDesc('date')->values()->toArray();

        // Calculate totals - amounts are automatically decrypted by accessors
        $totalIncome = (float) $incomes->sum('amount');
        $totalExpenses = (float) $expenses->sum('amount');
        $netSavings = $totalIncome - $totalExpenses;

        // Get budgets
        $currentMonthNumeric = (int) date('n', strtotime($selectedMonth));
        $currentYearNumeric = (int) date('Y', strtotime($selectedMonth));
        
        $budgets = Budget::where('user_id', $userId)
            ->where('month', $currentMonthNumeric)
            ->where('year', $currentYearNumeric)
            ->with('category')
            ->get();

        // Budget amounts are automatically decrypted by accessors
        $monthlyBudget = (float) $budgets->sum('amount');
        $remainingBudget = $monthlyBudget - $totalExpenses;

        // ========== ENHANCED CATEGORY ANALYSIS ==========
        $categories = Category::all();
        $categoryBreakdown = [];

        foreach ($categories as $category) {
            $categoryExpenses = $expenses->where('category_id', $category->id);
            $spent = (float) $categoryExpenses->sum('amount'); // Ensure float after decryption
            
            if ($spent > 0 || $budgets->where('category_id', $category->id)->count() > 0) {
                $budget = $budgets->where('category_id', $category->id)->first();
                
                // Get 6-month historical average
                $sixMonthAvg = $this->getCategoryAverage($userId, $category->id, $currentDate, 6);
                
                // Calculate variance vs historical average
                $varianceVsAvg = $sixMonthAvg > 0 
                    ? (($spent - $sixMonthAvg) / $sixMonthAvg) * 100 
                    : 0;
                
                // Get top transactions for this category
                $topTransactions = $categoryExpenses->sortByDesc('amount')->take(3)->pluck('description')->toArray();
                
                $categoryBreakdown[] = [
                    'name' => $category->name,
                    'expense' => $spent,
                    'budget' => $budget ? (float) $budget->amount : 0,
                    'remaining' => $budget ? ((float) $budget->amount - $spent) : 0,
                    'average_amount' => $sixMonthAvg,
                    'vs_average' => $varianceVsAvg,
                    'transaction_count' => $categoryExpenses->count(),
                    'avg_transaction' => $categoryExpenses->count() > 0 
                        ? $spent / $categoryExpenses->count() 
                        : 0,
                    'top_transactions' => $topTransactions,
                    'percent_of_total' => $totalExpenses > 0 ? ($spent / $totalExpenses) * 100 : 0
                ];
            }
        }

        // Sort by spending
        usort($categoryBreakdown, function($a, $b) {
            return $b['expense'] <=> $a['expense'];
        });

        // ========== COMPREHENSIVE HISTORICAL DATA (12 MONTHS) ==========
        $historicalMonths = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $monthIncome = (float) Income::where('user_id', $userId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $monthExpenses = (float) Expense::where('user_id', $userId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $monthSavings = $monthIncome - $monthExpenses;

            $historicalMonths[] = [
                'month' => $monthDate->format('M Y'),
                'income' => $monthIncome,
                'expenses' => $monthExpenses,
                'savings' => $monthSavings,
                'savings_rate' => $monthIncome > 0 ? (($monthIncome - $monthExpenses) / $monthIncome) * 100 : 0
            ];
        }
        
        $historicalMonths = array_reverse($historicalMonths);

        // ========== WEEKLY BREAKDOWN WITH ENHANCED STATUS ==========
        $weeklyBreakdown = $this->calculateWeeklyBreakdown($expenses, $startDate, $endDate);

        // ========== DAILY SPENDING PATTERN ANALYSIS ==========
        $dailyPattern = $this->analyzeDailyPattern($expenses);

        // ========== SPENDING VELOCITY & PROJECTION ==========
        $daysElapsed = max(1, $startDate->diffInDays(min(now(), $endDate)) + 1);
        $daysRemaining = max(0, now()->diffInDays($endDate, false));
        
        $currentPace = $totalExpenses / $daysElapsed;
        
        // Calculate historical average pace
        $historicalAvg = collect($historicalMonths)->where('expenses', '>', 0)->avg('expenses') ?? 0;
        $historicalPace = $historicalAvg / 30;
        
        // Calculate acceleration (% change vs historical)
        $acceleration = $historicalPace > 0 
            ? (($currentPace - $historicalPace) / $historicalPace) * 100 
            : 0;

        $projectedMonthEnd = $totalExpenses + ($currentPace * $daysRemaining);
        
        // Determine status
        $velocityStatus = 'normal';
        if ($acceleration > 20) {
            $velocityStatus = 'fast';
        } elseif ($acceleration < -20) {
            $velocityStatus = 'slow';
        }

        $spendingVelocity = [
            'current_pace' => $currentPace,
            'historical_pace' => $historicalPace,
            'acceleration' => $acceleration,
            'status' => $velocityStatus,
            'projected_month_end' => $projectedMonthEnd
        ];

        // ========== PREPARE COMPREHENSIVE AI DATA ==========
        $savingsRate = $totalIncome > 0 ? (($totalIncome - $totalExpenses) / $totalIncome) * 100 : 0;

        $aiData = [
            'period_start' => $startDate->format('M d, Y'),
            'period_end' => $endDate->format('M d, Y'),
            'days_elapsed' => $daysElapsed,
            'days_remaining' => $daysRemaining,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'monthly_budget' => $monthlyBudget,
            'remaining_budget' => $remainingBudget,
            'net_savings' => $netSavings,
            'savings_rate' => $savingsRate,
            'transactions' => $allTransactions,
            'category_budgets' => $categoryBreakdown,
            'historical_months' => $historicalMonths,
            'weekly_breakdown' => $weeklyBreakdown,
            'daily_pattern' => $dailyPattern,
            'spending_velocity' => $spendingVelocity
        ];

        // ========== GET AI INSIGHTS ==========
        $aiInsights = $this->geminiService->analyzeFinancialData($aiData);

        // ========== ENHANCED CHART DATA ==========
        
        // Daily spending chart with moving average
        $dailySpending = $expenses->groupBy(function($expense) {
            return Carbon::parse($expense->date)->format('Y-m-d');
        })->map(function($group, $date) {
            return [
                'date' => Carbon::parse($date)->format('M d'),
                'amount' => $group->sum('amount'),
                'count' => $group->count()
            ];
        })->values();

        // Category pie chart (top categories only)
        $categoryChart = collect($categoryBreakdown)
            ->filter(fn($c) => $c['expense'] > 0)
            ->sortByDesc('expense')
            ->take(8) // Top 8 categories
            ->map(function($cat) {
                return [
                    'category' => $cat['name'],
                    'amount' => $cat['expense'],
                    'percent' => $cat['percent_of_total']
                ];
            })->values();

        // Monthly trend chart with all 12 months
        $monthlyTrend = collect($historicalMonths)->map(function($month) {
            return [
                'month' => $month['month'],
                'expenses' => $month['expenses'],
                'income' => $month['income'],
                'savings' => $month['savings'],
                'savings_rate' => $month['savings_rate']
            ];
        });

        // ========== PREVIOUS PERIOD COMPARISON ==========
        $previousStartDate = $currentDate->copy()->subMonths(2)->setDay(26)->startOfDay();
        $previousEndDate = $currentDate->copy()->subMonth()->setDay(25)->endOfDay();

        $previousIncome = (float) Income::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->sum('amount');

        $previousExpenses = (float) Expense::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->sum('amount');

        $incomeChange = $previousIncome > 0 
            ? (($totalIncome - $previousIncome) / $previousIncome) * 100 
            : ($totalIncome > 0 ? 100 : 0);

        $expenseChange = $previousExpenses > 0 
            ? (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 
            : ($totalExpenses > 0 ? 100 : 0);

        // ========== ADDITIONAL INSIGHTS ==========
        
        // Weekend vs Weekday spending
        $weekendSpending = collect($allTransactions)
            ->where('type', 'expense')
            ->where('is_weekend', true)
            ->sum('amount');
            
        $weekdaySpending = $totalExpenses - $weekendSpending;

        return view('dashboard', compact(
            'selectedMonth',
            'totalIncome',
            'totalExpenses',
            'netSavings',
            'monthlyBudget',
            'remainingBudget',
            'incomeChange',
            'expenseChange',
            'allTransactions',
            'categoryBreakdown',
            'aiInsights',
            'dailySpending',
            'categoryChart',
            'monthlyTrend',
            'weeklyBreakdown',
            'dailyPattern',
            'daysElapsed',
            'daysRemaining',
            'spendingVelocity',
            'weekendSpending',
            'weekdaySpending'
        ));
    }

    /**
     * Calculate category average over N months
     */
    private function getCategoryAverage($userId, $categoryId, $currentDate, $months = 6)
    {
        $total = 0;
        $count = 0;

        for ($i = 1; $i <= $months; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $monthTotal = (float) Expense::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            if ($monthTotal > 0) {
                $total += $monthTotal;
                $count++;
            }
        }

        return $count > 0 ? $total / $count : 0;
    }

    /**
     * Calculate weekly breakdown with enhanced anomaly detection
     */
    private function calculateWeeklyBreakdown($expenses, $startDate, $endDate)
    {
        $weeks = [];
        $currentWeekStart = $startDate->copy();
        $weekNumber = 1;
        $allWeekTotals = [];

        // First pass: calculate all week totals
        $tempWeekStart = $startDate->copy();
        while ($tempWeekStart->lte($endDate)) {
            $weekEnd = $tempWeekStart->copy()->addDays(6);
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }

            $weekTotal = $expenses->filter(function($expense) use ($tempWeekStart, $weekEnd) {
                $expenseDate = Carbon::parse($expense->date);
                return $expenseDate->between($tempWeekStart, $weekEnd);
            })->sum('amount');

            $allWeekTotals[] = $weekTotal;
            $tempWeekStart->addDays(7);
        }

        $avgWeekTotal = count($allWeekTotals) > 0 ? array_sum($allWeekTotals) / count($allWeekTotals) : 0;

        // Second pass: build result with status and notable items
        while ($currentWeekStart->lte($endDate)) {
            $weekEnd = $currentWeekStart->copy()->addDays(6);
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }

            $weekExpenses = $expenses->filter(function($expense) use ($currentWeekStart, $weekEnd) {
                $expenseDate = Carbon::parse($expense->date);
                return $expenseDate->between($currentWeekStart, $weekEnd);
            });

            $weekTotal = (float) $weekExpenses->sum('amount');

            // Get notable items (top 2 expenses)
            $notableItems = $weekExpenses->sortByDesc('amount')->take(2)->pluck('description')->toArray();

            // Determine status
            $status = 'normal';
            if ($avgWeekTotal > 0) {
                $deviation = (($weekTotal - $avgWeekTotal) / $avgWeekTotal) * 100;
                if ($deviation > 25) {
                    $status = 'high';
                } elseif ($deviation < -25) {
                    $status = 'low';
                }
            }

            $weeks[] = [
                'week_number' => $weekNumber,
                'start_date' => $currentWeekStart->format('M d'),
                'end_date' => $weekEnd->format('M d'),
                'total' => $weekTotal,
                'status' => $status,
                'notable_items' => $notableItems,
                'transaction_count' => $weekExpenses->count()
            ];

            $currentWeekStart->addDays(7);
            $weekNumber++;
        }

        return $weeks;
    }

    /**
     * Analyze daily spending patterns with enhanced insights
     */
    private function analyzeDailyPattern($expenses)
    {
        $dayTotals = [];
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($dayOrder as $day) {
            $dayExpenses = $expenses->filter(function($expense) use ($day) {
                return Carbon::parse($expense->date)->dayName === $day;
            });

            $dayTotals[$day] = [
                'total' => $dayExpenses->sum('amount'),
                'count' => $dayExpenses->count(),
                'average' => $dayExpenses->count() > 0 
                    ? $dayExpenses->sum('amount') / $dayExpenses->count() 
                    : 0
            ];
        }

        // Find highest and lowest spending days
        $sortedByTotal = collect($dayTotals)->sortByDesc('total');
        $highest = $sortedByTotal->first();
        $highestDay = $sortedByTotal->keys()->first();
        
        $sortedByTotalAsc = collect($dayTotals)->sortBy('total')->filter(fn($day) => $day['count'] > 0);
        $lowest = $sortedByTotalAsc->first();
        $lowestDay = $sortedByTotalAsc->keys()->first();

        return [
            'by_day' => $dayTotals,
            'highest_day' => $highestDay,
            'highest_amount' => $highest['total'] ?? 0,
            'lowest_day' => $lowestDay ?? 'N/A',
            'lowest_amount' => $lowest['total'] ?? 0
        ];
    }
}