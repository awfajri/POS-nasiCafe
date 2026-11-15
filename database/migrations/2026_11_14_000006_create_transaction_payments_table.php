<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('payer_name')->nullable();
            $table->enum('payment_method', ['cash', 'qris']);
            $table->unsignedBigInteger('amount_paid');
            $table->unsignedBigInteger('cash_received')->nullable();
            $table->unsignedBigInteger('change_amount')->default(0);
            $table->json('paid_items_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};