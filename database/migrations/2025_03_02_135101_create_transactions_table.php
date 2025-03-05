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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('amount',10, 2);
            $table->integer('status')->default(0); 
            $table->string('transaction_reference');
            $table->string('invoice');
            $table->string('description')->nullable();
            $table->string('purpose')->nullable();
            $table->string('gateway')->nullable();
            $table->string('gateway_response')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('signature')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
