import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    ArrowLeft, Printer, Truck, CheckCircle2, ShieldCheck, MapPin,
    ArrowRight, Clock, AlertTriangle, Boxes, User, Calendar
} from 'lucide-react';
import { formatDate } from '@/utils/helpers';
import { Transfer } from './types';

interface ShowProps {
    transfer: Transfer;
}

export default function Show({ transfer }: ShowProps) {
    const { t } = useTranslation();
    const [isReceiveOpen, setIsReceiveOpen] = useState(false);
    const [inspectionNotes, setInspectionNotes] = useState('');
    const [itemInspections, setItemInspections] = useState<Record<number, number>>({});
    const [processing, setProcessing] = useState(false);

    const openReceiveModal = () => {
        setIsReceiveOpen(true);
        const initial: Record<number, number> = {};
        if (transfer.items && transfer.items.length > 0) {
            transfer.items.forEach(it => {
                initial[it.id] = Number(it.dispatched_quantity);
            });
        }
        setItemInspections(initial);
    };

    const submitReceive = () => {
        setProcessing(true);
        router.post(route('transfers.receive', transfer.id), {
            inspections: itemInspections,
            inspection_notes: inspectionNotes,
        }, {
            onSuccess: () => {
                setIsReceiveOpen(false);
                setProcessing(false);
            },
            onError: () => setProcessing(false),
        });
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'in_transit':
                return (
                    <Badge variant="outline" className="bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30 flex items-center gap-1.5 py-1 px-3 text-sm">
                        <Truck className="h-4 w-4 animate-pulse" />
                        {t('In Transit (En Route)')}
                    </Badge>
                );
            case 'received':
            case 'completed':
                return (
                    <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 flex items-center gap-1.5 py-1 px-3 text-sm">
                        <CheckCircle2 className="h-4 w-4" />
                        {t('Received & Verified')}
                    </Badge>
                );
            case 'draft':
                return (
                    <Badge variant="outline" className="bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30 flex items-center gap-1.5 py-1 px-3 text-sm">
                        <Clock className="h-4 w-4" />
                        {t('Draft Order')}
                    </Badge>
                );
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Inventory & Stock'), url: route('transfers.index') },
                { label: t('Stock Transfers'), url: route('transfers.index') },
                { label: transfer.transfer_number || `STO-#${transfer.id}` },
            ]}
            pageTitle={t('Stock Transfer Order & Waybill')}
            pageActions={
                <div className="flex items-center gap-2">
                    <Button variant="outline" asChild size="sm">
                        <Link href={route('transfers.index')}>
                            <ArrowLeft className="h-4 w-4 mr-1.5" />
                            {t('Back to Transfers')}
                        </Link>
                    </Button>

                    <Button variant="outline" asChild size="sm">
                        <Link href={route('transfers.waybill', transfer.id)} target="_blank">
                            <Printer className="h-4 w-4 mr-1.5" />
                            {t('Print Transit Waybill')}
                        </Link>
                    </Button>

                    {transfer.status === 'in_transit' && (
                        <Button onClick={openReceiveModal} size="sm" className="bg-emerald-600 hover:bg-emerald-700">
                            <CheckCircle2 className="h-4 w-4 mr-1.5" />
                            {t('Receive & Inspect Delivery')}
                        </Button>
                    )}
                </div>
            }
        >
            <Head title={`${transfer.transfer_number || 'STO'} - ${t('Waybill')}`} />

            <div className="space-y-6 max-w-5xl mx-auto">
                {/* Header Banner */}
                <Card className="border border-border/60 bg-card overflow-hidden">
                    <div className="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-border/50">
                        <div className="space-y-1">
                            <div className="flex items-center gap-3">
                                <h1 className="text-2xl font-bold tracking-tight text-foreground font-mono">
                                    {transfer.transfer_number || `STO-2026-${String(transfer.id).padStart(5, '0')}`}
                                </h1>
                                {getStatusBadge(transfer.status)}
                            </div>
                            <p className="text-xs text-muted-foreground flex items-center gap-2">
                                <Calendar className="h-3.5 w-3.5" />
                                {t('Created on')}: {formatDate(transfer.created_at)}
                                {transfer.creator && ` • ${t('Initiated by')}: ${transfer.creator.name}`}
                            </p>
                        </div>

                        {transfer.verification && (
                            <div className="flex items-center gap-3 p-2.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-700 dark:text-emerald-400">
                                <ShieldCheck className="h-5 w-5 shrink-0" />
                                <div>
                                    <div className="font-semibold">{t('Cryptographically Sealed')}</div>
                                    <div className="font-mono text-[11px] truncate max-w-[220px]">
                                        {transfer.verification.verification_hash}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Routing Cards: Origin to Destination */}
                    <CardContent className="p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 relative">
                            {/* Origin Warehouse */}
                            <div className="p-4 rounded-xl border border-amber-500/30 bg-amber-500/5 space-y-2">
                                <div className="flex items-center gap-2 text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">
                                    <MapPin className="h-4 w-4" />
                                    {t('Origin (Source Warehouse)')}
                                </div>
                                <div className="text-lg font-bold text-foreground">
                                    {transfer.from_warehouse?.name}
                                </div>
                                <div className="text-xs text-muted-foreground space-y-0.5">
                                    {transfer.from_warehouse?.code && <div>{t('Code')}: <span className="font-mono font-medium">{transfer.from_warehouse.code}</span></div>}
                                    {transfer.from_warehouse?.city && <div>{t('Location')}: {transfer.from_warehouse.city}</div>}
                                </div>
                            </div>

                            {/* Destination Warehouse */}
                            <div className="p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5 space-y-2">
                                <div className="flex items-center gap-2 text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                                    <MapPin className="h-4 w-4" />
                                    {t('Destination Warehouse')}
                                </div>
                                <div className="text-lg font-bold text-foreground">
                                    {transfer.to_warehouse?.name}
                                </div>
                                <div className="text-xs text-muted-foreground space-y-0.5">
                                    {transfer.to_warehouse?.code && <div>{t('Code')}: <span className="font-mono font-medium">{transfer.to_warehouse.code}</span></div>}
                                    {transfer.to_warehouse?.city && <div>{t('Location')}: {transfer.to_warehouse.city}</div>}
                                </div>
                            </div>
                        </div>

                        {/* Logistics Details */}
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-border/60 text-xs">
                            <div>
                                <span className="text-muted-foreground block">{t('Driver / Carrier')}</span>
                                <span className="font-medium text-foreground">{transfer.driver_name || transfer.shipping_carrier || '-'}</span>
                            </div>
                            <div>
                                <span className="text-muted-foreground block">{t('Vehicle Plate No.')}</span>
                                <span className="font-medium text-foreground font-mono">{transfer.vehicle_number || '-'}</span>
                            </div>
                            <div>
                                <span className="text-muted-foreground block">{t('Dispatched At')}</span>
                                <span className="font-medium text-foreground">
                                    {transfer.dispatched_at ? formatDate(transfer.dispatched_at) : (transfer.date ? formatDate(transfer.date) : '-')}
                                </span>
                            </div>
                            <div>
                                <span className="text-muted-foreground block">{t('Received At')}</span>
                                <span className="font-medium text-foreground">
                                    {transfer.received_at ? formatDate(transfer.received_at) : (transfer.status === 'completed' ? formatDate(transfer.created_at) : t('Pending Arrival'))}
                                </span>
                            </div>
                        </div>

                        {transfer.transit_notes && (
                            <div className="mt-4 p-3 rounded-lg bg-muted/40 text-xs">
                                <span className="font-semibold text-muted-foreground block mb-0.5">{t('Transit & Route Notes')}:</span>
                                <p className="text-foreground">{transfer.transit_notes}</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Items Manifest Table */}
                <Card className="border border-border/60 shadow-sm">
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base font-semibold flex items-center gap-2">
                            <Boxes className="h-5 w-5 text-primary" />
                            {t('Manifested Stock Items')}
                        </CardTitle>
                        <Badge variant="secondary">
                            {transfer.items?.length || transfer.total_items_count || 1} {t('Line Items')}
                        </Badge>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-muted text-muted-foreground text-xs uppercase font-semibold">
                                    <tr>
                                        <th className="p-3 text-left">#</th>
                                        <th className="p-3 text-left">{t('Product Details')}</th>
                                        <th className="p-3 text-left">{t('Bin Location')}</th>
                                        <th className="p-3 text-right">{t('Dispatched Qty')}</th>
                                        <th className="p-3 text-right">{t('Received Qty')}</th>
                                        <th className="p-3 text-right">{t('Discrepancy')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {transfer.items && transfer.items.length > 0 ? (
                                        transfer.items.map((it, idx) => (
                                            <tr key={it.id} className="hover:bg-muted/10">
                                                <td className="p-3 text-muted-foreground text-xs">{idx + 1}</td>
                                                <td className="p-3">
                                                    <div className="font-medium text-foreground">{it.product?.name}</div>
                                                    {it.product?.sku && (
                                                        <div className="text-xs text-muted-foreground font-mono">SKU: {it.product.sku}</div>
                                                    )}
                                                </td>
                                                <td className="p-3 text-xs">
                                                    {it.from_bin ? (
                                                        <span className="font-mono bg-muted px-2 py-0.5 rounded">{it.from_bin.bin_code}</span>
                                                    ) : (
                                                        <span className="text-muted-foreground">-</span>
                                                    )}
                                                </td>
                                                <td className="p-3 text-right font-medium">
                                                    {Number(it.dispatched_quantity).toLocaleString()}
                                                </td>
                                                <td className="p-3 text-right">
                                                    {it.received_quantity !== null && it.received_quantity !== undefined ? (
                                                        <span className="text-emerald-600 font-medium">{Number(it.received_quantity).toLocaleString()}</span>
                                                    ) : (
                                                        <span className="text-muted-foreground">{t('Pending')}</span>
                                                    )}
                                                </td>
                                                <td className="p-3 text-right">
                                                    {it.discrepancy_quantity && Number(it.discrepancy_quantity) !== 0 ? (
                                                        <span className="text-destructive font-medium">-{it.discrepancy_quantity}</span>
                                                    ) : (
                                                        <span className="text-muted-foreground">0</span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td className="p-3 text-muted-foreground text-xs">1</td>
                                            <td className="p-3 font-medium">{transfer.product?.name || t('Goods')}</td>
                                            <td className="p-3 text-muted-foreground text-xs">-</td>
                                            <td className="p-3 text-right font-medium">{Number(transfer.quantity).toLocaleString()}</td>
                                            <td className="p-3 text-right text-emerald-600 font-medium">{Number(transfer.quantity).toLocaleString()}</td>
                                            <td className="p-3 text-right text-muted-foreground">0</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Inspection & Sign-off Section */}
                {transfer.inspection_notes && (
                    <Card className="border border-border/60">
                        <CardHeader>
                            <CardTitle className="text-sm font-semibold flex items-center gap-2">
                                <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                                {t('Receiving Inspection Notes')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-xs text-foreground">
                            <p>{transfer.inspection_notes}</p>
                            {transfer.receiver && (
                                <p className="text-muted-foreground mt-2">
                                    {t('Inspected & Signed by')}: <span className="font-semibold">{transfer.receiver.name}</span>
                                </p>
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>

            {/* Receive & Inspect Modal */}
            <Dialog open={isReceiveOpen} onOpenChange={setIsReceiveOpen}>
                <DialogContent className="max-w-xl">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                            {t('Receive Delivery Inspection')}
                        </DialogTitle>
                    </DialogHeader>

                    <div className="space-y-4 py-2">
                        <p className="text-xs text-muted-foreground">
                            {t('Verify goods delivered by vehicle')} <span className="font-mono font-medium">{transfer.vehicle_number}</span> {t('into')} <span className="font-medium">{transfer.to_warehouse?.name}</span>.
                        </p>

                        <div className="space-y-2">
                            <Label className="text-xs font-semibold">{t('Verified Quantities (Units)')}</Label>
                            {transfer.items && transfer.items.length > 0 && (
                                <div className="border rounded divide-y text-xs">
                                    {transfer.items.map(it => (
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
                            )}
                        </div>

                        <div>
                            <Label htmlFor="inspectionNotes" className="text-xs">{t('Discrepancy / Inspection Remarks')}</Label>
                            <Input
                                id="inspectionNotes"
                                value={inspectionNotes}
                                onChange={(e) => setInspectionNotes(e.target.value)}
                                placeholder={t('All goods in good order / seal intact')}
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setIsReceiveOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button onClick={submitReceive} disabled={processing} className="bg-emerald-600 hover:bg-emerald-700">
                            {processing ? t('Saving...') : t('Sign & Accept Delivery')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
