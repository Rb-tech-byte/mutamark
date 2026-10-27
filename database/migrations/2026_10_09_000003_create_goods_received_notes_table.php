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
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->string('purchase_order_number')->nullable()->index();
            $table->unsignedBigInteger('purchase_invoice_id')->nullable()->index();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->date('grn_date');
            $table->string('delivery_challan_number')->nullable();
            $table->string('carrier_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->unsignedBigInteger('received_by')->nullable()->index();
            $table->enum('inspection_status', [
                'pending',
                'inspected_passed',
                'partially_accepted',
                'rejected'
            ])->default('pending');
            $table->enum('matching_status', [
                'unmatched',
                'matched',
                'variance_detected'
            ])->default('unmatched');
            $table->decimal('total_ordered_qty', 10, 2)->default(0);
            $table->decimal('total_received_qty', 10, 2)->default(0);
            $table->decimal('total_accepted_qty', 10, 2)->default(0);
            $table->decimal('total_rejected_qty', 10, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('quality_inspection_notes')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->enum('status', ['draft', 'received', 'posted', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('creator_id')->default(1);
            $table->unsignedBigInteger('created_by')->default(1);
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grn_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->decimal('ordered_quantity', 10, 2)->default(0);
            $table->decimal('received_quantity', 10, 2)->default(0);
            $table->decimal('accepted_quantity', 10, 2)->default(0);
            $table->decimal('rejected_quantity', 10, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('batch_number')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('grn_id')->references('id')->on('goods_received_notes')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('product_service_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('goods_received_notes');
    }
};
