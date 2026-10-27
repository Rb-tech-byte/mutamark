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
        if (!Schema::hasTable('sales_orders')) {
            Schema::create('sales_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique(); // SO-2026-00001
                $table->unsignedBigInteger('proposal_id')->nullable();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->date('order_date');
                $table->date('delivery_due_date')->nullable();
                $table->string('status')->default('confirmed'); // confirmed, stock_reserved, partially_fulfilled, fulfilled, cancelled
                $table->boolean('stock_reserved')->default(true);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->string('payment_terms')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sales_order_items')) {
            Schema::create('sales_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_order_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->string('sku')->nullable();
                $table->decimal('quantity', 15, 2)->default(1);
                $table->decimal('fulfilled_quantity', 15, 2)->default(0);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
