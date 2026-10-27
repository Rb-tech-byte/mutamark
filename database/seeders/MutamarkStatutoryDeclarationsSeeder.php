<?php

namespace Database\Seeders;

use App\Models\StatutoryDeclaration;
use Illuminate\Database\Seeder;
use Workdo\Hrm\Models\Employee;
use Workdo\Hrm\Models\Payroll;

class MutamarkStatutoryDeclarationsSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = 2; // Default admin/company
        $payroll = Payroll::where('created_by', $creatorId)->first();

        // 1. Update Employee statutory metadata with formal numbers
        $employees = Employee::where('created_by', $creatorId)->get();
        $sampleTins = ['123-456-781', '123-456-782', '123-456-783', '123-456-784', '123-456-785'];
        $sampleNssfs = ['NSSF-20819241', 'NSSF-20819242', 'NSSF-20819243', 'NSSF-20819244', 'NSSF-20819245'];
        $sampleNhifs = ['NHIF-70192831', 'NHIF-70192832', 'NHIF-70192833', 'NHIF-70192834', 'NHIF-70192835'];

        foreach ($employees as $idx => $emp) {
            $emp->tax_payer_id = $sampleTins[$idx % count($sampleTins)];
            $emp->nssf_number = $sampleNssfs[$idx % count($sampleNssfs)];
            $emp->nhif_number = $sampleNhifs[$idx % count($sampleNhifs)];
            $emp->wcf_exempt = false;
            $emp->sdl_exempt = false;
            $emp->save();
        }

        // 2. Seed Paid September 2026 TRA PAYE & SDL Declaration
        $dec1 = StatutoryDeclaration::updateOrCreate(
            [
                'declaration_number' => 'DEC-2026-00001',
                'created_by' => $creatorId,
            ],
            [
                'payroll_id' => $payroll?->id,
                'statutory_type' => 'tra_paye',
                'title' => 'September 2026 TRA Monthly PAYE & SDL Remittance',
                'filing_month' => '2026-09',
                'due_date' => '2026-10-07',
                'gepg_control_number' => '991238475821',
                'total_taxable_gross' => 10650000.00,
                'employee_deduction_amount' => 1974750.00,
                'employer_contribution_amount' => 372750.00, // SDL
                'penalty_amount' => 0.00,
                'total_payable_amount' => 2347500.00,
                'payment_status' => 'paid',
                'payment_date' => '2026-10-06',
                'payment_method' => 'bank_transfer',
                'bank_reference' => 'CRDB-TXN-8829104',
                'acknowledged_at' => '2026-10-06 14:30:00',
                'notes' => 'Filing reconciled and settled via CRDB Bank GePG Gateway before 7th statutory deadline.',
                'creator_id' => $creatorId,
            ]
        );
        $dec1->generateVerificationSeal();

        // 3. Seed Paid September 2026 NSSF Return
        $dec2 = StatutoryDeclaration::updateOrCreate(
            [
                'declaration_number' => 'DEC-2026-00002',
                'created_by' => $creatorId,
            ],
            [
                'payroll_id' => $payroll?->id,
                'statutory_type' => 'nssf_pension',
                'title' => 'September 2026 NSSF Monthly 20% Pension Contribution',
                'filing_month' => '2026-09',
                'due_date' => '2026-10-15',
                'gepg_control_number' => '994827103948',
                'total_taxable_gross' => 10650000.00,
                'employee_deduction_amount' => 1065000.00,
                'employer_contribution_amount' => 1065000.00,
                'penalty_amount' => 0.00,
                'total_payable_amount' => 2130000.00,
                'payment_status' => 'paid',
                'payment_date' => '2026-10-12',
                'payment_method' => 'bank_transfer',
                'bank_reference' => 'NMB-NSSF-492019',
                'acknowledged_at' => '2026-10-12 11:15:00',
                'notes' => 'NSSF monthly portal submission reconciled for 5 staff members.',
                'creator_id' => $creatorId,
            ]
        );
        $dec2->generateVerificationSeal();

        // 4. Seed Active/Submitted October 2026 Combined Declaration
        $dec3 = StatutoryDeclaration::updateOrCreate(
            [
                'declaration_number' => 'DEC-2026-00003',
                'created_by' => $creatorId,
            ],
            [
                'payroll_id' => $payroll?->id,
                'statutory_type' => 'combined_return',
                'title' => 'October 2026 Consolidated Tanzania Statutory Remittance',
                'filing_month' => '2026-10',
                'due_date' => '2026-11-07',
                'gepg_control_number' => '998471029384',
                'total_taxable_gross' => 10650000.00,
                'employee_deduction_amount' => 3194750.00, // PAYE + NSSF Employee + NHIF
                'employer_contribution_amount' => 1491000.00, // NSSF Employer + WCF + SDL
                'penalty_amount' => 0.00,
                'total_payable_amount' => 4685750.00,
                'payment_status' => 'submitted',
                'payment_date' => null,
                'payment_method' => 'gepg_gateway',
                'bank_reference' => null,
                'acknowledged_at' => null,
                'notes' => 'Generated from October 2026 payroll. Awaiting final Treasury payment authorization.',
                'creator_id' => $creatorId,
            ]
        );
        $dec3->generateVerificationSeal();
    }
}
