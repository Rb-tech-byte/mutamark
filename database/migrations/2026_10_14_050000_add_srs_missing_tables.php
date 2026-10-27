<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ─── Central Document Management System (SRS §3.22) ──────────────
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->string('document_number')->unique();
                $table->string('title');
                $table->string('category')->nullable();         // contract, certificate, ID, invoice, etc.
                $table->string('entity_type')->nullable();      // customer, vendor, employee, product, etc.
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('file_path');
                $table->string('file_name');
                $table->string('file_type')->nullable();        // pdf, docx, jpg, etc.
                $table->unsignedBigInteger('file_size')->default(0);
                $table->text('description')->nullable();
                $table->string('tags')->nullable();
                $table->string('access_level')->default('company');  // company, branch, private
                $table->date('expiry_date')->nullable();
                $table->boolean('is_confidential')->default(false);
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index(['entity_type', 'entity_id']);
                $table->index('created_by');
                $table->index('expiry_date');
            });
        }

        // ─── Document Versions (SRS §3.22.3) ─────────────────────────────
        if (!Schema::hasTable('document_versions')) {
            Schema::create('document_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('document_id');
                $table->unsignedInteger('version_number')->default(1);
                $table->string('file_path');
                $table->string('file_name');
                $table->unsignedBigInteger('file_size')->default(0);
                $table->text('change_notes')->nullable();
                $table->unsignedBigInteger('uploaded_by');
                $table->timestamps();
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            });
        }

        // ─── Proforma Invoices (SRS §3.9.3) ──────────────────────────────
        if (!Schema::hasTable('proforma_invoices')) {
            Schema::create('proforma_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('proforma_number')->unique();
                $table->date('issue_date');
                $table->date('expiry_date')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_name');
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->string('customer_address')->nullable();
                $table->string('currency')->default('TZS');
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->string('status')->default('draft'); // draft, sent, accepted, converted, cancelled
                $table->unsignedBigInteger('converted_to_invoice_id')->nullable();
                $table->string('payment_terms')->nullable();
                $table->text('notes')->nullable();
                $table->string('verification_hash')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index('customer_id');
                $table->index('created_by');
                $table->index('status');
            });
        }

        // ─── Proforma Invoice Items ───────────────────────────────────────
        if (!Schema::hasTable('proforma_invoice_items')) {
            Schema::create('proforma_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('proforma_invoice_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->string('sku')->nullable();
                $table->string('unit')->nullable();
                $table->decimal('quantity', 15, 4)->default(1);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->foreign('proforma_invoice_id')->references('id')->on('proforma_invoices')->onDelete('cascade');
            });
        }

        // ─── Purchase Orders (SRS §3.8.4) ────────────────────────────────
        // Table already exists — just add missing columns if needed
        if (Schema::hasTable('purchase_orders')) {
            $existing = Schema::getColumnListing('purchase_orders');

            Schema::table('purchase_orders', function (Blueprint $table) use ($existing) {
                if (!in_array('approved_by', $existing)) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('status');
                }
                if (!in_array('approved_at', $existing)) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!in_array('cancelled_by', $existing)) {
                    $table->unsignedBigInteger('cancelled_by')->nullable();
                }
                if (!in_array('cancelled_at', $existing)) {
                    $table->timestamp('cancelled_at')->nullable();
                }
                if (!in_array('cancellation_reason', $existing)) {
                    $table->text('cancellation_reason')->nullable();
                }
            });
        }

        // ─── Portal Authentication tokens (SRS §3.27 / §3.28) ───────────
        if (!Schema::hasTable('portal_access_tokens')) {
            Schema::create('portal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('portal_type'); // customer, supplier
                $table->unsignedBigInteger('entity_id'); // customer_id or vendor_id
                $table->string('entity_name');
                $table->string('email');
                $table->string('token', 80)->unique();
                $table->string('pin', 6)->nullable();         // Optional PIN for extra security
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index(['portal_type', 'entity_id']);
                $table->index('token');
            });
        }

        // ─── Warranty Expiry Alerts log ───────────────────────────────────
        if (!Schema::hasTable('warranty_expiry_alerts')) {
            Schema::create('warranty_expiry_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('warranty_id');
                $table->string('alert_type'); // 30_days, 7_days, expired
                $table->string('channel'); // email, sms, in_app
                $table->boolean('sent')->default(false);
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->index('warranty_id');
                $table->index(['sent', 'alert_type']);
            });
        }

        // ─── Stocktake / Physical Count (SRS §3.7.3) ─────────────────────
        if (!Schema::hasTable('stocktake_sessions')) {
            Schema::create('stocktake_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_number')->unique();
                $table->unsignedBigInteger('warehouse_id');
                $table->date('count_date');
                $table->string('status')->default('in_progress'); // in_progress, completed, posted
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('completed_by')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stocktake_items')) {
            Schema::create('stocktake_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stocktake_session_id');
                $table->unsignedBigInteger('product_id');
                $table->string('serial_number')->nullable();
                $table->string('barcode')->nullable();
                $table->decimal('system_quantity', 15, 4)->default(0);
                $table->decimal('counted_quantity', 15, 4)->nullable();
                $table->decimal('variance', 15, 4)->nullable();
                $table->string('variance_reason')->nullable();
                $table->string('status')->default('pending'); // pending, counted, verified
                $table->timestamps();
                $table->foreign('stocktake_session_id')->references('id')->on('stocktake_sessions')->onDelete('cascade');
                $table->index('product_id');
            });
        }

        // ─── Withholding Tax (SRS §3.12.1) ───────────────────────────────
        if (!Schema::hasTable('withholding_taxes')) {
            Schema::create('withholding_taxes', function (Blueprint $table) {
                $table->id();
                $table->string('wht_number')->unique();
                $table->string('reference_type'); // vendor_payment, customer_payment
                $table->unsignedBigInteger('reference_id');
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->decimal('gross_amount', 15, 2)->default(0);
                $table->decimal('wht_rate', 5, 2)->default(0);
                $table->decimal('wht_amount', 15, 2)->default(0);
                $table->decimal('net_payable', 15, 2)->default(0);
                $table->string('status')->default('pending'); // pending, remitted
                $table->date('payment_date')->nullable();
                $table->date('remittance_date')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index(['reference_type', 'reference_id']);
            });
        }

        // ─── Approval Delegation (SRS §3.24.2) ───────────────────────────
        if (!Schema::hasTable('approval_delegations')) {
            Schema::create('approval_delegations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delegator_id');   // who is delegating
                $table->unsignedBigInteger('delegate_id');    // who receives authority
                $table->date('start_date');
                $table->date('end_date');
                $table->string('document_types')->nullable(); // comma-separated or JSON, null = all
                $table->string('reason')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index(['delegator_id', 'is_active']);
                $table->index(['delegate_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('withholding_taxes');
        Schema::dropIfExists('stocktake_items');
        Schema::dropIfExists('stocktake_sessions');
        Schema::dropIfExists('warranty_expiry_alerts');
        Schema::dropIfExists('portal_access_tokens');
        Schema::dropIfExists('proforma_invoice_items');
        Schema::dropIfExists('proforma_invoices');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
