import React from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Container, Clock, AlertTriangle, ArrowLeft, CheckCircle2 } from 'lucide-react';
import { CFContainer } from '../types';

interface ContainersIndexProps {
    containers: {
        data: CFContainer[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}

export default function Index({ containers }: ContainersIndexProps) {
    const calculateDaysLeft = (deadline?: string | null, returnedAt?: string | null) => {
        if (returnedAt) return <Badge className="bg-emerald-100 text-emerald-800 text-[10px]">Returned</Badge>;
        if (!deadline) return <span className="text-slate-400 text-xs">No deadline</span>;

        const diff = Math.ceil((new Date(deadline).getTime() - new Date().getTime()) / (1000 * 3600 * 24));
        if (diff < 0) {
            return (
                <Badge variant="destructive" className="text-[10px] gap-1">
                    <AlertTriangle className="w-3 h-3" /> Overdue by {Math.abs(diff)}d
                </Badge>
            );
        } else if (diff <= 3) {
            return (
                <Badge className="bg-rose-100 text-rose-800 border-rose-300 text-[10px] gap-1">
                    <AlertTriangle className="w-3 h-3" /> {diff}d left (Critical)
                </Badge>
            );
        } else if (diff <= 7) {
            return (
                <Badge className="bg-amber-100 text-amber-800 border-amber-300 text-[10px] gap-1">
                    <Clock className="w-3 h-3" /> {diff}d left (Warning)
                </Badge>
            );
        }
        return (
            <Badge className="bg-blue-50 text-blue-800 border-blue-200 text-[10px]">
                {diff}d free time
            </Badge>
        );
    };

    return (
        <AuthenticatedLayout>
            <Head title="Container Demurrage Tracker - Clearing & Forwarding" />

            <div className="p-6 space-y-6">
                <div className="flex items-center justify-between border-b pb-4">
                    <div className="flex items-center gap-3">
                        <Link href={route('clearing-forwarding.dashboard')}>
                            <Button variant="ghost" size="icon" className="h-9 w-9">
                                <ArrowLeft className="w-5 h-5 text-slate-600" />
                            </Button>
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <Container className="w-6 h-6 text-amber-600" />
                                <h1 className="text-xl font-bold text-slate-900">
                                    Container Demurrage & Return Tracker
                                </h1>
                            </div>
                            <p className="text-xs text-slate-500">
                                Monitor container free time countdown, shipping line detention risk, gate-out status, and empty depot returns.
                            </p>
                        </div>
                    </div>
                </div>

                <Card className="border-slate-200 shadow-sm overflow-hidden">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-slate-50">
                                    <TableHead className="text-xs font-semibold">Container Number</TableHead>
                                    <TableHead className="text-xs font-semibold">Size & Type</TableHead>
                                    <TableHead className="text-xs font-semibold">Shipping Line</TableHead>
                                    <TableHead className="text-xs font-semibold">Job File / Customer</TableHead>
                                    <TableHead className="text-xs font-semibold">Gate-Out Date</TableHead>
                                    <TableHead className="text-xs font-semibold">Free Days</TableHead>
                                    <TableHead className="text-xs font-semibold">Demurrage Countdown</TableHead>
                                    <TableHead className="text-xs font-semibold">Status</TableHead>
                                    <TableHead className="text-xs font-semibold text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {containers.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={9} className="text-center py-10 text-slate-500 text-xs">
                                            No containers currently registered in the system.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    containers.data.map((c) => (
                                        <TableRow key={c.id} className="hover:bg-slate-50/70">
                                            <TableCell className="font-mono font-bold text-xs text-slate-900">
                                                {c.container_number}
                                                {c.seal_number && (
                                                    <div className="text-[10px] text-slate-400 font-normal">Seal: {c.seal_number}</div>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-xs">{c.size_type}</TableCell>
                                            <TableCell className="text-xs font-medium text-slate-700">{c.shipping_line || 'Shipping Line'}</TableCell>
                                            <TableCell>
                                                <Link
                                                    href={route('clearing-forwarding.jobs.show', c.cf_job_id)}
                                                    className="font-mono text-xs font-semibold text-indigo-700 hover:underline block"
                                                >
                                                    {c.job?.job_number}
                                                </Link>
                                                <div className="text-[11px] text-slate-500 truncate max-w-xs">{c.job?.customer?.name}</div>
                                            </TableCell>
                                            <TableCell className="text-xs">{c.gate_out_date || 'At Terminal'}</TableCell>
                                            <TableCell className="text-xs font-semibold">{c.free_days} days</TableCell>
                                            <TableCell>{calculateDaysLeft(c.empty_return_deadline, c.empty_returned_at)}</TableCell>
                                            <TableCell className="text-xs capitalize">
                                                <Badge variant="outline" className="text-[10px] capitalize">
                                                    {c.status.replace('_', ' ')}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link href={route('clearing-forwarding.jobs.show', c.cf_job_id)}>
                                                    <Button size="sm" variant="outline" className="h-7 text-xs">
                                                        Open Job
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
            </div>
        </AuthenticatedLayout>
    );
}

