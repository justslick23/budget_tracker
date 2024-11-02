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
    public function index()
{
    $userId = auth()->id();
    $currentYear = Carbon::now()->year; // Current year
    $currentMonth = Carbon::now(); // Current month and date

    // Create an array for month names
    $months = [];
    for ($i = 1; $i <= 12; $i++) {
        $months[] = Carbon::createFromDate($currentYear, $i, 1)->format('F'); // Month names
    }

    // Current month income and expenses
    $totalIncome = $this->fetchTotalIncome($userId, $currentMonth);
    $totalExpenses = $this->fetchTotalExpenses($userId, $currentMonth);

    // Previous month income and expenses
    $previousMonth = $currentMonth->copy()->subMonth();
    $previousIncome = $this->fetchTotalIncome($userId, $previousMonth);
    $previousExpenses = $this->fetchTotalExpenses($userId, $previousMonth);

    // Calculate percentage change
    $incomePercentageChange = $this->calculatePercentageChange($totalIncome, $previousIncome);
    $expensesPercentageChange = $this->calculatePercentageChange($totalExpenses, $previousExpenses);

    // Monthly budget
    $currentMonthNumeric = $currentMonth->format('m'); // Current month numeric
    $monthlyBudget = Budget::where('user_id', $userId)
        ->where('month', $currentMonthNumeric)
        ->where('year', $currentYear)
        ->first(); 

    $budgetAmount = $monthlyBudget ? $monthlyBudget->amount : 0; 
    $remainingBudget = $budgetAmount - $totalExpenses;

    // Recent transactions
    $recentIncome = $this->getRecentIncome($userId, $currentMonth->startOfMonth(), $currentMonth->endOfMonth());
    $recentExpenses = $this->getRecentExpenses($userId, $currentMonth->startOfMonth(), $currentMonth->endOfMonth());
    $recentTransactions = $recentIncome->merge($recentExpenses)->sortByDesc('date');

   // Monthly report data
// Initialize arrays to hold income, expenses, budgets, and remaining budgets for each month
$monthlyIncome = [];
$monthlyExpenses = [];
$monthlyBudgets = [];
$remainingBudgets = [];

// Loop through each month to fetch data
foreach ($months as $index => $monthName) {
    $monthNumeric = $index + 1; // Get month as numeric (1 to 12)

    // Fetch total income and expenses for the month
    $totalIncome = $this->fetchTotalIncome($userId, Carbon::createFromDate($currentYear, $monthNumeric, 1));
    \Log::info("Total Income for $monthNumeric: $totalIncome");

    $totalExpenses = $this->fetchTotalExpenses($userId, Carbon::createFromDate($currentYear, $monthNumeric, 1));
    \Log::info("Total Expenses for $monthNumeric: $totalExpenses");

    // Monthly budget
    $monthlyBudget = Budget::where('user_id', $userId)
        ->where('month', $monthNumeric)
        ->where('year', $currentYear)
        ->sum('amount');

    $budgetAmount = $monthlyBudget ? $monthlyBudget->amount : 0;
    \Log::info("Budget for month $monthNumeric: $budgetAmount");

    $remainingBudget = $budgetAmount - $totalExpenses;

    // Store the results
    $monthlyIncome[] = $totalIncome;
    $monthlyExpenses[] = $totalExpenses;
    $monthlyBudgets[] = $budgetAmount;
    $remainingBudgets[] = $remainingBudget;
}

    return view('dashboard', [
        'totalIncome' => $totalIncome,
        'totalExpenses' => $totalExpenses,
        'monthlyBudget' => $budgetAmount,
        'remainingBudget' => $remainingBudget,
        'recentTransactions' => $recentTransactions,
        'labels' => $this->getChartLabels($currentMonth),
        'data' => $this->getChartData($currentMonth),
        'incomePercentageChange' => $incomePercentageChange,
        'expensesPercentageChange' => $expensesPercentageChange,
        'months' => $months,
        'incomeData' => $monthlyIncome, // Correctly assigning monthly data
        'expensesData' => $monthlyExpenses, // Correctly assigning monthly data
        'budgetsData' => $monthlyBudgets, // Correctly assigning monthly data
        'remainingBudgetsData' => $remainingBudgets, // Correctly assigning monthly data
        'year' => $currentYear,
    ]);
}




