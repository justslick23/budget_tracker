<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\Budget;
use Twilio\Rest\Client; // Add this at the top for Twilio

use Illuminate\Http\Request;
use App\Notifications\OverBudgetNotification;
use Notification;
use App\Notifications\ExpenseRecorded;


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
        ->where('category_id', $request->category_id)
        ->where('year', $year)
        ->first();

    if ($budget) {
        // Calculate the total expenses for the specific budget month
        $totalExpenses = Expense::where('user_id', auth()->id())
            ->where('category_id', $request->category_id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year) // Ensure the year is also considered
            ->sum('amount');

            $budget->spent += $expense->amount; // Add the new expense to the spent amount


        // Check if the total expenses exceed the budget
        if ($totalExpenses > $budget->amount) {
            Notification::send(auth()->user(), new OverBudgetNotification($expense, $budget));
        }
    }

    // Notify the user about the recorded expense
    auth()->user()->notify(new ExpenseRecorded($expense->amount, $expense->category));
    $this->sendSmsNotification($expense);

    return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
}

public function destroy($id)
    {
        // Find the expense by ID
        $expense = Expense::where('user_id', auth()->id())->findOrFail($id);
        
        // Optionally update the budget spent amount
        $budget = Budget::where('user_id', auth()->id())
            ->where('month', $expense->date->format('m'))
            ->where('category_id', $expense->category_id)
            ->where('year', $expense->date->format('Y'))
            ->first();
        
        if ($budget) {
            $budget->spent += $expense->amount; // Subtract the expense from the spent amount
            $budget->save(); // Save the updated budget
        }

        // Delete the expense
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
    
    // Method to send an SMS using Twilio
protected function sendSmsNotification($expense)
{
    // Initialize Twilio Client
    $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

    // Define the message content
  // Format the amount as currency
  $formattedAmount = number_format($expense->amount, 2); // Format to 2 decimal places
  $message = "An expense of M $formattedAmount  ($expense->description) has been recorded in the {$expense->category->name} category.";

    // Send SMS
    $twilio->messages->create(
        auth()->user()->phone_number, // Send to user's phone number
        [
            'from' => env('TWILIO_PHONE_NUMBER'), // Twilio phone number
            'body' => $message
        ]
    );

    $twilio->messages->create(
        "whatsapp:{auth()->user()->phone_number}", // Ensure this is a WhatsApp number
        [
            'from' => env('TWILIO_WHATSAPP_NUMBER'), // Twilio WhatsApp number
            'body' => $message
        ]
    );
}
}
