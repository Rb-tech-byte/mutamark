import React, { useState, useMemo } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import {
    ShieldCheck,
    CheckCircle2,
    AlertTriangle,
    Sliders,
    FileText,
    Link as LinkIcon,
    Printer,
    Building2,
    Calendar,
    MoreVertical,
    Clock,
    Lock,
    Unlock,
    HelpCircle,
    Check,
    X,
    TrendingDown,
    ExternalLink,
    DollarSign
} from 'lucide-react';

interface AuditItem {
    id: number;
    product_id: number;
    product_name: string;
    sku: string;
    po_qty: number;
    grn_qty: number;
    billed_qty: number;
    po_price: number;
    billed_price: number;
    qty_variance: number;
    price_variance: number;
    amount_variance: number;
    is_matched: boolean;
}

interface AuditRecord {
    grn: {
        id: number;
        grn_number: string;
        purchase_order_number?: string;
        purchase_invoice_id?: number;
        grn_date: string;
        inspection_status: string;
        total_amount: number;
        vendor?: { id: number; company_name?: string; contact_person_name?: string };
        warehouse?: { id: number; name: string };
        purchase_invoice?: { id: number; invoice_number: string; total_amount: number; status: string };
    };
    has_invoice: boolean;
    match_status: 'matched_cleared' | 'variance_hold' | 'pending_invoice';
    qc_passed: boolean;
    exceeds_tolerance: boolean;
    tolerance_reasons: string[];
    total_qty_variance: number;
    total_price_variance: number;
    total_variance_amount_tzs: number;
    authorization?: {
        id: number;
        authorization_status: string;
        disbursement_cleared: boolean;
        cleared_at?: string;
        clearance_notes?: string;
        verification_hash?: string;
        cleared_by_user?: { id: number; name: string };
    } | null;
    items: AuditItem[];
}

interface PageProps {
    audits: AuditRecord[];
    config: {
        id: number;
        qty_tolerance_percent: number;
        price_tolerance_percent: number;
        max_variance_amount_tzs: number;
        require_qc_passed: boolean;
        auto_hold_on_variance: boolean;
    };
    kpi: {
        total_audited_count: number;
        cleared_count: number;
        variance_hold_count: number;
        pending_invoice_count: number;
        total_cleared_disbursement_tzs: number;
    };
    available_invoices: Array<{
        id: number;
        invoice_number: string;
        total_amount: number;
        status: string;
        invoice_date: string;
    }>;
    filters?: {
        search?: string;
        status?: string;
    };
}

