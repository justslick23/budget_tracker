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
                $this->info("No data for {$user->email} for {$previousMonthDate->format('F Y')}");
                continue;
            }
        
            $totalExpenses = $expenses->sum('amount'); // Laravel will call the accessor for each
            $totalBudget = $budgets->sum('amount');
        
            $pdf = Pdf::loadView('reports.user_report', compact('user', 'expenses', 'budgets', 'totalExpenses', 'totalBudget'));
            $pdfContent = $pdf->output();
        
            if (empty($pdfContent)) {
                \Log::error("PDF generation failed for {$user->email}");
                continue;
            }
        
            $safeUserName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $user->name);
            $fileName = "BudgetTrackerReport_{$safeUserName}_{$previousMonth}.pdf";
        
            Mail::to($user->email)->send(new UserReportMail($user, $pdfContent, $fileName));
        
            $this->info("Report sent to {$user->email} for {$previousMonthDate->format('F Y')}");
        }
    }
    
    
}
