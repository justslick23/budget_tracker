<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Notifications\IncomeRecorded;

class IncomeController extends Controller
{
    public function index()
    {
        $userId = auth()->id(); // Get the authenticated user's ID
        $incomes = Income::where('user_id', $userId)->get();
        return view('incomes.index', compact('incomes'));
    }

    public function create()
    {
        $userBudgets = Budget::with('category')
            ->where('user_id', auth()->id())
            ->get();

        return view('incomes.create', compact('userBudgets'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'source' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'include_in_budget' => 'sometimes|boolean',
            'budget_id' => 'nullable|exists:budgets,id', // Validate the budget ID if included
        ]);

        // Create income and associate it with the authenticated user
        $income = Income::create([
            'source' => $request->source,
            'amount' => $request->amount,
            'date' => $request->date,
            'user_id' => auth()->id(), // Assuming you're using authentication
        ]);

        // If the user chose to include the income in the current budget
        if ($request->has('include_in_budget') && $request->include_in_budget) {
            // Ensure budget_id is present before updating the budget
            if ($request->filled('budget_id')) {
                // Increment the budget amount for the selected budget
                Budget::where('user_id', auth()->id())
                    ->where('id', $request->budget_id)
                    ->increment('amount', $income->amount);
            }
        }

        // Notify the user of the recorded income
        auth()->user()->notify(new IncomeRecorded($income->amount, $income->source));

        return redirect()->route('incomes.index')->with('success', 'Income added successfully and budget updated.');
    }

    public function edit($id)
    {
        $income = Income::findOrFail($id); // Fetch the income by ID
        $userBudgets = Budget::with('category')
            ->where('user_id', auth()->id())
            ->get(); // Get user budgets for editing

        return view('incomes.edit', compact('income', 'userBudgets'));
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming data
        $request->validate([
            'source' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'include_in_budget' => 'sometimes|boolean',
            'budget_id' => 'nullable|exists:budgets,id',
        ]);

        // Find the income by ID
        $income = Income::findOrFail($id);

        // Update the income with validated data
        $income->update([
            'source' => $request->input('source'),
            'amount' => $request->input('amount'),
            'date' => $request->input('date'),
            'include_in_budget' => $request->has('include_in_budget') ? 1 : 0,
        ]);

        // Handle budget update if included in budget
        if ($request->has('include_in_budget') && $request->include_in_budget) {
            if ($request->filled('budget_id')) {
                // Increment the budget amount for the selected budget
                Budget::where('user_id', auth()->id())
                    ->where('id', $request->budget_id)
                    ->increment('amount', $income->amount);
            }
        }

        // Redirect back to the income list with a success message
        return redirect()->route('incomes.index')->with('success', 'Income updated successfully.');
    }
}
