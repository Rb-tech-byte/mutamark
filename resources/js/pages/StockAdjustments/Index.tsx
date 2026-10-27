import { useState, useRef, useEffect } from 'react';
import axios from 'axios';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { PaginatedData } from '@/types/common';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { DatePicker } from "@/components/ui/date-picker";
import { Badge } from '@/components/ui/badge';
import {
    Plus, Scale, CheckCircle2, Clock, AlertTriangle, ShieldCheck,
    Boxes, Trash2, ArrowUpDown
} from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import NoRecordsFound from '@/components/no-records-found';
import { formatDate, formatCurrency } from '@/utils/helpers';

interface StockAdjustmentItem {
    id: number;
    product_id: number;
    product?: {
        id: number;
        name: string;
        sku: string;
    };
    system_quantity: number;
    counted_quantity: number;
    difference_quantity: number;
    unit_cost: number;
    variance_value: number;
    remarks?: string;
}

interface StockAdjustment {
    id: number;
    adjustment_number: string;
    warehouse_id: number;
    warehouse?: {
        id: number;
        name: string;
        code?: string;
        city?: string;
    };
    adjustment_type: string;
    status: 'draft' | 'approved' | 'posted';
    adjustment_date: string;
    reason: string;
    total_variance_value: number;
    items?: StockAdjustmentItem[];
    approver?: {
        id: number;
        name: string;
    };
    created_at: string;
}

interface Warehouse {
    id: number;
    name: string;
    code?: string;
    city?: string;
}

interface Product {
    id: number;
    name: string;
    sku: string;
    purchase_price?: number;
}

interface WarehouseStock {
    id: number;
    product_id: number;
    warehouse_id: number;
    quantity: string | number;
}

interface StockAdjustmentsIndexProps {
    adjustments: PaginatedData<StockAdjustment>;
    warehouses: Warehouse[];
    products: Product[];
    warehouseStocks: WarehouseStock[];
    statistics: {
        total_adjustments: number;
        total_variance_value: number;
        approved_count: number;
        pending_count: number;
    };
    auth: any;
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { adjustments, warehouses, products, warehouseStocks, statistics } = usePage<StockAdjustmentsIndexProps>().props;

    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [postingId, setPostingId] = useState<number | null>(null);
    const [scanCode, setScanCode] = useState('');
    const [scanError, setScanError] = useState('');
    const [scanning, setScanning] = useState(false);

    // Form state
    const [formData, setFormData] = useState({
        warehouse_id: warehouses[0]?.id?.toString() || '',
        adjustment_type: 'cycle_count',
        adjustment_date: new Date().toISOString().split('T')[0],
        reason: '',
        items: [] as Array<{
            product_id: string;
            system_quantity: number;
            counted_quantity: string;
            remarks: string;
        }>,
    });

    useFlashMessages();
    const scannedSerials = useRef(new Set<number>());
    const activeWarehouse = useRef(formData.warehouse_id);
    useEffect(() => { activeWarehouse.current=formData.warehouse_id; scannedSerials.current.clear(); }, [formData.warehouse_id,isCreateOpen]);

    const scanProduct = async () => {
        if (!scanCode.trim() || scanning) return;
        setScanning(true); setScanError('');
        try {
            const warehouse = formData.warehouse_id;
            const { data } = await axios.get(route('stock-adjustments.scan'), { params: { code: scanCode.trim(), warehouse_id: formData.warehouse_id } });
            if (activeWarehouse.current !== warehouse) return;
            if (data.serial_id && scannedSerials.current.has(data.serial_id)) { setScanError(t('This serial was already counted.')); return; }
            if (data.serial_id) scannedSerials.current.add(data.serial_id);
            setFormData(current => {
                const exists = current.items.some(item => item.product_id === String(data.product_id));
                return { ...current, items: exists
                    ? current.items.map(item => item.product_id === String(data.product_id) ? { ...item, counted_quantity: String(Number(item.counted_quantity || 0) + 1) } : item)
                    : [...current.items, { product_id: String(data.product_id), system_quantity: data.system_quantity, counted_quantity: '1', remarks: '' }] };
            });
            setScanCode('');
        } catch (error: any) {
            setScanError(error.response?.data?.message || t('Product not found.'));
        } finally { setScanning(false); }
    };

