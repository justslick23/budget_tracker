<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Category;
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
        $categories = Category::all();
        return view('incomes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);
    
        // Create income and associate it with the authenticated user
        $income = Income::create([
            'source' => $request->source,
            'amount' => $request->amount,
            'date' => $request->date,
            'user_id' => auth()->id(), // Assuming you're using authentication
        ]);
    
        // Get the current month and year
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMonthNumber = Carbon::parse($currentMonth)->format('m'); // e.g., "10"

        $userId = auth()->id();

         // If the user chose to include the income in the current budget
    if ($request->has('include_in_budget')) {
        $currentMonth = Carbon::now()->format('Y-m');
        Budget::where('user_id', auth()->id())
            ->where('month', $currentMonthNumber)
            ->increment('amount', $income->amount);
    }

    auth()->user()->notify(new IncomeRecorded($income->amount, $income->source));

    
    
        return redirect()->route('incomes.index')->with('success', 'Income added successfully and budget updated.');
    }
    
    public function destroy($id)
    {
        // Find the income by ID
        $income = Income::where('user_id', auth()->id())->findOrFail($id);
        
        // Optionally, update the budget amount
        $currentMonthNumber = Carbon::now()->format('m');
        $budget = Budget::where('user_id', auth()->id())
            ->where('month', $currentMonthNumber)
            ->first();

        if ($budget) {
            $budget->amount -= $income->amount; // Subtract the income from the budget amount
            $budget->save(); // Save the updated budget
        }

        // Delete the income
        $income->delete();

        return redirect()->route('incomes.index')->with('success', 'Income deleted successfully.');
    }
    
}
