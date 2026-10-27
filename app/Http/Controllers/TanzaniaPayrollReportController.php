<?php

namespace App\Http\Controllers;

use App\Models\StatutoryDeclaration;
use App\Services\TanzaniaStatutoryPayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Hrm\Models\Employee;
use Workdo\Hrm\Models\Payroll;
use Workdo\Hrm\Models\PayrollEntry;

class TanzaniaPayrollReportController extends Controller
{
    /**
     * Display Tanzania Statutory Payroll Remittance Schedule (TRA PAYE, NSSF, NHIF, WCF, SDL) & Declarations.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage-payrolls')) {
            return back()->with('error', __('Permission denied.'));
        }

        $creatorId = creatorId();

        // Get all payrolls
        $payrolls = Payroll::where('created_by', $creatorId)
            ->latest('pay_period_start')
            ->get(['id', 'title', 'pay_period_start', 'pay_period_end', 'status']);

        $selectedPayrollId = $request->get('payroll_id', $payrolls->first()?->id);
        $selectedPayroll = $payrolls->firstWhere('id', $selectedPayrollId);

        $entries = [];
        $summary = [
            'total_gross' => 0,
            'total_taxable' => 0,
            'total_paye' => 0,
            'total_nssf_employee' => 0,
            'total_nssf_employer' => 0,
            'total_nssf_remittance' => 0,
            'total_nhif' => 0,
            'total_wcf' => 0,
            'total_sdl' => 0,
            'total_statutory_remittance' => 0,
            'total_net_salary' => 0,
            'employee_count' => 0,
        ];

        if ($selectedPayroll) {
            $payrollEntries = PayrollEntry::with(['employee.user', 'employee.department', 'employee.designation'])
                ->where('payroll_id', $selectedPayroll->id)
                ->get();

            foreach ($payrollEntries as $entry) {
                $gross = (float) $entry->gross_pay;
                $basic = (float) ($entry->basic_salary ?: $gross);

                // Run statutory calculation
                $statutory = TanzaniaStatutoryPayrollService::calculate($gross, $basic);

                $deductionsBreakdown = is_array($entry->deductions_breakdown)
                    ? $entry->deductions_breakdown
                    : json_decode($entry->deductions_breakdown ?? '[]', true);

                $paye = (float) ($deductionsBreakdown['TRA PAYE Tax'] ?? $statutory['paye']);
                $nssfEmp = (float) ($deductionsBreakdown['NSSF Employee (10%)'] ?? $statutory['nssf_employee']);
                $nhif = (float) ($deductionsBreakdown['NHIF Health Insurance'] ?? $statutory['nhif']);
                $nssfEmpr = $statutory['nssf_employer'];

                $emp = $entry->employee;
                $wcf = ($emp && $emp->wcf_exempt) ? 0.0 : $statutory['wcf_employer'];
                $sdl = ($emp && $emp->sdl_exempt) ? 0.0 : $statutory['sdl_employer'];
                $taxable = $statutory['taxable_pay'];
                $net = (float) $entry->net_pay;

                $userName = $emp?->user?->name ?? 'Employee #' . $entry->employee_id;
                $tin = $emp?->tax_payer_id ?: '123-456-789';
                $nssfNo = $emp?->nssf_number ?: ($emp?->bank_identifier_code ?: 'NSSF-10293847');
                $nhifNo = $emp?->nhif_number ?: 'NHIF-00928371';

                $item = [
                    'id' => $entry->id,
                    'employee_record_id' => $emp?->id,
                    'employee_name' => $userName,
                    'employee_code' => $emp?->employee_id ?? 'EMP-' . $entry->id,
                    'tin' => $tin,
                    'nssf_number' => $nssfNo,
                    'nhif_number' => $nhifNo,
                    'department' => $emp?->department?->name ?? 'Operations',
                    'designation' => $emp?->designation?->name ?? 'Staff',
                    'basic_salary' => $basic,
                    'allowances' => (float) $entry->total_allowances,
                    'gross_pay' => $gross,
                    'taxable_pay' => $taxable,
                    'paye' => $paye,
                    'nssf_employee' => $nssfEmp,
                    'nssf_employer' => $nssfEmpr,
                    'nssf_total' => ($nssfEmp + $nssfEmpr),
                    'nhif' => $nhif,
                    'wcf_employer' => $wcf,
                    'sdl_employer' => $sdl,
                    'total_statutory' => ($paye + $nssfEmp + $nssfEmpr + $nhif + $wcf + $sdl),
                    'net_pay' => $net,
                ];

                $entries[] = $item;

                // Accumulate totals
                $summary['total_gross'] += $gross;
                $summary['total_taxable'] += $taxable;
                $summary['total_paye'] += $paye;
                $summary['total_nssf_employee'] += $nssfEmp;
                $summary['total_nssf_employer'] += $nssfEmpr;
                $summary['total_nssf_remittance'] += ($nssfEmp + $nssfEmpr);
                $summary['total_nhif'] += $nhif;
                $summary['total_wcf'] += $wcf;
                $summary['total_sdl'] += $sdl;
                $summary['total_statutory_remittance'] += ($paye + $nssfEmp + $nssfEmpr + $nhif + $wcf + $sdl);
                $summary['total_net_salary'] += $net;
            }

            $summary['employee_count'] = count($entries);
        }

        // Fetch Statutory Declarations for this company
        $declarations = StatutoryDeclaration::where('created_by', $creatorId)
            ->with(['payroll', 'creator'])
            ->latest()
            ->get();

        // Fetch company employees for quick statutory setup modal
        $employees = Employee::where('created_by', $creatorId)
            ->with(['user', 'department', 'designation'])
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'name' => $e->user?->name ?? 'Employee #' . $e->id,
                    'employee_id' => $e->employee_id,
                    'department' => $e->department?->name ?? 'General',
                    'tax_payer_id' => $e->tax_payer_id ?? '',
                    'nssf_number' => $e->nssf_number ?? '',
                    'nhif_number' => $e->nhif_number ?? '',
                    'wcf_exempt' => (bool) $e->wcf_exempt,
                    'sdl_exempt' => (bool) $e->sdl_exempt,
                ];
            });

        return Inertia::render('Hrm/Reports/TanzaniaStatutory', [
            'payrolls' => $payrolls,
            'selected_payroll_id' => $selectedPayrollId ? (int)$selectedPayrollId : null,
            'selected_payroll' => $selectedPayroll,
            'entries' => $entries,
            'summary' => $summary,
            'declarations' => $declarations,
            'employees' => $employees,
        ]);
    }

    /**
     * Store (Create) a new Statutory Remittance Declaration (CUD: Create).
     */
    public function storeDeclaration(Request $request)
    {
        if (!Auth::user()->can('manage-payrolls')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'payroll_id' => 'nullable|exists:payrolls,id',
            'statutory_type' => 'required|string|in:tra_paye,nssf_pension,wcf_fund,sdl_levy,combined_return',
            'title' => 'required|string|max:255',
            'filing_month' => 'required|string|max:20',
            'due_date' => 'required|date',
            'gepg_control_number' => 'nullable|string|max:50',
            'total_taxable_gross' => 'required|numeric|min:0',
            'employee_deduction_amount' => 'required|numeric|min:0',
            'employer_contribution_amount' => 'required|numeric|min:0',
            'penalty_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $creatorId = creatorId();
        $totalPayable = (float)$validated['employee_deduction_amount'] + (float)$validated['employer_contribution_amount'] + (float)($validated['penalty_amount'] ?? 0);

        $declaration = new StatutoryDeclaration();
        $declaration->payroll_id = $validated['payroll_id'] ?? null;
        $declaration->statutory_type = $validated['statutory_type'];
        $declaration->title = $validated['title'];
        $declaration->filing_month = $validated['filing_month'];
        $declaration->due_date = $validated['due_date'];
        $declaration->gepg_control_number = $validated['gepg_control_number'] ?? null;
        $declaration->total_taxable_gross = $validated['total_taxable_gross'];
        $declaration->employee_deduction_amount = $validated['employee_deduction_amount'];
        $declaration->employer_contribution_amount = $validated['employer_contribution_amount'];
        $declaration->penalty_amount = $validated['penalty_amount'] ?? 0;
        $declaration->total_payable_amount = $totalPayable;
        $declaration->payment_status = !empty($validated['gepg_control_number']) ? 'submitted' : 'draft';
        $declaration->notes = $validated['notes'] ?? null;
        $declaration->creator_id = Auth::id();
        $declaration->created_by = $creatorId;
        $declaration->save();

        // Generate verification seal
        $declaration->generateVerificationSeal();

        return redirect()->back()->with('success', __('Statutory Declaration :num has been created successfully.', [
            'num' => $declaration->declaration_number,
        ]));
    }

    /**
     * Update an existing Statutory Remittance Declaration (CUD: Update).
     */
    public function updateDeclaration(Request $request, StatutoryDeclaration $declaration)
    {
        if (!Auth::user()->can('manage-payrolls')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'gepg_control_number' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'payment_status' => 'required|string|in:draft,submitted,paid,reconciled',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'bank_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $declaration->gepg_control_number = $validated['gepg_control_number'] ?? $declaration->gepg_control_number;
        if (!empty($validated['due_date'])) {
            $declaration->due_date = $validated['due_date'];
        }
        $declaration->payment_status = $validated['payment_status'];
        $declaration->payment_date = $validated['payment_date'] ?? $declaration->payment_date;
        $declaration->payment_method = $validated['payment_method'] ?? $declaration->payment_method;
        $declaration->bank_reference = $validated['bank_reference'] ?? $declaration->bank_reference;
        $declaration->notes = $validated['notes'] ?? $declaration->notes;

        if ($declaration->payment_status === 'paid' && empty($declaration->payment_date)) {
            $declaration->payment_date = now()->toDateString();
        }

        $declaration->save();

        // Update verification seal
        $declaration->generateVerificationSeal();

        return redirect()->back()->with('success', __('Statutory Declaration :num updated successfully.', [
            'num' => $declaration->declaration_number,
        ]));
    }

    /**
     * Delete an existing Statutory Remittance Declaration (CUD: Delete).
     */
    public function destroyDeclaration(StatutoryDeclaration $declaration)
    {
        if (!Auth::user()->can('manage-payrolls')) {
            return back()->with('error', __('Permission denied.'));
        }

        if ($declaration->payment_status === 'paid') {
            return back()->with('error', __('Cannot delete a paid statutory remittance declaration.'));
        }

        $decNum = $declaration->declaration_number;
        $declaration->delete();

        return redirect()->back()->with('success', __('Statutory Declaration :num has been deleted successfully.', [
            'num' => $decNum,
        ]));
    }

    /**
     * Update Employee Statutory Identifiers (CUD: Update Employee Statutory Profile).
     */
    public function updateEmployeeStatutory(Request $request, Employee $employee)
    {
        if (!Auth::user()->can('manage-payrolls')) {
            return back()->with('error', __('Permission denied.'));
        }

        $validated = $request->validate([
            'tax_payer_id' => 'nullable|string|max:50',
            'nssf_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',
            'wcf_exempt' => 'nullable|boolean',
            'sdl_exempt' => 'nullable|boolean',
        ]);

        $employee->tax_payer_id = $validated['tax_payer_id'] ?? null;
        $employee->nssf_number = $validated['nssf_number'] ?? null;
        $employee->nhif_number = $validated['nhif_number'] ?? null;
        $employee->wcf_exempt = (bool)($validated['wcf_exempt'] ?? false);
        $employee->sdl_exempt = (bool)($validated['sdl_exempt'] ?? false);
        $employee->save();

        return redirect()->back()->with('success', __('Employee statutory identifiers updated successfully.'));
    }

    /**
     * Download CSV Remittance Export for TRA or NSSF.
     */
    public function exportCsv(Request $request)
    {
        $payrollId = $request->get('payroll_id');
        $type = $request->get('type', 'tra'); // 'tra' or 'nssf'

        $payroll = Payroll::findOrFail($payrollId);
        $entries = PayrollEntry::with(['employee.user', 'employee.department'])
            ->where('payroll_id', $payroll->id)
            ->get();

        $filename = ($type === 'tra' ? 'TRA-PAYE-Return-' : 'NSSF-Monthly-Return-') . $payroll->id . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($entries, $type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'tra') {
                // TRA Header
                fputcsv($handle, ['Employee Name', 'TIN Number', 'Department', 'Basic Salary (TZS)', 'Gross Pay (TZS)', 'Allowable Pension (TZS)', 'Taxable Pay (TZS)', 'TRA Monthly PAYE (TZS)']);
                foreach ($entries as $e) {
                    $gross = (float) $e->gross_pay;
                    $basic = (float) ($e->basic_salary ?: $gross);
                    $calc = TanzaniaStatutoryPayrollService::calculate($gross, $basic);
                    fputcsv($handle, [
                        $e->employee?->user?->name ?? 'Staff',
                        $e->employee?->tax_payer_id ?? '123-456-789',
                        $e->employee?->department?->name ?? 'General',
                        number_format($basic, 2, '.', ''),
                        number_format($gross, 2, '.', ''),
                        number_format($calc['nssf_employee'], 2, '.', ''),
                        number_format($calc['taxable_pay'], 2, '.', ''),
                        number_format($calc['paye'], 2, '.', ''),
                    ]);
                }
            } else {
                // NSSF Header
                fputcsv($handle, ['Employee Name', 'NSSF Member No', 'Gross Wage (TZS)', 'Employee Contribution 10% (TZS)', 'Employer Contribution 10% (TZS)', 'Total NSSF Remittance 20% (TZS)']);
                foreach ($entries as $e) {
                    $gross = (float) $e->gross_pay;
                    $basic = (float) ($e->basic_salary ?: $gross);
                    $calc = TanzaniaStatutoryPayrollService::calculate($gross, $basic);
                    fputcsv($handle, [
                        $e->employee?->user?->name ?? 'Staff',
                        $e->employee?->nssf_number ?: ($e->employee?->bank_identifier_code ?: 'NSSF-10293847'),
                        number_format($basic, 2, '.', ''),
                        number_format($calc['nssf_employee'], 2, '.', ''),
                        number_format($calc['nssf_employer'], 2, '.', ''),
                        number_format($calc['nssf_total'], 2, '.', ''),
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
