import React, { useState, useMemo } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import CorporateDocumentTemplate, { DocumentKind } from '@/components/CorporateDocumentTemplate';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    FileText,
    Plus,
    Palette,
    Eye,
    Star,
    Check,
    Sliders,
    Printer,
    Edit3,
    Trash2,
    ShieldCheck,
    Building2,
    QrCode,
    Sparkles,
    CheckCircle2,
    FileCheck,
    Layers,
    DollarSign,
    Briefcase,
    FileSpreadsheet
} from 'lucide-react';

interface DocumentTemplateRecord {
    id: number;
    template_key: string;
    name: string;
    document_type: string;
    document_type_label: string;
    header_title: string;
    header_subtitle?: string | null;
    header_text?: string | null;
    footer_text?: string | null;
    terms_and_conditions?: string | null;
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    font_family: string;
    paper_size: string;
    orientation: string;
    show_company_logo: boolean;
    show_qr_code: boolean;
    show_digital_seal: boolean;
    show_signature_block: boolean;
    show_bank_details: boolean;
    show_tax_breakdown: boolean;
    show_amount_in_words: boolean;
    watermark_text?: string | null;
    is_default: boolean;
}

interface PageProps {
    templates: DocumentTemplateRecord[];
    kpi: {
        total_templates: number;
        sales_count: number;
        procurement_count: number;
        financial_count: number;
    };
    filters?: {
        search?: string;
        document_type?: string;
    };
}

const DOCUMENT_TYPES = [
    { value: 'sales_invoice', label: 'Tax Invoice (INV)', category: 'sales' },
    { value: 'quotation', label: 'Sales Quotation (QT)', category: 'sales' },
    { value: 'proforma_invoice', label: 'Proforma Invoice (PI)', category: 'sales' },
    { value: 'sales_order', label: 'Sales Order (SO)', category: 'sales' },
    { value: 'delivery_note', label: 'Delivery Note (DN)', category: 'sales' },
    { value: 'receipt_voucher', label: 'Payment Receipt Voucher (RV)', category: 'sales' },
    { value: 'purchase_requisition', label: 'Purchase Requisition (PR)', category: 'procurement' },
    { value: 'rfq', label: 'Request for Quotation (RFQ)', category: 'procurement' },
    { value: 'comparative_statement', label: 'Comparative Evaluation Matrix', category: 'procurement' },
    { value: 'purchase_order', label: 'Purchase Order (PO)', category: 'procurement' },
    { value: 'goods_received_note', label: 'Goods Received Note (GRN)', category: 'procurement' },
    { value: 'credit_note', label: 'Credit Note (CN)', category: 'financial' },
    { value: 'debit_note', label: 'Debit Note (DN)', category: 'financial' },
    { value: 'payslip', label: 'Employee Salary Payslip', category: 'financial' },
    { value: 'contract', label: 'Commercial Contract', category: 'financial' },
];

