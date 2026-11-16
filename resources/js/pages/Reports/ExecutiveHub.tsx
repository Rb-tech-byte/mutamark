import React, { useRef, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import CorporateDocumentTemplate from '@/components/CorporateDocumentTemplate';
import { Printer, Download, FileSpreadsheet, FileText } from 'lucide-react';

interface ExecutiveHubProps {
    report: {
        title: string;
        doc_number: string;
        columns: Array<{ key: string; label: string }>;
        rows: Array<Record<string, any>>;
        totals: Array<{ label: string; value: string | number; isGrandTotal?: boolean }>;
    };
    reportType: string;
    filters: Record<string, any>;
    options: {
        warehouses: Array<{ id: number; name: string }>;
        employees: Array<{ id: number; name: string }>;
        customers: Array<{ id: number; name: string }>;
        vendors: Array<{ id: number; name: string }>;
        products: Array<{ id: number; name: string; sku: string }>;
        categories: Array<{ id: number; name: string }>;
    };
}

const REPORT_TYPES = [
    { value: 'sales', label: 'Sales Report' },
    { value: 'purchase', label: 'Purchase Report' },
    { value: 'tax', label: 'Tax Report' },
    { value: 'inventory', label: 'Inventory Report' },
    { value: 'stock_valuation', label: 'Stock Valuation Report' },
    { value: 'expense', label: 'Expense Report' },
    { value: 'cash_register', label: 'Cash Register Report' },
    { value: 'customer', label: 'Customer Report' },
    { value: 'supplier', label: 'Supplier Report' },
    { value: 'sales_rep', label: 'Sales Representative Report' },
    { value: 'profit_loss', label: 'Profit & Loss Statement' },
    { value: 'cash_flow', label: 'Cash Flow Statement' },
    { value: 'business_comparison', label: 'Business / Location Comparison' },
];

export default function ExecutiveHub({ report, reportType, filters, options }: ExecutiveHubProps) {
    const { t } = useTranslation();
    const docRef = useRef<HTMLDivElement>(null);
    const [downloading, setDownloading] = useState(false);

    const [formFilters, setFormFilters] = useState({
        report_type: reportType || 'sales',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        warehouse_id: filters.warehouse_id || 'all',
        employee_id: filters.employee_id || 'all',
        customer_id: filters.customer_id || 'all',
        vendor_id: filters.vendor_id || 'all',
        product_id: filters.product_id || 'all',
        category_id: filters.category_id || 'all',
    });

    const cleanParams = (src = formFilters) => {
        const out: Record<string, string> = {};
        Object.entries(src).forEach(([k, v]) => {
            if (v && v !== 'all') out[k] = String(v);
        });
        return out;
    };

    const applyFilters = () => {
        router.get(route('reports.executive'), cleanParams(), { preserveState: true });
    };

    const handleExportExcel = () => {
        const qs = new URLSearchParams(cleanParams()).toString();
        window.location.href = `${route('reports.executive.export-excel')}?${qs}`;
    };

    const handleExportCsv = () => {
        const escapeCell = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`;
        const headers = report.columns.map((c) => c.label);
        const rows = report.rows.map((r) => report.columns.map((c) => r[c.key] ?? ''));
        const csv = [headers.map(escapeCell).join(','), ...rows.map((r) => r.map(escapeCell).join(','))].join('\n');
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `report-${formFilters.report_type}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    const handleDownloadPdf = async () => {
        if (!docRef.current) return;
        setDownloading(true);
        try {
            const html2pdf = (await import('html2pdf.js')).default;
            await html2pdf()
                .set({
                    margin: [8, 8, 8, 8],
                    filename: `${report.doc_number}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                })
                .from(docRef.current)
                .save();
        } finally {
            setDownloading(false);
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Reports') }, { label: t('Executive Reports Hub') }]}
            pageTitle={t('Reporting & Analytics Hub')}
        >
            <Head title={t('Reporting & Analytics Hub')} />

            <div className="space-y-6">
                {/* Multi-Filter Panel */}
                <Card className="print:hidden">
                    <CardContent className="p-4 space-y-4">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('Report Type')}</Label>
                                <Select value={formFilters.report_type} onValueChange={(v) => setFormFilters({ ...formFilters, report_type: v })}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        {REPORT_TYPES.map((rt) => (
                                            <SelectItem key={rt.value} value={rt.value}>{t(rt.label)}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('From Date')}</Label>
                                <Input type="date" value={formFilters.date_from} onChange={(e) => setFormFilters({ ...formFilters, date_from: e.target.value })} />
                            </div>
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('To Date')}</Label>
                                <Input type="date" value={formFilters.date_to} onChange={(e) => setFormFilters({ ...formFilters, date_to: e.target.value })} />
                            </div>
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('Business / Location')}</Label>
                                <Select value={formFilters.warehouse_id} onValueChange={(v) => setFormFilters({ ...formFilters, warehouse_id: v })}>
                                    <SelectTrigger><SelectValue placeholder={t('All Locations')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Locations')}</SelectItem>
                                        {options.warehouses.map((w) => <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('Employee / Sales Rep')}</Label>
                                <Select value={formFilters.employee_id} onValueChange={(v) => setFormFilters({ ...formFilters, employee_id: v })}>
                                    <SelectTrigger><SelectValue placeholder={t('All Employees')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Employees')}</SelectItem>
                                        {options.employees.map((e) => <SelectItem key={e.id} value={String(e.id)}>{e.name}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('Customer')}</Label>
                                <Select value={formFilters.customer_id} onValueChange={(v) => setFormFilters({ ...formFilters, customer_id: v })}>
                                    <SelectTrigger><SelectValue placeholder={t('All Customers')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Customers')}</SelectItem>
                                        {options.customers.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('Supplier')}</Label>
                                <Select value={formFilters.vendor_id} onValueChange={(v) => setFormFilters({ ...formFilters, vendor_id: v })}>
                                    <SelectTrigger><SelectValue placeholder={t('All Suppliers')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Suppliers')}</SelectItem>
                                        {options.vendors.map((v) => <SelectItem key={v.id} value={String(v.id)}>{v.name}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t('Product / Category')}</Label>
                                <Select value={formFilters.category_id} onValueChange={(v) => setFormFilters({ ...formFilters, category_id: v })}>
                                    <SelectTrigger><SelectValue placeholder={t('All Categories')} /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Categories')}</SelectItem>
                                        {options.categories.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center justify-between gap-2 pt-2 border-t">
                            <Button onClick={applyFilters}>{t('Generate Report Document')}</Button>
                            <div className="flex flex-wrap gap-2">
                                <Button type="button" variant="outline" size="sm" onClick={handleExportExcel}>
                                    <FileSpreadsheet className="h-4 w-4 mr-1.5" /> {t('Export Excel (.xlsx)')}
                                </Button>
                                <Button type="button" variant="outline" size="sm" onClick={handleExportCsv}>
                                    <FileText className="h-4 w-4 mr-1.5" /> {t('Export CSV')}
                                </Button>
                                <Button type="button" variant="outline" size="sm" onClick={() => window.print()}>
                                    <Printer className="h-4 w-4 mr-1.5" /> {t('Print')}
                                </Button>
                                <Button type="button" size="sm" onClick={handleDownloadPdf} disabled={downloading}>
                                    <Download className="h-4 w-4 mr-1.5" /> {downloading ? t('Generating...') : t('Download PDF')}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Formal Document Output */}
                <div className="bg-white rounded-lg border shadow-sm overflow-x-auto print:border-0 print:shadow-none">
                    <div ref={docRef} className="min-w-[750px] p-4 print:p-0">
                        <CorporateDocumentTemplate
                            type="invoice"
                            documentTitle={report.title}
                            documentNumber={report.doc_number}
                            documentNumberLabel={t('Report Ref')}
                            date={new Date().toLocaleDateString('en-GB')}
                            dateLabel={t('Generated On')}
                            leftBoxTitle={t('Filter Criteria')}
                            leftBoxFields={[
                                { label: t('Period'), value: `${formFilters.date_from} to ${formFilters.date_to}` },
                                { label: t('Records'), value: report.rows.length },
                            ]}
                            rightBoxTitle={t('Document Control')}
                            rightBoxFields={[
                                { label: t('Classification'), value: t('Official Management Report') },
                                { label: t('Format'), value: 'A4 / PDF / Excel / CSV' },
                            ]}
                            columns={report.columns.map((c) => ({ key: c.key, label: c.label }))}
                            items={report.rows}
                            totals={report.totals.map((tot) => ({
                                label: tot.label,
                                value: typeof tot.value === 'number' ? tot.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : String(tot.value),
                                isGrandTotal: tot.isGrandTotal,
                            }))}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

