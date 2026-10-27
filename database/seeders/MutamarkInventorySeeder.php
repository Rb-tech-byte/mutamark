<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Transfer;
use App\Models\InventorySerialNumber;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class MutamarkInventorySeeder extends Seeder
{
    public function run(): void
    {
        $company = User::where('type', 'company')->first();
        if (!$company) {
            $this->command->error('No company user found.');
            return;
        }

        $userId = $company->id;

        // 1. Seed / Update 3 Primary Tanzanian Warehouses
        $dsm = Warehouse::updateOrCreate(
            ['name' => 'Dar es Salaam Central Warehouse', 'created_by' => $userId],
            [
                'address' => 'Plot 44, Kurasini Harbour Industrial Area, Bandari Road',
                'city' => 'Dar es Salaam',
                'zip_code' => '11101',
                'phone' => '+255 22 286 1000',
                'email' => 'warehouse.dsm@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $userId,
            ]
        );

        $arusha = Warehouse::updateOrCreate(
            ['name' => 'Arusha Regional Distribution Hub', 'created_by' => $userId],
            [
                'address' => 'Plot 12B, Themi Industrial Estate, Old Moshi Road',
                'city' => 'Arusha',
                'zip_code' => '23100',
                'phone' => '+255 27 254 3400',
                'email' => 'warehouse.arusha@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $userId,
            ]
        );

        $mwanza = Warehouse::updateOrCreate(
            ['name' => 'Mwanza Lake-Zone Depot', 'created_by' => $userId],
            [
                'address' => 'Plot 88, Nyakato Industrial Area, Kenyatta Road',
                'city' => 'Mwanza',
                'zip_code' => '33100',
                'phone' => '+255 28 250 0900',
                'email' => 'warehouse.mwanza@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $userId,
            ]
        );

        // 2. Ensure Additional Hardware Items
        $router = ProductServiceItem::updateOrCreate(
            ['sku' => 'MUT-HW-RTR5G', 'created_by' => $userId],
            [
                'name' => 'Industrial 4G/5G Enterprise Gateway Router',
                'type' => 'product',
                'category_id' => 1,
                'sale_price' => 750000.00,
                'purchase_price' => 520000.00,
                'unit' => 'pcs',
                'description' => 'Dual SIM industrial cellular gateway router with failover for remote branches and warehouses.',
                'is_active' => true,
                'tax_ids' => json_encode([1, 2]),
                'creator_id' => $userId,
            ]
        );

        $patchPanel = ProductServiceItem::updateOrCreate(
            ['sku' => 'MUT-HW-FIB48', 'created_by' => $userId],
            [
                'name' => 'High-Density 48-Port Fiber Distribution Panel',
                'type' => 'product',
                'category_id' => 1,
                'sale_price' => 420000.00,
                'purchase_price' => 290000.00,
                'unit' => 'pcs',
                'description' => 'Rackmount 1U fiber optical terminal patch panel for datacenter distribution frames.',
                'is_active' => true,
                'tax_ids' => json_encode([1, 2]),
                'creator_id' => $userId,
            ]
        );

        // 3. Clear & Populate Warehouse Stocks across all 3 Warehouses
        DB::table('warehouse_stocks')->delete();

        $stockMatrix = [
            // Product 2: Barcode Scanners
            ['product_id' => 2, 'warehouse_id' => $dsm->id, 'quantity' => 45],
            ['product_id' => 2, 'warehouse_id' => $arusha->id, 'quantity' => 20],
            ['product_id' => 2, 'warehouse_id' => $mwanza->id, 'quantity' => 15],

            // Product 3: Thermal Receipt Printers
            ['product_id' => 3, 'warehouse_id' => $dsm->id, 'quantity' => 35],
            ['product_id' => 3, 'warehouse_id' => $arusha->id, 'quantity' => 12],
            ['product_id' => 3, 'warehouse_id' => $mwanza->id, 'quantity' => 18],

            // Product 6: 4G/5G Routers
            ['product_id' => $router->id, 'warehouse_id' => $dsm->id, 'quantity' => 25],
            ['product_id' => $router->id, 'warehouse_id' => $arusha->id, 'quantity' => 10],
            ['product_id' => $router->id, 'warehouse_id' => $mwanza->id, 'quantity' => 8],

            // Product 7: Fiber Patch Panels
            ['product_id' => $patchPanel->id, 'warehouse_id' => $dsm->id, 'quantity' => 30],
            ['product_id' => $patchPanel->id, 'warehouse_id' => $arusha->id, 'quantity' => 8],
            ['product_id' => $patchPanel->id, 'warehouse_id' => $mwanza->id, 'quantity' => 10],
        ];

        foreach ($stockMatrix as $stk) {
            WarehouseStock::create(array_merge($stk, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 4. Seed Inter-Warehouse Stock Transfers
        DB::table('transfers')->where('created_by', $userId)->delete();

        $transfers = [
            [
                'from_warehouse' => $dsm->id,
                'to_warehouse' => $arusha->id,
                'product_id' => 2, // Barcode Scanner
                'quantity' => 15.00,
                'date' => '2026-10-04',
                'creator_id' => $userId,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'from_warehouse' => $dsm->id,
                'to_warehouse' => $mwanza->id,
                'product_id' => 3, // Thermal Printer
                'quantity' => 10.00,
                'date' => '2026-10-06',
                'creator_id' => $userId,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'from_warehouse' => $arusha->id,
                'to_warehouse' => $mwanza->id,
                'product_id' => 2, // Barcode Scanner
                'quantity' => 5.00,
                'date' => '2026-10-08',
                'creator_id' => $userId,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('transfers')->insert($transfers);

        // 5. Seed Serial Number & Batch Tracking Items
        DB::table('inventory_serial_numbers')->where('created_by', $userId)->delete();

        $serialsData = [
            // Barcode Scanners - Batch 1
            [
                'serial_number' => 'MUT-SC-261001',
                'batch_number' => 'LOT-2026-SC01',
                'product_id' => 2,
                'warehouse_id' => $dsm->id,
                'status' => 'in_stock',
                'purchase_cost' => 250000.00,
                'selling_price' => 350000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTSC261001',
                'notes' => 'QA Passed. Firmware v2.4.1 flashed.',
            ],
            [
                'serial_number' => 'MUT-SC-261002',
                'batch_number' => 'LOT-2026-SC01',
                'product_id' => 2,
                'warehouse_id' => $dsm->id,
                'status' => 'allocated',
                'purchase_cost' => 250000.00,
                'selling_price' => 350000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTSC261002',
                'notes' => 'Allocated for Safari Holdings dispatch.',
            ],
            [
                'serial_number' => 'MUT-SC-261003',
                'batch_number' => 'LOT-2026-SC01',
                'product_id' => 2,
                'warehouse_id' => $arusha->id,
                'status' => 'in_stock',
                'purchase_cost' => 250000.00,
                'selling_price' => 350000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTSC261003',
                'notes' => 'Arusha northern zone safari client stock.',
            ],
            [
                'serial_number' => 'MUT-SC-261004',
                'batch_number' => 'LOT-2026-SC01',
                'product_id' => 2,
                'warehouse_id' => $mwanza->id,
                'status' => 'in_stock',
                'purchase_cost' => 250000.00,
                'selling_price' => 350000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTSC261004',
                'notes' => 'Transferred to Mwanza Lake-Zone depot.',
            ],
            [
                'serial_number' => 'MUT-SC-261005',
                'batch_number' => 'LOT-2026-SC01',
                'product_id' => 2,
                'warehouse_id' => $dsm->id,
                'status' => 'defective',
                'purchase_cost' => 250000.00,
                'selling_price' => 350000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTSC261005',
                'notes' => 'Trigger sensor loose on port unboxing. Sent to vendor RMA.',
            ],

            // Thermal Printers - Batch 2
            [
                'serial_number' => 'MUT-PR-262001',
                'batch_number' => 'LOT-2026-PR02',
                'product_id' => 3,
                'warehouse_id' => $dsm->id,
                'status' => 'in_stock',
                'purchase_cost' => 190000.00,
                'selling_price' => 280000.00,
                'warranty_months' => 12,
                'warranty_expiry_date' => '2027-10-01',
                'barcode' => 'MUTPR262001',
                'notes' => 'Thermal head tested, print speed 250mm/s.',
            ],
            [
                'serial_number' => 'MUT-PR-262002',
                'batch_number' => 'LOT-2026-PR02',
                'product_id' => 3,
                'warehouse_id' => $dsm->id,
                'status' => 'dispatched',
                'purchase_cost' => 190000.00,
                'selling_price' => 280000.00,
                'warranty_months' => 12,
                'warranty_expiry_date' => '2027-10-01',
                'barcode' => 'MUTPR262002',
                'notes' => 'Delivered to Kilimanjaro Logistics main office.',
            ],
            [
                'serial_number' => 'MUT-PR-262003',
                'batch_number' => 'LOT-2026-PR02',
                'product_id' => 3,
                'warehouse_id' => $arusha->id,
                'status' => 'in_stock',
                'purchase_cost' => 190000.00,
                'selling_price' => 280000.00,
                'warranty_months' => 12,
                'warranty_expiry_date' => '2027-10-01',
                'barcode' => 'MUTPR262003',
                'notes' => 'In storage at Arusha regional depot.',
            ],
            [
                'serial_number' => 'MUT-PR-262004',
                'batch_number' => 'LOT-2026-PR02',
                'product_id' => 3,
                'warehouse_id' => $mwanza->id,
                'status' => 'in_stock',
                'purchase_cost' => 190000.00,
                'selling_price' => 280000.00,
                'warranty_months' => 12,
                'warranty_expiry_date' => '2027-10-01',
                'barcode' => 'MUTPR262004',
                'notes' => 'Retail POS ready stock in Mwanza.',
            ],

            // 4G/5G Gateway Routers - Batch 3
            [
                'serial_number' => 'MUT-RT-263001',
                'batch_number' => 'LOT-2026-RT03',
                'product_id' => $router->id,
                'warehouse_id' => $dsm->id,
                'status' => 'in_stock',
                'purchase_cost' => 520000.00,
                'selling_price' => 750000.00,
                'warranty_months' => 36,
                'warranty_expiry_date' => '2029-10-01',
                'barcode' => 'MUTRT263001',
                'notes' => 'Vodacom & Airtel Tanzania Dual-APN pre-configured.',
            ],
            [
                'serial_number' => 'MUT-RT-263002',
                'batch_number' => 'LOT-2026-RT03',
                'product_id' => $router->id,
                'warehouse_id' => $dsm->id,
                'status' => 'allocated',
                'purchase_cost' => 520000.00,
                'selling_price' => 750000.00,
                'warranty_months' => 36,
                'warranty_expiry_date' => '2029-10-01',
                'barcode' => 'MUTRT263002',
                'notes' => 'Assigned for Serengeti Breweries factory floor IoT installation.',
            ],
            [
                'serial_number' => 'MUT-RT-263003',
                'batch_number' => 'LOT-2026-RT03',
                'product_id' => $router->id,
                'warehouse_id' => $arusha->id,
                'status' => 'in_stock',
                'purchase_cost' => 520000.00,
                'selling_price' => 750000.00,
                'warranty_months' => 36,
                'warranty_expiry_date' => '2029-10-01',
                'barcode' => 'MUTRT263003',
                'notes' => 'Available for Northern Tanzania branch expansions.',
            ],
            [
                'serial_number' => 'MUT-RT-263004',
                'batch_number' => 'LOT-2026-RT03',
                'product_id' => $router->id,
                'warehouse_id' => $mwanza->id,
                'status' => 'in_stock',
                'purchase_cost' => 520000.00,
                'selling_price' => 750000.00,
                'warranty_months' => 36,
                'warranty_expiry_date' => '2029-10-01',
                'barcode' => 'MUTRT263004',
                'notes' => 'Mwanza Lake-Zone depot telecom reserve.',
            ],

            // 48-Port Fiber Distribution Panels - Batch 4
            [
                'serial_number' => 'MUT-FP-264001',
                'batch_number' => 'LOT-2026-FP04',
                'product_id' => $patchPanel->id,
                'warehouse_id' => $dsm->id,
                'status' => 'in_stock',
                'purchase_cost' => 290000.00,
                'selling_price' => 420000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTFP264001',
                'notes' => 'SC/UPC simplex couplers pre-installed with splice trays.',
            ],
            [
                'serial_number' => 'MUT-FP-264002',
                'batch_number' => 'LOT-2026-FP04',
                'product_id' => $patchPanel->id,
                'warehouse_id' => $dsm->id,
                'status' => 'delivered',
                'purchase_cost' => 290000.00,
                'selling_price' => 420000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTFP264002',
                'notes' => 'Delivered to Zanzibar Spice Traders datacenter.',
            ],
            [
                'serial_number' => 'MUT-FP-264003',
                'batch_number' => 'LOT-2026-FP04',
                'product_id' => $patchPanel->id,
                'warehouse_id' => $arusha->id,
                'status' => 'in_stock',
                'purchase_cost' => 290000.00,
                'selling_price' => 420000.00,
                'warranty_months' => 24,
                'warranty_expiry_date' => '2028-10-01',
                'barcode' => 'MUTFP264003',
                'notes' => 'Arusha Themi datacenter installation stock.',
            ],
        ];

        foreach ($serialsData as $s) {
            InventorySerialNumber::create(array_merge($s, [
                'creator_id' => $userId,
                'created_by' => $userId,
            ]));
        }

        $this->command->info('Mutamark Inventory, Multi-Warehouse & Serial/Batch Tracking data seeded successfully.');
    }
}
