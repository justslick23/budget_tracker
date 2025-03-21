<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Expense;
use App\Models\Budget;
use App\Mail\UserReportMail;

class GenerateMonthlyReport extends Command
{
    protected $signature = 'report:all';
    protected $description = 'Generate and email a monthly report for all users';

    public function handle()
    {
        $users = User::all();  // Fetch all users
    
        if ($users->isEmpty()) {
            $this->info("No users found.");
            return;
        }
    
        // Get previous month
        $previousMonthDate = Carbon::now()->subMonth();
        $previousMonth = $previousMonthDate->format('Y-m');
    
        foreach ($users as $user) {
            $expenses = Expense::where('user_id', $user->id)
                ->where('date', 'like', "$previousMonth%")
                ->get();
    
            $budgets = Budget::where('user_id', $user->id)
                ->where('month', $previousMonthDate->month)
                ->get();
    
            if ($expenses->isEmpty() && $budgets->isEmpty()) {
                $this->info("No data found for {$user->email} for {$previousMonthDate->format('F Y')}.");
                continue; // Skip the current user and move to the next one
            }
    
            // Calculate totals
            $totalExpenses = $expenses->sum(fn ($expense) => ($expense->amount));  // Assuming amount is encrypted
            $totalBudget = $budgets->sum(fn ($budget) => ($budget->amount));  // Assuming amount is encrypted
    
            // Generate PDF
            $pdf = Pdf::loadView('reports.user_report', compact('user', 'expenses', 'budgets', 'totalExpenses', 'totalBudget'));
            $fileName = "BudgetTrackerReport_{$user->name}_{$previousMonth}.pdf";
            $pdfPath = storage_path("app/public/reports/$fileName");
    
            $pdf->save($pdfPath);
    
            // Send email
            Mail::to($user->email)->send(new UserReportMail($user, $pdfPath));
    
            $this->info("Report sent to {$user->email} for {$previousMonthDate->format('F Y')}");
        }
    }
    
}
