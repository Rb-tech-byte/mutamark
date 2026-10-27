<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Three-Way Matching Audit & AP Reconciliation (SRS Module 8 - Section 3.8.6).
     */
    public function up(): void
    {
        if (!Schema::hasTable('three_way_match_configs')) {
            Schema::create('three_way_match_configs', function (Blueprint $table) {
                $table->id();
                $table->decimal('qty_tolerance_percent', 5, 2)->default(0.00); // 0% by default
                $table->decimal('price_tolerance_percent', 5, 2)->default(0.00); // 0% by default
                $table->decimal('max_variance_amount_tzs', 15, 2)->default(0.00);
                $table->boolean('require_qc_passed')->default(true);
                $table->boolean('auto_hold_on_variance')->default(true);
                $table->unsignedBigInteger('created_by')->default(1)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('three_way_match_authorizations')) {
            Schema::create('three_way_match_authorizations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('grn_id')->index();
                $table->unsignedBigInteger('purchase_invoice_id')->nullable()->index();
                $table->enum('authorization_status', ['pending', 'cleared', 'disputed', 'rejected'])->default('pending');
                $table->decimal('qty_variance_detected', 12, 2)->default(0);
                $table->decimal('price_variance_detected', 15, 2)->default(0);
                $table->decimal('total_variance_amount_tzs', 15, 2)->default(0);
                $table->boolean('disbursement_cleared')->default(false);
                $table->unsignedBigInteger('cleared_by')->nullable()->index();
                $table->timestamp('cleared_at')->nullable();
                $table->text('clearance_notes')->nullable();
                $table->string('verification_hash')->nullable();
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->foreign('grn_id')->references('id')->on('goods_received_notes')->onDelete('cascade');
                $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('three_way_match_authorizations');
        Schema::dropIfExists('three_way_match_configs');
    }
};
