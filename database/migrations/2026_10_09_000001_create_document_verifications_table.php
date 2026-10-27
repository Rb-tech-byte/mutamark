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
        if (!Schema::hasTable('document_verifications')) {
            Schema::create('document_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('document_type', 50)->index(); // invoice, quotation, purchase_order, receipt_voucher, payment_voucher, delivery_note
                $table->unsignedBigInteger('document_id')->nullable()->index();
                $table->string('document_number', 50)->index();
                $table->string('verification_hash', 64)->unique();
                $table->text('qr_payload')->nullable();
                $table->string('issuer_name')->default('Mutamark Enterprises');
                $table->string('party_name')->nullable();
                $table->string('party_email')->nullable();
                $table->string('party_phone')->nullable();
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->string('currency', 10)->default('TZS');
                $table->date('issue_date')->nullable();
                $table->date('due_date')->nullable();
                $table->string('status', 30)->default('verified'); // verified, paid, pending, cancelled
                $table->string('signatory_name')->nullable()->default('Mutamark Authorized Signatory');
                $table->string('signatory_title')->nullable()->default('Finance & Operations Directorate');
                $table->text('signature_data')->nullable(); // base64 signature or seal hash
                $table->string('lipa_namba', 30)->default('16381432');
                $table->string('tin_number', 30)->nullable();
                $table->string('vrn_number', 30)->nullable();
                $table->text('bank_details')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};
