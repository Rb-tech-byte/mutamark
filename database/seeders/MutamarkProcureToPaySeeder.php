<?php

namespace Database\Seeders;

use App\Models\DocumentVerification;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Workdo\ProductService\Models\ProductServiceItem;

class MutamarkProcureToPaySeeder extends Seeder
{
    /**
     * Run the database seeds for Procure-to-Pay Purchase Requisitions.
     */
    public function run(): void
    {
        $creator = User::where('email', 'company@example.com')->first();
        if (!$creator) {
            $creator = User::first();
        }
        $creatorId = $creator->id;

        $warehouse = Warehouse::where('created_by', $creatorId)->first();
        $vendor = User::where('type', 'vendor')->where('created_by', $creatorId)->first();
        if (!$vendor) {
            $vendor = User::where('id', '!=', $creatorId)->first() ?? $creator;
        }

        $products = ProductServiceItem::where('created_by', $creatorId)->take(4)->get();

        // 1. PR-2026-00001: Converted to Purchase Bill (PO-2026-XXXXX)
        if (!PurchaseRequisition::where('requisition_number', 'PR-2026-00001')->exists()) {
            $hash1 = hash('sha256', "PR-1-PR-2026-00001-{$creatorId}");

            // Create linked purchase invoice
            $invoice = PurchaseInvoice::create([
                'invoice_date' => now()->subDays(5)->toDateString(),
                'due_date' => now()->addDays(25)->toDateString(),
                'vendor_id' => $vendor->id,
                'warehouse_id' => $warehouse?->id ?? 1,
                'subtotal' => 4500000.00,
                'tax_amount' => 810000.00,
                'discount_amount' => 0.00,
                'total_amount' => 5310000.00,
                'paid_amount' => 0.00,
                'balance_amount' => 5310000.00,
                'status' => 'draft',
                'payment_terms' => 'Net 30 Days',
                'notes' => 'Converted from Approved Requisition PR-2026-00001 (Operations Maintenance).',
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            $pr1 = PurchaseRequisition::create([
                'requisition_number' => 'PR-2026-00001',
                'requisition_date' => now()->subDays(6)->toDateString(),
                'required_by_date' => now()->subDays(1)->toDateString(),
                'requester_id' => $creatorId,
                'department_name' => 'Operations',
                'warehouse_id' => $warehouse?->id ?? 1,
                'preferred_vendor_id' => $vendor->id,
                'priority' => 'high',
                'status' => 'converted',
                'purpose' => 'Essential replenishment of factory safety wear and lubricants for upcoming quarterly maintenance cycle.',
                'budget_reference' => 'BUD-2026-OPS-001',
                'total_estimated_amount' => 4500000.00,
                'approved_by' => $creatorId,
                'approved_at' => now()->subDays(5),
                'approval_notes' => 'Budget approved for Q4 operations.',
                'converted_invoice_id' => $invoice->id,
                'verification_hash' => $hash1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            PurchaseRequisitionItem::create([
                'requisition_id' => $pr1->id,
                'product_id' => $products[0]?->id,
                'product_name' => $products[0]?->name ?? 'Industrial Heavy-Duty Lubricant',
                'sku' => $products[0]?->sku ?? 'LUB-IND-01',
                'quantity' => 15.00,
                'estimated_unit_price' => 200000.00,
                'estimated_total_price' => 3000000.00,
                'unit' => 'drums',
                'description' => 'ISO VG 68 compliant, synthetic high-pressure lubricant.',
            ]);

            PurchaseRequisitionItem::create([
                'requisition_id' => $pr1->id,
                'product_id' => $products[1]?->id,
                'product_name' => $products[1]?->name ?? 'Workplace Protective Helmets & Gear',
                'sku' => $products[1]?->sku ?? 'SAF-HELM-02',
                'quantity' => 30.00,
                'estimated_unit_price' => 50000.00,
                'estimated_total_price' => 1500000.00,
                'unit' => 'sets',
                'description' => 'EN397 certified industrial safety equipment.',
            ]);
        }

        // 2. PR-2026-00002: Approved (Ready for PO Conversion)
        if (!PurchaseRequisition::where('requisition_number', 'PR-2026-00002')->exists()) {
            $hash2 = hash('sha256', "PR-2-PR-2026-00002-{$creatorId}");

            $pr2 = PurchaseRequisition::create([
                'requisition_number' => 'PR-2026-00002',
                'requisition_date' => now()->subDays(2)->toDateString(),
                'required_by_date' => now()->addDays(2)->toDateString(),
                'requester_id' => $creatorId,
                'department_name' => 'Engineering',
                'warehouse_id' => $warehouse?->id ?? 1,
                'preferred_vendor_id' => $vendor->id,
                'priority' => 'urgent',
                'status' => 'approved',
                'purpose' => 'Emergency procurement of replacement circuit breakers and high-voltage electrical conduits for Arusha workshop.',
                'budget_reference' => 'BUD-2026-ENG-002',
                'total_estimated_amount' => 2800000.00,
                'approved_by' => $creatorId,
                'approved_at' => now()->subDay(),
                'approval_notes' => 'Urgent replacement approved.',
                'verification_hash' => $hash2,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            PurchaseRequisitionItem::create([
                'requisition_id' => $pr2->id,
                'product_name' => 'High-Capacity 3-Phase Industrial Breakers',
                'sku' => 'ENG-BRK-400',
                'quantity' => 4.00,
                'estimated_unit_price' => 700000.00,
                'estimated_total_price' => 2800000.00,
                'unit' => 'units',
                'description' => 'Schneider Electric 400A MCCB with thermal magnetic trip unit.',
            ]);
        }

        // 3. PR-2026-00003: Submitted (Pending Approval)
        if (!PurchaseRequisition::where('requisition_number', 'PR-2026-00003')->exists()) {
            $pr3 = PurchaseRequisition::create([
                'requisition_number' => 'PR-2026-00003',
                'requisition_date' => now()->toDateString(),
                'required_by_date' => now()->addDays(7)->toDateString(),
                'requester_id' => $creatorId,
                'department_name' => 'Administration & HR',
                'warehouse_id' => $warehouse?->id ?? 1,
                'priority' => 'medium',
                'status' => 'submitted',
                'purpose' => 'Corporate office stationery, archival box files, and high-speed multi-function printer toner cartridges for Dodoma liaison office.',
                'budget_reference' => 'BUD-2026-ADM-003',
                'total_estimated_amount' => 1250000.00,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            PurchaseRequisitionItem::create([
                'requisition_id' => $pr3->id,
                'product_name' => 'Original Laser Printer Toner Cartridges (Black & Color)',
                'sku' => 'ADM-TNR-08',
                'quantity' => 5.00,
                'estimated_unit_price' => 250000.00,
                'estimated_total_price' => 1250000.00,
                'unit' => 'cartridges',
                'description' => 'HP LaserJet Enterprise 500 series high-yield toner.',
            ]);
        }

        // 4. PR-2026-00004: Draft
        if (!PurchaseRequisition::where('requisition_number', 'PR-2026-00004')->exists()) {
            $pr4 = PurchaseRequisition::create([
                'requisition_number' => 'PR-2026-00004',
                'requisition_date' => now()->toDateString(),
                'required_by_date' => now()->addDays(14)->toDateString(),
                'requester_id' => $creatorId,
                'department_name' => 'IT & Technology',
                'warehouse_id' => $warehouse?->id ?? 1,
                'priority' => 'low',
                'status' => 'draft',
                'purpose' => 'Server room UPS battery backup replacements and patch cabling upgrade.',
                'budget_reference' => 'BUD-2026-IT-004',
                'total_estimated_amount' => 850000.00,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);

            PurchaseRequisitionItem::create([
                'requisition_id' => $pr4->id,
                'product_name' => 'Deep Cycle VRLA Battery 12V 100Ah',
                'sku' => 'IT-BAT-100',
                'quantity' => 2.00,
                'estimated_unit_price' => 425000.00,
                'estimated_total_price' => 850000.00,
                'unit' => 'units',
                'description' => 'Sealed maintenance-free lead acid battery for online rack UPS.',
            ]);
        }
    }
}
