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
        // 1. Daily Z-Reports (TRA EFDMS End-Of-Day Fiscal Summaries)
        if (!Schema::hasTable('tra_z_reports')) {
            Schema::create('tra_z_reports', function (Blueprint $table) {
                $table->id();
                $table->string('z_report_number')->unique(); // ZREP-YYYY-#####
                $table->date('report_date');
                $table->string('opening_receipt_number')->nullable();
                $table->string('closing_receipt_number')->nullable();
                $table->integer('total_receipts_count')->default(0);
                $table->decimal('gross_turnover', 15, 2)->default(0);
                $table->decimal('net_taxable_turnover', 15, 2)->default(0);
                $table->decimal('total_vat_18', 15, 2)->default(0); // Standard VAT A (18%)
                $table->decimal('total_vat_zero_rated', 15, 2)->default(0); // VAT C (0%)
                $table->decimal('total_vat_exempt', 15, 2)->default(0); // VAT D (Exempt)
                $table->decimal('cumulative_turnover_to_date', 18, 2)->default(0);
                $table->string('status')->default('open'); // open, closed, transmitted
                $table->string('tra_ack_token')->nullable(); // TRA server acknowledgment token
                $table->dateTime('closed_at')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['report_date', 'status']);
            });
        }

        // 2. Real-Time TRA Fiscal Receipts (Sales Invoices & POS Sales)
        if (!Schema::hasTable('tra_fiscal_receipts')) {
            Schema::create('tra_fiscal_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_number')->unique(); // TRA-RCT-YYYY-#####
                $table->string('source_type')->default('sales_invoice'); // sales_invoice, pos_sale
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('customer_name')->default('General Consumer');
                $table->string('customer_tin')->default('999-999-999');
                $table->string('customer_vrn')->nullable();
                $table->decimal('total_tax_exclusive_amount', 15, 2)->default(0);
                $table->decimal('tax_amount_a', 15, 2)->default(0); // 18% VAT
                $table->decimal('tax_amount_b', 15, 2)->default(0); // Other
                $table->decimal('tax_amount_c', 15, 2)->default(0); // 0%
                $table->decimal('tax_amount_d', 15, 2)->default(0); // Exempt
                $table->decimal('total_tax_inclusive_amount', 15, 2)->default(0);
                $table->string('z_number')->nullable(); // Current active Z-Report number
                $table->integer('daily_receipt_counter')->default(1);
                $table->string('rct_verification_code', 32); // Cryptographic checksum
                $table->text('qr_payload'); // TRA URL for QR scanning
                $table->string('vfd_device_serial')->default('MUTAMARK-VFD-01');
                $table->string('status')->default('fiscalized'); // fiscalized, transmitted, voided
                $table->dateTime('fiscalized_at');
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['source_type', 'source_id']);
                $table->index(['created_by', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tra_fiscal_receipts');
        Schema::dropIfExists('tra_z_reports');
    }
};
