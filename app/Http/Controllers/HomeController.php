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
        $viewMode = $request->input('view', 'month');
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $selectedYear = $request->input('year', now()->year);
        $userId = auth()->id();

        if ($viewMode === 'year') {
            return $this->yearView($request, $selectedYear, $userId);
        }

        return $this->monthView($request, $selectedMonth, $userId);
    }

    private function monthView($request, $selectedMonth, $userId)
    {
        $viewMode = 'month';
        $currentDate = Carbon::parse($selectedMonth . '-01');
        
        // Define period (26th previous month to 25th current month)
        $startDate = $currentDate->copy()->subMonth()->setDay(26)->startOfDay();
        $endDate = $currentDate->copy()->setDay(25)->endOfDay();

        // ========== FETCH CURRENT PERIOD DATA ==========
        $expenses = Expense::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        $incomes = Income::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->orderBy('date', 'desc')
            ->get();

        // Combine all transactions - amounts are auto-decrypted by model accessors
        $allTransactions = $expenses->map(function ($expense) {
            $expenseDate = Carbon::parse($expense->date);
            return [
                'id' => $expense->id,
                'date' => $expenseDate->format('Y-m-d H:i:s'),
                'description' => $expense->description,
                'amount' => $expense->amount, // Already decrypted by accessor
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
                'date' => $incomeDate->format('Y-m-d H:i:s'),
                'description' => $income->source ?? 'Income',
                'amount' => $income->amount, // Already decrypted by accessor
                'category' => 'Income',
                'category_id' => null,
                'type' => 'income',
                'day_of_week' => $incomeDate->dayName,
                'week_number' => $incomeDate->weekOfMonth,
                'hour' => $incomeDate->hour,
                'is_weekend' => in_array($incomeDate->dayOfWeek, [0, 6])
            ];
        }))->sortByDesc('date')->values()->toArray();

        // Calculate totals - sum() works with decrypted accessors
        $totalIncome = $incomes->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        $netSavings = $totalIncome - $totalExpenses;

        // Get budgets
        $currentMonthNumeric = (int) $currentDate->format('n');
        $currentYearNumeric = (int) $currentDate->format('Y');
        
        $budgets = Budget::where('user_id', $userId)
            ->where('month', $currentMonthNumeric)
            ->where('year', $currentYearNumeric)
            ->with('category')
            ->get();

        $monthlyBudget = $budgets->sum('amount'); // Auto-decrypted
        $remainingBudget = $monthlyBudget - $totalExpenses; // Total budget minus ALL expenses

        // ========== CATEGORY ANALYSIS ==========
        $categories = Category::all();
        $categoryBreakdown = [];

        foreach ($categories as $category) {
            $categoryExpenses = $expenses->where('category_id', $category->id);
            $spent = $categoryExpenses->sum('amount');
            
            if ($spent > 0 || $budgets->where('category_id', $category->id)->count() > 0) {
                $budget = $budgets->where('category_id', $category->id)->first();
                $sixMonthAvg = $this->getCategoryAverage($userId, $category->id, $currentDate, 6);
                $varianceVsAvg = $sixMonthAvg > 0 
                    ? (($spent - $sixMonthAvg) / $sixMonthAvg) * 100 
                    : 0;
                
                $topTransactions = $categoryExpenses->sortByDesc('amount')->take(3)->pluck('description')->toArray();
                
                $categoryBreakdown[] = [
                    'name' => $category->name,
                    'expense' => $spent,
                    'budget' => $budget ? $budget->amount : 0,
                    'remaining' => $budget ? ($budget->amount - $spent) : 0,
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

        usort($categoryBreakdown, function($a, $b) {
            return $b['expense'] <=> $a['expense'];
        });

        // ========== HISTORICAL DATA (12 MONTHS) ==========
        $historicalMonths = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $monthIncomes = Income::where('user_id', $userId)
                ->where('date', '>=', $periodStart)
                ->where('date', '<=', $periodEnd)
                ->get();

            $monthExpensesCollection = Expense::where('user_id', $userId)
                ->where('date', '>=', $periodStart)
                ->where('date', '<=', $periodEnd)
                ->get();

            $monthIncome = $monthIncomes->sum('amount');
            $monthExpenses = $monthExpensesCollection->sum('amount');
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

        // ========== WEEKLY BREAKDOWN ==========
        $weeklyBreakdown = $this->calculateWeeklyBreakdown($expenses, $startDate, $endDate);

        // ========== DAILY SPENDING PATTERN ==========
        $dailyPattern = $this->analyzeDailyPattern($expenses);

        // ========== SPENDING VELOCITY ==========
        $now = Carbon::now();
        if ($now->lt($startDate)) {
            $daysElapsed = 0;
            $daysRemaining = $startDate->diffInDays($endDate) + 1;
        } elseif ($now->gt($endDate)) {
            $daysElapsed = $startDate->diffInDays($endDate) + 1;
            $daysRemaining = 0;
        } else {
            $daysElapsed = max(1, $startDate->diffInDays($now) + 1);
            $daysRemaining = max(0, $now->diffInDays($endDate));
        }
        
        $currentPace = $daysElapsed > 0 ? $totalExpenses / $daysElapsed : 0;
        
        $historicalAvg = collect($historicalMonths)
            ->where('expenses', '>', 0)
            ->avg('expenses') ?? 0;
        $historicalPace = $historicalAvg / 30;
        
        $acceleration = $historicalPace > 0 
            ? (($currentPace - $historicalPace) / $historicalPace) * 100 
            : 0;

        $projectedMonthEnd = $totalExpenses + ($currentPace * $daysRemaining);
        
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

        // ========== BUDGET VS ACTUAL SUMMARY ==========
        $budgetSummary = [
            'total_budget' => $monthlyBudget,
            'total_spent' => $totalExpenses,
            'budget_utilization' => $monthlyBudget > 0 ? ($totalExpenses / $monthlyBudget) * 100 : 0,
            'categories' => $categoryBreakdown
        ];

        // ========== AI DATA ==========
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

        $aiInsights = $this->geminiService->analyzeFinancialData($aiData);

        // ========== CHART DATA ==========
        $dailySpending = $expenses->groupBy(function($expense) {
            return Carbon::parse($expense->date)->format('Y-m-d');
        })->map(function($group, $date) {
            return [
                'date' => Carbon::parse($date)->format('M d'),
                'amount' => $group->sum('amount'),
                'count' => $group->count()
            ];
        })->values();

        $categoryChart = collect($categoryBreakdown)
            ->filter(fn($c) => $c['expense'] > 0)
            ->sortByDesc('expense')
            ->map(function($cat) {
                return [
                    'category' => $cat['name'],
                    'amount' => $cat['expense'],
                    'percent' => $cat['percent_of_total']
                ];
            })->values();

        $monthlyTrend = collect($historicalMonths);

        // ========== PREVIOUS PERIOD COMPARISON ==========
        $previousStartDate = $currentDate->copy()->subMonths(2)->setDay(26)->startOfDay();
        $previousEndDate = $currentDate->copy()->subMonth()->setDay(25)->endOfDay();

        $previousIncomes = Income::where('user_id', $userId)
            ->where('date', '>=', $previousStartDate)
            ->where('date', '<=', $previousEndDate)
            ->get();

        $previousExpensesCollection = Expense::where('user_id', $userId)
            ->where('date', '>=', $previousStartDate)
            ->where('date', '<=', $previousEndDate)
            ->get();

        $previousIncome = $previousIncomes->sum('amount');
        $previousExpenses = $previousExpensesCollection->sum('amount');

        $incomeChange = $previousIncome > 0 
            ? (($totalIncome - $previousIncome) / $previousIncome) * 100 
            : ($totalIncome > 0 ? 100 : 0);

        $expenseChange = $previousExpenses > 0 
            ? (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 
            : ($totalExpenses > 0 ? 100 : 0);

        // Weekend vs Weekday spending
        $weekendSpending = collect($allTransactions)
            ->where('type', 'expense')
            ->where('is_weekend', true)
            ->sum('amount');
            
        $weekdaySpending = $totalExpenses - $weekendSpending;

        return view('dashboard', compact(
            'viewMode',
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
            'budgetSummary',
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

    private function yearView($request, $selectedYear, $userId)
    {
        $viewMode = 'year';
        
        // Calculate year range (26th Dec previous year to 25th Dec current year)
        $yearStart = Carbon::createFromDate($selectedYear - 1, 12, 26)->startOfDay();
        $yearEnd = Carbon::createFromDate($selectedYear, 12, 25)->endOfDay();

        // Get all year data
        $yearExpensesCollection = Expense::where('user_id', $userId)
            ->where('date', '>=', $yearStart)
            ->where('date', '<=', $yearEnd)
            ->get();

        $yearIncomesCollection = Income::where('user_id', $userId)
            ->where('date', '>=', $yearStart)
            ->where('date', '<=', $yearEnd)
            ->get();

        $yearExpenses = $yearExpensesCollection->sum('amount');
        $yearIncome = $yearIncomesCollection->sum('amount');
        $yearSavings = $yearIncome - $yearExpenses;
        $yearSavingsRate = $yearIncome > 0 ? (($yearIncome - $yearExpenses) / $yearIncome) * 100 : 0;

        // Previous year comparison
        $prevYearStart = Carbon::createFromDate($selectedYear - 2, 12, 26)->startOfDay();
        $prevYearEnd = Carbon::createFromDate($selectedYear - 1, 12, 25)->endOfDay();

        $prevYearExpensesCollection = Expense::where('user_id', $userId)
            ->where('date', '>=', $prevYearStart)
            ->where('date', '<=', $prevYearEnd)
            ->get();

        $prevYearExpenses = $prevYearExpensesCollection->sum('amount');

        $yearOverYearChange = $prevYearExpenses > 0 
            ? (($yearExpenses - $prevYearExpenses) / $prevYearExpenses) * 100 
            : 0;

        // Monthly breakdown
        $yearlyMonthlyBreakdown = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $monthDate = Carbon::createFromDate($selectedYear, $i, 1);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $monthIncomes = Income::where('user_id', $userId)
                ->where('date', '>=', $periodStart)
                ->where('date', '<=', $periodEnd)
                ->get();

            $monthExpensesCollection = Expense::where('user_id', $userId)
                ->where('date', '>=', $periodStart)
                ->where('date', '<=', $periodEnd)
                ->get();

            $monthIncome = $monthIncomes->sum('amount');
            $monthExpenses = $monthExpensesCollection->sum('amount');

            $yearlyMonthlyBreakdown[] = [
                'month' => $monthDate->format('M'),
                'income' => $monthIncome,
                'expenses' => $monthExpenses,
                'savings' => $monthIncome - $monthExpenses
            ];
        }

        // Available years
        $availableYears = Expense::where('user_id', $userId)
            ->selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        $allTransactions = [];

        return view('dashboard', compact(
            'viewMode',
            'selectedYear',
            'yearIncome',
            'yearExpenses',
            'yearSavings',
            'yearSavingsRate',
            'yearOverYearChange',
            'yearlyMonthlyBreakdown',
            'availableYears',
            'allTransactions'
        ));
    }

    private function getCategoryAverage($userId, $categoryId, $currentDate, $months = 6)
    {
        $total = 0;
        $count = 0;

        for ($i = 1; $i <= $months; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $monthExpenses = Expense::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->where('date', '>=', $periodStart)
                ->where('date', '<=', $periodEnd)
                ->get();

            $monthTotal = $monthExpenses->sum('amount');

            if ($monthTotal > 0) {
                $total += $monthTotal;
                $count++;
            }
        }

        return $count > 0 ? $total / $count : 0;
    }

    private function calculateWeeklyBreakdown($expenses, $startDate, $endDate)
    {
        $weeks = [];
        $currentWeekStart = $startDate->copy();
        $weekNumber = 1;
        $allWeekTotals = [];

        // First pass
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

        // Second pass
        while ($currentWeekStart->lte($endDate)) {
            $weekEnd = $currentWeekStart->copy()->addDays(6);
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }

            $weekExpenses = $expenses->filter(function($expense) use ($currentWeekStart, $weekEnd) {
                $expenseDate = Carbon::parse($expense->date);
                return $expenseDate->between($currentWeekStart, $weekEnd);
            });

            $weekTotal = $weekExpenses->sum('amount');
            $notableItems = $weekExpenses->sortByDesc('amount')->take(2)->pluck('description')->toArray();

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