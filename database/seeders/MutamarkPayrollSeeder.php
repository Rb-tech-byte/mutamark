<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\TanzaniaStatutoryPayrollService;
use Workdo\Hrm\Models\Branch;
use Workdo\Hrm\Models\Department;
use Workdo\Hrm\Models\Designation;
use Workdo\Hrm\Models\Employee;
use Workdo\Hrm\Models\Payroll;
use Workdo\Hrm\Models\PayrollEntry;

class MutamarkPayrollSeeder extends Seeder
{
    public function run(): void
    {
        $company = User::where('type', 'company')->first() ?? User::find(2);
        if (!$company) {
            $this->command->warn('Company user not found, skipping payroll seeding.');
            return;
        }

        $creatorId = $company->id;

        // 1. Branches
        $branchDar = Branch::firstOrCreate(
            ['branch_name' => 'Mutamark HQ - Dar es Salaam', 'created_by' => $creatorId],
            ['branch_name' => 'Mutamark HQ - Dar es Salaam', 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $branchArusha = Branch::firstOrCreate(
            ['branch_name' => 'Arusha Tech & Logistics Center', 'created_by' => $creatorId],
            ['branch_name' => 'Arusha Tech & Logistics Center', 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        // 2. Departments
        $deptEng = Department::firstOrCreate(
            ['department_name' => 'Engineering & IT', 'created_by' => $creatorId],
            ['department_name' => 'Engineering & IT', 'branch_id' => $branchDar->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $deptFin = Department::firstOrCreate(
            ['department_name' => 'Finance & Accounting', 'created_by' => $creatorId],
            ['department_name' => 'Finance & Accounting', 'branch_id' => $branchDar->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $deptLog = Department::firstOrCreate(
            ['department_name' => 'Supply Chain & Logistics', 'created_by' => $creatorId],
            ['department_name' => 'Supply Chain & Logistics', 'branch_id' => $branchDar->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $deptSales = Department::firstOrCreate(
            ['department_name' => 'Commercial & Enterprise Sales', 'created_by' => $creatorId],
            ['department_name' => 'Commercial & Enterprise Sales', 'branch_id' => $branchArusha->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $deptSupport = Department::firstOrCreate(
            ['department_name' => 'Technical Support & Operations', 'created_by' => $creatorId],
            ['department_name' => 'Technical Support & Operations', 'branch_id' => $branchArusha->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        // 3. Designations
        $desigEng = Designation::firstOrCreate(
            ['designation_name' => 'Senior Full-Stack Software Engineer', 'created_by' => $creatorId],
            ['designation_name' => 'Senior Full-Stack Software Engineer', 'department_id' => $deptEng->id, 'branch_id' => $branchDar->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $desigFin = Designation::firstOrCreate(
            ['designation_name' => 'Head of Finance & Tax Compliance', 'created_by' => $creatorId],
            ['designation_name' => 'Head of Finance & Tax Compliance', 'department_id' => $deptFin->id, 'branch_id' => $branchDar->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $desigLog = Designation::firstOrCreate(
            ['designation_name' => 'Logistics & Warehouse Manager', 'created_by' => $creatorId],
            ['designation_name' => 'Logistics & Warehouse Manager', 'department_id' => $deptLog->id, 'branch_id' => $branchDar->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $desigSales = Designation::firstOrCreate(
            ['designation_name' => 'Enterprise Solutions Executive', 'created_by' => $creatorId],
            ['designation_name' => 'Enterprise Solutions Executive', 'department_id' => $deptSales->id, 'branch_id' => $branchArusha->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        $desigSupport = Designation::firstOrCreate(
            ['designation_name' => 'Field Operations Specialist', 'created_by' => $creatorId],
            ['designation_name' => 'Field Operations Specialist', 'department_id' => $deptSupport->id, 'branch_id' => $branchArusha->id, 'creator_id' => $creatorId, 'created_by' => $creatorId]
        );

        // 4. Employees Data Definition
        $employeesData = [
            [
                'name' => 'Juma Bakari Mwalimu',
                'email' => 'juma.mwalimu@mutamark.co.tz',
                'emp_code' => 'EMP20260001',
                'tin' => '112-409-881',
                'nssf' => 'NSSF-70881923',
                'branch' => $branchDar,
                'department' => $deptEng,
                'designation' => $desigEng,
                'basic_salary' => 3200000.0,
                'allowances' => 300000.0,
                'bank_name' => 'CRDB Bank Plc',
                'acc_num' => '0150293847100',
                'branch_name' => 'Azikiwe Corporate Branch',
            ],
            [
                'name' => 'Amina Salum Kibona',
                'email' => 'amina.kibona@mutamark.co.tz',
                'emp_code' => 'EMP20260002',
                'tin' => '114-883-205',
                'nssf' => 'NSSF-70420918',
                'branch' => $branchDar,
                'department' => $deptFin,
                'designation' => $desigFin,
                'basic_salary' => 2600000.0,
                'allowances' => 250000.0,
                'bank_name' => 'NMB Bank Plc',
                'acc_num' => '2011004829100',
                'branch_name' => 'Kariakoo Commercial Branch',
            ],
            [
                'name' => 'Baraka Joseph Mwangi',
                'email' => 'baraka.mwangi@mutamark.co.tz',
                'emp_code' => 'EMP20260003',
                'tin' => '116-302-944',
                'nssf' => 'NSSF-70631982',
                'branch' => $branchDar,
                'department' => $deptLog,
                'designation' => $desigLog,
                'basic_salary' => 1800000.0,
                'allowances' => 200000.0,
                'bank_name' => 'NBC Bank Tanzania',
                'acc_num' => '011039827461',
                'branch_name' => 'Samora Corporate Branch',
            ],
            [
                'name' => 'Zawadi Emmanuel Mushi',
                'email' => 'zawadi.mushi@mutamark.co.tz',
                'emp_code' => 'EMP20260004',
                'tin' => '118-994-120',
                'nssf' => 'NSSF-70192847',
                'branch' => $branchArusha,
                'department' => $deptSales,
                'designation' => $desigSales,
                'basic_salary' => 1200000.0,
                'allowances' => 150000.0,
                'bank_name' => 'Stanbic Bank Tanzania',
                'acc_num' => '912000384729',
                'branch_name' => 'Arusha Main Branch',
            ],
            [
                'name' => 'Daudi Hamisi Mfaume',
                'email' => 'daudi.mfaume@mutamark.co.tz',
                'emp_code' => 'EMP20260005',
                'tin' => '120-771-364',
                'nssf' => 'NSSF-70339105',
                'branch' => $branchArusha,
                'department' => $deptSupport,
                'designation' => $desigSupport,
                'basic_salary' => 850000.0,
                'allowances' => 100000.0,
                'bank_name' => 'CRDB Bank Plc',
                'acc_num' => '0150992837411',
                'branch_name' => 'Arusha Meru Branch',
            ],
        ];

        $seededEmployees = [];

        foreach ($employeesData as $empInfo) {
            // Find or create User
            $user = User::firstOrCreate(
                ['email' => $empInfo['email']],
                [
                    'name' => $empInfo['name'],
                    'password' => Hash::make('password123'),
                    'type' => 'employee',
                    'email_verified_at' => now(),
                    'creator_id' => $creatorId,
                    'created_by' => $creatorId,
                ]
            );

            // Find or create Employee profile
            $employee = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => $empInfo['emp_code'],
                    'date_of_birth' => '1992-05-15',
                    'gender' => 'Male',
                    'date_of_joining' => '2023-01-10',
                    'employment_type' => 'full_time',
                    'address_line_1' => 'Ali Hassan Mwinyi Road',
                    'city' => 'Dar es Salaam',
                    'state' => 'Dar es Salaam',
                    'country' => 'Tanzania',
                    'bank_name' => $empInfo['bank_name'],
                    'account_holder_name' => $empInfo['name'],
                    'account_number' => $empInfo['acc_num'],
                    'bank_identifier_code' => $empInfo['nssf'],
                    'bank_branch' => $empInfo['branch_name'],
                    'tax_payer_id' => $empInfo['tin'],
                    'basic_salary' => $empInfo['basic_salary'],
                    'hours_per_day' => 8,
                    'days_per_week' => 5,
                    'rate_per_hour' => round($empInfo['basic_salary'] / (22 * 8), 2),
                    'user_id' => $user->id,
                    'branch_id' => $empInfo['branch']->id,
                    'department_id' => $empInfo['department']->id,
                    'designation_id' => $empInfo['designation']->id,
                    'creator_id' => $creatorId,
                    'created_by' => $creatorId,
                ]
            );

            $seededEmployees[] = [
                'user' => $user,
                'employee' => $employee,
                'basic_salary' => $empInfo['basic_salary'],
                'allowances' => $empInfo['allowances'],
            ];
        }

        // 5. Create or update October 2026 Payroll
        $payroll = Payroll::firstOrCreate(
            [
                'title' => 'October 2026 - Monthly Statutory Payroll',
                'created_by' => $creatorId,
            ],
            [
                'title' => 'October 2026 - Monthly Statutory Payroll',
                'payroll_frequency' => 'monthly',
                'pay_period_start' => '2026-10-01',
                'pay_period_end' => '2026-10-31',
                'pay_date' => '2026-10-31',
                'notes' => 'Official monthly payroll schedule with Tanzanian TRA PAYE progressive tax, NSSF 20% pension, NHIF, WCF (0.5%), and SDL (3.5%) employer levies.',
                'total_gross_pay' => 0,
                'total_deductions' => 0,
                'total_net_pay' => 0,
                'employee_count' => count($seededEmployees),
                'status' => 'completed',
                'is_payroll_paid' => 1,
                'creator_id' => $creatorId,
                'created_by' => $creatorId,
            ]
        );

        $totGross = 0;
        $totDeductions = 0;
        $totNet = 0;

        foreach ($seededEmployees as $item) {
            $basic = $item['basic_salary'];
            $allowance = $item['allowances'];
            $gross = $basic + $allowance;

            // Statutory calculation
            $statutory = TanzaniaStatutoryPayrollService::calculate($gross, $basic);

            PayrollEntry::updateOrCreate(
                [
                    'payroll_id' => $payroll->id,
                    'employee_id' => $item['user']->id,
                ],
                [
                    'payroll_id' => $payroll->id,
                    'employee_id' => $item['user']->id,
                    'basic_salary' => $basic,
                    'total_allowances' => $allowance,
                    'total_manual_overtimes' => 0,
                    'total_deductions' => $statutory['total_employee_deductions'],
                    'total_loans' => 0,
                    'gross_pay' => $gross,
                    'net_pay' => $statutory['net_pay'],
                    'per_day_salary' => round($basic / 22, 2),
                    'working_days' => 22,
                    'present_days' => 22,
                    'status' => 'paid',
                    'allowances_breakdown' => [
                        'Transport Allowance' => round($allowance * 0.6, 2),
                        'Communication Allowance' => round($allowance * 0.4, 2),
                    ],
                    'deductions_breakdown' => $statutory['deductions_breakdown'],
                    'creator_id' => $creatorId,
                    'created_by' => $creatorId,
                ]
            );

            $totGross += $gross;
            $totDeductions += $statutory['total_employee_deductions'];
            $totNet += $statutory['net_pay'];
        }

        // Update Payroll Totals
        $payroll->update([
            'total_gross_pay' => $totGross,
            'total_deductions' => $totDeductions,
            'total_net_pay' => $totNet,
            'employee_count' => count($seededEmployees),
        ]);

        $this->command->info("Mutamark Payroll Seeder successfully populated 5 employees and October 2026 Payroll!");
    }
}
