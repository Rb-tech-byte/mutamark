<?php

namespace Database\Seeders;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class MutamarkWmsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Update Warehouses with WMS metadata
        $warehouses = Warehouse::all();
        $whConfigs = [
            1 => ['code' => 'WH-DSM-01', 'type' => 'central', 'manager' => 'Baraka Mrema', 'cbm' => 12500.00, 'temp' => false],
            2 => ['code' => 'WH-ARU-01', 'type' => 'regional_depot', 'manager' => 'Neema Kimaro', 'cbm' => 4800.00, 'temp' => true],
            3 => ['code' => 'WH-ARU-02', 'type' => 'transit_hub', 'manager' => 'Emanuel Mollel', 'cbm' => 2500.00, 'temp' => false],
            4 => ['code' => 'WH-MWZ-01', 'type' => 'regional_depot', 'manager' => 'Juma Maganga', 'cbm' => 3800.00, 'temp' => false],
        ];

        foreach ($warehouses as $wh) {
            $cfg = $whConfigs[$wh->id] ?? [
                'code' => 'WH-' . strtoupper(substr($wh->city ?: 'TZ', 0, 3)) . '-' . sprintf('%02d', $wh->id),
                'type' => 'regional_depot',
                'manager' => 'Warehouse Supervisor',
                'cbm' => 3000.00,
                'temp' => false,
            ];

            $wh->code = $cfg['code'];
            $wh->warehouse_type = $cfg['type'];
            $wh->manager_name = $cfg['manager'];
            $wh->storage_capacity_cbm = $cfg['cbm'];
            $wh->temperature_controlled = $cfg['temp'];
            $wh->save();

            // Create Bin Locations for warehouse if none exist
            if ($wh->locations()->count() === 0) {
                $bins = [
                    ['zone' => 'Zone A - High Value', 'aisle' => 'A-01', 'rack' => 'R-01', 'shelf' => 'S-01', 'bin_code' => 'BIN-A1-R1-S1'],
                    ['zone' => 'Zone A - High Value', 'aisle' => 'A-01', 'rack' => 'R-01', 'shelf' => 'S-02', 'bin_code' => 'BIN-A1-R1-S2'],
                    ['zone' => 'Zone B - Fast Moving', 'aisle' => 'B-02', 'rack' => 'R-02', 'shelf' => 'S-01', 'bin_code' => 'BIN-B2-R2-S1'],
                    ['zone' => 'Zone B - Fast Moving', 'aisle' => 'B-02', 'rack' => 'R-02', 'shelf' => 'S-02', 'bin_code' => 'BIN-B2-R2-S2'],
                    ['zone' => 'Zone C - Bulk Pallets', 'aisle' => 'C-01', 'rack' => 'R-01', 'shelf' => 'S-G', 'bin_code' => 'BIN-C1-PALLET-01'],
                ];

                foreach ($bins as $b) {
                    WarehouseLocation::create([
                        'warehouse_id' => $wh->id,
                        'zone' => $b['zone'],
                        'aisle' => $b['aisle'],
                        'rack' => $b['rack'],
                        'shelf' => $b['shelf'],
                        'bin_code' => $wh->code . '-' . $b['bin_code'],
                        'max_capacity_units' => 1000,
                        'is_active' => true,
                        'creator_id' => $wh->creator_id ?: 2,
                        'created_by' => $wh->created_by ?: 2,
                    ]);
                }
            }
        }

        // 2. Backfill existing transfers
        $existingTransfers = Transfer::all();
        foreach ($existingTransfers as $idx => $t) {
            if (empty($t->transfer_number)) {
                $t->transfer_number = sprintf('STO-2026-%05d', $idx + 1);
            }
            if (empty($t->status)) {
                $t->status = 'received';
            }
            if (empty($t->total_transferred_qty)) {
                $t->total_transferred_qty = $t->quantity;
            }
            if (empty($t->total_items_count)) {
                $t->total_items_count = 1;
            }
            $t->save();

            // Create transfer item row if missing
            if ($t->items()->count() === 0 && $t->product_id) {
                TransferItem::create([
                    'transfer_id' => $t->id,
                    'product_id' => $t->product_id,
                    'requested_quantity' => $t->quantity,
                    'dispatched_quantity' => $t->quantity,
                    'received_quantity' => $t->quantity,
                    'discrepancy_quantity' => 0,
                    'unit_cost' => 150000,
                ]);
            }
        }

        // 3. Create a realistic "In Transit" multi-item transfer order from DSM Central to Arusha Depot
        $dsmWh = Warehouse::firstWhere('code', 'WH-DSM-01') ?: $warehouses->first();
        $aruWh = Warehouse::firstWhere('code', 'WH-ARU-01') ?: ($warehouses->count() > 1 ? $warehouses[1] : $warehouses->first());
        $products = ProductServiceItem::take(3)->get();

        if ($dsmWh && $aruWh && $products->count() > 0) {
            $inTransitTransfer = Transfer::create([
                'transfer_number' => 'STO-2026-00004',
                'from_warehouse' => $dsmWh->id,
                'to_warehouse' => $aruWh->id,
                'product_id' => $products[0]->id,
                'quantity' => 25,
                'date' => now()->toDateString(),
                'status' => 'in_transit',
                'driver_name' => 'Musa Hamisi (East Africa Logistics)',
                'vehicle_number' => 'T 392 DKR',
                'shipping_carrier' => 'Fleet Logistics Ltd',
                'expected_delivery_date' => now()->addDays(2)->toDateString(),
                'dispatched_at' => now()->subHours(6),
                'transit_notes' => 'Expedited overland transfer between Dar es Salaam Central and Arusha Regional Depot via Chalinze highway.',
                'total_items_count' => $products->count(),
                'total_transferred_qty' => 45.00,
                'creator_id' => 2,
                'created_by' => 2,
            ]);

            foreach ($products as $i => $prod) {
                $qty = 15 - ($i * 5); // 15, 10, 5
                TransferItem::create([
                    'transfer_id' => $inTransitTransfer->id,
                    'product_id' => $prod->id,
                    'requested_quantity' => $qty,
                    'dispatched_quantity' => $qty,
                    'received_quantity' => null,
                    'discrepancy_quantity' => 0,
                    'unit_cost' => $prod->purchase_price ?: 120000,
                    'notes' => 'Inspected and loaded in good condition',
                ]);
            }

            // Create a Stock Adjustment Order for cycle counting
            $adjustment = StockAdjustment::create([
                'adjustment_number' => 'ADJ-2026-00001',
                'warehouse_id' => $dsmWh->id,
                'adjustment_type' => 'cycle_count',
                'status' => 'approved',
                'adjustment_date' => now()->subDays(1)->toDateString(),
                'reason' => 'Quarterly physical inventory verification in Zone A aisle shelves.',
                'total_variance_value' => 240000.00,
                'approved_by' => 2,
                'posted_at' => now()->subDay(),
                'creator_id' => 2,
                'created_by' => 2,
            ]);

            foreach ($products as $prod) {
                $sysQty = 50;
                $cntQty = 52;
                $diff = $cntQty - $sysQty;
                $cost = (float)($prod->purchase_price ?: 120000);

                StockAdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'product_id' => $prod->id,
                    'system_quantity' => $sysQty,
                    'counted_quantity' => $cntQty,
                    'difference_quantity' => $diff,
                    'unit_cost' => $cost,
                    'variance_value' => $diff * $cost,
                    'remarks' => 'Found 2 extra sealed units on Upper Shelf S-02 during audit',
                ]);
            }
        }
    }
}
