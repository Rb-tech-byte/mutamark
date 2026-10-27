import { useState } from 'react';
import { Head, usePage, router, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    ClipboardCheck, 
    CheckCircle2, 
    AlertTriangle, 
    ShieldCheck, 
    PackageCheck, 
    ArrowLeft, 
    Building2, 
    Warehouse as WarehouseIcon, 
    Truck, 
    Calendar, 
    FileText,
    Receipt,
    UserCheck,
    Printer
} from "lucide-react";
import { formatCurrency, formatDate } from '@/utils/helpers';

interface GrnItem {
    id: number;
    product_id: number;
    ordered_quantity: number;
    received_quantity: number;
    accepted_quantity: number;
    rejected_quantity: number;
    unit_price: number;
    total_amount: number;
    batch_number?: string;
    rejection_reason?: string;
    product?: {
        id: number;
        name: string;
        sku: string;
    };
}

interface MatchingItemAnalysis {
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
    is_item_matched: boolean;
}

interface MatchingAnalysis {
    has_invoice: boolean;
    match_status: 'matched' | 'variance_detected' | 'unmatched';
    total_qty_variance: number;
    total_price_variance: number;
    items: MatchingItemAnalysis[];
}

interface ShowProps {
    grn: {
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
        quality_inspection_notes?: string;
        rejection_notes?: string;
        status: 'draft' | 'received' | 'posted' | 'cancelled';
        created_at: string;
        vendor?: {
            id: number;
            company_name: string;
            contact_person_name?: string;
            contact_person_mobile?: string;
            billing_address?: string;
        };
        warehouse?: {
            id: number;
            name: string;
            city?: string;
            address?: string;
        };
        receiver?: {
            id: number;
            name: string;
            email: string;
        };
        purchaseInvoice?: {
            id: number;
            invoice_number: string;
            total_amount: number;
            status: string;
        };
        items: GrnItem[];
    };
    matching_analysis: MatchingAnalysis;
    auth: {
        user?: {
            permissions?: string[];
        };
    };
    [key: string]: any;
}

