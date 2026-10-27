<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Mutamark Unified Payment Gateway Manager (SRS Module 33).
     */
    public function up(): void
    {
        // 1. Payment Gateways Master Configuration
        if (!Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('gateway_key', 50)->unique(); // selcom, pesapal, stripe, paypal, manual_mobile_money, bank_transfer
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->string('mode', 20)->default('sandbox'); // sandbox, live
                $table->json('supported_currencies')->nullable();
                $table->json('credentials')->nullable();
                $table->decimal('transaction_fee_percentage', 5, 2)->default(0.00);
                $table->decimal('transaction_fee_flat', 15, 2)->default(0.00);
                $table->string('webhook_secret', 255)->nullable();
                $table->text('instructions')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->index(['gateway_key', 'is_active']);
            });
        }

        // 2. Payment Transactions & Settlements Ledger
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_reference', 50)->unique(); // TXN-2026-00001
                $table->string('gateway_key', 50);
                $table->string('invoice_type', 50)->default('sales_invoice');
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->string('invoice_number', 50)->nullable();
                $table->string('customer_name', 150)->nullable();
                $table->string('customer_email', 150)->nullable();
                $table->string('customer_phone', 50)->nullable();
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->string('currency', 10)->default('TZS');
                $table->decimal('fee_amount', 15, 2)->default(0.00);
                $table->decimal('net_amount', 15, 2)->default(0.00);
                $table->string('status', 30)->default('pending'); // pending, processing, completed, failed, cancelled, refunded
                $table->string('gateway_transaction_id', 100)->nullable();
                $table->string('payment_method_channel', 50)->nullable(); // mpesa, tigopesa, airtelmoney, card, paypal, bank
                $table->string('receipt_voucher_number', 50)->nullable(); // RV-2026-00001
                $table->unsignedBigInteger('customer_payment_id')->nullable();
                $table->json('gateway_response')->nullable();
                $table->string('checkout_token', 64)->unique()->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->index(['invoice_type', 'invoice_id']);
                $table->index(['status', 'gateway_key']);
                $table->index('checkout_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_gateways');
    }
};
