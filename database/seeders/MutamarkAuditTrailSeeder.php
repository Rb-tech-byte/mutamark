<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FinancialAuditLog;
use App\Services\FinancialControlService;
use Illuminate\Support\Facades\DB;

class MutamarkAuditTrailSeeder extends Seeder
{
    public function run(): void
    {
        $company = User::where('type', 'company')->first() ?? User::find(2);
        if (!$company) {
            $this->command->warn('Company user not found, skipping audit trail seeding.');
            return;
        }

        $creatorId = $company->id;

        // Clear existing test audit logs to build a clean cryptographic chain
        FinancialAuditLog::truncate();

        // 1. Ensure sample posted Double-Entry Journals exist
        $cashAcc = DB::table('chart_of_accounts')->where('account_code', '1000')->first() ?? DB::table('chart_of_accounts')->first();
        $expenseAcc = DB::table('chart_of_accounts')->where('normal_balance', 'debit')->where('id', '!=', $cashAcc?->id)->first() ?? $cashAcc;

        $jv1 = DB::table('journal_entries')->where('journal_number', 'JV-2026-00001')->first();
        if (!$jv1) {
            $jv1Id = DB::table('journal_entries')->insertGetId([
                'journal_number' => 'JV-2026-00001',
                'journal_date' => '2026-10-02',
                'entry_type' => 'manual',
                'reference_type' => 'Manual',
                'reference_id' => null,
                'description' => 'Monthly Network Infrastructure Depreciation & Cloud Server Accrual',
                'total_debit' => 1850000.00,
                'total_credit' => 1850000.00,
                'status' => 'posted',
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
                'created_at' => '2026-10-02 09:30:00',
                'updated_at' => '2026-10-02 09:30:00',
            ]);

            if ($cashAcc && $expenseAcc) {
                DB::table('journal_entry_items')->insert([
                    [
                        'journal_entry_id' => $jv1Id,
                        'account_id' => $expenseAcc->id,
                        'description' => 'Network Depreciation Expense',
                        'debit_amount' => 1850000.00,
                        'credit_amount' => 0.00,
                        'creator_id' => $creatorId,
                        'created_by' => $creatorId,
                        'created_at' => '2026-10-02 09:30:00',
                        'updated_at' => '2026-10-02 09:30:00',
                    ],
                    [
                        'journal_entry_id' => $jv1Id,
                        'account_id' => $cashAcc->id,
                        'description' => 'Accumulated Reserve Provision',
                        'debit_amount' => 0.00,
                        'credit_amount' => 1850000.00,
                        'creator_id' => $creatorId,
                        'created_by' => $creatorId,
                        'created_at' => '2026-10-02 09:30:00',
                        'updated_at' => '2026-10-02 09:30:00',
                    ],
                ]);
            }
        }

        $jv2 = DB::table('journal_entries')->where('journal_number', 'JV-2026-00002')->first();
        if (!$jv2) {
            $jv2Id = DB::table('journal_entries')->insertGetId([
                'journal_number' => 'JV-2026-00002',
                'journal_date' => '2026-10-04',
                'entry_type' => 'manual',
                'reference_type' => 'Manual',
                'reference_id' => null,
                'description' => 'Advance Office Lease Accrual - Q4 2026 (Samora Tower)',
                'total_debit' => 3500000.00,
                'total_credit' => 3500000.00,
                'status' => 'reversed',
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
                'created_at' => '2026-10-04 11:15:00',
                'updated_at' => '2026-10-05 14:00:00',
            ]);

            if ($cashAcc && $expenseAcc) {
                DB::table('journal_entry_items')->insert([
                    [
                        'journal_entry_id' => $jv2Id,
                        'account_id' => $expenseAcc->id,
                        'description' => 'Advance Office Rent Account',
                        'debit_amount' => 3500000.00,
                        'credit_amount' => 0.00,
                        'creator_id' => $creatorId,
                        'created_by' => $creatorId,
                        'created_at' => '2026-10-04 11:15:00',
                        'updated_at' => '2026-10-04 11:15:00',
                    ],
                    [
                        'journal_entry_id' => $jv2Id,
                        'account_id' => $cashAcc->id,
                        'description' => 'Prepaid Lease Accrual',
                        'debit_amount' => 0.00,
                        'credit_amount' => 3500000.00,
                        'creator_id' => $creatorId,
                        'created_by' => $creatorId,
                        'created_at' => '2026-10-04 11:15:00',
                        'updated_at' => '2026-10-04 11:15:00',
                    ],
                ]);
            }
        }

        // 2. Sequential Cryptographic Audit Chain Generation
        $events = [
            [
                'audit_num' => 'AUD-2026-00001',
                'time' => '2026-10-01 10:15:00',
                'event_type' => 'INVOICE_LOCKED',
                'module' => 'Sales',
                'ref_num' => 'INV-2026-00001',
                'actor' => 'Juma Bakari Mwalimu',
                'role' => 'Senior Full-Stack Engineer',
                'ip' => '192.168.1.45',
                'action' => 'Sales invoice INV-2026-00001 locked to immutable state',
                'details' => 'Full settlement received via CRDB Bank Bank Transfer. Under Financial Control Policy (Appendix B), status changed to paid and record is permanently sealed.',
                'severity' => 'info',
                'old' => ['status' => 'sent', 'balance_due' => 4500000.0],
                'new' => ['status' => 'paid', 'balance_due' => 0.0, 'immutability' => 'enforced'],
            ],
            [
                'audit_num' => 'AUD-2026-00002',
                'time' => '2026-10-02 14:22:10',
                'event_type' => 'IMMUTABILITY_BLOCKED',
                'module' => 'Sales',
                'ref_num' => 'INV-2026-00001',
                'actor' => 'Temporary Trainee User',
                'role' => 'Accounts Clerk',
                'ip' => '192.168.1.112',
                'action' => 'Attempted deletion of paid invoice INV-2026-00001 blocked by immutability guard',
                'details' => 'MUTAMARK ERP ENFORCEMENT: Documents with status "paid" or "posted" cannot be altered or removed. Attempt logged for compliance review.',
                'severity' => 'critical',
                'old' => ['status' => 'paid'],
                'new' => ['attempted_action' => 'delete', 'result' => 'HTTP_403_RESTRICTED'],
            ],
            [
                'audit_num' => 'AUD-2026-00003',
                'time' => '2026-10-03 09:30:00',
                'event_type' => 'JOURNAL_POSTED',
                'module' => 'Accounting',
                'ref_num' => 'JV-2026-00001',
                'actor' => 'Amina Salum Kibona',
                'role' => 'Head of Finance & Tax Compliance',
                'ip' => '192.168.1.20',
                'action' => 'Posted Double-Entry Journal Voucher JV-2026-00001 to General Ledger',
                'details' => 'Balanced entry totaling TZS 1,850,000 for monthly network depreciation across 2 accounts.',
                'severity' => 'info',
                'old' => ['status' => 'draft'],
                'new' => ['status' => 'posted', 'total_debit' => 1850000.0, 'total_credit' => 1850000.0],
            ],
            [
                'audit_num' => 'AUD-2026-00004',
                'time' => '2026-10-04 11:45:18',
                'event_type' => 'IMMUTABILITY_BLOCKED',
                'module' => 'Accounting',
                'ref_num' => 'JV-2026-00001',
                'actor' => 'Baraka Joseph Mwangi',
                'role' => 'Logistics Manager',
                'ip' => '192.168.1.33',
                'action' => 'Attempted direct alteration of posted journal JV-2026-00001 rejected',
                'details' => 'DoubleEntry Ledger Immutability: Posted journal vouchers cannot be edited. User directed to issue Reversal Journal Voucher (RJV).',
                'severity' => 'critical',
                'old' => ['status' => 'posted'],
                'new' => ['attempted_field' => 'total_debit', 'result' => 'BLOCKED_BY_POLICY'],
            ],
            [
                'audit_num' => 'AUD-2026-00005',
                'time' => '2026-10-05 14:00:00',
                'event_type' => 'JOURNAL_REVERSED',
                'module' => 'Accounting',
                'ref_num' => 'RJV-2026-00001',
                'actor' => 'Amina Salum Kibona',
                'role' => 'Head of Finance & Tax Compliance',
                'ip' => '192.168.1.20',
                'action' => 'Executed Reversal Journal Voucher RJV-2026-00001 reversing JV-2026-00002',
                'details' => 'Reason: Landlord renegotiated Q4 lease rate downwards; advance accrual reversed cleanly with inverted Debits and Credits of TZS 3,500,000.',
                'severity' => 'info',
                'old' => ['target_journal' => 'JV-2026-00002', 'original_status' => 'posted'],
                'new' => ['rjv_number' => 'RJV-2026-00001', 'target_status' => 'reversed', 'offset_amount' => 3500000.0],
            ],
            [
                'audit_num' => 'AUD-2026-00006',
                'time' => '2026-10-06 16:10:45',
                'event_type' => 'NEGATIVE_STOCK_BLOCKED',
                'module' => 'Inventory',
                'ref_num' => 'ST-2026-00002',
                'actor' => 'Daudi Hamisi Mfaume',
                'role' => 'Field Operations Specialist',
                'ip' => '192.168.2.14',
                'action' => 'Blocked warehouse transfer exceeding physical stock balance',
                'details' => 'Inventory Guard: Transfer requested 20 units of 4G/5G Enterprise Router at Arusha Hub, but only 8 physical serialized units exist in stock. Negative balance averted.',
                'severity' => 'warning',
                'old' => ['warehouse_stock' => 8, 'requested' => 20],
                'new' => ['dispatch_status' => 'HELD_FOR_RESTOCK', 'deficit' => 12],
            ],
            [
                'audit_num' => 'AUD-2026-00007',
                'time' => '2026-10-07 10:05:00',
                'event_type' => 'CREDIT_NOTE_ISSUED',
                'module' => 'Accounting',
                'ref_num' => 'CN-2026-00001',
                'actor' => 'Amina Salum Kibona',
                'role' => 'Head of Finance & Tax Compliance',
                'ip' => '192.168.1.20',
                'action' => 'Issued Credit Note CN-2026-00001 against locked invoice INV-2026-00001',
                'details' => 'Authorized billing concession of TZS 150,000 for early payment rebate. Ledger accounts adjusted without violating invoice immutability.',
                'severity' => 'info',
                'old' => ['invoice_number' => 'INV-2026-00001', 'rebate_applied' => false],
                'new' => ['credit_note' => 'CN-2026-00001', 'amount' => 150000.0, 'vat_offset_18%' => 27000.0],
            ],
            [
                'audit_num' => 'AUD-2026-00008',
                'time' => '2026-10-08 08:00:00',
                'event_type' => 'INVOICE_LOCKED',
                'module' => 'Procurement',
                'ref_num' => 'BILL-2026-00001',
                'actor' => 'System Automated Engine',
                'role' => '3-Way Match Processor',
                'ip' => '127.0.0.1',
                'action' => 'Purchase Bill BILL-2026-00001 sealed post 3-Way Match verification',
                'details' => 'Physical GRN goods received matched Purchase Order and Vendor Tax Invoice within 0.00% variance tolerance. Approved for disbursement.',
                'severity' => 'info',
                'old' => ['status' => 'draft', 'match_verification' => 'pending'],
                'new' => ['status' => 'posted', 'match_verification' => 'PASSED_100%'],
            ],
        ];

        $prevHash = 'GENESIS_HASH_MUTAMARK_ERP_2026';

        foreach ($events as $ev) {
            $currentHash = FinancialAuditLog::computeHash(
                $ev['audit_num'],
                $ev['event_type'],
                $ev['ref_num'],
                $ev['actor'],
                $ev['time'],
                $prevHash
            );

            FinancialAuditLog::create([
                'audit_number' => $ev['audit_num'],
                'event_type' => $ev['event_type'],
                'module' => $ev['module'],
                'reference_type' => 'Document',
                'reference_id' => 1,
                'reference_number' => $ev['ref_num'],
                'actor_id' => $creatorId,
                'actor_name' => $ev['actor'],
                'actor_role' => $ev['role'],
                'ip_address' => $ev['ip'],
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Mutamark ERP Client',
                'action' => $ev['action'],
                'details' => $ev['details'],
                'old_values' => $ev['old'],
                'new_values' => $ev['new'],
                'severity' => $ev['severity'],
                'previous_hash' => $prevHash,
                'current_hash' => $currentHash,
                'is_verified' => true,
                'created_by' => $creatorId,
                'creator_id' => $creatorId,
                'created_at' => $ev['time'],
                'updated_at' => $ev['time'],
            ]);

            $prevHash = $currentHash;
        }

        $this->command->info("Mutamark Audit Trail Seeder successfully created 8 chained audit blocks with 100% SHA-256 integrity!");
    }
}
