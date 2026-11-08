<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\HelpdeskTicket;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\SalesOrder;
use App\Models\SalesProposal;
use App\Models\GoodsReceivedNote;
use App\Models\Transfer;
use App\Models\DeliveryNote;
use App\Models\ClearingForwarding\CFJob;
use App\Models\ClearingForwarding\CFDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Models\Warehouse;
use Workdo\Account\Models\BankAccount;
use Workdo\Account\Models\Revenue;
use Workdo\Account\Models\Expense;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\Hrm\Models\Employee;
use Workdo\Hrm\Models\Attendance;
use Workdo\Hrm\Models\LeaveApplication;

class HomeController extends Controller
{
    public function Dashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->type === 'superadmin') {
            return $this->superAdminDashboard();
        }

        // B2B Portals redirection
        if ($user->type === 'client' || $user->hasRole('client')) {
            return redirect()->route('portal.customer');
        }

        if ($user->type === 'vendor' || $user->hasRole('vendor')) {
            return redirect()->route('portal.supplier');
        }

        // Dedicated Clearing & Forwarding operational roles
        if (($user->hasRole('Clearing & Forwarding Manager') || $user->hasRole('Customs Declarant') || $user->hasRole('Transport Coordinator')) && $user->type !== 'company') {
            return redirect()->route('clearing-forwarding.dashboard');
        }

        return $this->buildEnterpriseRoleDashboard($user);
    }

    private function superAdminDashboard()
    {
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $chartData = [];

        // Tickets pending admin response (tickets where last reply is from client/company)
        $adminUserId = Auth::id();
        
        $weeklyPendingTickets = HelpdeskTicket::with(['category', 'creator', 'replies' => function($query) {
                $query->latest()->limit(1);
            }])
            ->whereIn('status', ['open', 'in_progress'])
            ->where(function($query) use ($adminUserId) {
                // Tickets with no replies at all
                $query->whereDoesntHave('replies')
                    // OR tickets where last reply is NOT from superadmin
                    ->orWhereHas('replies', function($q) use ($adminUserId) {
                        $q->whereRaw('id = (SELECT MAX(id) FROM helpdesk_replies WHERE ticket_id = helpdesk_tickets.id)')
                          ->where('created_by', '!=', $adminUserId);
                    });
            })
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($ticket) => [
                'id' => $ticket->id,
                'ticket_id' => $ticket->ticket_id,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category?->name,
                'category_color' => $ticket->category?->color,
                'creator' => $ticket->creator?->name,
                'created_at' => $ticket->created_at->diffForHumans(),
                'last_reply_at' => $ticket->replies->first()?->created_at?->diffForHumans() ?? $ticket->created_at->diffForHumans(),
                'days_pending' => $ticket->replies->first() 
                    ? $ticket->replies->first()->created_at->diffInDays(now())
                    : $ticket->created_at->diffInDays(now())
            ]);

        // Monthly ticket trends
        $ticketMonthlyData = HelpdeskTicket::selectRaw('MONTH(created_at) as month, COUNT(*) as created')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $resolvedMonthlyData = HelpdeskTicket::selectRaw('MONTH(resolved_at) as month, COUNT(*) as resolved')
            ->whereYear('resolved_at', now()->year)
            ->whereNotNull('resolved_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $ticketChartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $ticketChartData[] = [
                'month' => $months[$i-1],
                'created' => $ticketMonthlyData[$i]->created ?? 0,
                'resolved' => $resolvedMonthlyData[$i]->resolved ?? 0
            ];
        }

        // Recent helpdesk tickets
        $recentTickets = HelpdeskTicket::with(['category', 'creator'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($ticket) => [
                'id' => $ticket->id,
                'ticket_id' => $ticket->ticket_id,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category?->name,
                'category_color' => $ticket->category?->color,
                'creator' => $ticket->creator?->name,
                'created_at' => $ticket->created_at->diffForHumans()
            ]);

        return Inertia::render('SuperAdminDashboard', [
            'stats' => [
                'total_companies' => User::where('type', 'company')->count(),
                'active_modules' => count((new \App\Classes\Module())->allEnabled()),
                'verified_documents' => \App\Models\DocumentVerification::count(),
                'open_tickets' => HelpdeskTicket::whereIn('status', ['open', 'in_progress'])->count(),
            ],
            'chartData' => $chartData,
            'ticketChartData' => $ticketChartData,
            'recentTickets' => $recentTickets,
            'weeklyPendingTickets' => $weeklyPendingTickets
        ]);
    }

    private function buildEnterpriseRoleDashboard($user)
    {
        $isCompany = $user->type === 'company' || $user->hasRole('company');
        $isFinance = $user->hasRole('finance_manager');
        $isWarehouse = $user->hasRole('warehouse_manager');
        $isSales = $user->hasRole('sales_manager');
        $isHr = $user->hasRole('hr_manager');
        $isPos = $user->hasRole('pos_cashier');

        $companyId = $isCompany ? $user->id : ($user->created_by ?: creatorId());

        // Resolve active role metadata
        $activeRole = 'employee';
        $roleTitle = 'Staff Member';
        $roleBadge = 'Employee';

        if ($isCompany) {
            $activeRole = 'company';
            $roleTitle = 'Enterprise Owner';
            $roleBadge = 'Enterprise Owner';
        } elseif ($isFinance) {
            $activeRole = 'finance_manager';
            $roleTitle = 'Head of Finance & Tax Compliance';
            $roleBadge = 'TRA Fiscal & GL';
        } elseif ($isWarehouse) {
            $activeRole = 'warehouse_manager';
            $roleTitle = 'Logistics & Supply Chain Manager';
            $roleBadge = 'WMS & Supply Chain';
        } elseif ($isSales) {
            $activeRole = 'sales_manager';
            $roleTitle = 'Commercial Sales & B2B CRM Executive';
            $roleBadge = 'Wholesale & B2B';
        } elseif ($isHr) {
            $activeRole = 'hr_manager';
            $roleTitle = 'HR & Statutory Payroll Specialist';
            $roleBadge = 'TZ Statutory HR';
        } elseif ($isPos) {
            $activeRole = 'pos_cashier';
            $roleTitle = 'POS Cashier Desk';
            $roleBadge = 'Retail Desk';
        }

        // Shared or Company Master Data
        $data = [
            'active_role' => $activeRole,
            'role_title' => $roleTitle,
            'role_badge' => $roleBadge,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ];

        // -------------------------------------------------------------
        // 1. FINANCE METRICS (For Company & Finance Manager)
        // -------------------------------------------------------------
        if ($isCompany || $isFinance) {
            $bankAccounts = BankAccount::where('created_by', $companyId)
                ->where('is_active', 1)
                ->get(['id', 'bank_name', 'account_name', 'account_number', 'current_balance', 'currency'])
                ->map(fn($b) => [
                    'id' => $b->id,
                    'bank_name' => $b->bank_name,
                    'account_name' => $b->account_name,
                    'account_number' => $b->account_number,
                    'current_balance' => (float) $b->current_balance,
                    'currency' => $b->currency ?: 'TZS',
                ]);

            $totalBankBalance = (float) $bankAccounts->sum('current_balance');

            $monthlyRevenue = (float) Revenue::where('created_by', $companyId)
                ->whereYear('revenue_date', now()->year)
                ->whereMonth('revenue_date', now()->month)
                ->sum('amount');

            $monthlyExpense = (float) Expense::where('created_by', $companyId)
                ->whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month)
                ->sum('amount');

            $unpaidInvoicesQuery = SalesInvoice::where('created_by', $companyId)
                ->whereIn('status', ['sent', 'unpaid', 'partially_paid']);
            $unpaidInvoicesAmount = (float) $unpaidInvoicesQuery->sum('balance_amount');
            $unpaidInvoicesCount = $unpaidInvoicesQuery->count();

            $unpaidBillsQuery = PurchaseInvoice::where('created_by', $companyId)
                ->whereIn('status', ['sent', 'unpaid', 'partially_paid']);
            $unpaidBillsAmount = (float) $unpaidBillsQuery->sum('balance_amount');
            $unpaidBillsCount = $unpaidBillsQuery->count();

            $traFiscalCount = SalesInvoice::where('created_by', $companyId)
                ->where('status', '!=', 'cancelled')
                ->count();

            $threeWayMatchCount = GoodsReceivedNote::where('created_by', $companyId)
                ->where('matching_status', 'matched')
                ->count();

            $recentInvoices = SalesInvoice::where('created_by', $companyId)
                ->latest()
                ->take(5)
                ->get(['id', 'invoice_number', 'customer_id', 'total_amount', 'balance_amount', 'status', 'invoice_date'])
                ->map(fn($inv) => [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'customer_name' => $inv->customer?->name ?? 'Customer #' . $inv->customer_id,
                    'total_amount' => (float) $inv->total_amount,
                    'balance_amount' => (float) $inv->balance_amount,
                    'status' => $inv->status,
                    'invoice_date' => $inv->invoice_date,
                ]);

            $recentRevenues = Revenue::where('created_by', $companyId)
                ->latest()
                ->take(5)
                ->get(['id', 'revenue_number', 'amount', 'revenue_date', 'description', 'status']);

            $recentExpenses = Expense::where('created_by', $companyId)
                ->latest()
                ->take(5)
                ->get(['id', 'expense_number', 'amount', 'expense_date', 'description', 'status']);

            $data['finance'] = [
                'bank_accounts' => $bankAccounts,
                'total_bank_balance' => $totalBankBalance,
                'monthly_revenue' => $monthlyRevenue,
                'monthly_expense' => $monthlyExpense,
                'net_cashflow' => $monthlyRevenue - $monthlyExpense,
                'unpaid_invoices_amount' => $unpaidInvoicesAmount,
                'unpaid_invoices_count' => $unpaidInvoicesCount,
                'unpaid_bills_amount' => $unpaidBillsAmount,
                'unpaid_bills_count' => $unpaidBillsCount,
                'tra_fiscal_count' => $traFiscalCount,
                'three_way_match_count' => $threeWayMatchCount,
                'recent_invoices' => $recentInvoices,
                'recent_revenues' => $recentRevenues,
                'recent_expenses' => $recentExpenses,
            ];
        }

        // -------------------------------------------------------------
        // 2. WAREHOUSE & INVENTORY METRICS (For Company & Warehouse Manager)
        // -------------------------------------------------------------
        if ($isCompany || $isWarehouse) {
            $totalItemsCount = ProductServiceItem::where('created_by', $companyId)->count();

            $stockValuation = (float) DB::table('warehouse_stocks')
                ->join('product_service_items', 'warehouse_stocks.product_id', '=', 'product_service_items.id')
                ->where('product_service_items.created_by', $companyId)
                ->sum(DB::raw('warehouse_stocks.quantity * product_service_items.purchase_price'));

            // Low stock items (items where total stock <= 30)
            $lowStockItems = DB::table('warehouse_stocks')
                ->join('product_service_items', 'warehouse_stocks.product_id', '=', 'product_service_items.id')
                ->where('product_service_items.created_by', $companyId)
                ->select(
                    'product_service_items.id',
                    'product_service_items.name',
                    'product_service_items.sku',
                    'product_service_items.sale_price',
                    'product_service_items.purchase_price',
                    DB::raw('SUM(warehouse_stocks.quantity) as current_stock')
                )
                ->groupBy('product_service_items.id', 'product_service_items.name', 'product_service_items.sku', 'product_service_items.sale_price', 'product_service_items.purchase_price')
                ->having('current_stock', '<=', 40)
                ->take(6)
                ->get();

            $pendingGrns = GoodsReceivedNote::where('created_by', $companyId)
                ->whereIn('status', ['draft', 'pending', 'inspection'])
                ->orWhere('inspection_status', 'pending')
                ->latest()
                ->take(5)
                ->get(['id', 'grn_number', 'vendor_id', 'total_amount', 'grn_date', 'status', 'inspection_status'])
                ->map(fn($g) => [
                    'id' => $g->id,
                    'grn_number' => $g->grn_number,
                    'vendor_name' => $g->vendor?->name ?? 'Vendor #' . $g->vendor_id,
                    'total_amount' => (float) $g->total_amount,
                    'grn_date' => $g->grn_date,
                    'status' => $g->status,
                    'inspection_status' => $g->inspection_status,
                ]);

            $activeTransfers = Transfer::where('created_by', $companyId)
                ->whereIn('status', ['pending', 'in_transit'])
                ->latest()
                ->take(5)
                ->get(['id', 'transfer_number', 'from_warehouse', 'to_warehouse', 'quantity', 'status', 'date'])
                ->map(fn($t) => [
                    'id' => $t->id,
                    'transfer_number' => $t->transfer_number,
                    'from_wh' => $t->fromWarehouse?->name ?? 'Warehouse #' . $t->from_warehouse,
                    'to_wh' => $t->toWarehouse?->name ?? 'Warehouse #' . $t->to_warehouse,
                    'quantity' => (int) $t->quantity,
                    'status' => $t->status,
                    'date' => $t->date,
                ]);

            $pendingDeliveryNotes = DeliveryNote::where('created_by', $companyId)
                ->whereIn('status', ['pending', 'dispatched'])
                ->latest()
                ->take(5)
                ->get(['id', 'delivery_number', 'destination_city', 'delivery_date', 'status']);

            $warehousesSummary = Warehouse::where('created_by', $companyId)
                ->get(['id', 'name', 'city', 'phone', 'is_active']);

            $data['warehouse'] = [
                'stock_valuation' => $stockValuation,
                'total_items_count' => $totalItemsCount,
                'low_stock_count' => count($lowStockItems),
                'low_stock_items' => $lowStockItems,
                'pending_grn_count' => $pendingGrns->count(),
                'pending_grns' => $pendingGrns,
                'active_transfers_count' => $activeTransfers->count(),
                'active_transfers' => $activeTransfers,
                'pending_delivery_notes_count' => $pendingDeliveryNotes->count(),
                'pending_delivery_notes' => $pendingDeliveryNotes,
                'warehouses_summary' => $warehousesSummary,
            ];
        }

        // -------------------------------------------------------------
        // 3. COMMERCIAL SALES & POS (For Company, Sales Manager, POS Cashier)
        // -------------------------------------------------------------
        if ($isCompany || $isSales || $isPos) {
            $monthlySalesAmount = (float) SalesOrder::where('created_by', $companyId)
                ->whereYear('order_date', now()->year)
                ->whereMonth('order_date', now()->month)
                ->sum('total_amount');

            $openSalesOrders = SalesOrder::where('created_by', $companyId)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->latest()
                ->take(5)
                ->get(['id', 'order_number', 'customer_id', 'total_amount', 'status', 'order_date'])
                ->map(fn($o) => [
                    'id' => $o->id,
                    'order_number' => $o->order_number,
                    'customer_name' => $o->customer?->name ?? 'Customer #' . $o->customer_id,
                    'total_amount' => (float) $o->total_amount,
                    'status' => $o->status,
                    'order_date' => $o->order_date,
                ]);

            $pendingProposalsCount = SalesProposal::where('created_by', $companyId)
                ->whereIn('status', ['draft', 'sent'])
                ->count();

            $openProformasCount = SalesInvoice::where('created_by', $companyId)
                ->where('type', 'proforma')
                ->whereIn('status', ['sent', 'unpaid'])
                ->count();

            // Today's POS turnover
            $posTodaySales = (float) DB::table('pos')
                ->join('pos_payments', 'pos.id', '=', 'pos_payments.pos_id')
                ->where('pos.created_by', $companyId)
                ->whereDate('pos.created_at', now()->today())
                ->sum('pos_payments.amount');

            $posTodayOrdersCount = DB::table('pos')
                ->where('created_by', $companyId)
                ->whereDate('created_at', now()->today())
                ->count();

            $recentPosOrders = DB::table('pos')
                ->leftJoin('pos_payments', 'pos.id', '=', 'pos_payments.pos_id')
                ->where('pos.created_by', $companyId)
                ->latest('pos.created_at')
                ->take(6)
                ->select('pos.id', 'pos.sale_number', 'pos.created_at', 'pos.status', 'pos_payments.amount', 'pos_payments.payment_method')
                ->get();

            $data['sales'] = [
                'monthly_sales_amount' => $monthlySalesAmount,
                'open_sales_orders_count' => $openSalesOrders->count(),
                'open_sales_orders' => $openSalesOrders,
                'pending_proposals_count' => $pendingProposalsCount,
                'open_proformas_count' => $openProformasCount,
                'pos_today_sales' => $posTodaySales,
                'pos_today_orders_count' => $posTodayOrdersCount,
                'average_basket' => $posTodayOrdersCount > 0 ? round($posTodaySales / $posTodayOrdersCount, 2) : 0,
                'recent_pos_orders' => $recentPosOrders,
            ];
        }

        // -------------------------------------------------------------
        // 4. CLEARING & FORWARDING (For Company & C&F Operations)
        // -------------------------------------------------------------
        if ($isCompany) {
            $activeCfJobsCount = CFJob::where('created_by', $companyId)
                ->whereNotIn('status', ['closed', 'cancelled'])
                ->count();

            $tancisPendingCount = CFDeclaration::whereHas('job', function($q) use ($companyId) {
                $q->where('created_by', $companyId);
            })->whereNotIn('status', ['released', 'cancelled'])->count();

            $demurrageRiskCount = DB::table('cf_containers')
                ->join('cf_jobs', 'cf_containers.cf_job_id', '=', 'cf_jobs.id')
                ->where('cf_jobs.created_by', $companyId)
                ->where('cf_containers.status', '!=', 'returned')
                ->whereNotNull('cf_containers.empty_return_deadline')
                ->whereDate('cf_containers.empty_return_deadline', '<=', now()->addDays(3))
                ->count();

            $recentCfJobs = CFJob::where('created_by', $companyId)
                ->latest()
                ->take(5)
                ->get(['id', 'job_number', 'consignee_name', 'transport_mode', 'service_type', 'status', 'created_at']);

            $data['clearing_forwarding'] = [
                'active_jobs_count' => $activeCfJobsCount,
                'tancis_pending_count' => $tancisPendingCount,
                'demurrage_risk_count' => $demurrageRiskCount,
                'recent_jobs' => $recentCfJobs,
            ];
        }

        // -------------------------------------------------------------
        // 5. HUMAN RESOURCES & PAYROLL (For Company & HR Manager)
        // -------------------------------------------------------------
        if ($isCompany || $isHr) {
            $totalEmployees = Employee::where('created_by', $companyId)->count();

            $presentToday = Attendance::where('created_by', $companyId)
                ->whereDate('date', now()->today())
                ->count();

            $onLeaveToday = LeaveApplication::where('created_by', $companyId)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', now()->today())
                ->whereDate('end_date', '>=', now()->today())
                ->count();

            $pendingLeaves = LeaveApplication::where('created_by', $companyId)
                ->where('status', 'pending')
                ->with('employee.user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($l) => [
                    'id' => $l->id,
                    'employee_name' => $l->employee?->user?->name ?? 'Employee #' . $l->employee_id,
                    'start_date' => $l->start_date,
                    'end_date' => $l->end_date,
                    'total_days' => $l->total_days,
                    'reason' => $l->reason,
                    'status' => $l->status,
                ]);

            $recentEmployees = Employee::where('created_by', $companyId)
                ->with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($e) => [
                    'id' => $e->id,
                    'name' => $e->user?->name ?? 'Employee #' . $e->employee_id,
                    'email' => $e->user?->email ?? '-',
                    'phone' => $e->emergency_contact_number ?? '-',
                    'is_active' => $e->user?->is_enable_login ?? 1,
                    'created_at' => $e->created_at?->diffForHumans() ?? '-',
                ]);

            $data['hrm'] = [
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'on_leave_today' => $onLeaveToday,
                'pending_leaves_count' => $pendingLeaves->count(),
                'pending_leaves' => $pendingLeaves,
                'recent_employees' => $recentEmployees,
                'statutory_deadlines' => [
                    ['authority' => 'TRA PAYE', 'deadline' => '7th of Every Month', 'status' => 'Pending Filing'],
                    ['authority' => 'NSSF Tanzania', 'deadline' => '15th of Every Month', 'status' => 'Compliance OK'],
                    ['authority' => 'WCF Workers Comp', 'deadline' => '30th of Every Month', 'status' => 'Compliance OK'],
                    ['authority' => 'SDL Skills Levy', 'deadline' => '7th of Every Month', 'status' => 'Pending Filing'],
                ],
            ];
        }

        return Inertia::render('dashboard', $data);
    }
}
