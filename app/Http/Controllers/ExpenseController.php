<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\Budget;

use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $userId = auth()->id(); // Get the currently authenticated user's ID
        $expenses = Expense::with('category')->where('user_id', $userId)->get(); // Eager load category
    
        return view('expenses.index', compact('expenses'));
    }
    

    public function create()
    {
        $categories = Category::where('user_id', auth()->user()->id)->get(); // Fetch all categories

        return view('expenses.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);
    
        // Add user_id to the request data
        $requestData = $request->all();
        $requestData['user_id'] = auth()->id(); // Set the user_id from the authenticated user
    
        // Create the expense
        $expense = Expense::create($requestData);
    
        // Get the date of the expense
        $expenseDate = \Carbon\Carbon::parse($expense->date);
        $month = $expenseDate->month;
        $year = $expenseDate->year;
    
        // Determine the correct budget month
        if ($expenseDate->day >= 28) {
            // If the date is from 28th to end of the month, adjust to next month
            $month = ($month % 12) + 1; // Increment month
            $year += ($month === 1) ? 1 : 0; // Increment year if we rolled over to January
        }
    
        // Update the budget for the determined month
        $budget = Budget::where('user_id', auth()->id())
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    
        if ($budget) {
            $budget->spent += $expense->amount; // Increment the amount spent
            $budget->save(); // Save the budget
        } 
    
        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }
    
    // Add edit, update, and delete methods as needed
}
