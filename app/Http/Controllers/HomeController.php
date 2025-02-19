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
        $startDate = $currentDate->copy()->startOfMonth()->startOfDay(); // Start of the selected month
        $endDate = $currentDate->copy()->endOfMonth()->endOfDay(); // End of the selected month
     
        $currentMonthNumeric = (int) $currentDate->format('m'); // Convert to integer explicitly

       // Fetch the total income for the selected month

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
    ->get()
    ->sum(function ($budget) {
        return $budget->amount;  // The getter will automatically decrypt the amount
    });
    
   
        // Fetch data for the previous month for comparison
        $previousStartDate = $currentDate->copy()->subMonth()->startOfMonth()->startOfDay();
        $previousEndDate = $currentDate->copy()->subMonth()->endOfMonth()->endOfDay();
    
        $previousTotalIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])->get()
            ->sum(function ($income) {
                return $income->amount;  // The getter will automatically decrypt the amount
            });
    
        $previousTotalExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])->get()
              ->sum(function ($income) {
                return $income->amount;  // The getter will automatically decrypt the amount
            });
    
        // Calculate percentage change in income and expenses
        $incomePercentageChange = $previousTotalIncome > 0 ? (($totalIncome - $previousTotalIncome) / $previousTotalIncome) * 100 : 0;
        $expensesPercentageChange = $previousTotalExpenses > 0 ? (($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100 : 0;
    
    

    
    
        // Combine the recent transactions and sort by date
        $recentTransactions = $recentExpenses->merge($recentIncome)->sortByDesc('date');
    
        $netSavings = $monthlyBudget - $totalExpenses;
    
        // Fetch all categories for comparison
        $allCategories = Category::all();
    
        // Group recent expenses by category and calculate total expenses and budgets
        $groupedExpenses = $recentExpenses->groupBy('category_id')->map(function ($group) use ($userId, $currentMonthNumeric) {
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

        // Total Income Percentage Change
       
$incomePercentageChange = $previousTotalIncome > 0 ? (($totalIncome - $previousTotalIncome) / $previousTotalIncome) * 100 : 0;

// Total Expenses Percentage Change

$expensesPercentageChange = $previousTotalExpenses > 0 ? (($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100 : 0;

$previousMonthNumeric = $currentDate->copy()->subMonth()->month;
// Monthly Budget Percentage Change
$previousTotalBudget = Budget::where('user_id', $userId)
->where('month', $previousMonthNumeric)->get() // Set the previous month
->sum(function ($budget) {
    return $budget->amount;  // The getter will automatically decrypt the amount
});
$budgetPercentageChange = $previousTotalBudget > 0 ? (($monthlyBudget - $previousTotalBudget) / $previousTotalBudget) * 100 : 0;

$currentYear = now()->year;

// Initialize arrays for storing data
$months = [];
$monthlyBudgets = [];
$monthlyExpenses = [];

// Loop through each month from January to the current month
for ($month = 1; $month <= now()->month; $month++) {
    $startDate = Carbon::createFromDate($currentYear, $month, 1)->startOfMonth();
    $endDate = Carbon::createFromDate($currentYear, $month, 1)->endOfMonth();

    // Get total budget for the month
    $totalBudget = Budget::where('user_id', $userId)
        ->where('month', $month)->get()
        ->sum(function ($budget) {
            return $budget->amount;  // The getter will automatically decrypt the amount
        });

    // Get total expenses for the month
    $totalExpense = Expense::where('user_id', $userId)
        ->whereBetween('date', [$startDate, $endDate])->get()
        ->sum(function ($expense) {
            return $expense->amount;  // The getter will automatically decrypt the amount
        });

    // Store data in arrays
    $months[] = $startDate->format('M'); // Month name
    $monthlyBudgets[] = $totalBudget;
    $monthlyExpenses[] = $totalExpense;
} 
        return view('dashboard', compact(
            'totalIncome', 'totalExpenses', 'netSavings', 'monthlyBudget', 
            'incomePercentageChange', 'expensesPercentageChange', 
            'recentTransactions', 'labels', 'data', 'remainingBudget', 'budgetsData', 'selectedMonth', 'budgetPercentageChange', 'months', 'monthlyBudgets', 'monthlyExpenses',
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