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
        if (!Schema::hasTable('purchase_requisitions')) {
            Schema::create('purchase_requisitions', function (Blueprint $table) {
                $table->id();
                $table->string('requisition_number', 50)->unique(); // PR-2026-00001
                $table->date('requisition_date');
                $table->date('required_by_date')->nullable();
                
                $table->unsignedBigInteger('requester_id'); // User who created/requested
                $table->string('department')->default('Operations'); // e.g., Operations, IT, Finance, Engineering
                $table->unsignedBigInteger('warehouse_id'); // Destination fulfillment warehouse
                $table->unsignedBigInteger('preferred_vendor_id')->nullable(); // Optional preferred vendor
                
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'converted', 'cancelled'])->default('draft');
                
                $table->text('justification')->nullable(); // Purpose/Reason for expenditure
                $table->decimal('estimated_total_cost', 15, 2)->default(0.00); // Currency TZS
                
                // Approval fields
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                
                // Conversion link
                $table->unsignedBigInteger('converted_invoice_id')->nullable();
                $table->timestamp('converted_at')->nullable();
                
                // Digital verification
                $table->string('verification_hash', 64)->nullable()->unique();
                
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->index(['created_by', 'status']);
                $table->index('requisition_date');
            });
        }

        if (!Schema::hasTable('purchase_requisition_items')) {
            Schema::create('purchase_requisition_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('requisition_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->string('sku')->nullable();
                $table->decimal('quantity_requested', 12, 2)->default(1.00);
                $table->decimal('estimated_unit_cost', 15, 2)->default(0.00);
                $table->decimal('estimated_total_cost', 15, 2)->default(0.00);
                $table->string('unit', 50)->default('units');
                $table->text('specifications')->nullable();
                $table->timestamps();

                $table->foreign('requisition_id')->references('id')->on('purchase_requisitions')->onDelete('cascade');
                $table->index('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_items');
        Schema::dropIfExists('purchase_requisitions');
    }
};