export default function DocumentTemplatesIndex({
    templates = [],
    kpi,
    filters = {},
}: PageProps) {
    const { t } = useTranslation();

    // Filters
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedCategoryTab, setSelectedCategoryTab] = useState('all');

    // Modals
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);

    // Active records
    const [activeTemplate, setActiveTemplate] = useState<DocumentTemplateRecord | null>(null);
    const [templateToDelete, setTemplateToDelete] = useState<DocumentTemplateRecord | null>(null);

    // Form
    const templateForm = useForm({
        name: '',
        document_type: 'sales_invoice',
        header_title: 'TAX INVOICE',
        header_subtitle: 'Commercial VAT Invoice',
        header_text: '',
        footer_text: 'Thank you for your business. For any questions, contact accounts@mutamark.co.tz',
        terms_and_conditions: 'Payment due within 30 days. Late payments subject to 2% monthly interest.',
        primary_color: '#4f46e5',
        secondary_color: '#0f172a',
        accent_color: '#10b981',
        font_family: 'Inter',
        paper_size: 'A4',
        orientation: 'portrait',
        show_company_logo: true,
        show_qr_code: true,
        show_digital_seal: true,
        show_signature_block: true,
        show_bank_details: true,
        show_tax_breakdown: true,
        show_amount_in_words: true,
        watermark_text: '',
        is_default: false,
    });

    // Handlers
    const openCreateModal = () => {
        templateForm.reset();
        setIsCreateOpen(true);
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        templateForm.post(route('paperless.templates.store'), {
            onSuccess: () => {
                setIsCreateOpen(false);
                templateForm.reset();
            },
        });
    };

    const openEditModal = (tpl: DocumentTemplateRecord) => {
        setActiveTemplate(tpl);
        templateForm.setData({
            name: tpl.name,
            document_type: tpl.document_type,
            header_title: tpl.header_title,
            header_subtitle: tpl.header_subtitle || '',
            header_text: tpl.header_text || '',
            footer_text: tpl.footer_text || '',
            terms_and_conditions: tpl.terms_and_conditions || '',
            primary_color: tpl.primary_color,
            secondary_color: tpl.secondary_color,
            accent_color: tpl.accent_color,
            font_family: tpl.font_family,
            paper_size: tpl.paper_size,
            orientation: tpl.orientation,
            show_company_logo: tpl.show_company_logo,
            show_qr_code: tpl.show_qr_code,
            show_digital_seal: tpl.show_digital_seal,
            show_signature_block: tpl.show_signature_block,
            show_bank_details: tpl.show_bank_details,
            show_tax_breakdown: tpl.show_tax_breakdown,
            show_amount_in_words: tpl.show_amount_in_words,
            watermark_text: tpl.watermark_text || '',
            is_default: tpl.is_default,
        });
        setIsEditOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeTemplate) return;
        templateForm.put(route('paperless.templates.update', activeTemplate.id), {
            onSuccess: () => {
                setIsEditOpen(false);
                setActiveTemplate(null);
            },
        });
    };

    const handleSetDefault = (tpl: DocumentTemplateRecord) => {
        router.post(route('paperless.templates.set-default', tpl.id));
    };

    const openPreviewModal = (tpl: DocumentTemplateRecord) => {
        setActiveTemplate(tpl);
        setIsPreviewOpen(true);
    };

    const openDeleteModal = (tpl: DocumentTemplateRecord) => {
        setTemplateToDelete(tpl);
        setIsDeleteOpen(true);
    };

    const confirmDelete = () => {
        if (!templateToDelete) return;
        router.delete(route('paperless.templates.destroy', templateToDelete.id), {
            onSuccess: () => {
                setIsDeleteOpen(false);
                setTemplateToDelete(null);
            },
        });
    };

    // Filtered templates
    const filteredTemplates = useMemo(() => {
        return templates.filter(t => {
            const matchesSearch =
                !searchTerm ||
                t.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                t.document_type_label.toLowerCase().includes(searchTerm.toLowerCase()) ||
                t.header_title.toLowerCase().includes(searchTerm.toLowerCase());

            const typeConfig = DOCUMENT_TYPES.find(d => d.value === t.document_type);
            const category = typeConfig ? typeConfig.category : 'other';

            let matchesCategory = true;
            if (selectedCategoryTab === 'sales') {
                matchesCategory = category === 'sales';
            } else if (selectedCategoryTab === 'procurement') {
                matchesCategory = category === 'procurement';
            } else if (selectedCategoryTab === 'financial') {
                matchesCategory = category === 'financial';
            }

            return matchesSearch && matchesCategory;
        });
    }, [templates, searchTerm, selectedCategoryTab]);

    return (
        <AuthenticatedLayout>
            <Head title={t("Paperless Office Dynamic Document Template Builder")} />

            <div className="p-6 space-y-6 max-w-7xl mx-auto">
                {/* Header Banner */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 rounded-xl shadow-lg border border-indigo-900/50">
                    <div className="flex items-center gap-4">
                        <div className="p-3 bg-indigo-600/30 rounded-xl border border-indigo-500/40">
                            <Palette className="w-8 h-8 text-indigo-400" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-2xl font-bold tracking-tight">
                                    {t("Paperless Office Dynamic Document Template Builder")}
                                </h1>
                                <Badge className="bg-indigo-500/20 text-indigo-300 border-indigo-500/30">
                                    SRS Module 25: Paperless Office
                                </Badge>
                            </div>
                            <p className="text-sm text-slate-300 mt-1">
                                {t("Visual Template Designer for Quotations, Invoices, Delivery Notes, PRs, RFQs, POs, and Receipts")}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            onClick={openCreateModal}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-md font-semibold gap-2"
                        >
                            <Plus className="w-4 h-4" />
                            {t("New Custom Template")}
                        </Button>
                    </div>
                </div>

                {/* KPI Overview Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Total Active Templates")}
                            </CardTitle>
                            <Layers className="w-4 h-4 text-slate-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{kpi.total_templates}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("Configured across system")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Sales & Quote-to-Cash")}
                            </CardTitle>
                            <DollarSign className="w-4 h-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{kpi.sales_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("QT, PI, SO, DN, INV, RV")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Procure-to-Pay Templates")}
                            </CardTitle>
                            <Briefcase className="w-4 h-4 text-indigo-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-indigo-600">{kpi.procurement_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("PR, RFQ, Matrix, PO, GRN")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Financial Vouchers & HR")}
                            </CardTitle>
                            <FileSpreadsheet className="w-4 h-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">{kpi.financial_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("CN, DN, Payslips, Contracts")}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <Input
                        placeholder={t("Search by template name or document type...")}
                        value={searchTerm}
                        onChange={e => setSearchTerm(e.target.value)}
                        className="w-80"
                    />

                    <div className="flex items-center gap-1 bg-muted p-1 rounded-lg border border-border/50 text-xs font-medium">
                        {[
                            { id: 'all', label: t('All Templates') },
                            { id: 'sales', label: t('Sales & Invoicing') },
                            { id: 'procurement', label: t('Procurement & WMS') },
                            { id: 'financial', label: t('Finance & HR') },
                        ].map(tab => (
                            <button
                                key={tab.id}
                                onClick={() => setSelectedCategoryTab(tab.id)}
                                className={`px-3 py-1.5 rounded-md transition-colors ${
                                    selectedCategoryTab === tab.id
                                        ? 'bg-background text-foreground shadow-sm font-semibold'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Templates Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    {filteredTemplates.map(tpl => (
                        <Card
                            key={tpl.id}
                            className="overflow-hidden border-border/70 hover:shadow-lg transition-all flex flex-col justify-between"
                        >
                            <div>
                                {/* Primary Color Accent Header Bar */}
                                <div
                                    className="h-3 w-full"
                                    style={{ backgroundColor: tpl.primary_color }}
                                />

                                <CardHeader className="pb-3">
                                    <div className="flex items-start justify-between gap-2">
                                        <div>
                                            <Badge variant="outline" className="text-[10px] uppercase font-mono mb-1">
                                                {tpl.document_type_label}
                                            </Badge>
                                            <CardTitle className="text-base font-bold text-foreground">
                                                {tpl.name}
                                            </CardTitle>
                                        </div>

                                        {tpl.is_default ? (
                                            <Badge className="bg-amber-500 text-white text-[10px] gap-1 shrink-0">
                                                <Star className="w-3 h-3 fill-current" />
                                                {t("Default")}
                                            </Badge>
                                        ) : (
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => handleSetDefault(tpl)}
                                                className="text-xs text-muted-foreground hover:text-amber-600 h-6 px-1.5"
                                            >
                                                {t("Set Default")}
                                            </Button>
                                        )}
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-3 text-xs">
                                    <div className="p-2.5 rounded-lg bg-muted/40 border border-border/50">
                                        <span className="text-muted-foreground block text-[10px] uppercase font-semibold">
                                            {t("Header Title & Subtitle")}
                                        </span>
                                        <div
                                            className="font-extrabold text-sm tracking-wide mt-0.5"
                                            style={{ color: tpl.primary_color }}
                                        >
                                            {tpl.header_title}
                                        </div>
                                        {tpl.header_subtitle && (
                                            <div className="text-muted-foreground text-[11px]">
                                                {tpl.header_subtitle}
                                            </div>
                                        )}
                                    </div>

                                    {/* Palette & Badges */}
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-1.5">
                                            <span className="text-[11px] text-muted-foreground mr-1">{t("Colors")}:</span>
                                            <span
                                                className="w-4 h-4 rounded-full border shadow-sm inline-block"
                                                style={{ backgroundColor: tpl.primary_color }}
                                                title={`Primary: ${tpl.primary_color}`}
                                            />
                                            <span
                                                className="w-4 h-4 rounded-full border shadow-sm inline-block"
                                                style={{ backgroundColor: tpl.secondary_color }}
                                                title={`Secondary: ${tpl.secondary_color}`}
                                            />
                                            <span
                                                className="w-4 h-4 rounded-full border shadow-sm inline-block"
                                                style={{ backgroundColor: tpl.accent_color }}
                                                title={`Accent: ${tpl.accent_color}`}
                                            />
                                        </div>

                                        <span className="text-[11px] font-mono text-muted-foreground">
                                            {tpl.paper_size} • {tpl.orientation}
                                        </span>
                                    </div>

                                    {/* Feature Badges */}
                                    <div className="flex flex-wrap gap-1 pt-1">
                                        {tpl.show_qr_code && (
                                            <Badge variant="secondary" className="text-[10px] gap-1">
                                                <QrCode className="w-2.5 h-2.5" /> QR Seal
                                            </Badge>
                                        )}
                                        {tpl.show_digital_seal && (
                                            <Badge variant="secondary" className="text-[10px] gap-1">
                                                <ShieldCheck className="w-2.5 h-2.5 text-emerald-600" /> Digital Hash
                                            </Badge>
                                        )}
                                        {tpl.show_bank_details && (
                                            <Badge variant="secondary" className="text-[10px]">
                                                Bank Details
                                            </Badge>
                                        )}
                                        {tpl.show_signature_block && (
                                            <Badge variant="secondary" className="text-[10px]">
                                                Signatures
                                            </Badge>
                                        )}
                                    </div>
                                </CardContent>
                            </div>

                            {/* Footer Actions */}
                            <div className="p-4 pt-2 border-t border-border/50 flex items-center justify-between gap-2 bg-muted/20">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => openPreviewModal(tpl)}
                                    className="gap-1.5 text-xs font-semibold"
                                >
                                    <Eye className="w-3.5 h-3.5" />
                                    {t("Live Preview")}
                                </Button>

                                <div className="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openEditModal(tpl)}
                                        className="h-8 px-2 text-blue-600 hover:text-blue-700"
                                    >
                                        <Edit3 className="w-3.5 h-3.5" />
                                    </Button>

                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteModal(tpl)}
                                        className="h-8 px-2 text-red-500 hover:text-red-700"
                                    >
                                        <Trash2 className="w-3.5 h-3.5" />
                                    </Button>
                                </div>
                            </div>
                        </Card>
                    ))}
                </div>
            </div>

            {/* MODAL 1: Create / Edit Template */}
            <Dialog
                open={isCreateOpen || isEditOpen}
                onOpenChange={open => {
                    if (!open) {
                        setIsCreateOpen(false);
                        setIsEditOpen(false);
                    }
                }}
            >
                <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-xl font-bold">
                            <Palette className="w-5 h-5 text-indigo-600" />
                            {isEditOpen ? t("Edit Document Template") : t("Create Custom Document Template")}
                        </DialogTitle>
                        <p className="text-xs text-muted-foreground">
                            {t("Customize dynamic typography, colors, terms, and digital trust blocks.")}
                        </p>
                    </DialogHeader>

                    <form onSubmit={isEditOpen ? handleEditSubmit : handleCreateSubmit} className="space-y-4 pt-2">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Template Name *")}</Label>
                                <Input
                                    required
                                    placeholder={t("e.g. Modern Executive Invoice")}
                                    value={templateForm.data.name}
                                    onChange={e => templateForm.setData('name', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Document Type *")}</Label>
                                <Select
                                    disabled={isEditOpen}
                                    value={templateForm.data.document_type}
                                    onValueChange={val => {
                                        templateForm.setData('document_type', val);
                                        const found = DOCUMENT_TYPES.find(d => d.value === val);
                                        if (found) {
                                            templateForm.setData('header_title', found.label.toUpperCase());
                                        }
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t("Select Type")} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {DOCUMENT_TYPES.map(dt => (
                                            <SelectItem key={dt.value} value={dt.value}>
                                                {dt.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Header Title & Subtitle */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Document Header Title *")}</Label>
                                <Input
                                    required
                                    placeholder="TAX INVOICE"
                                    value={templateForm.data.header_title}
                                    onChange={e => templateForm.setData('header_title', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Header Subtitle")}</Label>
                                <Input
                                    placeholder="Commercial VAT Document"
                                    value={templateForm.data.header_subtitle}
                                    onChange={e => templateForm.setData('header_subtitle', e.target.value)}
                                />
                            </div>
                        </div>

                        {/* Colors */}
                        <div className="grid grid-cols-3 gap-3 p-3 bg-muted/40 rounded-lg border">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Primary Color")}</Label>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="color"
                                        value={templateForm.data.primary_color}
                                        onChange={e => templateForm.setData('primary_color', e.target.value)}
                                        className="h-8 w-12 rounded cursor-pointer border"
                                    />
                                    <span className="text-xs font-mono">{templateForm.data.primary_color}</span>
                                </div>
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Secondary Color")}</Label>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="color"
                                        value={templateForm.data.secondary_color}
                                        onChange={e => templateForm.setData('secondary_color', e.target.value)}
                                        className="h-8 w-12 rounded cursor-pointer border"
                                    />
                                    <span className="text-xs font-mono">{templateForm.data.secondary_color}</span>
                                </div>
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Accent Color")}</Label>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="color"
                                        value={templateForm.data.accent_color}
                                        onChange={e => templateForm.setData('accent_color', e.target.value)}
                                        className="h-8 w-12 rounded cursor-pointer border"
                                    />
                                    <span className="text-xs font-mono">{templateForm.data.accent_color}</span>
                                </div>
                            </div>
                        </div>

                        {/* Paper & Orientation */}
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Paper Size")}</Label>
                                <Select
                                    value={templateForm.data.paper_size}
                                    onValueChange={val => templateForm.setData('paper_size', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="A4">A4 (Standard)</SelectItem>
                                        <SelectItem value="Letter">US Letter</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Orientation")}</Label>
                                <Select
                                    value={templateForm.data.orientation}
                                    onValueChange={val => templateForm.setData('orientation', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="portrait">Portrait</SelectItem>
                                        <SelectItem value="landscape">Landscape</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Feature Toggles */}
                        <div className="space-y-2 pt-2 border-t">
                            <Label className="text-xs font-bold text-foreground">
                                {t("Display Elements & Digital Trust Seals")}
                            </Label>
                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                                {[
                                    { key: 'show_company_logo', label: 'Company Logo' },
                                    { key: 'show_qr_code', label: 'QR Verification Seal' },
                                    { key: 'show_digital_seal', label: 'Cryptographic Hash' },
                                    { key: 'show_signature_block', label: 'Signature Blocks' },
                                    { key: 'show_bank_details', label: 'Bank Account Details' },
                                    { key: 'show_tax_breakdown', label: 'VAT 18% Breakdown' },
                                    { key: 'show_amount_in_words', label: 'Amount in Words' },
                                    { key: 'is_default', label: 'Set as Default Template' },
                                ].map(toggle => (
                                    <label
                                        key={toggle.key}
                                        className="flex items-center gap-2 p-2 rounded bg-muted/40 border border-border/50 cursor-pointer hover:bg-muted/70"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={Boolean((templateForm.data as any)[toggle.key])}
                                            onChange={e => templateForm.setData(toggle.key as any, e.target.checked)}
                                            className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <span className="font-medium">{toggle.label}</span>
                                    </label>
                                ))}
                            </div>
                        </div>

                        {/* Terms & Footer */}
                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Terms & Conditions Text")}</Label>
                            <Textarea
                                rows={2}
                                value={templateForm.data.terms_and_conditions}
                                onChange={e => templateForm.setData('terms_and_conditions', e.target.value)}
                            />
                        </div>

                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Footer Note")}</Label>
                            <Input
                                value={templateForm.data.footer_text}
                                onChange={e => templateForm.setData('footer_text', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-3 border-t">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    setIsCreateOpen(false);
                                    setIsEditOpen(false);
                                }}
                            >
                                {t("Cancel")}
                            </Button>
                            <Button
                                type="submit"
                                disabled={templateForm.processing}
                                className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                            >
                                {isEditOpen ? t("Save Changes") : t("Create Template")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 2: Live Interactive Document Preview */}
            <Dialog open={isPreviewOpen} onOpenChange={setIsPreviewOpen}>
                <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Eye className="w-5 h-5 text-indigo-600" />
                            {t("Dynamic Document Preview")} — {activeTemplate?.name}
                        </DialogTitle>
                    </DialogHeader>

                    {activeTemplate && (() => {
                        const kind: DocumentKind =
                            activeTemplate.document_type === 'purchase_requisition' ? 'purchase_requisition' :
                            activeTemplate.document_type === 'rfq' ? 'rfq' :
                            activeTemplate.document_type === 'purchase_order' ? 'purchase_order' :
                            activeTemplate.document_type === 'delivery_note' ? 'delivery_note' :
                            activeTemplate.document_type === 'goods_received_note' ? 'goods_received_note' : 'invoice';

                        const sampleItems = [
                            { id: 1, name: 'Industrial Heavy Duty Rubber Conveyor Belting', sku: 'BLT-CONV-800', quantity: 2, unit: 'rolls', unit_price: 1050000, total_amount: 2100000, qty_ordered: 2, qty_delivered: 2, ordered_quantity: 2, received_quantity: 2, accepted_quantity: 2, rejected_quantity: 0 },
                            { id: 2, name: 'Heavy Duty Steel Idler Roller Assemblies (100mm)', sku: 'IDL-STL-100', quantity: 10, unit: 'units', unit_price: 75000, total_amount: 750000, qty_ordered: 10, qty_delivered: 10, ordered_quantity: 10, received_quantity: 10, accepted_quantity: 10, rejected_quantity: 0 },
                            { id: 3, name: 'High-Temperature Synthetic Machine Grease ISO VG 220', sku: 'LUB-SYN-220', quantity: 5, unit: 'drums', unit_price: 320000, total_amount: 1600000, qty_ordered: 5, qty_delivered: 5, ordered_quantity: 5, received_quantity: 5, accepted_quantity: 5, rejected_quantity: 0 },
                        ];

                        return (
                            <div className="space-y-4">
                                {kind === 'purchase_requisition' ? (
                                    <CorporateDocumentTemplate
                                        type="purchase_requisition"
                                        documentTitle={activeTemplate.header_title || 'PURCHASE REQUISITION'}
                                        documentNumber="PR-2026-00042"
                                        documentNumberLabel="PR No:"
                                        date={new Date().toISOString().split('T')[0]}
                                        dateLabel="Date:"
                                        secondaryDate={new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0]}
                                        secondaryDateLabel="Date Required:"

                                        leftBoxTitle="REQUESTING DEPARTMENT"
                                        leftBoxFields={[
                                            { label: 'Department / Unit', value: 'Operations & Plant Engineering' },
                                            { label: 'Requested by', value: 'Eng. Juma Mwinyi' },
                                            { label: 'Phone / Email', value: '+255 754 112 233 | juma@mutamark.co.tz' },
                                            { label: 'Budget Line / Vote', value: 'BUD-2026-OPS-042' },
                                        ]}

                                        rightBoxTitle="REQUISITION DETAILS"
                                        rightBoxFields={[
                                            { label: 'Priority', value: 'URGENT / Normal' },
                                            { label: 'Delivery Location', value: 'Arusha Workshop Plant' },
                                            { label: 'Procurement Method', value: 'Competitive Quotation RFQ' },
                                            { label: 'Currency', value: 'TZS' },
                                        ]}

                                        columns={[
                                            { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                                            {
                                                key: 'description',
                                                label: 'Item / Description & Specification',
                                                align: 'left',
                                                render: (it) => (
                                                    <div>
                                                        <div className="font-semibold text-slate-900">{it.name}</div>
                                                        <div className="text-[10px] text-slate-500 font-mono">SKU: {it.sku}</div>
                                                    </div>
                                                ),
                                            },
                                            { key: 'quantity', label: 'Qty', align: 'center', width: '55px', render: (it) => it.quantity },
                                            { key: 'unit', label: 'Unit', align: 'center', width: '60px', render: (it) => it.unit },
                                            { key: 'unit_price', label: 'Est. Unit Price (TZS)', align: 'right', width: '135px', render: (it) => it.unit_price.toLocaleString() },
                                            { key: 'amount', label: 'Est. Amount (TZS)', align: 'right', width: '135px', render: (it) => it.total_amount.toLocaleString() },
                                        ]}
                                        items={sampleItems}

                                        totals={[
                                            { label: 'Estimated Subtotal', value: 4450000 },
                                            { label: 'VAT (if applicable)', value: 0 },
                                            { label: 'Other Costs (if any)', value: 0 },
                                            { label: 'TOTAL ESTIMATED (TZS)', value: 4450000, isGrandTotal: true },
                                        ]}

                                        lowerLeftTitle="Justification / Purpose"
                                        lowerLeftContent={
                                            <div className="space-y-1.5 text-[11px]">
                                                <div><span className="font-semibold">Reason for purchase:</span> Essential quarterly maintenance and breakdown prevention for factory conveyor lines.</div>
                                                <div className="pt-1"><span className="font-semibold">Existing stock checked:</span> Yes &nbsp;&nbsp;&nbsp;&nbsp; <span className="font-semibold">Funds available:</span> Yes</div>
                                            </div>
                                        }

                                        lowerRightTitle="Notes / Conditions"
                                        lowerRightContent={
                                            <div className="text-[11px] text-slate-700 leading-relaxed">
                                                <p>{activeTemplate.terms_and_conditions || 'Attach specifications or drawings where applicable. An approved requisition must be on file before any order is placed.'}</p>
                                            </div>
                                        }

                                        signatures={[
                                            { label: 'Requested by', value: 'Eng. Juma Mwinyi', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0] },
                                            { label: 'Head of Department', value: 'Operations Manager', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0] },
                                            { label: 'Procurement Officer', value: 'Procurement Specialist', extraLabel: 'Signature', extraValue: '' },
                                            { label: 'Approved by (Accounting Officer)', value: 'Managing Director / CFO', extraLabel: 'Signature / Stamp', extraValue: '' },
                                        ]}

                                        footerSlogan={activeTemplate.footer_text || 'Please attach supporting documents to this requisition.'}
                                    />
                                ) : kind === 'rfq' ? (
                                    <CorporateDocumentTemplate
                                        type="rfq"
                                        documentTitle={activeTemplate.header_title || 'REQUEST FOR QUOTATION'}
                                        documentNumber="RFQ-2026-00018"
                                        documentNumberLabel="RFQ No:"
                                        date={new Date().toISOString().split('T')[0]}
                                        dateLabel="Issue Date:"
                                        secondaryDate={new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0]}
                                        secondaryDateLabel="Closing Date:"

                                        leftBoxTitle="TO (SUPPLIER)"
                                        leftBoxFields={[
                                            { label: 'Supplier / Company Name', value: 'Kilimanjaro Industrial Parts Ltd' },
                                            { label: 'Address', value: 'Plot 15, Mandela Express, Dar es Salaam' },
                                            { label: 'Phone / Email', value: '+255 22 286 1122 | sales@kilimanjaroparts.co.tz' },
                                            { label: 'TIN / VRN (if applicable)', value: '119-823-455 | 40098124-K' },
                                        ]}

                                        rightBoxTitle="RFQ DETAILS"
                                        rightBoxFields={[
                                            { label: 'Reference / Tender No', value: 'TND-2026-OPS-004' },
                                            { label: 'Delivery Location', value: 'Dar es Salaam Central Warehouse' },
                                            { label: 'Quotation Validity', value: '30 days' },
                                            { label: 'Currency', value: 'TZS' },
                                        ]}

                                        columns={[
                                            { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                                            {
                                                key: 'description',
                                                label: 'Item / Description & Specification',
                                                align: 'left',
                                                render: (it) => (
                                                    <div>
                                                        <div className="font-semibold text-slate-900">{it.name}</div>
                                                        <div className="text-[10px] text-slate-500 font-mono">SKU: {it.sku}</div>
                                                    </div>
                                                ),
                                            },
                                            { key: 'quantity', label: 'Qty', align: 'center', width: '60px', render: (it) => it.quantity },
                                            { key: 'unit', label: 'Unit', align: 'center', width: '60px', render: (it) => it.unit },
                                            { key: 'unit_price', label: 'Unit Price (TZS)', align: 'right', width: '135px', render: () => '________________' },
                                            { key: 'amount', label: 'Amount (TZS)', align: 'right', width: '135px', render: () => '________________' },
                                        ]}
                                        items={sampleItems}

                                        totals={[
                                            { label: 'Subtotal', value: '________________' },
                                            { label: 'VAT (if applicable)', value: '________________' },
                                            { label: 'Delivery / Other Costs', value: '________________' },
                                            { label: 'TOTAL QUOTED (TZS)', value: '________________', isGrandTotal: true },
                                        ]}

                                        lowerLeftTitle="Instructions to Supplier"
                                        lowerLeftContent={
                                            <div className="text-[11px] text-slate-700 leading-relaxed space-y-1">
                                                <p>Quote all prices in TZS and state whether VAT is included.</p>
                                                <p>Submit your quotation on or before the closing date. Late quotations may not be considered.</p>
                                            </div>
                                        }

                                        lowerRightTitle="Supplier Offer (to be completed)"
                                        lowerRightContent={
                                            <div className="space-y-1.5 text-[11px]">
                                                <div><span className="font-semibold">Delivery Period (days):</span> _______________________________</div>
                                                <div><span className="font-semibold">Payment Terms Offered:</span> ______________________________</div>
                                                <div><span className="font-semibold">Warranty (if any):</span> ___________________________________</div>
                                                <div><span className="font-semibold">Contact Person:</span> ____________________________________</div>
                                            </div>
                                        }

                                        signatures={[
                                            { label: 'Issued by', value: 'Procurement Specialist', extraLabel: 'Name', extraValue: 'Supply Chain Management Unit', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0] },
                                            { label: 'Supplier signature & stamp', value: '', extraLabel: 'Name', extraValue: '', dateLabel: 'Date', dateValue: '' },
                                        ]}

                                        footerSlogan={activeTemplate.footer_text || 'Thank you for your quotation.'}
                                    />
                                ) : kind === 'purchase_order' ? (
                                    <CorporateDocumentTemplate
                                        type="purchase_order"
                                        documentTitle={activeTemplate.header_title || 'PURCHASE ORDER'}
                                        documentNumber="PO-2026-00088"
                                        documentNumberLabel="PO No:"
                                        date={new Date().toISOString().split('T')[0]}
                                        dateLabel="Order Date:"
                                        secondaryDate={new Date(Date.now() + 10 * 86400000).toISOString().split('T')[0]}
                                        secondaryDateLabel="Delivery Date:"

                                        leftBoxTitle="VENDOR / SUPPLIER"
                                        leftBoxFields={[
                                            { label: 'Supplier / Company Name', value: 'Kilimanjaro Industrial Parts Ltd' },
                                            { label: 'Address', value: 'Plot 15, Mandela Express, Dar es Salaam, Tanzania' },
                                            { label: 'Phone / Email', value: '+255 22 286 1122 | sales@kilimanjaroparts.co.tz' },
                                            { label: 'TIN / VRN (if applicable)', value: '119-823-455 | 40098124-K' },
                                        ]}

                                        rightBoxTitle="SHIP TO / ORDER DETAILS"
                                        rightBoxFields={[
                                            { label: 'Deliver To', value: 'Main Factory Warehouse, Dar es Salaam' },
                                            { label: 'Quotation / RFQ Ref', value: 'RFQ-2026-00018' },
                                            { label: 'Payment Terms', value: '30 days Net' },
                                            { label: 'Currency', value: 'TZS' },
                                        ]}

                                        columns={[
                                            { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                                            {
                                                key: 'description',
                                                label: 'Item / Service Description',
                                                align: 'left',
                                                render: (it) => (
                                                    <div>
                                                        <div className="font-semibold text-slate-900">{it.name}</div>
                                                        <div className="text-[10px] text-slate-500 font-mono">SKU: {it.sku}</div>
                                                    </div>
                                                ),
                                            },
                                            { key: 'quantity', label: 'Qty', align: 'center', width: '55px', render: (it) => it.quantity },
                                            { key: 'unit', label: 'Unit', align: 'center', width: '60px', render: (it) => it.unit },
                                            { key: 'unit_price', label: 'Unit Price (TZS)', align: 'right', width: '130px', render: (it) => it.unit_price.toLocaleString() },
                                            { key: 'amount', label: 'Amount (TZS)', align: 'right', width: '130px', render: (it) => it.total_amount.toLocaleString() },
                                        ]}
                                        items={sampleItems}

                                        totals={[
                                            { label: 'Subtotal', value: 4450000 },
                                            { label: 'Discount (if any)', value: 0 },
                                            { label: 'VAT (if applicable)', value: 801000 },
                                            { label: 'TOTAL ORDER VALUE (TZS)', value: 5251000, isGrandTotal: true },
                                        ]}

                                        lowerLeftTitle="Invoicing Instructions"
                                        lowerLeftContent={
                                            <div className="space-y-1 text-[11px] text-slate-700">
                                                <p>Send invoice to: accounts@mutamark.co.tz</p>
                                                <p>Quote the PO number on all invoices, delivery notes and correspondence.</p>
                                            </div>
                                        }

                                        lowerRightTitle="Terms & Conditions"
                                        lowerRightContent={
                                            <div className="space-y-1 text-[11px] text-slate-700">
                                                <p>Goods must match the specification ordered.</p>
                                                <p>Goods are subject to inspection on delivery.</p>
                                                <p>Non-conforming goods may be rejected.</p>
                                                <p>Deliver on or before the date stated.</p>
                                            </div>
                                        }

                                        signatures={[
                                            { label: 'Prepared by', value: 'Procurement Specialist', extraLabel: 'Name', extraValue: 'Supply Chain Directorate' },
                                            { label: 'Authorised signature', value: 'Managing Director / CFO', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0] },
                                            { label: 'Vendor acknowledgement', value: 'Accepted by Supplier Rep', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0] },
                                        ]}

                                        footerSlogan={activeTemplate.footer_text || 'Please acknowledge receipt of this order.'}
                                    />
                                ) : kind === 'delivery_note' ? (
                                    <CorporateDocumentTemplate
                                        type="delivery_note"
                                        documentTitle={activeTemplate.header_title || 'DELIVERY NOTE'}
                                        documentNumber="DN-2026-00104"
                                        documentNumberLabel="DN No:"
                                        date={new Date().toISOString().split('T')[0]}
                                        dateLabel="Delivery Date:"
                                        tertiaryNumber="PO-2026-00088"
                                        tertiaryNumberLabel="PO / Order No:"

                                        leftBoxTitle="DELIVER TO"
                                        leftBoxFields={[
                                            { label: 'Customer / Company Name', value: 'Serengeti Breweries Logistics Unit' },
                                            { label: 'Delivery Address', value: "Plot 88, Chang'ombe Industrial Area, Dar es Salaam" },
                                            { label: 'Contact Person / Phone', value: 'Hassan Ally (+255 784 990 011)' },
                                            { label: 'Receiving Department', value: 'Central Plant Receiving Warehouse' },
                                        ]}

                                        rightBoxTitle="DELIVERY DETAILS"
                                        rightBoxFields={[
                                            { label: 'Customer PO No', value: 'PO-SBL-2026-902' },
                                            { label: 'Invoice No (if issued)', value: 'INV-2026-00214' },
                                            { label: 'Vehicle / Driver', value: 'T 412 DKM / Ramadhani Bakari' },
                                            { label: 'Delivered by', value: 'Mutamark Express Logistics' },
                                        ]}

                                        columns={[
                                            { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                                            {
                                                key: 'description',
                                                label: 'Item / Description',
                                                align: 'left',
                                                render: (it) => (
                                                    <div>
                                                        <div className="font-semibold text-slate-900">{it.name}</div>
                                                        <div className="text-[10px] text-slate-500 font-mono">SKU: {it.sku}</div>
                                                    </div>
                                                ),
                                            },
                                            { key: 'unit', label: 'Unit', align: 'center', width: '60px', render: (it) => it.unit },
                                            { key: 'quantity_ordered', label: 'Qty Ordered', align: 'center', width: '85px', render: (it) => it.qty_ordered },
                                            { key: 'quantity_shipped', label: 'Qty Delivered', align: 'center', width: '85px', render: (it) => it.qty_delivered },
                                            { key: 'remarks', label: 'Remarks', align: 'left', width: '130px', render: () => 'Delivered in Good Order' },
                                        ]}
                                        items={sampleItems}

                                        lowerLeftTitle="Delivery Condition"
                                        lowerLeftContent={
                                            <div className="space-y-2 text-[11px]">
                                                <div><span className="font-semibold">Received in good condition:</span> Yes / No</div>
                                                <div><span className="font-semibold">Damages / shortages noted:</span> ___________________________</div>
                                            </div>
                                        }

                                        lowerRightTitle="Notes"
                                        lowerRightContent={
                                            <div className="text-[11px] text-slate-700 leading-relaxed">
                                                <p>Please check goods on receipt and note any discrepancies before signing. Report any shortage or damage promptly.</p>
                                            </div>
                                        }

                                        signatures={[
                                            { label: 'Delivered by (name & signature)', value: 'Ramadhani Bakari (Driver)', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0], extraLabel: 'Supplier stamp', extraValue: '' },
                                            { label: 'Received by (name & signature)', value: 'Hassan Ally (Storekeeper)', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0], extraLabel: 'Receiver stamp', extraValue: '' },
                                        ]}

                                        footerSlogan={activeTemplate.footer_text || 'Thank you for your business.'}
                                    />
                                ) : kind === 'goods_received_note' ? (
                                    <CorporateDocumentTemplate
                                        type="goods_received_note"
                                        documentTitle={activeTemplate.header_title || 'GOODS RECEIVED NOTE'}
                                        documentNumber="GRN-2026-00075"
                                        documentNumberLabel="GRN No:"
                                        date={new Date().toISOString().split('T')[0]}
                                        dateLabel="Date Received:"
                                        tertiaryNumber="PO-2026-00088"
                                        tertiaryNumberLabel="PO No:"

                                        leftBoxTitle="SUPPLIER"
                                        leftBoxFields={[
                                            { label: 'Supplier Name', value: 'Kilimanjaro Industrial Parts Ltd' },
                                            { label: 'Address', value: 'Plot 15, Mandela Express, Dar es Salaam' },
                                            { label: 'Phone / Email', value: '+255 22 286 1122 | sales@kilimanjaroparts.co.tz' },
                                            { label: 'Delivery Note No', value: 'DN-KIP-2026-441' },
                                        ]}

                                        rightBoxTitle="RECEIVING DETAILS"
                                        rightBoxFields={[
                                            { label: 'Received at (Store / Location)', value: 'Main Warehouse Bay 4' },
                                            { label: 'Supplier Invoice No', value: 'INV-KIP-2026-1029' },
                                            { label: 'Inspection Date', value: new Date().toISOString().split('T')[0] },
                                            { label: 'Receiving Department', value: 'Stores & Inventory Directorate' },
                                        ]}

                                        columns={[
                                            { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                                            {
                                                key: 'description',
                                                label: 'Item / Description',
                                                align: 'left',
                                                render: (it) => (
                                                    <div>
                                                        <div className="font-semibold text-slate-900">{it.name}</div>
                                                        <div className="text-[10px] text-slate-500 font-mono">SKU: {it.sku}</div>
                                                    </div>
                                                ),
                                            },
                                            { key: 'unit', label: 'Unit', align: 'center', width: '55px', render: (it) => it.unit },
                                            { key: 'ordered', label: 'Ordered', align: 'center', width: '65px', render: (it) => it.ordered_quantity },
                                            { key: 'received', label: 'Received', align: 'center', width: '65px', render: (it) => it.received_quantity },
                                            { key: 'accepted', label: 'Accepted', align: 'center', width: '65px', render: (it) => it.accepted_quantity },
                                            { key: 'rejected', label: 'Rejected', align: 'center', width: '65px', render: (it) => it.rejected_quantity },
                                            { key: 'remarks', label: 'Condition / Remarks', align: 'left', width: '135px', render: () => 'Inspected & Passed' },
                                        ]}
                                        items={sampleItems}

                                        lowerLeftTitle="Inspection Findings"
                                        lowerLeftContent={
                                            <div className="space-y-1.5 text-[11px]">
                                                <div><span className="font-semibold">Specification met:</span> Yes / No</div>
                                                <div><span className="font-semibold">Quantity matches order:</span> Yes / No</div>
                                                <div><span className="font-semibold">Comments:</span> All items inspected and match approved procurement technical specification.</div>
                                            </div>
                                        }

                                        lowerRightTitle="Disposition"
                                        lowerRightContent={
                                            <div className="space-y-1.5 text-[11px]">
                                                <div><span className="font-semibold">Entered in store ledger:</span> Yes / No</div>
                                                <div><span className="font-semibold">Ledger Folio No:</span> LF-2026-089</div>
                                                <div><span className="font-semibold">Rejected items:</span> Return / Replace / Credit</div>
                                            </div>
                                        }

                                        signatures={[
                                            { label: 'Received by (Store Keeper)', value: 'Warehouse Storekeeper', extraLabel: 'Inspected by', extraValue: 'QA Technical Inspector' },
                                            { label: 'Verified by (Procurement)', value: 'Procurement Officer', extraLabel: 'Approved by', extraValue: 'Operations Director' },
                                            { label: 'Date', value: new Date().toISOString().split('T')[0], extraLabel: 'Date', extraValue: new Date().toISOString().split('T')[0] },
                                        ]}

                                        footerSlogan={activeTemplate.footer_text || 'Distribution: Accounts / Store / Procurement'}
                                    />
                                ) : (
                                    <CorporateDocumentTemplate
                                        type="invoice"
                                        documentTitle={activeTemplate.header_title || 'INVOICE'}
                                        documentNumber="INV-2026-00084"
                                        documentNumberLabel="Invoice No:"
                                        date={new Date().toISOString().split('T')[0]}
                                        dateLabel="Invoice Date:"
                                        secondaryDate={new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0]}
                                        secondaryDateLabel="Due Date:"

                                        leftBoxTitle="BILL TO"
                                        leftBoxFields={[
                                            { label: 'Customer / Company Name', value: 'Kilimanjaro Industrial Parts Ltd' },
                                            { label: 'Address', value: 'Plot 42, Nyerere Road, Dar es Salaam, Tanzania' },
                                            { label: 'Phone / Email', value: '+255 754 000 111 | accounts@kilimanjaroparts.co.tz' },
                                            { label: 'TIN / VRN (if applicable)', value: '119-823-455 | 40098124-K' },
                                        ]}

                                        rightBoxTitle="DELIVERY / REFERENCE DETAILS"
                                        rightBoxFields={[
                                            { label: 'Customer PO No', value: 'PO-2026-REF-88' },
                                            { label: 'Delivery Note No', value: 'DN-2026-00104' },
                                            { label: 'Payment Terms', value: '30 days Net' },
                                            { label: 'Currency', value: 'TZS' },
                                        ]}

                                        columns={[
                                            { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                                            {
                                                key: 'description',
                                                label: 'Item / Service Description',
                                                align: 'left',
                                                render: (it) => (
                                                    <div>
                                                        <div className="font-semibold text-slate-900">{it.name}</div>
                                                        <div className="text-[10px] text-slate-500 font-mono">SKU: {it.sku}</div>
                                                    </div>
                                                ),
                                            },
                                            { key: 'quantity', label: 'Qty', align: 'center', width: '55px', render: (it) => it.quantity },
                                            { key: 'unit', label: 'Unit', align: 'center', width: '60px', render: (it) => it.unit },
                                            { key: 'unit_price', label: 'Unit Price (TZS)', align: 'right', width: '130px', render: (it) => it.unit_price.toLocaleString() },
                                            { key: 'amount', label: 'Amount (TZS)', align: 'right', width: '130px', render: (it) => it.total_amount.toLocaleString() },
                                        ]}
                                        items={sampleItems}

                                        totals={[
                                            { label: 'Subtotal', value: 4450000 },
                                            { label: 'Discount (if any)', value: 0 },
                                            { label: 'VAT (if applicable)', value: 801000 },
                                            { label: 'TOTAL DUE (TZS)', value: 5251000, isGrandTotal: true },
                                        ]}

                                        lowerLeftTitle="Payment Details"
                                        lowerLeftContent={
                                            <div className="space-y-1 text-[11px]">
                                                <div><span className="font-semibold">Bank:</span> CRDB Bank Plc / NMB Bank Plc</div>
                                                <div><span className="font-semibold">Account Name:</span> MUTAMARK ENTERPRISES LTD</div>
                                                <div><span className="font-semibold">Account No:</span> 0150-2938-4100 (TZS) / 0250-2938-4101 (USD)</div>
                                                <div><span className="font-semibold">Mobile Money:</span> Lipa Namba: 5492810 (MUTAMARK) — M-Pesa / Tigo Pesa / Airtel Money</div>
                                            </div>
                                        }

                                        lowerRightTitle="Notes / Terms"
                                        lowerRightContent={
                                            <div className="space-y-1 text-[11px] text-slate-700">
                                                <p>{activeTemplate.terms_and_conditions || 'Payment due within 30 days unless agreed otherwise.'}</p>
                                                <p>Please quote the invoice number when paying.</p>
                                                <p>Goods/services supplied as described above.</p>
                                            </div>
                                        }

                                        signatures={[
                                            { label: 'Prepared by', value: 'Accounts Officer', extraLabel: 'Name', extraValue: '' },
                                            { label: 'Authorised signature', value: '', dateLabel: 'Date', dateValue: new Date().toISOString().split('T')[0] },
                                        ]}

                                        footerSlogan={activeTemplate.footer_text || 'Thank you for your business.'}
                                    />
                                )}

                                <DialogFooter className="pt-3 border-t print:hidden">
                                    <Button variant="outline" onClick={() => setIsPreviewOpen(false)}>
                                        {t("Close Preview")}
                                    </Button>
                                    <Button
                                        onClick={() => window.print()}
                                        className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold gap-1"
                                    >
                                        <Printer className="w-4 h-4" />
                                        {t("Print Preview")}
                                    </Button>
                                </DialogFooter>
                            </div>
                        );
                    })()}
                </DialogContent>
            </Dialog>

            {/* MODAL 3: Delete Confirmation */}
            <ConfirmationDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                title={t("Delete Document Template?")}
                message={t("Are you sure you want to delete this document template? Documents using this template will automatically revert to default.")}
                onConfirm={confirmDelete}
            />
        </AuthenticatedLayout>
    );
}
