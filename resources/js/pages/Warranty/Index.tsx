import React, { useState, useMemo } from 'react';
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
    ShieldCheck,
    Wrench,
    Clock,
    CheckCircle2,
    AlertCircle,
    Search,
    Plus,
    FileText,
    Truck,
    Smartphone,
    User,
    Calendar,
    DollarSign,
    RefreshCw,
    ExternalLink,
    Filter
} from 'lucide-react';

interface Warranty {
    id: number;
    warranty_code: string;
    serial_number: string;
    customer_name: string;
    customer_phone?: string;
    customer_email?: string;
    start_date: string;
    end_date: string;
    warranty_period_months: number;
    warranty_terms?: string;
    status: 'active' | 'expired' | 'voided' | 'claimed';
    days_remaining?: number;
    service_tickets_count?: number;
}

interface ServiceTicket {
    id: number;
    ticket_number: string;
    warranty_id?: number | null;
    customer_name: string;
    contact_phone?: string;
    product_name: string;
    serial_number?: string;
    service_type: 'warranty_claim' | 'paid_repair' | 'preventive_maintenance';
    issue_reported: string;
    diagnostic_findings?: string;
    vendor_repair: boolean;
    vendor_name?: string;
    vendor_tracking_ref?: string;
    estimated_cost: number;
    actual_cost: number;
    assigned_technician_id?: number;
    status: 'received' | 'diagnosing' | 'sent_to_vendor' | 'repaired' | 'delivered_to_customer' | 'cancelled';
    completed_at?: string;
    created_at: string;
    technician?: { id: number; name: string };
    warranty?: Warranty;
}

interface PageProps {
    metrics: {
        total_warranties: number;
        active_warranties: number;
        expired_warranties: number;
        open_tickets: number;
        vendor_repairs: number;
        completed_tickets: number;
    };
    warranties: Warranty[];
    service_tickets: ServiceTicket[];
    products: Array<{ id: number; name: string; sku?: string }>;
    technicians: Array<{ id: number; name: string; email?: string }>;
}

