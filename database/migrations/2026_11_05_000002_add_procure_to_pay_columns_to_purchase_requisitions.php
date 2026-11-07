<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to align purchase_requisitions and items columns across environments.
     */
    public function up(): void
    {
        if (Schema::hasTable('purchase_requisitions')) {
            Schema::table('purchase_requisitions', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_requisitions', 'department_name')) {
                    $table->string('department_name', 100)->default('Operations')->after('requester_id');
                }
                if (!Schema::hasColumn('purchase_requisitions', 'total_estimated_amount')) {
                    $table->decimal('total_estimated_amount', 15, 2)->default(0.00)->after('status');
                }
                if (!Schema::hasColumn('purchase_requisitions', 'purpose')) {
                    $table->text('purpose')->nullable()->after('total_estimated_amount');
                }
                if (!Schema::hasColumn('purchase_requisitions', 'budget_reference')) {
                    $table->string('budget_reference', 100)->nullable()->after('purpose');
                }
                if (!Schema::hasColumn('purchase_requisitions', 'approval_notes')) {
                    $table->text('approval_notes')->nullable()->after('approved_at');
                }
            });

            // Backfill from legacy columns if they exist
            if (Schema::hasColumn('purchase_requisitions', 'department') && Schema::hasColumn('purchase_requisitions', 'department_name')) {
                DB::statement("UPDATE purchase_requisitions SET department_name = department WHERE (department_name IS NULL OR department_name = 'Operations') AND department IS NOT NULL");
            }
            if (Schema::hasColumn('purchase_requisitions', 'estimated_total_cost') && Schema::hasColumn('purchase_requisitions', 'total_estimated_amount')) {
                DB::statement("UPDATE purchase_requisitions SET total_estimated_amount = estimated_total_cost WHERE (total_estimated_amount = 0 OR total_estimated_amount IS NULL) AND estimated_total_cost > 0");
            }
            if (Schema::hasColumn('purchase_requisitions', 'justification') && Schema::hasColumn('purchase_requisitions', 'purpose')) {
                DB::statement("UPDATE purchase_requisitions SET purpose = justification WHERE purpose IS NULL AND justification IS NOT NULL");
            }
            if (Schema::hasColumn('purchase_requisitions', 'rejection_reason') && Schema::hasColumn('purchase_requisitions', 'approval_notes')) {
                DB::statement("UPDATE purchase_requisitions SET approval_notes = rejection_reason WHERE approval_notes IS NULL AND rejection_reason IS NOT NULL");
            }
        }

        if (Schema::hasTable('purchase_requisition_items')) {
            Schema::table('purchase_requisition_items', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_requisition_items', 'quantity')) {
                    $table->decimal('quantity', 12, 2)->default(1.00)->after('sku');
                }
                if (!Schema::hasColumn('purchase_requisition_items', 'estimated_unit_price')) {
                    $table->decimal('estimated_unit_price', 15, 2)->default(0.00)->after('quantity');
                }
                if (!Schema::hasColumn('purchase_requisition_items', 'estimated_total_price')) {
                    $table->decimal('estimated_total_price', 15, 2)->default(0.00)->after('estimated_unit_price');
                }
                if (!Schema::hasColumn('purchase_requisition_items', 'description')) {
                    $table->text('description')->nullable()->after('estimated_total_price');
                }
            });

            // Backfill from legacy columns if they exist
            if (Schema::hasColumn('purchase_requisition_items', 'quantity_requested') && Schema::hasColumn('purchase_requisition_items', 'quantity')) {
                DB::statement("UPDATE purchase_requisition_items SET quantity = quantity_requested WHERE (quantity = 1 OR quantity IS NULL) AND quantity_requested > 0");
            }
            if (Schema::hasColumn('purchase_requisition_items', 'estimated_unit_cost') && Schema::hasColumn('purchase_requisition_items', 'estimated_unit_price')) {
                DB::statement("UPDATE purchase_requisition_items SET estimated_unit_price = estimated_unit_cost WHERE (estimated_unit_price = 0 OR estimated_unit_price IS NULL) AND estimated_unit_cost > 0");
            }
            if (Schema::hasColumn('purchase_requisition_items', 'estimated_total_cost') && Schema::hasColumn('purchase_requisition_items', 'estimated_total_price')) {
                DB::statement("UPDATE purchase_requisition_items SET estimated_total_price = estimated_total_cost WHERE (estimated_total_price = 0 OR estimated_total_price IS NULL) AND estimated_total_cost > 0");
            }
            if (Schema::hasColumn('purchase_requisition_items', 'specifications') && Schema::hasColumn('purchase_requisition_items', 'description')) {
                DB::statement("UPDATE purchase_requisition_items SET description = specifications WHERE description IS NULL AND specifications IS NOT NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op to prevent column drops on live data
    }
};

