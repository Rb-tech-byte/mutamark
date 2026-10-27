<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Request for Quotation (RFQ) and Comparative Evaluation (SRS Module 8 - Section 3.8.3).
     */
    public function up(): void
    {
        if (!Schema::hasTable('rfqs')) {
            Schema::create('rfqs', function (Blueprint $table) {
                $table->id();
                $table->string('rfq_number')->unique(); // RFQ-2026-00001
                $table->string('title');
                $table->unsignedBigInteger('requisition_id')->nullable()->index();
                $table->date('issue_date');
                $table->date('closing_date');
                $table->date('delivery_deadline')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable()->index();
                $table->enum('status', ['draft', 'published', 'evaluating', 'awarded', 'cancelled'])->default('draft');
                $table->string('payment_terms')->nullable()->default('Net 30 Days');
                $table->string('delivery_terms')->nullable()->default('DAP Destination');
                $table->unsignedBigInteger('awarded_vendor_id')->nullable()->index();
                $table->unsignedBigInteger('awarded_bid_id')->nullable()->index();
                $table->unsignedBigInteger('awarded_po_id')->nullable()->index();
                $table->text('evaluation_notes')->nullable();
                $table->string('verification_hash')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('requisition_id')->references('id')->on('purchase_requisitions')->onDelete('set null');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('rfq_items')) {
            Schema::create('rfq_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rfq_id')->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_name');
                $table->string('sku')->nullable();
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('unit')->default('units');
                $table->decimal('target_unit_price', 15, 2)->default(0);
                $table->text('specifications')->nullable();
                $table->timestamps();

                $table->foreign('rfq_id')->references('id')->on('rfqs')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('product_service_items')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('supplier_quotations')) {
            Schema::create('supplier_quotations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rfq_id')->index();
                $table->unsignedBigInteger('vendor_id')->index();
                $table->string('vendor_name')->nullable();
                $table->string('quotation_reference'); // e.g. QUO-VEND-1049
                $table->date('submission_date');
                $table->date('valid_until')->nullable();
                $table->integer('delivery_lead_time_days')->default(7);
                $table->integer('warranty_months')->default(12);
                $table->string('payment_terms')->nullable()->default('30 Days Net');
                $table->decimal('total_bid_amount', 15, 2)->default(0);
                $table->enum('status', ['submitted', 'shortlisted', 'awarded', 'rejected'])->default('submitted');
                $table->decimal('evaluation_score', 5, 2)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('rfq_id')->references('id')->on('rfqs')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('supplier_quotation_items')) {
            Schema::create('supplier_quotation_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_quotation_id')->index();
                $table->unsignedBigInteger('rfq_item_id')->index();
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('total_price', 15, 2)->default(0);
                $table->string('remarks')->nullable();
                $table->timestamps();

                $table->foreign('supplier_quotation_id')->references('id')->on('supplier_quotations')->onDelete('cascade');
                $table->foreign('rfq_item_id')->references('id')->on('rfq_items')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_quotation_items');
        Schema::dropIfExists('supplier_quotation_items');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
    }
};
