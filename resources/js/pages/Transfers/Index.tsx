import { useState } from 'react';
import { Head, usePage, router, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import {
    Plus, Trash2, ArrowRightLeft, Truck, Boxes, CheckCircle2,
    Clock, AlertTriangle, Eye, Printer, ShieldCheck, QrCode, MapPin
} from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { Badge } from '@/components/ui/badge';
import NoRecordsFound from '@/components/no-records-found';
import Create from './create';
import { Transfer as TransferType, TransfersIndexProps, TransferFilters, TransferModalState } from './types';
import { formatDate } from '@/utils/helpers';

export default function Index() {
    const { t } = useTranslation();
    const { transfers, warehouses, products, statistics, auth } = usePage<TransfersIndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState<TransferFilters>({
        product_name: urlParams.get('product_name') || '',
        from_warehouse: urlParams.get('from_warehouse') || '',
        to_warehouse: urlParams.get('to_warehouse') || '',
        status: urlParams.get('status') || '',
        search: urlParams.get('search') || '',
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [showFilters, setShowFilters] = useState(false);
    const [modalState, setModalState] = useState<TransferModalState>({
        isOpen: false,
        mode: '',
        data: null
    });

    // Receiving Inspection Modal State
    const [receivingTransfer, setReceivingTransfer] = useState<TransferType | null>(null);
    const [inspectionNotes, setInspectionNotes] = useState('');
    const [itemInspections, setItemInspections] = useState<Record<number, number>>({});
    const [isReceiving, setIsReceiving] = useState(false);

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'transfers.destroy',
        defaultMessage: t('Are you sure you want to delete this transfer?')
    });

    const handleFilter = () => {
        router.get(route('transfers.index'), {
            ...filters,
            per_page: perPage,
            sort: sortField,
            direction: sortDirection
        }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('transfers.index'), {
            ...filters,
            per_page: perPage,
            sort: field,
            direction
        }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ product_name: '', from_warehouse: '', to_warehouse: '', status: '', search: '' });
        router.get(route('transfers.index'), { per_page: perPage });
    };

    const openModal = (mode: 'add', data: TransferType | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const openReceiveModal = (transfer: TransferType) => {
        setReceivingTransfer(transfer);
        setInspectionNotes('');
        const initial: Record<number, number> = {};
        if (transfer.items && transfer.items.length > 0) {
            transfer.items.forEach(it => {
                initial[it.id] = Number(it.dispatched_quantity);
            });
        }
        setItemInspections(initial);
    };

    const submitReceive = () => {
        if (!receivingTransfer) return;
        setIsReceiving(true);

        router.post(route('transfers.receive', receivingTransfer.id), {
            inspections: itemInspections,
            inspection_notes: inspectionNotes,
        }, {
            onSuccess: () => {
                setReceivingTransfer(null);
                setIsReceiving(false);
            },
            onError: () => setIsReceiving(false),
        });
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'in_transit':
                return (
                    <Badge variant="outline" className="bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30 flex items-center gap-1">
                        <Truck className="h-3 w-3 animate-pulse" />
                        {t('In Transit')}
                    </Badge>
                );
            case 'received':
            case 'completed':
                return (
                    <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 flex items-center gap-1">
                        <CheckCircle2 className="h-3 w-3" />
                        {t('Received')}
                    </Badge>
                );
            case 'draft':
                return (
                    <Badge variant="outline" className="bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30 flex items-center gap-1">
                        <Clock className="h-3 w-3" />
                        {t('Draft')}
                    </Badge>
                );
            case 'cancelled':
                return (
                    <Badge variant="destructive" className="flex items-center gap-1">
                        <AlertTriangle className="h-3 w-3" />
                        {t('Cancelled')}
                    </Badge>
                );
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    const tableColumns = [
        {
            key: 'transfer_number',
            header: t('STO Number'),
            sortable: true,
            render: (value: any, transfer: TransferType) => (
                <div className="flex flex-col">
                    <Link
                        href={route('transfers.show', transfer.id)}
                        className="font-semibold text-primary hover:underline flex items-center gap-1 text-sm"
                    >
                        {transfer.transfer_number || `STO-2026-${String(transfer.id).padStart(5, '0')}`}
                    </Link>
                    <span className="text-xs text-muted-foreground">
                        {transfer.date ? formatDate(transfer.date) : '-'}
                    </span>
                </div>
            )
        },
        {
            key: 'route',
            header: t('Routing (Origin → Destination)'),
            render: (_: any, transfer: TransferType) => (
                <div className="flex items-center gap-2 text-xs">
                    <div className="flex flex-col">
                        <span className="font-medium text-foreground">{transfer.from_warehouse?.name}</span>
                        {transfer.from_warehouse?.code && (
                            <span className="text-muted-foreground">{transfer.from_warehouse.code}</span>
                        )}
                    </div>
                    <ArrowRightLeft className="h-3.5 w-3.5 text-muted-foreground shrink-0" />
                    <div className="flex flex-col">
                        <span className="font-medium text-foreground">{transfer.to_warehouse?.name}</span>
                        {transfer.to_warehouse?.code && (
                            <span className="text-muted-foreground">{transfer.to_warehouse.code}</span>
                        )}
                    </div>
                </div>
            )
        },
        {
            key: 'items',
            header: t('Items & Volume'),
            render: (_: any, transfer: TransferType) => {
                const count = transfer.items?.length || transfer.total_items_count || 1;
                const totalQty = transfer.total_transferred_qty || transfer.quantity;
                const firstItemName = transfer.items?.[0]?.product?.name || transfer.product?.name || t('Goods');

                return (
                    <div className="flex flex-col text-xs">
                        <span className="font-medium text-foreground">
                            {count > 1 ? `${count} ${t('Products')}` : firstItemName}
                        </span>
                        <span className="text-muted-foreground">
                            {Number(totalQty).toLocaleString()} {t('Units')}
                        </span>
                    </div>
                );
            }
        },
        {
            key: 'logistics',
            header: t('Carrier & Vehicle'),
            render: (_: any, transfer: TransferType) => (
                <div className="flex flex-col text-xs">
                    {transfer.vehicle_number ? (
                        <>
                            <span className="font-medium text-foreground">{transfer.vehicle_number}</span>
                            <span className="text-muted-foreground">{transfer.driver_name || transfer.shipping_carrier || '-'}</span>
                        </>
                    ) : (
                        <span className="text-muted-foreground">{transfer.driver_name || transfer.shipping_carrier || 'Direct Handover'}</span>
                    )}
                </div>
            )
        },
        {
            key: 'status',
            header: t('Status'),
            sortable: true,
            render: (_: any, transfer: TransferType) => getStatusBadge(transfer.status)
        },
        {
            key: 'verification',
            header: t('Seal & Proof'),
            render: (_: any, transfer: TransferType) => (
                transfer.verification ? (
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <span className="inline-flex items-center gap-1 text-xs font-mono text-emerald-600 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                                    <ShieldCheck className="h-3 w-3" />
                                    {transfer.verification.verification_hash.substring(0, 8)}...
                                </span>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Cryptographically Sealed & Verified')}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                ) : (
                    <span className="text-xs text-muted-foreground">-</span>
                )
            )
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, transfer: TransferType) => (
                <div className="flex items-center gap-1">
                    {/* View Details */}
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon" className="h-8 w-8" asChild>
                                    <Link href={route('transfers.show', transfer.id)}>
                                        <Eye className="h-4 w-4" />
                                    </Link>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('View Waybill')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    {/* Receive Inspection (if in transit) */}
                    {transfer.status === 'in_transit' && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                                        onClick={() => openReceiveModal(transfer)}
                                    >
                                        <CheckCircle2 className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('Receive & Inspect')}</p></TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}

                    {/* Print Waybill */}
                    <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon" className="h-8 w-8" asChild>
                                    <Link href={route('transfers.waybill', transfer.id)} target="_blank">
                                        <Printer className="h-4 w-4" />
                                    </Link>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent><p>{t('Print Waybill')}</p></TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    {/* Delete */}
                    {auth.user?.permissions?.includes('delete-transfers') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={() => openDeleteDialog(transfer.id)}
                                        className="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent><p>{t('Delete')}</p></TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                </div>
            )
        }
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Inventory & Stock') }, { label: t('Stock Transfers') }]}
            pageTitle={t('Multi-Location Stock Transfer Orders (STO)')}
            pageActions={
                <div className="flex gap-2">
                    {auth.user?.permissions?.includes('create-transfers') && (
                        <Button onClick={() => openModal('add')} className="flex items-center gap-2">
                            <Plus className="h-4 w-4" />
                            {t('New Transfer Order')}
                        </Button>
                    )}
                </div>
            }
        >
            <Head title={t('Stock Transfers (WMS)')} />

            <div className="space-y-6">
                {/* WMS KPI Stat Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Total Transfer Orders')}
                            </CardTitle>
                            <ArrowRightLeft className="h-4 w-4 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics?.total_transfers ?? transfers.total}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {statistics?.total_quantity_moved ? Number(statistics.total_quantity_moved).toLocaleString() : 0} {t('units transferred')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('In Transit (Logistics)')}
                            </CardTitle>
                            <Truck className="h-4 w-4 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                {statistics?.in_transit ?? 0}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Active overland shipments')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Completed / Received')}
                            </CardTitle>
                            <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                {statistics?.received ?? 0}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Fully inspected & reconciled')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border border-border/60 bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                {t('Active Warehouses')}
                            </CardTitle>
                            <MapPin className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {statistics?.active_warehouses_count ?? warehouses.length}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('DSM, Arusha & Lake Zone')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Filter & Table Card */}
                <Card className="shadow-sm border border-border/60">
                    <CardContent className="p-4 border-b bg-muted/20">
                        <div className="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                            <div className="flex-1 max-w-md">
                                <SearchInput
                                    value={filters.search || ''}
                                    onChange={(value) => setFilters({ ...filters, search: value })}
                                    onSearch={handleFilter}
                                    placeholder={t('Search STO number, driver, vehicle plate...')}
                                />
                            </div>
                            <div className="flex items-center gap-3">
                                <PerPageSelector
                                    routeName="transfers.index"
                                    filters={filters}
                                />
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                            </div>
                        </div>

                        {showFilters && (
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-3 mt-4 pt-4 border-t">
                                <div>
                                    <label className="block text-xs font-medium text-muted-foreground mb-1">{t('Origin Warehouse')}</label>
                                    <Select
                                        value={filters.from_warehouse}
                                        onValueChange={(value) => setFilters({ ...filters, from_warehouse: value })}
                                    >
                                        <SelectTrigger className="h-9">
                                            <SelectValue placeholder={t('All Warehouses')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">{t('All Warehouses')}</SelectItem>
                                            {warehouses.map((wh) => (
                                                <SelectItem key={wh.id} value={wh.id.toString()}>{wh.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-muted-foreground mb-1">{t('Destination Warehouse')}</label>
                                    <Select
                                        value={filters.to_warehouse || ''}
                                        onValueChange={(value) => setFilters({ ...filters, to_warehouse: value })}
                                    >
                                        <SelectTrigger className="h-9">
                                            <SelectValue placeholder={t('All Warehouses')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">{t('All Warehouses')}</SelectItem>
                                            {warehouses.map((wh) => (
                                                <SelectItem key={wh.id} value={wh.id.toString()}>{wh.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="block text-xs font-medium text-muted-foreground mb-1">{t('Transfer Status')}</label>
                                    <Select
                                        value={filters.status || ''}
                                        onValueChange={(value) => setFilters({ ...filters, status: value })}
                                    >
                                        <SelectTrigger className="h-9">
                                            <SelectValue placeholder={t('All Statuses')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">{t('All Statuses')}</SelectItem>
                                            <SelectItem value="in_transit">{t('In Transit')}</SelectItem>
                                            <SelectItem value="received">{t('Received')}</SelectItem>
                                            <SelectItem value="draft">{t('Draft')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="flex items-end gap-2">
                                    <Button onClick={handleFilter} size="sm" className="h-9">{t('Apply Filters')}</Button>
                                    <Button variant="outline" onClick={clearFilters} size="sm" className="h-9">{t('Clear')}</Button>
                                </div>
                            </div>
                        )}
                    </CardContent>

                    <CardContent className="p-0">
                        {transfers.data.length > 0 ? (
                            <DataTable
                                columns={tableColumns}
                                data={transfers.data}
                                onSort={handleSort}
                                sortKey={sortField}
                                sortDirection={sortDirection as 'asc' | 'desc'}
                            />
                        ) : (
                            <div className="p-8 text-center">
                                <NoRecordsFound icon={Boxes} title={t('No stock transfer orders match your filter criteria.')} />
                            </div>
                        )}
                    </CardContent>

                    {transfers.data.length > 0 && (
                        <div className="p-4 border-t flex justify-end">
                            <Pagination data={transfers} />
                        </div>
                    )}
                </Card>
            </div>

            {/* Create Stock Transfer Modal */}
            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.isOpen && <Create onSuccess={closeModal} />}
            </Dialog>

            {/* Receive & Inspect Delivery Modal */}
            <Dialog open={!!receivingTransfer} onOpenChange={() => setReceivingTransfer(null)}>
                <DialogContent className="max-w-xl">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                            {t('Receive & Inspect Transfer Order')} - {receivingTransfer?.transfer_number}
                        </DialogTitle>
                    </DialogHeader>

                    {receivingTransfer && (
                        <div className="space-y-4 py-2">
                            <div className="p-3 bg-muted/40 rounded-lg text-xs space-y-1">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">{t('From Warehouse')}:</span>
                                    <span className="font-medium">{receivingTransfer.from_warehouse?.name}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">{t('To Warehouse')}:</span>
                                    <span className="font-medium">{receivingTransfer.to_warehouse?.name}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">{t('Vehicle Plate')}:</span>
                                    <span className="font-medium">{receivingTransfer.vehicle_number || t('Not specified')}</span>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label className="text-xs font-semibold">{t('Confirm Quantities Received (Units)')}</Label>
                                {receivingTransfer.items && receivingTransfer.items.length > 0 ? (
                                    <div className="border rounded divide-y text-xs">
                                        {receivingTransfer.items.map(it => (
                                            <div key={it.id} className="p-2 flex items-center justify-between gap-4">
                                                <div className="flex-1">
                                                    <span className="font-medium">{it.product?.name}</span>
                                                    <span className="block text-muted-foreground text-[11px]">
                                                        {t('Dispatched')}: {it.dispatched_quantity}
                                                    </span>
                                                </div>
                                                <div className="w-28">
                                                    <Input
                                                        type="number"
                                                        value={itemInspections[it.id] ?? it.dispatched_quantity}
                                                        onChange={(e) => setItemInspections({
                                                            ...itemInspections,
                                                            [it.id]: Number(e.target.value)
                                                        })}
                                                        className="h-8 text-right"
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-xs text-muted-foreground">
                                        {t('Confirm receipt of :qty units of :prod.', {
                                            qty: receivingTransfer.quantity,
                                            prod: receivingTransfer.product?.name || 'Product',
                                        })}
                                    </p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="inspectionNotes" className="text-xs">{t('Inspection & Discrepancy Notes')}</Label>
                                <Input
                                    id="inspectionNotes"
                                    value={inspectionNotes}
                                    onChange={(e) => setInspectionNotes(e.target.value)}
                                    placeholder={t('All goods received intact / seal intact / remarks...')}
                                />
                            </div>
                        </div>
                    )}

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setReceivingTransfer(null)}>
                            {t('Cancel')}
                        </Button>
                        <Button onClick={submitReceive} disabled={isReceiving} className="bg-emerald-600 hover:bg-emerald-700">
                            {isReceiving ? t('Processing...') : t('Confirm Receipt & Seal')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation */}
            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={(open) => !open && closeDeleteDialog()}
                onConfirm={confirmDelete}
                title={t('Delete Stock Transfer')}
                message={deleteState.message}
            />
        </AuthenticatedLayout>
    );
}
