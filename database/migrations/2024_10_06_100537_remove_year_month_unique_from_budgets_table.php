<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('budgets', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['year', 'month']); // Assuming the constraint was named as budgets_year_month_unique
        });
    }

    public function down()
    {
        Schema::table('budgets', function (Blueprint $table) {
            // Add the unique constraint back in case of rollback
            $table->unique(['year', 'month'], 'budgets_year_month_unique');
        });
    }
};