    const addItemRow = () => {
        const availableProds = products.filter(p => !formData.items.some(it => it.product_id === p.id.toString()));
        const firstProd = availableProds[0] || products[0];
        if (!firstProd) return;

        const currentStock = warehouseStocks.find(
            s => s.warehouse_id.toString() === formData.warehouse_id && s.product_id === firstProd.id
        );
        const sysQty = currentStock ? Number(currentStock.quantity) : 0;

        setFormData({
            ...formData,
            items: [
                ...formData.items,
                {
                    product_id: firstProd.id.toString(),
                    system_quantity: sysQty,
                    counted_quantity: sysQty.toString(),
                    remarks: '',
                }
            ]
        });
    };

    const handleProductChange = (index: number, prodId: string) => {
        const currentStock = warehouseStocks.find(
            s => s.warehouse_id.toString() === formData.warehouse_id && s.product_id.toString() === prodId
        );
        const sysQty = currentStock ? Number(currentStock.quantity) : 0;

        const next = [...formData.items];
        next[index] = {
            ...next[index],
            product_id: prodId,
            system_quantity: sysQty,
            counted_quantity: sysQty.toString(),
        };
        setFormData({ ...formData, items: next });
    };

    const handleCountChange = (index: number, countedVal: string) => {
        const next = [...formData.items];
        next[index] = { ...next[index], counted_quantity: countedVal };
        setFormData({ ...formData, items: next });
    };

    const removeItemRow = (index: number) => {
        const next = [...formData.items];
        next.splice(index, 1);
        setFormData({ ...formData, items: next });
    };

