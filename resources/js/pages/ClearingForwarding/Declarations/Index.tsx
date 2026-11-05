import React from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ShieldCheck, ArrowLeft, FileText, CheckCircle2 } from 'lucide-react';
import { CFDeclaration } from '../types';

interface DeclarationsIndexProps {
    declarations: {
        data: CFDeclaration[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}

export default function Index({ declarations }: DeclarationsIndexProps) {
    const formatMoney = (val: number | string) => {
        return 'TZS ' + Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Customs Declarations & TANCIS Workspace" />

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
                                <ShieldCheck className="w-6 h-6 text-purple-600" />
                                <h1 className="text-xl font-bold text-slate-900">
                                    Customs & TANCIS Declaration Register
                                </h1>
                            </div>
                            <p className="text-xs text-slate-500">
                                Track Tanzania Revenue Authority (TRA) assessment notices, GePG control numbers, and release orders.
                            </p>
                        </div>
                    </div>
                </div>

                <Card className="border-slate-200 shadow-sm overflow-hidden">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-slate-50">
                                    <TableHead className="text-xs font-semibold">TANSAD / Decl No</TableHead>
                                    <TableHead className="text-xs font-semibold">TANCIS Ref</TableHead>
                                    <TableHead className="text-xs font-semibold">Customs Office</TableHead>
                                    <TableHead className="text-xs font-semibold">Job File / Customer</TableHead>
                                    <TableHead className="text-xs font-semibold text-right">Assessed Duty</TableHead>
                                    <TableHead className="text-xs font-semibold">Control Number</TableHead>
                                    <TableHead className="text-xs font-semibold">Release Order</TableHead>
                                    <TableHead className="text-xs font-semibold">Status</TableHead>
                                    <TableHead className="text-xs font-semibold text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {declarations.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={9} className="text-center py-10 text-slate-500 text-xs">
                                            No declarations registered yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    declarations.data.map((d) => (
                                        <TableRow key={d.id} className="hover:bg-slate-50/70">
                                            <TableCell className="font-mono font-bold text-xs text-slate-900">
                                                {d.declaration_number || 'Under Prep'}
                                                <div className="text-[10px] text-slate-400 font-normal">{d.declaration_type}</div>
                                            </TableCell>
                                            <TableCell className="text-xs font-mono">{d.tancis_reference || '-'}</TableCell>
                                            <TableCell className="text-xs font-medium text-slate-700">{d.customs_office}</TableCell>
                                            <TableCell>
                                                <Link
                                                    href={route('clearing-forwarding.jobs.show', d.cf_job_id)}
                                                    className="font-mono text-xs font-semibold text-indigo-700 hover:underline block"
                                                >
                                                    {d.job?.job_number}
                                                </Link>
                                                <div className="text-[11px] text-slate-500 truncate max-w-xs">{d.job?.customer?.name}</div>
                                            </TableCell>
                                            <TableCell className="text-xs text-right font-semibold text-indigo-900">
                                                {formatMoney(d.assessed_amount_tzs)}
                                            </TableCell>
                                            <TableCell className="text-xs font-mono font-medium">
                                                {d.payment_control_number ? (
                                                    <span className="bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                                        {d.payment_control_number}
                                                    </span>
                                                ) : (
                                                    '-'
                                                )}
                                            </TableCell>
                                            <TableCell className="text-xs font-mono text-emerald-800 font-semibold">
                                                {d.release_order_number || 'Awaiting Release'}
                                            </TableCell>
                                            <TableCell className="text-xs capitalize">
                                                <Badge variant="outline" className="text-[10px] capitalize">
                                                    {d.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link href={route('clearing-forwarding.jobs.show', d.cf_job_id)}>
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

