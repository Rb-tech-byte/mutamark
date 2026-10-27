<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesProposal;
use App\Models\SalesProposalItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Services\DocumentVerificationService;
use Workdo\ProductService\Models\ProductServiceItem;

class MutamarkSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $companyUser = User::where('type', 'company')->first();
        if (!$companyUser) {
            $companyUser = User::first();
        }
        $companyId = $companyUser?->id ?? 2;

        echo "Seeding Mutamark ERP Sample Data for Company ID {$companyId}...\n";

        // 1. Warehouses
        $warehouse1 = Warehouse::firstOrCreate(
            ['name' => 'Dar es Salaam Central Warehouse', 'created_by' => $companyId],
            [
                'address' => 'Plot 45, Nyerere Road, Industrial Area',
                'city' => 'Dar es Salaam',
                'zip_code' => '11101',
                'phone' => '+255 22 211 4455',
                'email' => 'dar.warehouse@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $companyId,
            ]
        );

        $warehouse2 = Warehouse::firstOrCreate(
            ['name' => 'Arusha Regional Depot', 'created_by' => $companyId],
            [
                'address' => 'Njiro Road, Near Complex',
                'city' => 'Arusha',
                'zip_code' => '23100',
                'phone' => '+255 27 254 8899',
                'email' => 'arusha.depot@mutamark.co.tz',
                'is_active' => true,
                'creator_id' => $companyId,
            ]
        );

        // 2. Customers
        $customersData = [
            [
                'name' => 'Safari Holdings Ltd',
                'email' => 'finance@safariholdings.co.tz',
                'phone' => '+255 754 112 233',
                'address' => 'Ali Hassan Mwinyi Rd, Dar es Salaam',
                'tin' => '120-456-789',
                'vrn' => '40-009876-A',
            ],
            [
                'name' => 'Kilimanjaro Logistics & Supply Ltd',
                'email' => 'procurement@kililogistics.tz',
                'phone' => '+255 768 445 566',
                'address' => 'Moshi Highway, Arusha',
                'tin' => '135-792-468',
                'vrn' => '40-005432-B',
            ],
            [
                'name' => 'Serengeti Breweries Distribution',
                'email' => 'accounts@sbl-dist.co.tz',
                'phone' => '+255 784 990 011',
                'address' => 'Capripoint, Mwanza',
                'tin' => '142-857-142',
                'vrn' => '40-008123-C',
            ],
            [
                'name' => 'Zanzibar Spice & Trading Co',
                'email' => 'trade@zanzibarspice.tz',
                'phone' => '+255 777 332 211',
                'address' => 'Stone Town, Zanzibar',
                'tin' => '156-234-890',
                'vrn' => '40-003456-D',
            ],
        ];

        $customerUsers = [];
        foreach ($customersData as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'password' => Hash::make('password123'),
                    'type' => 'client',
                    'mobile_no' => $c['phone'],
                    'created_by' => $companyId,
                ]
            );

            // Customer details table
            DB::table('customers')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'customer_code' => 'CUST-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'company_name' => $c['name'],
                    'contact_person_name' => $c['name'] . ' Manager',
                    'contact_person_email' => $c['email'],
                    'contact_person_mobile' => $c['phone'],
                    'tax_number' => $c['tin'],
                    'payment_terms' => 'Net 30 Days',
                    'billing_address' => json_encode(['address_line_1' => $c['address'], 'city' => 'Tanzania']),
                    'shipping_address' => json_encode(['address_line_1' => $c['address'], 'city' => 'Tanzania']),
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $customerUsers[$c['name']] = $user;
        }

        // 3. Vendors
        $vendorsData = [
            [
                'name' => 'Dar Industrial Hardware Supplies Ltd',
                'email' => 'sales@darindustrial.co.tz',
                'phone' => '+255 713 554 433',
                'address' => 'Gerezani, Dar es Salaam',
                'tin' => '110-333-777',
            ],
            [
                'name' => 'Coastal Paper & Packaging Mills',
                'email' => 'orders@coastalpaper.co.tz',
                'phone' => '+255 715 889 900',
                'address' => 'Port Area, Tanga',
                'tin' => '115-444-888',
            ],
        ];

        $vendorUsers = [];
        foreach ($vendorsData as $v) {
            $user = User::firstOrCreate(
                ['email' => $v['email']],
                [
                    'name' => $v['name'],
                    'password' => Hash::make('password123'),
                    'type' => 'vendor',
                    'mobile_no' => $v['phone'],
                    'created_by' => $companyId,
                ]
            );

            DB::table('vendors')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'vendor_code' => 'VEND-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'company_name' => $v['name'],
                    'contact_person_name' => $v['name'] . ' Representative',
                    'contact_person_email' => $v['email'],
                    'contact_person_mobile' => $v['phone'],
                    'tax_number' => $v['tin'],
                    'payment_terms' => 'Net 15 Days',
                    'billing_address' => json_encode(['address_line_1' => $v['address']]),
                    'shipping_address' => json_encode(['address_line_1' => $v['address']]),
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $vendorUsers[$v['name']] = $user;
        }

        // 4. Products / Services
        $products = [
            [
                'name' => 'Enterprise Cloud ERP Platform - Annual',
                'sku' => 'MUT-ERP-ANNUAL',
                'sale_price' => 2500000,
                'purchase_price' => 1500000,
                'type' => 'service',
            ],
            [
                'name' => 'High-Capacity Industrial Barcode Scanner',
                'sku' => 'MUT-HW-BCSCAN',
                'sale_price' => 350000,
                'purchase_price' => 220000,
                'type' => 'product',
            ],
            [
                'name' => 'Direct Thermal Receipt Printer 80mm',
                'sku' => 'MUT-HW-PRINTER',
                'sale_price' => 280000,
                'purchase_price' => 170000,
                'type' => 'product',
            ],
            [
                'name' => 'Custom ERP Workflow Automation & Integration',
                'sku' => 'MUT-SRV-AUTO',
                'sale_price' => 1800000,
                'purchase_price' => 900000,
                'type' => 'service',
            ],
            [
                'name' => 'Monthly Maintenance & Premium SLA Support',
                'sku' => 'MUT-SRV-SLA',
                'sale_price' => 450000,
                'purchase_price' => 200000,
                'type' => 'service',
            ],
        ];

        $createdProducts = [];
        foreach ($products as $p) {
            $createdProducts[$p['sku']] = ProductServiceItem::firstOrCreate(
                ['sku' => $p['sku'], 'created_by' => $companyId],
                [
                    'name' => $p['name'],
                    'sale_price' => $p['sale_price'],
                    'purchase_price' => $p['purchase_price'],
                    'type' => $p['type'],
                    'is_active' => true,
                    'creator_id' => $companyId,
                ]
            );
        }

        // 5. Sales Invoices
        $invoices = [
            [
                'number' => 'INV-2026-0001',
                'customer' => 'Safari Holdings Ltd',
                'subtotal' => 450000,
                'tax' => 81000,
                'total' => 450000,
                'paid' => 450000,
                'balance' => 0,
                'status' => 'paid',
                'date' => '2026-10-01',
                'due' => '2026-10-15',
                'sku' => 'MUT-SRV-SLA',
                'qty' => 1,
                'price' => 450000,
            ],
            [
                'number' => 'INV-2026-0002',
                'customer' => 'Kilimanjaro Logistics & Supply Ltd',
                'subtotal' => 1850000,
                'tax' => 333000,
                'total' => 1850000,
                'paid' => 0,
                'balance' => 1850000,
                'status' => 'posted',
                'date' => '2026-10-05',
                'due' => '2026-10-25',
                'sku' => 'MUT-HW-BCSCAN',
                'qty' => 5,
                'price' => 370000,
            ],
            [
                'number' => 'INV-2026-0003',
                'customer' => 'Serengeti Breweries Distribution',
                'subtotal' => 4300000,
                'tax' => 774000,
                'total' => 4300000,
                'paid' => 2000000,
                'balance' => 2300000,
                'status' => 'partial',
                'date' => '2026-10-08',
                'due' => '2026-11-08',
                'sku' => 'MUT-ERP-ANNUAL',
                'qty' => 1,
                'price' => 4300000,
            ],
        ];

        foreach ($invoices as $inv) {
            $customer = $customerUsers[$inv['customer']] ?? $customerUsers['Safari Holdings Ltd'];
            $invoice = SalesInvoice::updateOrCreate(
                ['invoice_number' => $inv['number'], 'created_by' => $companyId],
                [
                    'invoice_date' => $inv['date'],
                    'due_date' => $inv['due'],
                    'customer_id' => $customer->id,
                    'warehouse_id' => $warehouse1->id,
                    'subtotal' => $inv['subtotal'],
                    'tax_amount' => $inv['tax'],
                    'discount_amount' => 0,
                    'total_amount' => $inv['total'],
                    'paid_amount' => $inv['paid'],
                    'balance_amount' => $inv['balance'],
                    'status' => $inv['status'],
                    'type' => 'product',
                    'payment_terms' => 'Lipa Hapa (Till): 16381432 or CRDB/NMB Bank',
                    'notes' => 'Official verified invoice. Verified via Wolinet Cryptographic Engine.',
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                ]
            );

            // Item
            $prod = $createdProducts[$inv['sku']] ?? null;
            if ($prod) {
                SalesInvoiceItem::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'product_id' => $prod->id],
                    [
                        'quantity' => $inv['qty'],
                        'unit_price' => $inv['price'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'tax_percentage' => 18,
                        'tax_amount' => $inv['tax'],
                        'total_amount' => $inv['total'],
                    ]
                );
            }

            // Create Cryptographic Verification Record
            $verification = DocumentVerificationService::getOrCreateVerification($invoice, 'invoice');
            echo "Verified Invoice {$inv['number']} -> Hash: {$verification->verification_hash}\n";
        }

        // 6. Sales Proposals
        $proposals = [
            [
                'number' => 'QUO-2026-0001',
                'customer' => 'Serengeti Breweries Distribution',
                'subtotal' => 3200000,
                'tax' => 576000,
                'total' => 3200000,
                'status' => 'sent',
                'date' => '2026-10-04',
                'due' => '2026-11-04',
                'sku' => 'MUT-SRV-AUTO',
            ],
            [
                'number' => 'QUO-2026-0002',
                'customer' => 'Zanzibar Spice & Trading Co',
                'subtotal' => 920000,
                'tax' => 165600,
                'total' => 920000,
                'status' => 'accepted',
                'date' => '2026-10-06',
                'due' => '2026-11-06',
                'sku' => 'MUT-HW-PRINTER',
            ],
        ];

        foreach ($proposals as $prop) {
            $customer = $customerUsers[$prop['customer']] ?? $customerUsers['Safari Holdings Ltd'];
            $proposal = SalesProposal::updateOrCreate(
                ['proposal_number' => $prop['number'], 'created_by' => $companyId],
                [
                    'proposal_date' => $prop['date'],
                    'due_date' => $prop['due'],
                    'customer_id' => $customer->id,
                    'warehouse_id' => $warehouse1->id,
                    'type' => 'product',
                    'subtotal' => $prop['subtotal'],
                    'tax_amount' => $prop['tax'],
                    'discount_amount' => 0,
                    'total_amount' => $prop['total'],
                    'status' => $prop['status'],
                    'converted_to_invoice' => false,
                    'payment_terms' => 'Lipa Hapa (Till): 16381432 / Valid for 30 Days',
                    'notes' => 'Authentic quotation verified by Mutamark Directorate.',
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                ]
            );

            $prod = $createdProducts[$prop['sku']] ?? null;
            if ($prod) {
                SalesProposalItem::updateOrCreate(
                    ['proposal_id' => $proposal->id, 'product_id' => $prod->id],
                    [
                        'quantity' => 1,
                        'unit_price' => $prop['subtotal'],
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'tax_percentage' => 18,
                        'tax_amount' => $prop['tax'],
                        'total_amount' => $prop['total'],
                    ]
                );
            }

            $verification = DocumentVerificationService::getOrCreateVerification($proposal, 'quotation');
            echo "Verified Proposal {$prop['number']} -> Hash: {$verification->verification_hash}\n";
        }

        // 7. Purchase Invoices / Orders
        $purchases = [
            [
                'number' => 'PO-2026-0001',
                'vendor' => 'Dar Industrial Hardware Supplies Ltd',
                'subtotal' => 2400000,
                'tax' => 432000,
                'total' => 2400000,
                'paid' => 1000000,
                'balance' => 1400000,
                'status' => 'partial',
                'date' => '2026-10-02',
                'due' => '2026-10-22',
            ],
            [
                'number' => 'PO-2026-0002',
                'vendor' => 'Coastal Paper & Packaging Mills',
                'subtotal' => 850000,
                'tax' => 153000,
                'total' => 850000,
                'paid' => 850000,
                'balance' => 0,
                'status' => 'paid',
                'date' => '2026-10-07',
                'due' => '2026-10-27',
            ],
        ];

        foreach ($purchases as $purch) {
            $vendor = $vendorUsers[$purch['vendor']] ?? $vendorUsers['Dar Industrial Hardware Supplies Ltd'];
            $purchase = PurchaseInvoice::updateOrCreate(
                ['invoice_number' => $purch['number'], 'created_by' => $companyId],
                [
                    'invoice_date' => $purch['date'],
                    'due_date' => $purch['due'],
                    'vendor_id' => $vendor->id,
                    'warehouse_id' => $warehouse2->id,
                    'subtotal' => $purch['subtotal'],
                    'tax_amount' => $purch['tax'],
                    'discount_amount' => 0,
                    'total_amount' => $purch['total'],
                    'paid_amount' => $purch['paid'],
                    'balance_amount' => $purch['balance'],
                    'status' => $purch['status'],
                    'payment_terms' => 'Net 15 Days',
                    'notes' => 'Official verified purchase order.',
                    'creator_id' => $companyId,
                    'created_by' => $companyId,
                ]
            );

            $prod = $createdProducts['MUT-HW-BCSCAN'] ?? null;
            if ($prod) {
                PurchaseInvoiceItem::updateOrCreate(
                    ['invoice_id' => $purchase->id, 'product_id' => $prod->id],
                    [
                        'quantity' => 2,
                        'unit_price' => $purch['subtotal'] / 2,
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                        'tax_percentage' => 18,
                        'tax_amount' => $purch['tax'],
                        'total_amount' => $purch['total'],
                    ]
                );
            }

            $verification = DocumentVerificationService::getOrCreateVerification($purchase, 'purchase_order');
            echo "Verified Purchase Order {$purch['number']} -> Hash: {$verification->verification_hash}\n";
        }

        echo "All Mutamark ERP Sample Data seeded successfully!\n";
    }
}