export default function WarrantyIndex({ metrics, warranties, service_tickets, products, technicians }: PageProps) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState<'warranties' | 'tickets' | 'lookup' | 'device_repairs'>('warranties');

    // Filter states
    const [warrantySearch, setWarrantySearch] = useState('');
    const [ticketSearch, setTicketSearch] = useState('');
    const [ticketStatusFilter, setTicketStatusFilter] = useState('all');

    // Dialog states
    const [warrantyDialogOpen, setWarrantyDialogOpen] = useState(false);
    const [ticketDialogOpen, setTicketDialogOpen] = useState(false);
    const [updateStatusDialogOpen, setUpdateStatusDialogOpen] = useState(false);
    const [selectedTicket, setSelectedTicket] = useState<ServiceTicket | null>(null);

    // Live Lookup states
    const [lookupQuery, setLookupQuery] = useState('');
    const [lookupLoading, setLookupLoading] = useState(false);
    const [lookupResult, setLookupResult] = useState<any | null>(null);
    const [lookupError, setLookupError] = useState<string | null>(null);

    // Warranty Form
    const warrantyForm = useForm({
        serial_number: '',
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        product_id: '',
        start_date: new Date().toISOString().split('T')[0],
        warranty_period_months: '12',
        warranty_terms: 'Standard manufacturer hardware warranty coverage against defects.',
    });

    // Service Ticket Form
    const ticketForm = useForm({
        product_name: '',
        serial_number: '',
        customer_name: '',
        contact_phone: '',
        service_type: 'warranty_claim',
        issue_reported: '',
        vendor_repair: false,
        vendor_name: '',
        vendor_tracking_ref: '',
        estimated_cost: '0',
        assigned_technician_id: '',
    });

    // Update Status Form
    const statusForm = useForm({
        status: 'received',
        diagnostic_findings: '',
        actual_cost: '0',
        vendor_tracking_ref: '',
    });

    const handleCreateWarranty = (e: React.FormEvent) => {
        e.preventDefault();
        warrantyForm.post(route('warranty.warranties.store'), {
            onSuccess: () => {
                setWarrantyDialogOpen(false);
                warrantyForm.reset();
            },
        });
    };

    const handleCreateTicket = (e: React.FormEvent) => {
        e.preventDefault();
        ticketForm.post(route('warranty.tickets.store'), {
            onSuccess: () => {
                setTicketDialogOpen(false);
                ticketForm.reset();
            },
        });
    };

    const openUpdateStatus = (ticket: ServiceTicket) => {
        setSelectedTicket(ticket);
        statusForm.setData({
            status: ticket.status,
            diagnostic_findings: ticket.diagnostic_findings || '',
            actual_cost: String(ticket.actual_cost || 0),
            vendor_tracking_ref: ticket.vendor_tracking_ref || '',
        });
        setUpdateStatusDialogOpen(true);
    };

    const handleUpdateStatus = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedTicket) return;
        statusForm.put(route('warranty.tickets.update', selectedTicket.id), {
            onSuccess: () => {
                setUpdateStatusDialogOpen(false);
                setSelectedTicket(null);
            },
        });
    };

    const handlePerformLookup = async () => {
        if (!lookupQuery.trim()) return;
        setLookupLoading(true);
        setLookupError(null);
        setLookupResult(null);
        try {
            const res = await fetch(route('warranty.lookup') + `?q=${encodeURIComponent(lookupQuery.trim())}`);
            const data = await res.json();
            if (data.found) {
                setLookupResult(data.warranty);
            } else {
                setLookupError(data.message || 'No matching warranty certificate found.');
            }
        } catch (err) {
            setLookupError('Failed to query warranty registry. Please try again.');
        } finally {
            setLookupLoading(false);
        }
    };

    const filteredWarranties = useMemo(() => {
        return warranties.filter((w) => {
            const term = warrantySearch.toLowerCase();
            return (
                w.warranty_code.toLowerCase().includes(term) ||
                w.serial_number.toLowerCase().includes(term) ||
                w.customer_name.toLowerCase().includes(term) ||
                (w.customer_phone && w.customer_phone.includes(term))
            );
        });
    }, [warranties, warrantySearch]);

    const filteredTickets = useMemo(() => {
        return service_tickets.filter((t) => {
            const matchesSearch =
                t.ticket_number.toLowerCase().includes(ticketSearch.toLowerCase()) ||
                t.customer_name.toLowerCase().includes(ticketSearch.toLowerCase()) ||
                t.product_name.toLowerCase().includes(ticketSearch.toLowerCase()) ||
                (t.serial_number && t.serial_number.toLowerCase().includes(ticketSearch.toLowerCase()));

            const matchesStatus = ticketStatusFilter === 'all' || t.status === ticketStatusFilter;
            return matchesSearch && matchesStatus;
        });
    }, [service_tickets, ticketSearch, ticketStatusFilter]);

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'active':
                return <Badge className="bg-emerald-600/90 text-white font-medium">Active Coverage</Badge>;
            case 'expired':
                return <Badge variant="secondary" className="bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">Expired</Badge>;
            case 'voided':
                return <Badge variant="destructive">Voided</Badge>;
            case 'claimed':
                return <Badge className="bg-amber-500 text-white">Claimed</Badge>;
            case 'received':
                return <Badge variant="outline" className="border-blue-500 text-blue-600">Received</Badge>;
            case 'diagnosing':
                return <Badge className="bg-amber-500 text-white">Diagnosing</Badge>;
            case 'sent_to_vendor':
                return <Badge className="bg-purple-600 text-white">Vendor RMA</Badge>;
            case 'repaired':
                return <Badge className="bg-teal-600 text-white">Repaired</Badge>;
            case 'delivered_to_customer':
                return <Badge className="bg-emerald-600 text-white">Delivered / Closed</Badge>;
            case 'cancelled':
                return <Badge variant="secondary">Cancelled</Badge>;
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Warranty & Servicing')} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                            <ShieldCheck className="h-7 w-7 text-primary" />
                            {t('Warranty & Servicing Management')}
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            {t('Track hardware warranties, validate serial numbers, manage RMA repair tickets and technician assignments')}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            onClick={() => {
                                setActiveTab('lookup');
                            }}
                            className="flex items-center gap-2"
                        >
                            <Search className="h-4 w-4 text-primary" />
                            {t('Quick Serial Lookup')}
                        </Button>
                        <Button
                            variant="outline"
                            onClick={() => setWarrantyDialogOpen(true)}
                            className="flex items-center gap-2"
                        >
                            <Plus className="h-4 w-4" />
                            {t('Register Warranty')}
                        </Button>
                        <Button
                            onClick={() => setTicketDialogOpen(true)}
                            className="flex items-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground"
                        >
                            <Wrench className="h-4 w-4" />
                            {t('Open Service Ticket')}
                        </Button>
                    </div>
                </div>

                {/* Metrics Cards */}
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Total Warranties')}</span>
                                <FileText className="h-4 w-4 text-blue-500" />
                            </div>
                            <div className="text-2xl font-bold mt-2">{metrics.total_warranties}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-emerald-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Active Coverage')}</span>
                                <ShieldCheck className="h-4 w-4 text-emerald-500" />
                            </div>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{metrics.active_warranties}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Expired')}</span>
                                <AlertCircle className="h-4 w-4 text-rose-500" />
                            </div>
                            <div className="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">{metrics.expired_warranties}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm border-l-4 border-l-amber-500">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Open Tickets')}</span>
                                <Wrench className="h-4 w-4 text-amber-500" />
                            </div>
                            <div className="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2">{metrics.open_tickets}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Vendor RMA')}</span>
                                <Truck className="h-4 w-4 text-purple-500" />
                            </div>
                            <div className="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2">{metrics.vendor_repairs}</div>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-muted-foreground">{t('Resolved')}</span>
                                <CheckCircle2 className="h-4 w-4 text-teal-500" />
                            </div>
                            <div className="text-2xl font-bold text-teal-600 dark:text-teal-400 mt-2">{metrics.completed_tickets}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs Navigation */}
                <div className="border-b border-border">
                    <nav className="flex space-x-6">
                        <button
                            onClick={() => setActiveTab('warranties')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'warranties'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <ShieldCheck className="h-4 w-4" />
                            {t('Warranty Certificates')} ({warranties.length})
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
                            {t('Service & Repair Tickets')} ({service_tickets.length})
                        </button>

                        <button
                            onClick={() => setActiveTab('lookup')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'lookup'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Search className="h-4 w-4" />
                            {t('Serial Verification Lookup')}
                        </button>

                        <button
                            onClick={() => setActiveTab('device_repairs')}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                activeTab === 'device_repairs'
                                    ? 'border-primary text-primary font-semibold'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            }`}
                        >
                            <Smartphone className="h-4 w-4" />
                            {t('Device & Mobile Repairs')}
                        </button>
                    </nav>
                </div>

                {/* TAB 1: Warranty Registrations */}
                {activeTab === 'warranties' && (
                    <Card className="shadow-sm">
                        <CardHeader className="pb-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div>
                                <CardTitle className="text-base font-semibold">{t('Registered Warranties')}</CardTitle>
                                <CardDescription>{t('Manage hardware warranty certificates and coverage duration')}</CardDescription>
                            </div>
                            <div className="w-full md:w-72">
                                <Input
                                    placeholder={t('Search code, serial, customer...')}
                                    value={warrantySearch}
                                    onChange={(e) => setWarrantySearch(e.target.value)}
                                    className="h-9"
                                />
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/40">
                                            <TableHead>{t('Warranty Code')}</TableHead>
                                            <TableHead>{t('Serial Number')}</TableHead>
                                            <TableHead>{t('Customer')}</TableHead>
                                            <TableHead>{t('Coverage Period')}</TableHead>
                                            <TableHead>{t('Remaining')}</TableHead>
                                            <TableHead>{t('Status')}</TableHead>
                                            <TableHead className="text-right">{t('Action')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredWarranties.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={7} className="text-center py-8 text-muted-foreground">
                                                    {t('No warranty certificates found.')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            filteredWarranties.map((w) => (
                                                <TableRow key={w.id} className="hover:bg-muted/30">
                                                    <TableCell className="font-semibold text-primary">{w.warranty_code}</TableCell>
                                                    <TableCell>
                                                        <code className="bg-muted px-2 py-0.5 rounded text-xs font-mono">
                                                            {w.serial_number}
                                                        </code>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="font-medium text-sm">{w.customer_name}</div>
                                                        {w.customer_phone && (
                                                            <div className="text-xs text-muted-foreground">{w.customer_phone}</div>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-muted-foreground">
                                                        {w.start_date} → {w.end_date} ({w.warranty_period_months} mo)
                                                    </TableCell>
                                                    <TableCell>
                                                        {w.status === 'active' ? (
                                                            <span className="text-xs font-medium text-emerald-600">
                                                                {w.days_remaining} {t('days left')}
                                                            </span>
                                                        ) : (
                                                            <span className="text-xs text-muted-foreground">{t('Ended')}</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>{getStatusBadge(w.status)}</TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            size="sm"
                                                            variant="ghost"
                                                            onClick={() => {
                                                                ticketForm.setData({
                                                                    ...ticketForm.data,
                                                                    serial_number: w.serial_number,
                                                                    customer_name: w.customer_name,
                                                                    contact_phone: w.customer_phone || '',
                                                                });
                                                                setTicketDialogOpen(true);
                                                            }}
                                                        >
                                                            <Wrench className="h-3.5 w-3.5 mr-1 text-primary" />
                                                            {t('Claim / Service')}
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* TAB 2: Service & Repair Tickets */}
                {activeTab === 'tickets' && (
                    <Card className="shadow-sm">
                        <CardHeader className="pb-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div>
                                <CardTitle className="text-base font-semibold">{t('Service & Repair Tickets (RMA)')}</CardTitle>
                                <CardDescription>{t('Manage repair workflows, technician assignments and vendor dispatches')}</CardDescription>
                            </div>
                            <div className="flex items-center gap-2 w-full md:w-auto">
                                <Select value={ticketStatusFilter} onValueChange={setTicketStatusFilter}>
                                    <SelectTrigger className="w-36 h-9">
                                        <SelectValue placeholder={t('Filter status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                        <SelectItem value="received">{t('Received')}</SelectItem>
                                        <SelectItem value="diagnosing">{t('Diagnosing')}</SelectItem>
                                        <SelectItem value="sent_to_vendor">{t('Vendor RMA')}</SelectItem>
                                        <SelectItem value="repaired">{t('Repaired')}</SelectItem>
                                        <SelectItem value="delivered_to_customer">{t('Delivered')}</SelectItem>
                                    </SelectContent>
                                </Select>

                                <Input
                                    placeholder={t('Search ticket, product, serial...')}
                                    value={ticketSearch}
                                    onChange={(e) => setTicketSearch(e.target.value)}
                                    className="h-9 w-56"
                                />
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/40">
                                            <TableHead>{t('Ticket #')}</TableHead>
                                            <TableHead>{t('Product / Serial')}</TableHead>
                                            <TableHead>{t('Customer')}</TableHead>
                                            <TableHead>{t('Type')}</TableHead>
                                            <TableHead>{t('Issue Reported')}</TableHead>
                                            <TableHead>{t('Assigned')}</TableHead>
                                            <TableHead>{t('Status')}</TableHead>
                                            <TableHead className="text-right">{t('Action')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredTickets.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={8} className="text-center py-8 text-muted-foreground">
                                                    {t('No service tickets found.')}
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            filteredTickets.map((tkt) => (
                                                <TableRow key={tkt.id} className="hover:bg-muted/30">
                                                    <TableCell className="font-semibold text-primary">{tkt.ticket_number}</TableCell>
                                                    <TableCell>
                                                        <div className="font-medium text-sm">{tkt.product_name}</div>
                                                        {tkt.serial_number && (
                                                            <code className="text-xs bg-muted px-1.5 py-0.5 rounded font-mono">
                                                                {tkt.serial_number}
                                                            </code>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="font-medium text-sm">{tkt.customer_name}</div>
                                                        <div className="text-xs text-muted-foreground">{tkt.contact_phone}</div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <span className="text-xs capitalize font-medium">
                                                            {tkt.service_type.replace('_', ' ')}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell className="max-w-xs truncate text-xs text-muted-foreground">
                                                        {tkt.issue_reported}
                                                    </TableCell>
                                                    <TableCell className="text-xs">
                                                        {tkt.vendor_repair ? (
                                                            <span className="text-purple-600 font-medium">
                                                                Vendor: {tkt.vendor_name || 'Outside Lab'}
                                                            </span>
                                                        ) : (
                                                            <span>{tkt.technician?.name || 'Unassigned'}</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>{getStatusBadge(tkt.status)}</TableCell>
                                                    <TableCell className="text-right">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() => openUpdateStatus(tkt)}
                                                        >
                                                            {t('Update Status')}
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* TAB 3: Serial Verification Lookup */}
                {activeTab === 'lookup' && (
                    <Card className="shadow-sm max-w-2xl mx-auto">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Search className="h-5 w-5 text-primary" />
                                {t('Real-Time Warranty Verification Lookup')}
                            </CardTitle>
                            <CardDescription>
                                {t('Instantly check warranty status by Product Serial Number, Certificate Code, or Customer Phone Number')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex gap-2">
                                <Input
                                    placeholder={t('Enter Serial #, e.g. SN-892147 or WAR-2026-00001')}
                                    value={lookupQuery}
                                    onChange={(e) => setLookupQuery(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handlePerformLookup()}
                                    className="h-10 text-base font-mono"
                                />
                                <Button onClick={handlePerformLookup} disabled={lookupLoading} className="h-10 px-5">
                                    {lookupLoading ? <RefreshCw className="h-4 w-4 animate-spin" /> : <Search className="h-4 w-4 mr-1" />}
                                    {t('Verify')}
                                </Button>
                            </div>

                            {lookupError && (
                                <div className="p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
                                    <AlertCircle className="h-5 w-5 flex-shrink-0" />
                                    <span>{lookupError}</span>
                                </div>
                            )}

                            {lookupResult && (
                                <div className="p-5 rounded-lg border bg-card space-y-4">
                                    <div className="flex items-center justify-between border-b pb-3">
                                        <div>
                                            <div className="text-xs text-muted-foreground uppercase tracking-wider font-semibold">{t('Certificate Code')}</div>
                                            <div className="text-lg font-bold text-primary font-mono">{lookupResult.warranty_code}</div>
                                        </div>
                                        {lookupResult.is_active ? (
                                            <Badge className="bg-emerald-600 text-white px-3 py-1 text-sm font-semibold flex items-center gap-1.5">
                                                <CheckCircle2 className="h-4 w-4" />
                                                {t('GENUINE & ACTIVE COVERAGE')}
                                            </Badge>
                                        ) : (
                                            <Badge variant="destructive" className="px-3 py-1 text-sm font-semibold">
                                                {t('EXPIRED / INACTIVE')}
                                            </Badge>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span className="text-xs text-muted-foreground">{t('Customer Name')}:</span>
                                            <div className="font-medium">{lookupResult.customer_name}</div>
                                        </div>
                                        <div>
                                            <span className="text-xs text-muted-foreground">{t('Serial Number')}:</span>
                                            <div className="font-mono font-medium">{lookupResult.serial_number}</div>
                                        </div>
                                        <div>
                                            <span className="text-xs text-muted-foreground">{t('Start Date')}:</span>
                                            <div>{lookupResult.start_date}</div>
                                        </div>
                                        <div>
                                            <span className="text-xs text-muted-foreground">{t('Expiry Date')}:</span>
                                            <div>{lookupResult.end_date}</div>
                                        </div>
                                        <div>
                                            <span className="text-xs text-muted-foreground">{t('Remaining Validity')}:</span>
                                            <div className="font-semibold text-emerald-600">{lookupResult.days_remaining} {t('Days')}</div>
                                        </div>
                                        <div>
                                            <span className="text-xs text-muted-foreground">{t('Service Tickets Filed')}:</span>
                                            <div className="font-medium">{lookupResult.service_tickets_count} {t('tickets')}</div>
                                        </div>
                                    </div>

                                    <div className="pt-2 flex justify-end">
                                        <Button
                                            size="sm"
                                            onClick={() => {
                                                ticketForm.setData({
                                                    ...ticketForm.data,
                                                    serial_number: lookupResult.serial_number,
                                                    customer_name: lookupResult.customer_name,
                                                    contact_phone: lookupResult.customer_phone || '',
                                                });
                                                setTicketDialogOpen(true);
                                            }}
                                        >
                                            <Wrench className="h-4 w-4 mr-1.5" />
                                            {t('Create Claim Ticket for this Item')}
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* TAB 4: Device & Mobile Repairs */}
                {activeTab === 'device_repairs' && (
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Smartphone className="h-5 w-5 text-primary" />
                                {t('Device & Mobile Service Management')}
                            </CardTitle>
                            <CardDescription>
                                {t('Specialized workshop repair flows for mobile devices, diagnostics, customer quotes, and technician job sheets')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <Card className="p-4 border hover:border-primary transition-colors cursor-pointer" onClick={() => router.visit(route('mobile-service-management.pending-service-requests.index'))}>
                                    <div className="flex items-center gap-3">
                                        <div className="p-2.5 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                            <Clock className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <div className="font-semibold text-sm">{t('Pending Requests')}</div>
                                            <div className="text-xs text-muted-foreground">{t('Intake & customer queue')}</div>
                                        </div>
                                    </div>
                                </Card>

                                <Card className="p-4 border hover:border-primary transition-colors cursor-pointer" onClick={() => router.visit(route('mobile-service-management.service-requests.index'))}>
                                    <div className="flex items-center gap-3">
                                        <div className="p-2.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                            <Wrench className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <div className="font-semibold text-sm">{t('Active Service Requests')}</div>
                                            <div className="text-xs text-muted-foreground">{t('Workshop technician benches')}</div>
                                        </div>
                                    </div>
                                </Card>

                                <Card className="p-4 border hover:border-primary transition-colors cursor-pointer" onClick={() => router.visit(route('mobile-service-management.service-history.index'))}>
                                    <div className="flex items-center gap-3">
                                        <div className="p-2.5 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                            <CheckCircle2 className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <div className="font-semibold text-sm">{t('Service History & Billing')}</div>
                                            <div className="text-xs text-muted-foreground">{t('Delivered repairs & archive')}</div>
                                        </div>
                                    </div>
                                </Card>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>

            {/* REGISTER WARRANTY DIALOG */}
            <Dialog open={warrantyDialogOpen} onOpenChange={setWarrantyDialogOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Register Warranty Certificate')}</DialogTitle>
                        <DialogDescription>{t('Issue hardware coverage certificate linked to a serial number')}</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleCreateWarranty} className="space-y-3">
                        <div>
                            <Label>{t('Product Serial Number *')}</Label>
                            <Input
                                required
                                placeholder="e.g. SN-9821431"
                                value={warrantyForm.data.serial_number}
                                onChange={(e) => warrantyForm.setData('serial_number', e.target.value)}
                            />
                        </div>

                        <div>
                            <Label>{t('Customer Name *')}</Label>
                            <Input
                                required
                                placeholder="Customer / Organization Name"
                                value={warrantyForm.data.customer_name}
                                onChange={(e) => warrantyForm.setData('customer_name', e.target.value)}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Customer Phone')}</Label>
                                <Input
                                    placeholder="+255 7..."
                                    value={warrantyForm.data.customer_phone}
                                    onChange={(e) => warrantyForm.setData('customer_phone', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('Customer Email')}</Label>
                                <Input
                                    type="email"
                                    placeholder="client@example.com"
                                    value={warrantyForm.data.customer_email}
                                    onChange={(e) => warrantyForm.setData('customer_email', e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Start Date *')}</Label>
                                <Input
                                    type="date"
                                    required
                                    value={warrantyForm.data.start_date}
                                    onChange={(e) => warrantyForm.setData('start_date', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('Duration (Months) *')}</Label>
                                <Select
                                    value={warrantyForm.data.warranty_period_months}
                                    onValueChange={(val) => warrantyForm.setData('warranty_period_months', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="3">3 Months</SelectItem>
                                        <SelectItem value="6">6 Months</SelectItem>
                                        <SelectItem value="12">12 Months (1 Year)</SelectItem>
                                        <SelectItem value="24">24 Months (2 Years)</SelectItem>
                                        <SelectItem value="36">36 Months (3 Years)</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div>
                            <Label>{t('Warranty Terms / Coverage Details')}</Label>
                            <Textarea
                                rows={2}
                                value={warrantyForm.data.warranty_terms}
                                onChange={(e) => warrantyForm.setData('warranty_terms', e.target.value)}
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setWarrantyDialogOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={warrantyForm.processing}>
                                {t('Register & Issue')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* OPEN SERVICE TICKET DIALOG */}
            <Dialog open={ticketDialogOpen} onOpenChange={setTicketDialogOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>{t('Open Service & Repair Ticket (RMA)')}</DialogTitle>
                        <DialogDescription>{t('Log defective equipment, customer report, and diagnostic path')}</DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleCreateTicket} className="space-y-3">
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Product Name / Model *')}</Label>
                                <Input
                                    required
                                    placeholder="e.g. Dell Latitude 5420 / HP LaserJet"
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
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Customer Name *')}</Label>
                                <Input
                                    required
                                    placeholder="Contact Person"
                                    value={ticketForm.data.customer_name}
                                    onChange={(e) => ticketForm.setData('customer_name', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('Phone Number')}</Label>
                                <Input
                                    placeholder="+255 ..."
                                    value={ticketForm.data.contact_phone}
                                    onChange={(e) => ticketForm.setData('contact_phone', e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Service Type *')}</Label>
                                <Select
                                    value={ticketForm.data.service_type}
                                    onValueChange={(val: any) => ticketForm.setData('service_type', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="warranty_claim">{t('Warranty Claim')}</SelectItem>
                                        <SelectItem value="paid_repair">{t('Paid Repair')}</SelectItem>
                                        <SelectItem value="preventive_maintenance">{t('Maintenance')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label>{t('Estimated Cost (TZS)')}</Label>
                                <Input
                                    type="number"
                                    placeholder="0"
                                    value={ticketForm.data.estimated_cost}
                                    onChange={(e) => ticketForm.setData('estimated_cost', e.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <Label>{t('Defect / Issue Reported *')}</Label>
                            <Textarea
                                required
                                rows={2}
                                placeholder="Describe symptoms or breakdown cause..."
                                value={ticketForm.data.issue_reported}
                                onChange={(e) => ticketForm.setData('issue_reported', e.target.value)}
                            />
                        </div>

                        <div className="p-3 bg-muted/40 rounded-lg space-y-2 border">
                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="vendor_repair"
                                    checked={ticketForm.data.vendor_repair}
                                    onChange={(e) => ticketForm.setData('vendor_repair', e.target.checked)}
                                    className="rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <Label htmlFor="vendor_repair" className="font-semibold cursor-pointer">
                                    {t('Dispatch to External Vendor / OEM Manufacturer')}
                                </Label>
                            </div>

                            {ticketForm.data.vendor_repair && (
                                <div className="grid grid-cols-2 gap-2 pt-1">
                                    <div>
                                        <Label className="text-xs">{t('Vendor / Repair Center Name')}</Label>
                                        <Input
                                            placeholder="e.g. Authorized Sony Center"
                                            value={ticketForm.data.vendor_name}
                                            onChange={(e) => ticketForm.setData('vendor_name', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label className="text-xs">{t('Waybill / Tracking Ref')}</Label>
                                        <Input
                                            placeholder="DHL / Waybill #"
                                            value={ticketForm.data.vendor_tracking_ref}
                                            onChange={(e) => ticketForm.setData('vendor_tracking_ref', e.target.value)}
                                        />
                                    </div>
                                </div>
                            )}
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setTicketDialogOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={ticketForm.processing}>
                                {t('Open Ticket')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* UPDATE STATUS DIALOG */}
            <Dialog open={updateStatusDialogOpen} onOpenChange={setUpdateStatusDialogOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Update Service Ticket Status')}</DialogTitle>
                        <DialogDescription>
                            {selectedTicket?.ticket_number} — {selectedTicket?.product_name}
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleUpdateStatus} className="space-y-3">
                        <div>
                            <Label>{t('Current Stage / Status *')}</Label>
                            <Select
                                value={statusForm.data.status}
                                onValueChange={(val: any) => statusForm.setData('status', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="received">{t('Received at Desk')}</SelectItem>
                                    <SelectItem value="diagnosing">{t('Under Diagnostics')}</SelectItem>
                                    <SelectItem value="sent_to_vendor">{t('Sent to Vendor / Outside RMA')}</SelectItem>
                                    <SelectItem value="repaired">{t('Repaired & Quality Tested')}</SelectItem>
                                    <SelectItem value="delivered_to_customer">{t('Delivered to Customer (Closed)')}</SelectItem>
                                    <SelectItem value="cancelled">{t('Cancelled / Unrepairable')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label>{t('Technician Findings / Diagnostic Notes')}</Label>
                            <Textarea
                                rows={2}
                                placeholder="Component replaced, diagnostics performed..."
                                value={statusForm.data.diagnostic_findings}
                                onChange={(e) => statusForm.setData('diagnostic_findings', e.target.value)}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label>{t('Actual Cost (TZS)')}</Label>
                                <Input
                                    type="number"
                                    value={statusForm.data.actual_cost}
                                    onChange={(e) => statusForm.setData('actual_cost', e.target.value)}
                                />
                            </div>
                            <div>
                                <Label>{t('Vendor Tracking / Waybill')}</Label>
                                <Input
                                    value={statusForm.data.vendor_tracking_ref}
                                    onChange={(e) => statusForm.setData('vendor_tracking_ref', e.target.value)}
                                />
                            </div>
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setUpdateStatusDialogOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={statusForm.processing}>
                                {t('Save Status')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}