<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringExpense;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecurringExpenseCreatedMail;
class ProcessRecurringExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process active recurring expenses and create expenses for current day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now();
        $dayOfMonth = (int) $today->format('d');

        $this->info("Processing recurring expenses for day {$dayOfMonth}...");

        // Get all active recurring expenses where day_of_month matches today
        $recurrings = RecurringExpense::where('active', true)
            ->where('day_of_month', $dayOfMonth)
            ->get();

        foreach ($recurrings as $recurring) {
            // Check if an expense for this recurring rule was already created this month
            $existingExpense = Expense::where('user_id', $recurring->user_id)
                ->where('category_id', $recurring->category_id)
                ->where('description', $recurring->description)
                ->whereYear('date', $today->year)
                ->whereMonth('date', $today->month)
                ->whereDay('date', $dayOfMonth)
                ->first();

            if ($existingExpense) {
                $this->line("Expense for recurring ID {$recurring->id} already exists for today. Skipping.");
                continue;
            }

            $expense = Expense::create([
                'user_id' => $recurring->user_id,
                'category_id' => $recurring->category_id,
                'amount' => $recurring->amount,
                'description' => $recurring->description,
                'date' => $today->toDateString(),
            ]);
        
            Mail::to($expense->user->email)->send(new RecurringExpenseCreatedMail($expense));
        

            $this->info("Created expense for recurring ID {$recurring->id}.");
        }

        $this->info('Recurring expenses processed.');
    }
}
