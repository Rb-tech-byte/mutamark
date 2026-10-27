<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Paperless Office Dynamic Document Template Builder (SRS Module 25).
     */
    public function up(): void
    {
        if (!Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_key')->unique();
                $table->string('name');
                $table->string('document_type'); // quotation, proforma_invoice, sales_order, delivery_note, sales_invoice, receipt_voucher, purchase_requisition, rfq, comparative_statement, purchase_order, goods_received_note, credit_note, debit_note, payslip, contract
                $table->string('header_title');
                $table->string('header_subtitle')->nullable();
                $table->text('header_text')->nullable();
                $table->text('footer_text')->nullable();
                $table->text('terms_and_conditions')->nullable();
                $table->string('primary_color')->default('#4f46e5'); // Indigo
                $table->string('secondary_color')->default('#0f172a'); // Slate 900
                $table->string('accent_color')->default('#10b981'); // Emerald
                $table->string('font_family')->default('Inter');
                $table->string('paper_size')->default('A4');
                $table->string('orientation')->default('portrait');
                $table->boolean('show_company_logo')->default(true);
                $table->boolean('show_qr_code')->default(true);
                $table->boolean('show_digital_seal')->default(true);
                $table->boolean('show_signature_block')->default(true);
                $table->boolean('show_bank_details')->default(true);
                $table->boolean('show_tax_breakdown')->default(true);
                $table->boolean('show_amount_in_words')->default(true);
                $table->string('watermark_text')->nullable();
                $table->boolean('is_default')->default(false);
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->index(['created_by', 'document_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
