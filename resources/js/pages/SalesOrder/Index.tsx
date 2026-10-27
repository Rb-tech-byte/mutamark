import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/dialog";
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    ShoppingBag,
    Plus,
    Truck,
    Receipt,
    CheckCircle2,
    Clock,
    Lock,
    Unlock,
    Search,
    FileText,
    Warehouse as WarehouseIcon,
    AlertCircle,
    Trash2,
    Layers,
    DollarSign,
    Replace,
    ArrowRight
} from 'lucide-react';

interface SalesOrderItem {
    id: number;
    product_name: string;
    sku?: string;
    quantity: number;
    fulfilled_quantity: number;
    unit_price: number;
    total_amount: number;
}

interface SalesOrder {
    id: number;
    order_number: string;
    proposal_id?: number | null;
    customer_id: number;
    warehouse_id?: number | null;
    order_date: string;
    delivery_due_date?: string | null;
    status: 'confirmed' | 'stock_reserved' | 'partially_fulfilled' | 'fulfilled' | 'cancelled';
    stock_reserved: boolean;
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    payment_terms?: string;
    notes?: string;
    customer?: { id: number; name: string; email?: string; mobile_no?: string };
    warehouse?: { id: number; name: string };
    proposal?: { id: number; proposal_number: string };
    items?: SalesOrderItem[];
}

interface Props {
    orders: {
        data: SalesOrder[];
        links: any[];
        total: number;
        current_page: number;
    };
    metrics: {
        total_orders: number;
        confirmed_count: number;
        stock_reserved_count: number;
        fulfilled_count: number;
        total_order_value: number;
    };
    customers: Array<{ id: number; name: string; email?: string; mobile_no?: string }>;
    warehouses: Array<{ id: number; name: string }>;
    products: Array<{ id: number; name: string; sku?: string; sale_price?: number }>;
    convertible_proposals: any[];
    filters: { search?: string; status?: string };
}

