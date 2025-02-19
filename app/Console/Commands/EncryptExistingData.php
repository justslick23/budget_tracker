<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptBudgetData extends Command
{
    protected $signature = 'encrypt:budgets';
    protected $description = 'Encrypt existing budgets amount and spent fields';

    public function handle()
    {
        $budgets = DB::table('budgets')->get();
        $incomes = DB::table('income')->get();
        $expenses = DB::table('expenses')->get();

        foreach ($budgets as $budget) {
            if (!str_starts_with($budget->amount, 'eyJ')) { // Check if it's not encrypted
                DB::table('budgets')
                    ->where('id', $budget->id)
                    ->update([
                        'amount' => Crypt::encryptString($budget->amount),
                        'spent' => Crypt::encryptString($budget->spent)
                    ]);
            }
        }

        foreach ($incomes as $income) {
            if (!str_starts_with($income->amount, 'eyJ')) { // Check if it's not encrypted
                DB::table('incomes')
                    ->where('id', $income->id)
                    ->update([
                        'amount' => Crypt::encryptString($income->amount),
                
                    ]);
            }
        }

        foreach ($expenses as $expense) {
            if (!str_starts_with($expense->amount, 'eyJ')) { // Check if it's not encrypted
                DB::table('expenses')
                    ->where('id', $expense->id)
                    ->update([
                        'amount' => Crypt::encryptString($expense->amount),
                
                    ]);
            }
        }

        $this->info('Budget data encrypted successfully.');
    }
}
