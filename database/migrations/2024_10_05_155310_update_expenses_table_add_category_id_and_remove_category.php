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
        Schema::table('expenses', function (Blueprint $table) {
            // Add category_id as a foreign key
            $table->unsignedBigInteger('category_id')->after('description');

            // Add foreign key constraint
            $table->foreign('category_id')->references('id')->on('categories');

            // Remove the existing category column
            $table->dropColumn('category'); // Adjust this if your column name is different
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Re-add the category column if you need to rollback
            $table->string('category')->after('description');

            // Remove the foreign key constraint
            $table->dropForeign(['category_id']);

            // Drop the category_id column
            $table->dropColumn('category_id');
        });
    }
};
