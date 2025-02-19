<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:encrypt-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt DB Data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $budgets = DB::table('budgets')->get();
        $incomes = DB::table('incomes')->get();
        $expenses = DB::table('expenses')->get();

        foreach ($budgets as $budget) {
            if (!str_starts_with($budget->amount, 'eyJ')) { // Check if it's not encrypted
                DB::table('budgets')
                    ->where('id', $budget->id)
                    ->update([
                        'amount' => Crypt::encryptString((string)$budget->amount),
                        'spent' => Crypt::encryptString((string)$budget->spent)
                    ]);
            }
        }

        foreach ($incomes as $income) {
            if (!str_starts_with($income->amount, 'eyJ')) { // Check if it's not encrypted
                DB::table('incomes')
                    ->where('id', $income->id)
                    ->update([
                        'amount' => Crypt::encryptString((string)$income->amount),
                
                    ]);
            }
        }

        foreach ($expenses as $expense) {
            if (!str_starts_with($expense->amount, 'eyJ')) { // Check if it's not encrypted
                DB::table('expenses')
                    ->where('id', $expense->id)
                    ->update([
                        'amount' => Crypt::encryptString((string)$expense->amount),
                
                    ]);
            }
        }

        $this->info('Budget data encrypted successfully.');
    }
}
