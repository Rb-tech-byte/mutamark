<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Workdo\Account\Models\BankAccount;
use Workdo\Account\Models\BankTransfer;
use Workdo\Account\Models\BankTransaction;
use Workdo\Account\Models\ChartOfAccount;
use Workdo\Account\Models\CustomerPayment;
use Workdo\Account\Models\CustomerPaymentAllocation;
use Workdo\Account\Models\VendorPayment;
use Workdo\Account\Models\VendorPaymentAllocation;
use Workdo\Account\Models\RevenueCategories;
use Workdo\Account\Models\Revenue;
use Workdo\Account\Models\ExpenseCategories;
use Workdo\Account\Models\Expense;
use Workdo\Account\Models\JournalEntry;
use Workdo\Account\Models\JournalEntryItem;

class MutamarkAccountingSeeder extends Seeder
{
    public function run(): void
    {
        $company = User::where('type', 'company')->first();
        if (!$company) {
            $this->command->error('No company user found.');
            return;
        }

        $userId = $company->id;

        // 1. Ensure Standard Tanzanian Bank Accounts (SRS Section 4.1 & Appendix B)
        $crdb = BankAccount::updateOrCreate(
            ['account_number' => '0150284758200', 'created_by' => $userId],
            [
                'account_name' => 'MUTAMARK ENTERPRISES',
                'bank_name' => 'CRDB Bank Plc',
                'branch_name' => 'Corporate Branch Dar es Salaam',
                'account_type' => 'Corporate Checking',
                'opening_balance' => 25000000.00,
                'current_balance' => 28247500.00,
                'swift_code' => 'CORUTZTZ',
                'is_active' => 1,
                'gl_account_id' => 3, // Bank Account - Main
                'creator_id' => $userId,
            ]
        );

        $nmb = BankAccount::updateOrCreate(
            ['account_number' => '20110034920', 'created_by' => $userId],
            [
                'account_name' => 'MUTAMARK ENTERPRISES',
                'bank_name' => 'NMB Bank Plc',
                'branch_name' => 'Clock Tower Branch',
                'account_type' => 'Business Operations',
                'opening_balance' => 12500000.00,
                'current_balance' => 13270000.00,
                'swift_code' => 'NMBLTZTZ',
                'is_active' => 1,
                'gl_account_id' => 4, // Bank Account - Savings
                'creator_id' => $userId,
            ]
        );

        $mpesa = BankAccount::updateOrCreate(
            ['account_number' => '16381432', 'created_by' => $userId],
            [
                'account_name' => 'MUTAMARK ENTERPRISES',
                'bank_name' => 'Vodacom M-Pesa Till',
                'branch_name' => 'Lipa Hapa Merchant Dar',
                'account_type' => 'Mobile Money Till',
                'opening_balance' => 4850000.00,
                'current_balance' => 2445000.00,
                'payment_gateway' => 'Vodacom M-Pesa',
                'is_active' => 1,
                'gl_account_id' => 1, // Cash
                'creator_id' => $userId,
            ]
        );

        // 2. Seed Realistic Bank Transfers
        $trf1 = BankTransfer::updateOrCreate(
            ['transfer_number' => 'TRF-2026-0001', 'created_by' => $userId],
            [
                'transfer_date' => '2026-10-02',
                'from_account_id' => $mpesa->id,
                'to_account_id' => $crdb->id,
                'transfer_amount' => 3500000.00,
                'transfer_charges' => 5000.00,
                'reference_number' => 'MP-CRDB-20261002-991',
                'description' => 'Daily merchant till sweep from Lipa Namba 16381432 to CRDB Corporate operating account',
                'status' => 'completed',
                'creator_id' => $userId,
            ]
        );

        $trf2 = BankTransfer::updateOrCreate(
            ['transfer_number' => 'TRF-2026-0002', 'created_by' => $userId],
            [
                'transfer_date' => '2026-10-06',
                'from_account_id' => $crdb->id,
                'to_account_id' => $nmb->id,
                'transfer_amount' => 2000000.00,
                'transfer_charges' => 2500.00,
                'reference_number' => 'TIPS-TISS-20261006-442',
                'description' => 'TIPS interbank liquidity rebalancing for branch vendor payments',
                'status' => 'completed',
                'creator_id' => $userId,
            ]
        );

        // 3. Clear & Seed Bank Transactions
        DB::table('bank_transactions')->where('created_by', $userId)->delete();

        $transactions = [
            // CRDB Transactions
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-01',
                'transaction_type' => 'credit',
                'reference_number' => 'OB-CRDB-2026',
                'description' => 'Opening balance verified by Bank Reconciliation statement',
                'amount' => 25000000.00,
                'running_balance' => 25000000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-02',
                'transaction_type' => 'credit',
                'reference_number' => 'MP-CRDB-20261002-991',
                'description' => 'Till sweep received from Vodacom Lipa Hapa 16381432',
                'amount' => 3500000.00,
                'running_balance' => 28500000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-06',
                'transaction_type' => 'debit',
                'reference_number' => 'TIPS-TISS-20261006-442',
                'description' => 'TIPS outgoing liquidity transfer to NMB Operational account',
                'amount' => 2000000.00,
                'running_balance' => 26500000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-06',
                'transaction_type' => 'debit',
                'reference_number' => 'CHG-TIPS-442',
                'description' => 'TIPS Interbank clearing & settlement fee',
                'amount' => 2500.00,
                'running_balance' => 26497500.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-07',
                'transaction_type' => 'credit',
                'reference_number' => 'CRDB-FT-883921-SBL',
                'description' => 'Customer payment received from Serengeti Breweries (INV-2026-0003)',
                'amount' => 2000000.00,
                'running_balance' => 28497500.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-08',
                'transaction_type' => 'debit',
                'reference_number' => 'CRDB-TISS-772910-DIH',
                'description' => 'Vendor milestone settlement to Dar Industrial Hardware (PO-2026-0001)',
                'amount' => 1200000.00,
                'running_balance' => 27297500.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-08',
                'transaction_type' => 'credit',
                'reference_number' => 'CRDB-REV-ZNZ-01',
                'description' => 'Cloud ERP integration retainer received from Zanzibar Spice Traders',
                'amount' => 1200000.00,
                'running_balance' => 28497500.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $crdb->id,
                'transaction_date' => '2026-10-08',
                'transaction_type' => 'debit',
                'reference_number' => 'CRDB-EXP-TRA-02',
                'description' => 'TRA EFD Virtual Device Security & cryptographic certificate renewal',
                'amount' => 250000.00,
                'running_balance' => 28247500.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // NMB Transactions
            [
                'bank_account_id' => $nmb->id,
                'transaction_date' => '2026-10-01',
                'transaction_type' => 'credit',
                'reference_number' => 'OB-NMB-2026',
                'description' => 'Opening balance verified by Bank Reconciliation statement',
                'amount' => 12500000.00,
                'running_balance' => 12500000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $nmb->id,
                'transaction_date' => '2026-10-06',
                'transaction_type' => 'credit',
                'reference_number' => 'TIPS-TISS-20261006-442',
                'description' => 'TIPS interbank funds inbound from CRDB Corporate account',
                'amount' => 2000000.00,
                'running_balance' => 14500000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $nmb->id,
                'transaction_date' => '2026-10-07',
                'transaction_type' => 'debit',
                'reference_number' => 'NMB-EFT-449102-MTS',
                'description' => 'Vendor payment settlement to Mwanza Tech Solutions (PO-2026-0002)',
                'amount' => 850000.00,
                'running_balance' => 13650000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $nmb->id,
                'transaction_date' => '2026-10-08',
                'transaction_type' => 'debit',
                'reference_number' => 'NMB-EXP-VOD-01',
                'description' => 'Vodacom dedicated optic fiber datacenter link subscription',
                'amount' => 380000.00,
                'running_balance' => 13270000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Vodacom M-Pesa Till Transactions
            [
                'bank_account_id' => $mpesa->id,
                'transaction_date' => '2026-10-01',
                'transaction_type' => 'credit',
                'reference_number' => 'OB-MPESA-2026',
                'description' => 'Opening balance on Till Lipa Namba 16381432',
                'amount' => 4850000.00,
                'running_balance' => 4850000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $mpesa->id,
                'transaction_date' => '2026-10-02',
                'transaction_type' => 'debit',
                'reference_number' => 'MP-CRDB-20261002-991',
                'description' => 'Daily sweep to CRDB Bank Plc account',
                'amount' => 3500000.00,
                'running_balance' => 1350000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $mpesa->id,
                'transaction_date' => '2026-10-02',
                'transaction_type' => 'debit',
                'reference_number' => 'CHG-MP-SWEEP',
                'description' => 'Vodacom business till transfer fee',
                'amount' => 5000.00,
                'running_balance' => 1345000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $mpesa->id,
                'transaction_date' => '2026-10-03',
                'transaction_type' => 'credit',
                'reference_number' => 'MP-REC-489210-SAFARI',
                'description' => 'Customer settlement received from Safari Holdings (INV-2026-0001)',
                'amount' => 450000.00,
                'running_balance' => 1795000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_account_id' => $mpesa->id,
                'transaction_date' => '2026-10-07',
                'transaction_type' => 'credit',
                'reference_number' => 'MP-REV-KLM-02',
                'description' => 'Annual Cloud Disaster Recovery retainer from Kilimanjaro Logistics',
                'amount' => 650000.00,
                'running_balance' => 2445000.00,
                'transaction_status' => 'cleared',
                'reconciliation_status' => 'reconciled',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('bank_transactions')->insert($transactions);

        // 4. Seed Customer Payments & Allocations
        DB::table('customer_payment_allocations')->where('created_by', $userId)->delete();
        DB::table('customer_payments')->where('created_by', $userId)->delete();

        $cp1 = CustomerPayment::create([
            'payment_number' => 'RV-2026-00001',
            'payment_date' => '2026-10-03',
            'customer_id' => 3, // Safari Holdings
            'bank_account_id' => $mpesa->id,
            'reference_number' => 'MP-REC-489210-SAFARI',
            'payment_amount' => 450000.00,
            'status' => 'cleared',
            'notes' => 'Full payment settlement for INV-2026-0001 via Lipa Hapa Till 16381432',
            'creator_id' => $userId,
            'created_by' => $userId,
        ]);

        CustomerPaymentAllocation::create([
            'payment_id' => $cp1->id,
            'invoice_id' => 1,
            'allocated_amount' => 450000.00,
            'creator_id' => $userId,
            'created_by' => $userId,
        ]);

        $cp2 = CustomerPayment::create([
            'payment_number' => 'RV-2026-00002',
            'payment_date' => '2026-10-07',
            'customer_id' => 5, // Serengeti Breweries
            'bank_account_id' => $crdb->id,
            'reference_number' => 'CRDB-FT-883921-SBL',
            'payment_amount' => 2000000.00,
            'status' => 'cleared',
            'notes' => 'Advance milestone tranche for INV-2026-0003 IoT industrial hardware',
            'creator_id' => $userId,
            'created_by' => $userId,
        ]);

        CustomerPaymentAllocation::create([
            'payment_id' => $cp2->id,
            'invoice_id' => 3,
            'allocated_amount' => 2000000.00,
            'creator_id' => $userId,
            'created_by' => $userId,
        ]);

        // 5. Seed Vendor Payments & Allocations
        DB::table('vendor_payment_allocations')->delete();
        DB::table('vendor_payments')->where('created_by', $userId)->delete();

        $vp1 = VendorPayment::create([
            'payment_number' => 'VP-2026-00001',
            'payment_date' => '2026-10-04',
            'vendor_id' => 8, // Mwanza Tech Solutions
            'bank_account_id' => $nmb->id,
            'reference_number' => 'NMB-EFT-449102-MTS',
            'payment_amount' => 850000.00,
            'status' => 'cleared',
            'notes' => 'Settlement for PO-2026-0002 wireless handheld scanner inventory',
            'creator_id' => $userId,
            'created_by' => $userId,
        ]);

        VendorPaymentAllocation::create([
            'payment_id' => $vp1->id,
            'invoice_id' => 2,
            'allocated_amount' => 850000.00,
        ]);

        $vp2 = VendorPayment::create([
            'payment_number' => 'VP-2026-00002',
            'payment_date' => '2026-10-08',
            'vendor_id' => 7, // Dar Industrial Hardware
            'bank_account_id' => $crdb->id,
            'reference_number' => 'CRDB-TISS-772910-DIH',
            'payment_amount' => 1200000.00,
            'status' => 'cleared',
            'notes' => 'Milestone advance payment for PO-2026-0001 industrial cables',
            'creator_id' => $userId,
            'created_by' => $userId,
        ]);

        VendorPaymentAllocation::create([
            'payment_id' => $vp2->id,
            'invoice_id' => 1,
            'allocated_amount' => 1200000.00,
        ]);

        // 6. Seed Revenue Categories & Revenues
        $revCat1 = RevenueCategories::updateOrCreate(
            ['category_code' => 'REV-CONS', 'created_by' => $userId],
            [
                'category_name' => 'Consulting & Integration Services',
                'description' => 'ERP setup, API integration, and on-site implementation',
                'is_active' => '1',
                'gl_account_id' => 29, // Service Revenue
                'creator_id' => $userId,
            ]
        );

        $revCat2 = RevenueCategories::updateOrCreate(
            ['category_code' => 'REV-SUB', 'created_by' => $userId],
            [
                'category_name' => 'ERP Subscriptions & Cloud Hosting',
                'description' => 'Monthly and annual SaaS subscriptions',
                'is_active' => '1',
                'gl_account_id' => 31, // Subscription Revenue
                'creator_id' => $userId,
            ]
        );

        Revenue::updateOrCreate(
            ['revenue_number' => 'REV-2026-0001', 'created_by' => $userId],
            [
                'revenue_date' => '2026-10-05',
                'category_id' => $revCat1->id,
                'bank_account_id' => $crdb->id,
                'chart_of_account_id' => 29,
                'reference_number' => 'CRDB-REV-ZNZ-01',
                'amount' => 1200000.00,
                'status' => 'posted',
                'description' => 'Enterprise ERP deployment retainer & cloud onboarding for Zanzibar Spice Traders',
                'approved_by' => $userId,
                'creator_id' => $userId,
            ]
        );

        Revenue::updateOrCreate(
            ['revenue_number' => 'REV-2026-0002', 'created_by' => $userId],
            [
                'revenue_date' => '2026-10-07',
                'category_id' => $revCat2->id,
                'bank_account_id' => $mpesa->id,
                'chart_of_account_id' => 31,
                'reference_number' => 'MP-REV-KLM-02',
                'amount' => 650000.00,
                'status' => 'posted',
                'description' => 'Annual automated cloud disaster recovery retainer for Kilimanjaro Logistics',
                'approved_by' => $userId,
                'creator_id' => $userId,
            ]
        );

        // 7. Seed Expense Categories & Expenses
        $expCat1 = ExpenseCategories::updateOrCreate(
            ['category_code' => 'EXP-NET', 'created_by' => $userId],
            [
                'category_name' => 'Datacenter & Fiber Connectivity',
                'description' => 'High speed dedicated leased lines & cloud infrastructure',
                'is_active' => '1',
                'gl_account_id' => 48, // Utilities Expense
                'creator_id' => $userId,
            ]
        );

        $expCat2 = ExpenseCategories::updateOrCreate(
            ['category_code' => 'EXP-TRA', 'created_by' => $userId],
            [
                'category_name' => 'TRA Tax & Regulatory Compliance',
                'description' => 'EFD certificates, fiscalization compliance, and tax stamps',
                'is_active' => '1',
                'gl_account_id' => 54, // Tax Expense
                'creator_id' => $userId,
            ]
        );

        Expense::updateOrCreate(
            ['expense_number' => 'EXP-2026-0001', 'created_by' => $userId],
            [
                'expense_date' => '2026-10-03',
                'category_id' => $expCat1->id,
                'bank_account_id' => $nmb->id,
                'chart_of_account_id' => 48,
                'reference_number' => 'NMB-EXP-VOD-01',
                'amount' => 380000.00,
                'status' => 'posted',
                'description' => 'Vodacom dedicated optic fiber datacenter link subscription - October 2026',
                'approved_by' => $userId,
                'creator_id' => $userId,
            ]
        );

        Expense::updateOrCreate(
            ['expense_number' => 'EXP-2026-0002', 'created_by' => $userId],
            [
                'expense_date' => '2026-10-05',
                'category_id' => $expCat2->id,
                'bank_account_id' => $crdb->id,
                'chart_of_account_id' => 54,
                'reference_number' => 'CRDB-EXP-TRA-02',
                'amount' => 250000.00,
                'status' => 'posted',
                'description' => 'TRA EFD Virtual Device Security & cryptographic certificate renewal fees',
                'approved_by' => $userId,
                'creator_id' => $userId,
            ]
        );

        // 8. Tanzania 18% TRA VAT Integration (Seed sales_invoice_item_taxes & purchase_invoice_item_taxes)
        DB::table('sales_invoice_item_taxes')->delete();
        foreach (DB::table('sales_invoice_items')->get() as $item) {
            DB::table('sales_invoice_item_taxes')->insert([
                'item_id' => $item->id,
                'tax_name' => 'VAT (18%)',
                'tax_rate' => 18.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('purchase_invoice_item_taxes')->delete();
        foreach (DB::table('purchase_invoice_items')->get() as $item) {
            DB::table('purchase_invoice_item_taxes')->insert([
                'item_id' => $item->id,
                'tax_name' => 'VAT (18%)',
                'tax_rate' => 18.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 9. Seed Balanced Double-Entry Journal Entries (`journal_entries` & `journal_entry_items`)
        DB::table('journal_entry_items')->where('created_by', $userId)->delete();
        DB::table('journal_entries')->where('created_by', $userId)->delete();

        $journals = [
            // Sales Invoices Journals
            [
                'header' => [
                    'journal_number' => 'JN-2026-0001',
                    'journal_date' => '2026-10-01',
                    'entry_type' => 'automatic',
                    'reference_type' => 'SalesInvoice',
                    'reference_id' => 1,
                    'description' => 'Sales Invoice INV-2026-0001 for Safari Holdings Ltd',
                    'total_debit' => 531000.00,
                    'total_credit' => 531000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 7, 'description' => 'Accounts Receivable (Safari Holdings)', 'debit_amount' => 531000.00, 'credit_amount' => 0.00], // 1100 A/R
                    ['account_id' => 27, 'description' => 'Sales Revenue - Fiber Modems', 'debit_amount' => 0.00, 'credit_amount' => 450000.00], // 4100 Sales
                    ['account_id' => 19, 'description' => 'VAT Payable (TRA 18% Output VAT)', 'debit_amount' => 0.00, 'credit_amount' => 81000.00], // 2210 VAT Output
                ]
            ],
            [
                'header' => [
                    'journal_number' => 'JN-2026-0002',
                    'journal_date' => '2026-10-02',
                    'entry_type' => 'automatic',
                    'reference_type' => 'SalesInvoice',
                    'reference_id' => 2,
                    'description' => 'Sales Invoice INV-2026-0002 for Kilimanjaro Logistics',
                    'total_debit' => 2183000.00,
                    'total_credit' => 2183000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 7, 'description' => 'Accounts Receivable (Kilimanjaro Logistics)', 'debit_amount' => 2183000.00, 'credit_amount' => 0.00],
                    ['account_id' => 27, 'description' => 'Sales Revenue - Thermal Barcode Printers', 'debit_amount' => 0.00, 'credit_amount' => 1850000.00],
                    ['account_id' => 19, 'description' => 'VAT Payable (TRA 18% Output VAT)', 'debit_amount' => 0.00, 'credit_amount' => 333000.00],
                ]
            ],
            [
                'header' => [
                    'journal_number' => 'JN-2026-0003',
                    'journal_date' => '2026-10-04',
                    'entry_type' => 'automatic',
                    'reference_type' => 'SalesInvoice',
                    'reference_id' => 3,
                    'description' => 'Sales Invoice INV-2026-0003 for Serengeti Breweries Ltd',
                    'total_debit' => 5074000.00,
                    'total_credit' => 5074000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 7, 'description' => 'Accounts Receivable (Serengeti Breweries)', 'debit_amount' => 5074000.00, 'credit_amount' => 0.00],
                    ['account_id' => 27, 'description' => 'Sales Revenue - Enterprise Server Rack', 'debit_amount' => 0.00, 'credit_amount' => 4300000.00],
                    ['account_id' => 19, 'description' => 'VAT Payable (TRA 18% Output VAT)', 'debit_amount' => 0.00, 'credit_amount' => 774000.00],
                ]
            ],

            // Purchase Invoices Journals
            [
                'header' => [
                    'journal_number' => 'JN-2026-0004',
                    'journal_date' => '2026-10-02',
                    'entry_type' => 'automatic',
                    'reference_type' => 'PurchaseInvoice',
                    'reference_id' => 1,
                    'description' => 'Purchase Invoice PO-2026-0001 from Dar Industrial Hardware',
                    'total_debit' => 2832000.00,
                    'total_credit' => 2832000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 8, 'description' => 'Inventory Asset - Industrial Grade Cables', 'debit_amount' => 2400000.00, 'credit_amount' => 0.00], // 1200 Inventory
                    ['account_id' => 11, 'description' => 'Tax Receivable (TRA 18% Input VAT)', 'debit_amount' => 432000.00, 'credit_amount' => 0.00], // 1500 Input VAT
                    ['account_id' => 16, 'description' => 'Accounts Payable (Dar Industrial Hardware)', 'debit_amount' => 0.00, 'credit_amount' => 2832000.00], // 2000 A/P
                ]
            ],
            [
                'header' => [
                    'journal_number' => 'JN-2026-0005',
                    'journal_date' => '2026-10-03',
                    'entry_type' => 'automatic',
                    'reference_type' => 'PurchaseInvoice',
                    'reference_id' => 2,
                    'description' => 'Purchase Invoice PO-2026-0002 from Mwanza Tech Solutions',
                    'total_debit' => 1003000.00,
                    'total_credit' => 1003000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 8, 'description' => 'Inventory Asset - Barcode Scanners', 'debit_amount' => 850000.00, 'credit_amount' => 0.00],
                    ['account_id' => 11, 'description' => 'Tax Receivable (TRA 18% Input VAT)', 'debit_amount' => 153000.00, 'credit_amount' => 0.00],
                    ['account_id' => 16, 'description' => 'Accounts Payable (Mwanza Tech Solutions)', 'debit_amount' => 0.00, 'credit_amount' => 1003000.00],
                ]
            ],

            // Bank Transfers Journals
            [
                'header' => [
                    'journal_number' => 'JN-2026-0006',
                    'journal_date' => '2026-10-02',
                    'entry_type' => 'automatic',
                    'reference_type' => 'BankTransfer',
                    'reference_id' => $trf1->id,
                    'description' => 'Bank Transfer TRF-2026-0001 M-Pesa Till 16381432 sweep to CRDB Corporate',
                    'total_debit' => 3505000.00,
                    'total_credit' => 3505000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 3, 'description' => 'CRDB Corporate Operating Account (1010)', 'debit_amount' => 3500000.00, 'credit_amount' => 0.00],
                    ['account_id' => 53, 'description' => 'Vodacom Merchant Cashout Fees (5510)', 'debit_amount' => 5000.00, 'credit_amount' => 0.00],
                    ['account_id' => 1, 'description' => 'Vodacom M-Pesa Business Till 16381432 (1000)', 'debit_amount' => 0.00, 'credit_amount' => 3505000.00],
                ]
            ],
            [
                'header' => [
                    'journal_number' => 'JN-2026-0007',
                    'journal_date' => '2026-10-06',
                    'entry_type' => 'automatic',
                    'reference_type' => 'BankTransfer',
                    'reference_id' => $trf2->id,
                    'description' => 'Bank Transfer TRF-2026-0002 TIPS Rebalancing from CRDB to NMB',
                    'total_debit' => 2002500.00,
                    'total_credit' => 2002500.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 4, 'description' => 'NMB Operational Branch Account (1020)', 'debit_amount' => 2000000.00, 'credit_amount' => 0.00],
                    ['account_id' => 53, 'description' => 'Bank TIPS Transfer Fees (5510)', 'debit_amount' => 2500.00, 'credit_amount' => 0.00],
                    ['account_id' => 3, 'description' => 'CRDB Corporate Operating Account (1010)', 'debit_amount' => 0.00, 'credit_amount' => 2002500.00],
                ]
            ],

            // Customer Payments Journals
            [
                'header' => [
                    'journal_number' => 'JN-2026-0008',
                    'journal_date' => '2026-10-03',
                    'entry_type' => 'automatic',
                    'reference_type' => 'CustomerPayment',
                    'reference_id' => $cp1->id,
                    'description' => 'Customer Payment RV-2026-00001 Safari Holdings Ltd',
                    'total_debit' => 450000.00,
                    'total_credit' => 450000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 1, 'description' => 'Vodacom M-Pesa Till 16381432 (1000)', 'debit_amount' => 450000.00, 'credit_amount' => 0.00],
                    ['account_id' => 7, 'description' => 'Accounts Receivable (Safari Holdings)', 'debit_amount' => 0.00, 'credit_amount' => 450000.00],
                ]
            ],
            [
                'header' => [
                    'journal_number' => 'JN-2026-0009',
                    'journal_date' => '2026-10-07',
                    'entry_type' => 'automatic',
                    'reference_type' => 'CustomerPayment',
                    'reference_id' => $cp2->id,
                    'description' => 'Customer Payment RV-2026-00002 Serengeti Breweries Ltd',
                    'total_debit' => 2000000.00,
                    'total_credit' => 2000000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 3, 'description' => 'CRDB Corporate Operating Account (1010)', 'debit_amount' => 2000000.00, 'credit_amount' => 0.00],
                    ['account_id' => 7, 'description' => 'Accounts Receivable (Serengeti Breweries)', 'debit_amount' => 0.00, 'credit_amount' => 2000000.00],
                ]
            ],

            // Vendor Payments Journals
            [
                'header' => [
                    'journal_number' => 'JN-2026-0010',
                    'journal_date' => '2026-10-04',
                    'entry_type' => 'automatic',
                    'reference_type' => 'VendorPayment',
                    'reference_id' => $vp1->id,
                    'description' => 'Vendor Payment VP-2026-00001 to Mwanza Tech Solutions',
                    'total_debit' => 850000.00,
                    'total_credit' => 850000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 16, 'description' => 'Accounts Payable (Mwanza Tech Solutions)', 'debit_amount' => 850000.00, 'credit_amount' => 0.00],
                    ['account_id' => 4, 'description' => 'NMB Operational Branch Account (1020)', 'debit_amount' => 0.00, 'credit_amount' => 850000.00],
                ]
            ],
            [
                'header' => [
                    'journal_number' => 'JN-2026-0011',
                    'journal_date' => '2026-10-08',
                    'entry_type' => 'automatic',
                    'reference_type' => 'VendorPayment',
                    'reference_id' => $vp2->id,
                    'description' => 'Vendor Payment VP-2026-00002 to Dar Industrial Hardware',
                    'total_debit' => 1200000.00,
                    'total_credit' => 1200000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 16, 'description' => 'Accounts Payable (Dar Industrial Hardware)', 'debit_amount' => 1200000.00, 'credit_amount' => 0.00],
                    ['account_id' => 3, 'description' => 'CRDB Corporate Operating Account (1010)', 'debit_amount' => 0.00, 'credit_amount' => 1200000.00],
                ]
            ],

