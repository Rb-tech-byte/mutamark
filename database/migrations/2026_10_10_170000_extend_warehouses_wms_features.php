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
        // 1. Extend warehouses table with WMS features
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('warehouses', 'warehouse_type')) {
                $table->string('warehouse_type')->default('central')->after('code'); // central, regional_depot, retail_branch, transit_hub
            }
            if (!Schema::hasColumn('warehouses', 'manager_name')) {
                $table->string('manager_name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('warehouses', 'storage_capacity_cbm')) {
                $table->decimal('storage_capacity_cbm', 12, 2)->default(500.00)->after('manager_name');
            }
            if (!Schema::hasColumn('warehouses', 'temperature_controlled')) {
                $table->boolean('temperature_controlled')->default(false)->after('storage_capacity_cbm');
            }
        });

        // 2. Warehouse Locations / Bins (Aisle, Rack, Shelf, Bin)
        if (!Schema::hasTable('warehouse_locations')) {
            Schema::create('warehouse_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('zone')->default('Zone A'); // Zone A, Zone B, Cold Storage, Staging
                $table->string('aisle')->nullable();       // A-01, A-02
                $table->string('rack')->nullable();        // R-01, R-02
                $table->string('shelf')->nullable();       // S-01, S-02
                $table->string('bin_code');                // BIN-A01-R01-S01
                $table->integer('max_capacity_units')->default(500);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['warehouse_id', 'bin_code']);
            });
        }

        // 3. Extend transfers table (Stock Transfer Orders: STO-YYYY-#####)
        Schema::table('transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('transfers', 'transfer_number')) {
                $table->string('transfer_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('transfers', 'status')) {
                $table->string('status')->default('completed')->after('date'); // draft, approved, dispatched, in_transit, received, cancelled
            }
            if (!Schema::hasColumn('transfers', 'driver_name')) {
                $table->string('driver_name')->nullable()->after('status');
            }
            if (!Schema::hasColumn('transfers', 'vehicle_number')) {
                $table->string('vehicle_number')->nullable()->after('driver_name');
            }
            if (!Schema::hasColumn('transfers', 'shipping_carrier')) {
                $table->string('shipping_carrier')->nullable()->after('vehicle_number');
            }
            if (!Schema::hasColumn('transfers', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('shipping_carrier');
            }
            if (!Schema::hasColumn('transfers', 'dispatched_at')) {
                $table->dateTime('dispatched_at')->nullable()->after('expected_delivery_date');
            }
            if (!Schema::hasColumn('transfers', 'received_at')) {
                $table->dateTime('received_at')->nullable()->after('dispatched_at');
            }
            if (!Schema::hasColumn('transfers', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('received_at');
            }
            if (!Schema::hasColumn('transfers', 'transit_notes')) {
                $table->text('transit_notes')->nullable()->after('received_by');
            }
            if (!Schema::hasColumn('transfers', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable()->after('transit_notes');
            }
            if (!Schema::hasColumn('transfers', 'total_items_count')) {
                $table->integer('total_items_count')->default(1)->after('inspection_notes');
            }
            if (!Schema::hasColumn('transfers', 'total_transferred_qty')) {
                $table->decimal('total_transferred_qty', 15, 2)->default(0)->after('total_items_count');
            }
            if (!Schema::hasColumn('transfers', 'qr_verification_code')) {
                $table->string('qr_verification_code')->nullable()->after('total_transferred_qty');
            }
        });

        // 4. Stock Transfer Items (multi-item transfers)
        if (!Schema::hasTable('transfer_items')) {
            Schema::create('transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transfer_id')->constrained('transfers')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id');
                $table->decimal('requested_quantity', 15, 2)->default(1);
                $table->decimal('dispatched_quantity', 15, 2)->default(1);
                $table->decimal('received_quantity', 15, 2)->nullable();
                $table->decimal('discrepancy_quantity', 15, 2)->default(0);
                $table->unsignedBigInteger('from_bin_id')->nullable();
                $table->unsignedBigInteger('to_bin_id')->nullable();
                $table->decimal('unit_cost', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['transfer_id', 'product_id']);
            });
        }

        // 5. Stock Adjustments & Cycle Counting
        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->string('adjustment_number')->unique(); // ADJ-YYYY-#####
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('adjustment_type')->default('cycle_count'); // cycle_count, damage_spoilage, theft_loss, revaluation, opening_balance
                $table->string('status')->default('draft'); // draft, approved, posted
                $table->date('adjustment_date');
                $table->text('reason')->nullable();
                $table->decimal('total_variance_value', 15, 2)->default(0);
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->dateTime('posted_at')->nullable();
                $table->unsignedBigInteger('creator_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['warehouse_id', 'status']);
            });
        }

        // 6. Stock Adjustment Items
        if (!Schema::hasTable('stock_adjustment_items')) {
            Schema::create('stock_adjustment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id');
                $table->decimal('system_quantity', 15, 2)->default(0);
                $table->decimal('counted_quantity', 15, 2)->default(0);
                $table->decimal('difference_quantity', 15, 2)->default(0); // counted - system
                $table->decimal('unit_cost', 15, 2)->default(0);
                $table->decimal('variance_value', 15, 2)->default(0); // difference * unit_cost
                $table->string('remarks')->nullable();
                $table->timestamps();

                $table->index(['adjustment_id', 'product_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('warehouse_locations');

        Schema::table('transfers', function (Blueprint $table) {
            $table->dropColumn([
                'transfer_number', 'status', 'driver_name', 'vehicle_number',
                'shipping_carrier', 'expected_delivery_date', 'dispatched_at',
                'received_at', 'received_by', 'transit_notes', 'inspection_notes',
                'total_items_count', 'total_transferred_qty', 'qr_verification_code'
            ]);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'warehouse_type', 'manager_name',
                'storage_capacity_cbm', 'temperature_controlled'
            ]);
        });
    }
};
