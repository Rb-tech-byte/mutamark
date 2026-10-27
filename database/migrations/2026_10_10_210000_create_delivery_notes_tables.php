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
        if (!Schema::hasTable('delivery_notes')) {
            Schema::create('delivery_notes', function (Blueprint $table) {
                $table->id();
                $table->string('delivery_number', 50)->unique(); // e.g. DN-2026-00001
                $table->date('delivery_date');
                $table->date('estimated_delivery_date')->nullable();
                $table->date('actual_delivery_date')->nullable();
                
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('warehouse_id');
                
                // Transport & Fleet integration
                $table->unsignedBigInteger('vehicle_id')->nullable();
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->string('driver_name')->nullable();
                $table->string('driver_phone')->nullable();
                $table->string('vehicle_plate')->nullable();
                
                // Shipping destination
                $table->text('shipping_address')->nullable();
                $table->string('destination_city')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('contact_phone')->nullable();
                
                // Status & Tracking
                $table->enum('status', ['draft', 'scheduled', 'dispatched', 'in_transit', 'delivered', 'returned', 'cancelled'])->default('draft');
                $table->string('tracking_number')->nullable();
                $table->integer('total_packages')->default(1);
                $table->decimal('total_weight_kg', 10, 2)->default(0.00);
                $table->text('notes')->nullable();
                
                // Proof of Delivery (POD)
                $table->string('proof_of_delivery_recipient')->nullable();
                $table->longText('proof_of_delivery_signature')->nullable();
                $table->text('proof_of_delivery_notes')->nullable();
                $table->timestamp('delivery_verified_at')->nullable();
                
                // Digital verification & stock integrity
                $table->string('verification_hash', 64)->nullable()->unique();
                $table->boolean('stock_deducted')->default(false);
                
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->index(['created_by', 'status']);
                $table->index('delivery_date');
            });
        }

        if (!Schema::hasTable('delivery_note_items')) {
            Schema::create('delivery_note_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delivery_note_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->string('sku')->nullable();
                $table->decimal('quantity_ordered', 12, 2)->default(1.00);
                $table->decimal('quantity_shipped', 12, 2)->default(1.00);
                $table->decimal('quantity_received', 12, 2)->default(0.00);
                $table->string('unit', 50)->default('units');
                $table->string('batch_number')->nullable();
                $table->string('serial_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->onDelete('cascade');
                $table->index('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};
