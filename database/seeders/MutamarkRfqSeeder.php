<?php

namespace Database\Seeders;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\SupplierQuotation;
use App\Models\SupplierQuotationItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\RfqService;
use Illuminate\Database\Seeder;
use Workdo\Account\Models\Vendor;
use Workdo\ProductService\Models\ProductServiceItem;

class MutamarkRfqSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'company@example.com')->first();
        if (!$user) {
            $user = User::where('type', 'company')->first() ?? User::first();
        }

        \Illuminate\Support\Facades\Auth::login($user);

        $companyId = $user->id;
        $warehouse = Warehouse::where('created_by', $companyId)->first();
        $warehouseId = $warehouse ? $warehouse->id : 1;

        // Ensure we have some vendors
        $vendors = Vendor::where('created_by', $companyId)->get();
        if ($vendors->count() < 3) {
            $sampleVendors = [
                ['name' => 'Kilimanjaro Industrial Parts Ltd', 'email' => 'sales@kilimanjaroparts.co.tz', 'contact_number' => '+255 754 112 233'],
                ['name' => 'Victoria Heavy Hardware Supplies', 'email' => 'orders@victoriahardware.co.tz', 'contact_number' => '+255 765 443 322'],
                ['name' => 'Simba Machinery & Safety Tools Co', 'email' => 'tenders@simbatools.co.tz', 'contact_number' => '+255 784 998 877'],
            ];

            foreach ($sampleVendors as $vData) {
                Vendor::create([
                    'name' => $vData['name'],
                    'company_name' => $vData['name'],
                    'email' => $vData['email'],
                    'contact_number' => $vData['contact_number'],
                    'billing_address' => 'Nyerere Road Industrial Area, Dar es Salaam',
                    'shipping_address' => 'Nyerere Road Industrial Area, Dar es Salaam',
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                ]);
            }
            $vendors = Vendor::where('created_by', $companyId)->get();
        }

        $vendorList = $vendors->values();
        $v1 = $vendorList[0];
        $v2 = $vendorList[1] ?? $vendorList[0];
        $v3 = $vendorList[2] ?? $vendorList[0];

        $product = ProductServiceItem::where('created_by', $companyId)->first();
        $pr = PurchaseRequisition::where('created_by', $companyId)->first();

        // 1. RFQ-2026-00001 (Awarded)
        $rfq1 = Rfq::updateOrCreate(
            ['rfq_number' => 'RFQ-2026-00001'],
            [
                'title' => 'Tender: Industrial Conveyor Belts & Heavy Machinery Rollers',
                'requisition_id' => $pr ? $pr->id : null,
                'issue_date' => date('Y-m-d', strtotime('-14 days')),
                'closing_date' => date('Y-m-d', strtotime('-5 days')),
                'delivery_deadline' => date('Y-m-d', strtotime('+10 days')),
                'warehouse_id' => $warehouseId,
                'status' => 'draft', // Will be awarded via service
                'payment_terms' => 'Net 30 Days after GRN inspection',
                'delivery_terms' => 'DAP Dar es Salaam Warehouse',
                'evaluation_notes' => 'Tender evaluated by Procurement Committee. Kilimanjaro Parts selected on best price and shortest delivery lead time.',
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ]
        );

        $rfq1->items()->delete();
        $item1_1 = RfqItem::create([
            'rfq_id' => $rfq1->id,
            'product_id' => $product ? $product->id : null,
            'product_name' => 'Reinforced Rubber Conveyor Belt (800mm x 50m)',
            'sku' => 'CV-BELT-800',
            'quantity' => 2,
            'unit' => 'rolls',
            'target_unit_price' => 1500000.00,
            'specifications' => 'Heat resistant EP400 3-ply rubber conveyor belt.',
        ]);

        $item1_2 = RfqItem::create([
            'rfq_id' => $rfq1->id,
            'product_id' => null,
            'product_name' => 'Heavy Duty Steel Roller Assemblies',
            'sku' => 'RL-STEEL-01',
            'quantity' => 10,
            'unit' => 'units',
            'target_unit_price' => 85000.00,
            'specifications' => 'Sealed ball bearing steel idler rollers 108mm diameter.',
        ]);

        // Add 3 competing quotation bids
        $rfq1->quotations()->delete();
        
        // Bid 1: Kilimanjaro Industrial (Winning bid: 2,850,000 TZS total)
        $bid1 = SupplierQuotation::create([
            'rfq_id' => $rfq1->id,
            'vendor_id' => $v1->id,
            'vendor_name' => $v1->company_name ?: $v1->name,
            'quotation_reference' => 'QUO-KIL-2026-089',
            'submission_date' => date('Y-m-d', strtotime('-8 days')),
            'valid_until' => date('Y-m-d', strtotime('+22 days')),
            'delivery_lead_time_days' => 5,
            'warranty_months' => 24,
            'payment_terms' => '30 Days Net',
            'total_bid_amount' => 2850000.00,
            'status' => 'submitted',
            'evaluation_score' => 96.50,
            'notes' => 'Includes free on-site installation technical assistance.',
            'creator_id' => $companyId,
            'created_by' => $companyId,
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid1->id,
            'rfq_item_id' => $item1_1->id,
            'unit_price' => 1050000.00,
            'quantity' => 2,
            'total_price' => 2100000.00,
            'remarks' => 'German grade conveyor belt with reinforced tensile steel cord.',
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid1->id,
            'rfq_item_id' => $item1_2->id,
            'unit_price' => 75000.00,
            'quantity' => 10,
            'total_price' => 750000.00,
            'remarks' => 'Heavy duty sealed bearing rollers.',
        ]);

        // Bid 2: Victoria Heavy Hardware (Competing bid: 3,200,000 TZS)
        $bid2 = SupplierQuotation::create([
            'rfq_id' => $rfq1->id,
            'vendor_id' => $v2->id,
            'vendor_name' => $v2->company_name ?: $v2->name,
            'quotation_reference' => 'VHH-QUOT-5521',
            'submission_date' => date('Y-m-d', strtotime('-7 days')),
            'valid_until' => date('Y-m-d', strtotime('+15 days')),
            'delivery_lead_time_days' => 7,
            'warranty_months' => 12,
            'payment_terms' => '50% Advance, 50% on Delivery',
            'total_bid_amount' => 3200000.00,
            'status' => 'submitted',
            'evaluation_score' => 84.00,
            'notes' => 'Standard industrial delivery warranty.',
            'creator_id' => $companyId,
            'created_by' => $companyId,
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid2->id,
            'rfq_item_id' => $item1_1->id,
            'unit_price' => 1200000.00,
            'quantity' => 2,
            'total_price' => 2400000.00,
            'remarks' => 'Standard rubber conveyor.',
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid2->id,
            'rfq_item_id' => $item1_2->id,
            'unit_price' => 80000.00,
            'quantity' => 10,
            'total_price' => 800000.00,
            'remarks' => 'Standard rollers.',
        ]);

        // Bid 3: Simba Machinery (Competing bid: 3,450,000 TZS)
        $bid3 = SupplierQuotation::create([
            'rfq_id' => $rfq1->id,
            'vendor_id' => $v3->id,
            'vendor_name' => $v3->company_name ?: $v3->name,
            'quotation_reference' => 'SIMBA-TEN-902',
            'submission_date' => date('Y-m-d', strtotime('-6 days')),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'delivery_lead_time_days' => 10,
            'warranty_months' => 12,
            'payment_terms' => '100% Cash on Delivery',
            'total_bid_amount' => 3450000.00,
            'status' => 'submitted',
            'evaluation_score' => 78.00,
            'notes' => 'Transport to Dar es Salaam warehouse included.',
            'creator_id' => $companyId,
            'created_by' => $companyId,
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid3->id,
            'rfq_item_id' => $item1_1->id,
            'unit_price' => 1300000.00,
            'quantity' => 2,
            'total_price' => 2600000.00,
            'remarks' => 'Rubber conveyor belt.',
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid3->id,
            'rfq_item_id' => $item1_2->id,
            'unit_price' => 85000.00,
            'quantity' => 10,
            'total_price' => 850000.00,
            'remarks' => 'Steel rollers.',
        ]);

        // Award winning bid via service!
        RfqService::awardBidAndGeneratePo($rfq1, $bid1->id, $companyId);

        // 2. RFQ-2026-00002 (Under Evaluation)
        $rfq2 = Rfq::updateOrCreate(
            ['rfq_number' => 'RFQ-2026-00002'],
            [
                'title' => 'Supply of Cisco Gigabit Network Switches & Rackmount Online UPS',
                'issue_date' => date('Y-m-d', strtotime('-5 days')),
                'closing_date' => date('Y-m-d', strtotime('+2 days')),
                'delivery_deadline' => date('Y-m-d', strtotime('+15 days')),
                'warehouse_id' => $warehouseId,
                'status' => 'evaluating',
                'payment_terms' => 'Net 30 Days',
                'delivery_terms' => 'DAP Dar es Salaam IT Center',
                'evaluation_notes' => 'Received 2 quotations. Technical committee comparing warranty duration and switch management features.',
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ]
        );

        $rfq2->items()->delete();
        $item2_1 = RfqItem::create([
            'rfq_id' => $rfq2->id,
            'product_name' => 'Cisco Catalyst 24-Port Gigabit Managed Switch',
            'sku' => 'SW-CISCO-24G',
            'quantity' => 3,
            'unit' => 'units',
            'target_unit_price' => 1800000.00,
            'specifications' => 'Layer 2/3 Managed PoE+ 370W switch with 4x SFP+ uplinks.',
        ]);

        $item2_2 = RfqItem::create([
            'rfq_id' => $rfq2->id,
            'product_name' => 'APC Smart-UPS 3000VA LCD Rackmount 2U',
            'sku' => 'UPS-APC-3KVA',
            'quantity' => 2,
            'unit' => 'units',
            'target_unit_price' => 2400000.00,
            'specifications' => 'Pure sine wave online interactive UPS with network card.',
        ]);

        $rfq2->quotations()->delete();
        $bid2_1 = SupplierQuotation::create([
            'rfq_id' => $rfq2->id,
            'vendor_id' => $v1->id,
            'vendor_name' => $v1->company_name ?: $v1->name,
            'quotation_reference' => 'QUO-TECH-IT-001',
            'submission_date' => date('Y-m-d', strtotime('-2 days')),
            'valid_until' => date('Y-m-d', strtotime('+20 days')),
            'delivery_lead_time_days' => 4,
            'warranty_months' => 36,
            'payment_terms' => 'Net 30 Days',
            'total_bid_amount' => 9700000.00,
            'status' => 'shortlisted',
            'notes' => 'Official Cisco certified warranty with 3-year replacement guarantee.',
            'creator_id' => $companyId,
            'created_by' => $companyId,
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid2_1->id,
            'rfq_item_id' => $item2_1->id,
            'unit_price' => 1700000.00,
            'quantity' => 3,
            'total_price' => 5100000.00,
            'remarks' => 'Cisco Catalyst genuine enterprise hardware.',
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid2_1->id,
            'rfq_item_id' => $item2_2->id,
            'unit_price' => 2300000.00,
            'quantity' => 2,
            'total_price' => 4600000.00,
            'remarks' => 'APC Smart-UPS 3000VA.',
        ]);

        $bid2_2 = SupplierQuotation::create([
            'rfq_id' => $rfq2->id,
            'vendor_id' => $v2->id,
            'vendor_name' => $v2->company_name ?: $v2->name,
            'quotation_reference' => 'VHH-IT-8842',
            'submission_date' => date('Y-m-d', strtotime('-1 days')),
            'valid_until' => date('Y-m-d', strtotime('+14 days')),
            'delivery_lead_time_days' => 6,
            'warranty_months' => 12,
            'payment_terms' => '30 Days Net',
            'total_bid_amount' => 10100000.00,
            'status' => 'submitted',
            'notes' => 'Delivery within 6 business days.',
            'creator_id' => $companyId,
            'created_by' => $companyId,
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid2_2->id,
            'rfq_item_id' => $item2_1->id,
            'unit_price' => 1750000.00,
            'quantity' => 3,
            'total_price' => 5250000.00,
            'remarks' => 'Switch units.',
        ]);

        SupplierQuotationItem::create([
            'supplier_quotation_id' => $bid2_2->id,
            'rfq_item_id' => $item2_2->id,
            'unit_price' => 2425000.00,
            'quantity' => 2,
            'total_price' => 4850000.00,
            'remarks' => 'UPS battery backup.',
        ]);

        // 3. RFQ-2026-00003 (Published - Open for Bidding)
        $rfq3 = Rfq::updateOrCreate(
            ['rfq_number' => 'RFQ-2026-00003'],
            [
                'title' => 'Quarterly Supply of Factory Safety Gear (Helmets, Steel-Toe Boots, Gloves)',
                'issue_date' => date('Y-m-d'),
                'closing_date' => date('Y-m-d', strtotime('+10 days')),
                'delivery_deadline' => date('Y-m-d', strtotime('+20 days')),
                'warehouse_id' => $warehouseId,
                'status' => 'published',
                'payment_terms' => 'Net 15 Days',
                'delivery_terms' => 'Delivered to Main Store',
                'creator_id' => $companyId,
                'created_by' => $companyId,
            ]
        );

        $rfq3->items()->delete();
        RfqItem::create([
            'rfq_id' => $rfq3->id,
            'product_name' => 'Industrial Safety Hard Hat Helmet (EN 397 Certified)',
            'sku' => 'PPE-HLM-01',
            'quantity' => 50,
            'unit' => 'pcs',
            'target_unit_price' => 25000.00,
            'specifications' => 'Yellow UV-resistant polyethylene with 6-point ratchet suspension.',
        ]);

        RfqItem::create([
            'rfq_id' => $rfq3->id,
            'product_name' => 'Steel-Toe Leather Safety Work Boots (Sizes 40-45)',
            'sku' => 'PPE-BOOT-02',
            'quantity' => 30,
            'unit' => 'pairs',
            'target_unit_price' => 75000.00,
            'specifications' => 'S3 certified anti-penetration steel midsole and oil-resistant sole.',
        ]);
    }
}
