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
        
        // Parse the selected month 
        $currentDate = Carbon::parse($selectedMonth);
        $userId = auth()->id();
        
        // Define custom month period (27th of previous month to 26th of current month)
        $startDate = $currentDate->copy()->subMonth()->setDay(27)->startOfDay();
        $endDate = $currentDate->copy()->setDay(26)->endOfDay();
        
        // Get the numeric month and year for budget queries
        // Since we're using a custom period, we'll use the month of the selected date
        $currentMonthNumeric = (int) $currentDate->format('m');
        $currentYearNumeric = (int) $currentDate->format('Y');
    
        // Fetch recent expenses and income for the custom period
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
    
        // The rest of your code would follow the same pattern, using the custom period
        // for any date-based calculations
        
        // For the previous period, use 27th of two months ago to 26th of last month
        $previousStartDate = $currentDate->copy()->subMonths(2)->setDay(27)->startOfDay();
        $previousEndDate = $currentDate->copy()->subMonth()->setDay(26)->endOfDay();
        $previousMonthNumeric = (int) $currentDate->copy()->subMonth()->format('m');
        $previousYearNumeric = (int) $currentDate->copy()->subMonth()->format('Y');
        
        // Weekly breakdown would also need adjustment to align with custom period
        $weeklyBreakdown = [];
        
        // Start from the first day of the custom period
        $currentWeekStart = $startDate->copy()->startOfWeek();
        
        // If the start of the week is before the start of the custom period, adjust it
        if ($currentWeekStart->lt($startDate)) {
            $currentWeekStart = $startDate->copy();
        }
        
        // Calculate the end of the first week
        $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();
        
        // If the end of the week is after the end of the custom period, adjust it
        if ($currentWeekEnd->gt($endDate)) {
            $currentWeekEnd = $endDate->copy();
        }
    
        // Loop through each week in the custom period
        while ($currentWeekStart->lte($endDate)) {
            // Ensure we don't go beyond the period boundaries
            $weekStart = max($currentWeekStart, $startDate);
            $weekEnd = min($currentWeekEnd, $endDate);
    
            // Get expenses for this week
            $weekExpenses = Expense::where('user_id', $userId)
                ->whereBetween('date', [
                    $weekStart->copy()->startOfDay(), 
                    $weekEnd->copy()->endOfDay()
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
            $currentWeekEnd = $currentWeekStart->copy()->addDays(6);
    
            // Check if we're past the end of the period
            if ($currentWeekStart->gt($endDate)) {
                break;
            }
        }
        
        // Display the custom period range in the UI for clarity
        $displayPeriod = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
        
        // Pass the custom period information to the view
        return view('dashboard', compact(
            'totalIncome', 'totalExpenses', 'netSavings', 'monthlyBudget',
            'incomePercentageChange', 'expensesPercentageChange',
            'recentTransactions', 'labels', 'data', 'remainingBudget', 'budgetsData', 
            'selectedMonth', 'budgetPercentageChange', 'months', 'monthlyBudgets', 
            'monthlyExpenses', 'averageWeeklySpent', 'weeklyBreakdown', 'recurringExpenses',
            'displayPeriod' // New variable to show the custom period range
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