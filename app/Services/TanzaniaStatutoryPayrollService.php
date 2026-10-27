<?php

namespace App\Services;

class TanzaniaStatutoryPayrollService
{
    /**
     * Compute Tanzanian statutory payroll deductions and employer liabilities.
     *
     * @param float $grossPay Total gross emoluments
     * @param float|null $basicSalary Basic monthly salary
     * @return array Breakdown of deductions, tax, and employer liabilities
     */
    public static function calculate(float $grossPay, ?float $basicSalary = null): array
    {
        $basic = $basicSalary ?? $grossPay;

        // 1. NSSF (10% Employee, 10% Employer)
        $nssfEmployee = round($basic * 0.10, 2);
        $nssfEmployer = round($basic * 0.10, 2);
        $nssfTotal = $nssfEmployee + $nssfEmployer;

        // 2. Taxable Pay (Gross minus allowable pension contribution up to 10%)
        $taxablePay = max(0, $grossPay - $nssfEmployee);

        // 3. TRA PAYE Progressive Tax Brackets (Monthly, Tanzania)
        // Band 1: 0 - 270,000 => 0%
        // Band 2: 270,001 - 520,000 => 8% on excess
        // Band 3: 520,001 - 760,000 => 20,000 + 20% on excess
        // Band 4: 760,001 - 1,000,000 => 68,000 + 25% on excess
        // Band 5: > 1,000,000 => 128,000 + 30% on excess
        $paye = 0.0;
        if ($taxablePay > 1000000) {
            $paye = 128000 + (($taxablePay - 1000000) * 0.30);
        } elseif ($taxablePay > 760000) {
            $paye = 68000 + (($taxablePay - 760000) * 0.25);
        } elseif ($taxablePay > 520000) {
            $paye = 20000 + (($taxablePay - 520000) * 0.20);
        } elseif ($taxablePay > 270000) {
            $paye = ($taxablePay - 270000) * 0.08;
        } else {
            $paye = 0.0;
        }
        $paye = round($paye, 2);

        // 4. NHIF (Statutory health contribution tier)
        $nhif = 0.0;
        if ($grossPay >= 1000000) {
            $nhif = 40000.0;
        } elseif ($grossPay >= 500000) {
            $nhif = 25000.0;
        } else {
            $nhif = 15000.0;
        }

        // 5. Employer Liabilities
        // WCF: 0.5% of gross salary
        $wcfEmployer = round($grossPay * 0.005, 2);
        // SDL: 3.5% of gross salary
        $sdlEmployer = round($grossPay * 0.035, 2);
        $totalEmployerLiability = round($nssfEmployer + $wcfEmployer + $sdlEmployer, 2);

        // 6. Net Pay Calculation
        $totalEmployeeDeductions = round($nssfEmployee + $paye + $nhif, 2);
        $netPay = max(0, round($grossPay - $totalEmployeeDeductions, 2));

        return [
            'gross_pay' => $grossPay,
            'basic_salary' => $basic,
            'taxable_pay' => $taxablePay,
            'nssf_employee' => $nssfEmployee,
            'nssf_employer' => $nssfEmployer,
            'nssf_total' => $nssfTotal,
            'paye' => $paye,
            'nhif' => $nhif,
            'wcf_employer' => $wcfEmployer,
            'sdl_employer' => $sdlEmployer,
            'total_employer_liability' => $totalEmployerLiability,
            'total_employee_deductions' => $totalEmployeeDeductions,
            'net_pay' => $netPay,
            'deductions_breakdown' => [
                'TRA PAYE Tax' => $paye,
                'NSSF Employee (10%)' => $nssfEmployee,
                'NHIF Health Insurance' => $nhif,
            ],
            'employer_breakdown' => [
                'NSSF Employer (10%)' => $nssfEmployer,
                'WCF Fund (0.5%)' => $wcfEmployer,
                'SDL Levy (3.5%)' => $sdlEmployer,
            ]
        ];
    }
}
