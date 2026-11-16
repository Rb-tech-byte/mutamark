<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Workdo\Account\Models\BankAccount;
use Workdo\Account\Models\CustomerPayment;
use Workdo\Account\Models\Expense;
use Workdo\Account\Models\VendorPayment;
use Workdo\Pos\Models\Pos;
use Workdo\Pos\Models\PosBillingCounter;
use Workdo\Pos\Models\PosItem;
use Workdo\ProductService\Models\ProductServiceCategory;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\WarehouseStock;

class ExecutiveReportController extends Controller
{
    public function index(Request $request)
    {
        $creatorId = creatorId();
        $reportType = $request->input('report_type', 'sales');
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $warehouseId = $request->input('warehouse_id');
        $employeeId = $request->input('employee_id');
        $customerId = $request->input('customer_id');
        $vendorId = $request->input('vendor_id');
        $productId = $request->input('product_id');
        $categoryId = $request->input('category_id');

        $report = $this->buildReport($reportType, $creatorId, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'warehouse_id' => $warehouseId,
            'employee_id' => $employeeId,
            'customer_id' => $customerId,
            'vendor_id' => $vendorId,
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        return Inertia::render('Reports/ExecutiveHub', [
            'report' => $report,
            'reportType' => $reportType,
            'filters' => [
                'report_type' => $reportType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'warehouse_id' => $warehouseId,
                'employee_id' => $employeeId,
                'customer_id' => $customerId,
                'vendor_id' => $vendorId,
                'product_id' => $productId,
                'category_id' => $categoryId,
            ],
            'options' => [
                'warehouses' => Warehouse::where('created_by', $creatorId)->get(['id', 'name']),
                'employees' => User::where('created_by', $creatorId)->whereNotIn('type', ['client', 'vendor'])->get(['id', 'name']),
                'customers' => User::where('created_by', $creatorId)->where('type', 'client')->get(['id', 'name']),
                'vendors' => User::where('created_by', $creatorId)->where('type', 'vendor')->get(['id', 'name']),
                'products' => ProductServiceItem::where('created_by', $creatorId)->get(['id', 'name', 'sku']),
                'categories' => ProductServiceCategory::where('created_by', $creatorId)->get(['id', 'name']),
            ],
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $creatorId = creatorId();
        $reportType = $request->input('report_type', 'sales');
        $report = $this->buildReport($reportType, $creatorId, $request->all());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $report['title']), 0, 30) ?: 'Report');

        $sheet->setCellValue('A1', $report['title']);
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        $colIdx = 1;
        foreach ($report['columns'] as $col) {
            $sheet->setCellValue([$colIdx++, 4], $col['label']);
        }

        $rowIdx = 5;
        foreach ($report['rows'] as $row) {
            $colIdx = 1;
            foreach ($report['columns'] as $col) {
                $sheet->setCellValue([$colIdx++, $rowIdx], $row[$col['key']] ?? '');
            }
            $rowIdx++;
        }

        foreach ($report['totals'] as $total) {
            $sheet->setCellValue([1, $rowIdx], $total['label']);
            $sheet->setCellValue([max(2, count($report['columns'])), $rowIdx], $total['value']);
            $rowIdx++;
        }

        $filename = 'report-' . $reportType . '-' . now()->format('Ymd-His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildReport(string $type, int $creatorId, array $f): array
    {
        $dateFrom = $f['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $f['date_to'] ?? now()->toDateString();
        $warehouseId = !empty($f['warehouse_id']) ? (int) $f['warehouse_id'] : null;
        $employeeId = !empty($f['employee_id']) ? (int) $f['employee_id'] : null;
        $customerId = !empty($f['customer_id']) ? (int) $f['customer_id'] : null;
        $vendorId = !empty($f['vendor_id']) ? (int) $f['vendor_id'] : null;
        $productId = !empty($f['product_id']) ? (int) $f['product_id'] : null;
        $categoryId = !empty($f['category_id']) ? (int) $f['category_id'] : null;

        switch ($type) {
            case 'purchase':
                $q = PurchaseInvoice::with(['vendor:id,name', 'warehouse:id,name'])
                    ->where('created_by', $creatorId)
                    ->whereBetween('invoice_date', [$dateFrom, $dateTo]);
                if ($warehouseId) $q->where('warehouse_id', $warehouseId);
                if ($vendorId) $q->where('vendor_id', $vendorId);
                $items = $q->latest('invoice_date')->get();
                $rows = $items->map(fn ($i) => [
                    'doc_no' => $i->invoice_number,
                    'date' => optional($i->invoice_date)->format('Y-m-d'),
                    'supplier' => $i->vendor?->name ?? 'Supplier',
                    'location' => $i->warehouse?->name ?? '-',
                    'subtotal' => (float) $i->subtotal,
                    'tax' => (float) $i->tax_amount,
                    'total' => (float) $i->total_amount,
                    'status' => ucfirst($i->status),
                ])->values()->all();
                return [
                    'title' => 'PURCHASE REGISTER REPORT',
                    'doc_number' => 'RPT-PUR-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'doc_no', 'label' => 'Bill No'],
                        ['key' => 'date', 'label' => 'Date'],
                        ['key' => 'supplier', 'label' => 'Supplier'],
                        ['key' => 'location', 'label' => 'Location'],
                        ['key' => 'subtotal', 'label' => 'Net Amount'],
                        ['key' => 'tax', 'label' => 'Tax'],
                        ['key' => 'total', 'label' => 'Total'],
                        ['key' => 'status', 'label' => 'Status'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Bills', 'value' => count($rows)],
                        ['label' => 'Total Purchase Value', 'value' => round(collect($rows)->sum('total'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'tax':
                $salesQ = SalesInvoice::where('created_by', $creatorId)->where('status', '!=', 'draft')->whereBetween('invoice_date', [$dateFrom, $dateTo]);
                $purQ = PurchaseInvoice::where('created_by', $creatorId)->where('status', '!=', 'draft')->whereBetween('invoice_date', [$dateFrom, $dateTo]);
                if ($warehouseId) {
                    $salesQ->where('warehouse_id', $warehouseId);
                    $purQ->where('warehouse_id', $warehouseId);
                }
                $outTaxInv = (float) $salesQ->sum('tax_amount');
                $outTaxPos = (float) PosItem::where('created_by', $creatorId)
                    ->whereHas('sale', fn ($s) => $s->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']))
                    ->sum('tax_amount');
                $inTax = (float) $purQ->sum('tax_amount');
                $rows = [
                    ['tax_stream' => 'Output VAT (Sales Invoices)', 'base_amount' => round((float) $salesQ->sum('subtotal'), 2), 'tax_amount' => round($outTaxInv, 2)],
                    ['tax_stream' => 'Output VAT (POS Sales)', 'base_amount' => round((float) PosItem::where('created_by', $creatorId)->whereHas('sale', fn ($s) => $s->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']))->sum('subtotal'), 2), 'tax_amount' => round($outTaxPos, 2)],
                    ['tax_stream' => 'Input VAT (Purchase Bills)', 'base_amount' => round((float) $purQ->sum('subtotal'), 2), 'tax_amount' => round($inTax, 2)],
                ];
                return [
                    'title' => 'TAX SUMMARY & VAT RECONCILIATION REPORT',
                    'doc_number' => 'RPT-TAX-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'tax_stream', 'label' => 'Tax Category'],
                        ['key' => 'base_amount', 'label' => 'Taxable Base'],
                        ['key' => 'tax_amount', 'label' => 'Tax Amount'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Output Tax', 'value' => round($outTaxInv + $outTaxPos, 2)],
                        ['label' => 'Total Input Tax', 'value' => round($inTax, 2)],
                        ['label' => 'Net Tax Payable', 'value' => round(($outTaxInv + $outTaxPos) - $inTax, 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'inventory':
            case 'stock_valuation':
                $sq = WarehouseStock::with(['product:id,name,sku,category_id,purchase_price,sale_price', 'warehouse:id,name'])
                    ->whereHas('product', function ($pq) use ($creatorId, $productId, $categoryId) {
                        $pq->where('created_by', $creatorId);
                        if ($productId) $pq->where('id', $productId);
                        if ($categoryId) $pq->where('category_id', $categoryId);
                    });
                if ($warehouseId) $sq->where('warehouse_id', $warehouseId);
                $stocks = $sq->get();
                $rows = $stocks->map(function ($s) {
                    $qty = (float) $s->quantity;
                    $cost = (float) ($s->product?->purchase_price ?? 0);
                    $sale = (float) ($s->product?->sale_price ?? 0);
                    return [
                        'sku' => $s->product?->sku ?? '-',
                        'product' => $s->product?->name ?? 'Item',
                        'location' => $s->warehouse?->name ?? '-',
                        'qty' => $qty,
                        'unit_cost' => $cost,
                        'cost_value' => round($qty * $cost, 2),
                        'retail_value' => round($qty * $sale, 2),
                    ];
                })->values()->all();
                return [
                    'title' => $type === 'stock_valuation' ? 'STOCK VALUATION STATEMENT' : 'INVENTORY STOCK LEVEL REPORT',
                    'doc_number' => 'RPT-INV-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'sku', 'label' => 'SKU'],
                        ['key' => 'product', 'label' => 'Product'],
                        ['key' => 'location', 'label' => 'Location'],
                        ['key' => 'qty', 'label' => 'Qty On Hand'],
                        ['key' => 'unit_cost', 'label' => 'Unit Cost'],
                        ['key' => 'cost_value', 'label' => 'Stock Cost Value'],
                        ['key' => 'retail_value', 'label' => 'Retail Value'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Units', 'value' => round(collect($rows)->sum('qty'), 2)],
                        ['label' => 'Total Cost Valuation', 'value' => round(collect($rows)->sum('cost_value'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'expense':
                $eq = Expense::with(['category:id,category_name', 'warehouse:id,name', 'bankAccount:id,account_name'])
                    ->where('created_by', $creatorId)
                    ->whereBetween('expense_date', [$dateFrom, $dateTo]);
                if ($warehouseId) $eq->where('warehouse_id', $warehouseId);
                $exps = $eq->latest('expense_date')->get();
                $rows = $exps->map(fn ($e) => [
                    'expense_no' => $e->expense_number,
                    'date' => optional($e->expense_date)->format('Y-m-d'),
                    'category' => $e->category?->category_name ?? '-',
                    'location' => $e->warehouse?->name ?? 'Head Office',
                    'payment_method' => ucfirst(str_replace('_', ' ', $e->payment_method ?? 'bank_transfer')),
                    'status' => ucfirst($e->status),
                    'amount' => (float) $e->amount,
                ])->values()->all();
                return [
                    'title' => 'OPERATING EXPENSE REPORT',
                    'doc_number' => 'RPT-EXP-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'expense_no', 'label' => 'Expense No'],
                        ['key' => 'date', 'label' => 'Date'],
                        ['key' => 'category', 'label' => 'Category'],
                        ['key' => 'location', 'label' => 'Location'],
                        ['key' => 'payment_method', 'label' => 'Method'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'amount', 'label' => 'Amount'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Expenses', 'value' => round(collect($rows)->sum('amount'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'cash_register':
                $counters = PosBillingCounter::where('created_by', $creatorId)->get();
                $rows = $counters->map(function ($c) use ($creatorId, $dateFrom, $dateTo, $warehouseId) {
                    $salesQ = Pos::where('created_by', $creatorId)
                        ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                    if ($warehouseId) $salesQ->where('warehouse_id', $warehouseId);
                    $txCount = $salesQ->count();
                    $total = (float) PosItem::whereIn('pos_id', (clone $salesQ)->pluck('id'))->sum('total_amount');
                    return [
                        'counter' => $c->name . ' (' . $c->code . ')',
                        'status' => $c->status ? 'Active' : 'Inactive',
                        'transactions' => $txCount,
                        'total_collected' => round($total, 2),
                    ];
                })->values()->all();
                return [
                    'title' => 'CASH REGISTER & COUNTER SUMMARY REPORT',
                    'doc_number' => 'RPT-CSH-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'counter', 'label' => 'Counter'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'transactions', 'label' => 'Transactions'],
                        ['key' => 'total_collected', 'label' => 'Total Sales'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Collected', 'value' => round(collect($rows)->sum('total_collected'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'customer':
                $cq = SalesInvoice::with('customer:id,name')->where('created_by', $creatorId)->whereBetween('invoice_date', [$dateFrom, $dateTo]);
                if ($customerId) $cq->where('customer_id', $customerId);
                if ($warehouseId) $cq->where('warehouse_id', $warehouseId);
                $rows = $cq->get()->groupBy('customer_id')->map(fn ($g) => [
                    'customer' => $g->first()->customer?->name ?? 'Walk-in',
                    'invoices' => $g->count(),
                    'billed' => round((float) $g->sum('total_amount'), 2),
                    'paid' => round((float) $g->sum('paid_amount'), 2),
                    'balance' => round((float) $g->sum('balance_amount'), 2),
                ])->values()->all();
                return [
                    'title' => 'CUSTOMER STATEMENT & RECEIVABLES REPORT',
                    'doc_number' => 'RPT-CST-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'customer', 'label' => 'Customer'],
                        ['key' => 'invoices', 'label' => 'Invoices'],
                        ['key' => 'billed', 'label' => 'Total Billed'],
                        ['key' => 'paid', 'label' => 'Total Paid'],
                        ['key' => 'balance', 'label' => 'Receivable Balance'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Receivables', 'value' => round(collect($rows)->sum('balance'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'supplier':
                $vq = PurchaseInvoice::with('vendor:id,name')->where('created_by', $creatorId)->whereBetween('invoice_date', [$dateFrom, $dateTo]);
                if ($vendorId) $vq->where('vendor_id', $vendorId);
                if ($warehouseId) $vq->where('warehouse_id', $warehouseId);
                $rows = $vq->get()->groupBy('vendor_id')->map(fn ($g) => [
                    'supplier' => $g->first()->vendor?->name ?? 'Supplier',
                    'bills' => $g->count(),
                    'billed' => round((float) $g->sum('total_amount'), 2),
                    'paid' => round((float) $g->sum('paid_amount'), 2),
                    'balance' => round((float) $g->sum('balance_amount'), 2),
                ])->values()->all();
                return [
                    'title' => 'SUPPLIER STATEMENT & PAYABLES REPORT',
                    'doc_number' => 'RPT-SUP-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'supplier', 'label' => 'Supplier'],
                        ['key' => 'bills', 'label' => 'Bills'],
                        ['key' => 'billed', 'label' => 'Total Billed'],
                        ['key' => 'paid', 'label' => 'Total Paid'],
                        ['key' => 'balance', 'label' => 'Payable Balance'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Payables', 'value' => round(collect($rows)->sum('balance'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'sales_rep':
                $rq = SalesInvoice::with('creator:id,name')->where('created_by', $creatorId)->whereBetween('invoice_date', [$dateFrom, $dateTo]);
                if ($employeeId) $rq->where('creator_id', $employeeId);
                $rows = $rq->get()->groupBy('creator_id')->map(fn ($g) => [
                    'rep' => $g->first()->creator?->name ?? 'Staff',
                    'orders' => $g->count(),
                    'total_sales' => round((float) $g->sum('total_amount'), 2),
                    'collected' => round((float) $g->sum('paid_amount'), 2),
                ])->values()->all();
                return [
                    'title' => 'SALES REPRESENTATIVE PERFORMANCE REPORT',
                    'doc_number' => 'RPT-REP-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'rep', 'label' => 'Sales Representative'],
                        ['key' => 'orders', 'label' => 'Invoices'],
                        ['key' => 'total_sales', 'label' => 'Total Sales'],
                        ['key' => 'collected', 'label' => 'Collected'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Sales', 'value' => round(collect($rows)->sum('total_sales'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'profit_loss':
                $invRev = (float) SalesInvoice::where('created_by', $creatorId)->where('status', '!=', 'draft')->whereBetween('invoice_date', [$dateFrom, $dateTo])->sum('subtotal');
                $posRev = (float) PosItem::where('created_by', $creatorId)->whereHas('sale', fn ($q) => $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']))->sum('subtotal');
                $revenue = round($invRev + $posRev, 2);
                $cogs = round((float) PurchaseInvoice::where('created_by', $creatorId)->where('status', '!=', 'draft')->whereBetween('invoice_date', [$dateFrom, $dateTo])->sum('subtotal'), 2);
                $gross = round($revenue - $cogs, 2);
                $opex = round((float) Expense::where('created_by', $creatorId)->whereIn('status', ['approved', 'posted'])->whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount'), 2);
                $net = round($gross - $opex, 2);
                $rows = [
                    ['line_item' => 'Sales Revenue (Invoices + POS)', 'category' => 'Income', 'amount' => $revenue],
                    ['line_item' => 'Cost of Goods / Purchases', 'category' => 'Direct Cost', 'amount' => -$cogs],
                    ['line_item' => 'Gross Profit', 'category' => 'Subtotal', 'amount' => $gross],
                    ['line_item' => 'Operating Expenses', 'category' => 'Expense', 'amount' => -$opex],
                ];
                return [
                    'title' => 'PROFIT & LOSS (INCOME) STATEMENT',
                    'doc_number' => 'RPT-PL-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'line_item', 'label' => 'Account / Line Item'],
                        ['key' => 'category', 'label' => 'Classification'],
                        ['key' => 'amount', 'label' => 'Amount'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Gross Profit', 'value' => $gross],
                        ['label' => 'Net Profit / (Loss)', 'value' => $net, 'isGrandTotal' => true],
                    ],
                ];

            case 'cash_flow':
                $custIn = round((float) CustomerPayment::where('created_by', $creatorId)->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('payment_amount'), 2);
                $posIn = round((float) PosPayment::where('created_by', $creatorId)->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->sum('amount'), 2);
                $vendOut = round((float) VendorPayment::where('created_by', $creatorId)->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('payment_amount'), 2);
                $expOut = round((float) Expense::where('created_by', $creatorId)->where('status', 'posted')->whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount'), 2);
                $netCash = round(($custIn + $posIn) - ($vendOut + $expOut), 2);
                $rows = [
                    ['activity' => 'Customer Invoice Collections', 'flow' => 'Inflow', 'amount' => $custIn],
                    ['activity' => 'POS Cash & Counter Receipts', 'flow' => 'Inflow', 'amount' => $posIn],
                    ['activity' => 'Supplier / Vendor Payments', 'flow' => 'Outflow', 'amount' => -$vendOut],
                    ['activity' => 'Operating Expense Disbursements', 'flow' => 'Outflow', 'amount' => -$expOut],
                ];
                return [
                    'title' => 'CASH FLOW STATEMENT',
                    'doc_number' => 'RPT-CF-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'activity', 'label' => 'Cash Activity'],
                        ['key' => 'flow', 'label' => 'Direction'],
                        ['key' => 'amount', 'label' => 'Amount'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Net Cash Movement', 'value' => $netCash, 'isGrandTotal' => true],
                    ],
                ];

            case 'business_comparison':
                $whs = Warehouse::where('created_by', $creatorId)->get();
                $rows = $whs->map(function ($w) use ($creatorId, $dateFrom, $dateTo) {
                    $sales = (float) SalesInvoice::where('created_by', $creatorId)->where('warehouse_id', $w->id)->where('status', '!=', 'draft')->whereBetween('invoice_date', [$dateFrom, $dateTo])->sum('total_amount');
                    $pos = (float) PosItem::where('created_by', $creatorId)->whereHas('sale', fn ($q) => $q->where('warehouse_id', $w->id)->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']))->sum('total_amount');
                    $exp = (float) Expense::where('created_by', $creatorId)->where('warehouse_id', $w->id)->whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
                    return [
                        'location' => $w->name,
                        'invoice_sales' => round($sales, 2),
                        'pos_sales' => round($pos, 2),
                        'expenses' => round($exp, 2),
                        'net_contribution' => round(($sales + $pos) - $exp, 2),
                    ];
                })->values()->all();
                return [
                    'title' => 'LOCATION & BUSINESS COMPARISON REPORT',
                    'doc_number' => 'RPT-CMP-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'location', 'label' => 'Business / Location'],
                        ['key' => 'invoice_sales', 'label' => 'Invoice Sales'],
                        ['key' => 'pos_sales', 'label' => 'POS Sales'],
                        ['key' => 'expenses', 'label' => 'Expenses'],
                        ['key' => 'net_contribution', 'label' => 'Net Contribution'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Combined Net Contribution', 'value' => round(collect($rows)->sum('net_contribution'), 2), 'isGrandTotal' => true],
                    ],
                ];

            case 'sales':
            default:
                $sq = SalesInvoice::with(['customer:id,name', 'warehouse:id,name'])
                    ->where('created_by', $creatorId)
                    ->whereBetween('invoice_date', [$dateFrom, $dateTo]);
                if ($warehouseId) $sq->where('warehouse_id', $warehouseId);
                if ($customerId) $sq->where('customer_id', $customerId);
                if ($employeeId) $sq->where('creator_id', $employeeId);
                $invs = $sq->latest('invoice_date')->get();
                $rows = $invs->map(fn ($i) => [
                    'doc_no' => $i->invoice_number,
                    'date' => optional($i->invoice_date)->format('Y-m-d'),
                    'customer' => $i->customer?->name ?? 'Customer',
                    'location' => $i->warehouse?->name ?? '-',
                    'subtotal' => (float) $i->subtotal,
                    'tax' => (float) $i->tax_amount,
                    'total' => (float) $i->total_amount,
                    'status' => ucfirst($i->status),
                ])->values()->all();
                return [
                    'title' => 'SALES INVOICE REGISTER REPORT',
                    'doc_number' => 'RPT-SLS-' . date('Ymd'),
                    'columns' => [
                        ['key' => 'doc_no', 'label' => 'Invoice No'],
                        ['key' => 'date', 'label' => 'Date'],
                        ['key' => 'customer', 'label' => 'Customer'],
                        ['key' => 'location', 'label' => 'Location'],
                        ['key' => 'subtotal', 'label' => 'Net'],
                        ['key' => 'tax', 'label' => 'Tax'],
                        ['key' => 'total', 'label' => 'Total'],
                        ['key' => 'status', 'label' => 'Status'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        ['label' => 'Total Invoices', 'value' => count($rows)],
                        ['label' => 'Total Sales Value', 'value' => round(collect($rows)->sum('total'), 2), 'isGrandTotal' => true],
                    ],
                ];
        }
    }
}

