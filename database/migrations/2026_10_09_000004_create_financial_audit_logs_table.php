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
        Schema::dropIfExists('financial_audit_logs');

        Schema::create('financial_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number', 60)->unique();
            $table->string('event_type', 50)->index(); // INVOICE_LOCKED, IMMUTABILITY_BLOCKED, JOURNAL_POSTED, JOURNAL_REVERSED, CREDIT_NOTE_ISSUED, NEGATIVE_STOCK_BLOCKED, TAMPER_DETECTED
            $table->string('module', 50)->index(); // Accounting, Sales, Procurement, Inventory, Payroll
            $table->string('reference_type', 80)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number', 80)->index();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name', 120);
            $table->string('actor_role', 60)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action', 255);
            $table->text('details')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('severity', 30)->default('info')->index(); // info, warning, critical, compliance_alert
            $table->string('previous_hash', 64)->nullable();
            $table->string('current_hash', 64)->index();
            $table->boolean('is_verified')->default(true);
            $table->unsignedBigInteger('created_by')->default(2);
            $table->unsignedBigInteger('creator_id')->default(2);
            $table->timestamps();

            $table->index(['event_type', 'severity']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_audit_logs');
    }
};
