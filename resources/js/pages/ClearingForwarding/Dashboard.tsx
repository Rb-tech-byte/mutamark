import React from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    Ship,
    Truck,
    CheckCircle2,
    Clock,
    AlertTriangle,
    DollarSign,
    PlusCircle,
    FileText,
    ArrowUpRight,
    Container,
    ShieldAlert,
    Anchor,
    MapPin
} from 'lucide-react';
import { CFJob, CFContainer } from './types';

interface DashboardProps {
    metrics: {
        total_jobs: number;
        active_jobs: number;
        in_transit_jobs: number;
        released_jobs: number;
        pending_declarations: number;
        containers_at_risk_count: number;
        total_advances: number;
        total_disbursed: number;
        total_service_revenue: number;
        total_profit: number;
    };
    containersAtRisk: CFContainer[];
    recentJobs: CFJob[];
}

export default function Dashboard({ metrics, containersAtRisk, recentJobs }: DashboardProps) {
    const formatMoney = (val: number) => {
        return 'TZS ' + Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'cargo_released':
            case 'completed':
            case 'delivered':
                return <Badge className="bg-emerald-100 text-emerald-800 border-emerald-300 capitalize">{status.replace('_', ' ')}</Badge>;
            case 'in_transit':
                return <Badge className="bg-blue-100 text-blue-800 border-blue-300 capitalize">In Transit</Badge>;
            case 'declaration_submitted':
            case 'assessed':
                return <Badge className="bg-purple-100 text-purple-800 border-purple-300 capitalize">{status.replace('_', ' ')}</Badge>;
            case 'compliance_check':
            case 'documents_pending':
                return <Badge className="bg-amber-100 text-amber-800 border-amber-300 capitalize">{status.replace('_', ' ')}</Badge>;
            case 'cancelled':
                return <Badge variant="destructive" className="capitalize">Cancelled</Badge>;
            default:
                return <Badge variant="outline" className="capitalize">{status.replace('_', ' ')}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Clearing & Forwarding Dashboard" />

            <div className="p-6 space-y-6">
                {/* Header & Quick Action Buttons */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b pb-5">
                    <div>
                        <div className="flex items-center gap-2.5">
                            <div className="p-2 bg-indigo-50 border border-indigo-200 rounded-lg text-indigo-700">
                                <Ship className="w-6 h-6" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight text-slate-900">
                                    Clearing & Forwarding Operations
                                </h1>
                                <p className="text-sm text-slate-500">
                                    Module 43: Centralized cargo tracking, TANCIS customs pipeline, container demurrage & job costing.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-2.5 flex-wrap">
                        <Link href={route('clearing-forwarding.jobs.create')}>
                            <Button className="bg-[#1e3a5f] hover:bg-[#152a45] text-white gap-1.5 shadow-sm">
                                <PlusCircle className="w-4 h-4" /> Open New Job File
                            </Button>
                        </Link>
                        <Link href={route('clearing-forwarding.declarations.index')}>
                            <Button variant="outline" className="gap-1.5">
                                <FileText className="w-4 h-4 text-purple-600" /> TANCIS Declarations
                            </Button>
                        </Link>
                        <Link href={route('clearing-forwarding.containers.index')}>
                            <Button variant="outline" className="gap-1.5">
                                <Container className="w-4 h-4 text-amber-600" /> Demurrage Tracker
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Primary Metric KPI Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-slate-200 shadow-sm bg-gradient-to-br from-white to-slate-50/50">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Active Pipeline
                            </CardTitle>
                            <Anchor className="w-4 h-4 text-indigo-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-extrabold text-slate-900">{metrics.active_jobs}</div>
                            <p className="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                <span className="font-semibold text-slate-700">{metrics.total_jobs}</span> total lifetime files
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-slate-200 shadow-sm bg-gradient-to-br from-white to-emerald-50/30">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Released & Cleared
                            </CardTitle>
                            <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-extrabold text-emerald-700">{metrics.released_jobs}</div>
                            <p className="text-xs text-slate-500 mt-1">Ready for transport or gated out</p>
                        </CardContent>
                    </Card>

                    <Card className="border-slate-200 shadow-sm bg-gradient-to-br from-white to-blue-50/30">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                In-Transit Haulage
                            </CardTitle>
                            <Truck className="w-4 h-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-3xl font-extrabold text-blue-700">{metrics.in_transit_jobs}</div>
                            <p className="text-xs text-slate-500 mt-1">Active transit corridors & domestic runs</p>
                        </CardContent>
                    </Card>

                    <Card className={`border-slate-200 shadow-sm ${metrics.containers_at_risk_count > 0 ? 'bg-amber-50/50 border-amber-300' : 'bg-white'}`}>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Demurrage At Risk
                            </CardTitle>
                            <AlertTriangle className={`w-4 h-4 ${metrics.containers_at_risk_count > 0 ? 'text-amber-600 animate-pulse' : 'text-slate-400'}`} />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-3xl font-extrabold ${metrics.containers_at_risk_count > 0 ? 'text-amber-700' : 'text-slate-900'}`}>
                                {metrics.containers_at_risk_count}
                            </div>
                            <p className="text-xs text-slate-500 mt-1">Containers ≤ 5 days free time remaining</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Financial Overview Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-semibold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                <span>Customer Advances Pool</span>
                                <DollarSign className="w-4 h-4 text-indigo-600" />
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-indigo-900">{formatMoney(metrics.total_advances)}</div>
                            <p className="text-xs text-slate-500 mt-1">Deposited for statutory duties & port disbursement</p>
                        </CardContent>
                    </Card>

                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-semibold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                <span>Statutory & Third-Party Outflows</span>
                                <FileText className="w-4 h-4 text-slate-600" />
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-slate-800">{formatMoney(metrics.total_disbursed)}</div>
                            <p className="text-xs text-slate-500 mt-1">Paid to TRA, TPA, Shipping Lines & transporters</p>
                        </CardContent>
                    </Card>

                    <Card className="border-slate-200 shadow-sm bg-gradient-to-br from-white to-indigo-50/20">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-xs font-semibold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                <span>Agency Service Margin</span>
                                <ArrowUpRight className="w-4 h-4 text-emerald-600" />
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-700">{formatMoney(metrics.total_profit)}</div>
                            <p className="text-xs text-slate-500 mt-1">Recognized agency revenue net of direct freight</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Urgent Demurrage Countdown Table (If any) */}
                {containersAtRisk.length > 0 && (
                    <Card className="border-amber-300 shadow-sm overflow-hidden bg-amber-50/20">
                        <CardHeader className="bg-amber-100/60 pb-3 border-b border-amber-200">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <ShieldAlert className="w-5 h-5 text-amber-700" />
                                    <div>
                                        <CardTitle className="text-sm font-bold text-amber-950">
                                            Urgent Container Demurrage Alerts
                                        </CardTitle>
                                        <CardDescription className="text-xs text-amber-800">
                                            Shipping line empty container return deadlines requiring immediate attention to avoid penalties.
                                        </CardDescription>
                                    </div>
                                </div>
                                <Link href={route('clearing-forwarding.containers.index')}>
                                    <Button size="sm" variant="outline" className="border-amber-300 text-amber-900 bg-white">
                                        View Full Container Register
                                    </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-amber-50">
                                        <TableHead className="font-semibold text-xs text-amber-900">Container No</TableHead>
                                        <TableHead className="font-semibold text-xs text-amber-900">Size / Line</TableHead>
                                        <TableHead className="font-semibold text-xs text-amber-900">Job Reference</TableHead>
                                        <TableHead className="font-semibold text-xs text-amber-900">Customer</TableHead>
                                        <TableHead className="font-semibold text-xs text-amber-900">Return Deadline</TableHead>
                                        <TableHead className="font-semibold text-xs text-amber-900 text-right">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {containersAtRisk.map((c) => (
                                        <TableRow key={c.id} className="hover:bg-amber-100/40">
                                            <TableCell className="font-mono font-bold text-slate-900">
                                                {c.container_number}
                                            </TableCell>
                                            <TableCell className="text-xs">
                                                {c.size_type} &bull; {c.shipping_line || 'Shipping Line'}
                                            </TableCell>
                                            <TableCell>
                                                <Link
                                                    href={route('clearing-forwarding.jobs.show', c.cf_job_id)}
                                                    className="text-xs font-semibold text-indigo-600 hover:underline"
                                                >
                                                    {c.job?.job_number}
                                                </Link>
                                            </TableCell>
                                            <TableCell className="text-xs font-medium text-slate-700">
                                                {c.job?.customer?.name}
                                            </TableCell>
                                            <TableCell>
                                                <span className="inline-flex items-center gap-1 text-xs font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded">
                                                    <Clock className="w-3 h-3 text-amber-600" />
                                                    {c.empty_return_deadline}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link href={route('clearing-forwarding.jobs.show', c.cf_job_id)}>
                                                    <Button size="sm" variant="ghost" className="h-7 text-xs">
                                                        Open Job &rarr;
                                                    </Button>
                                                </Link>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}

                {/* Recent Shipment Jobs Table */}
                <Card className="border-slate-200 shadow-sm">
                    <CardHeader className="flex flex-row items-center justify-between pb-3 border-b">
                        <div>
                            <CardTitle className="text-base font-bold text-slate-900">
                                Recent Shipment Jobs
                            </CardTitle>
                            <CardDescription className="text-xs text-slate-500">
                                Latest import, export, and transit consignments under active processing.
                            </CardDescription>
                        </div>
                        <Link href={route('clearing-forwarding.jobs.index')}>
                            <Button variant="outline" size="sm" className="gap-1">
                                View All Jobs &rarr;
                            </Button>
                        </Link>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-slate-50">
                                    <TableHead className="font-semibold text-xs text-slate-700">Job No</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Customer / Consignee</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Service & Mode</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">BL / AWB & Vessel</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Status</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700 text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {recentJobs.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center py-8 text-slate-500 text-sm">
                                            No shipment jobs registered yet. Open your first job file above.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    recentJobs.map((j) => (
                                        <TableRow key={j.id} className="hover:bg-slate-50/80">
                                            <TableCell>
                                                <Link
                                                    href={route('clearing-forwarding.jobs.show', j.id)}
                                                    className="font-mono font-bold text-indigo-700 hover:underline text-sm"
                                                >
                                                    {j.job_number}
                                                </Link>
                                                <div className="text-[10px] text-slate-400">
                                                    {new Date(j.created_at).toLocaleDateString()}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-semibold text-xs text-slate-900">{j.customer?.name}</div>
                                                {j.consignee_name && (
                                                    <div className="text-[11px] text-slate-500 truncate max-w-xs">
                                                        Consignee: {j.consignee_name}
                                                    </div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1.5 text-xs font-medium text-slate-700 capitalize">
                                                    {j.transport_mode === 'sea' ? <Ship className="w-3.5 h-3.5 text-blue-600" /> : <Truck className="w-3.5 h-3.5 text-slate-600" />}
                                                    {j.service_type} &bull; {j.customs_regime.replace('_', ' ')}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-mono text-xs font-medium text-slate-800">
                                                    {j.shipment?.bl_awb_number || 'Pending BL'}
                                                </div>
                                                <div className="text-[11px] text-slate-500 truncate max-w-xs">
                                                    {j.shipment?.vessel_flight_truck || j.shipment?.port_of_discharge}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {getStatusBadge(j.status)}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <Link href={route('clearing-forwarding.jobs.show', j.id)}>
                                                        <Button size="sm" variant="outline" className="h-7 text-xs">
                                                            View File
                                                        </Button>
                                                    </Link>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}

