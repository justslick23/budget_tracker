<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use App\Models\Category;

class BudgetController extends Controller
{

    public function index()
    {
        $budgets = Budget::with('category')
                         ->where('user_id', auth()->id())->get();

        return view('budgets.index', compact('budgets'));
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
