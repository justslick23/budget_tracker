<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
use App\Models\Category;

use Carbon\Carbon;
use Phpml\ModelManager; // Include this at the top if using PHP-ML
use App\Services\OpenAIService; // Add this line

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->middleware('auth');
        $this->openAIService = $openAIService; // Inject OpenAIService
    }

    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Get the selected month from the request, default to the current month
        $selectedMonth = $request->input('month', now()->format('Y-m')); // Default to current month
        
        // Parse the selected month into Carbon instances
        $currentDate = Carbon::parse($selectedMonth); // Parse the date for selected month
        $userId = auth()->id();
        
        // Define custom month period (26th of previous month to 25th of current month)
        $startDate = $currentDate->copy()->subMonth()->setDay(26)->startOfDay();
        $endDate = $currentDate->copy()->setDay(25)->endOfDay();
     
        $currentMonthNumeric = (int) $currentDate->format('m'); // Convert to integer explicitly
        $currentYearNumeric = (int) $currentDate->format('Y'); // Add year for budget queries
    
        // Fetch recent expenses and income for the selected month
        $recentExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($expense) {
                $expense->type = 'Expense';
                return $expense;
            });
    
        $recentIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
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
            ->sum(function ($budget) {
                return $budget->amount;  // The getter will automatically decrypt the amount
            });
        
        // Fetch data for the previous month for comparison
        $previousStartDate = $currentDate->copy()->subMonths(2)->setDay(26)->startOfDay();
        $previousEndDate = $currentDate->copy()->subMonth()->setDay(25)->endOfDay();
        $previousMonthNumeric = (int) $currentDate->copy()->subMonth()->format('m');
        $previousYearNumeric = (int) $currentDate->copy()->subMonth()->format('Y');
    
        $previousTotalIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->get()
            ->sum('amount');
    
        $previousTotalExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->get()
            ->sum('amount');
    
        // Calculate percentage change in income and expenses
        $incomePercentageChange = $previousTotalIncome > 0 ? (($totalIncome - $previousTotalIncome) / $previousTotalIncome) * 100 : 0;
        $expensesPercentageChange = $previousTotalExpenses > 0 ? (($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100 : 0;
    
        // Combine the recent transactions and sort by date
        $recentTransactions = $recentExpenses->merge($recentIncome)->sortByDesc('date');
    
        $netSavings = $totalIncome - $totalExpenses;
    
        // Fetch all categories for comparison
        $allCategories = Category::all();
    
        // Calculate historical averages for each category (ALL TIME data)
        $categoryAverages = collect();
        
        foreach ($allCategories as $category) {
            // Get ALL historical expenses for this category (no date restriction)
            $historicalExpenses = Expense::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->get();
            
            // Group by month and calculate monthly totals
            $monthlyTotals = $historicalExpenses->groupBy(function ($expense) {
                $expenseDate = Carbon::parse($expense->date);
                // Use custom month period grouping (26th to 25th)
                if ($expenseDate->day >= 26) {
                    return $expenseDate->format('Y-m');
                } else {
                    return $expenseDate->subMonth()->format('Y-m');
                }
            })->map(function ($monthExpenses) {
                return $monthExpenses->sum('amount');
            });
            
            // Calculate average (only for months that had expenses to avoid skewing)
            $averageAmount = $monthlyTotals->count() > 0 ? $monthlyTotals->avg() : 0;
            
            $categoryAverages->put($category->id, [
                'category_name' => $category->name,
                'average_amount' => round($averageAmount, 2),
                'months_with_data' => $monthlyTotals->count()
            ]);
        }
    
        // Group recent expenses by category and calculate total expenses and budgets
        $groupedExpenses = $recentExpenses->groupBy('category_id')->map(function ($group) use ($userId, $currentMonthNumeric, $currentYearNumeric, $categoryAverages) {
            // Get the category ID for this group
            $categoryId = $group->first()->category_id; 
        
            // Calculate the total expense for the current category
            $totalExpense = $group->sum('amount');
        
            // Only process categories that have expenses
            if ($totalExpense > 0) {
                // Fetch the category based on category_id
                $category = Category::find($categoryId);
        
                // Check if category exists
                if ($category) {
                    // Fetch the budget for the current category and month
                    $budgetForCategory = Budget::where('user_id', $userId)
                        ->where('category_id', $categoryId)
                        ->where('month', $currentMonthNumeric)
                        ->where('year', $currentYearNumeric)
                        ->first();
    
                    // Get historical average for this category
                    $categoryAverage = $categoryAverages->get($categoryId, ['average_amount' => 0]);
    
                    return [
                        'name' => $category->name,
                        'expense' => $totalExpense,
                        'budget' => $budgetForCategory ? $budgetForCategory->amount : 0,
                        'average_amount' => $categoryAverage['average_amount'],
                        'vs_average' => $categoryAverage['average_amount'] > 0 ? 
                            round((($totalExpense - $categoryAverage['average_amount']) / $categoryAverage['average_amount']) * 100, 1) : 0
                    ];
                }
            }
        })->filter();
    
        // Prepare final data by including all categories, even those with no expenses
        $finalData = $allCategories->map(function ($category) use ($groupedExpenses, $categoryAverages) {
            $expenseData = $groupedExpenses->firstWhere('name', $category->name);
            $categoryAverage = $categoryAverages->get($category->id, ['average_amount' => 0]);
    
            return [
                'name' => $category->name,
                'expense' => $expenseData['expense'] ?? 0,
                'budget' => $expenseData['budget'] ?? 0,
                'average_amount' => $categoryAverage['average_amount'],
                'vs_average' => isset($expenseData['vs_average']) ? $expenseData['vs_average'] : 0
            ];
        });
    
        // Calculate the remaining budget
        $remainingBudget = $monthlyBudget - $totalExpenses;
    
        // Labels and data for charts
        $labels = $groupedExpenses->pluck('name');
        $data = $groupedExpenses->pluck('expense');
        $budgetsData = $groupedExpenses->pluck('budget');
        $averagesData = $groupedExpenses->pluck('average_amount'); // New: averages for chart
    
        // Monthly Budget Percentage Change
        $previousTotalBudget = Budget::where('user_id', $userId)
            ->where('month', $previousMonthNumeric)
            ->where('year', $previousYearNumeric)
            ->get()
            ->sum('amount');
            
        $budgetPercentageChange = $previousTotalBudget > 0 ? (($monthlyBudget - $previousTotalBudget) / $previousTotalBudget) * 100 : 0;
    
        $monthsToShow = $request->input('filter', 12); // Default to last 12 months
    
        $months = [];
        $monthlyBudgets = [];
        $monthlyExpenses = [];
    
        // Loop through the last N months
        for ($i = $monthsToShow; $i >= 0; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $year = $date->year;
            $month = $date->month;
            
            // Get total budget for the month
            $totalBudget = Budget::where('user_id', $userId)
                ->where('year', $year)
                ->where('month', $month)
                ->get()
                ->sum('amount');
    
            // Fixed: Use custom month period for expenses calculation
            $monthStartDate = $date->copy()->subMonth()->setDay(26)->startOfDay();
            $monthEndDate = $date->copy()->setDay(25)->endOfDay();
    
            $totalExpense = Expense::where('user_id', $userId)
                ->whereBetween('date', [$monthStartDate, $monthEndDate])
                ->get()
                ->sum('amount');
    
            // Store data in arrays
            $months[] = $date->format('M Y');
            $monthlyBudgets[] = $totalBudget;
            $monthlyExpenses[] = $totalExpense;
        }
        
        // Calculate the number of weeks in the selected month
        $startOfMonth = $startDate->copy();
        $endOfMonth = $endDate->copy();
    
        // Difference in weeks (inclusive)
        $numberOfWeeks = ceil($startOfMonth->diffInDays($endOfMonth) / 7);
    
        // Avoid division by zero
        $averageWeeklySpent = $numberOfWeeks > 0 ? $totalExpenses / $numberOfWeeks : 0;
    
        $weeklyBreakdown = [];
        
        // Start from the beginning of our custom month period
        $currentWeekStart = $startDate->copy()->startOfWeek();
        
        // If the start of the week is before our period start, adjust it
        if ($currentWeekStart->lt($startDate)) {
            $currentWeekStart = $startDate->copy();
        }
    
        // Loop through each week in the period
        while ($currentWeekStart->lte($endDate)) {
            // Calculate week end (6 days after start)
            $currentWeekEnd = $currentWeekStart->copy()->addDays(6);
            
            // Ensure we don't go beyond the period boundaries
            $weekStart = $currentWeekStart->copy();
            $weekEnd = $currentWeekEnd->gt($endDate) ? $endDate->copy() : $currentWeekEnd->copy();
    
            // Get expenses for this week
            $weekExpenses = Expense::where('user_id', $userId)
                ->whereBetween('date', [
                    $weekStart->startOfDay(), 
                    $weekEnd->endOfDay()
                ])
                ->get()
                ->sum('amount');
    
            // Add to breakdown array
            $weeklyBreakdown[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'week_range' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'total_expense' => $weekExpenses
            ];
    
            // Move to the next week
            $currentWeekStart = $currentWeekEnd->copy()->addDay();
    
            // Check if we're past the end of our period
            if ($currentWeekStart->gt($endDate)) {
                break;
            }
        }
    
        $topExpenses = $recentExpenses
            ->groupBy('description')
            ->map(fn($group) => [
                'description'   => $group->first()->description,
                'frequency'     => $group->count(),
                'total_amount'  => $group->sum('amount'),
            ])
            ->sortByDesc('total_amount')
            ->take(5);
    
        return view('dashboard', compact(
            'totalIncome', 'totalExpenses', 'netSavings', 'monthlyBudget', 
            'incomePercentageChange', 'expensesPercentageChange', 
            'recentTransactions', 'labels', 'data', 'remainingBudget', 'budgetsData', 
            'selectedMonth', 'budgetPercentageChange', 'months', 'monthlyBudgets', 
            'monthlyExpenses', 'averageWeeklySpent', 'weeklyBreakdown', 'topExpenses',
            'finalData', 'categoryAverages', 'averagesData'
        ));
    }
/**
 * Predict future expenses based on historical data.
 *
 * @param int $userId
 * @return array
 */


private function getChartLabels($month)
{
    // Get the total number of days in the selected month
    $daysInMonth = Carbon::parse($month)->daysInMonth;

    // Create an array of day numbers (e.g., 1, 2, 3, ..., 30)
    $labels = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $labels[] = $day;
    }

    return $labels;
}
private function getChartData($month)
{
    // Get the total number of days in the selected month
    $daysInMonth = Carbon::parse($month)->daysInMonth;

    // Initialize an array to store expense totals for each day
    $data = array_fill(0, $daysInMonth, []);

    // Fetch all expenses for the selected month
    $expenses = Expense::with('category') // Assuming a relationship named 'category'
        ->where('user_id', auth()->id())
        ->whereMonth('date', Carbon::parse($month)->month)
        ->whereYear('date', Carbon::parse($month)->year)
        ->get();

    // Loop through the expenses and sum the amounts by day
    foreach ($expenses as $expense) {
        $day = Carbon::parse($expense->date)->day;
        // Store the amount under the category name
        $data[$day - 1][$expense->category->name] = ($data[$day - 1][$expense->category->name] ?? 0) + $expense->amount;
    }

    return $data; // Now $data will be an array of arrays with amounts categorized by day
}



}