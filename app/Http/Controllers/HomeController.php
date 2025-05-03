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
        $currentMonthNumeric = Carbon::parse($selectedMonth)->format('m');
    
     
        $currentDate = Carbon::parse($selectedMonth); // Parse the date for selected month
        $userId = auth()->id();
      // Define custom month period (27th of previous month to 26th of current month)
        $startDate = $currentDate->copy()->subMonth()->setDay(27)->startOfDay();
        $endDate = $currentDate->copy()->setDay(26)->endOfDay();
     
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
            ->where('year', $currentYearNumeric) // Add year to the query
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
            ->sum('amount'); // Simplified sum syntax
    
        $previousTotalExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->get()
            ->sum('amount'); // Fixed - was using 'income' variable name
    
        // Calculate percentage change in income and expenses
        $incomePercentageChange = $previousTotalIncome > 0 ? (($totalIncome - $previousTotalIncome) / $previousTotalIncome) * 100 : 0;
        $expensesPercentageChange = $previousTotalExpenses > 0 ? (($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100 : 0;
    
        // Combine the recent transactions and sort by date
        $recentTransactions = $recentExpenses->merge($recentIncome)->sortByDesc('date');
    
        $netSavings = $totalIncome - $totalExpenses; // Fixed - was using budget instead of income
    
        // Fetch all categories for comparison
        $allCategories = Category::all();
    
        // Group recent expenses by category and calculate total expenses and budgets
        $groupedExpenses = $recentExpenses->groupBy('category_id')->map(function ($group) use ($userId, $currentMonthNumeric, $currentYearNumeric) {
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
                        ->where('year', $currentYearNumeric) // Add year to query
                        ->first(); // Use first() to ensure you get the single record
    
                    return [
                        'name' => $category->name, // Category name
                        'expense' => $totalExpense, // Total expenses for the category
                        'budget' => $budgetForCategory ? $budgetForCategory->amount : 0 // Return 0 if no budget is found
                    ];
                }
            }
        
        })->filter(); // Use filter to remove any null results
    
        // Prepare final data by including all categories, even those with no expenses
        $finalData = $allCategories->map(function ($category) use ($groupedExpenses) {
            $expenseData = $groupedExpenses->firstWhere('name', $category->name);
    
            return [
                'name' => $category->name,
                'expense' => $expenseData['expense'] ?? 0,
                'budget' => $expenseData['budget'] ?? 0
            ];
        });
    
        // Calculate the remaining budget
        $remainingBudget = $monthlyBudget - $totalExpenses;
    
        // Labels and data for charts
        $labels = $groupedExpenses->pluck('name');
        $data = $groupedExpenses->pluck('expense');
        $budgetsData = $groupedExpenses->pluck('budget'); // Budgets for each category
    
        // Monthly Budget Percentage Change
        $previousTotalBudget = Budget::where('user_id', $userId)
            ->where('month', $previousMonthNumeric)
            ->where('year', $previousYearNumeric) // Add year to query
            ->get()
            ->sum('amount'); // Simplified sum syntax
            
        $budgetPercentageChange = $previousTotalBudget > 0 ? (($monthlyBudget - $previousTotalBudget) / $previousTotalBudget) * 100 : 0;
    
        $monthsToShow = $request->input('filter', 12); // Default to last 12 months
        $start = now()->subMonths($monthsToShow)->startOfMonth();
        $end = now()->endOfMonth();
    
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
                ->sum('amount'); // Simplified sum syntax
    
            $totalExpense = Expense::where('user_id', $userId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->sum('amount'); // Simplified sum syntax
    
            // Store data in arrays
            $months[] = $date->format('M Y'); // Example: "Feb 2024"
            $monthlyBudgets[] = $totalBudget;
            $monthlyExpenses[] = $totalExpense;
        }
        
        // Calculate the number of weeks in the selected month
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
    
        // Difference in weeks (inclusive)
        $numberOfWeeks = ceil($startOfMonth->diffInDays($endOfMonth) / 7);
    
        // Avoid division by zero
        $averageWeeklySpent = $numberOfWeeks > 0 ? $totalExpenses / $numberOfWeeks : 0;
    
        $weeklyBreakdown = [];
        
        // Start from the first day of the month
        $currentWeekStart = $startDate->copy()->startOfWeek();
        
        // If the start of the week is before the start of the month, adjust it
        if ($currentWeekStart->lt($startDate)) {
            $currentWeekStart = $startDate->copy();
        }
        
        // Calculate the end of the first week
        $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();
        
        // If the end of the week is after the end of the month, adjust it
        if ($currentWeekEnd->gt($endDate)) {
            $currentWeekEnd = $endDate->copy();
        }
    
        // Loop through each week in the month
        while ($currentWeekStart->lte($endDate)) {
            // Ensure we don't go beyond the month boundaries
            $weekStart = max($currentWeekStart, $startDate);
            $weekEnd = min($currentWeekEnd, $endDate);
    
            // Get expenses for this week
            $weekExpenses = Expense::where('user_id', $userId)
                ->whereBetween('date', [
                    $weekStart->copy()->startOfDay(), 
                    $weekEnd->copy()->endOfDay()
                ])
                ->get() // Make sure to get the data first
                ->sum('amount');
    
            // Add to breakdown array
            $weeklyBreakdown[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'week_range' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'total_expense' => $weekExpenses
            ];
    
            // Move to the next week - CORRECTED
            $currentWeekStart = $currentWeekEnd->copy()->addDay();
            $currentWeekEnd = $currentWeekStart->copy()->addDays(6); // Go 6 days ahead (for a 7-day week)
    
            // Check if we're past the end of the month
            if ($currentWeekStart->gt($endDate)) {
                break; // Exit the loop if we've gone past the month
            }
        }
    
        $recurringExpenses = $recentExpenses
        ->groupBy('description')
        ->filter(function ($group) {
            return $group->count() > 1; // More than one expense with same description
        })
        ->map(function ($group) {
            return [
                'description' => $group->first()->description,
                'frequency' => $group->count(),
                'total_amount' => $group->sum('amount')
            ];
        })
        ->sortByDesc('total_amount')
        ->take(5); // Top 5 recurring expenses
    
        return view('dashboard', compact(
            'totalIncome', 'totalExpenses', 'netSavings', 'monthlyBudget', 
            'incomePercentageChange', 'expensesPercentageChange', 
            'recentTransactions', 'labels', 'data', 'remainingBudget', 'budgetsData', 'selectedMonth', 'budgetPercentageChange', 'months', 'monthlyBudgets', 'monthlyExpenses', 'averageWeeklySpent', 'weeklyBreakdown', 'recurringExpenses'
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