export default function Show() {
    const { t } = useTranslation();
    const { grn, matching_analysis, auth } = usePage<ShowProps>().props;

    useFlashMessages();

    // Inspection Dialog State
    const [isInspectOpen, setIsInspectOpen] = useState(false);
    const { data: inspectData, setData: setInspectData, post: postInspect, processing: inspectProcessing } = useForm({
        inspection_status: grn.inspection_status || 'inspected_passed',
        quality_inspection_notes: grn.quality_inspection_notes || '',
        rejection_notes: grn.rejection_notes || '',
    });

    const handleInspectSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        postInspect(route('goods-received-notes.inspection', grn.id), {
            onSuccess: () => {
                setIsInspectOpen(false);
            }
        });
    };

    const handlePostToStock = () => {
        if (confirm(t('Post accepted items to warehouse inventory? This will increment stock balances immediately.'))) {
            router.post(route('goods-received-notes.post', grn.id));
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Procurement'), url: route('goods-received-notes.index') },
                { label: t('Goods Received (GRN)'), url: route('goods-received-notes.index') },
                { label: grn.grn_number }
            ]}
            pageTitle={`${t('Goods Received Note')} - ${grn.grn_number}`}
        >
            <Head title={`${grn.grn_number} - ${t('GRN Details')}`} />

            {/* Header Banner */}
            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <div className="flex items-center gap-3">
                        <Link href={route('goods-received-notes.index')}>
                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                <ArrowLeft className="h-4 w-4" />
                            </Button>
                        </Link>
                        <h1 className="text-2xl font-bold font-mono tracking-tight text-foreground">
                            {grn.grn_number}
                        </h1>
                        <Badge className={
                            grn.status === 'posted' ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white'
                        }>
                            {grn.status === 'posted' ? t('Posted to Stock') : t('Received')}
                        </Badge>
                        {matching_analysis.match_status === 'matched' ? (
                            <Badge className="bg-emerald-50 text-emerald-700 border-emerald-200 flex items-center gap-1">
                                <ShieldCheck className="h-3 w-3" /> {t('3-Way Matched')}
                            </Badge>
                        ) : matching_analysis.match_status === 'variance_detected' ? (
                            <Badge className="bg-amber-50 text-amber-700 border-amber-200 flex items-center gap-1">
                                <AlertTriangle className="h-3 w-3" /> {t('Variance Detected')}
                            </Badge>
                        ) : (
                            <Badge variant="secondary">{t('Unmatched')}</Badge>
                        )}
                    </div>
                    <p className="text-xs text-muted-foreground mt-1 ml-11">
                        {t('Issued on')} {formatDate(grn.grn_date)} &bull; {grn.warehouse?.name} &bull; {grn.vendor?.company_name}
                    </p>
                </div>

                <div className="flex items-center gap-2">
                    {auth.user?.permissions?.includes('inspect-grn') && (
                        <Button variant="outline" size="sm" onClick={() => setIsInspectOpen(true)}>
                            <ClipboardCheck className="h-4 w-4 mr-1.5" />
                            {t('Update QA Inspection')}
                        </Button>
                    )}

                    {grn.status !== 'posted' && auth.user?.permissions?.includes('post-grn') && (
                        <Button size="sm" onClick={handlePostToStock} className="bg-emerald-600 hover:bg-emerald-700">
                            <PackageCheck className="h-4 w-4 mr-1.5" />
                            {t('Post Intake to Stock')}
                        </Button>
                    )}

                    <Button variant="outline" size="sm" onClick={() => window.print()}>
                        <Printer className="h-4 w-4 mr-1.5" />
                        {t('Print Slip')}
                    </Button>
                </div>
            </div>

            {/* Top Info Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                {/* Vendor Details */}
                <Card className="shadow-sm bg-card/60 backdrop-blur-sm">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase flex items-center gap-1.5">
                            <Building2 className="h-4 w-4 text-primary" /> {t('Vendor / Supplier')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="text-sm">
                        <div className="font-semibold text-foreground">{grn.vendor?.company_name}</div>
                        {grn.vendor?.contact_person_name && (
                            <p className="text-xs text-muted-foreground mt-0.5">{grn.vendor.contact_person_name} ({grn.vendor.contact_person_mobile})</p>
                        )}
                        <p className="text-xs text-muted-foreground mt-1">{grn.vendor?.billing_address || t('Tanzania Commercial Vendor')}</p>
                    </CardContent>
                </Card>

                {/* Warehouse & Shipping */}
                <Card className="shadow-sm bg-card/60 backdrop-blur-sm">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase flex items-center gap-1.5">
                            <WarehouseIcon className="h-4 w-4 text-emerald-500" /> {t('Destination & Carrier')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="text-sm">
                        <div className="font-semibold text-foreground">{grn.warehouse?.name}</div>
                        <p className="text-xs text-muted-foreground mt-0.5">{grn.warehouse?.city || 'Tanzania'}</p>
                        <div className="mt-2 text-xs flex flex-col gap-0.5 text-muted-foreground">
                            <span><Truck className="h-3 w-3 inline mr-1" /> {grn.carrier_name || t('Direct Delivery')}</span>
                            {grn.vehicle_number && <span className="font-mono">{t('Plate')}: {grn.vehicle_number}</span>}
                            {grn.delivery_challan_number && <span>{t('Challan')}: {grn.delivery_challan_number}</span>}
                        </div>
                    </CardContent>
                </Card>

                {/* Inspection & Receiving */}
                <Card className="shadow-sm bg-card/60 backdrop-blur-sm">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-xs font-semibold text-muted-foreground uppercase flex items-center gap-1.5">
                            <UserCheck className="h-4 w-4 text-indigo-500" /> {t('Intake & Verification')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="text-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs text-muted-foreground">{t('PO Reference')}:</span>
                            <span className="font-mono font-semibold">{grn.purchase_order_number || 'Direct Purchase'}</span>
                        </div>
                        <div className="flex items-center justify-between mt-1">
                            <span className="text-xs text-muted-foreground">{t('Received By')}:</span>
                            <span className="text-xs font-medium">{grn.receiver?.name || 'Storekeeper'}</span>
                        </div>
                        <div className="flex items-center justify-between mt-1">
                            <span className="text-xs text-muted-foreground">{t('QA Status')}:</span>
                            <span className="text-xs font-semibold text-emerald-600 capitalize">
                                {grn.inspection_status.replace('_', ' ')}
                            </span>
                        </div>
                        {grn.quality_inspection_notes && (
                            <p className="text-xs italic text-muted-foreground mt-2 border-t pt-1">
                                &ldquo;{grn.quality_inspection_notes}&rdquo;
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* 3-WAY MATCHING AUDIT ENGINE MATRIX */}
            <Card className="shadow-sm mb-6 border-l-4 border-l-primary bg-card/60 backdrop-blur-sm">
                <CardHeader>
                    <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                        <div>
                            <CardTitle className="text-base font-bold flex items-center gap-2">
                                <ShieldCheck className="h-5 w-5 text-primary" />
                                {t('Three-Way Matching Verification Matrix (PO vs GRN vs Supplier Bill)')}
                            </CardTitle>
                            <CardDescription className="text-xs">
                                {t('Automated reconciliation comparing contractual purchase order pricing, physically inspected warehouse quantities, and supplier billed amounts.')}
                            </CardDescription>
                        </div>
                        {matching_analysis.has_invoice && (
                            <Badge variant="outline" className="font-mono text-xs">
                                {t('Linked Bill')}: {grn.purchaseInvoice?.invoice_number}
                            </Badge>
                        )}
                    </div>
                </CardHeader>
                <CardContent>
                    {/* Status Alert Banner */}
                    {matching_analysis.match_status === 'matched' ? (
                        <div className="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-3 text-emerald-800 text-xs">
                            <CheckCircle2 className="h-5 w-5 text-emerald-600 shrink-0" />
                            <div>
                                <span className="font-bold">{t('3-Way Match Verified & Cleared')}</span>
                                <p className="text-emerald-700 mt-0.5">
                                    {t('Quantities and unit rates match 100% across the Purchase Order, Goods Received Note, and Supplier Bill. Payment authorization is verified under Mutamark Financial Controls.')}
                                </p>
                            </div>
                        </div>
                    ) : matching_analysis.match_status === 'variance_detected' ? (
                        <div className="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3 text-amber-800 text-xs">
                            <AlertTriangle className="h-5 w-5 text-amber-600 shrink-0" />
                            <div>
                                <span className="font-bold">{t('Variance Flagged in 3-Way Reconciliation')}</span>
                                <p className="text-amber-700 mt-0.5">
                                    {t('Discrepancy detected between goods physically accepted and supplier bill lines. Review quantity and price variance before finance approval.')}
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3 text-blue-800 text-xs">
                            <FileText className="h-5 w-5 text-blue-600 shrink-0" />
                            <div>
                                <span className="font-bold">{t('Pending Supplier Bill Reconciliation')}</span>
                                <p className="text-blue-700 mt-0.5">
                                    {t('Goods physically received and inspected. When the vendor invoice is posted, automated 3-way matching will verify price and quantity alignment.')}
                                </p>
                            </div>
                        </div>
                    )}

                    {/* Matrix Comparison Table */}
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/30">
                                <TableHead className="font-semibold text-xs">{t('Item Description')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('PO Ordered Qty')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('GRN Accepted Qty')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('Billed Qty')}</TableHead>
                                <TableHead className="font-semibold text-xs text-right">{t('PO Unit Rate')}</TableHead>
                                <TableHead className="font-semibold text-xs text-right">{t('Billed Unit Rate')}</TableHead>
                                <TableHead className="font-semibold text-xs text-right">{t('Qty Variance')}</TableHead>
                                <TableHead className="font-semibold text-xs text-right">{t('Price Variance')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('Match Status')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {matching_analysis.items.map((row) => (
                                <TableRow key={row.id} className="hover:bg-muted/20">
                                    <TableCell className="font-medium text-xs">
                                        <div>{row.product_name}</div>
                                        <div className="text-[10px] font-mono text-muted-foreground">{row.sku}</div>
                                    </TableCell>
                                    <TableCell className="text-center font-mono text-xs">{Math.round(row.po_qty)}</TableCell>
                                    <TableCell className="text-center font-mono text-xs font-semibold text-emerald-600">
                                        {Math.round(row.grn_qty)}
                                    </TableCell>
                                    <TableCell className="text-center font-mono text-xs">{Math.round(row.billed_qty)}</TableCell>
                                    <TableCell className="text-right font-mono text-xs">{formatCurrency(row.po_price)}</TableCell>
                                    <TableCell className="text-right font-mono text-xs">{formatCurrency(row.billed_price)}</TableCell>
                                    <TableCell className="text-right font-mono text-xs">
                                        {row.qty_variance > 0 ? (
                                            <span className="text-rose-600 font-bold">+{row.qty_variance}</span>
                                        ) : (
                                            <span className="text-emerald-600">0</span>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right font-mono text-xs">
                                        {row.price_variance > 0 ? (
                                            <span className="text-rose-600 font-bold">{formatCurrency(row.price_variance)}</span>
                                        ) : (
                                            <span className="text-emerald-600">0</span>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-center">
                                        {row.is_item_matched ? (
                                            <Badge className="bg-emerald-50 text-emerald-700 border-emerald-200 text-[10px]">
                                                {t('Match')}
                                            </Badge>
                                        ) : (
                                            <Badge className="bg-amber-50 text-amber-700 border-amber-200 text-[10px]">
                                                {t('Variance')}
                                            </Badge>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            {/* Line Items Detail Card */}
            <Card className="shadow-sm bg-card/60 backdrop-blur-sm">
                <CardHeader>
                    <CardTitle className="text-base font-bold">{t('Received Line Items')}</CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow className="bg-muted/40">
                                <TableHead className="font-semibold text-xs pl-6">{t('Hardware Item')}</TableHead>
                                <TableHead className="font-semibold text-xs">{t('Batch / LOT')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('Ordered')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('Received')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('Accepted')}</TableHead>
                                <TableHead className="font-semibold text-xs text-center">{t('Rejected')}</TableHead>
                                <TableHead className="font-semibold text-xs text-right">{t('Unit Cost (TZS)')}</TableHead>
                                <TableHead className="font-semibold text-xs text-right pr-6">{t('Accepted Subtotal (TZS)')}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {grn.items.map((item) => (
                                <TableRow key={item.id} className="hover:bg-muted/20">
                                    <TableCell className="pl-6 font-medium text-xs">
                                        <div>{item.product?.name}</div>
                                        <div className="font-mono text-[10px] text-muted-foreground">{item.product?.sku}</div>
                                    </TableCell>
                                    <TableCell className="font-mono text-xs">{item.batch_number || '-'}</TableCell>
                                    <TableCell className="text-center font-mono text-xs">{Math.round(item.ordered_quantity)}</TableCell>
                                    <TableCell className="text-center font-mono text-xs">{Math.round(item.received_quantity)}</TableCell>
                                    <TableCell className="text-center font-mono text-xs font-semibold text-emerald-600">
                                        {Math.round(item.accepted_quantity)}
                                    </TableCell>
                                    <TableCell className="text-center font-mono text-xs">
                                        {item.rejected_quantity > 0 ? (
                                            <span className="text-rose-600 font-bold">{Math.round(item.rejected_quantity)}</span>
                                        ) : (
                                            '0'
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right font-mono text-xs">{formatCurrency(item.unit_price)}</TableCell>
                                    <TableCell className="text-right font-mono text-xs font-bold pr-6">
                                        {formatCurrency(item.total_amount)}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>

                    <div className="p-4 border-t flex justify-end">
                        <div className="w-64 space-y-2 text-xs">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">{t('Total Ordered Units')}:</span>
                                <span className="font-mono font-semibold">{Math.round(grn.total_ordered_qty)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">{t('Total Accepted Units')}:</span>
                                <span className="font-mono font-semibold text-emerald-600">{Math.round(grn.total_accepted_qty)}</span>
                            </div>
                            <div className="flex justify-between border-t pt-2 text-sm font-bold">
                                <span>{t('Total Intake Value')}:</span>
                                <span className="font-mono text-primary">{formatCurrency(grn.total_amount)}</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* QA Inspection Update Dialog */}
            <Dialog open={isInspectOpen} onOpenChange={setIsInspectOpen}>
                <DialogContent className="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <ClipboardCheck className="h-5 w-5 text-primary" />
                            {t('Quality Inspection Sign-off')}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleInspectSubmit} className="space-y-4 pt-2">
                        <div>
                            <Label htmlFor="inspection_status">{t('Inspection Result')} *</Label>
                            <Select 
                                value={inspectData.inspection_status} 
                                onValueChange={(val) => setInspectData('inspection_status', val as any)}
                            >
                                <SelectTrigger id="inspection_status" className="mt-1">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="inspected_passed">{t('Inspected & Passed (100% Quality Acceptance)')}</SelectItem>
                                    <SelectItem value="partially_accepted">{t('Partially Accepted (Some Units Defective/Damaged)')}</SelectItem>
                                    <SelectItem value="rejected">{t('Rejected (Shipment Does Not Meet Specification)')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label htmlFor="quality_inspection_notes">{t('Quality Inspection Notes')}</Label>
                            <Textarea
                                id="quality_inspection_notes"
                                value={inspectData.quality_inspection_notes}
                                onChange={(e) => setInspectData('quality_inspection_notes', e.target.value)}
                                rows={3}
                                className="mt-1 text-xs"
                                placeholder={t('Provide technical inspection findings...')}
                            />
                        </div>

                        <div>
                            <Label htmlFor="rejection_notes">{t('Rejection or Return Reason (if any)')}</Label>
                            <Textarea
                                id="rejection_notes"
                                value={inspectData.rejection_notes}
                                onChange={(e) => setInspectData('rejection_notes', e.target.value)}
                                rows={2}
                                className="mt-1 text-xs"
                                placeholder={t('Reason for non-acceptance...')}
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setIsInspectOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={inspectProcessing}>
                                {inspectProcessing ? t('Saving...') : t('Sign-off QA Inspection')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
