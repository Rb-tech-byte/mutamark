import { useState } from 'react';
import { Head, usePage, router, useForm, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
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
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import NoRecordsFound from '@/components/no-records-found';
import { 
    ClipboardCheck, 
    CheckCircle2, 
    Clock, 
    AlertTriangle, 
    Plus, 
    Trash2, 
    Eye, 
    ShieldCheck, 
    PackageCheck, 
    Building2, 
    Warehouse as WarehouseIcon, 
    Truck, 
    FileText,
    RefreshCw
} from "lucide-react";
import { formatCurrency, formatDate } from '@/utils/helpers';

interface VendorItem {
    id: number;
    company_name: string;
    contact_person_name?: string;
}

interface WarehouseItem {
    id: number;
    name: string;
    city?: string;
}

interface ProductItem {
    id: number;
    name: string;
    sku: string;
    purchase_price?: number;
}

interface GrnRecord {
    id: number;
    grn_number: string;
    purchase_order_number?: string;
    purchase_invoice_id?: number;
    vendor_id: number;
    warehouse_id: number;
    grn_date: string;
    delivery_challan_number?: string;
    carrier_name?: string;
    vehicle_number?: string;
    inspection_status: 'pending' | 'inspected_passed' | 'partially_accepted' | 'rejected';
    matching_status: 'unmatched' | 'matched' | 'variance_detected';
    total_ordered_qty: number;
    total_received_qty: number;
    total_accepted_qty: number;
    total_rejected_qty: number;
    total_amount: number;
    status: 'draft' | 'received' | 'posted' | 'cancelled';
    created_at: string;
    vendor?: VendorItem;
    warehouse?: WarehouseItem;
}

interface IndexProps {
    grns: {
        data: GrnRecord[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    vendors: VendorItem[];
    warehouses: WarehouseItem[];
    products: ProductItem[];
    stats: {
        total_grns: number;
        inspected_passed: number;
        pending_inspection: number;
        matched_grns: number;
        matched_value: number;
    };
    filters: {
        search?: string;
        vendor_id?: string;
        warehouse_id?: string;
        inspection_status?: string;
        matching_status?: string;
        status?: string;
    };
    auth: {
        user?: {
            permissions?: string[];
        };
    };
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { grns, vendors, warehouses, products, stats, filters: serverFilters, auth, purchaseOrders = [] } = usePage<IndexProps>().props;

    useFlashMessages();

    // Filters
    const [search, setSearch] = useState(serverFilters?.search || '');
    const [selectedVendor, setSelectedVendor] = useState(serverFilters?.vendor_id || 'all');
    const [selectedWarehouse, setSelectedWarehouse] = useState(serverFilters?.warehouse_id || 'all');
    const [selectedInspection, setSelectedInspection] = useState(serverFilters?.inspection_status || 'all');
    const [selectedMatching, setSelectedMatching] = useState(serverFilters?.matching_status || 'all');

    // Quick Intake Dialog State
    const [isCreateOpen, setIsCreateOpen] = useState(false);

    // Form
    const { data, setData, post, processing, errors, reset } = useForm({
        purchase_order_number: '',
        purchase_order_id: '',
        vendor_id: vendors[0]?.id ? String(vendors[0].id) : '',
        warehouse_id: warehouses[0]?.id ? String(warehouses[0].id) : '',
        grn_date: new Date().toISOString().split('T')[0],
        delivery_challan_number: '',
        carrier_name: 'Simba Freight & Logistics',
        vehicle_number: 'T 482 DXC',
        quality_inspection_notes: 'All cartons inspected upon receipt. Visual seals intact.',
        items: [
            {
                product_id: products[0]?.id ? String(products[0].id) : '',
                ordered_quantity: '10',
                received_quantity: '10',
                accepted_quantity: '10',
                rejected_quantity: '0',
                unit_price: products[0]?.purchase_price ? String(products[0].purchase_price) : '50000',
                batch_number: 'LOT-' + new Date().getFullYear() + '-01',
                rejection_reason: '',
            }
        ]
    });

    const openCreateModal = () => {
        reset();
        if (vendors.length > 0) setData('vendor_id', String(vendors[0].id));
        if (warehouses.length > 0) setData('warehouse_id', String(warehouses[0].id));
        if (products.length > 0) {
            setData('items', [{
                product_id: String(products[0].id),
                ordered_quantity: '10',
                received_quantity: '10',
                accepted_quantity: '10',
                rejected_quantity: '0',
                unit_price: products[0].purchase_price ? String(products[0].purchase_price) : '50000',
                batch_number: 'LOT-' + new Date().getFullYear() + '-01',
                rejection_reason: '',
            }]);
        }
        setIsCreateOpen(true);
    };

    const handleFormSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('goods-received-notes.store'), {
            onSuccess: () => {
                setIsCreateOpen(false);
                reset();
            }
        });
    };

    const handlePostToStock = (id: number) => {
        if (confirm(t('Are you sure you want to post this GRN to inventory? Stock quantities in the target warehouse will be incremented immediately.'))) {
            router.post(route('goods-received-notes.post', id));
        }
    };

    // Delete handler
    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'goods-received-notes.destroy',
        defaultMessage: t('Are you sure you want to remove this Goods Received Note?')
    });

    // Apply filters
    const applyFilters = () => {
        router.get(route('goods-received-notes.index'), {
            search: search || undefined,
            vendor_id: selectedVendor !== 'all' ? selectedVendor : undefined,
            warehouse_id: selectedWarehouse !== 'all' ? selectedWarehouse : undefined,
            inspection_status: selectedInspection !== 'all' ? selectedInspection : undefined,
            matching_status: selectedMatching !== 'all' ? selectedMatching : undefined,
        }, { preserveState: true });
    };

    const clearFilters = () => {
        setSearch('');
        setSelectedVendor('all');
        setSelectedWarehouse('all');
        setSelectedInspection('all');
        setSelectedMatching('all');
        router.get(route('goods-received-notes.index'));
    };

    const getInspectionBadge = (status: string) => {
        switch (status) {
            case 'inspected_passed':
                return <Badge className="bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 flex items-center gap-1"><CheckCircle2 className="h-3 w-3" /> {t('Passed')}</Badge>;
            case 'partially_accepted':
                return <Badge className="bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> {t('Partial')}</Badge>;
            case 'rejected':
                return <Badge className="bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> {t('Rejected')}</Badge>;
            default:
                return <Badge className="bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 flex items-center gap-1"><Clock className="h-3 w-3" /> {t('Pending QA')}</Badge>;
        }
    };

    const getMatchingBadge = (status: string) => {
        switch (status) {
            case 'matched':
                return <Badge className="bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 flex items-center gap-1"><ShieldCheck className="h-3 w-3" /> {t('3-Way Matched')}</Badge>;
            case 'variance_detected':
                return <Badge className="bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100 flex items-center gap-1"><AlertTriangle className="h-3 w-3" /> {t('Variance Flagged')}</Badge>;
            default:
                return <Badge variant="secondary" className="text-muted-foreground">{t('Unmatched')}</Badge>;
        }
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'posted':
                return <Badge className="bg-emerald-600 text-white hover:bg-emerald-700">{t('Posted to Stock')}</Badge>;
            case 'received':
                return <Badge variant="outline" className="text-blue-700 border-blue-300">{t('Received')}</Badge>;
            case 'cancelled':
                return <Badge variant="destructive">{t('Cancelled')}</Badge>;
            default:
                return <Badge variant="secondary">{t('Draft')}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Procurement'), url: route('goods-received-notes.index') },
                { label: t('Goods Received (GRN)') }
            ]}
            pageTitle={t('Goods Received Notes (GRN)')}
        >
            <Head title={t('Goods Received Notes (GRN)')} />

            {/* KPI Metric Stat Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Card className="shadow-sm border-l-4 border-l-primary bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('Total GRN Receipts')}
                        </CardTitle>
                        <ClipboardCheck className="h-5 w-5 text-primary" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.total_grns}</div>
                        <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                            <Truck className="h-3 w-3" /> {t('Warehouse intake shipments')}
                        </p>
                    </CardContent>
                </Card>

                <Card className="shadow-sm border-l-4 border-l-emerald-500 bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('Inspected & Passed')}
                        </CardTitle>
                        <CheckCircle2 className="h-5 w-5 text-emerald-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-emerald-600">{stats.inspected_passed}</div>
                        <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                            <ShieldCheck className="h-3 w-3" /> {t('Quality inspection verified')}
                        </p>
                    </CardContent>
                </Card>

                <Card className="shadow-sm border-l-4 border-l-amber-500 bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('Pending Inspection')}
                        </CardTitle>
                        <Clock className="h-5 w-5 text-amber-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-amber-600">{stats.pending_inspection}</div>
                        <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                            <Clock className="h-3 w-3" /> {t('Awaiting QA technician')}
                        </p>
                    </CardContent>
                </Card>

                <Card className="shadow-sm border-l-4 border-l-indigo-500 bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('3-Way Matched Value')}
                        </CardTitle>
                        <ShieldCheck className="h-5 w-5 text-indigo-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-indigo-600">{formatCurrency(stats.matched_value)}</div>
                        <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                            {t('PO vs GRN vs Bill verified')}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Filter Bar & Action Header */}
            <Card className="shadow-sm mb-6 bg-card/60 backdrop-blur-sm">
                <CardContent className="p-4">
                    <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                        <div className="flex flex-1 flex-wrap items-center gap-3">
                            <div className="w-full sm:w-64">
                                <SearchInput
                                    value={search}
                                    onChange={setSearch}
                                    onSearch={applyFilters}
                                    placeholder={t('Search GRN, PO, Challan...')}
                                />
                            </div>

                            <Select value={selectedVendor} onValueChange={(val) => { setSelectedVendor(val); }}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder={t('All Vendors')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Vendors')}</SelectItem>
                                    {vendors.map((v) => (
                                        <SelectItem key={v.id} value={String(v.id)}>{v.company_name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedWarehouse} onValueChange={(val) => { setSelectedWarehouse(val); }}>
                                <SelectTrigger className="w-[180px]">
                                    <SelectValue placeholder={t('All Warehouses')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Warehouses')}</SelectItem>
                                    {warehouses.map((w) => (
                                        <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedInspection} onValueChange={(val) => { setSelectedInspection(val); }}>
                                <SelectTrigger className="w-[150px]">
                                    <SelectValue placeholder={t('Inspection')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All QA Status')}</SelectItem>
                                    <SelectItem value="inspected_passed">{t('Passed')}</SelectItem>
                                    <SelectItem value="partially_accepted">{t('Partial')}</SelectItem>
                                    <SelectItem value="rejected">{t('Rejected')}</SelectItem>
                                    <SelectItem value="pending">{t('Pending')}</SelectItem>
                                </SelectContent>
                            </Select>

                            <Button variant="outline" size="sm" onClick={applyFilters}>
                                <RefreshCw className="h-4 w-4 mr-1.5" /> {t('Apply')}
                            </Button>

                            {(search || selectedVendor !== 'all' || selectedWarehouse !== 'all' || selectedInspection !== 'all' || selectedMatching !== 'all') && (
                                <Button variant="ghost" size="sm" onClick={clearFilters}>
                                    {t('Reset')}
                                </Button>
                            )}
                        </div>

                        {auth.user?.permissions?.includes('create-grn') && (
                            <Button onClick={openCreateModal} className="shrink-0 shadow-sm">
                                <Plus className="h-4 w-4 mr-1.5" />
                                {t('Receive Goods (GRN)')}
                            </Button>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* GRN Data Table */}
            <Card className="shadow-sm bg-card/60 backdrop-blur-sm">
                <CardContent className="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/40">
                                <TableHead className="font-semibold pl-6">{t('GRN Number')}</TableHead>
                                <TableHead className="font-semibold">{t('PO Ref / Challan')}</TableHead>
                                <TableHead className="font-semibold">{t('Supplier / Vendor')}</TableHead>
                                <TableHead className="font-semibold">{t('Intake Hub')}</TableHead>
                                <TableHead className="font-semibold">{t('Date')}</TableHead>
                                <TableHead className="font-semibold">{t('Quantities (Ord/Rec/Acc)')}</TableHead>
                                <TableHead className="font-semibold">{t('QA Inspection')}</TableHead>
                                <TableHead className="font-semibold">{t('3-Way Match')}</TableHead>
                                <TableHead className="font-semibold">{t('Status')}</TableHead>
                                <TableHead className="text-right font-semibold pr-6">{t('Actions')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {grns.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={10} className="py-12 text-center">
                                        <NoRecordsFound
                                            icon={ClipboardCheck}
                                            title={t('No Goods Received Notes Found')}
                                            description={t('Record warehouse intake shipments against purchase orders to enable 3-Way Matching.')}
                                            hasFilters={Boolean(search || selectedVendor !== 'all' || selectedWarehouse !== 'all' || selectedInspection !== 'all' || selectedMatching !== 'all')}
                                            onClearFilters={clearFilters}
                                            createPermission="create-grn"
                                            onCreateClick={openCreateModal}
                                            createButtonText={t('Receive Goods (GRN)')}
                                        />
                                    </TableCell>
                                </TableRow>
                            ) : (
                                grns.data.map((item) => (
                                    <TableRow key={item.id} className="hover:bg-muted/30 transition-colors">
                                        <TableCell className="pl-6">
                                            <div className="flex flex-col">
                                                <Link 
                                                    href={route('goods-received-notes.show', item.id)}
                                                    className="font-mono font-semibold text-sm tracking-tight text-primary hover:underline"
                                                >
                                                    {item.grn_number}
                                                </Link>
                                                {item.carrier_name && (
                                                    <span className="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                                                        <Truck className="h-3 w-3" /> {item.carrier_name}
                                                    </span>
                                                )}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="font-mono text-xs font-medium">
                                                    {item.purchase_order_number || t('Direct PO')}
                                                </span>
                                                {item.delivery_challan_number && (
                                                    <span className="text-xs text-muted-foreground">
                                                        {t('DC')}: {item.delivery_challan_number}
                                                    </span>
                                                )}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div className="flex items-center gap-1.5 font-medium text-sm">
                                                <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                                                {item.vendor?.company_name || 'N/A'}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                                <WarehouseIcon className="h-3.5 w-3.5" />
                                                {item.warehouse?.name || 'N/A'}
                                            </div>
                                        </TableCell>

                                        <TableCell className="text-xs text-muted-foreground whitespace-nowrap">
                                            {formatDate(item.grn_date)}
                                        </TableCell>

                                        <TableCell>
                                            <div className="flex items-center gap-1.5 font-mono text-xs">
                                                <span className="text-muted-foreground" title={t('Ordered')}>{Math.round(item.total_ordered_qty)}</span>
                                                <span className="text-muted-foreground">/</span>
                                                <span className="font-semibold text-foreground" title={t('Received')}>{Math.round(item.total_received_qty)}</span>
                                                <span className="text-muted-foreground">/</span>
                                                <span className="font-semibold text-emerald-600" title={t('Accepted')}>{Math.round(item.total_accepted_qty)}</span>
                                                {item.total_rejected_qty > 0 && (
                                                    <span className="text-rose-600 font-bold ml-1">(-{Math.round(item.total_rejected_qty)})</span>
                                                )}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            {getInspectionBadge(item.inspection_status)}
                                        </TableCell>

                                        <TableCell>
                                            {getMatchingBadge(item.matching_status)}
                                        </TableCell>

                                        <TableCell>
                                            {getStatusBadge(item.status)}
                                        </TableCell>

                                        <TableCell className="text-right pr-6">
                                            <div className="flex items-center justify-end gap-1">
                                                <Link href={route('goods-received-notes.show', item.id)}>
                                                    <Button variant="ghost" size="sm" className="h-8 w-8 p-0" title={t('View & 3-Way Match Audit')}>
                                                        <Eye className="h-4 w-4 text-primary" />
                                                    </Button>
                                                </Link>

                                                {item.status !== 'posted' && auth.user?.permissions?.includes('post-grn') && (
                                                    <Button 
                                                        variant="ghost" 
                                                        size="sm" 
                                                        className="h-8 w-8 p-0 text-emerald-600 hover:text-emerald-700" 
                                                        onClick={() => handlePostToStock(item.id)}
                                                        title={t('Post Intake to Warehouse Stock')}
                                                    >
                                                        <PackageCheck className="h-4 w-4" />
                                                    </Button>
                                                )}

                                                {item.status !== 'posted' && auth.user?.permissions?.includes('delete-grn') && (
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => openDeleteDialog(item.id)}
                                                        className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                                        title={t('Delete')}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>

                    {grns.data.length > 0 && (
                        <div className="p-4 border-t">
                            <Pagination
                                data={grns}
                                routeName="goods-received-notes.index"
                                filters={{
                                    search: search || undefined,
                                    vendor_id: selectedVendor !== 'all' ? selectedVendor : undefined,
                                    warehouse_id: selectedWarehouse !== 'all' ? selectedWarehouse : undefined,
                                    inspection_status: selectedInspection !== 'all' ? selectedInspection : undefined,
                                    matching_status: selectedMatching !== 'all' ? selectedMatching : undefined,
                                }}
                            />
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Quick Goods Receipt Intake Dialog Modal */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="sm:max-w-[650px] max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <ClipboardCheck className="h-5 w-5 text-primary" />
                            {t('Record Goods Received Note (GRN)')}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleFormSubmit} className="space-y-4 pt-2">
                        <div><Label>{t('Issued Purchase Order')}</Label><Select value={data.purchase_order_id} onValueChange={value => {
                            const order = purchaseOrders.find((order: any) => String(order.id) === value); if (!order) return;
                            setData(current => ({ ...current, purchase_order_id: value, purchase_order_number: order.po_number, vendor_id: String(order.vendor_id), warehouse_id: String(order.warehouse_id), items: order.items.map((line: any) => ({ product_id: String(line.product_id), ordered_quantity: String(line.quantity), received_quantity: String(line.quantity), accepted_quantity: String(line.quantity), rejected_quantity: '0', unit_price: String(line.unit_price), batch_number: '', rejection_reason: '' })) }));
                        }}><SelectTrigger><SelectValue placeholder={t('Select issued order')} /></SelectTrigger><SelectContent>{purchaseOrders.filter((order: any) => ['issued','partially_received'].includes(order.status)).map((order: any) => <SelectItem key={order.id} value={String(order.id)}>{order.po_number}</SelectItem>)}</SelectContent></Select>{errors.purchase_order_id && <p className="text-destructive text-sm">{errors.purchase_order_id}</p>}</div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="purchase_order_number">{t('Purchase Order Reference')}</Label>
                                <Input
                                    id="purchase_order_number"
                                    value={data.purchase_order_number}
                                    onChange={(e) => setData('purchase_order_number', e.target.value)}
                                    placeholder="e.g. PO-2026-00001"
                                    className="mt-1 font-mono"
                                />
                                {errors.purchase_order_number && (
                                    <p className="text-xs text-destructive mt-1">{errors.purchase_order_number}</p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="vendor_id">{t('Supplier / Vendor')} *</Label>
                                <Select value={data.vendor_id} onValueChange={(val) => setData('vendor_id', val)}>
                                    <SelectTrigger id="vendor_id" className="mt-1">
                                        <SelectValue placeholder={t('Select Vendor')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {vendors.map((v) => (
                                            <SelectItem key={v.id} value={String(v.id)}>{v.company_name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.vendor_id && (
                                    <p className="text-xs text-destructive mt-1">{errors.vendor_id}</p>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="warehouse_id">{t('Receiving Warehouse Hub')} *</Label>
                                <Select value={data.warehouse_id} onValueChange={(val) => setData('warehouse_id', val)}>
                                    <SelectTrigger id="warehouse_id" className="mt-1">
                                        <SelectValue placeholder={t('Select Warehouse')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map((w) => (
                                            <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.warehouse_id && (
                                    <p className="text-xs text-destructive mt-1">{errors.warehouse_id}</p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="grn_date">{t('Receipt Date')} *</Label>
                                <Input
                                    id="grn_date"
                                    type="date"
                                    value={data.grn_date}
                                    onChange={(e) => setData('grn_date', e.target.value)}
                                    className="mt-1"
                                    required
                                />
                                {errors.grn_date && (
                                    <p className="text-xs text-destructive mt-1">{errors.grn_date}</p>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <Label htmlFor="delivery_challan_number">{t('Delivery Challan / DC')}</Label>
                                <Input
                                    id="delivery_challan_number"
                                    value={data.delivery_challan_number}
                                    onChange={(e) => setData('delivery_challan_number', e.target.value)}
                                    placeholder="DC-2026-99"
                                    className="mt-1"
                                />
                            </div>

                            <div>
                                <Label htmlFor="carrier_name">{t('Carrier / Transporter')}</Label>
                                <Input
                                    id="carrier_name"
                                    value={data.carrier_name}
                                    onChange={(e) => setData('carrier_name', e.target.value)}
                                    placeholder="e.g. Simba Freight"
                                    className="mt-1"
                                />
                            </div>

                            <div>
                                <Label htmlFor="vehicle_number">{t('Vehicle Plate No.')}</Label>
                                <Input
                                    id="vehicle_number"
                                    value={data.vehicle_number}
                                    onChange={(e) => setData('vehicle_number', e.target.value)}
                                    placeholder="T 482 DXC"
                                    className="mt-1 font-mono"
                                />
                            </div>
                        </div>

                        {/* Received Item Line */}
                        <div className="p-3 bg-muted/40 rounded-lg border space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                    {t('Shipment Line Item')}
                                </span>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <Label className="text-xs">{t('Item / Hardware')}</Label>
                                    <Select 
                                        value={data.items[0]?.product_id} 
                                        onValueChange={(val) => {
                                            const prod = products.find(p => String(p.id) === val);
                                            const updated = [...data.items];
                                            updated[0].product_id = val;
                                            if (prod?.purchase_price) {
                                                updated[0].unit_price = String(prod.purchase_price);
                                            }
                                            setData('items', updated);
                                        }}
                                    >
                                        <SelectTrigger className="mt-1 h-9 text-xs">
                                            <SelectValue placeholder={t('Select Product')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {products.map((p) => (
                                                <SelectItem key={p.id} value={String(p.id)}>{p.name} ({p.sku})</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Label className="text-xs">{t('Batch / LOT Identifier')}</Label>
                                    <Input
                                        value={data.items[0]?.batch_number}
                                        onChange={(e) => {
                                            const updated = [...data.items];
                                            updated[0].batch_number = e.target.value;
                                            setData('items', updated);
                                        }}
                                        placeholder="LOT-2026-01"
                                        className="mt-1 h-9 text-xs font-mono"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-4 gap-2">
                                <div>
                                    <Label className="text-xs">{t('PO Qty')}</Label>
                                    <Input
                                        type="number"
                                        value={data.items[0]?.ordered_quantity}
                                        onChange={(e) => {
                                            const updated = [...data.items];
                                            updated[0].ordered_quantity = e.target.value;
                                            setData('items', updated);
                                        }}
                                        className="mt-1 h-9 text-xs"
                                        min="1"
                                    />
                                </div>

                                <div>
                                    <Label className="text-xs">{t('Recv Qty')}</Label>
                                    <Input
                                        type="number"
                                        value={data.items[0]?.received_quantity}
                                        onChange={(e) => {
                                            const updated = [...data.items];
                                            updated[0].received_quantity = e.target.value;
                                            updated[0].accepted_quantity = e.target.value;
                                            setData('items', updated);
                                        }}
                                        className="mt-1 h-9 text-xs"
                                        min="1"
                                    />
                                </div>

                                <div>
                                    <Label className="text-xs text-emerald-600 font-semibold">{t('Accept Qty')}</Label>
                                    <Input
                                        type="number"
                                        value={data.items[0]?.accepted_quantity}
                                        onChange={(e) => {
                                            const updated = [...data.items];
                                            updated[0].accepted_quantity = e.target.value;
                                            const recv = parseFloat(updated[0].received_quantity || '0');
                                            const acc = parseFloat(e.target.value || '0');
                                            updated[0].rejected_quantity = String(Math.max(0, recv - acc));
                                            setData('items', updated);
                                        }}
                                        className="mt-1 h-9 text-xs font-semibold text-emerald-600"
                                        min="0"
                                    />
                                </div>

                                <div>
                                    <Label className="text-xs">{t('Unit Cost (TZS)')}</Label>
                                    <Input
                                        type="number"
                                        value={data.items[0]?.unit_price}
                                        onChange={(e) => {
                                            const updated = [...data.items];
                                            updated[0].unit_price = e.target.value;
                                            setData('items', updated);
                                        }}
                                        className="mt-1 h-9 text-xs"
                                    />
                                </div>
                            </div>
                        </div>

                        <div>
                            <Label htmlFor="quality_inspection_notes">{t('Quality & Receiving Notes')}</Label>
                            <Textarea
                                id="quality_inspection_notes"
                                value={data.quality_inspection_notes}
                                onChange={(e) => setData('quality_inspection_notes', e.target.value)}
                                rows={2}
                                className="mt-1 text-xs"
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? t('Saving...') : t('Generate GRN')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Goods Received Note')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
            />
            <Card className="mt-6"><CardHeader><CardTitle>{t('Purchase Orders')}</CardTitle></CardHeader><CardContent>
                <Table><TableHeader><TableRow><TableHead>{t('Number')}</TableHead><TableHead>{t('Supplier')}</TableHead><TableHead>{t('Status')}</TableHead><TableHead>{t('Actions')}</TableHead></TableRow></TableHeader><TableBody>
                    {purchaseOrders.map((order: any) => <TableRow key={order.id}><TableCell>{order.po_number}</TableCell><TableCell>{order.vendor?.company_name}</TableCell><TableCell><Badge variant="secondary">{t(order.status)}</Badge></TableCell><TableCell className="space-x-2">
                        {order.status === 'draft' && <Button variant="outline" onClick={() => router.post(route('purchase-orders.action', order.id), { action: 'issue' })}>{t('Issue')}</Button>}
                        {['issued','partially_received','received'].includes(order.status) && <Button variant="outline" onClick={() => router.post(route('purchase-orders.action', order.id), { action: 'invoice' })}>{t('Supplier invoice')}</Button>}
                    </TableCell></TableRow>)}
                </TableBody></Table>
            </CardContent></Card>
        </AuthenticatedLayout>
    );
}
