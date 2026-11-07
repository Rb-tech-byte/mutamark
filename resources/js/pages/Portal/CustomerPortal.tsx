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
    XCircle,
    Ship,
    Anchor,
    Navigation,
    Calendar,
    PackageCheck,
    Eye
} from 'lucide-react';

interface Props {
    customer: { id: number; name: string; email: string; phone: string };
    statement: { total_invoiced: number; total_paid: number; outstanding_balance: number };
    proposals: any[];
    invoices: any[];
    delivery_notes: any[];
    tickets: any[];
    clearing_jobs?: any[];
    available_customers: any[];
}

export default function CustomerPortal({
    customer,
    statement,
    proposals,
    invoices,
    delivery_notes,
    tickets,
    clearing_jobs = [],
    available_customers
}: Props) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState<'statement' | 'proposals' | 'invoices' | 'deliveries' | 'tickets' | 'clearing'>('statement');
    const [selectedInvoice, setSelectedInvoice] = useState<any | null>(null);
    const [paymentModalOpen, setPaymentModalOpen] = useState(false);
    const [ticketModalOpen, setTicketModalOpen] = useState(false);
    const [clearingModalOpen, setClearingModalOpen] = useState(false);
    const [selectedJobForTracking, setSelectedJobForTracking] = useState<any | null>(null);
    const [selectedJobForDelivery, setSelectedJobForDelivery] = useState<any | null>(null);
    const [deliveryModalOpen, setDeliveryModalOpen] = useState(false);
    const [revisionProposalId, setRevisionProposalId] = useState<number | null>(null);
    const revisionForm = useForm({ reason: '' });

    // Delivery Acknowledgment Form (SRS Section 4.7 / CUS-033)
    const deliveryForm = useForm({
        acknowledgement_type: 'confirmed' as 'confirmed' | 'discrepancy',
        received_by_name: customer.name || '',
        pod_document: null as File | null,
        notes: '',
    });

    const submitDeliveryAcknowledgement = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedJobForDelivery) return;
        deliveryForm.post(route('portal.customer.acknowledge-delivery', selectedJobForDelivery.id), {
            onSuccess: () => {
                setDeliveryModalOpen(false);
                deliveryForm.reset();
                setSelectedJobForDelivery(null);
            },
        });
    };

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

    // Clearing & Forwarding Request Form (SRS Section 4.2)
    const clearingForm = useForm({
        service_type: 'import',
        transport_mode: 'sea',
        customs_regime: 'home_use',
        bl_awb_number: '',
        vessel_flight_truck: '',
        port_of_loading: '',
        port_of_discharge: 'Dar es Salaam Port',
        final_destination: 'Dar es Salaam',
        commodity_description: '',
        package_type: 'packages',
        package_count: '1',
        gross_weight_kg: '',
        container_number: '',
        customer_reference: '',
        notes: '',
        commercial_invoice: null as File | null,
        packing_list: null as File | null,
        bill_of_lading: null as File | null,
    });

    const submitClearingRequest = (e: React.FormEvent) => {
        e.preventDefault();
        clearingForm.post(route('portal.customer.clearing-request'), {
            onSuccess: () => {
                setClearingModalOpen(false);
                clearingForm.reset();
            },
        });
    };

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

                        <Button onClick={() => setClearingModalOpen(true)} className="flex items-center gap-1.5 h-9 bg-indigo-700 hover:bg-indigo-800 text-white">
                            <Ship className="h-4 w-4" />
                            {t('Request Cargo Clearance')}
                        </Button>

                        <Button onClick={() => setTicketModalOpen(true)} variant="outline" className="flex items-center gap-1.5 h-9">
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

                        <button
                            onClick={() => setActiveTab('clearing')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'clearing'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Ship className="h-4 w-4 text-indigo-600" />
                            {t('Cargo & Customs Clearing')} ({clearing_jobs.length})
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

                {/* TAB 5: Cargo & Customs Clearing (C&F) */}
                {activeTab === 'clearing' && (
                    <div className="space-y-4">
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-indigo-50/60 dark:bg-indigo-950/30 p-4 rounded-xl border border-indigo-100 dark:border-indigo-900">
                            <div>
                                <h3 className="font-bold text-sm text-indigo-950 dark:text-indigo-200 flex items-center gap-2">
                                    <Ship className="w-4 h-4 text-indigo-600" />
                                    {t('Cargo Shipments & Customs Clearance Portal')}
                                </h3>
                                <p className="text-xs text-muted-foreground mt-0.5">
                                    {t('Real-time tracking for port arrivals, customs assessment, container free days, and inland transit deliveries.')}
                                </p>
                            </div>
                            <Button
                                size="sm"
                                onClick={() => setClearingModalOpen(true)}
                                className="bg-indigo-600 hover:bg-indigo-700 text-white gap-1.5 self-start sm:self-center"
                            >
                                <Plus className="w-4 h-4" />
                                {t('Request New Cargo Clearance')}
                            </Button>
                        </div>

                        <Card className="shadow-sm">
                            <CardHeader className="pb-3 border-b">
                                <CardTitle className="text-sm font-semibold">{t('Active Consignments & Shipment Register')}</CardTitle>
                                <CardDescription className="text-xs">{t('All clearing jobs managed under your enterprise account')}</CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/40 text-xs">
                                            <TableHead>{t('Job Ref / BL')}</TableHead>
                                            <TableHead>{t('Commodity & Packages')}</TableHead>
                                            <TableHead>{t('Routing & Carrier')}</TableHead>
                                            <TableHead>{t('Customs & Release')}</TableHead>
                                            <TableHead>{t('Demurrage Status')}</TableHead>
                                            <TableHead className="text-right">{t('Actions')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {clearing_jobs.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={6} className="text-center py-8 text-muted-foreground text-xs">
                                                    {t('No cargo clearing jobs currently recorded. Click "Request New Cargo Clearance" to submit your shipping instruction.')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            clearing_jobs.map((job: any) => {
                                                const container = job.containers?.[0];
                                                const decl = job.declarations?.[0];
                                                return (
                                                    <TableRow key={job.id} className="text-xs">
                                                        <TableCell>
                                                            <div className="font-mono font-bold text-indigo-700 dark:text-indigo-400">{job.job_number}</div>
                                                            <div className="text-[11px] text-muted-foreground font-mono mt-0.5">
                                                                BL/AWB: {job.shipment?.bl_awb_number || 'Pending'}
                                                            </div>
                                                            <Badge variant="outline" className="text-[9px] uppercase mt-1">
                                                                {job.customs_regime} &bull; {job.service_type}
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell>
                                                            <div className="font-semibold text-slate-900 dark:text-slate-100 line-clamp-1">
                                                                {job.cargo_lines?.[0]?.commodity_description || 'General Cargo'}
                                                            </div>
                                                            <div className="text-[11px] text-muted-foreground">
                                                                {job.cargo_lines?.[0]?.package_count || 1} {job.cargo_lines?.[0]?.package_type || 'packages'} &bull; {Number(job.cargo_lines?.[0]?.gross_weight_kg || 0).toLocaleString()} KG
                                                            </div>
                                                        </TableCell>
                                                        <TableCell>
                                                            <div className="font-medium text-slate-800 dark:text-slate-200">
                                                                {job.shipment?.port_of_discharge || 'Dar Port'} &rarr; {job.shipment?.final_destination || 'Dar es Salaam'}
                                                            </div>
                                                            <div className="text-[11px] text-muted-foreground">
                                                                {job.shipment?.vessel_flight_truck || job.shipment?.shipping_line_airline || 'Ocean Vessel'}
                                                            </div>
                                                        </TableCell>
                                                        <TableCell>
                                                            <div>
                                                                <Badge className={`text-[10px] capitalize ${
                                                                    ['cargo_released', 'delivered', 'completed'].includes(job.status)
                                                                        ? 'bg-emerald-600 text-white'
                                                                        : ['declaration_submitted', 'assessed', 'in_transit'].includes(job.status)
                                                                        ? 'bg-blue-600 text-white'
                                                                        : 'bg-amber-500 text-white'
                                                                }`}>
                                                                    {job.status.replace('_', ' ')}
                                                                </Badge>
                                                            </div>
                                                            {decl?.payment_control_number && (
                                                                <div className="text-[10px] text-muted-foreground mt-1 font-mono">
                                                                    GePG: {decl.payment_control_number}
                                                                </div>
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            {container ? (
                                                                <div>
                                                                    <span className="font-mono font-semibold text-[11px]">{container.container_number}</span>
                                                                    <div className="text-[10px] text-emerald-700 font-medium">
                                                                        {container.free_days} free days {container.empty_return_deadline ? `(Due ${container.empty_return_deadline})` : ''}
                                                                    </div>
                                                                </div>
                                                            ) : (
                                                                <span className="text-muted-foreground text-[11px]">Breakbulk / LCL</span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <div className="flex items-center justify-end gap-1.5">
                                                                <Button
                                                                    size="sm"
                                                                    variant="outline"
                                                                    className="h-7 text-[11px] gap-1"
                                                                    onClick={() => setSelectedJobForTracking(job)}
                                                                >
                                                                    <Navigation className="w-3 h-3 text-indigo-600" />
                                                                    {t('Track')}
                                                                </Button>
                                                                <a
                                                                    href={route('clearing-forwarding.jobs.print', { cfJob: job.id, type: 'funding_request' })}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                >
                                                                    <Button size="sm" variant="ghost" className="h-7 text-[11px] px-2 text-muted-foreground">
                                                                        <Receipt className="w-3 h-3 mr-1" />
                                                                        {t('Advance Slip')}
                                                                    </Button>
                                                                </a>
                                                                {['customs_cleared', 'cargo_released', 'in_transit', 'delivered'].includes(job.status) && (
                                                                    <Button
                                                                        size="sm"
                                                                        variant="outline"
                                                                        className={`h-7 text-[11px] gap-1 ${job.status === 'delivered' ? 'text-emerald-700 border-emerald-300 bg-emerald-50/50' : 'text-blue-700 border-blue-300 hover:bg-blue-50'}`}
                                                                        onClick={() => {
                                                                            setSelectedJobForDelivery(job);
                                                                            setDeliveryModalOpen(true);
                                                                        }}
                                                                    >
                                                                        <PackageCheck className="w-3 h-3 text-emerald-600" />
                                                                        {job.status === 'delivered' ? t('Receipt Confirmed') : t('Acknowledge Receipt')}
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>

            {/* CLEARING & FORWARDING REQUEST DIALOG (SRS Section 4.2) */}
            <Dialog open={clearingModalOpen} onOpenChange={setClearingModalOpen}>
                <DialogContent className="max-w-xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Ship className="w-5 h-5 text-indigo-600" />
                            {t('Submit Clearing & Forwarding Instruction')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Provide shipment particulars and upload commercial documentation for customs clearance.')}
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitClearingRequest} className="space-y-3 pt-1">
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">{t('Service Type *')}</Label>
                                <select
                                    value={clearingForm.data.service_type}
                                    onChange={(e) => clearingForm.setData('service_type', e.target.value as any)}
                                    className="w-full h-8 text-xs border rounded-md px-2 bg-background mt-1"
                                >
                                    <option value="import">{t('Import Clearing')}</option>
                                    <option value="export">{t('Export Forwarding')}</option>
                                    <option value="transit">{t('Transit Cargo')}</option>
                                    <option value="domestic">{t('Domestic Freight')}</option>
                                </select>
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">{t('Transport Mode *')}</Label>
                                <select
                                    value={clearingForm.data.transport_mode}
                                    onChange={(e) => clearingForm.setData('transport_mode', e.target.value as any)}
                                    className="w-full h-8 text-xs border rounded-md px-2 bg-background mt-1"
                                >
                                    <option value="sea">{t('Ocean Freight (Sea)')}</option>
                                    <option value="air">{t('Air Cargo (Air)')}</option>
                                    <option value="road">{t('Cross-Border (Road)')}</option>
                                    <option value="rail">{t('Rail Transport')}</option>
                                </select>
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">{t('Customs Regime *')}</Label>
                                <select
                                    value={clearingForm.data.customs_regime}
                                    onChange={(e) => clearingForm.setData('customs_regime', e.target.value as any)}
                                    className="w-full h-8 text-xs border rounded-md px-2 bg-background mt-1"
                                >
                                    <option value="home_use">IM4 - Home Consumption</option>
                                    <option value="transit">TR8 - Transit Corridor</option>
                                    <option value="warehousing">WH7 - Bonded Warehouse</option>
                                    <option value="temporary_import">TI5 - Temporary Import</option>
                                </select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">{t('BL / AWB Number *')}</Label>
                                <Input
                                    required
                                    placeholder="e.g. MSKU98172601 or 086-12345678"
                                    value={clearingForm.data.bl_awb_number}
                                    onChange={(e) => clearingForm.setData('bl_awb_number', e.target.value)}
                                    className="h-8 text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">{t('Vessel / Flight / Carrier')}</Label>
                                <Input
                                    placeholder="e.g. MAERSK SHEKOU / ET 804"
                                    value={clearingForm.data.vessel_flight_truck}
                                    onChange={(e) => clearingForm.setData('vessel_flight_truck', e.target.value)}
                                    className="h-8 text-xs mt-1"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">{t('Port of Discharge *')}</Label>
                                <Input
                                    required
                                    placeholder="e.g. Dar es Salaam Port (TICTS)"
                                    value={clearingForm.data.port_of_discharge}
                                    onChange={(e) => clearingForm.setData('port_of_discharge', e.target.value)}
                                    className="h-8 text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">{t('Final Delivery Destination *')}</Label>
                                <Input
                                    required
                                    placeholder="e.g. Customer Warehouse, Chang'ombe"
                                    value={clearingForm.data.final_destination}
                                    onChange={(e) => clearingForm.setData('final_destination', e.target.value)}
                                    className="h-8 text-xs mt-1"
                                />
                            </div>
                        </div>

                        <div>
                            <Label className="text-xs font-semibold">{t('Commodity Description *')}</Label>
                            <Input
                                required
                                placeholder="e.g. Commercial Telecommunications Equipment & Accessories"
                                value={clearingForm.data.commodity_description}
                                onChange={(e) => clearingForm.setData('commodity_description', e.target.value)}
                                className="h-8 text-xs mt-1"
                            />
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">{t('Package Count *')}</Label>
                                <Input
                                    type="number"
                                    required
                                    value={clearingForm.data.package_count}
                                    onChange={(e) => clearingForm.setData('package_count', e.target.value)}
                                    className="h-8 text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">{t('Gross Weight (KG)')}</Label>
                                <Input
                                    type="number"
                                    placeholder="e.g. 18500"
                                    value={clearingForm.data.gross_weight_kg}
                                    onChange={(e) => clearingForm.setData('gross_weight_kg', e.target.value)}
                                    className="h-8 text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">{t('Container No (if FCL)')}</Label>
                                <Input
                                    placeholder="MSKU1234567"
                                    value={clearingForm.data.container_number}
                                    onChange={(e) => clearingForm.setData('container_number', e.target.value)}
                                    className="h-8 text-xs mt-1 uppercase"
                                />
                            </div>
                        </div>

                        <div className="border-t pt-2 space-y-2">
                            <span className="text-xs font-bold text-slate-800 dark:text-slate-200 block">
                                {t('Upload Mandatory Shipping Documents')}
                            </span>
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                <div className="p-2 border rounded-lg bg-muted/30">
                                    <Label className="text-[11px] font-medium">{t('Bill of Lading / AWB')}</Label>
                                    <input
                                        type="file"
                                        className="text-[10px] mt-1 w-full"
                                        onChange={(e) => clearingForm.setData('bill_of_lading', e.target.files?.[0] || null)}
                                    />
                                </div>
                                <div className="p-2 border rounded-lg bg-muted/30">
                                    <Label className="text-[11px] font-medium">{t('Commercial Invoice')}</Label>
                                    <input
                                        type="file"
                                        className="text-[10px] mt-1 w-full"
                                        onChange={(e) => clearingForm.setData('commercial_invoice', e.target.files?.[0] || null)}
                                    />
                                </div>
                                <div className="p-2 border rounded-lg bg-muted/30">
                                    <Label className="text-[11px] font-medium">{t('Packing List')}</Label>
                                    <input
                                        type="file"
                                        className="text-[10px] mt-1 w-full"
                                        onChange={(e) => clearingForm.setData('packing_list', e.target.files?.[0] || null)}
                                    />
                                </div>
                            </div>
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setClearingModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={clearingForm.processing} className="bg-indigo-700 hover:bg-indigo-800 text-white">
                                {t('Submit Clearing Instruction')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* LIVE TRACKING TIMELINE DIALOG */}
            <Dialog open={selectedJobForTracking !== null} onOpenChange={(open) => { if (!open) setSelectedJobForTracking(null); }}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-base">
                            <Navigation className="w-4 h-4 text-indigo-600" />
                            {t('Shipment Tracking Timeline')} — {selectedJobForTracking?.job_number}
                        </DialogTitle>
                        <DialogDescription className="text-xs">
                            BL/AWB: {selectedJobForTracking?.shipment?.bl_awb_number || 'Pending'} &bull; Status: <span className="capitalize font-semibold text-slate-800 dark:text-slate-200">{selectedJobForTracking?.status?.replace('_', ' ')}</span>
                        </DialogDescription>
                    </DialogHeader>
                    <div className="py-2 max-h-[400px] overflow-y-auto space-y-3">
                        {selectedJobForTracking?.tracking_events && selectedJobForTracking.tracking_events.length > 0 ? (
                            <div className="relative border-l-2 border-indigo-200 dark:border-indigo-800 ml-3 space-y-4 pl-4 text-xs">
                                {selectedJobForTracking.tracking_events.map((ev: any, idx: number) => (
                                    <div key={ev.id || idx} className="relative">
                                        <div className="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-indigo-600 ring-4 ring-white dark:ring-slate-900" />
                                        <div className="font-semibold text-slate-900 dark:text-slate-100">{ev.location_name}</div>
                                        <div className="text-[11px] text-muted-foreground mt-0.5">{ev.remarks}</div>
                                        <div className="text-[10px] text-indigo-700 dark:text-indigo-400 font-mono mt-1">
                                            {new Date(ev.event_time).toLocaleString()}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-xs text-muted-foreground text-center py-4">
                                {t('No tracking events logged yet. Updates will appear as your consignment progresses.')}
                            </p>
                        )}
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setSelectedJobForTracking(null)}>
                            {t('Close')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* DELIVERY ACKNOWLEDGEMENT & DISCREPANCY DIALOG (SRS Section 4.7 / CUS-033) */}
            <Dialog open={deliveryModalOpen} onOpenChange={setDeliveryModalOpen}>
                <DialogContent className="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle className="text-base flex items-center gap-2">
                            <PackageCheck className="w-4 h-4 text-emerald-600" />
                            {t('Acknowledge Cargo Delivery / Report Discrepancy')}
                        </DialogTitle>
                        <DialogDescription className="text-xs">
                            {t('Confirm that your cargo has arrived at the destination in sound condition, or report missing/damaged items.')}
                        </DialogDescription>
                    </DialogHeader>

                    {selectedJobForDelivery && (
                        <div className="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900 border text-xs space-y-1">
                            <div className="flex justify-between font-semibold">
                                <span>{selectedJobForDelivery.job_number}</span>
                                <Badge variant="outline" className="text-[10px]">{selectedJobForDelivery.status}</Badge>
                            </div>
                            <div className="text-muted-foreground">
                                Destination: {selectedJobForDelivery.shipment?.final_destination || 'N/A'}
                            </div>
                        </div>
                    )}

                    <form onSubmit={submitDeliveryAcknowledgement} className="space-y-3 pt-1">
                        <div>
                            <Label className="text-xs">{t('Acknowledgment Type')} *</Label>
                            <div className="grid grid-cols-2 gap-2 mt-1">
                                <button
                                    type="button"
                                    onClick={() => deliveryForm.setData('acknowledgement_type', 'confirmed')}
                                    className={`p-2.5 rounded-lg border text-left text-xs transition-colors flex items-center gap-2 ${
                                        deliveryForm.data.acknowledgement_type === 'confirmed'
                                            ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 font-medium text-emerald-900 dark:text-emerald-300'
                                            : 'border-slate-200 hover:bg-slate-50'
                                    }`}
                                >
                                    <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
                                    <div>
                                        <div className="font-semibold">{t('Confirm Receipt')}</div>
                                        <div className="text-[10px] text-muted-foreground">{t('Cargo received soundly')}</div>
                                    </div>
                                </button>

                                <button
                                    type="button"
                                    onClick={() => deliveryForm.setData('acknowledgement_type', 'discrepancy')}
                                    className={`p-2.5 rounded-lg border text-left text-xs transition-colors flex items-center gap-2 ${
                                        deliveryForm.data.acknowledgement_type === 'discrepancy'
                                            ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 font-medium text-rose-900 dark:text-rose-300'
                                            : 'border-slate-200 hover:bg-slate-50'
                                    }`}
                                >
                                    <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
                                    <div>
                                        <div className="font-semibold">{t('Report Discrepancy')}</div>
                                        <div className="text-[10px] text-muted-foreground">{t('Damage or shortage')}</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div>
                            <Label className="text-xs">{t('Received By (Full Name / Title)')} *</Label>
                            <Input
                                className="h-8 text-xs mt-1"
                                required
                                placeholder="e.g. John Doe (Warehouse Receiver)"
                                value={deliveryForm.data.received_by_name}
                                onChange={(e) => deliveryForm.setData('received_by_name', e.target.value)}
                            />
                        </div>

                        <div>
                            <Label className="text-xs">{t('Remarks / Discrepancy Details')}</Label>
                            <Textarea
                                className="text-xs mt-1"
                                rows={2}
                                placeholder={
                                    deliveryForm.data.acknowledgement_type === 'confirmed'
                                        ? "e.g. Cargo received in 100% good condition. Seals intact."
                                        : "Describe any package damages, missing seals, or quantity shortages..."
                                }
                                value={deliveryForm.data.notes}
                                onChange={(e) => deliveryForm.setData('notes', e.target.value)}
                            />
                        </div>

                        <div>
                            <Label className="text-xs">{t('Signed Delivery Note / POD (Optional)')}</Label>
                            <Input
                                type="file"
                                className="h-8 text-xs mt-1"
                                accept=".pdf,.jpg,.jpeg,.png"
                                onChange={(e) => deliveryForm.setData('pod_document', e.target.files?.[0] || null)}
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setDeliveryModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button
                                type="submit"
                                size="sm"
                                disabled={deliveryForm.processing}
                                className={
                                    deliveryForm.data.acknowledgement_type === 'confirmed'
                                        ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                                        : 'bg-rose-600 hover:bg-rose-700 text-white'
                                }
                            >
                                {deliveryForm.data.acknowledgement_type === 'confirmed'
                                    ? t('Confirm Cargo Receipt')
                                    : t('Submit Discrepancy Notice')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

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
