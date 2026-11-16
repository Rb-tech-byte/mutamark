import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { Lock, Unlock, Plus, CheckCircle2, AlertTriangle, Landmark, Receipt, TrendingUp, Wallet } from 'lucide-react';

interface FinancialOverviewProps {
    metrics: {
        sales_revenue: number;
        payments_received: number;
        customer_receivables: number;
        supplier_payables: number;
        purchase_costs: number;
        operating_expenses: number;
        cash_balances: number;
        output_tax: number;
        input_tax: number;
        net_tax_payable: number;
        gross_profit: number;
        net_profit: number;
        ledger_total_debits: number;
        ledger_total_credits: number;
        ledger_balanced: boolean;
    };
    periods: Array<{
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        status: 'open' | 'closed';
        closed_at?: string;
        closed_by?: { id: number; name: string };
        notes?: string;
    }>;
    warehouses: Array<{ id: number; name: string }>;
    filters: {
        date_from: string;
        date_to: string;
        warehouse_id?: string | null;
    };
}

export default function FinancialOverview({ metrics, periods, warehouses, filters }: FinancialOverviewProps) {
    const { t } = useTranslation();
    const [dateFrom, setDateFrom] = useState(filters.date_from);
    const [dateTo, setDateTo] = useState(filters.date_to);
    const [warehouseId, setWarehouseId] = useState(filters.warehouse_id || 'all');
    const [showPeriodModal, setShowPeriodModal] = useState(false);

    const periodForm = useForm({
        name: '',
        start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0],
        notes: '',
    });

    const applyFilter = () => {
        router.get(route('financial.overview'), {
            date_from: dateFrom,
            date_to: dateTo,
            warehouse_id: warehouseId === 'all' ? '' : warehouseId,
        }, { preserveState: true });
    };

    const handleCreatePeriod = (e: React.FormEvent) => {
        e.preventDefault();
        periodForm.post(route('financial.periods.store'), {
            onSuccess: () => {
                setShowPeriodModal(false);
                periodForm.reset();
            },
        });
    };

    const kpiCards = [
        { label: t('Sales Revenue (Accrual)'), value: metrics.sales_revenue, icon: TrendingUp, color: 'text-emerald-700' },
        { label: t('Payments Received (Cash)'), value: metrics.payments_received, icon: Wallet, color: 'text-blue-700' },
        { label: t('Customer Receivables'), value: metrics.customer_receivables, icon: Receipt, color: 'text-amber-700' },
        { label: t('Supplier Payables'), value: metrics.supplier_payables, icon: Receipt, color: 'text-rose-700' },
        { label: t('Purchase Costs'), value: metrics.purchase_costs, icon: Landmark, color: 'text-slate-700' },
        { label: t('Operating Expenses'), value: metrics.operating_expenses, icon: Landmark, color: 'text-orange-700' },
        { label: t('Cash & Bank Balances'), value: metrics.cash_balances, icon: Wallet, color: 'text-indigo-700' },
        { label: t('Net Profit'), value: metrics.net_profit, icon: TrendingUp, color: metrics.net_profit >= 0 ? 'text-emerald-700' : 'text-red-700' },
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Accounting') }, { label: t('Financial Management') }]}
            pageTitle={t('Financial Management & Period Closing')}
        >
            <Head title={t('Financial Management')} />

            <div className="space-y-6">
                {/* Filter Bar */}
                <Card>
                    <CardContent className="p-4 flex flex-wrap items-end gap-4">
                        <div className="space-y-1">
                            <Label className="text-xs">{t('From Date')}</Label>
                            <Input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="w-44" />
                        </div>
                        <div className="space-y-1">
                            <Label className="text-xs">{t('To Date')}</Label>
                            <Input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="w-44" />
                        </div>
                        <div className="space-y-1">
                            <Label className="text-xs">{t('Location / Business Unit')}</Label>
                            <Select value={warehouseId} onValueChange={setWarehouseId}>
                                <SelectTrigger className="w-56">
                                    <SelectValue placeholder={t('All Locations')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Locations')}</SelectItem>
                                    {warehouses.map((w) => (
                                        <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <Button onClick={applyFilter}>{t('Apply Filters')}</Button>
                        <Button variant="outline" onClick={() => router.get(route('reports.executive'), { report_type: 'profit_loss', date_from: dateFrom, date_to: dateTo })}>
                            {t('Open P&L / Cash Flow Reports')}
                        </Button>
                    </CardContent>
                </Card>

                {/* Financial Metrics Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {kpiCards.map((item, idx) => (
                        <Card key={idx}>
                            <CardContent className="p-4 flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-muted-foreground font-medium">{item.label}</p>
                                    <p className={`text-xl font-bold mt-1 ${item.color}`}>{formatCurrency(item.value)}</p>
                                </div>
                                <item.icon className={`h-6 w-6 opacity-75 ${item.color}`} />
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Tax & Double-Entry Ledger Verification */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('Tax & Profit Summary')}</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex justify-between py-1 border-b">
                                <span className="text-muted-foreground">{t('Gross Profit (Revenue - Purchases)')}</span>
                                <span className="font-semibold">{formatCurrency(metrics.gross_profit)}</span>
                            </div>
                            <div className="flex justify-between py-1 border-b">
                                <span className="text-muted-foreground">{t('Output Tax (Sales & POS)')}</span>
                                <span className="font-semibold">{formatCurrency(metrics.output_tax)}</span>
                            </div>
                            <div className="flex justify-between py-1 border-b">
                                <span className="text-muted-foreground">{t('Input Tax (Purchase Bills)')}</span>
                                <span className="font-semibold">{formatCurrency(metrics.input_tax)}</span>
                            </div>
                            <div className="flex justify-between py-1 font-bold">
                                <span>{t('Net Tax Payable')}</span>
                                <span>{formatCurrency(metrics.net_tax_payable)}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base flex items-center justify-between">
                                <span>{t('Double-Entry Ledger Integrity')}</span>
                                {metrics.ledger_balanced ? (
                                    <Badge className="bg-emerald-600 text-white"><CheckCircle2 className="h-3.5 w-3.5 mr-1" />{t('Balanced')}</Badge>
                                ) : (
                                    <Badge variant="destructive"><AlertTriangle className="h-3.5 w-3.5 mr-1" />{t('Unbalanced')}</Badge>
                                )}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex justify-between py-1 border-b">
                                <span className="text-muted-foreground">{t('Posted Ledger Total Debits')}</span>
                                <span className="font-mono font-semibold">{formatCurrency(metrics.ledger_total_debits)}</span>
                            </div>
                            <div className="flex justify-between py-1 border-b">
                                <span className="text-muted-foreground">{t('Posted Ledger Total Credits')}</span>
                                <span className="font-mono font-semibold">{formatCurrency(metrics.ledger_total_credits)}</span>
                            </div>
                            <p className="text-xs text-muted-foreground pt-1">
                                {t('Posted journal entries are immutable. Corrections are recorded strictly via reversal entries in the Financial Audit Trail.')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Accounting Period Closing */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base">{t('Accounting Period Closing')}</CardTitle>
                        <Button size="sm" onClick={() => setShowPeriodModal(true)}>
                            <Plus className="h-4 w-4 mr-1" /> {t('New Period')}
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b text-left text-muted-foreground">
                                        <th className="py-2 pr-4">{t('Period Name')}</th>
                                        <th className="py-2 pr-4">{t('Start Date')}</th>
                                        <th className="py-2 pr-4">{t('End Date')}</th>
                                        <th className="py-2 pr-4">{t('Status')}</th>
                                        <th className="py-2 pr-4">{t('Closed By')}</th>
                                        <th className="py-2 text-right">{t('Action')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {periods.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="py-6 text-center text-muted-foreground">
                                                {t('No accounting periods defined yet. Create a period to lock financial postings after month/year close.')}
                                            </td>
                                        </tr>
                                    ) : periods.map((p) => (
                                        <tr key={p.id} className="border-b">
                                            <td className="py-2.5 pr-4 font-medium">{p.name}</td>
                                            <td className="py-2.5 pr-4">{formatDate(p.start_date)}</td>
                                            <td className="py-2.5 pr-4">{formatDate(p.end_date)}</td>
                                            <td className="py-2.5 pr-4">
                                                <Badge variant={p.status === 'closed' ? 'destructive' : 'secondary'}>
                                                    {p.status === 'closed' ? t('Closed / Locked') : t('Open')}
                                                </Badge>
                                            </td>
                                            <td className="py-2.5 pr-4">{p.closed_by?.name || '-'}</td>
                                            <td className="py-2.5 text-right">
                                                {p.status === 'open' ? (
                                                    <Button size="sm" variant="destructive" onClick={() => router.post(route('financial.periods.close', p.id))}>
                                                        <Lock className="h-3.5 w-3.5 mr-1" /> {t('Close Period')}
                                                    </Button>
                                                ) : (
                                                    <Button size="sm" variant="outline" onClick={() => router.post(route('financial.periods.reopen', p.id))}>
                                                        <Unlock className="h-3.5 w-3.5 mr-1" /> {t('Reopen')}
                                                    </Button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={showPeriodModal} onOpenChange={setShowPeriodModal}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Create Accounting Period')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreatePeriod} className="space-y-4">
                        <div className="space-y-1">
                            <Label>{t('Period Name')} *</Label>
                            <Input value={periodForm.data.name} onChange={(e) => periodForm.setData('name', e.target.value)} placeholder="e.g. FY 2026 - November" required />
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label>{t('Start Date')} *</Label>
                                <Input type="date" value={periodForm.data.start_date} onChange={(e) => periodForm.setData('start_date', e.target.value)} required />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('End Date')} *</Label>
                                <Input type="date" value={periodForm.data.end_date} onChange={(e) => periodForm.setData('end_date', e.target.value)} required />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setShowPeriodModal(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={periodForm.processing}>{t('Create Period')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}

