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
        if (!Schema::hasTable('inventory_serial_numbers')) {
            Schema::create('inventory_serial_numbers', function (Blueprint $table) {
                $table->id();
                $table->string('serial_number', 191)->index();
                $table->string('batch_number', 191)->nullable()->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->enum('status', [
                    'in_stock',
                    'allocated',
                    'dispatched',
                    'delivered',
                    'defective',
                    'under_maintenance'
                ])->default('in_stock');
                $table->decimal('purchase_cost', 15, 2)->nullable();
                $table->decimal('selling_price', 15, 2)->nullable();
                $table->integer('warranty_months')->default(12);
                $table->date('warranty_expiry_date')->nullable();
                $table->string('barcode', 191)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('product_service_items')
                    ->onDelete('cascade');

                $table->foreign('warehouse_id')
                    ->references('id')
                    ->on('warehouses')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_serial_numbers');
    }
};
