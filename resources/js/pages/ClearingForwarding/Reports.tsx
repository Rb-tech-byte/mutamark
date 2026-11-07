import React, { useState } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    BarChart3,
    Download,
    Filter,
    RotateCcw,
    Ship,
    Truck,
    Container,
    ShieldCheck,
    Clock,
    AlertTriangle,
    DollarSign,
    CheckCircle2,
    Calendar,
    ArrowLeft,
    Check,
    X,
    FileText
} from 'lucide-react';
import { CFJob } from './types';

interface ReportsProps {
    jobs: CFJob[];
    filters: {
        status?: string;
        transport_mode?: string;
        service_type?: string;
        date_from?: string;
        date_to?: string;
    };
    metrics: {
        total_jobs: number;
        cleared_jobs: number;
        total_customs_duty_tzs: number;
        total_demurrage_tzs: number;
        total_advances_tzs: number;
        total_revenue_tzs: number;
        total_disbursed_tzs: number;
        total_profit_tzs: number;
        stage_counts: {
            documents_pending: number;
            customs_processing: number;
            customs_cleared: number;
            in_transit: number;
            delivered: number;
        };
    };
}

export default function Reports({ jobs = [], filters = {}, metrics }: ReportsProps) {
    const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
    const [selectedMode, setSelectedMode] = useState(filters.transport_mode || '');
    const [selectedService, setSelectedService] = useState(filters.service_type || '');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');

    const applyFilters = () => {
        router.get(
            route('clearing-forwarding.reports'),
            {
                status: selectedStatus || undefined,
                transport_mode: selectedMode || undefined,
                service_type: selectedService || undefined,
                date_from: dateFrom || undefined,
                date_to: dateTo || undefined,
            },
            { preserveState: true }
        );
    };

    const resetFilters = () => {
        setSelectedStatus('');
        setSelectedMode('');
        setSelectedService('');
        setDateFrom('');
        setDateTo('');
        router.get(route('clearing-forwarding.reports'));
    };

    const exportCsvUrl = route('clearing-forwarding.reports.export-csv', {
        status: selectedStatus || undefined,
        transport_mode: selectedMode || undefined,
        service_type: selectedService || undefined,
        date_from: dateFrom || undefined,
        date_to: dateTo || undefined,
    });

    const formatMoney = (val: number) => {
        return 'TZS ' + Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'cargo_released':
            case 'completed':
            case 'delivered':
                return <Badge className="bg-emerald-100 text-emerald-800 border-emerald-300 capitalize">{status.replace('_', ' ')}</Badge>;
            case 'customs_assessment':
            case 'payment_pending':
                return <Badge className="bg-blue-100 text-blue-800 border-blue-300 capitalize">{status.replace('_', ' ')}</Badge>;
            case 'documents_pending':
                return <Badge className="bg-amber-100 text-amber-800 border-amber-300 capitalize">{status.replace('_', ' ')}</Badge>;
            case 'in_transit':
                return <Badge className="bg-indigo-100 text-indigo-800 border-indigo-300 capitalize">{status.replace('_', ' ')}</Badge>;
            default:
                return <Badge variant="outline" className="capitalize">{status.replace('_', ' ')}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="C&F Reports & Analytics - Mutamark ERP" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <Link href={route('clearing-forwarding.dashboard')}>
                                <Button size="sm" variant="ghost" className="h-8 px-2 text-muted-foreground">
                                    <ArrowLeft className="w-4 h-4 mr-1" />
                                    Dashboard
                                </Button>
                            </Link>
                            <h1 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <BarChart3 className="w-6 h-6 text-indigo-600" />
                                C&F Operations & Demurrage Reports
                            </h1>
                        </div>
                        <p className="text-sm text-muted-foreground mt-1">
                            Audited reporting across consignment turnaround, TANCIS customs tax assessments, container free-time demurrage, and job profitability.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <a href={exportCsvUrl} download>
                            <Button size="sm" variant="outline" className="text-xs gap-1.5 h-9 bg-white dark:bg-slate-900">
                                <Download className="w-4 h-4 text-emerald-600" />
                                Export Report (CSV / Excel)
                            </Button>
                        </a>
                    </div>
                </div>

                {/* Filter Toolbar */}
                <Card className="border shadow-sm">
                    <CardContent className="p-4 space-y-3">
                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                            <div>
                                <Label className="text-xs font-semibold">Stage / Status</Label>
                                <select
                                    value={selectedStatus}
                                    onChange={(e) => setSelectedStatus(e.target.value)}
                                    className="w-full h-8 mt-1 text-xs border rounded-md px-2 bg-background"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="documents_pending">Documents Pending</option>
                                    <option value="documents_verified">Documents Verified</option>
                                    <option value="declaration_drafted">Declaration Drafted</option>
                                    <option value="declaration_submitted">Lodged in TANCIS</option>
                                    <option value="customs_assessment">Assessed by TRA</option>
                                    <option value="payment_pending">Payment Pending</option>
                                    <option value="customs_cleared">Customs Cleared</option>
                                    <option value="in_transit">In Transit (Haulage)</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="completed">Completed & Reconciled</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Transport Mode</Label>
                                <select
                                    value={selectedMode}
                                    onChange={(e) => setSelectedMode(e.target.value)}
                                    className="w-full h-8 mt-1 text-xs border rounded-md px-2 bg-background"
                                >
                                    <option value="">All Modes</option>
                                    <option value="sea">Sea Freight</option>
                                    <option value="air">Air Freight</option>
                                    <option value="road">Road Freight</option>
                                    <option value="rail">Rail Freight</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Service Type</Label>
                                <select
                                    value={selectedService}
                                    onChange={(e) => setSelectedService(e.target.value)}
                                    className="w-full h-8 mt-1 text-xs border rounded-md px-2 bg-background"
                                >
                                    <option value="">All Services</option>
                                    <option value="import">Import Clearance</option>
                                    <option value="export">Export Forwarding</option>
                                    <option value="transit">Transit Cargo</option>
                                    <option value="domestic">Domestic Haulage</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Opened From</Label>
                                <Input
                                    type="date"
                                    className="h-8 mt-1 text-xs"
                                    value={dateFrom}
                                    onChange={(e) => setDateFrom(e.target.value)}
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Opened To</Label>
                                <Input
                                    type="date"
                                    className="h-8 mt-1 text-xs"
                                    value={dateTo}
                                    onChange={(e) => setDateTo(e.target.value)}
                                />
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-2 pt-1 border-t">
                            <Button size="sm" variant="ghost" onClick={resetFilters} className="text-xs h-8 gap-1 text-muted-foreground">
                                <RotateCcw className="w-3.5 h-3.5" />
                                Reset Filters
                            </Button>
                            <Button size="sm" onClick={applyFilters} className="text-xs h-8 gap-1 bg-indigo-600 hover:bg-indigo-700 text-white">
                                <Filter className="w-3.5 h-3.5" />
                                Apply Filters
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* KPI Overview Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase flex items-center justify-between">
                                Total Filtered Jobs
                                <Ship className="w-4 h-4 text-indigo-600" />
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.total_jobs}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {metrics.cleared_jobs} cleared / delivered ({metrics.total_jobs > 0 ? Math.round((metrics.cleared_jobs / metrics.total_jobs) * 100) : 0}%)
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase flex items-center justify-between">
                                Assessed Customs Duties
                                <ShieldCheck className="w-4 h-4 text-blue-600" />
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold text-blue-700 dark:text-blue-400">
                                {formatMoney(metrics.total_customs_duty_tzs)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Official TRA assessments recorded
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase flex items-center justify-between">
                                Demurrage Incurred
                                <Container className="w-4 h-4 text-amber-600" />
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold text-amber-700 dark:text-amber-400">
                                {formatMoney(metrics.total_demurrage_tzs)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Container detention & storage charges
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase flex items-center justify-between">
                                Gross Margin / Profit
                                <DollarSign className="w-4 h-4 text-emerald-600" />
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xl font-bold text-emerald-700 dark:text-emerald-400">
                                {formatMoney(metrics.total_profit_tzs)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Revenue: {formatMoney(metrics.total_revenue_tzs)}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabbed Report Registers */}
                <Tabs defaultValue="pipeline" className="space-y-4">
                    <TabsList className="bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                        <TabsTrigger value="pipeline" className="text-xs">
                            Operational Pipeline ({jobs.length})
                        </TabsTrigger>
                        <TabsTrigger value="customs" className="text-xs">
                            Customs & TANCIS Audit
                        </TabsTrigger>
                        <TabsTrigger value="containers" className="text-xs">
                            Container Demurrage Audit
                        </TabsTrigger>
                        <TabsTrigger value="financials" className="text-xs">
                            Financial Profitability
                        </TabsTrigger>
                    </TabsList>

                    {/* TAB 1: OPERATIONAL PIPELINE */}
                    <TabsContent value="pipeline">
                        <Card className="border shadow-sm">
                            <CardHeader className="p-4 pb-2">
                                <CardTitle className="text-sm font-semibold">Consignments & Routing Register</CardTitle>
                                <CardDescription className="text-xs">
                                    All clearing jobs matching the current filter criteria with carrier references and locations.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50 dark:bg-slate-900">
                                            <TableHead className="text-xs">Job Number</TableHead>
                                            <TableHead className="text-xs">Customer</TableHead>
                                            <TableHead className="text-xs">Mode & Service</TableHead>
                                            <TableHead className="text-xs">BL / AWB No</TableHead>
                                            <TableHead className="text-xs">Port of Discharge</TableHead>
                                            <TableHead className="text-xs">Destination</TableHead>
                                            <TableHead className="text-xs">Current Stage</TableHead>
                                            <TableHead className="text-xs text-right">Action</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {jobs.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={8} className="text-center py-6 text-xs text-muted-foreground">
                                                    No consignment records found matching the applied filters.
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            jobs.map((job) => (
                                                <TableRow key={job.id} className="hover:bg-slate-50/50">
                                                    <TableCell className="font-mono text-xs font-semibold">
                                                        <Link href={route('clearing-forwarding.jobs.show', job.id)} className="text-indigo-600 hover:underline">
                                                            {job.job_number}
                                                        </Link>
                                                    </TableCell>
                                                    <TableCell className="text-xs font-medium">
                                                        {job.customer?.name || 'N/A'}
                                                    </TableCell>
                                                    <TableCell className="text-xs">
                                                        <span className="capitalize">{job.transport_mode}</span> &bull; <span className="capitalize">{job.service_type}</span>
                                                    </TableCell>
                                                    <TableCell className="font-mono text-xs">
                                                        {job.shipment?.bl_awb_number || 'Pending'}
                                                    </TableCell>
                                                    <TableCell className="text-xs">
                                                        {job.shipment?.port_of_discharge || 'N/A'}
                                                    </TableCell>
                                                    <TableCell className="text-xs">
                                                        {job.shipment?.final_destination || 'N/A'}
                                                    </TableCell>
                                                    <TableCell>
                                                        {getStatusBadge(job.status)}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Link href={route('clearing-forwarding.jobs.show', job.id)}>
                                                            <Button size="sm" variant="outline" className="h-7 text-xs">
                                                                View Job
                                                            </Button>
                                                        </Link>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 2: CUSTOMS & TANCIS AUDIT */}
                    <TabsContent value="customs">
                        <Card className="border shadow-sm">
                            <CardHeader className="p-4 pb-2">
                                <CardTitle className="text-sm font-semibold">TRA/TANCIS Declarations & Dual Verification Audit</CardTitle>
                                <CardDescription className="text-xs">
                                    Audited customs lodgements, tax assessments, GePG control numbers, and Finance / Release sign-offs.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50 dark:bg-slate-900">
                                            <TableHead className="text-xs">Job Number</TableHead>
                                            <TableHead className="text-xs">TANCIS Ref</TableHead>
                                            <TableHead className="text-xs">Customs Office</TableHead>
                                            <TableHead className="text-xs text-right">Estimated Tax (TZS)</TableHead>
                                            <TableHead className="text-xs text-right">Assessed Tax (TZS)</TableHead>
                                            <TableHead className="text-xs">GePG Control No</TableHead>
                                            <TableHead className="text-xs text-center">Finance Verified</TableHead>
                                            <TableHead className="text-xs text-center">Release Verified</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {jobs.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={8} className="text-center py-6 text-xs text-muted-foreground">
                                                    No declaration audit records found.
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            jobs.map((job) => {
                                                const decl = job.declarations?.[0];
                                                return (
                                                    <TableRow key={job.id} className="hover:bg-slate-50/50">
                                                        <TableCell className="font-mono text-xs font-semibold">
                                                            <Link href={route('clearing-forwarding.jobs.show', job.id)} className="text-indigo-600 hover:underline">
                                                                {job.job_number}
                                                            </Link>
                                                        </TableCell>
                                                        <TableCell className="font-mono text-xs font-semibold">
                                                            {decl?.tancis_reference || decl?.declaration_number || <span className="text-slate-400">Not Lodged</span>}
                                                        </TableCell>
                                                        <TableCell className="text-xs">
                                                            {decl?.customs_office || job.shipment?.port_of_discharge || 'Dar es Salaam Port'}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono text-muted-foreground">
                                                            {formatMoney(decl?.total_tax_tzs || 0)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-semibold text-slate-900 dark:text-slate-100">
                                                            {decl?.assessed_amount_tzs ? formatMoney(decl.assessed_amount_tzs) : <span className="text-slate-400">Pending</span>}
                                                        </TableCell>
                                                        <TableCell className="font-mono text-xs">
                                                            {decl?.payment_control_number || <span className="text-slate-400">None</span>}
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            {decl?.is_assessment_verified ? (
                                                                <Badge className="bg-emerald-100 text-emerald-800 text-[10px] gap-1">
                                                                    <Check className="w-3 h-3" /> Verified
                                                                </Badge>
                                                            ) : (
                                                                <Badge variant="outline" className="text-[10px] text-slate-500">
                                                                    Pending
                                                                </Badge>
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            {decl?.is_release_verified ? (
                                                                <Badge className="bg-indigo-100 text-indigo-800 text-[10px] gap-1">
                                                                    <Check className="w-3 h-3" /> Released
                                                                </Badge>
                                                            ) : (
                                                                <Badge variant="outline" className="text-[10px] text-slate-500">
                                                                    Unverified
                                                                </Badge>
                                                            )}
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 3: CONTAINER DEMURRAGE AUDIT */}
                    <TabsContent value="containers">
                        <Card className="border shadow-sm">
                            <CardHeader className="p-4 pb-2">
                                <CardTitle className="text-sm font-semibold">Container Free-Time & Demurrage Liability</CardTitle>
                                <CardDescription className="text-xs">
                                    Track container discharge, free days expiration, and incurred demurrage penalties.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50 dark:bg-slate-900">
                                            <TableHead className="text-xs">Container No</TableHead>
                                            <TableHead className="text-xs">Job Ref</TableHead>
                                            <TableHead className="text-xs">Size & Line</TableHead>
                                            <TableHead className="text-xs">Discharge Date</TableHead>
                                            <TableHead className="text-xs">Free Days</TableHead>
                                            <TableHead className="text-xs">Return Deadline</TableHead>
                                            <TableHead className="text-xs">Status</TableHead>
                                            <TableHead className="text-xs text-right">Incurred Demurrage</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {jobs.filter((j) => j.containers && j.containers.length > 0).length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={8} className="text-center py-6 text-xs text-muted-foreground">
                                                    No containerized cargo recorded under current filters.
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            jobs.flatMap((j) =>
                                                (j.containers || []).map((c) => {
                                                    const isOverdue = c.empty_return_deadline && !c.empty_returned_at && new Date(c.empty_return_deadline) < new Date();
                                                    return (
                                                        <TableRow key={c.id} className="hover:bg-slate-50/50">
                                                            <TableCell className="font-mono text-xs font-bold text-slate-900 dark:text-slate-100">
                                                                {c.container_number}
                                                            </TableCell>
                                                            <TableCell className="font-mono text-xs">
                                                                <Link href={route('clearing-forwarding.jobs.show', j.id)} className="text-indigo-600 hover:underline">
                                                                    {j.job_number}
                                                                </Link>
                                                            </TableCell>
                                                            <TableCell className="text-xs">
                                                                {c.size_type} &bull; {c.shipping_line || 'N/A'}
                                                            </TableCell>
                                                            <TableCell className="text-xs">
                                                                {c.discharge_date ? new Date(c.discharge_date).toLocaleDateString() : 'Pending'}
                                                            </TableCell>
                                                            <TableCell className="text-xs font-semibold">
                                                                {c.free_days} days
                                                            </TableCell>
                                                            <TableCell className="text-xs">
                                                                {c.empty_return_deadline || 'N/A'}
                                                            </TableCell>
                                                            <TableCell>
                                                                {c.empty_returned_at ? (
                                                                    <Badge className="bg-emerald-100 text-emerald-800 text-[10px]">Returned</Badge>
                                                                ) : isOverdue ? (
                                                                    <Badge variant="destructive" className="text-[10px] gap-1">
                                                                        <AlertTriangle className="w-3 h-3" /> Overdue
                                                                    </Badge>
                                                                ) : (
                                                                    <Badge className="bg-blue-100 text-blue-800 text-[10px]">Active</Badge>
                                                                )}
                                                            </TableCell>
                                                            <TableCell className="text-xs text-right font-mono font-semibold text-rose-700 dark:text-rose-400">
                                                                {formatMoney(c.demurrage_amount_tzs || 0)}
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })
                                            )
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 4: FINANCIAL PROFITABILITY */}
                    <TabsContent value="financials">
                        <Card className="border shadow-sm">
                            <CardHeader className="p-4 pb-2">
                                <CardTitle className="text-sm font-semibold">Job Costing, Disbursements & Margin Ledger</CardTitle>
                                <CardDescription className="text-xs">
                                    Audited revenue, client advance recoveries, direct disbursement costs, and gross margins.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50 dark:bg-slate-900">
                                            <TableHead className="text-xs">Job Number</TableHead>
                                            <TableHead className="text-xs">Customer</TableHead>
                                            <TableHead className="text-xs text-right">Advance Received</TableHead>
                                            <TableHead className="text-xs text-right">Disbursements & Costs</TableHead>
                                            <TableHead className="text-xs text-right">Service Revenue</TableHead>
                                            <TableHead className="text-xs text-right">Gross Profit</TableHead>
                                            <TableHead className="text-xs text-center">Reconciled</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {jobs.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={7} className="text-center py-6 text-xs text-muted-foreground">
                                                    No financial records found.
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            jobs.map((job) => {
                                                const costing = job.job_costing || job.jobCosting;
                                                const isReconciled = costing?.is_financially_reconciled;
                                                return (
                                                    <TableRow key={job.id} className="hover:bg-slate-50/50">
                                                        <TableCell className="font-mono text-xs font-semibold">
                                                            <Link href={route('clearing-forwarding.jobs.show', job.id)} className="text-indigo-600 hover:underline">
                                                                {job.job_number}
                                                            </Link>
                                                        </TableCell>
                                                        <TableCell className="text-xs font-medium">
                                                            {job.customer?.name || 'N/A'}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono text-blue-700 dark:text-blue-400">
                                                            {formatMoney(costing?.customer_advance_received || 0)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono text-rose-700 dark:text-rose-400">
                                                            {formatMoney(costing?.total_disbursements_and_direct_costs || 0)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                                            {formatMoney(costing?.total_service_revenue || 0)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                                            {formatMoney(costing?.gross_profit || 0)}
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            {isReconciled ? (
                                                                <Badge className="bg-emerald-100 text-emerald-800 text-[10px] gap-1">
                                                                    <Check className="w-3 h-3" /> Reconciled
                                                                </Badge>
                                                            ) : (
                                                                <Badge variant="outline" className="text-[10px] text-amber-700 border-amber-300">
                                                                    Open
                                                                </Badge>
                                                            )}
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </AuthenticatedLayout>
    );
}

