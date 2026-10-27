import { useState, useMemo } from 'react';
import { Head, usePage, router, useForm } from '@inertiajs/react';
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
    Barcode, 
    Boxes, 
    CheckCircle2, 
    Clock, 
    AlertTriangle, 
    Plus, 
    Edit as EditIcon, 
    Trash2, 
    Search, 
    ShieldCheck, 
    Warehouse as WarehouseIcon, 
    Cpu, 
    DollarSign,
    RefreshCw
} from "lucide-react";
import { formatCurrency, formatDate } from '@/utils/helpers';

interface ProductItem {
    id: number;
    name: string;
    sku: string;
    purchase_price?: number;
    sale_price?: number;
}

interface WarehouseItem {
    id: number;
    name: string;
    city?: string;
}

interface SerialItem {
    id: number;
    serial_number: string;
    batch_number?: string;
    product_id: number;
    warehouse_id: number;
    status: 'in_stock' | 'allocated' | 'dispatched' | 'delivered' | 'defective' | 'under_maintenance';
    purchase_cost: number;
    selling_price: number;
    warranty_months: number;
    warranty_expiry_date?: string;
    barcode?: string;
    notes?: string;
    created_at: string;
    product?: ProductItem;
    warehouse?: WarehouseItem;
}

interface IndexProps {
    serials: {
        data: SerialItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    warehouses: WarehouseItem[];
    products: ProductItem[];
    stats: {
        total_units: number;
        in_stock_units: number;
        dispatched_units: number;
        defective_units: number;
        total_stock_value: number;
    };
    filters: {
        search?: string;
        product_id?: string;
        warehouse_id?: string;
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
    const { serials, warehouses, products, stats, filters: serverFilters, auth } = usePage<IndexProps>().props;

    useFlashMessages();

    // Filters state
    const [search, setSearch] = useState(serverFilters?.search || '');
    const [selectedProduct, setSelectedProduct] = useState(serverFilters?.product_id || 'all');
    const [selectedWarehouse, setSelectedWarehouse] = useState(serverFilters?.warehouse_id || 'all');
    const [selectedStatus, setSelectedStatus] = useState(serverFilters?.status || 'all');

    // Dialog state
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [editingItem, setEditingItem] = useState<SerialItem | null>(null);

    // Form
    const { data, setData, post, put, processing, errors, reset } = useForm({
        serial_number: '',
        batch_number: '',
        product_id: products[0]?.id ? String(products[0].id) : '',
        warehouse_id: warehouses[0]?.id ? String(warehouses[0].id) : '',
        status: 'in_stock',
        purchase_cost: '0',
        selling_price: '0',
        warranty_months: '12',
        warranty_expiry_date: '',
        barcode: '',
        notes: '',
    });

    const openCreateModal = () => {
        reset();
        setEditingItem(null);
        if (products.length > 0) setData('product_id', String(products[0].id));
        if (warehouses.length > 0) setData('warehouse_id', String(warehouses[0].id));
        setIsCreateOpen(true);
    };

    const openEditModal = (item: SerialItem) => {
        setEditingItem(item);
        setData({
            serial_number: item.serial_number,
            batch_number: item.batch_number || '',
            product_id: String(item.product_id),
            warehouse_id: String(item.warehouse_id),
            status: item.status,
            purchase_cost: String(item.purchase_cost || 0),
            selling_price: String(item.selling_price || 0),
            warranty_months: String(item.warranty_months || 12),
            warranty_expiry_date: item.warranty_expiry_date ? item.warranty_expiry_date.split('T')[0] : '',
            barcode: item.barcode || '',
            notes: item.notes || '',
        });
        setIsCreateOpen(true);
    };

    const handleFormSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editingItem) {
            put(route('inventory-serials.update', editingItem.id), {
                onSuccess: () => {
                    setIsCreateOpen(false);
                    setEditingItem(null);
                    reset();
                }
            });
        } else {
            post(route('inventory-serials.store'), {
                onSuccess: () => {
                    setIsCreateOpen(false);
                    reset();
                }
            });
        }
    };

