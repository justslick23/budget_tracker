<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;

Auth::routes();

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::resource('expenses', ExpenseController::class);
    Route::resource('incomes', IncomeController::class);
    Route::resource('budgets', BudgetController::class);
    Route::resource('categories', CategoryController::class);
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard.index');
});

use Illuminate\Support\Facades\Mail;

Route::get('test-email', function () {
    $toEmail = 'tokelo.foso23@gmail.com';  // Replace with a valid email address
    
    Mail::raw('This is a test email to verify configuration.', function ($message) use ($toEmail) {
        $message->to($toEmail)
                ->subject('Test Email')
                ->from('hello@tokelofoso.online', 'Budget Tracker');
    });

    return 'Test email sent!';
});
