<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomesTable extends Migration
{
    public function up()
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // E.g., salary, freelance work
            $table->decimal('amount', 10, 2);
            $table->date('date'); // Date of income
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('incomes');
    }
}
