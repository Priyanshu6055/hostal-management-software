<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Make start_date and end_date nullable
            $table->timestamp('start_date')->nullable()->change();
            $table->timestamp('end_date')->nullable()->change();
            // You might also want to add 'price' column if not already present or if it needs to be updated to be nullable.
            // And also the 'total_amount' if they aren't already.
            // $table->decimal('price', 10, 2)->nullable()->change(); // Example if you want to make price nullable too.
            // $table->decimal('total_amount', 10, 2)->nullable()->change(); // Example
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Revert changes in the down method if needed.
            // This assumes they were not nullable before. Adjust if your original schema was different.
            $table->timestamp('start_date')->nullable(false)->change();
            $table->timestamp('end_date')->nullable(false)->change();
            // $table->decimal('price', 10, 2)->nullable(false)->change();
            // $table->decimal('total_amount', 10, 2)->nullable(false)->change();
        });
    }
};