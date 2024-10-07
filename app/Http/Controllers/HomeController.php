<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
use Carbon\Carbon;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
{
    // Get the current month and date
    $currentMonth = Carbon::now()->format('Y-m');
    $currentMonthNumeric = Carbon::parse($currentMonth)->format('m'); // e.g., '10'

    $currentDate = now();
    
    // Get the ID of the currently logged-in user
    $userId = auth()->id();

    // Calculate the start and end dates for the desired range
    $startDate = $currentDate->copy()->subMonth()->day(31)->startOfDay(); // From the 26th of the previous month
    $endDate = $currentDate->copy()->day(31)->endOfDay(); // To the 25th of the current month

    // Get total income within the specified date range for the logged-in user
    $totalIncome = Income::where('user_id', $userId)
        ->whereBetween('date', [$startDate, $endDate])
        ->sum('amount');

    // Get total expenses within the same date range for the logged-in user
    $totalExpenses = Expense::where('user_id', $userId)
        ->whereBetween('date', [$startDate, $endDate])
        ->sum('amount');

    // Calculate net savings

    // Get monthly budget for the current month for the logged-in user
    $monthlyBudget = Budget::where('user_id', $userId)
        ->where('month', $currentMonthNumeric)
        ->sum('amount');


    // Calculate total income and expenses for the previous month (from the 26th to the 25th)
    $previousStartDate = $currentDate->copy()->subMonth()->day(26)->startOfDay();
    $previousEndDate = $currentDate->copy()->subMonth()->day(25)->endOfDay();

    $previousTotalIncome = Income::where('user_id', $userId)
        ->whereBetween('date', [$previousStartDate, $previousEndDate])
        ->sum('amount');

    $previousTotalExpenses = Expense::where('user_id', $userId)
        ->whereBetween('date', [$previousStartDate, $previousEndDate])
        ->sum('amount');

    // Calculate percentage difference from last month
    $incomePercentageChange = $previousTotalIncome > 0 ? (($totalIncome - $previousTotalIncome) / $previousTotalIncome) * 100 : 0;
    $expensesPercentageChange = $previousTotalExpenses > 0 ? (($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100 : 0;

    // Get recent transactions (income and expenses) for the logged-in user
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

    // Merge recent transactions and sort them by date
    $recentTransactions = $recentExpenses->merge($recentIncome)->sortByDesc('date');

    // Group recent expenses by category and sum amounts
    $netSavings = $monthlyBudget - $totalExpenses;


    $recentExpenses = Expense::where('user_id', $userId)
    ->whereBetween('date', [$startDate, $endDate])
    ->orderBy('date', 'desc')
    ->get()
    ->map(function ($expense) {
        $expense->type = 'Expense';
        return $expense;
    });

// Group expenses by category and sum amounts
$groupedExpenses = $recentExpenses->groupBy('category_id')->map(function ($group) {
    return [
        'name' => $group->first()->category->name, // Assuming there's a relationship named 'category'
        'amount' => $group->sum('amount') // Sum amounts for each category
    ]; 
});


$remainingBudget = $monthlyBudget - $totalExpenses;

    // Prepare data for the chart
    $labels = $groupedExpenses->pluck('name'); // Get category names
    $data = $groupedExpenses->pluck('amount'); // Get corresponding summed amounts
    return view('dashboard', compact(
        'totalIncome',
        'totalExpenses',
        'netSavings',
        'monthlyBudget', // Include monthly budget
        'incomePercentageChange', // Include income percentage change
        'expensesPercentageChange', // Include expenses percentage change
        'recentTransactions', 
        'labels', 
        'data', 'remainingBudget'
    ));
}

public function filter(Request $request)
{
    $month = $request->input('month', Carbon::now()->format('Y-m'));
    $currentMonthNumeric = Carbon::parse($month)->format('m'); // e.g., '10'
    $yearNumeric = Carbon::parse($month)->format('Y'); // e.g., '10'

    // Current month income and expenses
    $totalIncome = Income::where('user_id', auth()->id())
        ->whereMonth('date', Carbon::parse($month)->month)
        ->whereYear('date', Carbon::parse($month)->year)
        ->sum('amount');

    $totalExpenses = Expense::where('user_id', auth()->id())
        ->whereMonth('date', Carbon::parse($month)->month)
        ->whereYear('date', Carbon::parse($month)->year)
        ->sum('amount');

    // Previous month income and expenses
    $previousMonth = Carbon::parse($month)->subMonth();

    $previousIncome = Income::where('user_id', auth()->id())
        ->whereMonth('date', $previousMonth->month)
        ->whereYear('date', $previousMonth->year)
        ->sum('amount');

    $previousExpenses = Expense::where('user_id', auth()->id())
        ->whereMonth('date', $previousMonth->month)
        ->whereYear('date', $previousMonth->year)
        ->sum('amount');

    // Calculate percentage change for income and expenses
    $incomePercentageChange = $previousIncome != 0 ? (($totalIncome - $previousIncome) / $previousIncome) * 100 : 0;
    $expensesPercentageChange = $previousExpenses != 0 ? (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 : 0;

    $monthlyBudget = Budget::where('user_id', auth()->id())
    ->where('month',   $currentMonthNumeric )
    ->where('year',  $yearNumeric )
    ->first(); 
    
    $budgetAmount = $monthlyBudget ? $monthlyBudget->amount : 0; // If no budget exists, default to 0

    $remainingBudget = $budgetAmount - $totalExpenses;

    $recentIncome = Income::where('user_id', auth()->id())
        ->whereMonth('date', Carbon::parse($month)->month)
        ->whereYear('date', Carbon::parse($month)->year)
        ->get()->map(function ($income) {
            $income->type = 'Income';
            return $income;
        });

    $recentExpenses = Expense::where('user_id', auth()->id())
        ->whereMonth('date', Carbon::parse($month)->month)
        ->whereYear('date', Carbon::parse($month)->year)
        ->get()->map(function ($expense) {
            $expense->type = 'Expense';
            return $expense;
        });

    $recentTransactions = $recentIncome->merge($recentExpenses)->sortByDesc('date');

    return view('dashboard', [
        'totalIncome' => $totalIncome,
        'totalExpenses' => $totalExpenses,
        'monthlyBudget' => $budgetAmount,
        'remainingBudget' => $remainingBudget,
        'recentTransactions' => $recentTransactions,
        'labels' => $this->getChartLabels($month),
        'data' => $this->getChartData($month),
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
