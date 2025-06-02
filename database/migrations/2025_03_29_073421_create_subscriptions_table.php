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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_head_id')->constrained('fees')->onDelete('cascade');
            $table->string('subscription_type');
            $table->decimal('price', 10, 2); // Added price field
            $table->decimal('total_amount', 10, 2); // Added total amount field
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['Pending', 'Active', 'Expired'])->default('Pending');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
        
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