    const submitCreate = (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);
        router.post(route('stock-adjustments.store'), formData, {
            onSuccess: () => {
                setIsCreateOpen(false);
                setSubmitting(false);
                setFormData({
                    warehouse_id: warehouses[0]?.id?.toString() || '',
                    adjustment_type: 'cycle_count',
                    adjustment_date: new Date().toISOString().split('T')[0],
                    reason: '',
                    items: [],
                });
            },
            onError: () => setSubmitting(false),
        });
    };

    const handlePost = (adjId: number) => {
        setPostingId(adjId);
        router.post(route('stock-adjustments.post', adjId), {}, {
            onFinish: () => setPostingId(null),
        });
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'posted':
            case 'approved':
                return (
                    <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 flex items-center gap-1">
                        <CheckCircle2 className="h-3 w-3" />
                        {t('Reconciled & Posted')}
                    </Badge>
                );
            case 'draft':
                return (
                    <Badge variant="outline" className="bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30 flex items-center gap-1">
                        <Clock className="h-3 w-3" />
                        {t('Pending Review')}
                    </Badge>
                );
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    const tableColumns = [
        {
            key: 'adjustment_number',
            header: t('Adjustment ID'),
            render: (_: any, adj: StockAdjustment) => (
                <div className="flex flex-col">
                    <span className="font-semibold text-primary font-mono text-sm">{adj.adjustment_number}</span>
                    <span className="text-xs text-muted-foreground">{formatDate(adj.adjustment_date)}</span>
                </div>
            )
        },
        {
            key: 'warehouse',
            header: t('Warehouse Location'),
            render: (_: any, adj: StockAdjustment) => (
                <div className="flex flex-col text-xs">
                    <span className="font-medium text-foreground">{adj.warehouse?.name}</span>
                    <span className="text-muted-foreground">{adj.warehouse?.code} • {adj.warehouse?.city}</span>
                </div>
            )
        },
        {
            key: 'type',
            header: t('Adjustment Type'),
            render: (_: any, adj: StockAdjustment) => (
                <span className="capitalize text-xs font-medium">
                    {adj.adjustment_type.replace('_', ' ')}
                </span>
            )
        },
        {
            key: 'variance',
            header: t('Total Variance (TZS)'),
            render: (_: any, adj: StockAdjustment) => {
                const val = Number(adj.total_variance_value);
                return (
                    <span className={`text-xs font-mono font-semibold ${val < 0 ? 'text-destructive' : 'text-emerald-600'}`}>
                        {val > 0 ? `+${val.toLocaleString()} TZS` : `${val.toLocaleString()} TZS`}
                    </span>
                );
            }
        },
        {
            key: 'status',
            header: t('Status'),
            render: (_: any, adj: StockAdjustment) => getStatusBadge(adj.status)
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, adj: StockAdjustment) => (
                adj.status === 'draft' ? (
                    <Button
                        size="sm"
                        variant="outline"
                        className="text-xs h-7 text-emerald-600 border-emerald-500/30 hover:bg-emerald-50"
                        disabled={postingId === adj.id}
                        onClick={() => handlePost(adj.id)}
                    >
                        <CheckCircle2 className="h-3 w-3 mr-1" />
                        {postingId === adj.id ? t('Posting...') : t('Approve & Reconcile')}
                    </Button>
                ) : (
                    <span className="text-xs text-muted-foreground flex items-center gap-1 font-mono">
                        <ShieldCheck className="h-3 w-3 text-emerald-500" />
                        {t('Sealed')}
                    </span>
                )
            )
        }
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Inventory & Stock') }, { label: t('Stock Adjustments') }]}
            pageTitle={t('Stock Adjustments & Cycle Counting')}
            pageActions={
                <Button onClick={() => { setIsCreateOpen(true); if (formData.items.length === 0) addItemRow(); }} className="flex items-center gap-2">
                    <Plus className="h-4 w-4" />
                    {t('New Cycle Count / Adjustment')}
                </Button>
            }
        >
            <Head title={t('Stock Adjustments & Cycle Counting')} />

            <div className="space-y-6">
                {/* Metric Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Total Adjustments')}
                            </CardTitle>
                            <Scale className="h-4 w-4 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.total_adjustments}</div>
                            <p className="text-xs text-muted-foreground mt-1">{t('Recorded audit adjustments')}</p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Net Variance Value')}
                            </CardTitle>
                            <ArrowUpDown className="h-4 w-4 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400">
                                {Number(statistics.total_variance_value).toLocaleString()} TZS
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">{t('Total financial impact')}</p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Posted & Reconciled')}
                            </CardTitle>
                            <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                {statistics.approved_count}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">{t('Inventory ledger updated')}</p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Pending Approval')}
                            </CardTitle>
                            <Clock className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {statistics.pending_count}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">{t('Awaiting supervisor review')}</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Adjustments Table */}
                <Card className="shadow-sm border border-border/60">
                    <CardContent className="p-0">
                        {adjustments.data.length > 0 ? (
                            <DataTable
                                columns={tableColumns}
                                data={adjustments.data}
                            />
                        ) : (
                            <div className="p-8 text-center">
                                <NoRecordsFound icon={Scale} title={t('No stock adjustments found. Click New Cycle Count to create one.')} />
                            </div>
                        )}
                    </CardContent>
                    {adjustments.data.length > 0 && (
                        <div className="p-4 border-t flex justify-end">
                            <Pagination data={adjustments} />
                        </div>
                    )}
                </Card>
            </div>

            {/* Create Adjustment Dialog */}
            <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Scale className="h-5 w-5 text-primary" />
                            {t('New Physical Stock Count & Adjustment')}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={submitCreate} className="space-y-4 py-2">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label htmlFor="warehouse_id">{t('Warehouse')}</Label>
                                <Select
                                    value={formData.warehouse_id}
                                    onValueChange={(val) => setFormData({ ...formData, warehouse_id: val, items: [] })}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select warehouse')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map(wh => (
                                            <SelectItem key={wh.id} value={wh.id.toString()}>
                                                {wh.name} ({wh.code})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label htmlFor="adjustment_type">{t('Adjustment Type')}</Label>
                                <Select
                                    value={formData.adjustment_type}
                                    onValueChange={(val) => setFormData({ ...formData, adjustment_type: val })}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="cycle_count">{t('Physical Cycle Count')}</SelectItem>
                                        <SelectItem value="damage_spoilage">{t('Damage / Spoilage Write-Off')}</SelectItem>
                                        <SelectItem value="theft_loss">{t('Theft / Discrepancy Loss')}</SelectItem>
                                        <SelectItem value="revaluation">{t('Inventory Revaluation')}</SelectItem>
                                        <SelectItem value="opening_balance">{t('Opening Stock Inflow')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label>{t('Count Date')}</Label>
                                <DatePicker
                                    value={formData.adjustment_date}
                                    onChange={(val) => setFormData({ ...formData, adjustment_date: val })}
                                />
                            </div>
                            <div>
                                <Label htmlFor="reason">{t('Reason & Auditor Notes')}</Label>
                                <Input
                                    id="reason"
                                    value={formData.reason}
                                    onChange={(e) => setFormData({ ...formData, reason: e.target.value })}
                                    placeholder={t('Quarterly audit verification...')}
                                    required
                                />
                            </div>
                        </div>

                        {/* Items Counting Table */}
                        <div className="space-y-2"><Label htmlFor="stock-scan">{t('Scan SKU, Barcode or Serial')}</Label><div className="flex gap-2"><Input id="stock-scan" value={scanCode} onChange={e => setScanCode(e.target.value)} onKeyDown={e => { if (e.key === 'Enter') { e.preventDefault(); scanProduct(); } }} /><Button type="button" variant="outline" disabled={scanning || !formData.warehouse_id} onClick={scanProduct}>{t('Scan')}</Button></div>{scanError && <p role="alert" className="text-sm text-destructive">{scanError}</p>}</div>
                        <div className="space-y-2 pt-2 border-t">
                            <div className="flex items-center justify-between">
                                <Label className="text-xs font-semibold">{t('Counted Product Lines')}</Label>
                                <Button type="button" variant="outline" size="sm" onClick={addItemRow} className="text-xs">
                                    <Plus className="h-3 w-3 mr-1" />
                                    {t('Add Product')}
                                </Button>
                            </div>

                            <div className="border rounded divide-y text-xs">
                                {formData.items.map((row, idx) => {
                                    const diff = Number(row.counted_quantity || 0) - row.system_quantity;
                                    return (
                                        <div key={idx} className="p-2.5 flex items-center justify-between gap-3 hover:bg-muted/10">
                                            <div className="flex-1">
                                                <Select
                                                    value={row.product_id}
                                                    onValueChange={(val) => handleProductChange(idx, val)}
                                                >
                                                    <SelectTrigger className="h-8">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {products.map(p => (
                                                            <SelectItem key={p.id} value={p.id.toString()}>
                                                                {p.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="text-right w-24">
                                                <span className="text-muted-foreground block text-[10px]">{t('System Stock')}</span>
                                                <span className="font-semibold">{row.system_quantity}</span>
                                            </div>

                                            <div className="w-24">
                                                <span className="text-muted-foreground block text-[10px]">{t('Counted')}</span>
                                                <Input
                                                    type="number"
                                                    value={row.counted_quantity}
                                                    onChange={(e) => handleCountChange(idx, e.target.value)}
                                                    className="h-8 text-right font-semibold"
                                                    required
                                                />
                                            </div>

                                            <div className="text-right w-20">
                                                <span className="text-muted-foreground block text-[10px]">{t('Variance')}</span>
                                                <span className={`font-bold ${diff < 0 ? 'text-destructive' : 'text-emerald-600'}`}>
                                                    {diff > 0 ? `+${diff}` : diff}
                                                </span>
                                            </div>

                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-7 w-7 text-destructive"
                                                onClick={() => removeItemRow(idx)}
                                            >
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsCreateOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={submitting || formData.items.length === 0}>
                                {submitting ? t('Saving...') : t('Create Adjustment Order')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
