<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Fixed Assets (SRS 3.20) and Warranty & Servicing (SRS 3.21).
     */
    public function up(): void
    {
        if (!Schema::hasTable('asset_categories')) {
            Schema::create('asset_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('depreciation_method')->default('straight_line'); // straight_line, reducing_balance, none
                $table->decimal('depreciation_rate', 5, 2)->default(20.00); // 20%
                $table->integer('useful_life_years')->default(5);
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fixed_assets')) {
            Schema::create('fixed_assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_code')->unique(); // AST-2026-00001
                $table->string('name');
                $table->unsignedBigInteger('asset_category_id')->nullable();
                $table->date('purchase_date');
                $table->decimal('purchase_cost', 15, 2)->default(0); // TZS
                $table->decimal('salvage_value', 15, 2)->default(0); // TZS
                $table->decimal('current_value', 15, 2)->default(0); // Book Value in TZS
                $table->decimal('accumulated_depreciation', 15, 2)->default(0); // TZS
                $table->string('depreciation_method')->default('straight_line');
                $table->decimal('depreciation_rate', 5, 2)->default(20.00);
                $table->integer('useful_life_years')->default(5);
                $table->string('location')->nullable();
                $table->unsignedBigInteger('custodian_id')->nullable(); // users.id
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('serial_number')->nullable();
                $table->date('warranty_expiry_date')->nullable();
                $table->string('status')->default('active'); // active, under_maintenance, transferred, disposed, written_off
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('asset_category_id')->references('id')->on('asset_categories')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('asset_depreciations')) {
            Schema::create('asset_depreciations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('asset_id');
                $table->integer('fiscal_year');
                $table->string('period'); // e.g. 2026-10
                $table->decimal('opening_value', 15, 2);
                $table->decimal('depreciation_amount', 15, 2);
                $table->decimal('closing_value', 15, 2);
                $table->unsignedBigInteger('journal_entry_id')->nullable();
                $table->unsignedBigInteger('posted_by')->nullable();
                $table->timestamp('posted_at')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('asset_id')->references('id')->on('fixed_assets')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('asset_transfers')) {
            Schema::create('asset_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_number')->unique(); // TRF-2026-00001
                $table->unsignedBigInteger('asset_id');
                $table->date('transfer_date');
                $table->string('from_location')->nullable();
                $table->string('to_location')->nullable();
                $table->unsignedBigInteger('from_custodian_id')->nullable();
                $table->unsignedBigInteger('to_custodian_id')->nullable();
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('asset_id')->references('id')->on('fixed_assets')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('asset_disposals')) {
            Schema::create('asset_disposals', function (Blueprint $table) {
                $table->id();
                $table->string('disposal_number')->unique(); // DSP-2026-00001
                $table->unsignedBigInteger('asset_id');
                $table->date('disposal_date');
                $table->string('disposal_type')->default('sale'); // sale, scrapped, donated, write_off
                $table->decimal('sale_amount', 15, 2)->default(0);
                $table->decimal('book_value_at_disposal', 15, 2);
                $table->decimal('gain_loss_amount', 15, 2)->default(0);
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('asset_id')->references('id')->on('fixed_assets')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('warranty_registrations')) {
            Schema::create('warranty_registrations', function (Blueprint $table) {
                $table->id();
                $table->string('warranty_code')->unique(); // WAR-2026-00001
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('serial_number');
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_name');
                $table->string('customer_phone')->nullable();
                $table->string('customer_email')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('warranty_period_months')->default(12);
                $table->text('warranty_terms')->nullable();
                $table->string('status')->default('active'); // active, expired, voided, claimed
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('service_tickets')) {
            Schema::create('service_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique(); // SRV-2026-00001
                $table->unsignedBigInteger('warranty_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_name');
                $table->string('contact_phone')->nullable();
                $table->string('product_name');
                $table->string('serial_number')->nullable();
                $table->string('service_type')->default('warranty_claim'); // warranty_claim, paid_repair, preventive_maintenance
                $table->text('issue_reported');
                $table->text('diagnostic_findings')->nullable();
                $table->boolean('vendor_repair')->default(false);
                $table->string('vendor_name')->nullable();
                $table->string('vendor_tracking_ref')->nullable();
                $table->decimal('estimated_cost', 15, 2)->default(0);
                $table->decimal('actual_cost', 15, 2)->default(0);
                $table->unsignedBigInteger('assigned_technician_id')->nullable();
                $table->string('status')->default('received'); // received, diagnosing, sent_to_vendor, repaired, delivered_to_customer, cancelled
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('warranty_id')->references('id')->on('warranty_registrations')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tickets');
        Schema::dropIfExists('warranty_registrations');
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('asset_categories');
    }
};