    // Delete handler
    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'inventory-serials.destroy',
        defaultMessage: t('Are you sure you want to remove this serialized hardware item?')
    });

    // Apply filters
    const applyFilters = () => {
        router.get(route('inventory-serials.index'), {
            search: search || undefined,
            product_id: selectedProduct !== 'all' ? selectedProduct : undefined,
            warehouse_id: selectedWarehouse !== 'all' ? selectedWarehouse : undefined,
            status: selectedStatus !== 'all' ? selectedStatus : undefined,
        }, { preserveState: true });
    };

    const clearFilters = () => {
        setSearch('');
        setSelectedProduct('all');
        setSelectedWarehouse('all');
        setSelectedStatus('all');
        router.get(route('inventory-serials.index'));
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'in_stock':
                return <Badge className="bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100">{t('In Stock')}</Badge>;
            case 'allocated':
                return <Badge className="bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100">{t('Allocated')}</Badge>;
            case 'dispatched':
                return <Badge className="bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100">{t('Dispatched')}</Badge>;
            case 'delivered':
                return <Badge className="bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100">{t('Delivered')}</Badge>;
            case 'defective':
                return <Badge className="bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100">{t('Defective')}</Badge>;
            case 'under_maintenance':
                return <Badge className="bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100">{t('In Repair')}</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Inventory & Stock'), url: route('product-service.stock.index') },
                { label: t('Serial & Batch Tracking') }
            ]}
            pageTitle={t('Serial & Batch Tracking')}
        >
            <Head title={t('Serial & Batch Tracking')} />

            {/* KPI Metric Stat Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Card className="shadow-sm border-l-4 border-l-primary bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('Total Serial Units')}
                        </CardTitle>
                        <Barcode className="h-5 w-5 text-primary" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{stats.total_units}</div>
                        <p className="text-xs text-muted-foreground mt-1 flex items-center gap-1">
                            <Cpu className="h-3 w-3" /> {t('Active hardware assets tracked')}
                        </p>
                    </CardContent>
                </Card>

                <Card className="shadow-sm border-l-4 border-l-emerald-500 bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('Available In Stock')}
                        </CardTitle>
                        <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-emerald-700">{stats.in_stock_units}</div>
                        <p className="text-xs text-muted-foreground mt-1">
                            {t('Ready for immediate dispatch')}
                        </p>
                    </CardContent>
                </Card>

                <Card className="shadow-sm border-l-4 border-l-amber-500 bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('Dispatched / Deployed')}
                        </CardTitle>
                        <Clock className="h-5 w-5 text-amber-600" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-amber-700">{stats.dispatched_units}</div>
                        <p className="text-xs text-muted-foreground mt-1">
                            {t('Active in client deployments')}
                        </p>
                    </CardContent>
                </Card>

                <Card className="shadow-sm border-l-4 border-l-blue-500 bg-card/60 backdrop-blur-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            {t('Tracked Stock Value')}
                        </CardTitle>
                        <DollarSign className="h-5 w-5 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-xl font-bold text-blue-700">
                            {formatCurrency(stats.total_stock_value)}
                        </div>
                        <p className="text-xs text-muted-foreground mt-1">
                            {t('Valuation in TZS across hubs')}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Filter and Action Bar */}
            <Card className="shadow-sm mb-6 border">
                <CardContent className="p-4">
                    <div className="flex flex-col md:flex-row gap-4 items-center justify-between">
                        {/* Search & Dropdowns */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full md:w-auto flex-1">
                            <div className="relative">
                                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder={t('Search Serial, Batch, SKU...')}
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                                    className="pl-9 h-9"
                                />
                            </div>

                            <Select value={selectedProduct} onValueChange={setSelectedProduct}>
                                <SelectTrigger className="h-9">
                                    <SelectValue placeholder={t('Filter by Product')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Products')}</SelectItem>
                                    {products.map(p => (
                                        <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedWarehouse} onValueChange={setSelectedWarehouse}>
                                <SelectTrigger className="h-9">
                                    <SelectValue placeholder={t('Filter by Warehouse')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Warehouses')}</SelectItem>
                                    {warehouses.map(w => (
                                        <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>

                            <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                                <SelectTrigger className="h-9">
                                    <SelectValue placeholder={t('Filter by Status')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                    <SelectItem value="in_stock">{t('In Stock')}</SelectItem>
                                    <SelectItem value="allocated">{t('Allocated')}</SelectItem>
                                    <SelectItem value="dispatched">{t('Dispatched')}</SelectItem>
                                    <SelectItem value="delivered">{t('Delivered')}</SelectItem>
                                    <SelectItem value="defective">{t('Defective')}</SelectItem>
                                    <SelectItem value="under_maintenance">{t('In Repair')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Buttons */}
                        <div className="flex items-center gap-2 w-full md:w-auto justify-end">
                            <Button size="sm" onClick={applyFilters} className="h-9">
                                <Search className="h-3.5 w-3.5 mr-1" />
                                {t('Apply')}
                            </Button>
                            <Button size="sm" variant="outline" onClick={clearFilters} className="h-9">
                                <RefreshCw className="h-3.5 w-3.5 mr-1" />
                                {t('Reset')}
                            </Button>
                            {auth?.user?.permissions?.includes('create-inventory-serials') && (
                                <Button size="sm" onClick={openCreateModal} className="h-9 gap-1.5 shadow-sm">
                                    <Plus className="h-4 w-4" />
                                    {t('Register Serial')}
                                </Button>
                            )}
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Serial Numbers Table */}
            <Card className="shadow-sm border">
                <CardContent className="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/40">
                                <TableHead className="font-semibold">{t('Serial Number & Barcode')}</TableHead>
                                <TableHead className="font-semibold">{t('Batch / Lot')}</TableHead>
                                <TableHead className="font-semibold">{t('Hardware Item')}</TableHead>
                                <TableHead className="font-semibold">{t('Current Warehouse')}</TableHead>
                                <TableHead className="font-semibold">{t('Status')}</TableHead>
                                <TableHead className="font-semibold">{t('Cost (TZS)')}</TableHead>
                                <TableHead className="font-semibold">{t('Warranty Expiry')}</TableHead>
                                <TableHead className="text-right font-semibold pr-6">{t('Actions')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {serials.data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={8} className="py-12 text-center">
                                        <NoRecordsFound
                                            icon={Barcode}
                                            title={t('No Serialized Hardware Found')}
                                            description={t('Register serial numbers to track high-value equipment across Tanzanian distribution hubs.')}
                                            hasFilters={Boolean(search || (selectedProduct !== 'all') || (selectedWarehouse !== 'all') || (selectedStatus !== 'all'))}
                                            onClearFilters={clearFilters}
                                            createPermission="create-inventory-serials"
                                            onCreateClick={openCreateModal}
                                            createButtonText={t('Add Serial Unit')}
                                        />
                                    </TableCell>
                                </TableRow>
                            ) : (
                                serials.data.map((item) => (
                                    <TableRow key={item.id} className="hover:bg-muted/30 transition-colors">
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="font-mono font-semibold text-sm tracking-tight text-foreground">
                                                    {item.serial_number}
                                                </span>
                                                {item.barcode && (
                                                    <span className="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                                                        <Barcode className="h-3 w-3" /> {item.barcode}
                                                    </span>
                                                )}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            {item.batch_number ? (
                                                <Badge variant="outline" className="font-mono text-xs">
                                                    {item.batch_number}
                                                </Badge>
                                            ) : (
                                                <span className="text-muted-foreground text-xs">—</span>
                                            )}
                                        </TableCell>

                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="font-medium text-sm text-foreground">
                                                    {item.product?.name || t('Unknown Product')}
                                                </span>
                                                <span className="text-xs text-muted-foreground font-mono">
                                                    {item.product?.sku}
                                                </span>
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div className="flex items-center gap-1.5 text-sm">
                                                <WarehouseIcon className="h-3.5 w-3.5 text-muted-foreground" />
                                                <span>{item.warehouse?.name}</span>
                                                {item.warehouse?.city && (
                                                    <span className="text-xs text-muted-foreground">({item.warehouse.city})</span>
                                                )}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            {getStatusBadge(item.status)}
                                        </TableCell>

                                        <TableCell className="font-medium text-sm">
                                            {formatCurrency(item.purchase_cost || 0)}
                                        </TableCell>

                                        <TableCell>
                                            {item.warranty_expiry_date ? (
                                                <div className="flex items-center gap-1 text-xs">
                                                    <ShieldCheck className="h-3.5 w-3.5 text-emerald-600" />
                                                    <span>{formatDate(item.warranty_expiry_date)}</span>
                                                </div>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">{item.warranty_months} {t('months')}</span>
                                            )}
                                        </TableCell>

                                        <TableCell className="text-right pr-4">
                                            <div className="flex items-center justify-end gap-1">
                                                {auth?.user?.permissions?.includes('edit-inventory-serials') && (
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="h-8 w-8 p-0"
                                                        onClick={() => openEditModal(item)}
                                                    >
                                                        <EditIcon className="h-4 w-4 text-muted-foreground" />
                                                    </Button>
                                                )}
                                                {auth?.user?.permissions?.includes('delete-inventory-serials') && (
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                                        onClick={() => openDeleteDialog(item.id)}
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

                    {serials.data.length > 0 && (
                        <div className="p-4 border-t">
                            <Pagination
                                data={serials}
                                routeName="inventory-serials.index"
                                filters={{
                                    search: search || undefined,
                                    product_id: selectedProduct !== 'all' ? selectedProduct : undefined,
                                    warehouse_id: selectedWarehouse !== 'all' ? selectedWarehouse : undefined,
                                    status: selectedStatus !== 'all' ? selectedStatus : undefined,
                                }}
                            />
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Create / Edit Dialog Modal */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="sm:max-w-[550px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Barcode className="h-5 w-5 text-primary" />
                            {editingItem ? t('Edit Serialized Item') : t('Register New Serial Number')}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleFormSubmit} className="space-y-4 py-2">
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="serial_number">{t('Serial Number / IMEI')} *</Label>
                                <Input
                                    id="serial_number"
                                    value={data.serial_number}
                                    onChange={(e) => setData('serial_number', e.target.value)}
                                    placeholder="e.g. MUT-SN-2601001"
                                    required
                                    className="font-mono mt-1"
                                />
                                {errors.serial_number && <p className="text-xs text-destructive mt-1">{errors.serial_number}</p>}
                            </div>

                            <div>
                                <Label htmlFor="batch_number">{t('Batch / Lot Number')}</Label>
                                <Input
                                    id="batch_number"
                                    value={data.batch_number}
                                    onChange={(e) => setData('batch_number', e.target.value)}
                                    placeholder="e.g. LOT-2026-HW01"
                                    className="font-mono mt-1"
                                />
                                {errors.batch_number && <p className="text-xs text-destructive mt-1">{errors.batch_number}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="product_id">{t('Hardware Item')} *</Label>
                                <Select value={data.product_id} onValueChange={(v) => setData('product_id', v)}>
                                    <SelectTrigger className="mt-1">
                                        <SelectValue placeholder={t('Select Product')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {products.map(p => (
                                            <SelectItem key={p.id} value={String(p.id)}>{p.name} ({p.sku})</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.product_id && <p className="text-xs text-destructive mt-1">{errors.product_id}</p>}
                            </div>

                            <div>
                                <Label htmlFor="warehouse_id">{t('Storage Warehouse')} *</Label>
                                <Select value={data.warehouse_id} onValueChange={(v) => setData('warehouse_id', v)}>
                                    <SelectTrigger className="mt-1">
                                        <SelectValue placeholder={t('Select Warehouse')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map(w => (
                                            <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.warehouse_id && <p className="text-xs text-destructive mt-1">{errors.warehouse_id}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <Label htmlFor="status">{t('Status')} *</Label>
                                <Select value={data.status} onValueChange={(v: any) => setData('status', v)}>
                                    <SelectTrigger className="mt-1">
                                        <SelectValue placeholder={t('Select Status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="in_stock">{t('In Stock')}</SelectItem>
                                        <SelectItem value="allocated">{t('Allocated')}</SelectItem>
                                        <SelectItem value="dispatched">{t('Dispatched')}</SelectItem>
                                        <SelectItem value="delivered">{t('Delivered')}</SelectItem>
                                        <SelectItem value="defective">{t('Defective')}</SelectItem>
                                        <SelectItem value="under_maintenance">{t('In Repair')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label htmlFor="purchase_cost">{t('Cost Price (TZS)')}</Label>
                                <Input
                                    id="purchase_cost"
                                    type="number"
                                    value={data.purchase_cost}
                                    onChange={(e) => setData('purchase_cost', e.target.value)}
                                    placeholder="0.00"
                                    className="mt-1"
                                />
                            </div>

                            <div>
                                <Label htmlFor="warranty_months">{t('Warranty (Months)')}</Label>
                                <Input
                                    id="warranty_months"
                                    type="number"
                                    value={data.warranty_months}
                                    onChange={(e) => setData('warranty_months', e.target.value)}
                                    placeholder="12"
                                    className="mt-1"
                                />
                            </div>
                        </div>

                        <div>
                            <Label htmlFor="warranty_expiry_date">{t('Warranty Expiration Date')}</Label>
                            <Input
                                id="warranty_expiry_date"
                                type="date"
                                value={data.warranty_expiry_date}
                                onChange={(e) => setData('warranty_expiry_date', e.target.value)}
                                className="mt-1"
                            />
                        </div>

                        <div>
                            <Label htmlFor="notes">{t('Technical / Condition Notes')}</Label>
                            <Textarea
                                id="notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                placeholder={t('Firmware version, QA inspection sign-off, or deployment details...')}
                                rows={2}
                                className="mt-1"
                            />
                        </div>

                        <DialogFooter className="mt-6 pt-2 border-t">
                            <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {editingItem ? t('Update Item') : t('Save Serial Item')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                onConfirm={confirmDelete}
                title={t('Delete Serial Item')}
                message={deleteState.message}
            />
        </AuthenticatedLayout>
    );
}
