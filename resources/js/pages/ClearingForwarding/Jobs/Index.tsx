import React, { useState } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    Ship,
    Truck,
    Search,
    PlusCircle,
    Eye,
    Printer,
    FileText,
    Filter,
    RotateCcw,
    Anchor
} from 'lucide-react';
import { CFJob } from '../types';

interface JobsIndexProps {
    jobs: {
        data: CFJob[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
        service_type?: string;
        customs_regime?: string;
        transport_mode?: string;
    };
}

export default function Index({ jobs, filters }: JobsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [serviceType, setServiceType] = useState(filters.service_type || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('clearing-forwarding.jobs.index'),
            { search, status, service_type: serviceType },
            { preserveState: true, replace: true }
        );
    };

    const handleReset = () => {
        setSearch('');
        setStatus('');
        setServiceType('');
        router.get(route('clearing-forwarding.jobs.index'));
    };

    const getStatusBadge = (jobStatus: string) => {
        switch (jobStatus) {
            case 'cargo_released':
            case 'completed':
            case 'delivered':
                return <Badge className="bg-emerald-100 text-emerald-800 border-emerald-300 capitalize">{jobStatus.replace('_', ' ')}</Badge>;
            case 'in_transit':
                return <Badge className="bg-blue-100 text-blue-800 border-blue-300 capitalize">In Transit</Badge>;
            case 'declaration_submitted':
            case 'assessed':
                return <Badge className="bg-purple-100 text-purple-800 border-purple-300 capitalize">{jobStatus.replace('_', ' ')}</Badge>;
            case 'compliance_check':
            case 'documents_pending':
                return <Badge className="bg-amber-100 text-amber-800 border-amber-300 capitalize">{jobStatus.replace('_', ' ')}</Badge>;
            case 'cancelled':
                return <Badge variant="destructive" className="capitalize">Cancelled</Badge>;
            default:
                return <Badge variant="outline" className="capitalize">{jobStatus.replace('_', ' ')}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Shipment & Job Register - Clearing & Forwarding" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-5">
                    <div>
                        <div className="flex items-center gap-2">
                            <Anchor className="w-6 h-6 text-[#1e3a5f]" />
                            <h1 className="text-2xl font-bold tracking-tight text-slate-900">
                                Shipment & Job Register
                            </h1>
                        </div>
                        <p className="text-sm text-slate-500 mt-1">
                            Centralized register of all import, export, and transit forwarding assignments. Total: {jobs.total} files.
                        </p>
                    </div>

                    <Link href={route('clearing-forwarding.jobs.create')}>
                        <Button className="bg-[#1e3a5f] hover:bg-[#152a45] text-white gap-1.5 shadow-sm">
                            <PlusCircle className="w-4 h-4" /> Open New Job File
                        </Button>
                    </Link>
                </div>

                {/* Filter and Search Bar */}
                <Card className="border-slate-200 shadow-sm">
                    <CardContent className="p-4">
                        <form onSubmit={handleSearch} className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <label className="text-xs font-semibold text-slate-600 block mb-1">Search Keywords</label>
                                <div className="relative">
                                    <Search className="w-4 h-4 absolute left-2.5 top-2.5 text-slate-400" />
                                    <Input
                                        placeholder="Job no, BL, container, consignee..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-8 text-xs"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="text-xs font-semibold text-slate-600 block mb-1">Service Type</label>
                                <select
                                    value={serviceType}
                                    onChange={(e) => setServiceType(e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white"
                                >
                                    <option value="">All Services (Import/Export/Transit)</option>
                                    <option value="import">Import Clearance</option>
                                    <option value="export">Export Forwarding</option>
                                    <option value="transit">Transit Cargo (EAC Corridors)</option>
                                    <option value="domestic">Domestic Freight</option>
                                </select>
                            </div>

                            <div>
                                <label className="text-xs font-semibold text-slate-600 block mb-1">Status</label>
                                <select
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="documents_pending">Documents Pending</option>
                                    <option value="compliance_check">Compliance Check</option>
                                    <option value="declaration_submitted">Declaration Submitted</option>
                                    <option value="assessed">Customs Assessed</option>
                                    <option value="cargo_released">Cargo Released at Port</option>
                                    <option value="in_transit">In Transit Haulage</option>
                                    <option value="delivered">Delivered (POD Archived)</option>
                                    <option value="completed">Completed & Closed</option>
                                </select>
                            </div>

                            <div className="flex gap-2">
                                <Button type="submit" size="sm" className="bg-slate-800 hover:bg-slate-900 text-white gap-1 flex-1">
                                    <Filter className="w-3.5 h-3.5" /> Filter
                                </Button>
                                <Button type="button" size="sm" variant="outline" onClick={handleReset} className="gap-1">
                                    <RotateCcw className="w-3.5 h-3.5" /> Reset
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Jobs Table */}
                <Card className="border-slate-200 shadow-sm overflow-hidden">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-slate-50">
                                    <TableHead className="font-semibold text-xs text-slate-700">Job No / Date</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Client / Consignee</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Service & Regime</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Transport & BL/AWB</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Containers</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700">Status</TableHead>
                                    <TableHead className="font-semibold text-xs text-slate-700 text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {jobs.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={7} className="text-center py-10 text-slate-500">
                                            No shipment jobs found matching the selected criteria.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    jobs.data.map((job) => (
                                        <TableRow key={job.id} className="hover:bg-slate-50/80">
                                            <TableCell>
                                                <Link
                                                    href={route('clearing-forwarding.jobs.show', job.id)}
                                                    className="font-mono font-bold text-indigo-700 hover:underline text-sm block"
                                                >
                                                    {job.job_number}
                                                </Link>
                                                <div className="text-[11px] text-slate-500">
                                                    Opened: {new Date(job.created_at).toLocaleDateString()}
                                                </div>
                                                {job.priority === 'urgent' && (
                                                    <span className="text-[10px] bg-amber-100 text-amber-800 font-semibold px-1.5 py-0.2 rounded">Urgent</span>
                                                )}
                                                {job.priority === 'critical' && (
                                                    <span className="text-[10px] bg-rose-100 text-rose-800 font-semibold px-1.5 py-0.2 rounded">Critical</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-semibold text-xs text-slate-900">{job.customer?.name}</div>
                                                <div className="text-[11px] text-slate-600 truncate max-w-xs">
                                                    To: {job.consignee_name || 'Designated Consignee'}
                                                </div>
                                                {job.customer_reference && (
                                                    <div className="text-[10px] text-slate-400 font-mono">Ref: {job.customer_reference}</div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <div className="capitalize text-xs font-medium text-slate-800 flex items-center gap-1.5">
                                                    {job.transport_mode === 'sea' ? (
                                                        <Ship className="w-3.5 h-3.5 text-blue-600" />
                                                    ) : (
                                                        <Truck className="w-3.5 h-3.5 text-slate-600" />
                                                    )}
                                                    <span>{job.service_type}</span>
                                                </div>
                                                <div className="text-[11px] text-slate-500 capitalize">
                                                    {job.customs_regime.replace('_', ' ')}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-mono text-xs font-semibold text-slate-800">
                                                    {job.shipment?.bl_awb_number || 'Awaiting BL'}
                                                </div>
                                                <div className="text-[11px] text-slate-500 truncate max-w-xs">
                                                    {job.shipment?.vessel_flight_truck || job.shipment?.port_of_discharge}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {job.containers && job.containers.length > 0 ? (
                                                    <div className="space-y-0.5">
                                                        <span className="font-mono text-xs font-bold text-slate-900">
                                                            {job.containers[0].container_number}
                                                        </span>
                                                        <div className="text-[10px] text-slate-500">
                                                            {job.containers[0].size_type} &bull; {job.containers.length} unit(s)
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <span className="text-xs text-slate-400">Non-containerized / General</span>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                {getStatusBadge(job.status)}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <Link href={route('clearing-forwarding.jobs.show', job.id)}>
                                                        <Button size="sm" variant="outline" className="h-7 text-xs gap-1">
                                                            <Eye className="w-3.5 h-3.5" /> File Hub
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

                {/* Pagination */}
                {jobs.last_page > 1 && (
                    <div className="flex items-center justify-center gap-1.5 pt-2">
                        {jobs.links.map((link, idx) => (
                            <Link
                                key={idx}
                                href={link.url || '#'}
                                className={`px-3 py-1.5 text-xs rounded border ${
                                    link.active
                                        ? 'bg-[#1e3a5f] text-white border-[#1e3a5f]'
                                        : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'
                                } ${!link.url ? 'opacity-40 pointer-events-none' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

