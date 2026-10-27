<?php

namespace Database\Seeders;

use App\Models\GoodsReceivedNote;
use App\Models\GrnItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Workdo\Account\Models\Vendor;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class MutamarkProcurementSeeder extends Seeder
{
    /**
     * Run the database seeds for Mutamark Procurement & 3-Way Matching.
     */
    public function run(): void
    {
        $creatorId = 2; // Company account

        // 1. Ensure Warehouses exist
        $dsmWarehouse = Warehouse::where('name', 'Dar es Salaam Central Warehouse')->first();
        if (!$dsmWarehouse) {
            $dsmWarehouse = Warehouse::firstOrCreate(['name' => 'Dar es Salaam Central Warehouse'], [
                'address' => 'Plot 45, Nyerere Road, Ilala Industrial Area',
                'city' => 'Dar es Salaam',
                'zip_code' => '11101',
                'phone' => '+255 22 211 4455',
                'email' => 'warehouse.dsm@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);
        }

        $arushaWarehouse = Warehouse::where('name', 'Arusha Regional Distribution Hub')->first() ?? $dsmWarehouse;
        $mwanzaWarehouse = Warehouse::where('name', 'Mwanza Lake-Zone Depot')->first() ?? $dsmWarehouse;

        // 2. Ensure Products exist
        $scanner = ProductServiceItem::where('sku', 'MUT-HW-BCSCAN')->first();
        if (!$scanner) {
            $scanner = ProductServiceItem::create([
                'name' => 'High-Capacity Industrial Barcode Scanner',
                'sku' => 'MUT-HW-BCSCAN',
                'sale_price' => 350000,
                'purchase_price' => 180000,
                'quantity' => 20,
                'type' => 'product',
                'category_id' => 1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);
        }

        $router = ProductServiceItem::where('sku', 'MUT-HW-RTR5G')->first();
        if (!$router) {
            $router = ProductServiceItem::create([
                'name' => 'Enterprise 4G/5G Gateway Router',
                'sku' => 'MUT-HW-RTR5G',
                'sale_price' => 850000,
                'purchase_price' => 450000,
                'quantity' => 15,
                'type' => 'product',
                'category_id' => 1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);
        }

        $fiberPanel = ProductServiceItem::where('sku', 'MUT-HW-FIB48')->first();
        if (!$fiberPanel) {
            $fiberPanel = ProductServiceItem::create([
                'name' => 'High-Density 48-Port Fiber Distribution Panel',
                'sku' => 'MUT-HW-FIB48',
                'sale_price' => 450000,
                'purchase_price' => 250000,
                'quantity' => 10,
                'type' => 'product',
                'category_id' => 1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);
        }

        // 3. Create or Fetch Realistic Tanzanian Telecom Vendors
        $vendorData = [
            [
                'name' => 'Simba Telecom Supplies Ltd',
                'email' => 'sales@simbatelecom.co.tz',
                'contact_name' => 'Juma Bakari',
                'phone' => '+255 754 112 233',
                'tax_number' => '110-384-921',
                'address' => 'Msimbazi Street, Kariakoo, Dar es Salaam',
                'vendor_code' => 'VEND-2026-0001',
            ],
            [
                'name' => 'Kilombero Optical Networks Co.',
                'email' => 'info@kilombero-optics.co.tz',
                'contact_name' => 'Neema Mrosso',
                'phone' => '+255 768 445 566',
                'tax_number' => '124-902-118',
                'address' => 'Plot 12, Mwenge Industrial Area, Dar es Salaam',
                'vendor_code' => 'VEND-2026-0002',
            ],
            [
                'name' => 'Tanzania Power & Inverter Systems',
                'email' => 'support@tzpowerinverters.co.tz',
                'contact_name' => 'Godfrey Tarimo',
                'phone' => '+255 713 778 899',
                'tax_number' => '135-441-209',
                'address' => 'Themi Industrial Estate, Arusha',
                'vendor_code' => 'VEND-2026-0003',
            ],
        ];

        $vendors = [];
        foreach ($vendorData as $vd) {
            $user = User::firstOrCreate(['email' => $vd['email']], [
                'name' => $vd['name'],
                'password' => Hash::make('password123'),
                'type' => 'vendor',
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            $vendor = Vendor::firstOrCreate(['vendor_code' => $vd['vendor_code']], [
                'user_id' => $user->id,
                'company_name' => $vd['name'],
                'contact_person_name' => $vd['contact_name'],
                'contact_person_email' => $vd['email'],
                'contact_person_mobile' => $vd['phone'],
                'tax_number' => $vd['tax_number'],
                'payment_terms' => 'Net 30 Days',
                'billing_address' => $vd['address'],
                'shipping_address' => $vd['address'],
                'same_as_billing' => true,
                'notes' => 'Preferred tier-1 hardware distributor in Tanzania.',
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            $vendors[$vd['vendor_code']] = $vendor;
        }

        // 4. Create Linked Purchase Invoices / Bills for 3-Way Matching
        // Bill 1: Simba Telecom - 20 Barcode Scanners at 180,000 TZS
        $bill1 = PurchaseInvoice::firstOrCreate(['invoice_number' => 'INV-2026-00001'], [
            'invoice_date' => '2026-10-01',
            'due_date' => '2026-10-31',
            'vendor_id' => $vendors['VEND-2026-0001']->user_id,
            'warehouse_id' => $dsmWarehouse->id,
            'subtotal' => 3600000,
            'tax_amount' => 648000, // 18% VAT
            'discount_amount' => 0,
            'total_amount' => 4248000,
            'paid_amount' => 4248000,
            'balance_amount' => 0,
            'status' => 'paid',
            'payment_terms' => 'Net 30',
            'notes' => 'Supply of 20 industrial barcode scanners for Mutamark logistics hubs.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        PurchaseInvoiceItem::firstOrCreate([
            'invoice_id' => $bill1->id,
            'product_id' => $scanner->id,
        ], [
            'quantity' => 20,
            'unit_price' => 180000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 18,
            'tax_amount' => 648000,
            'total_amount' => 4248000,
        ]);

        // Bill 2: Kilombero Optical Networks - Billed 10 Fiber Panels at 250,000 TZS
        $bill2 = PurchaseInvoice::firstOrCreate(['invoice_number' => 'INV-2026-00002'], [
            'invoice_date' => '2026-10-03',
            'due_date' => '2026-11-02',
            'vendor_id' => $vendors['VEND-2026-0002']->user_id,
            'warehouse_id' => $arushaWarehouse->id,
            'subtotal' => 2500000,
            'tax_amount' => 450000, // 18% VAT
            'discount_amount' => 0,
            'total_amount' => 2950000,
            'paid_amount' => 0,
            'balance_amount' => 2950000,
            'status' => 'posted',
            'payment_terms' => 'Net 30',
            'notes' => 'Supply of 10 48-port fiber panels. 3-way match on hold due to 2 damaged units.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        PurchaseInvoiceItem::firstOrCreate([
            'invoice_id' => $bill2->id,
            'product_id' => $fiberPanel->id,
        ], [
            'quantity' => 10,
            'unit_price' => 250000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 18,
            'tax_amount' => 450000,
            'total_amount' => 2950000,
        ]);

        // 5. Seed Goods Received Notes (GRN)

        // GRN 1: 100% MATCHED (PO-2026-00001 vs GRN-2026-00001 vs INV-2026-00001)
        $grn1 = GoodsReceivedNote::firstOrCreate(['grn_number' => 'GRN-2026-00001'], [
            'purchase_order_number' => 'PO-2026-00001',
            'purchase_invoice_id' => $bill1->id,
            'vendor_id' => $vendors['VEND-2026-0001']->id,
            'warehouse_id' => $dsmWarehouse->id,
            'grn_date' => '2026-10-02',
            'delivery_challan_number' => 'DC-SIMBA-8812',
            'carrier_name' => 'Simba Cargo & Logistics',
            'vehicle_number' => 'T 482 DXC',
            'received_by' => $creatorId,
            'inspection_status' => 'inspected_passed',
            'matching_status' => 'matched',
            'total_ordered_qty' => 20,
            'total_received_qty' => 20,
            'total_accepted_qty' => 20,
            'total_rejected_qty' => 0,
            'total_amount' => 3600000,
            'quality_inspection_notes' => 'All 20 industrial barcode scanners passed functional test, optics check, and battery voltage inspection. Approved for deployment.',
            'status' => 'posted',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        GrnItem::firstOrCreate([
            'grn_id' => $grn1->id,
            'product_id' => $scanner->id,
        ], [
            'ordered_quantity' => 20,
            'received_quantity' => 20,
            'accepted_quantity' => 20,
            'rejected_quantity' => 0,
            'unit_price' => 180000,
            'total_amount' => 3600000,
            'batch_number' => 'LOT-2026-SC01',
        ]);

        // GRN 2: VARIANCE DETECTED (PO-2026-00002: Ordered 10, Accepted 8, Rejected 2 vs Billed 10)
        $grn2 = GoodsReceivedNote::firstOrCreate(['grn_number' => 'GRN-2026-00002'], [
            'purchase_order_number' => 'PO-2026-00002',
            'purchase_invoice_id' => $bill2->id,
            'vendor_id' => $vendors['VEND-2026-0002']->id,
            'warehouse_id' => $arushaWarehouse->id,
            'grn_date' => '2026-10-04',
            'delivery_challan_number' => 'DC-KILOMBERO-4410',
            'carrier_name' => 'Tahmeed Express Freight',
            'vehicle_number' => 'T 911 EKK',
            'received_by' => $creatorId,
            'inspection_status' => 'partially_accepted',
            'matching_status' => 'variance_detected',
            'total_ordered_qty' => 10,
            'total_received_qty' => 10,
            'total_accepted_qty' => 8,
            'total_rejected_qty' => 2,
            'total_amount' => 2000000, // 8 * 250,000
            'quality_inspection_notes' => '8 panels accepted in sound physical condition; 2 panels rejected due to bent 19-inch mounting brackets. Supplier notified for credit note.',
            'rejection_notes' => 'Transit impact damage to side mounting flanges on 2 units.',
            'status' => 'posted',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        GrnItem::firstOrCreate([
            'grn_id' => $grn2->id,
            'product_id' => $fiberPanel->id,
        ], [
            'ordered_quantity' => 10,
            'received_quantity' => 10,
            'accepted_quantity' => 8,
            'rejected_quantity' => 2,
            'unit_price' => 250000,
            'total_amount' => 2000000,
            'batch_number' => 'LOT-2026-FP48',
            'rejection_reason' => 'Bent rack ears on 2 units',
        ]);

        // GRN 3: UNMATCHED / PENDING BILL (PO-2026-00003: Received 15 routers at Mwanza Depot)
        $grn3 = GoodsReceivedNote::firstOrCreate(['grn_number' => 'GRN-2026-00003'], [
            'purchase_order_number' => 'PO-2026-00003',
            'purchase_invoice_id' => null,
            'vendor_id' => $vendors['VEND-2026-0003']->id,
            'warehouse_id' => $mwanzaWarehouse->id,
            'grn_date' => '2026-10-08',
            'delivery_challan_number' => 'DC-TZPOWER-1044',
            'carrier_name' => 'Lake Zone Haulage Ltd',
            'vehicle_number' => 'T 305 CLM',
            'received_by' => $creatorId,
            'inspection_status' => 'inspected_passed',
            'matching_status' => 'unmatched',
            'total_ordered_qty' => 15,
            'total_received_qty' => 15,
            'total_accepted_qty' => 15,
            'total_rejected_qty' => 0,
            'total_amount' => 6750000, // 15 * 450,000
            'quality_inspection_notes' => 'Brand new sealed enterprise routers inspected and firmware verified.',
            'status' => 'received',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        GrnItem::firstOrCreate([
            'grn_id' => $grn3->id,
            'product_id' => $router->id,
        ], [
            'ordered_quantity' => 15,
            'received_quantity' => 15,
            'accepted_quantity' => 15,
            'rejected_quantity' => 0,
            'unit_price' => 450000,
            'total_amount' => 6750000,
            'batch_number' => 'LOT-2026-RT5G',
        ]);

        // Ensure Warehouse Stocks are updated for posted items
        WarehouseStock::updateOrCreate([
            'warehouse_id' => $dsmWarehouse->id,
            'product_id' => $scanner->id,
        ], ['quantity' => 20]);

        WarehouseStock::updateOrCreate([
            'warehouse_id' => $arushaWarehouse->id,
            'product_id' => $fiberPanel->id,
        ], ['quantity' => 8]);

        echo "Mutamark Procurement, Goods Received Notes (GRN) & 3-Way Matching seeded successfully.\n";
    }
}
