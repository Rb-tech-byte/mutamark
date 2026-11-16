<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance expenses table with location, payment method, attachment, rejection reason, approved_at
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('expenses', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('category_id');
                }
                if (!Schema::hasColumn('expenses', 'payment_method')) {
                    $table->string('payment_method', 50)->default('bank_transfer')->after('bank_account_id');
                }
                if (!Schema::hasColumn('expenses', 'attachment')) {
                    $table->string('attachment')->nullable()->after('reference_number');
                }
                if (!Schema::hasColumn('expenses', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('status');
                }
                if (!Schema::hasColumn('expenses', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        // 2. Accounting Periods for Period Closing & Lock Enforcement
        if (!Schema::hasTable('accounting_periods')) {
            Schema::create('accounting_periods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index(['created_by', 'status']);
                $table->index(['start_date', 'end_date']);
            });
        }

        // 3. In-App Automation Notifications
        if (!Schema::hasTable('app_notifications')) {
            Schema::create('app_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('type', 80); // low_stock, purchase_due, supplier_payment, customer_payment, pending_approval, stock_transfer_request, stock_transfer_receipt, cash_register_closing, system_activity
                $table->string('title');
                $table->text('message');
                $table->string('severity', 20)->default('info'); // info, warning, critical, success
                $table->string('link')->nullable();
                $table->string('dedupe_key')->nullable();
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['created_by', 'read_at']);
                $table->index(['dedupe_key']);
            });
        }

        // 4. Offline POS Idempotency & Sync Logs
        if (Schema::hasTable('pos') && !Schema::hasColumn('pos', 'client_tx_id')) {
            Schema::table('pos', function (Blueprint $table) {
                $table->string('client_tx_id', 100)->nullable()->unique()->after('sale_number');
            });
        }

        if (!Schema::hasTable('pos_offline_sync_logs')) {
            Schema::create('pos_offline_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->string('client_tx_id', 100);
                $table->unsignedBigInteger('pos_id')->nullable();
                $table->unsignedBigInteger('warehouse_id')->nullable();
                $table->enum('status', ['synced', 'duplicate', 'conflict', 'failed'])->default('synced');
                $table->text('message')->nullable();
                $table->json('payload')->nullable();
                $table->json('conflict_details')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->index(['created_by', 'client_tx_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_offline_sync_logs');
        if (Schema::hasTable('pos') && Schema::hasColumn('pos', 'client_tx_id')) {
            Schema::table('pos', function (Blueprint $table) {
                $table->dropUnique(['client_tx_id']);
                $table->dropColumn('client_tx_id');
            });
        }
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('accounting_periods');
    }
};

