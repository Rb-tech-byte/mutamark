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
        Schema::table('cf_declarations', function (Blueprint $table) {
            // Worksheet Versioning (INT-MAN-003, INT-MAN-005)
            $table->unsignedInteger('worksheet_version')->default(1)->after('customs_channel');
            $table->timestamp('worksheet_generated_at')->nullable()->after('worksheet_version');
            $table->unsignedBigInteger('worksheet_generated_by')->nullable()->after('worksheet_generated_at');

            // External TANCIS Manual Submission metadata (INT-MAN-006, INT-MAN-008)
            $table->string('manual_entry_source')->default('Manual entry from TANCIS')->after('worksheet_generated_by');
            $table->dateTime('external_submission_time')->nullable()->after('manual_entry_source');
            $table->string('lodgement_evidence_path')->nullable()->after('external_submission_time');

            // Detailed Official TRA Assessed Tax Breakdown (INT-MAN-010)
            $table->decimal('import_duty_assessed_tzs', 18, 2)->default(0)->after('assessed_amount_tzs');
            $table->decimal('vat_assessed_tzs', 18, 2)->default(0)->after('import_duty_assessed_tzs');
            $table->decimal('rdl_assessed_tzs', 18, 2)->default(0)->after('vat_assessed_tzs');
            $table->decimal('cpf_assessed_tzs', 18, 2)->default(0)->after('rdl_assessed_tzs');
            $table->decimal('excise_assessed_tzs', 18, 2)->default(0)->after('cpf_assessed_tzs');
            $table->decimal('other_taxes_assessed_tzs', 18, 2)->default(0)->after('excise_assessed_tzs');
            $table->decimal('penalties_tzs', 18, 2)->default(0)->after('other_taxes_assessed_tzs');

            // Evidence Attachments & Verification (INT-MAN-010, INT-MAN-011, INT-MAN-015)
            $table->string('assessment_document_path')->nullable()->after('penalties_tzs');
            $table->boolean('is_assessment_verified')->default(false)->after('assessment_document_path');
            $table->unsignedBigInteger('assessment_verified_by')->nullable()->after('is_assessment_verified');
            $table->timestamp('assessment_verified_at')->nullable()->after('assessment_verified_by');

            $table->string('payment_receipt_path')->nullable()->after('payment_date');
            $table->boolean('is_payment_verified')->default(false)->after('payment_receipt_path');
            $table->unsignedBigInteger('payment_verified_by')->nullable()->after('is_payment_verified');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_verified_by');

            $table->string('release_order_path')->nullable()->after('release_date');
            $table->boolean('is_release_verified')->default(false)->after('release_order_path');
            $table->unsignedBigInteger('release_verified_by')->nullable()->after('is_release_verified');
            $table->timestamp('release_verified_at')->nullable()->after('release_verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cf_declarations', function (Blueprint $table) {
            $table->dropColumn([
                'worksheet_version',
                'worksheet_generated_at',
                'worksheet_generated_by',
                'manual_entry_source',
                'external_submission_time',
                'lodgement_evidence_path',
                'import_duty_assessed_tzs',
                'vat_assessed_tzs',
                'rdl_assessed_tzs',
                'cpf_assessed_tzs',
                'excise_assessed_tzs',
                'other_taxes_assessed_tzs',
                'penalties_tzs',
                'assessment_document_path',
                'is_assessment_verified',
                'assessment_verified_by',
                'assessment_verified_at',
                'payment_receipt_path',
                'is_payment_verified',
                'payment_verified_by',
                'payment_verified_at',
                'release_order_path',
                'is_release_verified',
                'release_verified_by',
                'release_verified_at',
            ]);
        });
    }
};