export default function ThreeWayMatchIndex({
    audits = [],
    config,
    kpi,
    available_invoices = [],
    filters = {},
}: PageProps) {
    const { t } = useTranslation();

    // Filters
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedTab, setSelectedTab] = useState(filters.status || 'all');

    // Modals
    const [isConfigOpen, setIsConfigOpen] = useState(false);
    const [isInspectOpen, setIsInspectOpen] = useState(false);
    const [isAuthorizeOpen, setIsAuthorizeOpen] = useState(false);
    const [isDisputeOpen, setIsDisputeOpen] = useState(false);
    const [isLinkOpen, setIsLinkOpen] = useState(false);
    const [isCertificateOpen, setIsCertificateOpen] = useState(false);

    // Active records
    const [activeAudit, setActiveAudit] = useState<AuditRecord | null>(null);

    // Forms
    const configForm = useForm({
        qty_tolerance_percent: config.qty_tolerance_percent || 0,
        price_tolerance_percent: config.price_tolerance_percent || 0,
        max_variance_amount_tzs: config.max_variance_amount_tzs || 0,
        require_qc_passed: config.require_qc_passed ?? true,
        auto_hold_on_variance: config.auto_hold_on_variance ?? true,
    });

    const authorizeForm = useForm({
        notes: 'Passed audit review. Commercial terms and delivered quantities verified.',
    });

    const disputeForm = useForm({
        notes: '',
    });

    const linkForm = useForm({
        grn_id: '',
        invoice_id: '',
    });

    // Handlers
    const handleConfigSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        configForm.put(route('procurement.three-way-match.config'), {
            onSuccess: () => setIsConfigOpen(false),
        });
    };

    const openInspectModal = (audit: AuditRecord) => {
        setActiveAudit(audit);
        setIsInspectOpen(true);
    };

    const openAuthorizeModal = (audit: AuditRecord) => {
        setActiveAudit(audit);
        setIsAuthorizeOpen(true);
    };

    const handleAuthorizeSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeAudit?.authorization) return;
        authorizeForm.post(route('procurement.three-way-match.authorize', activeAudit.authorization.id), {
            onSuccess: () => {
                setIsAuthorizeOpen(false);
                setIsInspectOpen(false);
            },
        });
    };

    const openDisputeModal = (audit: AuditRecord) => {
        setActiveAudit(audit);
        disputeForm.setData('notes', audit.tolerance_reasons?.join('\n') || 'Variance detected.');
        setIsDisputeOpen(true);
    };

    const handleDisputeSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeAudit?.authorization) return;
        disputeForm.post(route('procurement.three-way-match.dispute', activeAudit.authorization.id), {
            onSuccess: () => {
                setIsDisputeOpen(false);
                setIsInspectOpen(false);
            },
        });
    };

    const openLinkModal = (audit: AuditRecord) => {
        setActiveAudit(audit);
        linkForm.setData({
            grn_id: String(audit.grn.id),
            invoice_id: '',
        });
        setIsLinkOpen(true);
    };

    const handleLinkSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        linkForm.post(route('procurement.three-way-match.link'), {
            onSuccess: () => setIsLinkOpen(false),
        });
    };

    const openCertificateModal = (audit: AuditRecord) => {
        setActiveAudit(audit);
        setIsCertificateOpen(true);
    };

    // Filtered data
    const filteredAudits = useMemo(() => {
        return audits.filter(a => {
            const grnNum = a.grn.grn_number || '';
            const poNum = a.grn.purchase_order_number || '';
            const invNum = a.grn.purchase_invoice?.invoice_number || '';
            const vendorName = a.grn.vendor?.company_name || '';

            const matchesSearch =
                !searchTerm ||
                grnNum.toLowerCase().includes(searchTerm.toLowerCase()) ||
                poNum.toLowerCase().includes(searchTerm.toLowerCase()) ||
                invNum.toLowerCase().includes(searchTerm.toLowerCase()) ||
                vendorName.toLowerCase().includes(searchTerm.toLowerCase());

            let matchesTab = true;
            if (selectedTab === 'cleared') {
                matchesTab = a.authorization?.disbursement_cleared === true;
            } else if (selectedTab === 'variance_hold') {
                matchesTab = a.match_status === 'variance_hold';
            } else if (selectedTab === 'pending_invoice') {
                matchesTab = a.match_status === 'pending_invoice';
            }

            return matchesSearch && matchesTab;
        });
    }, [audits, searchTerm, selectedTab]);

    return (
        <AuthenticatedLayout>
            <Head title={t("3-Way Matching Audit & AP Reconciliation")} />

            <div className="p-6 space-y-6 max-w-7xl mx-auto">
                {/* Header Banner */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 rounded-xl shadow-lg border border-indigo-900/50">
                    <div className="flex items-center gap-4">
                        <div className="p-3 bg-indigo-600/30 rounded-xl border border-indigo-500/40">
                            <ShieldCheck className="w-8 h-8 text-indigo-400" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-2xl font-bold tracking-tight">
                                    {t("Three-Way Matching Audit & AP Reconciliation")}
                                </h1>
                                <Badge className="bg-indigo-500/20 text-indigo-300 border-indigo-500/30">
                                    SRS Module 8: Section 3.8.6
                                </Badge>
                            </div>
                            <p className="text-sm text-slate-300 mt-1">
                                {t("Automated PO ↔ GRN ↔ Supplier Invoice Variance Detection & Accounts Payable Disbursement Governance")}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            variant="outline"
                            onClick={() => setIsConfigOpen(true)}
                            className="bg-white/10 hover:bg-white/20 text-white border-white/20 gap-2 font-medium"
                        >
                            <Sliders className="w-4 h-4" />
                            {t("Tolerance Rules")} ({config.qty_tolerance_percent}% Qty / {config.price_tolerance_percent}% Price)
                        </Button>
                    </div>
                </div>

                {/* KPI Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Total Triplet Audits")}
                            </CardTitle>
                            <FileText className="w-4 h-4 text-slate-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{kpi.total_audited_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("PO ↔ GRN ↔ Bill records")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Cleared for AP Payment")}
                            </CardTitle>
                            <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">
                                {Number(kpi.total_cleared_disbursement_tzs).toLocaleString()} TZS
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {kpi.cleared_count} {t("disbursements authorized")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Variance Holds (Blocked)")}
                            </CardTitle>
                            <AlertTriangle className="w-4 h-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">{kpi.variance_hold_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("Exceeds tolerance limit")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Pending Supplier Invoice")}
                            </CardTitle>
                            <Clock className="w-4 h-4 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-600">{kpi.pending_invoice_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("Goods received, bill unattached")}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <Input
                        placeholder={t("Search by GRN#, PO#, Invoice#, or Supplier...")}
                        value={searchTerm}
                        onChange={e => setSearchTerm(e.target.value)}
                        className="w-80"
                    />

                    {/* Filter Tabs */}
                    <div className="flex items-center gap-1 bg-muted p-1 rounded-lg border border-border/50 text-xs font-medium">
                        {[
                            { id: 'all', label: t('All Audits') },
                            { id: 'cleared', label: t('Cleared for Payment') },
                            { id: 'variance_hold', label: t('Variance Holds') },
                            { id: 'pending_invoice', label: t('Pending Invoice') },
                        ].map(tab => (
                            <button
                                key={tab.id}
                                onClick={() => setSelectedTab(tab.id)}
                                className={`px-3 py-1.5 rounded-md transition-colors ${
                                    selectedTab === tab.id
                                        ? 'bg-background text-foreground shadow-sm font-semibold'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Audits Table */}
                <Card className="border-border/60 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/40">
                                    <TableHead className="font-semibold">{t("GRN Details")}</TableHead>
                                    <TableHead className="font-semibold">{t("Linked PO & Supplier")}</TableHead>
                                    <TableHead className="font-semibold">{t("Supplier Invoice / Bill")}</TableHead>
                                    <TableHead className="font-semibold">{t("Quality Inspection")}</TableHead>
                                    <TableHead className="font-semibold">{t("3-Way Match Status")}</TableHead>
                                    <TableHead className="font-semibold text-right">{t("Value & Detected Variance")}</TableHead>
                                    <TableHead className="font-semibold text-center">{t("AP Clearance")}</TableHead>
                                    <TableHead className="font-semibold text-right">{t("Actions")}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {filteredAudits.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={8} className="text-center py-10 text-muted-foreground">
                                            <div className="flex flex-col items-center gap-2">
                                                <ShieldCheck className="w-8 h-8 text-muted-foreground/50" />
                                                <p className="font-medium">{t("No Three-Way Match audit records found.")}</p>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    filteredAudits.map(audit => (
                                        <TableRow key={audit.grn.id} className="hover:bg-muted/30 transition-colors">
                                            <TableCell>
                                                <div className="font-mono font-bold text-sm text-indigo-600 dark:text-indigo-400">
                                                    {audit.grn.grn_number}
                                                </div>
                                                <div className="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                                                    <Calendar className="w-3 h-3" />
                                                    {audit.grn.grn_date}
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                <div className="font-semibold text-sm">
                                                    {audit.grn.vendor?.company_name || 'Vendor'}
                                                </div>
                                                <div className="text-xs text-muted-foreground font-mono">
                                                    PO: {audit.grn.purchase_order_number || 'N/A'}
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                {audit.grn.purchase_invoice ? (
                                                    <div>
                                                        <Badge variant="outline" className="font-mono text-xs border-indigo-200 text-indigo-700 bg-indigo-50 dark:bg-indigo-950/20">
                                                            {audit.grn.purchase_invoice.invoice_number}
                                                        </Badge>
                                                        <span className="block text-xs font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                                            {Number(audit.grn.purchase_invoice.total_amount).toLocaleString()} TZS
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => openLinkModal(audit)}
                                                        className="text-amber-600 hover:text-amber-700 p-0 h-auto font-medium text-xs gap-1"
                                                    >
                                                        <LinkIcon className="w-3.5 h-3.5" />
                                                        {t("Attach Bill")}
                                                    </Button>
                                                )}
                                            </TableCell>

                                            <TableCell>
                                                {audit.qc_passed ? (
                                                    <Badge className="bg-emerald-600 text-white text-[11px] gap-1">
                                                        <Check className="w-3 h-3" />
                                                        {t("Passed")}
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline" className="border-red-400 text-red-600 text-[11px]">
                                                        {audit.grn.inspection_status}
                                                    </Badge>
                                                )}
                                            </TableCell>

                                            <TableCell>
                                                {audit.match_status === 'matched_cleared' && (
                                                    <Badge className="bg-emerald-600 hover:bg-emerald-700 text-white gap-1">
                                                        <CheckCircle2 className="w-3.5 h-3.5" />
                                                        {t("Matched & Within Tolerance")}
                                                    </Badge>
                                                )}
                                                {audit.match_status === 'variance_hold' && (
                                                    <Badge className="bg-red-600 hover:bg-red-700 text-white gap-1">
                                                        <AlertTriangle className="w-3.5 h-3.5" />
                                                        {t("Variance Hold (Blocked)")}
                                                    </Badge>
                                                )}
                                                {audit.match_status === 'pending_invoice' && (
                                                    <Badge variant="outline" className="border-amber-400 text-amber-700 bg-amber-50 dark:bg-amber-950/30 gap-1">
                                                        <Clock className="w-3.5 h-3.5" />
                                                        {t("Awaiting Invoice")}
                                                    </Badge>
                                                )}
                                            </TableCell>

                                            <TableCell className="text-right">
                                                <div className="font-bold text-sm">
                                                    {Number(audit.grn.total_amount).toLocaleString()} TZS
                                                </div>
                                                {audit.total_variance_amount_tzs > 0 ? (
                                                    <span className="text-xs font-semibold text-red-600 block">
                                                        Diff: {Number(audit.total_variance_amount_tzs).toLocaleString()} TZS
                                                    </span>
                                                ) : (
                                                    <span className="text-xs text-emerald-600 block">
                                                        0.00 Variance
                                                    </span>
                                                )}
                                            </TableCell>

                                            <TableCell className="text-center">
                                                {audit.authorization?.disbursement_cleared ? (
                                                    <div className="flex flex-col items-center">
                                                        <Badge className="bg-emerald-600 text-white text-[10px] gap-1">
                                                            <Unlock className="w-3 h-3" />
                                                            {t("Payment Cleared")}
                                                        </Badge>
                                                        <span className="text-[10px] text-muted-foreground mt-0.5">
                                                            By: {audit.authorization.cleared_by_user?.name || 'Audit Lead'}
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <Badge variant="destructive" className="text-[10px] gap-1">
                                                        <Lock className="w-3 h-3" />
                                                        {t("Payment Hold")}
                                                    </Badge>
                                                )}
                                            </TableCell>

                                            <TableCell className="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                            <MoreVertical className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" className="w-56">
                                                        <DropdownMenuItem onClick={() => openInspectModal(audit)}>
                                                            <ShieldCheck className="w-4 h-4 mr-2 text-indigo-500" />
                                                            {t("Inspect 3-Way Matrix")}
                                                        </DropdownMenuItem>

                                                        {!audit.authorization?.disbursement_cleared && (
                                                            <DropdownMenuItem onClick={() => openAuthorizeModal(audit)}>
                                                                <Unlock className="w-4 h-4 mr-2 text-emerald-500" />
                                                                {t("Authorize AP Disbursement")}
                                                            </DropdownMenuItem>
                                                        )}

                                                        {audit.match_status === 'variance_hold' && (
                                                            <DropdownMenuItem onClick={() => openDisputeModal(audit)}>
                                                                <AlertTriangle className="w-4 h-4 mr-2 text-red-500" />
                                                                {t("Flag Commercial Dispute")}
                                                            </DropdownMenuItem>
                                                        )}

                                                        {!audit.has_invoice && (
                                                            <DropdownMenuItem onClick={() => openLinkModal(audit)}>
                                                                <LinkIcon className="w-4 h-4 mr-2 text-amber-500" />
                                                                {t("Link Purchase Bill")}
                                                            </DropdownMenuItem>
                                                        )}

                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem onClick={() => openCertificateModal(audit)}>
                                                            <Printer className="w-4 h-4 mr-2 text-slate-500" />
                                                            {t("Reconciliation Certificate")}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </Card>
            </div>

            {/* MODAL 1: Configure Tolerance Rules */}
            <Dialog open={isConfigOpen} onOpenChange={setIsConfigOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Sliders className="w-5 h-5 text-indigo-600" />
                            {t("Three-Way Matching Tolerance Rules")}
                        </DialogTitle>
                        <p className="text-xs text-muted-foreground">
                            {t("Define allowable quantity and price variance thresholds before triggering AP payment holds.")}
                        </p>
                    </DialogHeader>

                    <form onSubmit={handleConfigSubmit} className="space-y-4 pt-2">
                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Allowed Quantity Variance (%)")}</Label>
                            <Input
                                type="number"
                                min="0"
                                max="100"
                                step="0.1"
                                required
                                value={configForm.data.qty_tolerance_percent}
                                onChange={e => configForm.setData('qty_tolerance_percent', Number(e.target.value))}
                            />
                            <p className="text-[11px] text-muted-foreground">{t("e.g. 2% allows delivered quantity to differ up to 2% from billed.")}</p>
                        </div>

                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Allowed Unit Price Variance (%)")}</Label>
                            <Input
                                type="number"
                                min="0"
                                max="100"
                                step="0.1"
                                required
                                value={configForm.data.price_tolerance_percent}
                                onChange={e => configForm.setData('price_tolerance_percent', Number(e.target.value))}
                            />
                            <p className="text-[11px] text-muted-foreground">{t("e.g. 0% strictly enforces identical PO unit price on the invoice.")}</p>
                        </div>

                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Maximum Absolute Variance Amount (TZS)")}</Label>
                            <Input
                                type="number"
                                min="0"
                                required
                                value={configForm.data.max_variance_amount_tzs}
                                onChange={e => configForm.setData('max_variance_amount_tzs', Number(e.target.value))}
                            />
                        </div>

                        <div className="pt-2 border-t space-y-2">
                            <div className="flex items-center justify-between">
                                <Label className="text-xs font-semibold">{t("Require Quality Inspection Passed")}</Label>
                                <input
                                    type="checkbox"
                                    checked={configForm.data.require_qc_passed}
                                    onChange={e => configForm.setData('require_qc_passed', e.target.checked)}
                                    className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <Label className="text-xs font-semibold">{t("Auto-Hold on Variance Detection")}</Label>
                                <input
                                    type="checkbox"
                                    checked={configForm.data.auto_hold_on_variance}
                                    onChange={e => configForm.setData('auto_hold_on_variance', e.target.checked)}
                                    className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                            </div>
                        </div>

                        <DialogFooter className="pt-3 border-t">
                            <Button type="button" variant="outline" onClick={() => setIsConfigOpen(false)}>
                                {t("Cancel")}
                            </Button>
                            <Button type="submit" disabled={configForm.processing} className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">
                                {t("Save Tolerance Rules")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 2: Line Item Audit Matrix Inspection */}
            <Dialog open={isInspectOpen} onOpenChange={setIsInspectOpen}>
                <DialogContent className="max-w-4xl max-h-[85vh] overflow-y-auto">
                    <DialogHeader>
                        <div className="flex items-center justify-between">
                            <DialogTitle className="flex items-center gap-2 text-xl font-bold">
                                <ShieldCheck className="w-5 h-5 text-indigo-600" />
                                {t("PO ↔ GRN ↔ Supplier Invoice Line-by-Line Match Matrix")}
                            </DialogTitle>
                            <Badge variant="outline" className="font-mono text-xs">
                                {activeAudit?.grn.grn_number}
                            </Badge>
                        </div>
                    </DialogHeader>

                    {activeAudit && (
                        <div className="space-y-4 pt-2">
                            {/* Summary triplet card */}
                            <div className="grid grid-cols-3 gap-3 p-3 bg-muted/40 rounded-lg border text-xs">
                                <div>
                                    <span className="text-muted-foreground block">Purchase Order (PO):</span>
                                    <span className="font-bold text-sm text-foreground">{activeAudit.grn.purchase_order_number || 'N/A'}</span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground block">Goods Received Note (GRN):</span>
                                    <span className="font-bold text-sm text-indigo-600 dark:text-indigo-400">{activeAudit.grn.grn_number}</span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground block">Supplier Invoice / Bill:</span>
                                    <span className="font-bold text-sm text-emerald-600 dark:text-emerald-400">
                                        {activeAudit.grn.purchase_invoice?.invoice_number || 'Unattached'}
                                    </span>
                                </div>
                            </div>

                            {/* Variance Warnings */}
                            {activeAudit.tolerance_reasons?.length > 0 && (
                                <div className="p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg text-xs space-y-1 text-red-700 dark:text-red-300">
                                    <div className="font-bold flex items-center gap-1.5">
                                        <AlertTriangle className="w-4 h-4 text-red-600" />
                                        {t("Variance Tolerance Alerts Detected:")}
                                    </div>
                                    <ul className="list-disc list-inside space-y-0.5">
                                        {activeAudit.tolerance_reasons.map((r, i) => (
                                            <li key={i}>{r}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {/* Line Items Table */}
                            <div className="rounded-lg border overflow-x-auto text-xs">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/40">
                                            <TableHead className="font-semibold">{t("Item & SKU")}</TableHead>
                                            <TableHead className="font-semibold text-right">{t("PO Qty")}</TableHead>
                                            <TableHead className="font-semibold text-right">{t("GRN Accepted")}</TableHead>
                                            <TableHead className="font-semibold text-right">{t("Billed Qty")}</TableHead>
                                            <TableHead className="font-semibold text-right">{t("PO Price")}</TableHead>
                                            <TableHead className="font-semibold text-right">{t("Billed Price")}</TableHead>
                                            <TableHead className="font-semibold text-right">{t("Variance (TZS)")}</TableHead>
                                            <TableHead className="font-semibold text-center">{t("Status")}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {activeAudit.items.map(it => (
                                            <TableRow key={it.id}>
                                                <TableCell>
                                                    <span className="font-semibold">{it.product_name}</span>
                                                    <span className="text-muted-foreground block text-[11px]">{it.sku}</span>
                                                </TableCell>
                                                <TableCell className="text-right font-medium">{it.po_qty}</TableCell>
                                                <TableCell className="text-right font-bold text-indigo-600">{it.grn_qty}</TableCell>
                                                <TableCell className="text-right font-bold text-emerald-600">{it.billed_qty}</TableCell>
                                                <TableCell className="text-right">{Number(it.po_price).toLocaleString()}</TableCell>
                                                <TableCell className="text-right">{Number(it.billed_price).toLocaleString()}</TableCell>
                                                <TableCell className="text-right font-bold text-red-600">
                                                    {it.amount_variance > 0 ? Number(it.amount_variance).toLocaleString() : '0.00'}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    {it.is_matched ? (
                                                        <Badge className="bg-emerald-600 text-white text-[10px] gap-1">
                                                            <Check className="w-3 h-3" />
                                                            {t("Match")}
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="destructive" className="text-[10px] gap-1">
                                                            <X className="w-3 h-3" />
                                                            {t("Variance")}
                                                        </Badge>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            <DialogFooter className="pt-3 border-t flex justify-between">
                                <Button variant="outline" onClick={() => setIsInspectOpen(false)}>
                                    {t("Close")}
                                </Button>
                                {!activeAudit.authorization?.disbursement_cleared && (
                                    <Button
                                        onClick={() => openAuthorizeModal(activeAudit)}
                                        className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold gap-1.5"
                                    >
                                        <Unlock className="w-4 h-4" />
                                        {t("Authorize AP Disbursement")}
                                    </Button>
                                )}
                            </DialogFooter>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            {/* MODAL 3: Authorize AP Disbursement */}
            <Dialog open={isAuthorizeOpen} onOpenChange={setIsAuthorizeOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Unlock className="w-5 h-5 text-emerald-600" />
                            {t("Authorize Accounts Payable Disbursement")}
                        </DialogTitle>
                        <p className="text-xs text-muted-foreground">
                            {t("Release payment hold on")} {activeAudit?.grn.grn_number} ({Number(activeAudit?.grn.total_amount).toLocaleString()} TZS).
                        </p>
                    </DialogHeader>

                    <form onSubmit={handleAuthorizeSubmit} className="space-y-4 pt-2">
                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Audit Approval Sign-Off Notes *")}</Label>
                            <Textarea
                                rows={3}
                                required
                                value={authorizeForm.data.notes}
                                onChange={e => authorizeForm.setData('notes', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-3 border-t">
                            <Button type="button" variant="outline" onClick={() => setIsAuthorizeOpen(false)}>
                                {t("Cancel")}
                            </Button>
                            <Button type="submit" disabled={authorizeForm.processing} className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                                {t("Clear Payment & Release Hold")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 4: Flag Dispute */}
            <Dialog open={isDisputeOpen} onOpenChange={setIsDisputeOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <AlertTriangle className="w-5 h-5 text-red-600" />
                            {t("Flag Commercial Dispute & Block Payment")}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleDisputeSubmit} className="space-y-4 pt-2">
                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Reason for Dispute / Overbilling Notice *")}</Label>
                            <Textarea
                                rows={3}
                                required
                                value={disputeForm.data.notes}
                                onChange={e => disputeForm.setData('notes', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-3 border-t">
                            <Button type="button" variant="outline" onClick={() => setIsDisputeOpen(false)}>
                                {t("Cancel")}
                            </Button>
                            <Button type="submit" disabled={disputeForm.processing} variant="destructive">
                                {t("Enforce AP Payment Block")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 5: Link Supplier Invoice */}
            <Dialog open={isLinkOpen} onOpenChange={setIsLinkOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <LinkIcon className="w-5 h-5 text-indigo-600" />
                            {t("Link Supplier Bill to")} {activeAudit?.grn.grn_number}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleLinkSubmit} className="space-y-4 pt-2">
                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Select Unattached Purchase Invoice *")}</Label>
                            <Select
                                value={linkForm.data.invoice_id}
                                onValueChange={val => linkForm.setData('invoice_id', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t("-- Select Purchase Invoice --")} />
                                </SelectTrigger>
                                <SelectContent>
                                    {available_invoices.map(inv => (
                                        <SelectItem key={inv.id} value={String(inv.id)}>
                                            {inv.invoice_number} — {Number(inv.total_amount).toLocaleString()} TZS ({inv.invoice_date})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <DialogFooter className="pt-3 border-t">
                            <Button type="button" variant="outline" onClick={() => setIsLinkOpen(false)}>
                                {t("Cancel")}
                            </Button>
                            <Button type="submit" disabled={linkForm.processing || !linkForm.data.invoice_id}>
                                {t("Link & Re-Audit Match")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 6: Printable Reconciliation Certificate */}
            <Dialog open={isCertificateOpen} onOpenChange={setIsCertificateOpen}>
                <DialogContent className="max-w-2xl max-h-[85vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Printer className="w-5 h-5 text-indigo-600" />
                            {t("Audit Reconciliation Clearance Certificate")}
                        </DialogTitle>
                    </DialogHeader>

                    {activeAudit && (
                        <div className="p-4 border rounded-xl bg-white text-slate-900 shadow-sm space-y-4">
                            <div className="border-b pb-3 flex justify-between items-start">
                                <div>
                                    <h2 className="text-lg font-extrabold text-slate-900">MUTAMARK ENTERPRISES LTD</h2>
                                    <p className="text-xs text-slate-500">Internal Audit & Accounts Payable Governance</p>
                                </div>
                                <div className="text-right">
                                    <span className="text-[11px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">
                                        3-WAY MATCH CERTIFICATE
                                    </span>
                                    <p className="text-xs font-mono font-bold mt-1">REF: 3WAY-{activeAudit.grn.grn_number}</p>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3 text-xs p-3 bg-slate-50 rounded-lg border">
                                <div>
                                    <span className="text-slate-500 block">Goods Received Note:</span>
                                    <span className="font-bold">{activeAudit.grn.grn_number}</span>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">Purchase Order:</span>
                                    <span className="font-bold">{activeAudit.grn.purchase_order_number || 'N/A'}</span>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">Supplier Bill / Invoice:</span>
                                    <span className="font-bold">{activeAudit.grn.purchase_invoice?.invoice_number || 'N/A'}</span>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">Vendor:</span>
                                    <span className="font-bold">{activeAudit.grn.vendor?.company_name || 'Vendor'}</span>
                                </div>
                            </div>

                            <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs space-y-1 text-emerald-900">
                                <div className="font-bold flex items-center gap-1.5">
                                    <ShieldCheck className="w-4 h-4 text-emerald-600" />
                                    {t("Accounts Payable Audit Clearance Certified")}
                                </div>
                                <p>
                                    All delivered materials and itemized billed unit prices have undergone systematic reconciliation with the authorized purchase contract. Billed amount of <strong>{Number(activeAudit.grn.total_amount).toLocaleString()} TZS</strong> is hereby cleared for commercial bank transfer / mobile money disbursement.
                                </p>
                            </div>

                            <div className="pt-4 border-t flex justify-between items-center text-xs text-slate-500">
                                <div>
                                    <p className="font-semibold text-slate-800">Financial Audit Controller</p>
                                    <p>Authorized Internal Audit Officer</p>
                                </div>
                                <div className="text-right font-mono text-[10px]">
                                    <ShieldCheck className="w-4 h-4 text-emerald-600 inline mr-1" />
                                    <span>HASH: {activeAudit.authorization?.verification_hash ? activeAudit.authorization.verification_hash.substring(0, 16) + '...' : 'CLEARED-VERIFIED'}</span>
                                </div>
                            </div>

                            <DialogFooter className="pt-3 border-t">
                                <Button variant="outline" onClick={() => setIsCertificateOpen(false)}>
                                    {t("Close")}
                                </Button>
                                <Button onClick={() => window.print()} className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold gap-1">
                                    <Printer className="w-4 h-4" />
                                    {t("Print Certificate")}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
