<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Expense;

use Illuminate\Support\Facades\Crypt;

class BudgetController extends Controller
{

    public function index()
    {
 // Fetch the allocated amounts from the budgets table
 $budgets = Budget::where('user_id', auth()->id())
 ->get(['id', 'year', 'month', 'category_id', 'amount']); // Allocated Amount

// Fetch the expenses, including category name from the categories table
$expenses = Expense::where('expenses.user_id', auth()->id()) // Specify the table in WHERE clause
   ->join('categories', 'expenses.category_id', '=', 'categories.id') // Join with categories
   ->get(['expenses.date', 'expenses.amount', 'expenses.category_id', 'categories.name as category']); // Get category name

// Prepare an array to store the final budget summary
$budgetSummary = [];

// Loop through each budget entry
foreach ($budgets as $budget) {
// Initialize the data for each month/category
$key = $budget->year . '-' . $budget->month . '-' . $budget->category_id;

// Set the allocated amount
$budgetSummary[$key] = [
    'id' => $budget->id,  // Store the budget id

'year' => $budget->year,
'month' => $budget->month,
'category_id' => $budget->category_id,
'category' => $budget->category->name,  // Fetch category name
'allocated_amount' => $budget->amount,
'total_spent' => 0, // Default to 0, we'll sum this up later
'remaining_balance' => 0, // We'll calculate this later
];
}

// Loop through each expense entry
foreach ($expenses as $expense) {
// Access the decrypted amount via the getter
$decryptedAmount = $expense->amount;

// Extract the year, month, and category_id from the expense
$year = $expense->date->year;
$month = $expense->date->month;
$categoryId = $expense->category_id;

// Create the unique key for the category/year/month
$key = $year . '-' . $month . '-' . $categoryId;

// If the key exists in the budget summary, add the amount to total_spent
if (isset($budgetSummary[$key])) {
$budgetSummary[$key]['total_spent'] += $decryptedAmount;
}
}

// Calculate remaining balance for each category and month
foreach ($budgetSummary as $key => $data) {
$remainingBalance = $data['allocated_amount'] - $data['total_spent'];
$budgetSummary[$key]['remaining_balance'] = number_format($remainingBalance, 2); // Format remaining balance
$budgetSummary[$key]['total_spent'] = number_format($data['total_spent'], 2); // Format total spent
$budgetSummary[$key]['allocated_amount'] = number_format($data['allocated_amount'], 2); // Format allocated amount
}


        return view('budgets.index', compact('budgetSummary'));
    }

    public function edit(Budget $budget)
    {
        $categories = Category::where('user_id', auth()->user()->id)->get();
        return view('budgets.edit', compact('budget', 'categories')); // Return edit view with category
    }

    public function update(Request $request, $id)
{
    // Validate the incoming request
    $request->validate([
        'year' => 'required|integer|min:2000|max:2100',
        'month' => 'required|integer|min:1|max:12',
        'category_id' => 'required|exists:categories,id',
        'amount' => 'required|numeric|min:0',
    ]);

    // Find the budget by ID
    $budget = Budget::findOrFail($id);

    // Update the budget with validated data
    $budget->update([
        'year' => $request->input('year'),
        'month' => $request->input('month'),
        'category_id' => $request->input('category_id'),
        'amount' => $request->input('amount'),
    ]);

    // Redirect back with a success message
    return redirect()->route('budgets.index')->with('success', 'Budget updated successfully.');
}



    public function create()
    {
        $userID = auth()->user()->id;
        $categories = Category::where('user_id', $userID)->get();
        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric',
        ]);

    
        // Create budget and associate it with the authenticated user
        Budget::create([
            'year' => $request->year,
            'month' => $request->month,
            'amount' => $request->amount,
            'user_id' => auth()->id(), // Assuming you're using authentication
            'category_id' => $request->category_id,

        ]);
    
        return redirect()->route('budgets.index')->with('success', 'Budget created successfully.');
    }

    public function destroy($id)
{
    // Find the budget by ID and ensure it belongs to the authenticated user
    $budget = Budget::where('id', $id)->where('user_id', auth()->id())->first();

    if ($budget) {
        $budget->delete(); // Delete the budget
        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully.');
    } else {
        return redirect()->route('budgets.index')->with('error', 'Budget not found or you do not have permission to delete it.');
    }
}

    
}
