<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
use App\Models\Category;
use App\Services\GeminiAIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

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
        $currentDate = Carbon::parse($selectedMonth);
        $userId = auth()->id();

        // Define custom month period (26th of previous month to 25th of current month)
        $startDate = $currentDate->copy()->subMonth()->setDay(26)->startOfDay();
        $endDate = $currentDate->copy()->setDay(25)->endOfDay();

        $currentMonthNumeric = (int) date('n');
        $currentYearNumeric = (int) date('Y');

        // Fetch ALL transactions for AI analysis
        $recentExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with('category')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($expense) {
                $expense->type = 'Expense';
                return $expense;
            });

        $recentIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($income) {
                $income->type = 'Income';
                return $income;
            });

        $totalExpenses = $recentExpenses->sum('amount');
        $totalIncome = $recentIncome->sum('amount');

        $monthlyBudget = Budget::where('user_id', $userId)
            ->where('month', $currentMonthNumeric)
            ->where('year', $currentYearNumeric)
            ->get()
            ->sum('amount');
    
        // PREVIOUS MONTH COMPARISON
        $previousStartDate = $currentDate->copy()->subMonths(2)->setDay(26)->startOfDay();
        $previousEndDate = $currentDate->copy()->subMonth()->setDay(25)->endOfDay();

        $previousTotalIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate->toDateString(), $previousEndDate->toDateString()])
            ->get()
            ->sum('amount');

        $previousTotalExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate->toDateString(), $previousEndDate->toDateString()])
            ->get()
            ->sum('amount');

        $incomePercentageChange = $previousTotalIncome > 0
            ? (($totalIncome - $previousTotalIncome) / $previousTotalIncome) * 100
            : ($totalIncome > 0 ? 100 : 0);

        $expensesPercentageChange = $previousTotalExpenses > 0
            ? (($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100
            : ($totalExpenses > 0 ? 100 : 0);

        // MERGE ALL TRANSACTIONS (this was missing!)
        $allTransactions = $recentExpenses->merge($recentIncome)->sortByDesc('date');
        
        $netSavings = $totalIncome - $totalExpenses;

        $historicalTrends = $this->calculateHistoricalTrends($userId, $currentDate, $totalExpenses, $startDate, $endDate);
        $spendingVelocity = $this->calculateSpendingVelocity($totalExpenses, $historicalTrends, $startDate, $endDate);

        $allCategories = Category::all();
        $categoryAverages = $this->calculateCategoryAverages($userId, $allCategories);

        $groupedExpenses = $recentExpenses->groupBy('category_id')
            ->map(function ($group) use ($userId, $currentMonthNumeric, $currentYearNumeric, $categoryAverages) {
                $categoryId = $group->first()->category_id;
                $totalExpense = $group->sum('amount');

                if ($totalExpense > 0) {
                    $category = Category::find($categoryId);

                    if ($category) {
                        $budgetForCategory = Budget::where('user_id', $userId)
                            ->where('category_id', $categoryId)
                            ->where('month', $currentMonthNumeric)
                            ->where('year', $currentYearNumeric)
                            ->first();

                        $categoryAverage = $categoryAverages->get($categoryId, ['average_amount' => 0]);

                        return [
                            'name' => $category->name,
                            'expense' => $totalExpense,
                            'budget' => $budgetForCategory ? $budgetForCategory->amount : 0,
                            'average_amount' => $categoryAverage['average_amount'],
                            'vs_average' => $categoryAverage['average_amount'] > 0
                                ? round((($totalExpense - $categoryAverage['average_amount']) / $categoryAverage['average_amount']) * 100, 1)
                                : 0
                        ];
                    }
                }
                return null;
            })
            ->filter();

        $categoryTrends = $this->calculateAllCategoryTrends($userId, $currentDate, $allCategories);

        $finalData = $allCategories->map(function ($category) use ($groupedExpenses, $categoryAverages, $categoryTrends) {
            $expenseData = $groupedExpenses->firstWhere('name', $category->name);
            $categoryAverage = $categoryAverages->get($category->id, ['average_amount' => 0]);
            $categoryTrend = $categoryTrends->get($category->id, ['trend' => 'stable', 'average' => 0]);

            return [
                'name' => $category->name,
                'expense' => $expenseData['expense'] ?? 0,
                'budget' => $expenseData['budget'] ?? 0,
                'average_amount' => $categoryAverage['average_amount'],
                'vs_average' => $expenseData['vs_average'] ?? 0,
                'trend' => $categoryTrend['trend'],
                'trend_average' => $categoryTrend['average']
            ];
        });

        $remainingBudget = $monthlyBudget - $totalExpenses;

        $labels = $groupedExpenses->pluck('name');
        $data = $groupedExpenses->pluck('expense');
        $budgetsData = $groupedExpenses->pluck('budget');
        $averagesData = $groupedExpenses->pluck('average_amount');

        $topExpenses = $recentExpenses
            ->groupBy('description')
            ->map(fn($group) => [
                'description' => $group->first()->description,
                'frequency' => $group->count(),
                'total_amount' => $group->sum('amount'),
            ])
            ->sortByDesc('total_amount')
            ->take(10);

        if ($topExpenses->isEmpty()) {
            $topExpenses = collect([[
                'description' => 'N/A',
                'frequency' => 0,
                'total_amount' => 0,
            ]]);
        }

        $weeklyBreakdown = $this->calculateWeeklyBreakdown($userId, $startDate, $endDate);
        $averageWeeklySpent = count($weeklyBreakdown) > 0
            ? collect($weeklyBreakdown)->avg('total_expense')
            : 0;

        $projectedMonthlySpend = $historicalTrends['projected_total'];

        $spendingPatterns = $this->analyzeSpendingPatterns($userId, $currentDate, $recentExpenses);
        $savingsRate = $this->calculateSavingsRate($userId, $currentDate);
        $expenseVolatility = $this->calculateExpenseVolatility($userId, $currentDate);
        $categoryHealthScores = $this->calculateCategoryHealthScores($userId, $currentDate, $allCategories);
        $spendingMomentum = $this->calculateSpendingMomentum($userId, $currentDate);
        $budgetEfficiency = $this->calculateBudgetEfficiency($userId, $currentDate, $currentMonthNumeric, $currentYearNumeric);
        $unusualTransactions = $this->detectUnusualTransactions($recentExpenses, $categoryAverages);
        $seasonalInsights = $this->getSeasonalInsights($userId, $currentDate);

        // Prepare ALL transaction data for AI
        $allTransactionsForAI = $recentExpenses->map(function($expense) {
            return [
                'description' => $expense->description,
                'amount' => $expense->amount,
                'date' => $expense->date,
                'category' => $expense->category->name ?? 'uncategorized',
                'type' => 'expense'
            ];
        })->toArray();

        // ========== ENHANCED GEMINI AI INTEGRATION ==========
        $aiInsights = $this->getAIInsights($userId, $currentDate, [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'monthlyBudget' => $monthlyBudget,
            'remainingBudget' => $remainingBudget,
            'daysElapsed' => $historicalTrends['days_elapsed'],
            'daysRemaining' => $historicalTrends['days_remaining'],
            'allTransactions' => $allTransactionsForAI, // NEW: All individual transactions
            'historicalTrends' => $historicalTrends,
            'categoryData' => $finalData->toArray(),
            'spendingPatterns' => $spendingPatterns,
            'savingsRate' => $savingsRate,
            'expenseVolatility' => $expenseVolatility,
            'spendingVelocity' => $spendingVelocity,
            'spendingMomentum' => $spendingMomentum,
            'budgetEfficiency' => $budgetEfficiency,
            'unusualTransactions' => $unusualTransactions->toArray(),
            'categoryHealthScores' => $categoryHealthScores->toArray()
        ]);

        // Calculate daily spending for chart
        $dailySpending = $recentExpenses->groupBy(function($expense) {
            return Carbon::parse($expense->date)->format('Y-m-d');
        })->map->sum('amount')->sortKeys();

        // Prepare category breakdown
        $categoryBreakdown = $finalData->filter(fn($cat) => $cat['expense'] > 0);

        return view('dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'netSavings',
            'monthlyBudget',
            'incomePercentageChange',
            'expensesPercentageChange',
            'allTransactions',           // <-- THIS WAS MISSING!
            'remainingBudget',
            'selectedMonth',
            'dailySpending',
            'categoryBreakdown',
            'aiInsights'
        ));
    }

    /**
     * Get AI-powered insights with caching
     */
    private function getAIInsights($userId, $currentDate, $data)
    {
        // Cache key includes a hash of transaction count to refresh when data changes
        $transactionHash = md5(json_encode($data['allTransactions'] ?? []));
        $cacheKey = "ai_insights_{$userId}_{$currentDate->format('Y-m')}_{$transactionHash}";
        
        // Cache for 30 minutes (shorter to pick up new transactions faster)
        return Cache::remember($cacheKey, 1800, function () use ($data) {
            return $this->geminiService->analyzeBudgetData($data);
        });
    }

    /**
     * Analyze spending patterns (weekday vs weekend, time of month, etc.)
     */
    private function analyzeSpendingPatterns($userId, $currentDate, $recentExpenses)
    {
        $patterns = [
            'weekday_vs_weekend' => [],
            'time_of_month' => [],
            'highest_spending_day' => null,
            'spending_consistency' => 0
        ];

        if ($recentExpenses->isEmpty()) {
            return $patterns;
        }

        // Weekday vs Weekend
        $weekdaySpending = $recentExpenses->filter(function($expense) {
            $day = Carbon::parse($expense->date)->dayOfWeek;
            return $day >= 1 && $day <= 5;
        })->sum('amount');

        $weekendSpending = $recentExpenses->filter(function($expense) {
            $day = Carbon::parse($expense->date)->dayOfWeek;
            return $day == 0 || $day == 6;
        })->sum('amount');

        $patterns['weekday_vs_weekend'] = [
            'weekday' => $weekdaySpending,
            'weekend' => $weekendSpending,
            'preference' => $weekdaySpending > $weekendSpending ? 'weekday' : 'weekend'
        ];

        // Time of month
        $beginningSpending = $recentExpenses->filter(function($expense) {
            $day = Carbon::parse($expense->date)->day;
            return $day >= 26 || $day <= 5;
        })->sum('amount');

        $middleSpending = $recentExpenses->filter(function($expense) {
            $day = Carbon::parse($expense->date)->day;
            return $day >= 6 && $day <= 15;
        })->sum('amount');

        $endSpending = $recentExpenses->filter(function($expense) {
            $day = Carbon::parse($expense->date)->day;
            return $day >= 16 && $day <= 25;
        })->sum('amount');

        $patterns['time_of_month'] = [
            'beginning' => $beginningSpending,
            'middle' => $middleSpending,
            'end' => $endSpending,
            'peak_period' => collect([
                'beginning' => $beginningSpending,
                'middle' => $middleSpending,
                'end' => $endSpending
            ])->sortDesc()->keys()->first()
        ];

        // Highest spending day
        $dailySpending = $recentExpenses->groupBy(function($expense) {
            return Carbon::parse($expense->date)->format('l');
        })->map->sum('amount')->sortDesc();

        if ($dailySpending->isNotEmpty()) {
            $patterns['highest_spending_day'] = [
                'day' => $dailySpending->keys()->first(),
                'amount' => $dailySpending->first()
            ];
        }

        // Spending consistency
        $dailyTotals = $recentExpenses->groupBy(function($expense) {
            return Carbon::parse($expense->date)->format('Y-m-d');
        })->map->sum('amount')->values();

        if ($dailyTotals->count() > 1) {
            $mean = $dailyTotals->avg();
            $stdDev = sqrt($dailyTotals->map(fn($val) => pow($val - $mean, 2))->sum() / $dailyTotals->count());
            $patterns['spending_consistency'] = $mean > 0 ? ($stdDev / $mean) * 100 : 0;
        }

        return $patterns;
    }

    /**
     * Calculate savings rate over time
     */
    private function calculateSavingsRate($userId, $currentDate)
    {
        $rates = [];

        for ($i = 0; $i < 6; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $monthIncome = Income::where('user_id', $userId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $monthExpenses = Expense::where('user_id', $userId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $savingsRate = $monthIncome > 0 
                ? (($monthIncome - $monthExpenses) / $monthIncome) * 100 
                : 0;

            $rates[] = [
                'month' => $monthDate->format('M Y'),
                'rate' => round($savingsRate, 1),
                'saved' => $monthIncome - $monthExpenses
            ];
        }

        $averageRate = collect($rates)->avg('rate');
        $trend = $this->determineTrend(collect($rates)->pluck('rate')->toArray());

        return [
            'monthly_rates' => array_reverse($rates),
            'average_rate' => round($averageRate, 1),
            'trend' => $trend,
            'status' => $averageRate > 20 ? 'excellent' : ($averageRate > 10 ? 'good' : 'needs_improvement')
        ];
    }

    /**
     * Calculate expense volatility
     */
    private function calculateExpenseVolatility($userId, $currentDate)
    {
        $monthlyExpenses = [];

        for ($i = 0; $i < 12; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $total = Expense::where('user_id', $userId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $monthlyExpenses[] = $total;
        }

        $monthlyExpenses = collect($monthlyExpenses)->filter(fn($val) => $val > 0);

        if ($monthlyExpenses->count() < 3) {
            return [
                'volatility_score' => 0,
                'stability' => 'insufficient_data',
                'range' => 0
            ];
        }

        $mean = $monthlyExpenses->avg();
        $stdDev = sqrt($monthlyExpenses->map(fn($val) => pow($val - $mean, 2))->sum() / $monthlyExpenses->count());
        $volatility = $mean > 0 ? ($stdDev / $mean) * 100 : 0;

        $range = $monthlyExpenses->max() - $monthlyExpenses->min();

        return [
            'volatility_score' => round($volatility, 1),
            'stability' => $volatility < 15 ? 'very_stable' : ($volatility < 30 ? 'stable' : ($volatility < 50 ? 'moderate' : 'volatile')),
            'range' => round($range, 2),
            'interpretation' => $volatility < 15 ? 'Consistent spending' : ($volatility < 30 ? 'Minor fluctuations' : 'Significant variations')
        ];
    }

    /**
     * Calculate health scores for each category
     */
    private function calculateCategoryHealthScores($userId, $currentDate, $allCategories)
    {
        $scores = collect();

        foreach ($allCategories as $category) {
            $periodStart = $currentDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $currentDate->copy()->setDay(25)->endOfDay();

            $currentSpending = Expense::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $historicalAvg = 0;
            $monthsWithData = 0;

            for ($i = 1; $i <= 6; $i++) {
                $monthDate = $currentDate->copy()->subMonths($i);
                $hPeriodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
                $hPeriodEnd = $monthDate->copy()->setDay(25)->endOfDay();

                $monthTotal = Expense::where('user_id', $userId)
                    ->where('category_id', $category->id)
                    ->whereBetween('date', [$hPeriodStart, $hPeriodEnd])
                    ->sum('amount');

                if ($monthTotal > 0) {
                    $historicalAvg += $monthTotal;
                    $monthsWithData++;
                }
            }

            $historicalAvg = $monthsWithData > 0 ? $historicalAvg / $monthsWithData : 0;

            $score = 100;

            if ($historicalAvg > 0) {
                $deviation = (($currentSpending - $historicalAvg) / $historicalAvg) * 100;
                
                if ($deviation > 20) {
                    $score -= min(40, ($deviation - 20) * 2);
                }

                $consistency = $this->getCategoryConsistency($userId, $currentDate, $category->id);
                $score -= (100 - $consistency) * 0.3;
            }

            $scores->put($category->id, [
                'category' => $category->name,
                'score' => max(0, round($score, 1)),
                'status' => $score >= 80 ? 'healthy' : ($score >= 60 ? 'fair' : 'needs_attention'),
                'current_spending' => $currentSpending,
                'average_spending' => $historicalAvg
            ]);
        }

        return $scores->sortByDesc('score');
    }

    /**
     * Get category consistency score
     */
    private function getCategoryConsistency($userId, $currentDate, $categoryId)
    {
        $monthlyTotals = [];

        for ($i = 0; $i < 6; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $total = Expense::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $monthlyTotals[] = $total;
        }

        $monthlyTotals = collect($monthlyTotals)->filter(fn($val) => $val > 0);

        if ($monthlyTotals->count() < 2) {
            return 100;
        }

        $mean = $monthlyTotals->avg();
        $stdDev = sqrt($monthlyTotals->map(fn($val) => pow($val - $mean, 2))->sum() / $monthlyTotals->count());
        $cv = $mean > 0 ? ($stdDev / $mean) * 100 : 0;

        return max(0, 100 - $cv);
    }

    /**
     * Calculate spending momentum
     */
    private function calculateSpendingMomentum($userId, $currentDate)
    {
        $recentMonths = [];
        $olderMonths = [];

        for ($i = 0; $i < 2; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $total = Expense::where('user_id', $userId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $recentMonths[] = $total;
        }

        for ($i = 2; $i < 4; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();

            $total = Expense::where('user_id', $userId)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');

            $olderMonths[] = $total;
        }

        $recentAvg = collect($recentMonths)->avg();
        $olderAvg = collect($olderMonths)->avg();

        $momentum = $olderAvg > 0 ? (($recentAvg - $olderAvg) / $olderAvg) * 100 : 0;

        return [
            'momentum_score' => round($momentum, 1),
            'direction' => $momentum > 5 ? 'accelerating' : ($momentum < -5 ? 'decelerating' : 'stable'),
            'interpretation' => $momentum > 5 
                ? 'Spending is increasing' 
                : ($momentum < -5 ? 'Spending is decreasing' : 'Spending is stable')
        ];
    }

    /**
     * Calculate budget efficiency
     */
    private function calculateBudgetEfficiency($userId, $currentDate, $currentMonth, $currentYear)
    {
        $totalBudget = Budget::where('user_id', $userId)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('amount');

        if ($totalBudget == 0) {
            return ['efficiency' => 0, 'status' => 'no_budget'];
        }

        $periodStart = $currentDate->copy()->subMonth()->setDay(26)->startOfDay();
        $periodEnd = $currentDate->copy()->setDay(25)->endOfDay();

        $actualSpending = Expense::where('user_id', $userId)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('amount');

        $efficiency = $totalBudget > 0 ? (1 - ($actualSpending / $totalBudget)) * 100 : 0;

        return [
            'efficiency_score' => round($efficiency, 1),
            'status' => $efficiency > 20 ? 'excellent' : ($efficiency > 0 ? 'good' : ($efficiency > -10 ? 'close' : 'over_budget')),
            'variance' => $totalBudget - $actualSpending
        ];
    }

    /**
     * Detect unusual transactions
     */
    private function detectUnusualTransactions($recentExpenses, $categoryAverages)
    {
        $unusual = collect();

        foreach ($recentExpenses as $expense) {
            $categoryAvg = $categoryAverages->get($expense->category_id, ['average_amount' => 0])['average_amount'];
            
            if ($categoryAvg > 0 && $expense->amount > ($categoryAvg * 2)) {
                $unusual->push([
                    'date' => $expense->date,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'category' => $expense->category->name ?? 'Unknown',
                    'deviation' => round((($expense->amount / $categoryAvg) - 1) * 100, 1)
                ]);
            }
        }

        return $unusual->sortByDesc('amount')->take(5);
    }

    /**
     * Get seasonal insights
     */
    private function getSeasonalInsights($userId, $currentDate)
    {
        $currentMonth = $currentDate->month;
        $currentQuarter = ceil($currentMonth / 3);

        $quarterlyData = [];

        for ($q = 1; $q <= 4; $q++) {
            $quarterStart = Carbon::create($currentDate->year, ($q - 1) * 3 + 1, 26)->startOfDay();
            $quarterEnd = Carbon::create($currentDate->year, $q * 3, 25)->endOfDay();

            $total = Expense::where('user_id', $userId)
                ->whereBetween('date', [$quarterStart, $quarterEnd])
                ->sum('amount');

            $quarterlyData[$q] = $total;
        }

        $highestQuarter = collect($quarterlyData)->sortDesc()->keys()->first();
        $lowestQuarter = collect($quarterlyData)->filter(fn($val) => $val > 0)->sortKeys()->keys()->first();

        return [
            'current_quarter' => $currentQuarter,
            'quarterly_totals' => $quarterlyData,
            'highest_spending_quarter' => $highestQuarter,
            'lowest_spending_quarter' => $lowestQuarter,
            'quarter_names' => ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)']
        ];
    }

    /**
     * Determine trend direction
     */
    private function determineTrend($values)
    {
        if (count($values) < 2) return 'stable';

        $increases = 0;
        $decreases = 0;

        for ($i = 1; $i < count($values); $i++) {
            if ($values[$i] > $values[$i-1]) $increases++;
            if ($values[$i] < $values[$i-1]) $decreases++;
        }

        if ($increases > $decreases * 1.5) return 'increasing';
        if ($decreases > $increases * 1.5) return 'decreasing';
        return 'stable';
    }

    /**
     * Calculate historical trends
     */
    private function calculateHistoricalTrends($userId, $currentDate, $currentMonthExpenses, $currentStartDate, $currentEndDate)
    {
        $historicalExpenses = collect();
        
        for ($i = 1; $i <= 6; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();
            
            $monthTotal = Expense::where('user_id', $userId)
                ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->sum('amount');
            
            $historicalExpenses->push([
                'month' => $monthDate->format('M Y'),
                'period' => $periodStart->format('M d') . ' - ' . $periodEnd->format('M d, Y'),
                'total' => (float) $monthTotal,
            ]);
        }
        
        $historicalExpenses = $historicalExpenses->reverse()->values();
        $nonZeroExpenses = $historicalExpenses->where('total', '>', 0);
        $movingAverage = $nonZeroExpenses->avg('total') ?? 0;
        
        $trendDirection = 'stable';
        $trendPercentage = 0;
        
        if ($historicalExpenses->where('total', '>', 0)->count() >= 3) {
            $recentThreeAvg = $historicalExpenses->slice(-3)->avg('total');
            $olderThreeAvg = $historicalExpenses->slice(0, 3)->avg('total');
            
            if ($olderThreeAvg > 0) {
                $trendPercentage = (($recentThreeAvg - $olderThreeAvg) / $olderThreeAvg) * 100;
                
                if ($trendPercentage > 5) {
                    $trendDirection = 'increasing';
                } elseif ($trendPercentage < -5) {
                    $trendDirection = 'decreasing';
                }
            }
        }
        
        $today = now();
        $totalDaysInPeriod = $currentStartDate->diffInDays($currentEndDate) + 1;
        
        if ($today->between($currentStartDate, $currentEndDate)) {
            $daysElapsed = $currentStartDate->diffInDays($today) + 1;
        } else if ($today->gt($currentEndDate)) {
            $daysElapsed = $totalDaysInPeriod;
        } else {
            $daysElapsed = 1;
        }
        
        $daysElapsed = max(1, $daysElapsed);
        $daysRemaining = max(0, $totalDaysInPeriod - $daysElapsed);
        
        $dailyRate = $daysElapsed > 0 ? $currentMonthExpenses / $daysElapsed : 0;
        $simpleProjection = $currentMonthExpenses + ($dailyRate * $daysRemaining);
        
        if ($movingAverage > 0 && $nonZeroExpenses->count() >= 2) {
            $historicalBasedProjection = ($simpleProjection * 0.7) + ($movingAverage * 0.3);
            $projectedTotal = $historicalBasedProjection;
        } else {
            $projectedTotal = $simpleProjection;
        }
        
        $monthsWithData = $historicalExpenses->where('total', '>', 0)->count();
        if ($currentMonthExpenses > 0) {
            $monthsWithData++;
        }
        
        $hasData = $monthsWithData > 0;
        
        return [
            'moving_average'     => round($movingAverage, 2),
            'trend_direction'    => $trendDirection,
            'trend_percentage'   => round($trendPercentage, 1),
            'projected_total'    => round($projectedTotal, 2),
            'daily_rate'         => round($dailyRate, 2),
            'days_elapsed'       => $daysElapsed,
            'days_remaining'     => $daysRemaining,
            'total_days'         => $totalDaysInPeriod,
            'historical_data'    => $historicalExpenses,
            'historical_months'  => $monthsWithData,
            'has_data'           => $hasData,
        ];
    }

    /**
     * Calculate spending velocity
     */
    private function calculateSpendingVelocity($currentExpenses, $historicalTrends, $startDate, $endDate)
    {
        $daysElapsed = $historicalTrends['days_elapsed'];
        $currentPace = $daysElapsed > 0 ? $currentExpenses / $daysElapsed : 0;
        
        $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;
        $historicalPace = $totalDaysInPeriod > 0 ? $historicalTrends['moving_average'] / $totalDaysInPeriod : 0;
        
        $acceleration = $historicalPace > 0
            ? (($currentPace - $historicalPace) / $historicalPace) * 100
            : 0;
        
        return [
            'current_pace' => round($currentPace, 2),
            'historical_pace' => round($historicalPace, 2),
            'acceleration' => round($acceleration, 1),
            'status' => $acceleration > 15 ? 'fast' : ($acceleration < -15 ? 'slow' : 'normal')
        ];
    }

    /**
     * Calculate category averages
     */
    private function calculateCategoryAverages($userId, $allCategories)
    {
        $categoryAverages = collect();

        foreach ($allCategories as $category) {
            $expenses = Expense::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->orderBy('date')
                ->get();

            if ($expenses->isEmpty()) {
                $categoryAverages->put($category->id, [
                    'category_name' => $category->name,
                    'average_amount' => 0,
                    'months_with_data' => 0
                ]);
                continue;
            }

            $monthlyTotals = $expenses->groupBy(function ($expense) {
                $date = Carbon::parse($expense->date);
                
                if ($date->day >= 26) {
                    return $date->addMonth()->format('Y-m');
                } else {
                    return $date->format('Y-m');
                }
            })->map->sum('amount');

            $averageAmount = $monthlyTotals->avg() ?? 0;
            $monthsWithData = $monthlyTotals->where('>', 0)->count();

            $categoryAverages->put($category->id, [
                'category_name' => $category->name,
                'average_amount' => round($averageAmount, 2),
                'months_with_data' => $monthsWithData
            ]);
        }

        return $categoryAverages;
    }

    /**
     * Calculate all category trends
     */
    private function calculateAllCategoryTrends($userId, $currentDate, $allCategories)
    {
        $categoryTrends = collect();

        foreach ($allCategories as $category) {
            $trend = $this->calculateCategoryTrend($userId, $currentDate, $category->id);
            $categoryTrends->put($category->id, $trend);
        }

        return $categoryTrends;
    }

    /**
     * Calculate category trend
     */
    private function calculateCategoryTrend($userId, $currentDate, $categoryId)
    {
        $periodTotals = collect();
        
        for ($i = 1; $i <= 6; $i++) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $periodStart = $monthDate->copy()->subMonth()->setDay(26)->startOfDay();
            $periodEnd = $monthDate->copy()->setDay(25)->endOfDay();
            
            $total = Expense::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->sum('amount');
            
            $periodTotals->push([
                'period' => $monthDate->format('Y-m'),
                'total' => (float) $total
            ]);
        }
        
        $periodTotals = $periodTotals->reverse();
        $average = $periodTotals->avg('total') ?? 0;
        
        $trendDirection = 'stable';
        
        if ($periodTotals->where('total', '>', 0)->count() >= 3) {
            $recent = $periodTotals->slice(-2)->avg('total');
            $older = $periodTotals->slice(0, 2)->avg('total');
            
            if ($older > 0) {
                $change = (($recent - $older) / $older) * 100;
                if ($change > 10) {
                    $trendDirection = 'increasing';
                } elseif ($change < -10) {
                    $trendDirection = 'decreasing';
                }
            }
        }
        
        return [
            'average' => round($average, 2),
            'trend' => $trendDirection
        ];
    }

    /**
     * Calculate weekly breakdown
     */
    private function calculateWeeklyBreakdown($userId, $startDate, $endDate)
    {
        $weeklyBreakdown = [];
        $currentWeekStart = $startDate->copy();

        while ($currentWeekStart->lte($endDate)) {
            $weekEnd = $currentWeekStart->copy()->addDays(6);
            if ($weekEnd->gt($endDate)) {
                $weekEnd = $endDate->copy();
            }

            $weekExpenses = Expense::where('user_id', $userId)
                ->whereBetween('date', [$currentWeekStart->toDateString(), $weekEnd->toDateString()])
                ->sum('amount');

            $weeklyBreakdown[] = [
                'week_start' => $currentWeekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'week_range' => $currentWeekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'total_expense' => round($weekExpenses, 2)
            ];

            $currentWeekStart->addDays(7);
        }

        return $weeklyBreakdown;
    }
}