import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import CorporateDocumentTemplate from '@/components/CorporateDocumentTemplate';
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
    Building2,
    ShoppingCart,
    FileText,
    Send,
    CheckCircle2,
    Clock,
    AlertCircle,
    Calendar,
    DollarSign,
    Package,
    Printer
} from 'lucide-react';

interface Props {
    vendor: { id: number; name: string; contact?: string; email?: string } | null;
    open_rfqs: any[];
    submitted_bids: any[];
    purchase_orders: any[];
    available_vendors: any[];
}

export default function SupplierPortal({
    vendor,
    open_rfqs,
    submitted_bids,
    purchase_orders,
    available_vendors
}: Props) {
    const { t } = useTranslation();
    const profileForm=useForm({company_name:vendor?.name || '',contact_person_name:vendor?.contact || '',contact_person_email:vendor?.email || ''});
    const [attachmentError,setAttachmentError]=useState('');
    const [activeTab, setActiveTab] = useState<'rfqs' | 'bids' | 'orders'>('rfqs');
    const [selectedRfq, setSelectedRfq] = useState<any | null>(null);
    const [bidModalOpen, setBidModalOpen] = useState(false);
    const [selectedPo, setSelectedPo] = useState<any | null>(null);
    const [poModalOpen, setPoModalOpen] = useState(false);

    // Bid submission form
    const bidForm = useForm({
        vendor_id: vendor?.id || '',
        quotation_reference: '',
        delivery_lead_time_days: '7',
        warranty_months: '12',
        payment_terms: '30 Days Net',
        notes: '',
        items: [] as Array<{ rfq_item_id: number; product_name: string; quantity: number; unit_price: string }>,
    });

    const handleOpenBid = (rfq: any) => {
        setSelectedRfq(rfq);
        const mappedItems = (rfq.items || []).map((it: any) => ({
            rfq_item_id: it.id,
            product_name: it.product_name,
            quantity: Number(it.quantity),
            unit_price: String(it.target_unit_price || 0),
        }));

        bidForm.setData({
            vendor_id: vendor?.id || '',
            quotation_reference: `QUOT-${Date.now().toString().slice(-5)}`,
            delivery_lead_time_days: '7',
            warranty_months: '12',
            payment_terms: '30 Days Net',
            notes: 'Official supplier quotation submitted via Portal.',
            items: mappedItems,
        });
        setBidModalOpen(true);
    };

    const submitBid = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedRfq) return;
        bidForm.post(route('portal.supplier.bid', selectedRfq.id), {
            onSuccess: () => {
                setBidModalOpen(false);
                bidForm.reset();
            },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Supplier Self-Service Portal')} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <Badge className="bg-primary/10 text-primary border-primary/20 text-xs">
                                {t('Supplier Self-Service Portal')}
                            </Badge>
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground mt-1 flex items-center gap-2">
                            <Building2 className="h-7 w-7 text-primary" />
                            {vendor ? vendor.name : t('Vendor Self-Service Portal')}
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            {vendor?.email} {vendor?.contact ? `• Contact: ${vendor.contact}` : ''}
                        </p>
                    </div>

                    {available_vendors.length > 1 && (
                        <Select
                            value={String(vendor?.id || '')}
                            onValueChange={(val) => router.visit(route('portal.supplier') + `?vendor_id=${val}`)}
                        >
                            <SelectTrigger className="w-60 h-9 text-xs">
                                <SelectValue placeholder={t('Switch supplier')} />
                            </SelectTrigger>
                            <SelectContent>
                                {available_vendors.map((v) => (
                                    <SelectItem key={v.id} value={String(v.id)}>
                                        {v.company_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                </div>

                {/* Tabs */}
                <div className="border-b border-border">
                    <nav className="flex space-x-6">
                        <button
                            onClick={() => setActiveTab('rfqs')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'rfqs'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <ShoppingCart className="h-4 w-4" />
                            {t('Open RFQs & Tenders')} ({open_rfqs.length})
                        </button>

                        <button
                            onClick={() => setActiveTab('bids')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'bids'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Send className="h-4 w-4" />
                            {t('My Submitted Quotations')} ({submitted_bids.length})
                        </button>

                        <button
                            onClick={() => setActiveTab('orders')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'orders'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <FileText className="h-4 w-4" />
                            {t('Awarded Purchase Bills')} ({purchase_orders.length})
                        </button>
                    </nav>
                </div>

                {/* TAB 1: Open RFQs */}
                {activeTab === 'rfqs' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">{t('Active Requests for Quotation (RFQs)')}</CardTitle>
                            <CardDescription>{t('Submit competitive bids and pricing proposals for Mutamark procurement tenders')}</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('RFQ #')}</TableHead>
                                        <TableHead>{t('Title')}</TableHead>
                                        <TableHead>{t('Closing Date')}</TableHead>
                                        <TableHead>{t('Items Count')}</TableHead>
                                        <TableHead>{t('Status')}</TableHead>
                                        <TableHead className="text-right">{t('Action')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {open_rfqs.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center py-6 text-muted-foreground">
                                                {t('No active RFQs available at this time.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        open_rfqs.map((rfq) => (
                                            <TableRow key={rfq.id}>
                                                <TableCell className="font-semibold text-primary">{rfq.rfq_number}</TableCell>
                                                <TableCell className="font-medium text-sm">{rfq.title}</TableCell>
                                                <TableCell className="text-xs text-rose-600 font-medium">{rfq.closing_date}</TableCell>
                                                <TableCell className="text-xs">{rfq.items?.length || 0} {t('items')}</TableCell>
                                                <TableCell>
                                                    <Badge className="bg-blue-600 text-white uppercase text-[10px]">{rfq.status}</Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <Button
                                                        size="sm"
                                                        onClick={() => handleOpenBid(rfq)}
                                                        className="bg-primary h-8 text-xs"
                                                    >
                                                        <Send className="h-3.5 w-3.5 mr-1" />
                                                        {t('Submit Bid')}
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                {/* TAB 2: Submitted Bids */}
                {activeTab === 'bids' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">{t('Submitted Quotations & Tender Bids')}</CardTitle>
                            <CardDescription>{t('Review your submitted bids and evaluation statuses')}</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('Ref #')}</TableHead>
                                        <TableHead>{t('RFQ Tender')}</TableHead>
                                        <TableHead>{t('Total Bid Amount')}</TableHead>
                                        <TableHead>{t('Lead Time')}</TableHead>
                                        <TableHead>{t('Status')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {submitted_bids.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="text-center py-6 text-muted-foreground">
                                                {t('No bids submitted yet.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        submitted_bids.map((b) => (
                                            <TableRow key={b.id}>
                                                <TableCell className="font-semibold text-primary">{b.quotation_reference}</TableCell>
                                                <TableCell className="text-xs font-medium">{b.rfq?.rfq_number} — {b.rfq?.title}</TableCell>
                                                <TableCell className="font-bold">
                                                    TZS {Number(b.total_bid_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                                </TableCell>
                                                <TableCell className="text-xs">{b.delivery_lead_time_days} {t('Days')}</TableCell>
                                                <TableCell>
                                                    <Badge className={b.status === 'awarded' ? 'bg-emerald-600 text-white' : 'bg-secondary'}>
                                                        {b.status}
                                                    </Badge>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                {/* TAB 3: Purchase Orders */}
                {activeTab === 'orders' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">{t('Awarded Purchase Bills & Orders')}</CardTitle>
                            <CardDescription>{t('Official purchase commitments awarded to your company')}</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('Bill / PO #')}</TableHead>
                                        <TableHead>{t('Date')}</TableHead>
                                        <TableHead>{t('Total Amount')}</TableHead>
                                        <TableHead>{t('Payment Status')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {purchase_orders.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center py-6 text-muted-foreground">
                                                {t('No purchase orders recorded.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        purchase_orders.map((po) => (
                                            <TableRow key={po.id}>
                                                <TableCell className="font-semibold text-primary">{po.po_number}</TableCell>
                                                <TableCell className="text-xs">{po.issue_date || po.created_at?.split('T')[0]}</TableCell>
                                                <TableCell className="font-bold">
                                                    TZS {Number(po.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant={po.status === 'paid' ? 'default' : 'secondary'}>
                                                        {po.status || 'Pending'}
                                                    </Badge>
                                                    {!po.supplier_accepted_at && <Button size="sm" variant="outline" className="ml-2" onClick={()=>router.post(route('portal.supplier.accept-order',po.id))}>{t('Accept order')}</Button>}
                                                    <Button size="sm" variant="outline" className="ml-2" onClick={() => { setSelectedPo(po); setPoModalOpen(true); }}>
                                                        <Printer className="h-3.5 w-3.5 mr-1" />
                                                        {t('View / Print PO')}
                                                    </Button>
                                                    <Input className="mt-2" aria-label={t('Upload supplier invoice or delivery note')} type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp" onChange={event=>{const file=event.target.files?.[0];if(file)router.post(route('portal.supplier.upload',po.id),{file},{forceFormData:true,onError:errors=>setAttachmentError(Object.values(errors).join(' '))});}} />
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}
            </div>

            {/* SUBMIT BID DIALOG */}
            <Dialog open={bidModalOpen} onOpenChange={setBidModalOpen}>
                <DialogContent className="max-w-xl">
                    <DialogHeader>
                        <DialogTitle>{t('Submit Competitive Quotation Bid')}</DialogTitle>
                        <DialogDescription>
                            {selectedRfq?.rfq_number} — {selectedRfq?.title}
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitBid} className="space-y-3">
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Quotation Reference / Invoice # *')}</Label>
                                <Input
                                    required
                                    value={bidForm.data.quotation_reference}
                                    onChange={(e) => bidForm.setData('quotation_reference', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('Delivery Lead Time (Days) *')}</Label>
                                <Input
                                    type="number"
                                    required
                                    value={bidForm.data.delivery_lead_time_days}
                                    onChange={(e) => bidForm.setData('delivery_lead_time_days', e.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <Label className="font-semibold text-sm">{t('Item Pricing (TZS) *')}</Label>
                            <div className="space-y-2 mt-1 max-h-48 overflow-y-auto border p-2 rounded-lg bg-muted/20">
                                {bidForm.data.items.map((item, idx) => (
                                    <div key={item.rfq_item_id} className="flex items-center gap-2 text-xs">
                                        <div className="flex-1 font-medium truncate">{item.product_name} (Qty: {item.quantity})</div>
                                        <div className="w-32">
                                            <Input
                                                type="number"
                                                placeholder="Unit Price"
                                                className="h-7 text-xs"
                                                value={item.unit_price}
                                                onChange={(e) => {
                                                    const updated = [...bidForm.data.items];
                                                    updated[idx].unit_price = e.target.value;
                                                    bidForm.setData('items', updated);
                                                }}
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Warranty Coverage (Months)')}</Label>
                                <Input
                                    type="number"
                                    value={bidForm.data.warranty_months}
                                    onChange={(e) => bidForm.setData('warranty_months', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('Payment Terms')}</Label>
                                <Input
                                    value={bidForm.data.payment_terms}
                                    onChange={(e) => bidForm.setData('payment_terms', e.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <Label>{t('Supplier Notes / Commercial Terms')}</Label>
                            <Textarea
                                rows={2}
                                value={bidForm.data.notes}
                                onChange={(e) => bidForm.setData('notes', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setBidModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={bidForm.processing}>
                                {t('Submit Official Bid')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
            {/* PURCHASE ORDER VIEW / PRINT DIALOG */}
            <Dialog open={poModalOpen} onOpenChange={setPoModalOpen}>
                <DialogContent className="max-w-3xl max-h-[92vh] overflow-y-auto">
                    <DialogHeader className="print:hidden">
                        <DialogTitle className="flex items-center justify-between">
                            <span className="flex items-center gap-2">
                                <Printer className="w-5 h-5 text-indigo-500" />
                                Official Purchase Order: {selectedPo?.po_number}
                            </span>
                            <Button
                                onClick={() => window.print()}
                                size="sm"
                                className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                            >
                                <Printer className="w-4 h-4 mr-2" /> Print PO
                            </Button>
                        </DialogTitle>
                    </DialogHeader>

                    {selectedPo && (
                        <CorporateDocumentTemplate
                            type="purchase_order"
                            documentTitle="PURCHASE ORDER"
                            documentNumber={selectedPo.po_number}
                            documentNumberLabel="PO No:"
                            date={selectedPo.po_date || selectedPo.created_at?.split('T')[0] || 'DD/MM/YYYY'}
                            dateLabel="Order Date:"
                            secondaryDate={selectedPo.expected_delivery_date || 'DD/MM/YYYY'}
                            secondaryDateLabel="Delivery Date:"

                            leftBoxTitle="VENDOR / SUPPLIER"
                            leftBoxFields={[
                                { label: 'Supplier / Company Name', value: selectedPo.vendor?.company_name || vendor?.name },
                                { label: 'Address', value: selectedPo.vendor?.billing_address || (vendor as any)?.address || 'Dar es Salaam, Tanzania' },
                                {
                                    label: 'Phone / Email',
                                    value: `${selectedPo.vendor?.contact_person_mobile || vendor?.contact || ''} ${vendor?.email ? '| ' + vendor.email : ''}`.trim() || 'info@supplier.co.tz',
                                },
                                { label: 'TIN / VRN (if applicable)', value: selectedPo.vendor?.tax_number || '119-823-455' },
                            ]}

                            rightBoxTitle="SHIP TO / ORDER DETAILS"
                            rightBoxFields={[
                                { label: 'Deliver To', value: selectedPo.warehouse?.name || 'Main Factory Warehouse, Dar es Salaam' },
                                { label: 'Quotation / RFQ Ref', value: selectedPo.rfq?.rfq_number || 'RFQ-2026-REF' },
                                { label: 'Payment Terms', value: selectedPo.payment_terms || '30 days' },
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
                                            <div className="font-semibold text-slate-900">{it.product?.name || it.name}</div>
                                            {it.product?.sku && <div className="text-[10px] text-slate-500 font-mono">SKU: {it.product.sku}</div>}
                                        </div>
                                    ),
                                },
                                { key: 'quantity', label: 'Qty', align: 'center', width: '55px', render: (it) => it.quantity },
                                { key: 'unit', label: 'Unit', align: 'center', width: '60px', render: (it) => it.unit || 'units' },
                                {
                                    key: 'unit_price',
                                    label: 'Unit Price (TZS)',
                                    align: 'right',
                                    width: '130px',
                                    render: (it) => Number(it.unit_price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                                },
                                {
                                    key: 'amount',
                                    label: 'Amount (TZS)',
                                    align: 'right',
                                    width: '130px',
                                    render: (it) => Number(it.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                                },
                            ]}
                            items={selectedPo.items || []}
                            minRows={8}

                            totals={[
                                { label: 'Subtotal', value: Number(selectedPo.subtotal || selectedPo.total_amount) },
                                { label: 'Discount (if any)', value: Number(selectedPo.discount_amount || 0) },
                                { label: 'VAT (if applicable)', value: Number(selectedPo.tax_amount || 0) },
                                { label: 'TOTAL ORDER VALUE (TZS)', value: Number(selectedPo.total_amount), isGrandTotal: true },
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
                                {
                                    label: 'Prepared by',
                                    value: 'Procurement Specialist',
                                    extraLabel: 'Name',
                                    extraValue: 'Supply Chain Directorate',
                                },
                                {
                                    label: 'Authorised signature',
                                    value: 'Managing Director / CFO',
                                    dateLabel: 'Date',
                                    dateValue: selectedPo.po_date || selectedPo.created_at?.split('T')[0],
                                },
                                {
                                    label: 'Vendor acknowledgement',
                                    value: selectedPo.supplier_accepted_at ? 'ACCEPTED VIA SUPPLIER PORTAL' : 'Pending Vendor Signature',
                                    dateLabel: 'Date',
                                    dateValue: selectedPo.supplier_accepted_at ? selectedPo.supplier_accepted_at.split('T')[0] : '',
                                },
                            ]}

                            footerSlogan="Please acknowledge receipt of this order."
                        />
                    )}
                </DialogContent>
            </Dialog>

            {attachmentError && <p role="alert" className="text-sm text-destructive">{attachmentError}</p>}
            <Card className="mt-6"><CardHeader><CardTitle>{t('Supplier contact details')}</CardTitle></CardHeader><CardContent><form className="space-y-3" onSubmit={event=>{event.preventDefault();profileForm.patch(route('portal.supplier.update-profile'));}}>{(['company_name','contact_person_name','contact_person_email'] as const).map(key=><div key={key}><Label htmlFor={key}>{t(key.replaceAll('_',' '))}</Label><Input id={key} type={key.endsWith('email')?'email':'text'} value={profileForm.data[key]} onChange={event=>profileForm.setData(key,event.target.value)} /></div>)}{Object.values(profileForm.errors).map((error,index)=><p className="text-sm text-destructive" role="alert" key={index}>{error}</p>)}<Button disabled={profileForm.processing}>{t('Save contact details')}</Button></form></CardContent></Card>
        </AuthenticatedLayout>
    );
}
