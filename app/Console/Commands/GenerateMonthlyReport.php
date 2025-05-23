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
        $users = User::all();
    
        if ($users->isEmpty()) {
            $this->info("No users found.");
            return;
        }
    
        // Define custom period: 27th of previous month to 26th of current month
        $startDate = Carbon::now()->subMonth()->startOfMonth()->addDays(26); // 27th previous month
        $endDate = Carbon::now()->startOfMonth()->addDays(25); // 26th this month
    
        foreach ($users as $user) {
            $expenses = Expense::where('user_id', $user->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();
    
            $budgets = Budget::where('user_id', $user->id)
                ->whereBetween('month', [$startDate->month, $endDate->month]) // optional: refine if needed
                ->get();
    
            if ($expenses->isEmpty() && $budgets->isEmpty()) {
                $this->info("No data found for {$user->email} from {$startDate->format('d M')} to {$endDate->format('d M')}.");
                continue;
            }
    
            $totalExpenses = $expenses->sum(fn($expense) => $expense->amount);
            $totalBudget = $budgets->sum(fn($budget) => $budget->amount);
    
            $pdf = Pdf::loadView('reports.user_report', compact('user', 'expenses', 'budgets', 'totalExpenses', 'totalBudget'));
            $fileName = "BudgetTrackerReport_{$user->name}_{$startDate->format('Ymd')}_to_{$endDate->format('Ymd')}.pdf";
            $pdfPath = storage_path("app/public/reports/$fileName");
    
            $pdf->save($pdfPath);
    
            Mail::to($user->email)->send(new UserReportMail($user, $pdfPath));
    
            $this->info("Report sent to {$user->email} for period {$startDate->format('d M')} to {$endDate->format('d M')}.");
        }
    }
    
    
}
