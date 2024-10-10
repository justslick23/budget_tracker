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
use App\Services\OpenAIService;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

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

    // Get historical expenses for the user
    $historicalExpenses = Expense::where('user_id', auth()->id())
        ->orderBy('date', 'asc')
        ->get(); // Fetch historical expenses

        $expensesArray = $historicalExpenses->map(function ($expense) {
            return [
                'date' => $expense->date->format('Y-m-d'), // Format date as needed
                'amount' => $expense->amount,
                'description' => $expense->description,
                // Add any other relevant fields here
            ];
        })->toArray();

       
    

    // Notify the user about the recorded expense
    auth()->user()->notify(new ExpenseRecorded($expense->amount, $expense->category));
    $this->sendSmsNotification($expense);

   
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
            $budget->spent -= $expense->amount; // Subtract the expense from the spent amount
            $budget->save(); // Save the updated budget
        }

        // Delete the expense
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    protected function sendSmsNotification($expense)
    {
        // Initialize Twilio Client
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        // Format the amount as currency
        $formattedAmount = number_format($expense->amount, 2); // Format to 2 decimal places
        $message = "An expense of M {$formattedAmount} ({$expense->description}) has been recorded in the {$expense->category->name} category.";

        // Get the user's phone number directly from the database
        $userPhoneNumber = auth()->user()->phone_number; // Already in correct format

        // Send SMS
        try {
            $twilio->messages->create(
                $userPhoneNumber, // Send to user's phone number
                [
                    'from' => env('TWILIO_PHONE_NUMBER'), // Twilio phone number
                    'body' => $message
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Failed to send SMS: {$e->getMessage()}");
        }

        // Prepare WhatsApp number
        $whatsappNumber = "whatsapp:{$userPhoneNumber}"; // Format for WhatsApp

        // Send WhatsApp message
        try {
            $twilio->messages->create(
                $whatsappNumber, // Send to user's WhatsApp number
                [
                    'from' => env('TWILIO_WHATSAPP_NUMBER'), // Twilio WhatsApp number
                    'body' => $message
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Failed to send WhatsApp message: {$e->getMessage()}");
        }
    }
}
