<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
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
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthNumeric = Carbon::parse($currentMonth)->format('m'); 
        $currentDate = now();
        
        $userId = auth()->id();
        $startDate = $currentDate->copy()->subMonth()->day(31)->startOfDay();
        $endDate = $currentDate->copy()->day(31)->endOfDay();

        $totalIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $monthlyBudget = Budget::where('user_id', $userId)
            ->where('month', $currentMonthNumeric)
            ->sum('amount');

        $previousStartDate = $currentDate->copy()->subMonth()->day(26)->startOfDay();
        $previousEndDate = $currentDate->copy()->subMonth()->day(25)->endOfDay();

        $previousTotalIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->sum('amount');

        $previousTotalExpenses = Expense::where('user_id', $userId)
            ->whereBetween('date', [$previousStartDate, $previousEndDate])
            ->sum('amount');

        $incomePercentageChange = $previousTotalIncome > 0 ? (($totalIncome - $previousTotalIncome) / $previousTotalIncome) * 100 : 0;
        $expensesPercentageChange = $previousTotalExpenses > 0 ? (($totalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100 : 0;

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

        $recentTransactions = $recentExpenses->merge($recentIncome)->sortByDesc('date');
        $netSavings = $monthlyBudget - $totalExpenses;

        $groupedExpenses = $recentExpenses->groupBy('category_id')->map(function ($group) {
            return [
                'name' => $group->first()->category->name,
                'amount' => $group->sum('amount') 
            ]; 
        });

        $remainingBudget = $monthlyBudget - $totalExpenses;

        // Get predictions from OpenAIService
 // Step 1: Fetch historical expenses for the logged-in user
 $historicalExpenses = Expense::where('user_id', $userId)
 ->orderBy('date', 'asc')
 ->get();

// Prepare the expenses data for prediction
$expensesArray = $historicalExpenses->map(function ($expense) {
 return [
     'date' => $expense->date->format('Y-m-d'), // Format date as needed
     'amount' => $expense->amount,
     'category' =>$expense->category->name,
     'description' =>$expense->description,
     // Add any other relevant fields here if needed
 ];
})->toArray();


// Step 2: Use the OpenAIService to predict expenses
$predictionsString = $this->openAIService->predictExpenses($expensesArray)['predicted_expenses'] ?? 'No predictions available.';

// Parse the predictions into a structured array
$predictionsArray = [];
if ($predictionsString !== 'No predictions available.') {
    preg_match_all('/([A-Za-z]+):\s+-?\s*([M\d,.]+)/', $predictionsString, $matches);
    foreach ($matches[1] as $index => $category) {
        $predictionsArray[$category] = str_replace('M', '', $matches[2][$index]); // Remove 'M' and keep the numeric value
    }
}        // Prepare data for the chart
        $labels = $groupedExpenses->pluck('name'); 
        $data = $groupedExpenses->pluck('amount'); 

        return view('dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'netSavings',
            'monthlyBudget', 
            'incomePercentageChange', 
            'expensesPercentageChange', 
            'recentTransactions', 
            'labels', 
            'data', 
            'remainingBudget',
            'predictionsArray' // Include predictions
        ));
    }
/**
 * Predict future expenses based on historical data.
 *
 * @param int $userId
 * @return array
 */
private function predictExpenses($userId)
{
    // Fetch historical expenses for the logged-in user
    $historicalExpenses = Expense::where('user_id', $userId)
        ->orderBy('date', 'asc')
        ->get(['amount', 'date']);

    // Prepare data for prediction
    $samples = [];
    foreach ($historicalExpenses as $expense) {
        // Using the day of the year as a feature and the amount as a target
        $samples[] = [
            Carbon::parse($expense->date)->dayOfYear, // Day of the year (1-365)
            $expense->amount, // Historical expense amount
        ];
    }

    // Load the trained machine learning model
    $modelManager = new ModelManager();
    $model = $modelManager->restoreFromFile('model/lr_model.phpml'); // Adjust the path as necessary

    // Prepare an array to hold predictions for the next 30 days
    $predictions = [];

    // Get the last day of the year based on historical data
    $lastDayOfYear = max(array_column($samples, 0)); // Find the maximum day of the year from historical data

    // Make predictions (e.g., for the next 30 days)
    for ($i = 1; $i <= 30; $i++) {
        // Increment the day of the year based on the last recorded day
        $predictedDay = $lastDayOfYear + $i;
        
        // Predict future expense for the predicted day
        $predictedAmount = $model->predict([[$predictedDay, 0]]); // Adjust input as necessary (e.g., 0 for no previous expense)

        $predictions[] = [
            'day' => $predictedDay,
            'amount' => $predictedAmount[0], // Assuming your model returns an array
        ];
    }

    return $predictions; // Return the predictions for the next 30 days
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
