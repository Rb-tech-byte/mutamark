import React from 'react';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Head, Link, usePage } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { formatCurrency, getImagePath } from "@/utils/helpers";
import {
    Building2,
    ShieldCheck,
    Calculator,
    Boxes,
    TrendingUp,
    Users,
    Store,
    Ship,
    CheckCircle2,
    AlertTriangle,
    ArrowUpRight,
    ArrowDownLeft,
    FileText,
    ShoppingBag,
    Receipt,
    DollarSign,
    Clock,
    Layers,
    ArrowRight,
    ExternalLink,
    RefreshCw,
    Plus,
    Eye,
    Landmark,
    Truck,
    Package,
    Calendar,
    Briefcase,
    CheckCircle,
    UserCheck,
    AlertCircle,
} from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

interface BankAccountData {
    id: number;
    bank_name: string;
    account_name: string;
    account_number: string;
    current_balance: number;
    currency: string;
}

interface InvoiceData {
    id: number;
    invoice_number: string;
    customer_name: string;
    total_amount: number;
    balance_amount: number;
    status: string;
    invoice_date: string;
}

interface GrnData {
    id: number;
    grn_number: string;
    vendor_name: string;
    total_amount: number;
    grn_date: string;
    status: string;
    inspection_status: string;
}

interface TransferData {
    id: number;
    transfer_number: string;
    from_wh: string;
    to_wh: string;
    quantity: number;
    status: string;
    date: string;
}

interface SalesOrderData {
    id: number;
    order_number: string;
    customer_name: string;
    total_amount: number;
    status: string;
    order_date: string;
}

interface LeaveData {
    id: number;
    employee_name: string;
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: string;
}

interface DashboardProps {
    active_role: string;
    role_title: string;
    role_badge: string;
    user: {
        name: string;
        email: string;
        avatar?: string;
    };
    finance?: {
        bank_accounts: BankAccountData[];
        total_bank_balance: number;
        monthly_revenue: number;
        monthly_expense: number;
        net_cashflow: number;
        unpaid_invoices_amount: number;
        unpaid_invoices_count: number;
        unpaid_bills_amount: number;
        unpaid_bills_count: number;
        tra_fiscal_count: number;
        three_way_match_count: number;
        recent_invoices: InvoiceData[];
        recent_revenues: any[];
        recent_expenses: any[];
    };
    warehouse?: {
        stock_valuation: number;
        total_items_count: number;
        low_stock_count: number;
        low_stock_items: any[];
        pending_grn_count: number;
        pending_grns: GrnData[];
        active_transfers_count: number;
        active_transfers: TransferData[];
        pending_delivery_notes_count: number;
        pending_delivery_notes: any[];
        warehouses_summary: any[];
    };
    sales?: {
        monthly_sales_amount: number;
        open_sales_orders_count: number;
        open_sales_orders: SalesOrderData[];
        pending_proposals_count: number;
        open_proformas_count: number;
        pos_today_sales: number;
        pos_today_orders_count: number;
        average_basket: number;
        recent_pos_orders: any[];
    };
    clearing_forwarding?: {
        active_jobs_count: number;
        tancis_pending_count: number;
        demurrage_risk_count: number;
        recent_jobs: any[];
    };
    hrm?: {
        total_employees: number;
        present_today: number;
        on_leave_today: number;
        pending_leaves_count: number;
        pending_leaves: LeaveData[];
        recent_employees: any[];
        statutory_deadlines: Array<{ authority: string; deadline: string; status: string }>;
    };
}

