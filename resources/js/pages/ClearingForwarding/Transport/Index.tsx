import React from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Truck, ArrowLeft, MapPin } from 'lucide-react';
import { CFTransportOrder } from '../types';

interface TransportIndexProps {
    orders: {
        data: CFTransportOrder[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
}

export default function Index({ orders }: TransportIndexProps) {
    const formatMoney = (val: number | string) => {
        return 'TZS ' + Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Haulage & Transport Orders - Clearing & Forwarding" />

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
                                <Truck className="w-6 h-6 text-blue-600" />
                                <h1 className="text-xl font-bold text-slate-900">
                                    Haulage & Transport Orders
                                </h1>
                            </div>
                            <p className="text-xs text-slate-500">
                                Assigned vehicles, dispatch orders, in-transit corridor tracking, and destination deliveries.
                            </p>
                        </div>
                    </div>
                </div>

                <Card className="border-slate-200 shadow-sm overflow-hidden">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-slate-50">
                                    <TableHead className="text-xs font-semibold">Order No</TableHead>
                                    <TableHead className="text-xs font-semibold">Truck Plate</TableHead>
                                    <TableHead className="text-xs font-semibold">Driver Particulars</TableHead>
                                    <TableHead className="text-xs font-semibold">Carrier / Transporter</TableHead>
                                    <TableHead className="text-xs font-semibold">Route & Corridor</TableHead>
                                    <TableHead className="text-xs font-semibold">Job File / Customer</TableHead>
                                    <TableHead className="text-xs font-semibold text-right">Freight Cost</TableHead>
                                    <TableHead className="text-xs font-semibold">Status</TableHead>
                                    <TableHead className="text-xs font-semibold text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {orders.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={9} className="text-center py-10 text-slate-500 text-xs">
                                            No transport orders issued yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    orders.data.map((to) => (
                                        <TableRow key={to.id} className="hover:bg-slate-50/70">
                                            <TableCell className="font-mono font-bold text-xs text-slate-900">
                                                {to.order_number}
                                                <div className="text-[10px] text-slate-400 font-normal">
                                                    {to.dispatch_date ? new Date(to.dispatch_date).toLocaleDateString() : 'Draft'}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-xs font-bold font-mono text-slate-900">
                                                {to.vehicle_plate}
                                            </TableCell>
                                            <TableCell className="text-xs">
                                                <div className="font-semibold text-slate-800">{to.driver_name}</div>
                                                <div className="text-[10px] text-slate-500">{to.driver_phone}</div>
                                            </TableCell>
                                            <TableCell className="text-xs text-slate-700">{to.carrier_name}</TableCell>
                                            <TableCell className="text-xs">
                                                <div>{to.pickup_location}</div>
                                                <div className="text-[11px] text-slate-500">&rarr; {to.delivery_destination}</div>
                                                {to.route_corridor && (
                                                    <div className="text-[10px] text-indigo-600 font-medium">{to.route_corridor}</div>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <Link
                                                    href={route('clearing-forwarding.jobs.show', to.cf_job_id)}
                                                    className="font-mono text-xs font-semibold text-indigo-700 hover:underline block"
                                                >
                                                    {to.job?.job_number}
                                                </Link>
                                                <div className="text-[11px] text-slate-500 truncate max-w-xs">{to.job?.customer?.name}</div>
                                            </TableCell>
                                            <TableCell className="text-xs text-right font-semibold text-slate-900">
                                                {formatMoney(to.agreed_freight_cost)}
                                            </TableCell>
                                            <TableCell className="text-xs capitalize">
                                                <Badge variant="outline" className="text-[10px] capitalize">
                                                    {to.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Link href={route('clearing-forwarding.jobs.show', to.cf_job_id)}>
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

