import React, { useState, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
    ShoppingCart,
    FileCheck2,
    Building2,
    Clock,
    AlertCircle,
    CheckCircle,
    XCircle,
    Plus,
    Search,
    ArrowRight,
    Printer,
    QrCode,
    DollarSign,
    Layers,
    Trash2,
    Edit3,
    Send,
    MoreVertical,
    FileText,
    CheckCircle2,
    ShieldCheck,
    Briefcase,
    Calendar,
    UserCheck,
} from 'lucide-react';

interface RequisitionItem {
    id?: number;
    product_id?: number | null;
    product_name: string;
    sku?: string;
    quantity_requested: number;
    estimated_unit_cost: number;
    estimated_total_cost?: number;
    unit?: string;
    specifications?: string;
    product?: {
        id: number;
        name: string;
        sku?: string;
    };
}

interface PurchaseRequisition {
    id: number;
    requisition_number: string;
    requisition_date: string;
    required_by_date?: string;
    requester_id: number;
    department: string;
    warehouse_id: number;
    preferred_vendor_id?: number;
    priority: 'low' | 'medium' | 'high' | 'urgent';
    priority_label: string;
    status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'converted' | 'cancelled';
    status_label: string;
    justification: string;
    estimated_total_cost: number;
    approved_by?: number;
    approved_at?: string;
    rejection_reason?: string;
    converted_invoice_id?: number;
    converted_at?: string;
    verification_hash?: string;
    items: RequisitionItem[];
    requester?: {
        id: number;
        name: string;
        email?: string;
    };
    warehouse?: {
        id: number;
        name: string;
        city?: string;
    };
    preferred_vendor?: {
        id: number;
        name: string;
        email?: string;
    };
    approver?: {
        id: number;
        name: string;
    };
    converted_invoice?: {
        id: number;
        invoice_number: string;
        total_amount: number;
    };
}

interface Props {
    requisitions: PurchaseRequisition[];
    kpi: {
        total_requisitions: number;
        pending_approval_count: number;
        approved_count: number;
        converted_count: number;
        total_value_tzs: number;
    };
    filters: {
        status?: string;
        department?: string;
        priority?: string;
        search?: string;
    };
    warehouses: Array<{ id: number; name: string; address?: string; city?: string }>;
    vendors: Array<{ id: number; name: string; email?: string; mobile_no?: string }>;
    staff_users: Array<{ id: number; name: string; email?: string }>;
    products: Array<{ id: number; name: string; sku?: string; purchase_price: number; unit?: string }>;
    departments: string[];
}

