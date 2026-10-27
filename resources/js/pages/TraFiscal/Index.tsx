import { useState } from 'react';
import { Head, usePage, router, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import {
    Receipt, Landmark, QrCode, ShieldCheck, CheckCircle2,
    Clock, AlertTriangle, Printer, FileText, ArrowUpRight,
    TrendingUp, ExternalLink, Calendar, Search, Activity
} from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import NoRecordsFound from '@/components/no-records-found';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { PaginatedData } from '@/types/common';

interface TraFiscalReceipt {
    id: number;
    receipt_number: string;
    source_type: string;
    source_id: number;
    invoice_number: string;
    customer_name: string;
    customer_tin: string;
    customer_vrn?: string;
    total_tax_exclusive_amount: number;
    tax_amount_a: number;
    total_tax_inclusive_amount: number;
    z_number: string;
    daily_receipt_counter: number;
    rct_verification_code: string;
    qr_payload: string;
    vfd_device_serial: string;
    status: string;
    fiscalized_at: string;
}

interface TraZReport {
    id: number;
    z_report_number: string;
    report_date: string;
    opening_receipt_number?: string;
    closing_receipt_number?: string;
    total_receipts_count: number;
    gross_turnover: number;
    net_taxable_turnover: number;
    total_vat_18: number;
    cumulative_turnover_to_date: number;
    status: string;
    tra_ack_token?: string;
    closed_at?: string;
    closed_by_user?: {
        id: number;
        name: string;
    };
}

interface UnfiscalizedInvoice {
    id: number;
    invoice_number: string;
    total_amount: number;
    invoice_date: string;
    status: string;
}

interface TraFiscalIndexProps {
    receipts: PaginatedData<TraFiscalReceipt>;
    zReports: PaginatedData<TraZReport>;
    activeZReport: TraZReport;
    statistics: {
        vfd_status: string;
        vfd_device_serial: string;
        company_tin: string;
        company_vrn: string;
        active_z_number: string;
        active_z_status: string;
        today_receipts_count: number;
        today_gross_turnover: number;
        today_net_turnover: number;
        today_vat_18: number;
        cumulative_turnover: number;
    };
    unfiscalizedInvoices: UnfiscalizedInvoice[];
    auth: any;
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { receipts, zReports, activeZReport, statistics, unfiscalizedInvoices } = usePage<TraFiscalIndexProps>().props;

    const [activeTab, setActiveTab] = useState('receipts');
    const [search, setSearch] = useState('');
    const [isClosingZ, setIsClosingZ] = useState(false);
    const [processingFiscalize, setProcessingFiscalize] = useState<number | null>(null);

    useFlashMessages();

    const handleSearch = () => {
        router.get(route('tra-fiscal.index'), { search }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleFiscalizeInvoice = (invoiceId: number) => {
        setProcessingFiscalize(invoiceId);
        router.post(route('tra-fiscal.fiscalize', invoiceId), {}, {
            onFinish: () => setProcessingFiscalize(null),
        });
    };

    const handleCloseZReport = () => {
        setIsClosingZ(true);
        router.post(route('tra-fiscal.z-report.close'), {}, {
            onFinish: () => setIsClosingZ(false),
        });
    };

    const receiptColumns = [
        {
            key: 'receipt_number',
            header: t('Receipt Number'),
            render: (_: any, rct: TraFiscalReceipt) => (
                <div className="flex flex-col">
                    <Link
                        href={route('tra-fiscal.receipt', rct.id)}
                        className="font-mono font-semibold text-primary hover:underline text-xs flex items-center gap-1"
                    >
                        {rct.receipt_number}
                    </Link>
                    <span className="text-[11px] text-muted-foreground">
                        {formatDate(rct.fiscalized_at)}
                    </span>
                </div>
            )
        },
        {
            key: 'invoice_number',
            header: t('Source Ref'),
            render: (_: any, rct: TraFiscalReceipt) => (
                <div className="flex flex-col text-xs font-mono">
                    <span className="text-foreground">{rct.invoice_number || '-'}</span>
                    <span className="text-[10px] text-muted-foreground uppercase">{rct.source_type.replace('_', ' ')}</span>
                </div>
            )
        },
        {
            key: 'customer',
            header: t('Customer & Buyer TIN'),
            render: (_: any, rct: TraFiscalReceipt) => (
                <div className="flex flex-col text-xs">
                    <span className="font-medium text-foreground">{rct.customer_name}</span>
                    <span className="text-[11px] font-mono text-muted-foreground">TIN: {rct.customer_tin}</span>
                </div>
            )
        },
        {
            key: 'amounts',
            header: t('Gross Total (TZS)'),
            render: (_: any, rct: TraFiscalReceipt) => (
                <div className="flex flex-col text-xs font-mono text-right">
                    <span className="font-bold text-foreground">
                        {Number(rct.total_tax_inclusive_amount).toLocaleString()} TZS
                    </span>
                    <span className="text-[10px] text-emerald-600">
                        VAT 18%: {Number(rct.tax_amount_a).toLocaleString()} TZS
                    </span>
                </div>
            )
        },
        {
            key: 'vcode',
            header: t('Verification Code'),
            render: (_: any, rct: TraFiscalReceipt) => (
                <div className="flex flex-col text-xs font-mono">
                    <span className="text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                        {rct.rct_verification_code}
                    </span>
                    <span className="text-[10px] text-muted-foreground">{rct.z_number}</span>
                </div>
            )
        },
        {
            key: 'status',
            header: t('Status'),
            render: (_: any, rct: TraFiscalReceipt) => (
                <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 text-[10px] flex items-center gap-1 w-fit">
                    <ShieldCheck className="h-3 w-3" />
                    {t('Fiscalized')}
                </Badge>
            )
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, rct: TraFiscalReceipt) => (
                <div className="flex items-center gap-1">
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                                    <Link href={route('tra-fiscal.receipt', rct.id)}>
                                        <Printer className="h-3.5 w-3.5" />
                                    </Link>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Print TRA Fiscal Slip')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                                    <a href={rct.qr_payload} target="_blank" rel="noopener noreferrer">
                                        <ExternalLink className="h-3.5 w-3.5 text-muted-foreground" />
                                    </a>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Verify on TRA Gateway')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            )
        }
    ];

    const zReportColumns = [
        {
            key: 'z_report_number',
            header: t('Z-Report No.'),
            render: (_: any, z: TraZReport) => (
                <div className="flex flex-col">
                    <Link
                        href={route('tra-fiscal.z-report.show', z.id)}
                        className="font-mono font-semibold text-primary hover:underline text-xs flex items-center gap-1"
                    >
                        {z.z_report_number}
                    </Link>
                    <span className="text-[11px] text-muted-foreground">{formatDate(z.report_date)}</span>
                </div>
            )
        },
        {
            key: 'receipts_range',
            header: t('Receipt Range & Count'),
            render: (_: any, z: TraZReport) => (
                <div className="flex flex-col text-xs font-mono">
                    <span className="font-semibold text-foreground">{z.total_receipts_count} {t('Receipts')}</span>
                    <span className="text-[10px] text-muted-foreground">
                        {z.opening_receipt_number || '-'} → {z.closing_receipt_number || '-'}
                    </span>
                </div>
            )
        },
        {
            key: 'gross_turnover',
            header: t('Daily Gross Turnover (TZS)'),
            render: (_: any, z: TraZReport) => (
                <div className="text-right text-xs font-mono font-bold text-foreground">
                    {Number(z.gross_turnover).toLocaleString()} TZS
                </div>
            )
        },
        {
            key: 'vat_18',
            header: t('Total VAT A (18%)'),
            render: (_: any, z: TraZReport) => (
                <div className="text-right text-xs font-mono font-semibold text-emerald-600">
                    {Number(z.total_vat_18).toLocaleString()} TZS
                </div>
            )
        },
        {
            key: 'status',
            header: t('Status'),
            render: (_: any, z: TraZReport) => (
                z.status === 'transmitted' ? (
                    <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 border-emerald-500/30 text-[10px] flex items-center gap-1 w-fit">
                        <CheckCircle2 className="h-3 w-3" />
                        {t('Transmitted')}
                    </Badge>
                ) : (
                    <Badge variant="outline" className="bg-amber-500/10 text-amber-600 border-amber-500/30 text-[10px] flex items-center gap-1 w-fit">
                        <Clock className="h-3 w-3" />
                        {t('Open Fiscal Day')}
                    </Badge>
                )
            )
        },
        {
            key: 'tra_ack',
            header: t('TRA Acknowledgment'),
            render: (_: any, z: TraZReport) => (
                <span className="text-xs font-mono text-muted-foreground">
                    {z.tra_ack_token || t('Pending EOD')}
                </span>
            )
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, z: TraZReport) => (
                <Button variant="ghost" size="icon" className="h-7 w-7" asChild>
                    <Link href={route('tra-fiscal.z-report.show', z.id)}>
                        <Printer className="h-3.5 w-3.5" />
                    </Link>
                </Button>
            )
        }
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Finance & Accounting') }, { label: t('TRA Fiscal Compliance (EFDMS/VFD)') }]}
            pageTitle={t('TRA Real-Time Fiscal Compliance Engine')}
            pageActions={
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={handleCloseZReport}
                        disabled={isClosingZ || activeZReport.total_receipts_count === 0}
                        className="border-amber-500/40 text-amber-700 dark:text-amber-400 hover:bg-amber-500/10"
                    >
                        <Clock className="h-4 w-4 mr-1.5" />
                        {isClosingZ ? t('Transmitting...') : t('Close Daily Z-Report')}
                    </Button>
                </div>
            }
        >
            <Head title={t('TRA EFDMS / VFD Fiscal Compliance')} />

            <div className="space-y-6">
                {/* VFD Gateway Banner */}
                <div className="p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <div className="h-10 w-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600">
                            <Activity className="h-5 w-5 animate-pulse" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="font-bold text-sm text-foreground">
                                    Tanzania Revenue Authority EFDMS Virtual Fiscal Device (VFD)
                                </span>
                                <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 border-emerald-500/30 font-semibold text-[10px]">
                                    ● {statistics.vfd_status}
                                </Badge>
                            </div>
                            <div className="text-xs text-muted-foreground flex flex-wrap gap-x-4 gap-y-0.5 mt-0.5 font-mono">
                                <span>TIN: <strong className="text-foreground">{statistics.company_tin}</strong></span>
                                <span>VRN: <strong className="text-foreground">{statistics.company_vrn}</strong></span>
                                <span>Device Serial: <strong className="text-foreground">{statistics.vfd_device_serial}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-2 text-xs font-mono bg-background/80 p-2 rounded-lg border border-border">
                        <span className="text-muted-foreground">{t('Active Z-Report')}:</span>
                        <span className="font-bold text-primary">{statistics.active_z_number}</span>
                    </div>
                </div>

                {/* Fiscal Metric Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Today Fiscal Turnover')}
                            </CardTitle>
                            <TrendingUp className="h-4 w-4 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold font-mono">
                                {Number(statistics.today_gross_turnover).toLocaleString()} TZS
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Net Taxable')}: {Number(statistics.today_net_turnover).toLocaleString()} TZS
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('VAT A Collected (18%)')}
                            </CardTitle>
                            <Landmark className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                                {Number(statistics.today_vat_18).toLocaleString()} TZS
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('TRA statutory VAT liability')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Fiscal Receipts Today')}
                            </CardTitle>
                            <Receipt className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {statistics.today_receipts_count}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Receipts transmitted to TRA')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Cumulative Turnover')}
                            </CardTitle>
                            <ShieldCheck className="h-4 w-4 text-slate-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold font-mono text-foreground">
                                {Number(statistics.cumulative_turnover).toLocaleString()} TZS
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Fiscal memory total to date')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs for Receipts, Z-Reports, and Unfiscalized Queue */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList className="grid grid-cols-3 max-w-md">
                        <TabsTrigger value="receipts" className="text-xs">
                            <Receipt className="h-3.5 w-3.5 mr-1.5" />
                            {t('Fiscal Receipts')}
                        </TabsTrigger>
                        <TabsTrigger value="zreports" className="text-xs">
                            <Calendar className="h-3.5 w-3.5 mr-1.5" />
                            {t('Daily Z-Reports')}
                        </TabsTrigger>
                        <TabsTrigger value="queue" className="text-xs relative">
                            <FileText className="h-3.5 w-3.5 mr-1.5" />
                            {t('Pending Queue')}
                            {unfiscalizedInvoices.length > 0 && (
                                <span className="ml-1.5 bg-primary text-primary-foreground rounded-full px-1.5 py-0.2 text-[10px]">
                                    {unfiscalizedInvoices.length}
                                </span>
                            )}
                        </TabsTrigger>
                    </TabsList>

                    {/* Tab 1: Real-Time Fiscal Receipts */}
                    <TabsContent value="receipts" className="space-y-4">
                        <Card className="border border-border/60 shadow-sm">
                            <CardContent className="p-4 border-b bg-muted/20 flex justify-between items-center gap-4">
                                <div className="max-w-md flex-1">
                                    <SearchInput
                                        value={search}
                                        onChange={setSearch}
                                        onSearch={handleSearch}
                                        placeholder={t('Search receipt number, invoice, customer name...')}
                                    />
                                </div>
                            </CardContent>
                            <CardContent className="p-0">
                                {receipts.data.length > 0 ? (
                                    <DataTable
                                        columns={receiptColumns}
                                        data={receipts.data}
                                    />
                                ) : (
                                    <div className="p-8 text-center">
                                        <NoRecordsFound icon={Receipt} title={t('No fiscal receipts issued yet today.')} />
                                    </div>
                                )}
                            </CardContent>
                            {receipts.data.length > 0 && (
                                <div className="p-4 border-t flex justify-end">
                                    <Pagination data={receipts} />
                                </div>
                            )}
                        </Card>
                    </TabsContent>

                    {/* Tab 2: Daily Z-Reports */}
                    <TabsContent value="zreports" className="space-y-4">
                        <Card className="border border-border/60 shadow-sm">
                            <CardContent className="p-0">
                                {zReports.data.length > 0 ? (
                                    <DataTable
                                        columns={zReportColumns}
                                        data={zReports.data}
                                    />
                                ) : (
                                    <div className="p-8 text-center">
                                        <NoRecordsFound icon={Calendar} title={t('No historical Z-Reports recorded.')} />
                                    </div>
                                )}
                            </CardContent>
                            {zReports.data.length > 0 && (
                                <div className="p-4 border-t flex justify-end">
                                    <Pagination data={zReports} />
                                </div>
                            )}
                        </Card>
                    </TabsContent>

                    {/* Tab 3: Unfiscalized Invoices Queue */}
                    <TabsContent value="queue" className="space-y-4">
                        <Card className="border border-border/60 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-sm font-semibold flex items-center gap-2">
                                    <FileText className="h-4 w-4 text-primary" />
                                    {t('Invoices Awaiting Fiscalization')}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                {unfiscalizedInvoices.length > 0 ? (
                                    <div className="divide-y text-xs">
                                        {unfiscalizedInvoices.map((inv) => (
                                            <div key={inv.id} className="p-4 flex items-center justify-between gap-4 hover:bg-muted/10">
                                                <div className="space-y-0.5">
                                                    <div className="font-semibold text-foreground text-sm font-mono">
                                                        {inv.invoice_number}
                                                    </div>
                                                    <div className="text-muted-foreground">
                                                        {t('Date')}: {formatDate(inv.invoice_date)} • {t('Status')}: {inv.status}
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-4">
                                                    <div className="text-right font-mono font-bold text-sm">
                                                        {Number(inv.total_amount).toLocaleString()} TZS
                                                    </div>
                                                    <Button
                                                        size="sm"
                                                        onClick={() => handleFiscalizeInvoice(inv.id)}
                                                        disabled={processingFiscalize === inv.id}
                                                        className="bg-emerald-600 hover:bg-emerald-700"
                                                    >
                                                        <QrCode className="h-3.5 w-3.5 mr-1.5" />
                                                        {processingFiscalize === inv.id ? t('Fiscalizing...') : t('Issue Fiscal Slip')}
                                                    </Button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="p-8 text-center">
                                        <NoRecordsFound icon={CheckCircle2} title={t('All posted sales invoices have been fiscalized!')} />
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}