export default function SalesOrderIndex({
    orders,
    metrics,
    customers,
    warehouses,
    products,
    convertible_proposals,
    filters
}: Props) {
    const { t } = useTranslation();
    const [search, setSearch] = useState(filters.search || '');
    const [selectedOrder, setSelectedOrder] = useState<SalesOrder | null>(null);
    const [detailsModalOpen, setDetailsModalOpen] = useState(false);
    const [createModalOpen, setCreateModalOpen] = useState(false);
    const [convertModalOpen, setConvertModalOpen] = useState(false);
    const [deliveryOrder, setDeliveryOrder] = useState<SalesOrder | null>(null);
    const deliveryForm = useForm({ items: [] as { id: number; quantity: number }[] });

    // Create Order Form
    const orderForm = useForm({
        customer_id: '',
        warehouse_id: warehouses[0]?.id ? String(warehouses[0].id) : '',
        order_date: new Date().toISOString().split('T')[0],
        delivery_due_date: new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0],
        payment_terms: 'Due on Receipt',
        notes: '',
        items: [
            { product_id: '', product_name: '', sku: '', quantity: '1', unit_price: '0', discount_amount: '0', tax_amount: '0' }
        ]
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('sales-orders.index'), { search }, { preserveState: true });
    };

    const handleAddItem = () => {
        orderForm.setData('items', [
            ...orderForm.data.items,
            { product_id: '', product_name: '', sku: '', quantity: '1', unit_price: '0', discount_amount: '0', tax_amount: '0' }
        ]);
    };

    const handleRemoveItem = (idx: number) => {
        if (orderForm.data.items.length <= 1) return;
        const updated = [...orderForm.data.items];
        updated.splice(idx, 1);
        orderForm.setData('items', updated);
    };

    const handleProductSelect = (idx: number, productId: string) => {
        const prod = products.find(p => String(p.id) === productId);
        const updated = [...orderForm.data.items];
        updated[idx] = {
            ...updated[idx],
            product_id: productId,
            product_name: prod?.name || '',
            sku: prod?.sku || '',
            unit_price: String(prod?.sale_price || 0),
        };
        orderForm.setData('items', updated);
    };

    const handleCreateOrder = (e: React.FormEvent) => {
        e.preventDefault();
        orderForm.post(route('sales-orders.store'), {
            onSuccess: () => {
                setCreateModalOpen(false);
                orderForm.reset();
            }
        });
    };

    const handleConvertToDelivery = (order: SalesOrder) => {
        deliveryForm.setData('items', (order.items ?? []).filter(item => Number(item.fulfilled_quantity) < Number(item.quantity)).map(item => ({ id: item.id, quantity: Number(item.quantity) - Number(item.fulfilled_quantity) })));
        setDeliveryOrder(order);
    };

    const handleConvertToInvoice = (order: SalesOrder) => {
        router.post(route('sales-orders.convert-invoice', order.id));
    };

    const handleToggleStock = (order: SalesOrder) => {
        router.post(route('sales-orders.toggle-stock', order.id));
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Sales Orders (SO)')} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <Badge className="bg-primary/10 text-primary border-primary/20 text-xs font-semibold">
                                {t('Quote-to-Cash Stage 3 (SRS §3.9.4)')}
                            </Badge>
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground mt-1 flex items-center gap-2">
                            <ShoppingBag className="h-7 w-7 text-primary" />
                            {t('Sales Orders (SO)')}
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            {t('Formal customer commitments, inventory reservation allocation, and dispatch conversions')}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        {convertible_proposals.length > 0 && (
                            <Button
                                variant="outline"
                                onClick={() => setConvertModalOpen(true)}
                                className="flex items-center gap-1.5"
                            >
                                <Replace className="h-4 w-4 text-emerald-600" />
                                {t('Convert Quotation')} ({convertible_proposals.length})
                            </Button>
                        )}
                        <Button
                            onClick={() => setCreateModalOpen(true)}
                            className="flex items-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground font-semibold"
                        >
                            <Plus className="h-4 w-4" />
                            {t('Create Sales Order')}
                        </Button>
                    </div>
                </div>

                {/* Metrics */}
                <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Total Orders')}</span>
                                <ShoppingBag className="h-4 w-4 text-blue-500" />
                            </div>
                            <div className="text-2xl font-bold mt-2">{metrics.total_orders}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-blue-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Confirmed')}</span>
                                <CheckCircle2 className="h-4 w-4 text-blue-500" />
                            </div>
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">{metrics.confirmed_count}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-emerald-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Stock Reserved')}</span>
                                <Lock className="h-4 w-4 text-emerald-500" />
                            </div>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{metrics.stock_reserved_count}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Fulfilled')}</span>
                                <Truck className="h-4 w-4 text-purple-500" />
                            </div>
                            <div className="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2">{metrics.fulfilled_count}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-indigo-500 col-span-2 md:col-span-1">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Committed Value')}</span>
                                <DollarSign className="h-4 w-4 text-indigo-500" />
                            </div>
                            <div className="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-2 truncate">
                                TZS {metrics.total_order_value.toLocaleString(undefined, { minimumFractionDigits: 0 })}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter and Search */}
                <Card className="shadow-sm">
                    <CardHeader className="pb-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <CardTitle className="text-base font-semibold">{t('Orders Registry')}</CardTitle>
                            <CardDescription>{t('Manage customer orders, inventory allocations and dispatch notes')}</CardDescription>
                        </div>
                        <form onSubmit={handleSearch} className="flex items-center gap-2 w-full md:w-80">
                            <Input
                                placeholder={t('Search SO #, customer, item...')}
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="h-9"
                            />
                            <Button type="submit" size="sm" variant="outline" className="h-9 px-3">
                                <Search className="h-4 w-4" />
                            </Button>
                        </form>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('Order #')}</TableHead>
                                        <TableHead>{t('Date / Due')}</TableHead>
                                        <TableHead>{t('Customer')}</TableHead>
                                        <TableHead>{t('Warehouse')}</TableHead>
                                        <TableHead>{t('Stock Allocation')}</TableHead>
                                        <TableHead>{t('Total Amount')}</TableHead>
                                        <TableHead>{t('Status')}</TableHead>
                                        <TableHead className="text-right">{t('Conversions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {orders.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={8} className="text-center py-10 text-muted-foreground">
                                                <ShoppingBag className="h-8 w-8 mx-auto text-muted-foreground/50 mb-2" />
                                                {t('No sales orders found.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        orders.data.map((order) => (
                                            <TableRow key={order.id} className="hover:bg-muted/30">
                                                <TableCell>
                                                    <button
                                                        onClick={() => {
                                                            setSelectedOrder(order);
                                                            setDetailsModalOpen(true);
                                                        }}
                                                        className="font-bold text-primary hover:underline font-mono"
                                                    >
                                                        {order.order_number}
                                                    </button>
                                                    {order.proposal && (
                                                        <div className="text-[11px] text-muted-foreground">
                                                            Ref: {order.proposal.proposal_number}
                                                        </div>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-xs text-muted-foreground">
                                                    <div>{order.order_date}</div>
                                                    {order.delivery_due_date && (
                                                        <div className="text-[11px] text-blue-600 font-medium">Due: {order.delivery_due_date}</div>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="font-medium text-sm">{order.customer?.name}</div>
                                                    <div className="text-xs text-muted-foreground">{order.customer?.mobile_no}</div>
                                                </TableCell>
                                                <TableCell className="text-xs text-muted-foreground">
                                                    {order.warehouse?.name || 'Main Warehouse'}
                                                </TableCell>
                                                <TableCell>
                                                    {order.stock_reserved ? (
                                                        <Badge className="bg-emerald-600 text-white text-[11px] gap-1 cursor-pointer" onClick={() => handleToggleStock(order)}>
                                                            <Lock className="h-3 w-3" />
                                                            {t('Reserved')}
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="outline" className="text-muted-foreground gap-1 cursor-pointer" onClick={() => handleToggleStock(order)}>
                                                            <Unlock className="h-3 w-3" />
                                                            {t('Unlocked')}
                                                        </Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell className="font-bold text-sm">
                                                    TZS {Number(order.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={
                                                        order.status === 'fulfilled'
                                                            ? 'bg-purple-600 text-white'
                                                            : order.status === 'stock_reserved'
                                                            ? 'bg-emerald-600 text-white'
                                                            : 'bg-blue-600 text-white'
                                                    }>
                                                        {order.status.replace('_', ' ')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right space-x-1">
                                                    {order.status !== 'fulfilled' ? (
                                                        <>
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                className="h-8 text-xs border-indigo-300 text-indigo-700 hover:bg-indigo-50"
                                                                onClick={() => handleConvertToDelivery(order)}
                                                                title="Create Delivery Note from this Order"
                                                            >
                                                                <Truck className="h-3.5 w-3.5 mr-1" />
                                                                {t('Dispatch (DN)')}
                                                            </Button>
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                className="h-8 text-xs border-emerald-300 text-emerald-700 hover:bg-emerald-50"
                                                                onClick={() => handleConvertToInvoice(order)}
                                                                title="Issue Commercial Invoice"
                                                            >
                                                                <Receipt className="h-3.5 w-3.5 mr-1" />
                                                                {t('Bill (INV)')}
                                                            </Button>
                                                        </>
                                                    ) : (
                                                        <Badge variant="outline" className="text-purple-600 border-purple-300">
                                                            {t('Fulfilled')}
                                                        </Badge>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* DETAILS MODAL */}
            <Dialog open={detailsModalOpen} onOpenChange={setDetailsModalOpen}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <ShoppingBag className="h-5 w-5 text-primary" />
                            {selectedOrder?.order_number}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Customer')}: {selectedOrder?.customer?.name} • {t('Date')}: {selectedOrder?.order_date}
                        </DialogDescription>
                    </DialogHeader>
                    {selectedOrder && (
                        <div className="space-y-4 pt-2">
                            <div className="border rounded-lg overflow-hidden">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/50 text-xs">
                                            <TableHead>{t('Item Name')}</TableHead>
                                            <TableHead>{t('Quantity')}</TableHead>
                                            <TableHead>{t('Unit Price')}</TableHead>
                                            <TableHead className="text-right">{t('Line Total')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {(selectedOrder.items || []).map((it, i) => (
                                            <TableRow key={i} className="text-xs">
                                                <TableCell className="font-medium">{it.product_name}</TableCell>
                                                <TableCell>{it.quantity}</TableCell>
                                                <TableCell>TZS {Number(it.unit_price).toLocaleString()}</TableCell>
                                                <TableCell className="text-right font-bold">
                                                    TZS {Number(it.total_amount).toLocaleString()}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>

                            <div className="flex justify-between items-center text-sm font-semibold p-3 bg-muted/40 rounded-lg">
                                <span>{t('Grand Total Amount')}:</span>
                                <span className="text-lg text-primary">
                                    TZS {Number(selectedOrder.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                        </div>
                    )}
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDetailsModalOpen(false)}>
                            {t('Close')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* CREATE ORDER DIALOG */}
            <Dialog open={createModalOpen} onOpenChange={setCreateModalOpen}>
                <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>{t('Create Formal Sales Order (SO)')}</DialogTitle>
                        <DialogDescription>{t('Enter customer details, warehouse location, and items to lock inventory allocation')}</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleCreateOrder} className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <Label>{t('Customer *')}</Label>
                                <Select
                                    value={orderForm.data.customer_id}
                                    onValueChange={(val) => orderForm.setData('customer_id', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select customer')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {customers.map((c) => (
                                            <SelectItem key={c.id} value={String(c.id)}>
                                                {c.name} ({c.email || c.mobile_no || 'Client'})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label>{t('Warehouse Location')}</Label>
                                <Select
                                    value={orderForm.data.warehouse_id}
                                    onValueChange={(val) => orderForm.setData('warehouse_id', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select warehouse')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {warehouses.map((w) => (
                                            <SelectItem key={w.id} value={String(w.id)}>
                                                {w.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <Label>{t('Order Date *')}</Label>
                                <Input
                                    type="date"
                                    required
                                    value={orderForm.data.order_date}
                                    onChange={(e) => orderForm.setData('order_date', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('Delivery Due Date')}</Label>
                                <Input
                                    type="date"
                                    value={orderForm.data.delivery_due_date}
                                    onChange={(e) => orderForm.setData('delivery_due_date', e.target.value)}
                                />
                            </div>
                        </div>

                        {/* Line Items */}
                        <div className="space-y-2 border p-3 rounded-lg bg-muted/20">
                            <div className="flex justify-between items-center mb-1">
                                <Label className="font-semibold text-xs uppercase tracking-wider">{t('Order Line Items')}</Label>
                                <Button type="button" size="sm" variant="ghost" onClick={handleAddItem} className="h-7 text-xs text-primary">
                                    <Plus className="h-3.5 w-3.5 mr-1" />
                                    {t('Add Item')}
                                </Button>
                            </div>

                            {orderForm.data.items.map((it, idx) => (
                                <div key={idx} className="grid grid-cols-12 gap-2 items-center bg-card p-2 rounded border">
                                    <div className="col-span-5">
                                        <Select
                                            value={it.product_id}
                                            onValueChange={(val) => handleProductSelect(idx, val)}
                                        >
                                            <SelectTrigger className="h-8 text-xs">
                                                <SelectValue placeholder={t('Select Product')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {products.map((p) => (
                                                    <SelectItem key={p.id} value={String(p.id)}>
                                                        {p.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="col-span-2">
                                        <Input
                                            type="number"
                                            placeholder="Qty"
                                            className="h-8 text-xs"
                                            value={it.quantity}
                                            onChange={(e) => {
                                                const updated = [...orderForm.data.items];
                                                updated[idx].quantity = e.target.value;
                                                orderForm.setData('items', updated);
                                            }}
                                        />
                                    </div>

                                    <div className="col-span-4">
                                        <Input
                                            type="number"
                                            placeholder="Unit Price"
                                            className="h-8 text-xs"
                                            value={it.unit_price}
                                            onChange={(e) => {
                                                const updated = [...orderForm.data.items];
                                                updated[idx].unit_price = e.target.value;
                                                orderForm.setData('items', updated);
                                            }}
                                        />
                                    </div>

                                    <div className="col-span-1 text-right">
                                        {orderForm.data.items.length > 1 && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                className="h-7 w-7 p-0 text-rose-500"
                                                onClick={() => handleRemoveItem(idx)}
                                            >
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div>
                            <Label>{t('Notes / Delivery Instructions')}</Label>
                            <Textarea
                                rows={2}
                                value={orderForm.data.notes}
                                onChange={(e) => orderForm.setData('notes', e.target.value)}
                            />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setCreateModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={orderForm.processing}>
                                {t('Create Order & Reserve Stock')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* CONVERT PROPOSAL DIALOG */}
            <Dialog open={convertModalOpen} onOpenChange={setConvertModalOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>{t('Convert Approved Quotation to Sales Order')}</DialogTitle>
                        <DialogDescription>{t('Select an approved proposal to generate an official Sales Order and allocate stock')}</DialogDescription>
                    </DialogHeader>
                    <div className="space-y-3 max-h-80 overflow-y-auto">
                        {convertible_proposals.map((prop) => (
                            <div key={prop.id} className="p-3 border rounded-lg flex items-center justify-between hover:bg-muted/30">
                                <div>
                                    <div className="font-bold text-sm text-primary">{prop.proposal_number}</div>
                                    <div className="text-xs font-medium">{prop.customer?.name}</div>
                                    <div className="text-xs text-muted-foreground">
                                        TZS {Number(prop.total_amount).toLocaleString()} • {prop.items?.length || 0} {t('items')}
                                    </div>
                                </div>
                                <Button
                                    size="sm"
                                    onClick={() => router.post(route('sales-orders.convert-proposal', prop.id))}
                                    className="bg-emerald-600 hover:bg-emerald-700 text-white text-xs h-8"
                                >
                                    <ArrowRight className="h-3.5 w-3.5 mr-1" />
                                    {t('Convert to SO')}
                                </Button>
                            </div>
                        ))}
                    </div>
                </DialogContent>
            </Dialog>
            <Dialog open={!!deliveryOrder} onOpenChange={open => !open && setDeliveryOrder(null)}>
                <DialogContent><DialogHeader><DialogTitle>{t('Dispatch order')}</DialogTitle><DialogDescription>{t('Choose quantities for this delivery. Remaining quantities stay open.')}</DialogDescription></DialogHeader>
                    <form onSubmit={event => { event.preventDefault(); if (deliveryOrder) deliveryForm.post(route('sales-orders.convert-delivery', deliveryOrder.id), { onSuccess: () => setDeliveryOrder(null) }); }} className="space-y-4">
                        {deliveryForm.data.items.map((item, index) => { const line = deliveryOrder?.items?.find(line => line.id === item.id); return <div key={item.id}><Label htmlFor={`delivery-${item.id}`}>{line?.product_name}</Label><Input id={`delivery-${item.id}`} type="number" required min="0.0001" step="0.0001" max={line ? Number(line.quantity) - Number(line.fulfilled_quantity) : undefined} value={item.quantity} onChange={event => deliveryForm.setData('items', deliveryForm.data.items.map((current, position) => position === index ? { ...current, quantity: Number(event.target.value) } : current))} /></div>; })}
                        {Object.values(deliveryForm.errors).map((error, index) => <p key={index} role="alert" className="text-sm text-destructive">{error}</p>)}
                        <DialogFooter><Button type="submit" disabled={deliveryForm.processing}>{t('Dispatch selected quantities')}</Button></DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
