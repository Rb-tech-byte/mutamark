<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Account\Models\BankAccount;
use Workdo\Account\Models\CustomerPayment;
use Workdo\Account\Models\Expense;
use Workdo\Account\Models\JournalEntryItem;
use Workdo\Pos\Models\PosItem;
use Workdo\Pos\Models\PosPayment;

class FinancialManagementController extends Controller
{
    public function index(Request $request)
    {
        $creatorId = creatorId();
        $dateFrom = $request->input('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $warehouseId = $request->input('warehouse_id');

        // 1. Sales Revenue (Accrual: Sales Invoices + POS Sales, NOT cash received)
        $salesInvoiceQuery = SalesInvoice::where('created_by', $creatorId)
            ->where('status', '!=', 'draft')
            ->whereBetween('invoice_date', [$dateFrom, $dateTo]);
        if ($warehouseId) {
            $salesInvoiceQuery->where('warehouse_id', $warehouseId);
        }
        $invoiceSalesRevenue = (float) $salesInvoiceQuery->sum('subtotal');
        $outputTaxInvoices = (float) (clone $salesInvoiceQuery)->sum('tax_amount');

        $posSalesQuery = PosItem::where('created_by', $creatorId)
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo, $warehouseId) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            });
        $posSalesRevenue = (float) (clone $posSalesQuery)->sum('subtotal');
        $outputTaxPos = (float) (clone $posSalesQuery)->sum('tax_amount');

        $salesRevenue = round($invoiceSalesRevenue + $posSalesRevenue, 2);

        // 2. Payments Received (Cash Basis)
        $customerPayments = (float) CustomerPayment::where('created_by', $creatorId)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->sum('payment_amount');

        $posPayments = (float) PosPayment::where('created_by', $creatorId)
            ->whereHas('pos', function ($q) use ($dateFrom, $dateTo, $warehouseId) {
                $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            })
            ->sum('amount');

        $paymentsReceived = round($customerPayments + $posPayments, 2);

        // 3. Customer Receivables & Supplier Payables (Open Balances)
        $receivablesQuery = SalesInvoice::where('created_by', $creatorId)
            ->whereIn('status', ['posted', 'partial', 'sent', 'overdue']);
        if ($warehouseId) {
            $receivablesQuery->where('warehouse_id', $warehouseId);
        }
        $customerReceivables = round((float) $receivablesQuery->sum('balance_amount'), 2);

        $payablesQuery = PurchaseInvoice::where('created_by', $creatorId)
            ->whereIn('status', ['posted', 'partial', 'overdue']);
        if ($warehouseId) {
            $payablesQuery->where('warehouse_id', $warehouseId);
        }
        $supplierPayables = round((float) $payablesQuery->sum('balance_amount'), 2);

        // 4. Purchase Costs & Input Tax
        $purchaseQuery = PurchaseInvoice::where('created_by', $creatorId)
            ->where('status', '!=', 'draft')
            ->whereBetween('invoice_date', [$dateFrom, $dateTo]);
        if ($warehouseId) {
            $purchaseQuery->where('warehouse_id', $warehouseId);
        }
        $purchaseCosts = round((float) (clone $purchaseQuery)->sum('subtotal'), 2);
        $inputTax = round((float) (clone $purchaseQuery)->sum('tax_amount'), 2);

        // 5. Operating Expenses
        $expenseQuery = Expense::where('created_by', $creatorId)
            ->whereIn('status', ['approved', 'posted'])
            ->whereBetween('expense_date', [$dateFrom, $dateTo]);
        if ($warehouseId) {
            $expenseQuery->where('warehouse_id', $warehouseId);
        }
        $operatingExpenses = round((float) $expenseQuery->sum('amount'), 2);

        // 6. Cash & Bank Balances
        $cashBalances = round((float) BankAccount::where('created_by', $creatorId)
            ->where('is_active', true)
            ->sum('current_balance'), 2);

        // 7. Tax Summary & Profit Calculations
        $outputTax = round($outputTaxInvoices + $outputTaxPos, 2);
        $netTaxPayable = round($outputTax - $inputTax, 2);
        $grossProfit = round($salesRevenue - $purchaseCosts, 2);
        $netProfit = round($grossProfit - $operatingExpenses, 2);

        // 8. Double-Entry Ledger Balance Verification
        $ledgerTotals = JournalEntryItem::whereHas('journalEntry', function ($q) use ($creatorId) {
            $q->where('created_by', $creatorId)->where('status', 'posted');
        })->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')->first();

        $totalDebits = round((float) ($ledgerTotals->total_debit ?? 0), 2);
        $totalCredits = round((float) ($ledgerTotals->total_credit ?? 0), 2);

        $periods = AccountingPeriod::with('closedBy:id,name')
            ->where('created_by', $creatorId)
            ->orderByDesc('start_date')
            ->get();

        $warehouses = Warehouse::where('created_by', $creatorId)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Financial/Overview', [
            'metrics' => [
                'sales_revenue' => $salesRevenue,
                'payments_received' => $paymentsReceived,
                'customer_receivables' => $customerReceivables,
                'supplier_payables' => $supplierPayables,
                'purchase_costs' => $purchaseCosts,
                'operating_expenses' => $operatingExpenses,
                'cash_balances' => $cashBalances,
                'output_tax' => $outputTax,
                'input_tax' => $inputTax,
                'net_tax_payable' => $netTaxPayable,
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
                'ledger_total_debits' => $totalDebits,
                'ledger_total_credits' => $totalCredits,
                'ledger_balanced' => abs($totalDebits - $totalCredits) < 0.01,
            ],
            'periods' => $periods,
            'warehouses' => $warehouses,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }

    public function storePeriod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:500',
        ]);

        AccountingPeriod::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
            'creator_id' => Auth::id(),
            'created_by' => creatorId(),
        ]);

        return back()->with('success', __('Accounting period created successfully.'));
    }

    public function closePeriod(AccountingPeriod $period)
    {
        if ((int) $period->created_by !== (int) creatorId()) {
            return back()->with('error', __('Permission denied.'));
        }

        $period->update([
            'status' => 'closed',
            'closed_by' => Auth::id(),
            'closed_at' => now(),
        ]);

        return back()->with('success', __('Accounting period :name has been closed. New postings into this date range are now locked.', ['name' => $period->name]));
    }

    public function reopenPeriod(AccountingPeriod $period)
    {
        if ((int) $period->created_by !== (int) creatorId()) {
            return back()->with('error', __('Permission denied.'));
        }

        $period->update([
            'status' => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);

        return back()->with('success', __('Accounting period :name reopened.', ['name' => $period->name]));
    }
}

