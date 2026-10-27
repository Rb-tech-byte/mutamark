<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sales_invoice_items', 'purchase_invoice_items'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->decimal('quantity', 18, 4)->change());
        }
        if (!Schema::hasColumn('sales_invoices', 'sales_order_id')) Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_id')->nullable()->unique();
            $table->string('currency', 10)->default('TZS');
        });
        if (!Schema::hasColumn('delivery_notes', 'sales_order_id')) Schema::table('delivery_notes', fn (Blueprint $table) => $table->unsignedBigInteger('sales_order_id')->nullable()->index());
        if (!Schema::hasColumn('customers', 'credit_limit')) Schema::table('customers', fn (Blueprint $table) => $table->decimal('credit_limit', 18, 2)->nullable());
    }

    public function down(): void
    {
        // Preserve expanded precision and financial workflow references on rollback.
    }
};