public function filter(Request $request)
{
    $month = $request->input('month', Carbon::now()->format('Y-m'));
    $userId = auth()->id();
    $currentMonthNumeric = Carbon::parse($month)->format('m'); 
    $yearNumeric = Carbon::parse($month)->format('Y'); 

    // Current month income and expenses
    $totalIncome = $this->fetchTotalIncome($userId, Carbon::parse($month));
    $totalExpenses = $this->fetchTotalExpenses($userId, Carbon::parse($month));

    // Previous month income and expenses
    $previousMonth = Carbon::parse($month)->subMonth();
    $previousIncome = $this->fetchTotalIncome($userId, $previousMonth);
    $previousExpenses = $this->fetchTotalExpenses($userId, $previousMonth);

    // Calculate percentage change
    $incomePercentageChange = $this->calculatePercentageChange($totalIncome, $previousIncome);
    $expensesPercentageChange = $this->calculatePercentageChange($totalExpenses, $previousExpenses);

    // Monthly budget
    $monthlyBudget = Budget::where('user_id', $userId)
        ->where('month', $currentMonthNumeric)
        ->where('year', $yearNumeric)
        ->first(); 

    $budgetAmount = $monthlyBudget ? $monthlyBudget->amount : 0; 
    $remainingBudget = $budgetAmount - $totalExpenses;

    // Budgets data for the chart
    $budgetsData = []; // Initialize the budgets data array

    if ($monthlyBudget) {
        $budgetsData = [$budgetAmount]; // Example: You can customize this to match your requirements
    } else {
        $budgetsData = [0]; // If there's no budget, set it to zero
    }

    // Recent transactions
    $recentIncome = $this->getRecentIncome($userId, Carbon::parse($month)->startOfMonth(), Carbon::parse($month)->endOfMonth());
    $recentExpenses = $this->getRecentExpenses($userId, Carbon::parse($month)->startOfMonth(), Carbon::parse($month)->endOfMonth());
    $recentTransactions = $recentIncome->merge($recentExpenses)->sortByDesc('date');

    return view('dashboard', [
        'totalIncome' => $totalIncome,
        'totalExpenses' => $totalExpenses,
        'monthlyBudget' => $budgetAmount,
        'remainingBudget' => $remainingBudget,
        'recentTransactions' => $recentTransactions,
        'labels' => $this->getChartLabels($month),
        'data' => $this->getChartData($month),
        'budgetsData' => $budgetsData, // Pass the budgets data to the view
        'incomePercentageChange' => $incomePercentageChange,
        'expensesPercentageChange' => $expensesPercentageChange
    ]);
}


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

private function fetchTotalIncome($userId, $month)
{
    return Income::where('user_id', $userId)
        ->whereMonth('date', $month->month)
        ->whereYear('date', $month->year)
        ->sum('amount');
}

private function fetchTotalExpenses($userId, $month)
{
    return Expense::where('user_id', $userId)
        ->whereMonth('date', $month->month)
        ->whereYear('date', $month->year)
        ->sum('amount');
}

private function calculatePercentageChange($current, $previous)
{
    return $previous != 0 ? (($current - $previous) / $previous) * 100 : 0;
}

private function getRecentIncome($userId, $startDate, $endDate)
{
    return Income::where('user_id', $userId)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date', 'desc')
        ->get()->map(function ($income) {
            $income->type = 'Income';
            return $income;
        });
}

private function getRecentExpenses($userId, $startDate, $endDate)
{
    return Expense::where('user_id', $userId)
        ->whereBetween('date', [$startDate, $endDate])
        ->orderBy('date', 'desc')
        ->get()->map(function ($expense) {
            $expense->type = 'Expense';
            return $expense;
        });
}

private function groupExpensesByCategory($recentExpenses, $userId, $currentMonthNumeric)
{
    return $recentExpenses->groupBy('category_id')->map(function ($group) use ($userId, $currentMonthNumeric) {
        $categoryId = $group->first()->category_id; 
        $totalExpense = $group->sum('amount');

        if ($totalExpense > 0) {
            $category = Category::find($categoryId);
            if ($category) {
                $budgetForCategory = Budget::where('user_id', $userId)
                    ->where('category_id', $categoryId)
                    ->where('month', $currentMonthNumeric)
                    ->sum('amount');

                return [
                    'name' => $category->name,
                    'expense' => $totalExpense,
                    'budget' => $budgetForCategory
                ];
            }
        }
        return null; 
    })->filter(); 
}

private function prepareFinalData($allCategories, $groupedExpenses)
{
    return $allCategories->map(function ($category) use ($groupedExpenses) {
        $expenseData = $groupedExpenses->firstWhere('name', $category->name);
        return [
            'name' => $category->name,
            'expense' => $expenseData['expense'] ?? 0,
            'budget' => $expenseData['budget'] ?? 0
        ];
    });
}


private function getChartData($month)
{
    $daysInMonth = Carbon::parse($month)->daysInMonth;
    $data = array_fill(0, $daysInMonth, []);
    
    $expenses = Expense::with('category')
        ->where('user_id', auth()->id())
        ->whereMonth('date', Carbon::parse($month)->month)
        ->whereYear('date', Carbon::parse($month)->year)
        ->get();

    foreach ($expenses as $expense) {
        $day = Carbon::parse($expense->date)->day;
        $data[$day - 1][$expense->category->name] = ($data[$day - 1][$expense->category->name] ?? 0) + $expense->amount;
    }

    return $data; 
}




}