            // Revenues Journals
            [
                'header' => [
                    'journal_number' => 'JN-2026-0012',
                    'journal_date' => '2026-10-05',
                    'entry_type' => 'automatic',
                    'reference_type' => 'Revenue',
                    'reference_id' => 1,
                    'description' => 'Service Revenue REV-2026-0001 from Zanzibar Spice Traders',
                    'total_debit' => 1200000.00,
                    'total_credit' => 1200000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 3, 'description' => 'CRDB Corporate Operating Account (1010)', 'debit_amount' => 1200000.00, 'credit_amount' => 0.00],
                    ['account_id' => 29, 'description' => 'Service Revenue (4200)', 'debit_amount' => 0.00, 'credit_amount' => 1200000.00],
                ]
            ],

            // Expenses Journals
            [
                'header' => [
                    'journal_number' => 'JN-2026-0013',
                    'journal_date' => '2026-10-03',
                    'entry_type' => 'automatic',
                    'reference_type' => 'Expense',
                    'reference_id' => 1,
                    'description' => 'Operating Expense EXP-2026-0001 Fiber Internet',
                    'total_debit' => 380000.00,
                    'total_credit' => 380000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 48, 'description' => 'Utilities Expense - Vodacom Fiber (5400)', 'debit_amount' => 380000.00, 'credit_amount' => 0.00],
                    ['account_id' => 4, 'description' => 'NMB Operational Branch Account (1020)', 'debit_amount' => 0.00, 'credit_amount' => 380000.00],
                ]
            ],
            [
                'header' => [
                    'journal_number' => 'JN-2026-0014',
                    'journal_date' => '2026-10-05',
                    'entry_type' => 'automatic',
                    'reference_type' => 'Expense',
                    'reference_id' => 2,
                    'description' => 'Regulatory Expense EXP-2026-0002 TRA EFD Virtual Device Security',
                    'total_debit' => 250000.00,
                    'total_credit' => 250000.00,
                    'status' => 'posted',
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ],
                'items' => [
                    ['account_id' => 54, 'description' => 'Tax & Regulatory Compliance Expense (5600)', 'debit_amount' => 250000.00, 'credit_amount' => 0.00],
                    ['account_id' => 3, 'description' => 'CRDB Corporate Operating Account (1010)', 'debit_amount' => 0.00, 'credit_amount' => 250000.00],
                ]
            ],
        ];

        foreach ($journals as $jData) {
            $entry = JournalEntry::create($jData['header']);
            foreach ($jData['items'] as $item) {
                JournalEntryItem::create(array_merge($item, [
                    'journal_entry_id' => $entry->id,
                    'creator_id' => $userId,
                    'created_by' => $userId,
                ]));
            }
        }

        $this->command->info('Mutamark Accounting & Financial Ledger data seeded successfully.');
    }
}
