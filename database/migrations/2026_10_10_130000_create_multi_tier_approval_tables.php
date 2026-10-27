<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Mutamark Multi-Tier Approval Workflow Engine (SRS Module 24).
     */
    public function up(): void
    {
        // 1. Approval Policies (Rules & Escalation Thresholds)
        if (!Schema::hasTable('approval_policies')) {
            Schema::create('approval_policies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('document_type'); // purchase_invoice, expense, sales_proposal, asset_disposal
                $table->unsignedInteger('tier_level')->default(1);
                $table->string('tier_name'); // Operational Officer, Finance Manager, Managing Director
                $table->decimal('min_amount', 15, 2)->default(0.00);
                $table->decimal('max_amount', 15, 2)->nullable(); // null means unbounded
                $table->string('required_role')->nullable();
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->boolean('requires_digital_signature')->default(false);
                $table->boolean('enforce_segregation_of_duties')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->index(['document_type', 'is_active']);
                $table->index(['min_amount', 'max_amount']);
            });
        }

        // 2. Approval Requests (Live Pipeline Instances)
        if (!Schema::hasTable('approval_requests')) {
            Schema::create('approval_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_number')->unique(); // APR-2026-00001
                $table->string('document_type'); // purchase_invoice, expense, sales_proposal, asset_disposal
                $table->unsignedBigInteger('document_id');
                $table->string('document_number'); // INV-2026-00001, EXP-2026-00001, etc.
                $table->string('title');
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->string('currency', 10)->default('TZS');
                $table->unsignedInteger('current_tier')->default(1);
                $table->unsignedInteger('max_tier')->default(1);
                $table->string('status')->default('submitted'); // draft, submitted, under_review, returned, approved, rejected, signed, issued, closed
                $table->unsignedBigInteger('requester_id');
                $table->unsignedBigInteger('current_approver_id')->nullable();
                $table->string('current_approver_role')->nullable();
                $table->string('verification_code', 64)->nullable(); // SHA-256 hash
                $table->timestamp('signed_at')->nullable();
                $table->unsignedBigInteger('signed_by')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id')->default(1);
                $table->unsignedBigInteger('created_by')->default(1);
                $table->timestamps();

                $table->index(['document_type', 'document_id']);
                $table->index(['status', 'requester_id']);
                $table->index('request_number');
            });
        }

        // 3. Approval Actions (Audit Trail & Signature Ledger)
        if (!Schema::hasTable('approval_actions')) {
            Schema::create('approval_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('approval_request_id')->constrained('approval_requests')->onDelete('cascade');
                $table->unsignedInteger('tier_level')->default(1);
                $table->unsignedBigInteger('actor_id');
                $table->string('action'); // submit, review, return, approve, reject, sign, issue, close
                $table->string('previous_status');
                $table->string('new_status');
                $table->text('comments')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('digital_signature')->nullable();
                $table->timestamps();

                $table->index(['approval_request_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_policies');
    }
};
