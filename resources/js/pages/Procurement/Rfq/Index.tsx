import React, { useState, useMemo } from 'react';
import { Head, usePage, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import {
    FileText,
    Plus,
    Scale,
    Send,
    CheckCircle2,
    Clock,
    Award,
    Building2,
    Calendar,
    ChevronRight,
    Printer,
    Edit3,
    Trash2,
    MoreVertical,
    TrendingDown,
    ShieldCheck,
    Truck,
    Package,
    DollarSign,
    AlertCircle,
    UserCheck,
    Check,
    ExternalLink
} from 'lucide-react';

interface RfqItem {
    id: number;
    rfq_id: number;
    product_id?: number | null;
    product_name: string;
    sku?: string | null;
    quantity: number;
    unit: string;
    target_unit_price: number;
    specifications?: string | null;
    product?: any;
}

interface SupplierQuotationItem {
    id: number;
    supplier_quotation_id: number;
    rfq_item_id: number;
    unit_price: number;
    quantity: number;
    total_price: number;
    remarks?: string | null;
    rfq_item?: RfqItem;
}

interface SupplierQuotation {
    id: number;
    rfq_id: number;
    vendor_id: number;
    vendor_name: string;
    quotation_reference: string;
    submission_date: string;
    valid_until?: string | null;
    delivery_lead_time_days: number;
    warranty_months: number;
    payment_terms?: string | null;
    total_bid_amount: number;
    status: 'submitted' | 'shortlisted' | 'awarded' | 'rejected';
    evaluation_score?: number | null;
    notes?: string | null;
    items: SupplierQuotationItem[];
    vendor?: any;
}

interface RfqRecord {
    id: number;
    rfq_number: string;
    title: string;
    requisition_id?: number | null;
    issue_date: string;
    closing_date: string;
    delivery_deadline?: string | null;
    warehouse_id?: number | null;
    status: 'draft' | 'published' | 'evaluating' | 'awarded' | 'cancelled';
    payment_terms?: string | null;
    delivery_terms?: string | null;
    awarded_vendor_id?: number | null;
    awarded_bid_id?: number | null;
    awarded_po_id?: number | null;
    evaluation_notes?: string | null;
    verification_hash?: string | null;
    items: RfqItem[];
    quotations: SupplierQuotation[];
    warehouse?: { id: number; name: string };
    requisition?: { id: number; requisition_number: string };
    awarded_vendor?: { id: number; company_name?: string; name?: string };
    awarded_po?: { id: number; po_number: string };
}

interface PageProps {
    rfqs: RfqRecord[];
    kpi: {
        total_rfqs: number;
        published_count: number;
        evaluating_count: number;
        awarded_count: number;
        total_awarded_tzs: number;
    };
    warehouses: Array<{ id: number; name: string }>;
    vendors: Array<{ id: number; company_name?: string; name?: string; email?: string; contact_number?: string }>;
    products: Array<{ id: number; name: string; sku?: string; purchase_price?: number }>;
    approved_requisitions: Array<{ id: number; requisition_number: string; purpose?: string; items?: any[] }>;
    filters?: {
        search?: string;
        warehouse_id?: string;
        status?: string;
    };
}

export default function RfqIndex({
    rfqs = [],
    kpi,
    warehouses = [],
    vendors = [],
    products = [],
    approved_requisitions = [],
    filters = {},
}: PageProps) {
    const { t } = useTranslation();

    // Local filters
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedStatusTab, setSelectedStatusTab] = useState(filters.status || 'all');
    const [selectedWarehouse, setSelectedWarehouse] = useState(filters.warehouse_id || 'all');

    // Modals
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [isBidModalOpen, setIsBidModalOpen] = useState(false);
    const [isCompareModalOpen, setIsCompareModalOpen] = useState(false);
    const [isPrintModalOpen, setIsPrintModalOpen] = useState(false);
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);

    // Active records
    const [activeRfq, setActiveRfq] = useState<RfqRecord | null>(null);
    const [rfqToDelete, setRfqToDelete] = useState<RfqRecord | null>(null);

    // Create Form
    const createForm = useForm({
        title: '',
        requisition_id: '',
        closing_date: '',
        delivery_deadline: '',
        warehouse_id: '',
        payment_terms: 'Net 30 Days',
        delivery_terms: 'DAP Dar es Salaam Warehouse',
        evaluation_notes: '',
        items: [
            {
                product_id: '',
                product_name: '',
                sku: '',
                quantity: 1,
                unit: 'units',
                target_unit_price: 0,
                specifications: '',
            }
        ]
    });

    // Edit Form
    const editForm = useForm({
        title: '',
        closing_date: '',
        delivery_deadline: '',
        warehouse_id: '',
        payment_terms: '',
        delivery_terms: '',
        evaluation_notes: '',
    });

    // Record Supplier Bid Form
    const bidForm = useForm({
        vendor_id: '',
        quotation_reference: '',
        submission_date: new Date().toISOString().split('T')[0],
        delivery_lead_time_days: 7,
        warranty_months: 12,
        payment_terms: '30 Days Net',
        notes: '',
        items: [] as Array<{
            rfq_item_id: number;
            product_name: string;
            quantity: number;
            unit_price: number;
            remarks: string;
        }>
    });

    // Award Bid Form
    const awardForm = useForm({
        quotation_id: '',
    });

    // Handlers
    const handleRequisitionChange = (prIdStr: string) => {
        createForm.setData('requisition_id', prIdStr);
        if (!prIdStr || prIdStr === 'none') return;

        const pr = approved_requisitions.find(r => String(r.id) === prIdStr);
        if (pr) {
            createForm.setData(data => ({
                ...data,
                requisition_id: prIdStr,
                title: `Procurement for Requisition: ${pr.requisition_number}`,
                items: (pr.items && pr.items.length > 0) ? pr.items.map(item => ({
                    product_id: item.product_id ? String(item.product_id) : '',
                    product_name: item.product_name || 'Item',
                    sku: item.sku || '',
                    quantity: Number(item.quantity) || 1,
                    unit: item.unit || 'units',
                    target_unit_price: Number(item.estimated_unit_price) || 0,
                    specifications: item.description || '',
                })) : [
                    {
                        product_id: '',
                        product_name: 'Procured Item',
                        sku: '',
                        quantity: 1,
                        unit: 'units',
                        target_unit_price: 0,
                        specifications: '',
                    }
                ]
            }));
        }
    };

    const addCreateItemRow = () => {
        createForm.setData('items', [
            ...createForm.data.items,
            {
                product_id: '',
                product_name: '',
                sku: '',
                quantity: 1,
                unit: 'units',
                target_unit_price: 0,
                specifications: '',
            }
        ]);
    };

    const removeCreateItemRow = (index: number) => {
        if (createForm.data.items.length <= 1) return;
        const updated = [...createForm.data.items];
        updated.splice(index, 1);
        createForm.setData('items', updated);
    };

    const handleCreateItemProductSelect = (index: number, prodIdStr: string) => {
        const prod = products.find(p => String(p.id) === prodIdStr);
        const updated = [...createForm.data.items];
        if (prod) {
            updated[index].product_id = prodIdStr;
            updated[index].product_name = prod.name;
            updated[index].sku = prod.sku || '';
            updated[index].target_unit_price = prod.purchase_price || 0;
        } else {
            updated[index].product_id = '';
        }
        createForm.setData('items', updated);
    };

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post(route('procurement.rfqs.store'), {
            onSuccess: () => {
                setIsCreateOpen(false);
                createForm.reset();
            }
        });
    };

    const openEditModal = (rfq: RfqRecord) => {
        setActiveRfq(rfq);
        editForm.setData({
            title: rfq.title,
            closing_date: rfq.closing_date ? rfq.closing_date.split('T')[0] : '',
            delivery_deadline: rfq.delivery_deadline ? rfq.delivery_deadline.split('T')[0] : '',
            warehouse_id: rfq.warehouse_id ? String(rfq.warehouse_id) : '',
            payment_terms: rfq.payment_terms || '',
            delivery_terms: rfq.delivery_terms || '',
            evaluation_notes: rfq.evaluation_notes || '',
        });
        setIsEditOpen(true);
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeRfq) return;
        editForm.put(route('procurement.rfqs.update', activeRfq.id), {
            onSuccess: () => {
                setIsEditOpen(false);
                setActiveRfq(null);
            }
        });
    };

    const handlePublishRfq = (rfq: RfqRecord) => {
        router.post(route('procurement.rfqs.publish', rfq.id));
    };

    const openBidModal = (rfq: RfqRecord) => {
        setActiveRfq(rfq);
        bidForm.setData({
            vendor_id: '',
            quotation_reference: `QUO-${rfq.rfq_number.replace('RFQ-', '')}-${Math.floor(100 + Math.random() * 900)}`,
            submission_date: new Date().toISOString().split('T')[0],
            delivery_lead_time_days: 7,
            warranty_months: 12,
            payment_terms: rfq.payment_terms || '30 Days Net',
            notes: '',
            items: rfq.items.map(item => ({
                rfq_item_id: item.id,
                product_name: item.product_name,
                quantity: item.quantity,
                unit_price: item.target_unit_price || 0,
                remarks: '',
            }))
        });
        setIsBidModalOpen(true);
    };

    const handleBidSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeRfq) return;
        bidForm.post(route('procurement.rfqs.bids.store', activeRfq.id), {
            onSuccess: () => {
                setIsBidModalOpen(false);
                bidForm.reset();
            }
        });
    };

    const openCompareModal = (rfq: RfqRecord) => {
        setActiveRfq(rfq);
        setIsCompareModalOpen(true);
    };

    const handleAwardQuotation = (quotationId: number) => {
        if (!activeRfq) return;
        awardForm.setData('quotation_id', String(quotationId));
        router.post(route('procurement.rfqs.award', activeRfq.id), {
            quotation_id: quotationId,
        }, {
            onSuccess: () => {
                setIsCompareModalOpen(false);
            }
        });
    };

    const openPrintModal = (rfq: RfqRecord) => {
        setActiveRfq(rfq);
        setIsPrintModalOpen(true);
    };

    const openDeleteModal = (rfq: RfqRecord) => {
        setRfqToDelete(rfq);
        setIsDeleteOpen(true);
    };

    const confirmDelete = () => {
        if (!rfqToDelete) return;
        router.delete(route('procurement.rfqs.destroy', rfqToDelete.id), {
            onSuccess: () => {
                setIsDeleteOpen(false);
                setRfqToDelete(null);
            }
        });
    };

    // Filtered records
    const filteredRfqs = useMemo(() => {
        return rfqs.filter(rfq => {
            const matchesSearch =
                !searchTerm ||
                rfq.rfq_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
                rfq.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                rfq.items.some(i => i.product_name.toLowerCase().includes(searchTerm.toLowerCase()));

            const matchesWarehouse =
                selectedWarehouse === 'all' ||
                String(rfq.warehouse_id) === selectedWarehouse;

            let matchesStatus = true;
            if (selectedStatusTab === 'published') {
                matchesStatus = rfq.status === 'published';
            } else if (selectedStatusTab === 'evaluating') {
                matchesStatus = rfq.status === 'evaluating';
            } else if (selectedStatusTab === 'awarded') {
                matchesStatus = rfq.status === 'awarded';
            } else if (selectedStatusTab === 'draft') {
                matchesStatus = rfq.status === 'draft';
            }

            return matchesSearch && matchesWarehouse && matchesStatus;
        });
    }, [rfqs, searchTerm, selectedStatusTab, selectedWarehouse]);

    // Badge styling helpers
    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'draft':
                return <Badge variant="outline" className="border-slate-400 text-slate-600 bg-slate-50 dark:bg-slate-900">Draft</Badge>;
            case 'published':
                return <Badge className="bg-blue-600 hover:bg-blue-700 text-white">Open for Bidding</Badge>;
            case 'evaluating':
                return <Badge className="bg-amber-500 hover:bg-amber-600 text-white">Under Evaluation</Badge>;
            case 'awarded':
                return <Badge className="bg-emerald-600 hover:bg-emerald-700 text-white">Awarded & PO Committed</Badge>;
            case 'cancelled':
                return <Badge variant="destructive">Cancelled</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    // Live total for create form
    const createTargetTotal = useMemo(() => {
        return createForm.data.items.reduce((sum, item) => {
            return sum + (Number(item.quantity) || 0) * (Number(item.target_unit_price) || 0);
        }, 0);
    }, [createForm.data.items]);

    // Live total for bid form
    const bidTotal = useMemo(() => {
        return bidForm.data.items.reduce((sum, item) => {
            return sum + (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
        }, 0);
    }, [bidForm.data.items]);

    return (
        <AuthenticatedLayout>
            <Head title={t("Request for Quotations (RFQ) & Supplier Evaluation")} />

            <div className="p-6 space-y-6 max-w-7xl mx-auto">
                {/* Header Banner */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 rounded-xl shadow-lg border border-indigo-900/50">
                    <div className="flex items-center gap-4">
                        <div className="p-3 bg-indigo-600/30 rounded-xl border border-indigo-500/40">
                            <Scale className="w-8 h-8 text-indigo-400" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-2xl font-bold tracking-tight">
                                    {t("Request for Quotation (RFQ) Engine")}
                                </h1>
                                <Badge className="bg-indigo-500/20 text-indigo-300 border-indigo-500/30">
                                    SRS Module 8: Section 3.8.3
                                </Badge>
                            </div>
                            <p className="text-sm text-slate-300 mt-1">
                                {t("Competitive Supplier Bidding, Dynamic Comparative Evaluation Statement & Automated PO Awarding")}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            onClick={() => setIsCreateOpen(true)}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-md font-semibold gap-2"
                        >
                            <Plus className="w-4 h-4" />
                            {t("New RFQ Tender")}
                        </Button>
                    </div>
                </div>

                {/* KPI Overview Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Total RFQ Tenders")}
                            </CardTitle>
                            <FileText className="w-4 h-4 text-slate-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{kpi.total_rfqs}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("Competitive tenders issued")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Open for Bidding")}
                            </CardTitle>
                            <Send className="w-4 h-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{kpi.published_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("Awaiting supplier quotations")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Under Evaluation")}
                            </CardTitle>
                            <Scale className="w-4 h-4 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-600">{kpi.evaluating_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t("Comparative analysis active")}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border/60 shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader className="flex flex-row items-center justify-between pb-2 space-y-0">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t("Awarded & Committed")}
                            </CardTitle>
                            <Award className="w-4 h-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">
                                {Number(kpi.total_awarded_tzs).toLocaleString()} TZS
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {kpi.awarded_count} {t("orders committed to PO")}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters & Status Tabs */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex flex-wrap items-center gap-3">
                        <Input
                            placeholder={t("Search by RFQ#, title, or item...")}
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            className="w-72"
                        />
                        <Select
                            value={selectedWarehouse}
                            onValueChange={val => setSelectedWarehouse(val)}
                        >
                            <SelectTrigger className="w-56">
                                <SelectValue placeholder={t("All Warehouses")} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t("All Warehouses")}</SelectItem>
                                {warehouses.map(wh => (
                                    <SelectItem key={wh.id} value={String(wh.id)}>
                                        {wh.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {/* Filter Tabs */}
                    <div className="flex items-center gap-1 bg-muted p-1 rounded-lg border border-border/50 text-xs font-medium">
                        {(['all', 'published', 'evaluating', 'awarded', 'draft'] as const).map(tab => (
                            <button
                                key={tab}
                                onClick={() => setSelectedStatusTab(tab)}
                                className={`px-3 py-1.5 rounded-md capitalize transition-colors ${
                                    selectedStatusTab === tab
                                        ? 'bg-background text-foreground shadow-sm font-semibold'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                {tab === 'all' ? t('All Tenders') : tab}
                            </button>
                        ))}
                    </div>
                </div>

                {/* RFQ Data Table */}
                <Card className="border-border/60 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/40">
                                    <TableHead className="font-semibold">{t("RFQ Number & Dates")}</TableHead>
                                    <TableHead className="font-semibold">{t("Title & Linked PR")}</TableHead>
                                    <TableHead className="font-semibold">{t("Warehouse")}</TableHead>
                                    <TableHead className="font-semibold">{t("Status")}</TableHead>
                                    <TableHead className="font-semibold text-center">{t("Supplier Bids")}</TableHead>
                                    <TableHead className="font-semibold text-right">{t("Lowest Bid (TZS)")}</TableHead>
                                    <TableHead className="font-semibold">{t("Awarded PO")}</TableHead>
                                    <TableHead className="font-semibold text-right">{t("Actions")}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {filteredRfqs.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={8} className="text-center py-10 text-muted-foreground">
                                            <div className="flex flex-col items-center gap-2">
                                                <Scale className="w-8 h-8 text-muted-foreground/50" />
                                                <p className="font-medium">{t("No Requests for Quotation found.")}</p>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => setIsCreateOpen(true)}
                                                    className="mt-2"
                                                >
                                                    <Plus className="w-4 h-4 mr-1" />
                                                    {t("Create First RFQ")}
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    filteredRfqs.map(rfq => (
                                        <TableRow key={rfq.id} className="hover:bg-muted/30 transition-colors">
                                            <TableCell>
                                                <div className="font-mono font-bold text-sm text-indigo-600 dark:text-indigo-400">
                                                    {rfq.rfq_number}
                                                </div>
                                                <div className="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                                                    <Calendar className="w-3 h-3" />
                                                    {t("Closes")}: {rfq.closing_date ? rfq.closing_date.split('T')[0] : 'N/A'}
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                <div className="font-semibold text-sm max-w-xs truncate" title={rfq.title}>
                                                    {rfq.title}
                                                </div>
                                                <div className="flex items-center gap-2 mt-1">
                                                    {rfq.requisition ? (
                                                        <Badge variant="outline" className="text-xs border-indigo-200 text-indigo-600 bg-indigo-50 dark:bg-indigo-950/20">
                                                            PR: {rfq.requisition.requisition_number}
                                                        </Badge>
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground">
                                                            {rfq.items.length} {t("item line(s)")}
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                <div className="flex items-center gap-1.5 text-sm">
                                                    <Building2 className="w-3.5 h-3.5 text-muted-foreground" />
                                                    <span>{rfq.warehouse?.name || t("Main Warehouse")}</span>
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                {getStatusBadge(rfq.status)}
                                            </TableCell>

                                            <TableCell className="text-center">
                                                <Badge
                                                    variant={rfq.quotations.length > 0 ? "secondary" : "outline"}
                                                    className={`font-semibold cursor-pointer ${
                                                        rfq.quotations.length > 0 ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : ''
                                                    }`}
                                                    onClick={() => openCompareModal(rfq)}
                                                >
                                                    {rfq.quotations.length} {t("bids submitted")}
                                                </Badge>
                                            </TableCell>

                                            <TableCell className="text-right font-bold">
                                                {rfq.quotations.length > 0 ? (
                                                    <div>
                                                        <span className="text-emerald-600 dark:text-emerald-400">
                                                            {Number(
                                                                Math.min(...rfq.quotations.map(q => Number(q.total_bid_amount)))
                                                            ).toLocaleString()}{" "}
                                                            TZS
                                                        </span>
                                                        <span className="block text-xs font-normal text-muted-foreground">
                                                            Target: {Number(
                                                                rfq.items.reduce((sum, it) => sum + (Number(it.quantity) * Number(it.target_unit_price || 0)), 0)
                                                            ).toLocaleString()} TZS
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">-</span>
                                                )}
                                            </TableCell>

                                            <TableCell>
                                                {rfq.awarded_po ? (
                                                    <div>
                                                        <Badge variant="outline" className="font-mono text-xs border-emerald-300 text-emerald-700 bg-emerald-50 dark:bg-emerald-950/30">
                                                            {rfq.awarded_po.po_number}
                                                        </Badge>
                                                        {rfq.awarded_vendor && (
                                                            <div className="text-xs text-muted-foreground mt-0.5 truncate max-w-[120px]">
                                                                {rfq.awarded_vendor.company_name || rfq.awarded_vendor.name}
                                                            </div>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">-</span>
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
                                                        <DropdownMenuItem onClick={() => openCompareModal(rfq)}>
                                                            <Scale className="w-4 h-4 mr-2 text-indigo-500" />
                                                            {t("Comparative Statement")}
                                                        </DropdownMenuItem>

                                                        {rfq.status !== 'awarded' && (
                                                            <DropdownMenuItem onClick={() => openBidModal(rfq)}>
                                                                <Plus className="w-4 h-4 mr-2 text-emerald-500" />
                                                                {t("Record Supplier Bid")}
                                                            </DropdownMenuItem>
                                                        )}

                                                        {rfq.status === 'draft' && (
                                                            <DropdownMenuItem onClick={() => handlePublishRfq(rfq)}>
                                                                <Send className="w-4 h-4 mr-2 text-blue-500" />
                                                                {t("Publish for Bidding")}
                                                            </DropdownMenuItem>
                                                        )}

                                                        <DropdownMenuItem onClick={() => openPrintModal(rfq)}>
                                                            <Printer className="w-4 h-4 mr-2 text-slate-500" />
                                                            {t("View / Print RFQ Slip")}
                                                        </DropdownMenuItem>

                                                        {['draft', 'published'].includes(rfq.status) && (
                                                            <DropdownMenuItem onClick={() => openEditModal(rfq)}>
                                                                <Edit3 className="w-4 h-4 mr-2 text-blue-500" />
                                                                {t("Edit Details")}
                                                            </DropdownMenuItem>
                                                        )}

                                                        {rfq.status !== 'awarded' && (
                                                            <>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem
                                                                    onClick={() => openDeleteModal(rfq)}
                                                                    className="text-red-600 focus:text-red-600"
                                                                >
                                                                    <Trash2 className="w-4 h-4 mr-2" />
                                                                    {t("Delete RFQ")}
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}
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

            {/* MODAL 1: Create New RFQ */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-xl font-bold">
                            <Scale className="w-5 h-5 text-indigo-600" />
                            {t("Create Request for Quotation (RFQ)")}
                        </DialogTitle>
                        <p className="text-sm text-muted-foreground">
                            {t("Issue a competitive procurement tender manually or auto-populate from an approved requisition.")}
                        </p>
                    </DialogHeader>

                    <form onSubmit={handleCreateSubmit} className="space-y-4 pt-2">
                        {/* Auto-Populate from Purchase Requisition */}
                        <div className="bg-indigo-50/60 dark:bg-indigo-950/20 p-3.5 rounded-lg border border-indigo-200/60 dark:border-indigo-800/40">
                            <Label className="text-xs font-semibold text-indigo-900 dark:text-indigo-300">
                                {t("Import Line Items from Approved Purchase Requisition (Optional)")}
                            </Label>
                            <Select
                                value={createForm.data.requisition_id}
                                onValueChange={handleRequisitionChange}
                            >
                                <SelectTrigger className="mt-1 bg-background">
                                    <SelectValue placeholder={t("-- Select an approved requisition --")} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">{t("-- None / Manual Entry --")}</SelectItem>
                                    {approved_requisitions.map(pr => (
                                        <SelectItem key={pr.id} value={String(pr.id)}>
                                            {pr.requisition_number} â€” {pr.purpose || 'Material Request'}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Title & Warehouse */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="rfq_title" className="text-xs font-semibold">
                                    {t("Tender Title / Description *")}
                                </Label>
                                <Input
                                    id="rfq_title"
                                    required
                                    placeholder={t("e.g. Supply of Industrial Conveyor Belts")}
                                    value={createForm.data.title}
                                    onChange={e => createForm.setData('title', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="rfq_warehouse" className="text-xs font-semibold">
                                    {t("Delivery Warehouse *")}
                                </Label>
                                <Select
                                    value={createForm.data.warehouse_id}
                                    onValueChange={val => createForm.setData('warehouse_id', val)}
                                >
                                    <SelectTrigger id="rfq_warehouse">
                                        <SelectValue placeholder={t("Select Warehouse")} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map(wh => (
                                            <SelectItem key={wh.id} value={String(wh.id)}>
                                                {wh.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {/* Closing Date & Delivery Deadline */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="rfq_closing_date" className="text-xs font-semibold">
                                    {t("Quotation Submission Deadline *")}
                                </Label>
                                <Input
                                    id="rfq_closing_date"
                                    type="date"
                                    required
                                    value={createForm.data.closing_date}
                                    onChange={e => createForm.setData('closing_date', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="rfq_delivery_deadline" className="text-xs font-semibold">
                                    {t("Required Delivery Deadline")}
                                </Label>
                                <Input
                                    id="rfq_delivery_deadline"
                                    type="date"
                                    value={createForm.data.delivery_deadline}
                                    onChange={e => createForm.setData('delivery_deadline', e.target.value)}
                                />
                            </div>
                        </div>

                        {/* Terms */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label htmlFor="rfq_payment_terms" className="text-xs font-semibold">
                                    {t("Payment Terms")}
                                </Label>
                                <Input
                                    id="rfq_payment_terms"
                                    placeholder={t("e.g. Net 30 Days after GRN inspection")}
                                    value={createForm.data.payment_terms}
                                    onChange={e => createForm.setData('payment_terms', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="rfq_delivery_terms" className="text-xs font-semibold">
                                    {t("Delivery Terms (Incoterms)")}
                                </Label>
                                <Input
                                    id="rfq_delivery_terms"
                                    placeholder={t("e.g. DAP Dar es Salaam Warehouse")}
                                    value={createForm.data.delivery_terms}
                                    onChange={e => createForm.setData('delivery_terms', e.target.value)}
                                />
                            </div>
                        </div>

                        {/* Evaluation Notes */}
                        <div className="space-y-1">
                            <Label htmlFor="rfq_eval_notes" className="text-xs font-semibold">
                                {t("Evaluation Criteria / Tender Instructions")}
                            </Label>
                            <Textarea
                                id="rfq_eval_notes"
                                rows={2}
                                placeholder={t("e.g. Bidders must be certified manufacturers and provide minimum 12 months warranty.")}
                                value={createForm.data.evaluation_notes}
                                onChange={e => createForm.setData('evaluation_notes', e.target.value)}
                            />
                        </div>

                        {/* Line Items Section */}
                        <div className="space-y-2 pt-2">
                            <div className="flex items-center justify-between">
                                <Label className="text-sm font-bold text-foreground">
                                    {t("Requested Line Items & Specifications")}
                                </Label>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={addCreateItemRow}
                                    className="gap-1 text-xs"
                                >
                                    <Plus className="w-3.5 h-3.5" />
                                    {t("Add Item Row")}
                                </Button>
                            </div>

                            <div className="space-y-3">
                                {createForm.data.items.map((item, idx) => (
                                    <div
                                        key={idx}
                                        className="p-3 bg-muted/40 rounded-lg border border-border/60 space-y-2"
                                    >
                                        <div className="grid grid-cols-1 md:grid-cols-12 gap-2 items-center">
                                            <div className="md:col-span-5">
                                                <Label className="text-[11px] text-muted-foreground">{t("Catalog Item (Optional)")}</Label>
                                                <Select
                                                    value={item.product_id}
                                                    onValueChange={val => handleCreateItemProductSelect(idx, val)}
                                                >
                                                    <SelectTrigger className="h-8 text-xs">
                                                        <SelectValue placeholder={t("Select Catalog Item")} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="none">{t("-- Custom Item --")}</SelectItem>
                                                        {products.map(p => (
                                                            <SelectItem key={p.id} value={String(p.id)}>
                                                                {p.name} {p.sku ? `(${p.sku})` : ''}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="md:col-span-4">
                                                <Label className="text-[11px] text-muted-foreground">{t("Item Name *")}</Label>
                                                <Input
                                                    required
                                                    className="h-8 text-xs"
                                                    placeholder={t("Description")}
                                                    value={item.product_name}
                                                    onChange={e => {
                                                        const updated = [...createForm.data.items];
                                                        updated[idx].product_name = e.target.value;
                                                        createForm.setData('items', updated);
                                                    }}
                                                />
                                            </div>

                                            <div className="md:col-span-3 flex items-center justify-end">
                                                {createForm.data.items.length > 1 && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => removeCreateItemRow(idx)}
                                                        className="text-red-500 hover:text-red-700 h-8 px-2"
                                                    >
                                                        <Trash2 className="w-4 h-4 mr-1" />
                                                        <span className="text-xs">{t("Remove")}</span>
                                                    </Button>
                                                )}
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-2">
                                            <div>
                                                <Label className="text-[11px] text-muted-foreground">{t("Quantity *")}</Label>
                                                <Input
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                    required
                                                    className="h-8 text-xs font-semibold"
                                                    value={item.quantity}
                                                    onChange={e => {
                                                        const updated = [...createForm.data.items];
                                                        updated[idx].quantity = Number(e.target.value);
                                                        createForm.setData('items', updated);
                                                    }}
                                                />
                                            </div>

                                            <div>
                                                <Label className="text-[11px] text-muted-foreground">{t("Unit")}</Label>
                                                <Input
                                                    className="h-8 text-xs"
                                                    placeholder="units, pcs, kg"
                                                    value={item.unit}
                                                    onChange={e => {
                                                        const updated = [...createForm.data.items];
                                                        updated[idx].unit = e.target.value;
                                                        createForm.setData('items', updated);
                                                    }}
                                                />
                                            </div>

                                            <div>
                                                <Label className="text-[11px] text-muted-foreground">{t("Target Price (TZS)")}</Label>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    className="h-8 text-xs font-semibold"
                                                    value={item.target_unit_price}
                                                    onChange={e => {
                                                        const updated = [...createForm.data.items];
                                                        updated[idx].target_unit_price = Number(e.target.value);
                                                        createForm.setData('items', updated);
                                                    }}
                                                />
                                            </div>

                                            <div>
                                                <Label className="text-[11px] text-muted-foreground">{t("Line Total (TZS)")}</Label>
                                                <div className="h-8 flex items-center font-bold text-xs text-foreground px-2 bg-background rounded border border-input">
                                                    {(Number(item.quantity || 0) * Number(item.target_unit_price || 0)).toLocaleString()}
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <Label className="text-[11px] text-muted-foreground">{t("Specifications / Standards")}</Label>
                                            <Input
                                                className="h-7 text-xs"
                                                placeholder={t("e.g. High-grade rubber, ISO 9001 certified")}
                                                value={item.specifications}
                                                onChange={e => {
                                                    const updated = [...createForm.data.items];
                                                    updated[idx].specifications = e.target.value;
                                                    createForm.setData('items', updated);
                                                }}
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="flex justify-between items-center p-3 bg-muted/60 rounded-lg font-bold text-sm">
                                <span>{t("Total Target Procurement Value")}:</span>
                                <span className="text-indigo-600 dark:text-indigo-400">
                                    {createTargetTotal.toLocaleString()} TZS
                                </span>
                            </div>
                        </div>

                        <DialogFooter className="pt-4 border-t">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsCreateOpen(false)}
                            >
                                {t("Cancel")}
                            </Button>
                            <Button
                                type="submit"
                                disabled={createForm.processing}
                                className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                            >
                                {t("Create RFQ Tender")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 2: Edit RFQ Details */}
            <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
                <DialogContent className="max-w-xl">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Edit3 className="w-5 h-5 text-blue-600" />
                            {t("Edit RFQ Details")} â€” {activeRfq?.rfq_number}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleEditSubmit} className="space-y-4 pt-2">
                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Tender Title *")}</Label>
                            <Input
                                required
                                value={editForm.data.title}
                                onChange={e => editForm.setData('title', e.target.value)}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Closing Date *")}</Label>
                                <Input
                                    type="date"
                                    required
                                    value={editForm.data.closing_date}
                                    onChange={e => editForm.setData('closing_date', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Delivery Deadline")}</Label>
                                <Input
                                    type="date"
                                    value={editForm.data.delivery_deadline}
                                    onChange={e => editForm.setData('delivery_deadline', e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Payment Terms")}</Label>
                                <Input
                                    value={editForm.data.payment_terms}
                                    onChange={e => editForm.setData('payment_terms', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Delivery Terms")}</Label>
                                <Input
                                    value={editForm.data.delivery_terms}
                                    onChange={e => editForm.setData('delivery_terms', e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Evaluation Notes")}</Label>
                            <Textarea
                                rows={2}
                                value={editForm.data.evaluation_notes}
                                onChange={e => editForm.setData('evaluation_notes', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-3 border-t">
                            <Button type="button" variant="outline" onClick={() => setIsEditOpen(false)}>
                                {t("Cancel")}
                            </Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {t("Save Changes")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 3: Record Supplier Quotation Bid */}
            <Dialog open={isBidModalOpen} onOpenChange={setIsBidModalOpen}>
                <DialogContent className="max-w-2xl max-h-[85vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Plus className="w-5 h-5 text-emerald-600" />
                            {t("Record Supplier Quotation Bid")} â€” {activeRfq?.rfq_number}
                        </DialogTitle>
                        <p className="text-sm text-muted-foreground">
                            {t("Enter commercial quotation received from a registered supplier/vendor.")}
                        </p>
                    </DialogHeader>

                    <form onSubmit={handleBidSubmit} className="space-y-4 pt-2">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Supplier / Vendor *")}</Label>
                                <Select
                                    value={bidForm.data.vendor_id}
                                    onValueChange={val => bidForm.setData('vendor_id', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t("Select Supplier")} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {vendors.map(v => (
                                            <SelectItem key={v.id} value={String(v.id)}>
                                                {v.company_name || v.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Vendor Quote Reference *")}</Label>
                                <Input
                                    required
                                    placeholder="e.g. QUO-ABC-2026-01"
                                    value={bidForm.data.quotation_reference}
                                    onChange={e => bidForm.setData('quotation_reference', e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-3 gap-3">
                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Submission Date *")}</Label>
                                <Input
                                    type="date"
                                    required
                                    value={bidForm.data.submission_date}
                                    onChange={e => bidForm.setData('submission_date', e.target.value)}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Lead Time (Days) *")}</Label>
                                <Input
                                    type="number"
                                    min="1"
                                    required
                                    value={bidForm.data.delivery_lead_time_days}
                                    onChange={e => bidForm.setData('delivery_lead_time_days', Number(e.target.value))}
                                />
                            </div>

                            <div className="space-y-1">
                                <Label className="text-xs font-semibold">{t("Warranty (Months)")}</Label>
                                <Input
                                    type="number"
                                    min="0"
                                    value={bidForm.data.warranty_months}
                                    onChange={e => bidForm.setData('warranty_months', Number(e.target.value))}
                                />
                            </div>
                        </div>

                        {/* Itemized Pricing */}
                        <div className="space-y-2 pt-2">
                            <Label className="text-xs font-bold text-foreground">
                                {t("Quoted Unit Pricing per RFQ Line Item")}
                            </Label>
                            <div className="space-y-2.5">
                                {bidForm.data.items.map((bItem, idx) => (
                                    <div key={idx} className="p-3 bg-muted/40 rounded border border-border/50 grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                                        <div className="md:col-span-5">
                                            <div className="font-semibold text-xs">{bItem.product_name}</div>
                                            <div className="text-[11px] text-muted-foreground">Qty: {bItem.quantity}</div>
                                        </div>

                                        <div className="md:col-span-4">
                                            <Label className="text-[11px] text-muted-foreground">{t("Quoted Unit Price (TZS) *")}</Label>
                                            <Input
                                                type="number"
                                                min="0"
                                                required
                                                className="h-8 text-xs font-bold"
                                                value={bItem.unit_price}
                                                onChange={e => {
                                                    const updated = [...bidForm.data.items];
                                                    updated[idx].unit_price = Number(e.target.value);
                                                    bidForm.setData('items', updated);
                                                }}
                                            />
                                        </div>

                                        <div className="md:col-span-3 text-right">
                                            <span className="text-[11px] text-muted-foreground block">{t("Line Total")}</span>
                                            <span className="font-bold text-xs">
                                                {(Number(bItem.quantity || 0) * Number(bItem.unit_price || 0)).toLocaleString()} TZS
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="flex justify-between items-center p-3 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-300 rounded-lg font-bold text-sm">
                                <span>{t("Total Quoted Bid Amount")}:</span>
                                <span>{bidTotal.toLocaleString()} TZS</span>
                            </div>
                        </div>

                        <div className="space-y-1">
                            <Label className="text-xs font-semibold">{t("Vendor Comments / Conditions")}</Label>
                            <Textarea
                                rows={2}
                                placeholder={t("e.g. Free delivery included, installation available upon request.")}
                                value={bidForm.data.notes}
                                onChange={e => bidForm.setData('notes', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-3 border-t">
                            <Button type="button" variant="outline" onClick={() => setIsBidModalOpen(false)}>
                                {t("Cancel")}
                            </Button>
                            <Button type="submit" disabled={bidForm.processing} className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                                {t("Save Supplier Bid")}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 4: Interactive Comparative Evaluation Statement */}
            <Dialog open={isCompareModalOpen} onOpenChange={setIsCompareModalOpen}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <DialogTitle className="flex items-center gap-2 text-xl font-bold">
                                    <Scale className="w-5 h-5 text-indigo-600" />
                                    {t("Automatic Comparative Evaluation Statement")}
                                </DialogTitle>
                                <p className="text-xs text-muted-foreground mt-0.5">
                                    {t("Procurement Committee Bid Evaluation Matrix & PO Awarding Engine (SRS 3.8.3)")}
                                </p>
                            </div>
                            <Badge variant="outline" className="font-mono text-xs">
                                {activeRfq?.rfq_number}
                            </Badge>
                        </div>
                    </DialogHeader>

                    {activeRfq && (
                        <div className="space-y-5 pt-2">
                            {/* Tender Summary Bar */}
                            <div className="p-3 bg-muted/50 rounded-lg border flex flex-wrap justify-between items-center gap-3 text-xs">
                                <div>
                                    <span className="text-muted-foreground">{t("Tender Title")}:</span>
                                    <span className="font-semibold ml-1.5 text-foreground">{activeRfq.title}</span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground">{t("Submission Deadline")}:</span>
                                    <span className="font-semibold ml-1.5 text-foreground">
                                        {activeRfq.closing_date ? activeRfq.closing_date.split('T')[0] : 'N/A'}
                                    </span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground">{t("Target Budget")}:</span>
                                    <span className="font-bold ml-1.5 text-indigo-600 dark:text-indigo-400">
                                        {Number(
                                            activeRfq.items.reduce((s, it) => s + (Number(it.quantity) * Number(it.target_unit_price || 0)), 0)
                                        ).toLocaleString()} TZS
                                    </span>
                                </div>
                            </div>

                            {/* Bids Evaluation Grid */}
                            {activeRfq.quotations.length === 0 ? (
                                <div className="p-8 text-center bg-muted/20 rounded-lg border border-dashed text-muted-foreground space-y-2">
                                    <Scale className="w-8 h-8 mx-auto text-muted-foreground/60" />
                                    <p className="font-medium">{t("No supplier bids recorded for this tender yet.")}</p>
                                    <Button
                                        size="sm"
                                        onClick={() => {
                                            setIsCompareModalOpen(false);
                                            openBidModal(activeRfq);
                                        }}
                                        className="bg-emerald-600 hover:bg-emerald-700 text-white"
                                    >
                                        <Plus className="w-4 h-4 mr-1" />
                                        {t("Record First Supplier Bid")}
                                    </Button>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        {activeRfq.quotations.map(quote => {
                                            const isLowest = quote.total_bid_amount === Math.min(...activeRfq.quotations.map(q => Number(q.total_bid_amount)));
                                            const isFastest = quote.delivery_lead_time_days === Math.min(...activeRfq.quotations.map(q => Number(q.delivery_lead_time_days)));
                                            const isWinner = quote.status === 'awarded' || activeRfq.awarded_bid_id === quote.id;

                                            return (
                                                <div
                                                    key={quote.id}
                                                    className={`p-4 rounded-xl border relative transition-all ${
                                                        isWinner
                                                            ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-500 shadow-md ring-2 ring-emerald-500/20'
                                                            : isLowest
                                                            ? 'bg-indigo-50/50 dark:bg-indigo-950/20 border-indigo-300 dark:border-indigo-800'
                                                            : 'bg-card border-border/70'
                                                    }`}
                                                >
                                                    {/* Badges */}
                                                    <div className="flex flex-wrap gap-1 mb-2">
                                                        {isWinner && (
                                                            <Badge className="bg-emerald-600 text-white text-[10px] gap-1">
                                                                <Check className="w-3 h-3" />
                                                                {t("Awarded Winner")}
                                                            </Badge>
                                                        )}
                                                        {isLowest && !isWinner && (
                                                            <Badge className="bg-indigo-600 text-white text-[10px] gap-1">
                                                                <TrendingDown className="w-3 h-3" />
                                                                {t("Lowest Price")}
                                                            </Badge>
                                                        )}
                                                        {isFastest && (
                                                            <Badge variant="outline" className="border-blue-400 text-blue-600 text-[10px] gap-1">
                                                                <Truck className="w-3 h-3" />
                                                                {quote.delivery_lead_time_days} {t("Days Lead")}
                                                            </Badge>
                                                        )}
                                                    </div>

                                                    <h3 className="font-bold text-sm text-foreground truncate" title={quote.vendor_name}>
                                                        {quote.vendor_name}
                                                    </h3>
                                                    <div className="text-xs text-muted-foreground font-mono">
                                                        {quote.quotation_reference}
                                                    </div>

                                                    <div className="mt-3 pt-3 border-t border-border/60 space-y-1.5 text-xs">
                                                        <div className="flex justify-between items-baseline">
                                                            <span className="text-muted-foreground">{t("Total Quoted")}:</span>
                                                            <span className="text-base font-extrabold text-foreground">
                                                                {Number(quote.total_bid_amount).toLocaleString()} TZS
                                                            </span>
                                                        </div>
                                                        <div className="flex justify-between">
                                                            <span className="text-muted-foreground">{t("Delivery Time")}:</span>
                                                            <span className="font-semibold">{quote.delivery_lead_time_days} {t("business days")}</span>
                                                        </div>
                                                        <div className="flex justify-between">
                                                            <span className="text-muted-foreground">{t("Warranty")}:</span>
                                                            <span className="font-semibold">{quote.warranty_months} {t("months")}</span>
                                                        </div>
                                                        <div className="flex justify-between">
                                                            <span className="text-muted-foreground">{t("Payment Terms")}:</span>
                                                            <span className="font-medium truncate max-w-[120px]">{quote.payment_terms || 'Net 30'}</span>
                                                        </div>
                                                    </div>

                                                    {/* Award Button */}
                                                    <div className="mt-4 pt-3 border-t border-border/60">
                                                        {activeRfq.status === 'awarded' ? (
                                                            isWinner ? (
                                                                <div className="text-center text-xs font-bold text-emerald-600 flex items-center justify-center gap-1 py-1">
                                                                    <Award className="w-4 h-4" />
                                                                    {t("Awarded & Committed")}
                                                                </div>
                                                            ) : (
                                                                <div className="text-center text-xs text-muted-foreground py-1">
                                                                    {t("Rejected")}
                                                                </div>
                                                            )
                                                        ) : (
                                                            <Button
                                                                size="sm"
                                                                className={`w-full text-xs font-semibold gap-1.5 ${
                                                                    isLowest
                                                                        ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                                                                        : 'bg-primary hover:bg-primary/90 text-primary-foreground'
                                                                }`}
                                                                onClick={() => handleAwardQuotation(quote.id)}
                                                            >
                                                                <Award className="w-3.5 h-3.5" />
                                                                {t("Award Tender & Issue PO")}
                                                            </Button>
                                                        )}
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>

                                    {/* Line Item Side-by-Side Breakdown Table */}
                                    <div className="mt-4 pt-2">
                                        <Label className="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-2 block">
                                            {t("Line Item Comparative Price Schedule (TZS)")}
                                        </Label>
                                        <div className="rounded-lg border overflow-x-auto text-xs">
                                            <Table>
                                                <TableHeader>
                                                    <TableRow className="bg-muted/40">
                                                        <TableHead className="font-semibold">{t("Item & Qty")}</TableHead>
                                                        <TableHead className="font-semibold text-right">{t("Target Price")}</TableHead>
                                                        {activeRfq.quotations.map(q => (
                                                            <TableHead key={q.id} className="font-semibold text-right">
                                                                {q.vendor_name}
                                                            </TableHead>
                                                        ))}
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {activeRfq.items.map(rItem => (
                                                        <TableRow key={rItem.id}>
                                                            <TableCell>
                                                                <span className="font-semibold">{rItem.product_name}</span>
                                                                <span className="text-muted-foreground block text-[11px]">
                                                                    {rItem.quantity} {rItem.unit}
                                                                </span>
                                                            </TableCell>
                                                            <TableCell className="text-right font-medium text-muted-foreground">
                                                                {Number(rItem.target_unit_price).toLocaleString()}
                                                            </TableCell>
                                                            {activeRfq.quotations.map(q => {
                                                                const qItem = q.items?.find(it => it.rfq_item_id === rItem.id);
                                                                return (
                                                                    <TableCell key={q.id} className="text-right font-bold">
                                                                        {qItem ? Number(qItem.unit_price).toLocaleString() : '-'}
                                                                    </TableCell>
                                                                );
                                                            })}
                                                        </TableRow>
                                                    ))}
                                                </TableBody>
                                            </Table>
                                        </div>
                                    </div>
                                </div>
                            )}

                            <DialogFooter className="pt-3 border-t">
                                <Button variant="outline" onClick={() => setIsCompareModalOpen(false)}>
                                    {t("Close Matrix")}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            {/* MODAL 5: Printable RFQ Voucher & Comparative Slip */}
            <Dialog open={isPrintModalOpen} onOpenChange={setIsPrintModalOpen}>
                <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Printer className="w-5 h-5 text-indigo-600" />
                            {t("Print Request for Quotation Invitation Slip")}
                        </DialogTitle>
                    </DialogHeader>

                    {activeRfq && (
                        <div className="space-y-4 p-4 border rounded-xl bg-white text-slate-900 shadow-sm print:m-0 print:border-none">
                            {/* Slip Header */}
                            <div className="flex justify-between items-start border-b pb-4">
                                <div>
                                    <h2 className="text-xl font-extrabold text-slate-900 tracking-tight">
                                        MUTAMARK ENTERPRISES LTD
                                    </h2>
                                    <p className="text-xs text-slate-600">
                                        Plot 42, Nyerere Road Industrial Park, Dar es Salaam, Tanzania
                                    </p>
                                    <p className="text-xs text-slate-600">
                                        TIN: 104-892-411 | VRN: 40019283-Z | info@mutamark.co.tz
                                    </p>
                                </div>
                                <div className="text-right">
                                    <span className="text-xs uppercase tracking-widest font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded border border-indigo-200 block mb-1">
                                        TENDER INVITATION / RFQ
                                    </span>
                                    <span className="font-mono text-sm font-bold block">{activeRfq.rfq_number}</span>
                                    <span className="text-xs text-slate-500">
                                        Date: {activeRfq.issue_date ? activeRfq.issue_date.split('T')[0] : 'N/A'}
                                    </span>
                                </div>
                            </div>

                            {/* Particulars */}
                            <div className="grid grid-cols-2 gap-4 text-xs py-2 bg-slate-50 p-3 rounded-lg border">
                                <div>
                                    <span className="text-slate-500 block">{t("Tender Subject")}:</span>
                                    <span className="font-bold text-slate-900">{activeRfq.title}</span>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">{t("Submission Deadline")}:</span>
                                    <span className="font-bold text-red-600">
                                        {activeRfq.closing_date ? activeRfq.closing_date.split('T')[0] : 'N/A'}
                                    </span>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">{t("Delivery Terms")}:</span>
                                    <span className="font-semibold text-slate-900">{activeRfq.delivery_terms || 'DAP'}</span>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">{t("Payment Terms")}:</span>
                                    <span className="font-semibold text-slate-900">{activeRfq.payment_terms || 'Net 30 Days'}</span>
                                </div>
                            </div>

                            {/* Item Schedule */}
                            <div>
                                <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                    {t("Bill of Quantities & Technical Specifications")}
                                </h3>
                                <div className="border rounded overflow-hidden">
                                    <table className="w-full text-xs text-left">
                                        <thead className="bg-slate-100 text-slate-700 font-bold border-b">
                                            <tr>
                                                <th className="p-2">#</th>
                                                <th className="p-2">{t("Item Description")}</th>
                                                <th className="p-2">{t("Specifications")}</th>
                                                <th className="p-2 text-right">{t("Required Qty")}</th>
                                                <th className="p-2">{t("Unit")}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y text-slate-800">
                                            {activeRfq.items.map((it, idx) => (
                                                <tr key={it.id}>
                                                    <td className="p-2 font-mono">{idx + 1}</td>
                                                    <td className="p-2 font-semibold">{it.product_name}</td>
                                                    <td className="p-2 text-slate-600">{it.specifications || '-'}</td>
                                                    <td className="p-2 text-right font-bold">{it.quantity}</td>
                                                    <td className="p-2">{it.unit}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Verification Block */}
                            <div className="pt-4 border-t flex justify-between items-center text-xs text-slate-500">
                                <div>
                                    <p className="font-semibold text-slate-800">Tender Evaluation Committee</p>
                                    <p>Authorized Procurement Department</p>
                                </div>
                                <div className="text-right font-mono text-[10px]">
                                    <ShieldCheck className="w-4 h-4 text-emerald-600 inline mr-1" />
                                    <span>HASH: {activeRfq.verification_hash ? activeRfq.verification_hash.substring(0, 20) + '...' : 'SECURE-VERIFIED'}</span>
                                </div>
                            </div>

                            <DialogFooter className="pt-3 border-t">
                                <Button variant="outline" onClick={() => setIsPrintModalOpen(false)}>
                                    {t("Close")}
                                </Button>
                                <Button
                                    onClick={() => window.print()}
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold gap-1"
                                >
                                    <Printer className="w-4 h-4" />
                                    {t("Print Voucher")}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            {/* MODAL 6: Delete Confirmation */}
            <ConfirmationDialog
                open={isDeleteOpen}
                onOpenChange={setIsDeleteOpen}
                title={t("Delete Request for Quotation?")}
                message={t("Are you sure you want to delete this tender? All recorded supplier bids and comparative records will be permanently removed.")}
                onConfirm={confirmDelete}
            />
        </AuthenticatedLayout>
    );
}
