<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetsTable extends Migration
{
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->year('year'); // Store the year
            $table->unsignedTinyInteger('month'); // Store the month (1-12)
            $table->decimal('amount', 10, 2); // Store the budget amount
            $table->decimal('spent', 10, 2)->default(0); // Store the amount spent
            $table->timestamps();

            // Ensure each month of a year can only have one budget entry
            $table->unique(['year', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('budgets');
    }
}