export default function Dashboard(props: DashboardProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const {
        active_role = 'company',
        role_title = 'Enterprise Dashboard',
        role_badge = 'Enterprise Owner',
        finance,
        warehouse,
        sales,
        clearing_forwarding,
        hrm,
    } = props;

    const isCompany = active_role === 'company' || auth.user?.type === 'company';
    const isFinance = active_role === 'finance_manager';
    const isWarehouse = active_role === 'warehouse_manager';
    const isSales = active_role === 'sales_manager';
    const isHr = active_role === 'hr_manager';
    const isPos = active_role === 'pos_cashier';

    // Role theme badge styling
    const getRoleBadgeClasses = () => {
        if (isCompany) return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-200';
        if (isFinance) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200';
        if (isWarehouse) return 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-200';
        if (isSales) return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300 border-cyan-200';
        if (isHr) return 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-200';
        if (isPos) return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200';
        return 'bg-muted text-muted-foreground';
    };

    const getRoleIcon = () => {
        if (isCompany) return Building2;
        if (isFinance) return Calculator;
        if (isWarehouse) return Boxes;
        if (isSales) return TrendingUp;
        if (isHr) return Users;
        if (isPos) return Store;
        return Briefcase;
    };

    const RoleIcon = getRoleIcon();

    return (
        <AuthenticatedLayout header={t(role_title)}>
            <Head title={t(role_title)} />

            <div className="space-y-6 pb-12">
                {/* 1. TOP EXECUTIVE ROLE BANNER */}
                <div className="relative overflow-hidden rounded-2xl border bg-gradient-to-r from-card via-card to-muted/40 p-6 md:p-8 shadow-sm">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div className="space-y-2">
                            <div className="flex items-center gap-3">
                                <div className="h-12 w-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold">
                                    <RoleIcon className="h-6 w-6" />
                                </div>
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h1 className="text-2xl font-bold tracking-tight">
                                            {t('Welcome back')}, {auth.user?.name}!
                                        </h1>
                                        <Badge variant="outline" className={`font-semibold px-2.5 py-0.5 border ${getRoleBadgeClasses()}`}>
                                            {role_badge}
                                        </Badge>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        {auth.user?.email} • {new Date().toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Quick Context Actions */}
                        <div className="flex flex-wrap items-center gap-2.5">
                            {isCompany && (
                                <>
                                    <Button asChild size="sm" variant="default" className="gap-1.5 shadow-sm">
                                        <Link href={route('sales-invoices.create')}>
                                            <Plus className="h-4 w-4" /> {t('New Invoice')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('clearing-forwarding.jobs.create')}>
                                            <Ship className="h-4 w-4" /> {t('New C&F Job')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('goods-received-notes.create')}>
                                            <Boxes className="h-4 w-4" /> {t('New GRN')}
                                        </Link>
                                    </Button>
                                </>
                            )}

                            {isFinance && (
                                <>
                                    <Button asChild size="sm" variant="default" className="gap-1.5 shadow-sm">
                                        <Link href={route('sales-invoices.create')}>
                                            <Plus className="h-4 w-4" /> {t('Create Invoice')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('account.bank-accounts.index')}>
                                            <Landmark className="h-4 w-4" /> {t('Bank Accounts')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('account.reports.index')}>
                                            <FileText className="h-4 w-4" /> {t('Financial Reports')}
                                        </Link>
                                    </Button>
                                </>
                            )}

                            {isWarehouse && (
                                <>
                                    <Button asChild size="sm" variant="default" className="gap-1.5 shadow-sm">
                                        <Link href={route('goods-received-notes.create')}>
                                            <Plus className="h-4 w-4" /> {t('Receive GRN')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('transfers.create')}>
                                            <Truck className="h-4 w-4" /> {t('New Transfer')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('product-service.stock.index')}>
                                            <Boxes className="h-4 w-4" /> {t('Stock Valuation')}
                                        </Link>
                                    </Button>
                                </>
                            )}

                            {isSales && (
                                <>
                                    <Button asChild size="sm" variant="default" className="gap-1.5 shadow-sm">
                                        <Link href={route('sales-orders.create')}>
                                            <Plus className="h-4 w-4" /> {t('New Sales Order')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('sales-proposals.create')}>
                                            <FileText className="h-4 w-4" /> {t('Create Proposal')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('pos.create')}>
                                            <Store className="h-4 w-4" /> {t('POS Register')}
                                        </Link>
                                    </Button>
                                </>
                            )}

                            {isHr && (
                                <>
                                    <Button asChild size="sm" variant="default" className="gap-1.5 shadow-sm">
                                        <Link href={route('hrm.employees.create')}>
                                            <Plus className="h-4 w-4" /> {t('Add Employee')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('hrm.reports.tanzania-statutory')}>
                                            <ShieldCheck className="h-4 w-4" /> {t('Statutory Returns')}
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline" className="gap-1.5">
                                        <Link href={route('hrm.leave-applications.calendar')}>
                                            <Calendar className="h-4 w-4" /> {t('Leave Calendar')}
                                        </Link>
                                    </Button>
                                </>
                            )}

                            {isPos && (
                                <Button asChild size="lg" variant="default" className="gap-2 shadow-md bg-emerald-600 hover:bg-emerald-700">
                                    <Link href={route('pos.create')}>
                                        <Store className="h-5 w-5" /> {t('Launch POS Terminal')}
                                    </Link>
                                </Button>
                            )}
                        </div>
                    </div>
                </div>

                {/* ========================================================= */}
                {/* 2. COMPANY OWNER MASTER EXECUTIVE DASHBOARD               */}
                {/* ========================================================= */}
                {isCompany && (
                    <div className="space-y-6">
                        {/* High-Level Executive KPI Grid */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            <Card className="border-l-4 border-l-emerald-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                        {t('Bank Liquidity')}
                                    </CardTitle>
                                    <Landmark className="h-5 w-5 text-emerald-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold tracking-tight text-emerald-700 dark:text-emerald-400">
                                        {formatCurrency(finance?.total_bank_balance || 0)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                        <CheckCircle2 className="h-3.5 w-3.5 text-emerald-500" />
                                        {t('CRDB & NMB Operating Accounts')}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-amber-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                        {t('Stock Valuation')}
                                    </CardTitle>
                                    <Boxes className="h-5 w-5 text-amber-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold tracking-tight text-amber-700 dark:text-amber-400">
                                        {formatCurrency(warehouse?.stock_valuation || 0)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                        <Layers className="h-3.5 w-3.5 text-amber-500" />
                                        {warehouse?.total_items_count || 0} {t('registered product SKUs')}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-cyan-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                        {t('C&F Shipments')}
                                    </CardTitle>
                                    <Ship className="h-5 w-5 text-cyan-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold tracking-tight text-cyan-700 dark:text-cyan-400">
                                        {clearing_forwarding?.active_jobs_count || 0} {t('Active')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                        <CheckCircle className="h-3.5 w-3.5 text-cyan-500" />
                                        {clearing_forwarding?.tancis_pending_count || 0} {t('TANCIS clearance in progress')}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-indigo-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                        {t('POS Store Turnover')}
                                    </CardTitle>
                                    <Store className="h-5 w-5 text-indigo-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold tracking-tight text-indigo-700 dark:text-indigo-400">
                                        {formatCurrency(sales?.pos_today_sales || 0)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                                        <Clock className="h-3.5 w-3.5 text-indigo-500" />
                                        {sales?.pos_today_orders_count || 0} {t('receipts issued today')}
                                    </p>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Operational Overview Grid */}
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Bank Liquidity Matrix */}
                            <Card className="shadow-sm">
                                <CardHeader className="pb-3 flex flex-row items-center justify-between">
                                    <div>
                                        <CardTitle className="text-base font-semibold">{t('Real-Time Bank Accounts')}</CardTitle>
                                        <CardDescription>{t('Cash & bank balances across all authorized institutions')}</CardDescription>
                                    </div>
                                    <Button asChild variant="ghost" size="sm" className="gap-1 text-xs">
                                        <Link href={route('account.bank-accounts.index')}>
                                            {t('View All')} <ArrowRight className="h-3.5 w-3.5" />
                                        </Link>
                                    </Button>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {finance?.bank_accounts && finance.bank_accounts.length > 0 ? (
                                            finance.bank_accounts.map((bank) => (
                                                <div key={bank.id} className="flex items-center justify-between p-3.5 rounded-xl border bg-muted/20">
                                                    <div className="flex items-center gap-3">
                                                        <div className="h-10 w-10 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 flex items-center justify-center font-bold">
                                                            <Landmark className="h-5 w-5" />
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-sm">{bank.bank_name}</div>
                                                            <div className="text-xs text-muted-foreground font-mono">{bank.account_number} • {bank.account_name}</div>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="font-bold text-sm text-emerald-700 dark:text-emerald-400">
                                                            {formatCurrency(bank.current_balance)}
                                                        </div>
                                                        <div className="text-[11px] text-muted-foreground font-mono">{bank.currency}</div>
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <p className="text-sm text-muted-foreground text-center py-4">{t('No active bank accounts configured.')}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Recent Sales Invoices */}
                            <Card className="shadow-sm">
                                <CardHeader className="pb-3 flex flex-row items-center justify-between">
                                    <div>
                                        <CardTitle className="text-base font-semibold">{t('Recent Sales Invoices')}</CardTitle>
                                        <CardDescription>{t('Commercial billing and customer payment balances')}</CardDescription>
                                    </div>
                                    <Button asChild variant="ghost" size="sm" className="gap-1 text-xs">
                                        <Link href={route('sales-invoices.index')}>
                                            {t('View All')} <ArrowRight className="h-3.5 w-3.5" />
                                        </Link>
                                    </Button>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {finance?.recent_invoices && finance.recent_invoices.length > 0 ? (
                                            finance.recent_invoices.map((inv) => (
                                                <div key={inv.id} className="flex items-center justify-between p-3 rounded-lg border">
                                                    <div>
                                                        <div className="font-semibold text-sm flex items-center gap-2">
                                                            {inv.invoice_number}
                                                            <Badge variant="outline" className="text-[11px] uppercase py-0">
                                                                {inv.status}
                                                            </Badge>
                                                        </div>
                                                        <div className="text-xs text-muted-foreground mt-0.5">{inv.customer_name} • {inv.invoice_date}</div>
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="font-bold text-sm">{formatCurrency(inv.total_amount)}</div>
                                                        {inv.balance_amount > 0 && (
                                                            <div className="text-xs text-rose-600 font-medium">
                                                                {t('Bal')}: {formatCurrency(inv.balance_amount)}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <p className="text-sm text-muted-foreground text-center py-4">{t('No recent invoices found.')}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                )}

                {/* ========================================================= */}
                {/* 3. FINANCE & TAX COMPLIANCE DASHBOARD                     */}
                {/* ========================================================= */}
                {isFinance && finance && (
                    <div className="space-y-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            <Card className="border-l-4 border-l-emerald-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Total Bank Liquidity')}</CardTitle>
                                    <Landmark className="h-5 w-5 text-emerald-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-emerald-700 dark:text-emerald-400">
                                        {formatCurrency(finance.total_bank_balance)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Total cash & bank deposits')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-blue-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Accounts Receivable')}</CardTitle>
                                    <ArrowDownLeft className="h-5 w-5 text-blue-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-blue-700 dark:text-blue-400">
                                        {formatCurrency(finance.unpaid_invoices_amount)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{finance.unpaid_invoices_count} {t('unpaid sales invoices')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-rose-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Accounts Payable')}</CardTitle>
                                    <ArrowUpRight className="h-5 w-5 text-rose-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-rose-700 dark:text-rose-400">
                                        {formatCurrency(finance.unpaid_bills_amount)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{finance.unpaid_bills_count} {t('pending vendor bills')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-teal-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('3-Way Match Verified')}</CardTitle>
                                    <ShieldCheck className="h-5 w-5 text-teal-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-teal-700 dark:text-teal-400">
                                        {finance.three_way_match_count} {t('GRNs')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('PO vs GRN vs Bill verified')}</p>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Bank Accounts Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                            {finance.bank_accounts.map((b) => (
                                <Card key={b.id} className="border shadow-sm">
                                    <CardHeader className="pb-2">
                                        <div className="flex items-center justify-between">
                                            <Badge variant="outline" className="font-mono text-xs">{b.currency}</Badge>
                                            <Landmark className="h-4 w-4 text-emerald-600" />
                                        </div>
                                        <CardTitle className="text-base font-bold mt-2">{b.bank_name}</CardTitle>
                                        <CardDescription className="font-mono text-xs">{b.account_number}</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400">
                                            {formatCurrency(b.current_balance)}
                                        </div>
                                        <div className="text-xs text-muted-foreground mt-1">{b.account_name}</div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                )}

                {/* ========================================================= */}
                {/* 4. LOGISTICS & WAREHOUSE DASHBOARD                        */}
                {/* ========================================================= */}
                {isWarehouse && warehouse && (
                    <div className="space-y-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            <Card className="border-l-4 border-l-amber-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Stock Valuation')}</CardTitle>
                                    <Boxes className="h-5 w-5 text-amber-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-amber-700 dark:text-amber-400">
                                        {formatCurrency(warehouse.stock_valuation)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{warehouse.total_items_count} {t('registered product SKUs')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-rose-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Low Stock Alerts')}</CardTitle>
                                    <AlertTriangle className="h-5 w-5 text-rose-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-rose-700 dark:text-rose-400">
                                        {warehouse.low_stock_count} {t('SKUs')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Below minimum reorder threshold')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-blue-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Pending GRN Receipts')}</CardTitle>
                                    <Package className="h-5 w-5 text-blue-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-blue-700 dark:text-blue-400">
                                        {warehouse.pending_grn_count} {t('GRNs')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Goods waiting inspection / posting')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-emerald-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Inter-Warehouse Transfers')}</CardTitle>
                                    <Truck className="h-5 w-5 text-emerald-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-emerald-700 dark:text-emerald-400">
                                        {warehouse.active_transfers_count} {t('Active')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Dispatched / in-transit stock')}</p>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Low Stock Warning Table */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-3 flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle className="text-base font-semibold text-rose-700 flex items-center gap-2">
                                        <AlertTriangle className="h-4 w-4" /> {t('Low Stock & Reorder Threshold Warnings')}
                                    </CardTitle>
                                    <CardDescription>{t('Items requiring immediate procurement or warehouse transfer')}</CardDescription>
                                </div>
                                <Button asChild variant="outline" size="sm" className="gap-1 text-xs">
                                    <Link href={route('product-service.stock.index')}>
                                        {t('Stock Register')} <ArrowRight className="h-3.5 w-3.5" />
                                    </Link>
                                </Button>
                            </CardHeader>
                            <CardContent>
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>{t('SKU')}</TableHead>
                                                <TableHead>{t('Item Name')}</TableHead>
                                                <TableHead>{t('Purchase Price')}</TableHead>
                                                <TableHead>{t('Sale Price')}</TableHead>
                                                <TableHead className="text-right">{t('Current Stock')}</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {warehouse.low_stock_items && warehouse.low_stock_items.length > 0 ? (
                                                warehouse.low_stock_items.map((item) => (
                                                    <TableRow key={item.id}>
                                                        <TableCell className="font-mono text-xs font-bold">{item.sku}</TableCell>
                                                        <TableCell className="font-medium text-sm">{item.name}</TableCell>
                                                        <TableCell className="text-xs">{formatCurrency(item.purchase_price)}</TableCell>
                                                        <TableCell className="text-xs font-semibold">{formatCurrency(item.sale_price)}</TableCell>
                                                        <TableCell className="text-right">
                                                            <Badge variant="destructive" className="font-bold">
                                                                {item.current_stock} {t('Units')}
                                                            </Badge>
                                                        </TableCell>
                                                    </TableRow>
                                                ))
                                            ) : (
                                                <TableRow>
                                                    <TableCell colSpan={5} className="text-center py-4 text-muted-foreground">
                                                        {t('All stock levels are currently optimal!')}
                                                    </TableCell>
                                                </TableRow>
                                            )}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* ========================================================= */}
                {/* 5. COMMERCIAL SALES & CRM DASHBOARD                       */}
                {/* ========================================================= */}
                {isSales && sales && (
                    <div className="space-y-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            <Card className="border-l-4 border-l-cyan-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Monthly Sales Orders')}</CardTitle>
                                    <TrendingUp className="h-5 w-5 text-cyan-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-cyan-700 dark:text-cyan-400">
                                        {formatCurrency(sales.monthly_sales_amount)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Total sales orders booked this month')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-blue-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Open Sales Orders')}</CardTitle>
                                    <ShoppingBag className="h-5 w-5 text-blue-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-blue-700 dark:text-blue-400">
                                        {sales.open_sales_orders_count} {t('Orders')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Awaiting fulfillment or delivery')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-indigo-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('POS Retail Turnover')}</CardTitle>
                                    <Store className="h-5 w-5 text-indigo-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-indigo-700 dark:text-indigo-400">
                                        {formatCurrency(sales.pos_today_sales)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{sales.pos_today_orders_count} {t('receipts issued today')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-emerald-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Commercial Proposals')}</CardTitle>
                                    <FileText className="h-5 w-5 text-emerald-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-emerald-700 dark:text-emerald-400">
                                        {sales.pending_proposals_count} {t('Quotations')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{sales.open_proformas_count} {t('active proforma invoices')}</p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                )}

                {/* ========================================================= */}
                {/* 6. HR & STATUTORY PAYROLL DASHBOARD                       */}
                {/* ========================================================= */}
                {isHr && hrm && (
                    <div className="space-y-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            <Card className="border-l-4 border-l-rose-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Active Headcount')}</CardTitle>
                                    <Users className="h-5 w-5 text-rose-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-rose-700 dark:text-rose-400">
                                        {hrm.total_employees} {t('Staff')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Permanent & contract personnel')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-emerald-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Present Today')}</CardTitle>
                                    <UserCheck className="h-5 w-5 text-emerald-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-emerald-700 dark:text-emerald-400">
                                        {hrm.present_today} {t('Clocked In')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{hrm.on_leave_today} {t('staff on approved leave')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-amber-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Pending Leaves')}</CardTitle>
                                    <Calendar className="h-5 w-5 text-amber-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-amber-700 dark:text-amber-400">
                                        {hrm.pending_leaves_count} {t('Requests')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Awaiting manager approval')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-purple-500 shadow-sm">
                                <CardHeader className="pb-2 flex flex-row items-center justify-between">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Tanzania Statutory')}</CardTitle>
                                    <ShieldCheck className="h-5 w-5 text-purple-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-purple-700 dark:text-purple-400">
                                        4 {t('Bodies')}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('TRA PAYE, NSSF, WCF, SDL')}</p>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Statutory Deadlines Matrix */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-3">
                                <CardTitle className="text-base font-semibold">{t('Tanzania Statutory Compliance Deadlines')}</CardTitle>
                                <CardDescription>{t('Mandatory statutory monthly return filing schedule')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    {hrm.statutory_deadlines.map((item, idx) => (
                                        <div key={idx} className="p-4 rounded-xl border bg-muted/20 space-y-1">
                                            <div className="font-bold text-sm">{item.authority}</div>
                                            <div className="text-xs text-muted-foreground font-medium">{item.deadline}</div>
                                            <Badge variant="outline" className="text-[11px] bg-emerald-50 text-emerald-700 border-emerald-200 mt-2">
                                                {item.status}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* ========================================================= */}
                {/* 7. POS CASHIER DESK DASHBOARD                             */}
                {/* ========================================================= */}
                {isPos && sales && (
                    <div className="space-y-6">
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <Card className="border-l-4 border-l-emerald-500 shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Today\'s Register Sales')}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-extrabold text-emerald-700 dark:text-emerald-400">
                                        {formatCurrency(sales.pos_today_sales)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Total sales completed on counter')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-blue-500 shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Receipts Issued')}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-extrabold text-blue-700 dark:text-blue-400">
                                        {sales.pos_today_orders_count}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Total retail customer transactions')}</p>
                                </CardContent>
                            </Card>

                            <Card className="border-l-4 border-l-indigo-500 shadow-sm">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-xs font-semibold text-muted-foreground uppercase">{t('Average Basket Size')}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-extrabold text-indigo-700 dark:text-indigo-400">
                                        {formatCurrency(sales.average_basket)}
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-1">{t('Average revenue per transaction')}</p>
                                </CardContent>
                            </Card>
                        </div>

                        <div className="flex justify-center pt-4">
                            <Button asChild size="lg" className="px-8 py-6 text-lg font-bold shadow-lg gap-3 bg-emerald-600 hover:bg-emerald-700">
                                <Link href={route('pos.create')}>
                                    <Store className="h-6 w-6" /> {t('Launch Retail Register Terminal')}
                                </Link>
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
