import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { Plus, ShieldCheck, Truck } from 'lucide-react';

const CORRIDORS = [
    'Tanzania - Zambia (Tunduma / Nakonde)',
    'Tanzania - Kenya (Namanga / Horohoro / Holili)',
    'Tanzania - Uganda (Mutukula)',
    'Tanzania - Rwanda (Rusumo)',
    'Tanzania - Burundi (Kabanga / Manyovu)',
    'Tanzania - DR Congo (Tunduma / Kasumbalesa / Kigoma)',
];

export default function BorderIndex({ movements = [], jobs = [] }: { movements: any[]; jobs: any[] }) {
    const { t } = useTranslation();
    const [openCreate, setOpenCreate] = useState(false);

    const form = useForm({
        cf_job_id: 'none',
        corridor: CORRIDORS[0],
        truck_registration: '',
        trailer_number: '',
        driver_name: '',
        cargo_type: '',
        movement_type: 'transit',
        origin_country: 'Tanzania',
        destination_country: 'Zambia',
        border_entry_at: new Date().toISOString().slice(0, 16),
        customs_reference: '',
        rotation_number: '',
        transit_doc_number: '',
        gate_pass_number: '',
        border_expenses: '0',
        currency: 'TZS',
        delay_reason: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({
            ...data,
            cf_job_id: data.cf_job_id === 'none' ? null : Number(data.cf_job_id),
        }));
        form.post(route('clearing-forwarding.border.store'), {
            onSuccess: () => {
                setOpenCreate(false);
                form.reset();
            },
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Clearing & Forwarding'), url: route('clearing-forwarding.dashboard') },
                { label: t('Border & Transit Control') },
            ]}
            pageTitle={t('Border & Transit Control')}
        >
            <Head title={t('Border & Transit Control')} />

            <div className="space-y-6">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="flex items-center gap-2 text-base">
                            <Truck className="h-5 w-5 text-indigo-600" />
                            <span>{t('Cross-Border & Transit Movements (Tunduma/Nakonde, Namanga, Mutukula, Rusumo)')}</span>
                        </CardTitle>
                        <Button size="sm" onClick={() => setOpenCreate(true)}>
                            <Plus className="h-4 w-4 mr-1" /> {t('Record Border Movement')}
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b text-left text-muted-foreground">
                                        <th className="py-2 pr-3">{t('Movement No')}</th>
                                        <th className="py-2 pr-3">{t('Corridor')}</th>
                                        <th className="py-2 pr-3">{t('Truck / Trailer')}</th>
                                        <th className="py-2 pr-3">{t('Driver')}</th>
                                        <th className="py-2 pr-3">{t('C.32 / Transit Doc')}</th>
                                        <th className="py-2 pr-3">{t('Border Expenses')}</th>
                                        <th className="py-2 pr-3">{t('Status')}</th>
                                        <th className="py-2 text-right">{t('Update')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {movements.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="py-8 text-center text-muted-foreground">
                                                {t('No border movements recorded yet.')}
                                            </td>
                                        </tr>
                                    ) : movements.map((m) => (
                                        <tr key={m.id} className="border-b">
                                            <td className="py-2.5 pr-3 font-mono font-semibold text-xs">
                                                {m.movement_number}
                                                {m.job && <div className="text-[11px] text-muted-foreground">{m.job.job_number}</div>}
                                            </td>
                                            <td className="py-2.5 pr-3">{m.corridor}</td>
                                            <td className="py-2.5 pr-3">{m.truck_registration} {m.trailer_number ? `/ ${m.trailer_number}` : ''}</td>
                                            <td className="py-2.5 pr-3">{m.driver_name || '-'}</td>
                                            <td className="py-2.5 pr-3 font-mono text-xs">{m.transit_doc_number || m.rotation_number || '-'}</td>
                                            <td className="py-2.5 pr-3">{formatCurrency(m.border_expenses || 0)}</td>
                                            <td className="py-2.5 pr-3">
                                                <Badge variant={m.clearance_status === 'released' || m.clearance_status === 'exited' ? 'secondary' : m.clearance_status === 'held' ? 'destructive' : 'default'}>
                                                    {m.clearance_status.replace(/_/g, ' ').toUpperCase()}
                                                </Badge>
                                            </td>
                                            <td className="py-2.5 text-right">
                                                <Select
                                                    value={m.clearance_status}
                                                    onValueChange={(val) => router.patch(route('clearing-forwarding.border.status', m.id), { clearance_status: val })}
                                                >
                                                    <SelectTrigger className="h-8 w-36 text-xs">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="arrived">{t('Arrived')}</SelectItem>
                                                        <SelectItem value="customs_processing">{t('Customs Processing')}</SelectItem>
                                                        <SelectItem value="held">{t('Held')}</SelectItem>
                                                        <SelectItem value="released">{t('Released')}</SelectItem>
                                                        <SelectItem value="exited">{t('Exited Border')}</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={openCreate} onOpenChange={setOpenCreate}>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <ShieldCheck className="h-5 w-5 text-indigo-600" />
                            {t('Record Border Crossing / Transit Movement')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label>{t('Linked Shipment (Optional)')}</Label>
                                <Select value={form.data.cf_job_id} onValueChange={(v) => form.setData('cf_job_id', v)}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">{t('-- Standalone Movement --')}</SelectItem>
                                        {jobs.map((j) => <SelectItem key={j.id} value={String(j.id)}>{j.job_number} - {j.consignee_name || j.service_type}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Border Corridor')} *</Label>
                                <Select value={form.data.corridor} onValueChange={(v) => form.setData('corridor', v)}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        {CORRIDORS.map((c) => <SelectItem key={c} value={c}>{c}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Truck Registration')} *</Label>
                                <Input required value={form.data.truck_registration} onChange={(e) => form.setData('truck_registration', e.target.value)} placeholder="e.g. T 456 ABC" />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Trailer Number')}</Label>
                                <Input value={form.data.trailer_number} onChange={(e) => form.setData('trailer_number', e.target.value)} placeholder="e.g. T 789 XYZ" />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Driver Name')}</Label>
                                <Input value={form.data.driver_name} onChange={(e) => form.setData('driver_name', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Movement Type')} *</Label>
                                <Select value={form.data.movement_type} onValueChange={(v) => form.setData('movement_type', v)}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="transit">{t('Transit')}</SelectItem>
                                        <SelectItem value="import">{t('Import')}</SelectItem>
                                        <SelectItem value="export">{t('Export')}</SelectItem>
                                        <SelectItem value="empty_return">{t('Empty Truck Return')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label>{t('C.32 / Transit Doc No.')}</Label>
                                <Input value={form.data.transit_doc_number} onChange={(e) => form.setData('transit_doc_number', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Rotation / Gate Pass No.')}</Label>
                                <Input value={form.data.rotation_number} onChange={(e) => form.setData('rotation_number', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Border Expenses (TZS)')}</Label>
                                <Input type="number" min="0" value={form.data.border_expenses} onChange={(e) => form.setData('border_expenses', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Border Entry Date/Time')}</Label>
                                <Input type="datetime-local" value={form.data.border_entry_at} onChange={(e) => form.setData('border_entry_at', e.target.value)} />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setOpenCreate(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={form.processing}>{t('Save Movement')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}