export default function PurchaseRequisitionsIndex({
    requisitions,
    kpi,
    filters,
    warehouses,
    vendors,
    staff_users,
    products,
    departments,
}: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedDepartment, setSelectedDepartment] = useState(filters.department || 'all');
    const [selectedStatusTab, setSelectedStatusTab] = useState('all');

    // Modals
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [isApproveOpen, setIsApproveOpen] = useState(false);
    const [isRejectOpen, setIsRejectOpen] = useState(false);
    const [isConvertOpen, setIsConvertOpen] = useState(false);
    const [isSlipOpen, setIsSlipOpen] = useState(false);
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);

    const [activeReq, setActiveReq] = useState<PurchaseRequisition | null>(null);
    const [rejectionReason, setRejectionReason] = useState('');

    // Create Form
    const createForm = useForm({
        requisition_date: new Date().toISOString().split('T')[0],
        required_by_date: '',
        requester_id: staff_users[0]?.id ? String(staff_users[0].id) : '',
        department: departments[0] || 'Operations',
        warehouse_id: warehouses[0]?.id ? String(warehouses[0].id) : '',
        preferred_vendor_id: '',
        priority: 'medium',
        status: 'draft',
        justification: '',
        items: [
            {
                product_id: products[0]?.id || 0,
                product_name: products[0]?.name || '',
                sku: products[0]?.sku || '',
                quantity_requested: 1,
                estimated_unit_cost: products[0]?.purchase_price || 0,
                unit: products[0]?.unit || 'units',
                specifications: '',
            },
        ],
    });

    // Edit Form
    const editForm = useForm({
        requisition_date: '',
        required_by_date: '',
        requester_id: '',
        department: '',
        warehouse_id: '',
        preferred_vendor_id: '',
        priority: 'medium',
        justification: '',
        items: [] as any[],
    });

    // Filtered Requisitions
    const filteredRequisitions = useMemo(() => {
        return requisitions.filter((req) => {
            const matchesSearch =
                searchTerm === '' ||
                req.requisition_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
                req.justification.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (req.requester?.name && req.requester.name.toLowerCase().includes(searchTerm.toLowerCase())) ||
                req.items.some((i) => i.product_name.toLowerCase().includes(searchTerm.toLowerCase()));

            const matchesDept =
                selectedDepartment === 'all' || req.department === selectedDepartment;

            let matchesTab = true;
            if (selectedStatusTab === 'submitted') {
                matchesTab = req.status === 'submitted';
            } else if (selectedStatusTab === 'approved') {
                matchesTab = req.status === 'approved';
            } else if (selectedStatusTab === 'converted') {
                matchesTab = ['converted', 'converted_to_po', 'converted_to_rfq'].includes(req.status);
            } else if (selectedStatusTab === 'draft') {
                matchesTab = req.status === 'draft';
            }

            return matchesSearch && matchesDept && matchesTab;
        });
    }, [requisitions, searchTerm, selectedDepartment, selectedStatusTab]);

    // Live total for create form
    const liveCreateTotal = useMemo(() => {
        return createForm.data.items.reduce((sum, item) => {
            return sum + (Number(item.quantity_requested) || 0) * (Number(item.estimated_unit_cost) || 0);
        }, 0);
    }, [createForm.data.items]);

    const liveEditTotal = useMemo(() => {
        return editForm.data.items.reduce((sum, item) => {
            return sum + (Number(item.quantity_requested) || 0) * (Number(item.estimated_unit_cost) || 0);
        }, 0);
    }, [editForm.data.items]);

    // Dynamic item row handlers
    const handleAddItem = (formInstance: any) => {
        formInstance.setData('items', [
            ...formInstance.data.items,
            {
                product_id: products[0]?.id || 0,
                product_name: products[0]?.name || '',
                sku: products[0]?.sku || '',
                quantity_requested: 1,
                estimated_unit_cost: products[0]?.purchase_price || 0,
                unit: products[0]?.unit || 'units',
                specifications: '',
            },
        ]);
    };

    const handleRemoveItem = (index: number, formInstance: any) => {
        if (formInstance.data.items.length <= 1) return;
        const newItems = [...formInstance.data.items];
        newItems.splice(index, 1);
        formInstance.setData('items', newItems);
    };

    const handleItemChange = (index: number, field: string, value: any, formInstance: any) => {
        const newItems = [...formInstance.data.items];
        newItems[index] = { ...newItems[index], [field]: value };

        if (field === 'product_id') {
            const p = products.find((prod) => prod.id === Number(value));
            if (p) {
                newItems[index].product_name = p.name;
                newItems[index].sku = p.sku || '';
                newItems[index].estimated_unit_cost = p.purchase_price || 0;
                if (p.unit) newItems[index].unit = p.unit;
            }
        }

        formInstance.setData('items', newItems);
    };

    // Form Submissions
    const submitCreate = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post(route('procurement.purchase-requisitions.store'), {
            onSuccess: () => {
                setIsCreateOpen(false);
                createForm.reset();
            },
        });
    };

    const openEditModal = (req: PurchaseRequisition) => {
        setActiveReq(req);
        editForm.setData({
            requisition_date: req.requisition_date,
            required_by_date: req.required_by_date || '',
            requester_id: String(req.requester_id),
            department: req.department,
            warehouse_id: String(req.warehouse_id),
            preferred_vendor_id: req.preferred_vendor_id ? String(req.preferred_vendor_id) : '',
            priority: req.priority,
            justification: req.justification,
            items: req.items.map((i) => ({
                id: i.id,
                product_id: i.product_id || 0,
                product_name: i.product_name,
                sku: i.sku || '',
                quantity_requested: i.quantity_requested,
                estimated_unit_cost: i.estimated_unit_cost,
                unit: i.unit || 'units',
                specifications: i.specifications || '',
            })),
        });
        setIsEditOpen(true);
    };

    const submitEdit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeReq) return;
        editForm.put(route('procurement.purchase-requisitions.update', activeReq.id), {
            onSuccess: () => {
                setIsEditOpen(false);
                setActiveReq(null);
            },
        });
    };

    const handleSubmitForApproval = (req: PurchaseRequisition) => {
        router.post(route('procurement.purchase-requisitions.submit', req.id));
    };

    const openApproveModal = (req: PurchaseRequisition) => {
        setActiveReq(req);
        setIsApproveOpen(true);
    };

    const submitApprove = () => {
        if (!activeReq) return;
        router.post(
            route('procurement.purchase-requisitions.approve', activeReq.id),
            {},
            {
                onSuccess: () => {
                    setIsApproveOpen(false);
                    setActiveReq(null);
                },
            }
        );
    };

    const openRejectModal = (req: PurchaseRequisition) => {
        setActiveReq(req);
        setRejectionReason('');
        setIsRejectOpen(true);
    };

    const submitReject = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeReq) return;
        router.post(
            route('procurement.purchase-requisitions.reject', activeReq.id),
            { rejection_reason: rejectionReason },
            {
                onSuccess: () => {
                    setIsRejectOpen(false);
                    setActiveReq(null);
                },
            }
        );
    };

    const openConvertModal = (req: PurchaseRequisition) => {
        setActiveReq(req);
        setIsConvertOpen(true);
    };

    const submitConvert = () => {
        if (!activeReq) return;
        router.post(
            route('procurement.purchase-requisitions.convert-to-invoice', activeReq.id),
            {},
            {
                onSuccess: () => {
                    setIsConvertOpen(false);
                    setActiveReq(null);
                },
            }
        );
    };

    const openDeleteModal = (req: PurchaseRequisition) => {
        setActiveReq(req);
        setIsDeleteOpen(true);
    };

    const submitDelete = () => {
        if (!activeReq) return;
        router.delete(route('procurement.purchase-requisitions.destroy', activeReq.id), {
            onSuccess: () => {
                setIsDeleteOpen(false);
                setActiveReq(null);
            },
        });
    };

    const openSlipModal = (req: PurchaseRequisition) => {
        setActiveReq(req);
        setIsSlipOpen(true);
    };

    // Badge styling helpers
    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'draft':
                return <Badge variant="outline" className="border-slate-400 text-slate-600 bg-slate-50 dark:bg-slate-900">Draft</Badge>;
            case 'submitted':
                return <Badge className="bg-amber-500 hover:bg-amber-600 text-white">Pending Approval</Badge>;
            case 'approved':
                return <Badge className="bg-emerald-600 hover:bg-emerald-700 text-white">Approved (Ready for PO)</Badge>;
            case 'converted':
            case 'converted_to_po':
            case 'converted_to_rfq':
                return <Badge className="bg-indigo-600 hover:bg-indigo-700 text-white">Converted to Bill</Badge>;
            case 'rejected':
                return <Badge variant="destructive">Rejected</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    const getPriorityBadge = (priority: string) => {
        switch (priority) {
            case 'urgent':
                return <Badge className="bg-red-600 text-white">Urgent</Badge>;
            case 'high':
                return <Badge className="bg-orange-500 text-white">High</Badge>;
            case 'medium':
                return <Badge variant="outline" className="border-amber-400 text-amber-600 bg-amber-50 dark:bg-amber-950/20">Medium</Badge>;
            case 'low':
                return <Badge variant="secondary">Low</Badge>;
            default:
                return <Badge variant="secondary">{priority}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Purchase Requisitions & Material Requests" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 rounded-2xl shadow-xl text-white border border-indigo-800/40">
                    <div className="space-y-1">
                        <div className="flex items-center gap-3">
                            <div className="p-2.5 bg-indigo-500/20 rounded-xl border border-indigo-400/30">
                                <FileCheck2 className="w-7 h-7 text-indigo-400" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                                    Purchase Requisitions & Material Requests
                                    <Badge className="bg-indigo-500/20 text-indigo-300 border-indigo-400/30">
                                        SRS Module 8: Procure-to-Pay
                                    </Badge>
                                </h1>
                                <p className="text-sm text-slate-300">
                                    Internal Department Requests, Budget Justifications, Multi-Tier Approvals & Automated Purchase Order Conversion
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            onClick={() => setIsCreateOpen(true)}
                            className="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-500/20"
                        >
                            <Plus className="w-4 h-4 mr-2" />
                            New Requisition
                        </Button>
                    </div>
                </div>

                {/* KPI Metrics Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card className="border-border shadow-sm bg-card hover:border-indigo-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Requisitions
                            </CardTitle>
                            <div className="p-2 bg-blue-500/10 text-blue-500 rounded-lg">
                                <Layers className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{kpi.total_requisitions}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Logged across departments
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border shadow-sm bg-card hover:border-indigo-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Pending Approval
                            </CardTitle>
                            <div className="p-2 bg-amber-500/10 text-amber-500 rounded-lg">
                                <Clock className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-500">{kpi.pending_approval_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Awaiting management review
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border shadow-sm bg-card hover:border-indigo-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Approved & Ready for PO
                            </CardTitle>
                            <div className="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg">
                                <CheckCircle className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-500">{kpi.approved_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Ready for supplier order conversion
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border shadow-sm bg-card hover:border-indigo-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Requisition Value
                            </CardTitle>
                            <div className="p-2 bg-purple-500/10 text-purple-500 rounded-lg">
                                <DollarSign className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {Number(kpi.total_value_tzs).toLocaleString()} TZS
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Cumulative estimated procurement value
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter and Search Bar */}
                <Card className="border-border shadow-sm">
                    <CardContent className="p-4">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="flex items-center gap-3 w-full md:w-auto flex-1">
                                <div className="relative flex-1 max-w-sm">
                                    <Search className="w-4 h-4 absolute left-3 top-3 text-muted-foreground" />
                                    <Input
                                        placeholder="Search by PR#, justification, requester..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-9"
                                    />
                                </div>

                                <div className="w-52">
                                    <select
                                        value={selectedDepartment}
                                        onChange={(e) => setSelectedDepartment(e.target.value)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2"
                                    >
                                        <option value="all">All Departments</option>
                                        {departments.map((dept) => (
                                            <option key={dept} value={dept}>
                                                {dept}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <Tabs value={selectedStatusTab} onValueChange={setSelectedStatusTab} className="w-full md:w-auto">
                                <TabsList>
                                    <TabsTrigger value="all">All</TabsTrigger>
                                    <TabsTrigger value="submitted">Pending Approval</TabsTrigger>
                                    <TabsTrigger value="approved">Approved</TabsTrigger>
                                    <TabsTrigger value="converted">Converted</TabsTrigger>
                                    <TabsTrigger value="draft">Drafts</TabsTrigger>
                                </TabsList>
                            </Tabs>
                        </div>
                    </CardContent>
                </Card>

                {/* Requisitions Table */}
                <Card className="border-border shadow-sm">
                    <CardHeader className="px-6 py-4 border-b">
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg">Purchase Requisitions Ledger</CardTitle>
                                <CardDescription>
                                    Sequential material requests complying with Appendix A (`PR-YYYY-#####`)
                                </CardDescription>
                            </div>
                            <span className="text-xs text-muted-foreground">
                                Showing {filteredRequisitions.length} of {requisitions.length} records
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>PR Number</TableHead>
                                        <TableHead>Department & Requester</TableHead>
                                        <TableHead>Target Warehouse</TableHead>
                                        <TableHead>Priority</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">Estimated Cost (TZS)</TableHead>
                                        <TableHead>PO Reference</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredRequisitions.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={8} className="text-center py-8 text-muted-foreground">
                                                No purchase requisitions found matching criteria.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        filteredRequisitions.map((req) => (
                                            <TableRow key={req.id} className="hover:bg-muted/30">
                                                <TableCell className="font-semibold text-primary">
                                                    <div className="flex items-center gap-2">
                                                        <FileText className="w-4 h-4 text-indigo-500" />
                                                        <span>{req.requisition_number}</span>
                                                    </div>
                                                    <span className="text-xs text-muted-foreground block">
                                                        {req.requisition_date}
                                                    </span>
                                                </TableCell>

                                                <TableCell>
                                                    <div className="text-sm font-semibold flex items-center gap-1">
                                                        <Briefcase className="w-3.5 h-3.5 text-muted-foreground" />
                                                        {req.department}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        By: {req.requester?.name || 'Staff User'}
                                                    </div>
                                                </TableCell>

                                                <TableCell>
                                                    <div className="flex items-center gap-1 text-sm">
                                                        <Building2 className="w-3.5 h-3.5 text-muted-foreground" />
                                                        <span>{req.warehouse?.name || 'Warehouse'}</span>
                                                    </div>
                                                    {req.preferred_vendor && (
                                                        <span className="text-xs text-muted-foreground block">
                                                            Vendor: {req.preferred_vendor.name}
                                                        </span>
                                                    )}
                                                </TableCell>

                                                <TableCell>
                                                    {getPriorityBadge(req.priority)}
                                                </TableCell>

                                                <TableCell>
                                                    {getStatusBadge(req.status)}
                                                </TableCell>

                                                <TableCell className="text-right font-bold text-slate-900 dark:text-slate-100">
                                                    <div>{Number(req.estimated_total_cost).toLocaleString()} TZS</div>
                                                    <span className="text-xs text-muted-foreground font-normal">
                                                        {req.items.length} item line{req.items.length > 1 ? 's' : ''}
                                                    </span>
                                                </TableCell>

                                                <TableCell>
                                                    {req.converted_invoice ? (
                                                        <Badge variant="outline" className="font-mono text-xs border-indigo-300 text-indigo-600 bg-indigo-50 dark:bg-indigo-950/30">
                                                            {req.converted_invoice.invoice_number}
                                                        </Badge>
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
                                                        <DropdownMenuContent align="end" className="w-52">
                                                            <DropdownMenuItem onClick={() => openSlipModal(req)}>
                                                                <Printer className="w-4 h-4 mr-2 text-indigo-500" />
                                                                View & Print Voucher
                                                            </DropdownMenuItem>

                                                            {req.status === 'draft' && (
                                                                <DropdownMenuItem onClick={() => handleSubmitForApproval(req)}>
                                                                    <Send className="w-4 h-4 mr-2 text-amber-500" />
                                                                    Submit for Approval
                                                                </DropdownMenuItem>
                                                            )}

                                                            {req.status === 'submitted' && (
                                                                <>
                                                                    <DropdownMenuItem onClick={() => openApproveModal(req)}>
                                                                        <CheckCircle className="w-4 h-4 mr-2 text-emerald-500" />
                                                                        Approve Requisition
                                                                    </DropdownMenuItem>
                                                                    <DropdownMenuItem onClick={() => openRejectModal(req)}>
                                                                        <XCircle className="w-4 h-4 mr-2 text-red-500" />
                                                                        Reject Requisition
                                                                    </DropdownMenuItem>
                                                                </>
                                                            )}

                                                            {req.status === 'approved' && (
                                                                <DropdownMenuItem onClick={() => openConvertModal(req)}>
                                                                    <ArrowRight className="w-4 h-4 mr-2 text-indigo-500" />
                                                                    Convert to Purchase Bill
                                                                </DropdownMenuItem>
                                                            )}

                                                            {['draft', 'submitted'].includes(req.status) && (
                                                                <DropdownMenuItem onClick={() => openEditModal(req)}>
                                                                    <Edit3 className="w-4 h-4 mr-2 text-blue-500" />
                                                                    Edit Details
                                                                </DropdownMenuItem>
                                                            )}

                                                            {!['converted', 'converted_to_po', 'converted_to_rfq'].includes(req.status) && (
                                                                <>
                                                                    <DropdownMenuSeparator />
                                                                    <DropdownMenuItem
                                                                        onClick={() => openDeleteModal(req)}
                                                                        className="text-red-600"
                                                                    >
                                                                        <Trash2 className="w-4 h-4 mr-2" />
                                                                        Delete
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
                    </CardContent>
                </Card>

                {/* MODAL 1: Create Purchase Requisition */}
                <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                    <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-xl">
                                <FileCheck2 className="w-5 h-5 text-indigo-500" />
                                New Purchase Requisition
                            </DialogTitle>
                            <DialogDescription>
                                Submit an internal procurement requisition with estimated budget and line item specifications.
                            </DialogDescription>
                        </DialogHeader>

                        <form onSubmit={submitCreate} className="space-y-6 pt-2">
                            {/* Section 1: Department & Meta */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-muted/30 rounded-xl border">
                                <div>
                                    <Label>Department *</Label>
                                    <select
                                        value={createForm.data.department}
                                        onChange={(e) => createForm.setData('department', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {departments.map((dept) => (
                                            <option key={dept} value={dept}>
                                                {dept}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <Label>Requested By (Staff / Lead) *</Label>
                                    <select
                                        value={createForm.data.requester_id}
                                        onChange={(e) => createForm.setData('requester_id', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {staff_users.map((u) => (
                                            <option key={u.id} value={u.id}>
                                                {u.name} ({u.email})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <Label>Target Warehouse *</Label>
                                    <select
                                        value={createForm.data.warehouse_id}
                                        onChange={(e) => createForm.setData('warehouse_id', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {warehouses.map((wh) => (
                                            <option key={wh.id} value={wh.id}>
                                                {wh.name} ({wh.city || 'Tanzania'})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            {/* Section 2: Dates, Priority, Preferred Vendor */}
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <Label>Requisition Date *</Label>
                                    <Input
                                        type="date"
                                        value={createForm.data.requisition_date}
                                        onChange={(e) => createForm.setData('requisition_date', e.target.value)}
                                        required
                                        className="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label>Required By Date</Label>
                                    <Input
                                        type="date"
                                        value={createForm.data.required_by_date}
                                        onChange={(e) => createForm.setData('required_by_date', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label>Priority *</Label>
                                    <select
                                        value={createForm.data.priority}
                                        onChange={(e) => createForm.setData('priority', e.target.value as any)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>

                                <div>
                                    <Label>Preferred Vendor (Optional)</Label>
                                    <select
                                        value={createForm.data.preferred_vendor_id}
                                        onChange={(e) => createForm.setData('preferred_vendor_id', e.target.value)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        <option value="">-- No Preferred Vendor --</option>
                                        {vendors.map((v) => (
                                            <option key={v.id} value={v.id}>
                                                {v.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div>
                                <Label>Expenditure Justification / Purpose *</Label>
                                <Textarea
                                    placeholder="Explain why these goods or materials are needed for operational activities..."
                                    value={createForm.data.justification}
                                    onChange={(e) => createForm.setData('justification', e.target.value)}
                                    required
                                    className="mt-1"
                                    rows={2}
                                />
                            </div>

                            {/* Section 3: Line Items */}
                            <div className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <Label className="text-base font-semibold">Requested Items & Materials</Label>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleAddItem(createForm)}
                                    >
                                        <Plus className="w-3.5 h-3.5 mr-1" /> Add Line Item
                                    </Button>
                                </div>

                                <div className="border rounded-lg overflow-hidden">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="bg-muted/40">
                                                <TableHead>Product / Description</TableHead>
                                                <TableHead className="w-28">Qty *</TableHead>
                                                <TableHead className="w-24">Unit</TableHead>
                                                <TableHead className="w-36">Est. Unit Cost (TZS)</TableHead>
                                                <TableHead className="w-36 text-right">Line Total (TZS)</TableHead>
                                                <TableHead className="w-12"></TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {createForm.data.items.map((item, index) => {
                                                const lineTotal =
                                                    (Number(item.quantity_requested) || 0) *
                                                    (Number(item.estimated_unit_cost) || 0);

                                                return (
                                                    <TableRow key={index}>
                                                        <TableCell>
                                                            <select
                                                                value={item.product_id || ''}
                                                                onChange={(e) =>
                                                                    handleItemChange(index, 'product_id', e.target.value, createForm)
                                                                }
                                                                className="w-full text-xs rounded border border-input bg-background p-1.5"
                                                            >
                                                                {products.map((p) => (
                                                                    <option key={p.id} value={p.id}>
                                                                        {p.name} ({p.sku || 'No SKU'})
                                                                    </option>
                                                                ))}
                                                            </select>
                                                        </TableCell>

                                                        <TableCell>
                                                            <Input
                                                                type="number"
                                                                step="0.01"
                                                                value={item.quantity_requested}
                                                                onChange={(e) =>
                                                                    handleItemChange(index, 'quantity_requested', e.target.value, createForm)
                                                                }
                                                                required
                                                                className="h-8 text-xs font-semibold"
                                                            />
                                                        </TableCell>

                                                        <TableCell>
                                                            <Input
                                                                value={item.unit || 'units'}
                                                                onChange={(e) =>
                                                                    handleItemChange(index, 'unit', e.target.value, createForm)
                                                                }
                                                                className="h-8 text-xs"
                                                            />
                                                        </TableCell>

                                                        <TableCell>
                                                            <Input
                                                                type="number"
                                                                step="0.01"
                                                                value={item.estimated_unit_cost}
                                                                onChange={(e) =>
                                                                    handleItemChange(index, 'estimated_unit_cost', e.target.value, createForm)
                                                                }
                                                                className="h-8 text-xs"
                                                            />
                                                        </TableCell>

                                                        <TableCell className="text-right font-mono font-semibold text-xs">
                                                            {lineTotal.toLocaleString()} TZS
                                                        </TableCell>

                                                        <TableCell>
                                                            <Button
                                                                type="button"
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() => handleRemoveItem(index, createForm)}
                                                                className="h-8 w-8 p-0 text-red-500"
                                                            >
                                                                <Trash2 className="w-3.5 h-3.5" />
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
                                        </TableBody>
                                    </Table>
                                </div>

                                <div className="flex justify-end p-3 bg-muted/20 rounded-lg">
                                    <div className="text-right">
                                        <span className="text-xs text-muted-foreground mr-2">Estimated Total Budget:</span>
                                        <strong className="text-base text-indigo-600 dark:text-indigo-400">
                                            {liveCreateTotal.toLocaleString()} TZS
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setIsCreateOpen(false)}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={createForm.processing}
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                                >
                                    Save Requisition
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 2: Edit Requisition */}
                <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
                    <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <Edit3 className="w-5 h-5 text-blue-500" />
                                Edit Requisition: {activeReq?.requisition_number}
                            </DialogTitle>
                        </DialogHeader>

                        <form onSubmit={submitEdit} className="space-y-4 pt-2">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <Label>Department</Label>
                                    <select
                                        value={editForm.data.department}
                                        onChange={(e) => editForm.setData('department', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {departments.map((dept) => (
                                            <option key={dept} value={dept}>
                                                {dept}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <Label>Priority</Label>
                                    <select
                                        value={editForm.data.priority}
                                        onChange={(e) => editForm.setData('priority', e.target.value as any)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>

                                <div>
                                    <Label>Warehouse</Label>
                                    <select
                                        value={editForm.data.warehouse_id}
                                        onChange={(e) => editForm.setData('warehouse_id', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {warehouses.map((wh) => (
                                            <option key={wh.id} value={wh.id}>
                                                {wh.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div>
                                <Label>Justification</Label>
                                <Textarea
                                    value={editForm.data.justification}
                                    onChange={(e) => editForm.setData('justification', e.target.value)}
                                    required
                                    className="mt-1"
                                />
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={() => setIsEditOpen(false)}>
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={editForm.processing}>
                                    Save Changes
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 3: Approve Requisition */}
                <Dialog open={isApproveOpen} onOpenChange={setIsApproveOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-emerald-600">
                                <CheckCircle className="w-5 h-5" />
                                Approve Requisition: {activeReq?.requisition_number}
                            </DialogTitle>
                            <DialogDescription>
                                Authorize procurement expenditure for {activeReq?.department} department. This will issue an official approval stamp and cryptographic verification hash.
                            </DialogDescription>
                        </DialogHeader>

                        <div className="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Department:</span>
                                <span className="font-semibold">{activeReq?.department}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Requester:</span>
                                <span>{activeReq?.requester?.name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Estimated Cost:</span>
                                <span className="font-bold text-emerald-600 dark:text-emerald-400">
                                    {Number(activeReq?.estimated_total_cost).toLocaleString()} TZS
                                </span>
                            </div>
                            <div className="pt-2 border-t text-xs text-muted-foreground">
                                <strong>Justification:</strong> {activeReq?.justification}
                            </div>
                        </div>

                        <DialogFooter>
                            <Button variant="outline" onClick={() => setIsApproveOpen(false)}>
                                Cancel
                            </Button>
                            <Button
                                onClick={submitApprove}
                                className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold"
                            >
                                Authorize & Approve Requisition
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* MODAL 4: Reject Requisition */}
                <Dialog open={isRejectOpen} onOpenChange={setIsRejectOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-red-600">
                                <XCircle className="w-5 h-5" />
                                Reject Requisition: {activeReq?.requisition_number}
                            </DialogTitle>
                            <DialogDescription>
                                Please provide the official reason for turning down this purchase requisition.
                            </DialogDescription>
                        </DialogHeader>

                        <form onSubmit={submitReject} className="space-y-4 pt-2">
                            <div>
                                <Label>Reason for Rejection *</Label>
                                <Textarea
                                    value={rejectionReason}
                                    onChange={(e) => setRejectionReason(e.target.value)}
                                    placeholder="e.g. Budget ceiling reached for Q4, or item already available in central inventory..."
                                    required
                                    className="mt-1"
                                    rows={3}
                                />
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={() => setIsRejectOpen(false)}>
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    variant="destructive"
                                >
                                    Confirm Rejection
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 5: Convert to Purchase Bill */}
                <Dialog open={isConvertOpen} onOpenChange={setIsConvertOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-indigo-600">
                                <ArrowRight className="w-5 h-5" />
                                Convert to Purchase Order / Bill
                            </DialogTitle>
                            <DialogDescription>
                                Convert approved requisition {activeReq?.requisition_number} directly into an official Purchase Invoice (Bill) with line items and 18% VAT automatically calculated.
                            </DialogDescription>
                        </DialogHeader>

                        <div className="p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Preferred Vendor:</span>
                                <span className="font-semibold">{activeReq?.preferred_vendor?.name || 'Assigned Vendor'}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Target Warehouse:</span>
                                <span>{activeReq?.warehouse?.name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Requisition Total (Net):</span>
                                <span>{Number(activeReq?.estimated_total_cost).toLocaleString()} TZS</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">VAT 18%:</span>
                                <span>{Number(Number(activeReq?.estimated_total_cost || 0) * 0.18).toLocaleString()} TZS</span>
                            </div>
                            <div className="flex justify-between pt-1 border-t font-bold">
                                <span>Total Bill Value:</span>
                                <span className="text-indigo-600 dark:text-indigo-400">
                                    {Number(Number(activeReq?.estimated_total_cost || 0) * 1.18).toLocaleString()} TZS
                                </span>
                            </div>
                        </div>

                        <DialogFooter>
                            <Button variant="outline" onClick={() => setIsConvertOpen(false)}>
                                Cancel
                            </Button>
                            <Button
                                onClick={submitConvert}
                                className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                            >
                                Generate Purchase Bill
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* MODAL 6: Printable Requisition Voucher */}
                <Dialog open={isSlipOpen} onOpenChange={setIsSlipOpen}>
                    <DialogContent className="max-w-3xl max-h-[92vh] overflow-y-auto">
                        <DialogHeader className="print:hidden">
                            <DialogTitle className="flex items-center justify-between">
                                <span className="flex items-center gap-2">
                                    <Printer className="w-5 h-5 text-indigo-500" />
                                    Requisition Voucher: {activeReq?.requisition_number}
                                </span>
                                <Button
                                    onClick={() => window.print()}
                                    size="sm"
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                                >
                                    <Printer className="w-4 h-4 mr-2" /> Print Requisition
                                </Button>
                            </DialogTitle>
                        </DialogHeader>

                        {/* Printable Voucher */}
                        <div className="p-8 bg-white text-slate-900 rounded-xl border border-slate-200 shadow-sm space-y-6">
                            {/* Header */}
                            <div className="flex justify-between items-start border-b pb-6">
                                <div>
                                    <h2 className="text-2xl font-black tracking-tight text-slate-900">
                                        MUTAMARK ENTERPRISES LIMITED
                                    </h2>
                                    <p className="text-xs text-slate-600 font-medium">
                                        Internal Procurement & Material Requisition Department | Dar es Salaam, Tanzania
                                    </p>
                                    <div className="text-xs text-slate-600 space-x-3 mt-1">
                                        <span><strong>TIN:</strong> 104-982-341</span>
                                        <span><strong>VRN:</strong> 40-023912-K</span>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <div className="text-xl font-bold tracking-tight text-indigo-900">
                                        PURCHASE REQUISITION
                                    </div>
                                    <div className="text-sm font-mono font-bold text-slate-900 mt-1">
                                        {activeReq?.requisition_number}
                                    </div>
                                    <div className="text-xs text-slate-500 mt-0.5">
                                        Date: {activeReq?.requisition_date}
                                    </div>
                                </div>
                            </div>

                            {/* Meta Grid */}
                            <div className="grid grid-cols-2 gap-6 text-xs">
                                <div className="p-3 bg-slate-50 rounded border border-slate-200 space-y-1">
                                    <div className="font-bold text-slate-700 uppercase mb-1">REQUISITION ORIGIN:</div>
                                    <div><strong>Department:</strong> {activeReq?.department}</div>
                                    <div><strong>Requester:</strong> {activeReq?.requester?.name}</div>
                                    <div><strong>Fulfillment Warehouse:</strong> {activeReq?.warehouse?.name}</div>
                                </div>

                                <div className="p-3 bg-slate-50 rounded border border-slate-200 space-y-1">
                                    <div className="font-bold text-slate-700 uppercase mb-1">APPROVAL & PRIORITY:</div>
                                    <div><strong>Priority:</strong> <span className="uppercase font-bold">{activeReq?.priority}</span></div>
                                    <div><strong>Status:</strong> <span className="uppercase font-bold">{activeReq?.status}</span></div>
                                    {activeReq?.approver && (
                                        <div><strong>Authorized By:</strong> {activeReq.approver.name} ({activeReq.approved_at})</div>
                                    )}
                                </div>
                            </div>

                            {/* Justification Box */}
                            <div className="p-3 bg-slate-50 rounded border border-slate-200 text-xs">
                                <div className="font-bold text-slate-700 uppercase mb-1">PURPOSE / JUSTIFICATION:</div>
                                <div className="text-slate-800">{activeReq?.justification}</div>
                            </div>

                            {/* Itemized Table */}
                            <div className="border border-slate-300 rounded overflow-hidden">
                                <table className="w-full text-xs text-left">
                                    <thead className="bg-slate-100 font-bold border-b border-slate-300">
                                        <tr>
                                            <th className="p-2.5">#</th>
                                            <th className="p-2.5">Description</th>
                                            <th className="p-2.5">SKU</th>
                                            <th className="p-2.5 text-right">Quantity</th>
                                            <th className="p-2.5 text-center">Unit</th>
                                            <th className="p-2.5 text-right">Est. Unit Cost</th>
                                            <th className="p-2.5 text-right">Estimated Total</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-200">
                                        {activeReq?.items.map((item, idx) => (
                                            <tr key={idx}>
                                                <td className="p-2.5 text-slate-500">{idx + 1}</td>
                                                <td className="p-2.5 font-semibold text-slate-900">{item.product_name}</td>
                                                <td className="p-2.5 font-mono text-slate-600">{item.sku || '-'}</td>
                                                <td className="p-2.5 text-right font-bold">{item.quantity_requested}</td>
                                                <td className="p-2.5 text-center text-slate-600">{item.unit || 'units'}</td>
                                                <td className="p-2.5 text-right font-mono">
                                                    {Number(item.estimated_unit_cost).toLocaleString()} TZS
                                                </td>
                                                <td className="p-2.5 text-right font-mono font-bold text-slate-900">
                                                    {Number(item.estimated_total_cost).toLocaleString()} TZS
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot className="bg-slate-50 font-bold border-t border-slate-300">
                                        <tr>
                                            <td colSpan={6} className="p-2.5 text-right">Total Estimated Requisition Value:</td>
                                            <td className="p-2.5 text-right text-indigo-900 font-bold text-sm font-mono">
                                                {Number(activeReq?.estimated_total_cost).toLocaleString()} TZS
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {/* Verification Block */}
                            <div className="flex items-center justify-between pt-4 border-t border-slate-200 text-xs">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-slate-100 rounded border border-slate-300">
                                        <QrCode className="w-12 h-12 text-slate-800" />
                                    </div>
                                    <div className="space-y-0.5">
                                        <div className="font-bold text-slate-900">VERIFIED PROCUREMENT AUDIT TRAIL</div>
                                        <div className="text-[10px] text-slate-500 font-mono">
                                            HASH: {activeReq?.verification_hash?.substring(0, 24) || 'SYSTEM-GENERATED-STAMP'}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Signatures 3-Column */}
                            <div className="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200 text-xs text-center">
                                <div className="space-y-10">
                                    <div className="border-b border-slate-400 pb-1 font-semibold">
                                        {activeReq?.requester?.name || 'Department Requester'}
                                    </div>
                                    <div className="text-[11px] text-slate-500">Initiator Signature</div>
                                </div>

                                <div className="space-y-10">
                                    <div className="border-b border-slate-400 pb-1 font-semibold">
                                        {activeReq?.approver?.name || 'Department Head'}
                                    </div>
                                    <div className="text-[11px] text-slate-500">Department Approval</div>
                                </div>

                                <div className="space-y-10">
                                    <div className="border-b border-slate-400 pb-1 font-semibold">
                                        Finance & Procurement Director
                                    </div>
                                    <div className="text-[11px] text-slate-500">Budget Authorization</div>
                                </div>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>

                {/* MODAL 7: Delete Confirmation */}
                <Dialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="text-red-600 flex items-center gap-2">
                                <Trash2 className="w-5 h-5" />
                                Delete Requisition
                            </DialogTitle>
                            <DialogDescription>
                                Are you sure you want to delete requisition {activeReq?.requisition_number}? This action cannot be undone.
                            </DialogDescription>
                        </DialogHeader>

                        <DialogFooter>
                            <Button variant="outline" onClick={() => setIsDeleteOpen(false)}>
                                Cancel
                            </Button>
                            <Button
                                onClick={submitDelete}
                                variant="destructive"
                            >
                                Delete Requisition
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AuthenticatedLayout>
    );
}
