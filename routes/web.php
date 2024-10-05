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
    Route::get('/dashboard/filter', [HomeController::class, 'filter'])->name('dashboard.filter');
});