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
    UserCheck,
    Receipt,
    FileText,
    Truck,
    CheckCircle2,
    Clock,
    AlertCircle,
    CreditCard,
    Smartphone,
    Wrench,
    DollarSign,
    ExternalLink,
    Building2,
    Plus,
    XCircle
} from 'lucide-react';

interface Props {
    customer: { id: number; name: string; email: string; phone: string };
    statement: { total_invoiced: number; total_paid: number; outstanding_balance: number };
    proposals: any[];
    invoices: any[];
    delivery_notes: any[];
    tickets: any[];
    available_customers: any[];
}

export default function CustomerPortal({
    customer,
    statement,
    proposals,
    invoices,
    delivery_notes,
    tickets,
    available_customers
}: Props) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState<'statement' | 'proposals' | 'invoices' | 'deliveries' | 'tickets'>('statement');
    const [selectedInvoice, setSelectedInvoice] = useState<any | null>(null);
    const [paymentModalOpen, setPaymentModalOpen] = useState(false);
    const [ticketModalOpen, setTicketModalOpen] = useState(false);
    const [revisionProposalId, setRevisionProposalId] = useState<number | null>(null);
    const revisionForm = useForm({ reason: '' });

    // Payment Form
    const paymentForm = useForm({
        payment_method: 'lipa_namba',
        transaction_reference: '',
        amount_paid: '',
        notes: '',
    });

    // Support Ticket Form
    const ticketForm = useForm({
        product_name: '',
        serial_number: '',
        issue_reported: '',
        contact_phone: customer.phone,
    });

    const handleOpenPayment = (inv: any) => {
        setSelectedInvoice(inv);
        paymentForm.setData({
            payment_method: 'lipa_namba',
            transaction_reference: '',
            amount_paid: String(inv.balance_amount),
            notes: `Payment for Invoice #${inv.invoice_number}`,
        });
        setPaymentModalOpen(true);
    };

    const submitPayment = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedInvoice) return;
        paymentForm.post(route('portal.customer.payment', selectedInvoice.id), {
            onSuccess: () => {
                setPaymentModalOpen(false);
                paymentForm.reset();
            },
        });
    };

    const submitTicket = (e: React.FormEvent) => {
        e.preventDefault();
        ticketForm.post(route('portal.customer.ticket'), {
            onSuccess: () => {
                setTicketModalOpen(false);
                ticketForm.reset();
            },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Customer Self-Service Portal')} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <Badge className="bg-primary/10 text-primary border-primary/20 text-xs">
                                {t('Customer Self-Service Portal')}
                            </Badge>
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground mt-1 flex items-center gap-2">
                            <UserCheck className="h-7 w-7 text-primary" />
                            {customer.name}
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            {customer.email} • {customer.phone}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        {available_customers.length > 1 && (
                            <Select
                                value={String(customer.id)}
                                onValueChange={(val) => router.visit(route('portal.customer') + `?customer_id=${val}`)}
                            >
                                <SelectTrigger className="w-52 h-9 text-xs">
                                    <SelectValue placeholder={t('Switch customer view')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {available_customers.map((c) => (
                                        <SelectItem key={c.id} value={String(c.id)}>
                                            {c.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        )}

                        <Button onClick={() => setTicketModalOpen(true)} className="flex items-center gap-1.5 h-9">
                            <Wrench className="h-4 w-4" />
                            {t('Submit Support Request')}
                        </Button>
                    </div>
                </div>

                {/* Account Statement Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card className="shadow-sm border-l-4 border-l-blue-500">
                        <CardContent className="p-5">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Total Invoiced')}</span>
                                <Receipt className="h-5 w-5 text-blue-500" />
                            </div>
                            <div className="text-2xl font-bold mt-2">
                                TZS {statement.total_invoiced.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">{invoices.length} {t('commercial invoices')}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-emerald-500">
                        <CardContent className="p-5">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Total Paid')}</span>
                                <CheckCircle2 className="h-5 w-5 text-emerald-500" />
                            </div>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">
                                TZS {statement.total_paid.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">{t('Verified settlements')}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-amber-500">
                        <CardContent className="p-5">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Outstanding Balance')}</span>
                                <AlertCircle className="h-5 w-5 text-amber-500" />
                            </div>
                            <div className="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2">
                                TZS {statement.outstanding_balance.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                            </div>
                            <div className="text-xs text-muted-foreground mt-1">{t('Current account balance due')}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs */}
                <div className="border-b border-border">
                    <nav className="flex space-x-6">
                        <button
                            onClick={() => setActiveTab('statement')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'statement'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Receipt className="h-4 w-4" />
                            {t('Invoices & Billing')} ({invoices.length})
                        </button>

                        <button
                            onClick={() => setActiveTab('proposals')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'proposals'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <FileText className="h-4 w-4" />
                            {t('Quotations & Proposals')} ({proposals.length})
                        </button>

                        <button
                            onClick={() => setActiveTab('deliveries')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'deliveries'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Truck className="h-4 w-4" />
                            {t('Delivery Notes')} ({delivery_notes.length})
                        </button>

                        <button
                            onClick={() => setActiveTab('tickets')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'tickets'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Wrench className="h-4 w-4" />
                            {t('Support & Service Tickets')} ({tickets.length})
                        </button>
                    </nav>
                </div>

                {/* TAB 1: Invoices */}
                {activeTab === 'statement' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">{t('Sales Invoices & Online Payments')}</CardTitle>
                            <CardDescription>{t('Review billed orders and submit payment reference numbers')}</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('Invoice #')}</TableHead>
                                        <TableHead>{t('Invoice Date')}</TableHead>
                                        <TableHead>{t('Due Date')}</TableHead>
                                        <TableHead>{t('Total Amount')}</TableHead>
                                        <TableHead>{t('Status')}</TableHead>
                                        <TableHead className="text-right">{t('Action')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {invoices.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center py-6 text-muted-foreground">
                                                {t('No invoices available.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        invoices.map((inv) => (
                                            <TableRow key={inv.id}>
                                                <TableCell className="font-semibold text-primary">{inv.invoice_number}</TableCell>
                                                <TableCell className="text-xs">{inv.issue_date || inv.created_at?.split('T')[0]}</TableCell>
                                                <TableCell className="text-xs">{inv.due_date || 'On Receipt'}</TableCell>
                                                <TableCell className="font-semibold">
                                                    TZS {Number(inv.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className={inv.status === 'paid' ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white'}>
                                                        {inv.status || 'Pending'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <Button
                                                        size="sm"
                                                        onClick={() => handleOpenPayment(inv)}
                                                        className="bg-primary text-xs h-8"
                                                    >
                                                        <CreditCard className="h-3.5 w-3.5 mr-1" />
                                                        {t('Pay / Submit Ref')}
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

                {/* TAB 2: Proposals */}
                {activeTab === 'proposals' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">{t('Quotations & Pricing Proposals')}</CardTitle>
                            <CardDescription>{t('Review proposals and authorize or request adjustments')}</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('Proposal #')}</TableHead>
                                        <TableHead>{t('Date')}</TableHead>
                                        <TableHead>{t('Total Amount')}</TableHead>
                                        <TableHead>{t('Status')}</TableHead>
                                        <TableHead className="text-right">{t('Action')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {proposals.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="text-center py-6 text-muted-foreground">
                                                {t('No proposals found.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        proposals.map((prop) => (
                                            <TableRow key={prop.id}>
                                                <TableCell className="font-semibold text-primary">{prop.proposal_number}</TableCell>
                                                <TableCell className="text-xs">{prop.proposal_date}</TableCell>
                                                <TableCell className="font-semibold">
                                                    TZS {Number(prop.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant={prop.status === 'accepted' ? 'default' : 'secondary'}>
                                                        {prop.status}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right space-x-2">
                                                    {prop.status === 'sent' && (
                                                        <>
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                className="text-emerald-600 border-emerald-500 hover:bg-emerald-50 text-xs h-8"
                                                                onClick={() => router.post(route('portal.customer.accept-proposal', prop.id))}
                                                            >
                                                                <CheckCircle2 className="h-3.5 w-3.5 mr-1" />
                                                                {t('Approve')}
                                                            </Button>
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                className="text-rose-600 border-rose-500 hover:bg-rose-50 text-xs h-8"
                                                                onClick={() => { revisionForm.reset(); revisionForm.clearErrors(); setRevisionProposalId(prop.id); }}
                                                            >
                                                                <XCircle className="h-3.5 w-3.5 mr-1" />
                                                                {t('Request Revision')}
                                                            </Button>
                                                        </>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                {/* TAB 3: Delivery Notes */}
                {activeTab === 'deliveries' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">{t('Dispatched Deliveries & Waybills')}</CardTitle>
                            <CardDescription>{t('Track shipment notes and item fulfillments')}</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('Delivery #')}</TableHead>
                                        <TableHead>{t('Dispatch Date')}</TableHead>
                                        <TableHead>{t('Destination')}</TableHead>
                                        <TableHead>{t('Status')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {delivery_notes.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center py-6 text-muted-foreground">
                                                {t('No deliveries recorded.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        delivery_notes.map((dn) => (
                                            <TableRow key={dn.id}>
                                                <TableCell className="font-semibold text-primary">{dn.delivery_number}</TableCell>
                                                <TableCell className="text-xs">{dn.delivery_date}</TableCell>
                                                <TableCell className="text-xs">{dn.delivery_address || 'Customer Premises'}</TableCell>
                                                <TableCell>
                                                    <Badge className="bg-emerald-600 text-white">{dn.status || 'Dispatched'}</Badge>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                {/* TAB 4: Support Tickets */}
                {activeTab === 'tickets' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">{t('Support & Warranty Service Tickets')}</CardTitle>
                            <CardDescription>{t('Tickets submitted for repair and technical assistance')}</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>{t('Ticket #')}</TableHead>
                                        <TableHead>{t('Product / Item')}</TableHead>
                                        <TableHead>{t('Issue Reported')}</TableHead>
                                        <TableHead>{t('Status')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {tickets.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center py-6 text-muted-foreground">
                                                {t('No active tickets.')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        tickets.map((tkt) => (
                                            <TableRow key={tkt.id}>
                                                <TableCell className="font-semibold text-primary">{tkt.ticket_number}</TableCell>
                                                <TableCell className="font-medium text-sm">{tkt.product_name}</TableCell>
                                                <TableCell className="text-xs text-muted-foreground max-w-xs truncate">{tkt.issue_reported}</TableCell>
                                                <TableCell>
                                                    <Badge variant="outline">{tkt.status}</Badge>
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

            {/* PAYMENT DIALOG */}
            <Dialog open={paymentModalOpen} onOpenChange={setPaymentModalOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Submit Payment Reference')}</DialogTitle>
                        <DialogDescription>
                            {t('Invoice')} #{selectedInvoice?.invoice_number} — TZS {Number(selectedInvoice?.total_amount || 0).toLocaleString()}
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitPayment} className="space-y-3">
                        <div className="p-3 bg-muted/40 rounded-lg text-xs space-y-1 border">
                            <div className="font-semibold text-primary">{t('Official Merchant Lipa Namba (All Networks & Banks):')}</div>
                            <div className="text-base font-bold font-mono">16381432 (WOLINET)</div>
                            <div className="text-muted-foreground">{t('M-Pesa, Tigo Pesa, Airtel Money, CRDB, NMB')}</div>
                        </div>

                        <div>
                            <Label>{t('Payment Method *')}</Label>
                            <Select
                                value={paymentForm.data.payment_method}
                                onValueChange={(val) => paymentForm.setData('payment_method', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="lipa_namba">Lipa Namba (Mobile Money)</SelectItem>
                                    <SelectItem value="bank_transfer">Bank Transfer (EFT / TISS)</SelectItem>
                                    <SelectItem value="cash">Cash Counter Receipt</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label>{t('Transaction Reference / M-Pesa Code *')}</Label>
                            <Input
                                required
                                placeholder="e.g. QX92841492 or Bank Slip #"
                                value={paymentForm.data.transaction_reference}
                                onChange={(e) => paymentForm.setData('transaction_reference', e.target.value)}
                            />
                        </div>

                        <div>
                            <Label>{t('Amount Paid (TZS) *')}</Label>
                            <Input
                                type="number"
                                required
                                value={paymentForm.data.amount_paid}
                                onChange={(e) => paymentForm.setData('amount_paid', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setPaymentModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={paymentForm.processing}>
                                {t('Submit for Verification')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* TICKET DIALOG */}
            <Dialog open={ticketModalOpen} onOpenChange={setTicketModalOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Submit Support or Repair Request')}</DialogTitle>
                        <DialogDescription>{t('Report defect or request warranty service coverage')}</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitTicket} className="space-y-3">
                        <div>
                            <Label>{t('Product / Equipment Model *')}</Label>
                            <Input
                                required
                                placeholder="e.g. MacBook Pro M2, HP Printer"
                                value={ticketForm.data.product_name}
                                onChange={(e) => ticketForm.setData('product_name', e.target.value)}
                            />
                        </div>

                        <div>
                            <Label>{t('Serial Number')}</Label>
                            <Input
                                placeholder="Serial / IMEI"
                                value={ticketForm.data.serial_number}
                                onChange={(e) => ticketForm.setData('serial_number', e.target.value)}
                            />
                        </div>

                        <div>
                            <Label>{t('Issue Description *')}</Label>
                            <Textarea
                                required
                                rows={3}
                                placeholder="Describe the defect or service needed..."
                                value={ticketForm.data.issue_reported}
                                onChange={(e) => ticketForm.setData('issue_reported', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setTicketModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={ticketForm.processing}>
                                {t('Send Ticket')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
            <Dialog open={revisionProposalId !== null} onOpenChange={(open) => { if (!open) setRevisionProposalId(null); }}>
                <DialogContent>
                    <DialogHeader><DialogTitle>{t('Request Revision')}</DialogTitle><DialogDescription>{t('Describe the changes required. The sales team will revise and resend the quotation.')}</DialogDescription></DialogHeader>
                    <form className="space-y-4" onSubmit={(event) => { event.preventDefault(); if (revisionProposalId === null) return; revisionForm.post(route('portal.customer.reject-proposal', revisionProposalId), { onSuccess: () => setRevisionProposalId(null) }); }}>
                        <Label htmlFor="revision_reason">{t('Requested Changes')}</Label>
                        <Textarea id="revision_reason" required maxLength={1000} value={revisionForm.data.reason} onChange={(event) => revisionForm.setData('reason', event.target.value)} />
                        {revisionForm.errors.reason && <p className="text-sm text-destructive">{revisionForm.errors.reason}</p>}
                        <DialogFooter><Button type="submit" disabled={revisionForm.processing}>{t('Send Revision Request')}</Button></DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
