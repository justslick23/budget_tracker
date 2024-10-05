<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{

    public function index()
    {
        $userId = auth()->id(); // Get the authenticated user's ID

        $budgets = Budget::where('user_id', $userId)->paginate(10); // Change 10 to the desired number of items per page
            return view('budgets.index', compact('budgets'));
    }


    public function create()
    {
        return view('budgets.create'); // Create a view for adding a budget
    }

    public function store(Request $request)
    {
        $request->validate([
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
        ]);
    
        return redirect()->route('budgets.index')->with('success', 'Budget created successfully.');
    }
    
}
