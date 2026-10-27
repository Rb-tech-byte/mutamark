<?php

namespace Database\Seeders;

use App\Models\ApprovalAction;
use App\Models\ApprovalPolicy;
use App\Models\ApprovalRequest;
use App\Models\AssetDisposal;
use Workdo\Account\Models\Expense;
use Workdo\Account\Models\ExpenseCategories;
use Workdo\Account\Models\BankAccount;
use App\Models\PurchaseInvoice;
use App\Models\SalesProposal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MutamarkApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds for Mutamark Multi-Tier Approval Workflow Engine (SRS Module 24).
     */
    public function run(): void
    {
        $creatorId = 2; // Company Account
        $adminUser = User::where('email', 'company@example.com')->first() ?? User::first();
        $officerUser = User::where('email', 'like', '%@%')->where('id', '!=', $adminUser->id)->first() ?? $adminUser;

        // 1. Seed Approval Policies across Document Types
        $policyConfigs = [
            // Purchase Invoices (Vendor Bills)
            [
                'name' => 'Supplier Bill - Tier 1 Operational Review',
                'document_type' => 'purchase_invoice',
                'tier_level' => 1,
                'tier_name' => 'Operational Officer',
                'min_amount' => 0,
                'max_amount' => 500000,
                'required_role' => 'officer',
                'requires_digital_signature' => false,
                'enforce_segregation_of_duties' => true,
            ],
            [
                'name' => 'Supplier Bill - Tier 2 Finance Manager',
                'document_type' => 'purchase_invoice',
                'tier_level' => 2,
                'tier_name' => 'Finance / Procurement Manager',
                'min_amount' => 500000.01,
                'max_amount' => 5000000,
                'required_role' => 'manager',
                'requires_digital_signature' => true,
                'enforce_segregation_of_duties' => true,
            ],
            [
                'name' => 'Supplier Bill - Tier 3 Managing Director Sign-Off',
                'document_type' => 'purchase_invoice',
                'tier_level' => 3,
                'tier_name' => 'Managing Director / Executive',
                'min_amount' => 5000000.01,
                'max_amount' => null,
                'required_role' => 'company',
                'requires_digital_signature' => true,
                'enforce_segregation_of_duties' => true,
            ],

            // Operational Expenses
            [
                'name' => 'Operational Expense - Tier 1 Department Lead',
                'document_type' => 'expense',
                'tier_level' => 1,
                'tier_name' => 'Department Lead',
                'min_amount' => 0,
                'max_amount' => 500000,
                'required_role' => 'officer',
                'requires_digital_signature' => false,
                'enforce_segregation_of_duties' => true,
            ],
            [
                'name' => 'Operational Expense - Tier 2 Finance Controller',
                'document_type' => 'expense',
                'tier_level' => 2,
                'tier_name' => 'Financial Controller',
                'min_amount' => 500000.01,
                'max_amount' => 5000000,
                'required_role' => 'manager',
                'requires_digital_signature' => true,
                'enforce_segregation_of_duties' => true,
            ],
            [
                'name' => 'Operational Expense - Tier 3 Executive Authorization',
                'document_type' => 'expense',
                'tier_level' => 3,
                'tier_name' => 'Executive Director',
                'min_amount' => 5000000.01,
                'max_amount' => null,
                'required_role' => 'company',
                'requires_digital_signature' => true,
                'enforce_segregation_of_duties' => true,
            ],

            // Enterprise Sales Proposals / Quotes
            [
                'name' => 'Sales Proposal - Tier 1 Sales Lead',
                'document_type' => 'sales_proposal',
                'tier_level' => 1,
                'tier_name' => 'Sales Supervisor',
                'min_amount' => 0,
                'max_amount' => 2000000,
                'required_role' => 'officer',
                'requires_digital_signature' => false,
                'enforce_segregation_of_duties' => true,
            ],
            [
                'name' => 'Sales Proposal - Tier 2 Commercial Manager',
                'document_type' => 'sales_proposal',
                'tier_level' => 2,
                'tier_name' => 'Commercial Director',
                'min_amount' => 2000000.01,
                'max_amount' => 10000000,
                'required_role' => 'manager',
                'requires_digital_signature' => true,
                'enforce_segregation_of_duties' => true,
            ],
            [
                'name' => 'Sales Proposal - Tier 3 Managing Director',
                'document_type' => 'sales_proposal',
                'tier_level' => 3,
                'tier_name' => 'Managing Director',
                'min_amount' => 10000000.01,
                'max_amount' => null,
                'required_role' => 'company',
                'requires_digital_signature' => true,
                'enforce_segregation_of_duties' => true,
            ],

            // Asset Disposals
            [
                'name' => 'Asset Write-off & Disposal - Executive Authority',
                'document_type' => 'asset_disposal',
                'tier_level' => 1,
                'tier_name' => 'Managing Director / Board',
                'min_amount' => 0,
                'max_amount' => null,
                'required_role' => 'company',
                'requires_digital_signature' => true,
                'enforce_segregation_of_duties' => true,
            ],
        ];

        foreach ($policyConfigs as $pc) {
            ApprovalPolicy::firstOrCreate([
                'document_type' => $pc['document_type'],
                'tier_level' => $pc['tier_level'],
            ], array_merge($pc, [
                'is_active' => true,
                'creator_id' => $creatorId,
                'created_by' => $adminUser->id,
            ]));
        }

        // 2. Fetch or create representative documents
        $bill = PurchaseInvoice::first() ?? PurchaseInvoice::create([
            'invoice_number' => 'INV-2026-00002',
            'invoice_date' => '2026-10-03',
            'due_date' => '2026-11-02',
            'vendor_id' => 1,
            'warehouse_id' => 1,
            'subtotal' => 2500000,
            'tax_amount' => 450000,
            'total_amount' => 2950000,
            'balance_amount' => 2950000,
            'status' => 'posted',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        $expCategory = ExpenseCategories::first();
        $bankAccount = BankAccount::first();

        $exp = Expense::first();
        if (!$exp && $expCategory && $bankAccount) {
            $exp = Expense::create([
                'expense_number' => 'EXP-2026-00001',
                'expense_date' => '2026-10-02',
                'category_id' => $expCategory->id,
                'bank_account_id' => $bankAccount->id,
                'amount' => 450000,
                'status' => 'draft',
                'description' => 'Depot diesel generator replenishment for DSM logistics hub',
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]);
        }

        $proposal = SalesProposal::first() ?? SalesProposal::create([
            'proposal_number' => 'QT-2026-00001',
            'proposal_date' => '2026-10-05',
            'due_date' => '2026-11-05',
            'customer_id' => 1,
            'subtotal' => 12288135.59,
            'tax_amount' => 2211864.41,
            'total_amount' => 14500000,
            'status' => 'draft',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        $disposal = AssetDisposal::first() ?? AssetDisposal::create([
            'disposal_number' => 'DSP-2026-00001',
            'asset_id' => 1,
            'disposal_date' => '2026-10-08',
            'disposal_type' => 'write_off',
            'sale_amount' => 0,
            'book_value_at_disposal' => 18000000,
            'gain_loss_amount' => -18000000,
            'reason' => 'Total transmission breakdown post warranty expiry.',
            'creator_id' => $creatorId,
            'created_by' => $creatorId,
        ]);

        // 3. Seed Realistic Approval Requests
        // Request 1: Supplier Bill (2,950,000 TZS -> Under Review by Tier 2 Manager)
        $req1 = ApprovalRequest::firstOrCreate(['request_number' => 'APR-2026-00001'], [
            'document_type' => 'purchase_invoice',
            'document_id' => $bill->id,
            'document_number' => $bill->invoice_number,
            'title' => 'Supplier Bill: High-Density 48-Port Fiber Distribution Panels',
            'amount' => (float) $bill->total_amount,
            'currency' => 'TZS',
            'current_tier' => 2,
            'max_tier' => 2,
            'status' => 'under_review',
            'requester_id' => $officerUser->id,
            'current_approver_role' => 'manager',
            'current_approver_id' => null,
            'notes' => 'Requires Tier 2 authorization due to amount exceeding 500k TZS threshold.',
            'creator_id' => $creatorId,
            'created_by' => $officerUser->id,
        ]);

        ApprovalAction::firstOrCreate([
            'approval_request_id' => $req1->id,
            'action' => 'submit',
        ], [
            'tier_level' => 1,
            'actor_id' => $officerUser->id,
            'previous_status' => 'draft',
            'new_status' => 'submitted',
            'comments' => 'Initial bill submitted following warehouse GRN receipt verification.',
            'ip_address' => '127.0.0.1',
        ]);

        ApprovalAction::firstOrCreate([
            'approval_request_id' => $req1->id,
            'action' => 'review',
        ], [
            'tier_level' => 2,
            'actor_id' => $adminUser->id,
            'previous_status' => 'submitted',
            'new_status' => 'under_review',
            'comments' => 'Under finance review for price and physical discrepancy verification.',
            'ip_address' => '127.0.0.1',
        ]);

        // Request 2: Expense (450,000 TZS -> Tier 1 Approved)
        $expId = $exp ? $exp->id : 1;
        $expNumber = $exp ? $exp->expense_number : 'EXP-2026-00001';
        $expAmount = $exp ? (float) $exp->amount : 450000.0;

        $req2 = ApprovalRequest::firstOrCreate(['request_number' => 'APR-2026-00002'], [
            'document_type' => 'expense',
            'document_id' => $expId,
            'document_number' => $expNumber,
            'title' => 'Operational Fuel Expense: DSM Hub Backup Power Generator',
            'amount' => $expAmount,
            'currency' => 'TZS',
            'current_tier' => 1,
            'max_tier' => 1,
            'status' => 'approved',
            'requester_id' => $officerUser->id,
            'current_approver_role' => 'officer',
            'current_approver_id' => $adminUser->id,
            'notes' => 'Routine backup power generator diesel purchase within officer limit.',
            'creator_id' => $creatorId,
            'created_by' => $officerUser->id,
        ]);

        ApprovalAction::firstOrCreate([
            'approval_request_id' => $req2->id,
            'action' => 'approve',
        ], [
            'tier_level' => 1,
            'actor_id' => $adminUser->id,
            'previous_status' => 'submitted',
            'new_status' => 'approved',
            'comments' => 'Approved as per routine operations expenditure budget.',
            'ip_address' => '127.0.0.1',
        ]);

        // Request 3: Asset Disposal (18,000,000 TZS -> Tier 3 Managing Director Submitted)
        $req3 = ApprovalRequest::firstOrCreate(['request_number' => 'APR-2026-00003'], [
            'document_type' => 'asset_disposal',
            'document_id' => $disposal->id,
            'document_number' => $disposal->disposal_number,
            'title' => 'Asset Write-off Authorization: Toyota Hilux Logistics Van',
            'amount' => (float) $disposal->book_value_at_disposal,
            'currency' => 'TZS',
            'current_tier' => 1,
            'max_tier' => 1,
            'status' => 'submitted',
            'requester_id' => $officerUser->id,
            'current_approver_role' => 'company',
            'current_approver_id' => $adminUser->id,
            'notes' => 'Executive board authorization requested for vehicle disposal write-off.',
            'creator_id' => $creatorId,
            'created_by' => $officerUser->id,
        ]);

        ApprovalAction::firstOrCreate([
            'approval_request_id' => $req3->id,
            'action' => 'submit',
        ], [
            'tier_level' => 1,
            'actor_id' => $officerUser->id,
            'previous_status' => 'draft',
            'new_status' => 'submitted',
            'comments' => 'Disposal inspection report attached. Awaiting Managing Director signature.',
            'ip_address' => '127.0.0.1',
        ]);

        // Request 4: Enterprise Proposal (14,500,000 TZS -> Signed with Cryptographic Verification)
        $req4 = ApprovalRequest::firstOrCreate(['request_number' => 'APR-2026-00004'], [
            'document_type' => 'sales_proposal',
            'document_id' => $proposal->id,
            'document_number' => $proposal->proposal_number,
            'title' => 'Client Quotation: Commercial Fiber & 5G Backbone Setup',
            'amount' => (float) $proposal->total_amount,
            'currency' => 'TZS',
            'current_tier' => 3,
            'max_tier' => 3,
            'status' => 'signed',
            'requester_id' => $officerUser->id,
            'current_approver_role' => 'company',
            'current_approver_id' => $adminUser->id,
            'notes' => 'High-value enterprise contract signed by Managing Director with SHA-256 seal.',
            'creator_id' => $creatorId,
            'created_by' => $officerUser->id,
        ]);

        // Generate SHA-256 digital signature
        $sig = $req4->generateSignature($adminUser->id);

        ApprovalAction::firstOrCreate([
            'approval_request_id' => $req4->id,
            'action' => 'sign',
        ], [
            'tier_level' => 3,
            'actor_id' => $adminUser->id,
            'previous_status' => 'approved',
            'new_status' => 'signed',
            'comments' => 'Digitally signed with cryptographic verification code for client issuance.',
            'ip_address' => '127.0.0.1',
            'digital_signature' => $sig,
        ]);
    }
}
