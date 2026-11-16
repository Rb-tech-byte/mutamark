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
import { Plus, Ship, Sliders } from 'lucide-react';

export default function FreightIndex({
    bookings = [],
    jobs = [],
    customsRules = [],
}: {
    bookings: any[];
    jobs: any[];
    customsRules: any[];
}) {
    const { t } = useTranslation();
    const [openBooking, setOpenBooking] = useState(false);
    const [openRule, setOpenRule] = useState(false);

    const bookingForm = useForm({
        cf_job_id: 'none',
        freight_mode: 'sea',
        shipping_line_carrier: '',
        vessel_flight_voyage: '',
        port_of_loading: '',
        port_of_discharge: 'Dar es Salaam Port',
        etd: '',
        eta: '',
        delivery_order_number: '',
        freight_cost: '0',
        port_handling_charges: '0',
        free_days: '14',
        demurrage_days: '0',
        demurrage_amount: '0',
        detention_amount: '0',
        currency: 'USD',
    });

    const ruleForm = useForm({
        cpc_code: '',
        procedure_name: '',
        country_code: 'TZ',
        import_duty_rate: '25',
        vat_rate: '18',
        excise_duty_rate: '0',
        rdl_rate: '1.5',
    });

    const submitBooking = (e: React.FormEvent) => {
        e.preventDefault();
        bookingForm.transform((d) => ({
            ...d,
            cf_job_id: d.cf_job_id === 'none' ? null : Number(d.cf_job_id),
        }));
        bookingForm.post(route('clearing-forwarding.freight.store'), {
            onSuccess: () => {
                setOpenBooking(false);
                bookingForm.reset();
            },
        });
    };

    const submitRule = (e: React.FormEvent) => {
        e.preventDefault();
        ruleForm.post(route('clearing-forwarding.customs-rules.store'), {
            onSuccess: () => {
                setOpenRule(false);
                ruleForm.reset();
            },
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Clearing & Forwarding'), url: route('clearing-forwarding.dashboard') },
                { label: t('Freight Bookings & Customs Rules') },
            ]}
            pageTitle={t('Freight Forwarding & Configurable Customs Rules')}
        >
            <Head title={t('Freight Forwarding & Customs Rules')} />

            <div className="space-y-6">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="flex items-center gap-2 text-base">
                            <Ship className="h-5 w-5 text-indigo-600" />
                            <span>{t('Freight Bookings, Delivery Orders & Demurrage')}</span>
                        </CardTitle>
                        <Button size="sm" onClick={() => setOpenBooking(true)}>
                            <Plus className="h-4 w-4 mr-1" /> {t('New Freight Booking')}
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b text-left text-muted-foreground">
                                        <th className="py-2 pr-3">{t('Booking No')}</th>
                                        <th className="py-2 pr-3">{t('Mode')}</th>
                                        <th className="py-2 pr-3">{t('Carrier / Vessel')}</th>
                                        <th className="py-2 pr-3">{t('Route')}</th>
                                        <th className="py-2 pr-3">{t('DO Number')}</th>
                                        <th className="py-2 pr-3">{t('Freight + Port')}</th>
                                        <th className="py-2 pr-3">{t('Demurrage / Det.')}</th>
                                        <th className="py-2 pr-3">{t('Status')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {bookings.length === 0 ? (
                                        <tr><td colSpan={8} className="py-8 text-center text-muted-foreground">{t('No freight bookings recorded yet.')}</td></tr>
                                    ) : bookings.map((b) => (
                                        <tr key={b.id} className="border-b">
                                            <td className="py-2.5 pr-3 font-mono font-semibold text-xs">
                                                {b.booking_number}
                                                {b.job && <div className="text-[11px] text-muted-foreground">{b.job.job_number}</div>}
                                            </td>
                                            <td className="py-2.5 pr-3 uppercase text-xs font-semibold">{b.freight_mode}</td>
                                            <td className="py-2.5 pr-3">{b.shipping_line_carrier || '-'} {b.vessel_flight_voyage ? `(${b.vessel_flight_voyage})` : ''}</td>
                                            <td className="py-2.5 pr-3">{b.port_of_loading || '-'} → {b.port_of_discharge || '-'}</td>
                                            <td className="py-2.5 pr-3 font-mono text-xs">{b.delivery_order_number || '-'}</td>
                                            <td className="py-2.5 pr-3">{b.currency} {(Number(b.freight_cost || 0) + Number(b.port_handling_charges || 0)).toLocaleString()}</td>
                                            <td className="py-2.5 pr-3">{b.currency} {(Number(b.demurrage_amount || 0) + Number(b.detention_amount || 0)).toLocaleString()} ({b.demurrage_days || 0}d)</td>
                                            <td className="py-2.5 pr-3">
                                                <Select value={b.status} onValueChange={(val) => router.patch(route('clearing-forwarding.freight.status', b.id), { status: val })}>
                                                    <SelectTrigger className="h-8 w-32 text-xs"><SelectValue /></SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="quoted">{t('Quoted')}</SelectItem>
                                                        <SelectItem value="booked">{t('Booked')}</SelectItem>
                                                        <SelectItem value="in_transit">{t('In Transit')}</SelectItem>
                                                        <SelectItem value="arrived">{t('Arrived')}</SelectItem>
                                                        <SelectItem value="do_issued">{t('DO Issued')}</SelectItem>
                                                        <SelectItem value="handed_over">{t('Handed Over')}</SelectItem>
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

                {/* Configurable Customs Procedures & CPC Rules */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="flex items-center gap-2 text-base">
                            <Sliders className="h-5 w-5 text-indigo-600" />
                            <span>{t('Configurable Customs Procedures, CPC Codes & Tax Rates')}</span>
                        </CardTitle>
                        <Button size="sm" variant="outline" onClick={() => setOpenRule(true)}>
                            <Plus className="h-4 w-4 mr-1" /> {t('Add CPC Rule')}
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b text-left text-muted-foreground">
                                        <th className="py-2 pr-3">{t('CPC Code')}</th>
                                        <th className="py-2 pr-3">{t('Procedure')}</th>
                                        <th className="py-2 pr-3">{t('Country')}</th>
                                        <th className="py-2 pr-3">{t('Import Duty %')}</th>
                                        <th className="py-2 pr-3">{t('VAT %')}</th>
                                        <th className="py-2 pr-3">{t('Excise %')}</th>
                                        <th className="py-2 pr-3">{t('RDL %')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {customsRules.length === 0 ? (
                                        <tr><td colSpan={7} className="py-6 text-center text-muted-foreground">{t('No configurable CPC rules added yet.')}</td></tr>
                                    ) : customsRules.map((r) => (
                                        <tr key={r.id} className="border-b">
                                            <td className="py-2 pr-3 font-mono font-semibold">{r.cpc_code}</td>
                                            <td className="py-2 pr-3">{r.procedure_name}</td>
                                            <td className="py-2 pr-3"><Badge variant="outline">{r.country_code}</Badge></td>
                                            <td className="py-2 pr-3">{r.import_duty_rate}%</td>
                                            <td className="py-2 pr-3">{r.vat_rate}%</td>
                                            <td className="py-2 pr-3">{r.excise_duty_rate}%</td>
                                            <td className="py-2 pr-3">{r.rdl_rate}%</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={openBooking} onOpenChange={setOpenBooking}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader><DialogTitle>{t('New Freight Booking')}</DialogTitle></DialogHeader>
                    <form onSubmit={submitBooking} className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label>{t('Linked Shipment')}</Label>
                                <Select value={bookingForm.data.cf_job_id} onValueChange={(v) => bookingForm.setData('cf_job_id', v)}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">{t('-- Standalone --')}</SelectItem>
                                        {jobs.map((j) => <SelectItem key={j.id} value={String(j.id)}>{j.job_number} - {j.title}</SelectItem>)}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Freight Mode')} *</Label>
                                <Select value={bookingForm.data.freight_mode} onValueChange={(v) => bookingForm.setData('freight_mode', v)}>
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="sea">{t('Sea Freight')}</SelectItem>
                                        <SelectItem value="air">{t('Air Freight')}</SelectItem>
                                        <SelectItem value="road">{t('Road Freight')}</SelectItem>
                                        <SelectItem value="rail">{t('Rail Freight')}</SelectItem>
                                        <SelectItem value="multimodal">{t('Multimodal')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Shipping Line / Carrier')}</Label>
                                <Input value={bookingForm.data.shipping_line_carrier} onChange={(e) => bookingForm.setData('shipping_line_carrier', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Vessel / Flight / Voyage')}</Label>
                                <Input value={bookingForm.data.vessel_flight_voyage} onChange={(e) => bookingForm.setData('vessel_flight_voyage', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Port of Loading')}</Label>
                                <Input value={bookingForm.data.port_of_loading} onChange={(e) => bookingForm.setData('port_of_loading', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Port of Discharge')}</Label>
                                <Input value={bookingForm.data.port_of_discharge} onChange={(e) => bookingForm.setData('port_of_discharge', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Freight Cost')}</Label>
                                <Input type="number" min="0" value={bookingForm.data.freight_cost} onChange={(e) => bookingForm.setData('freight_cost', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Port Handling Charges')}</Label>
                                <Input type="number" min="0" value={bookingForm.data.port_handling_charges} onChange={(e) => bookingForm.setData('port_handling_charges', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Demurrage Amount')}</Label>
                                <Input type="number" min="0" value={bookingForm.data.demurrage_amount} onChange={(e) => bookingForm.setData('demurrage_amount', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Delivery Order (DO) Number')}</Label>
                                <Input value={bookingForm.data.delivery_order_number} onChange={(e) => bookingForm.setData('delivery_order_number', e.target.value)} />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setOpenBooking(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={bookingForm.processing}>{t('Save Booking')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={openRule} onOpenChange={setOpenRule}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader><DialogTitle>{t('Add Customs CPC & Tax Rule')}</DialogTitle></DialogHeader>
                    <form onSubmit={submitRule} className="space-y-3">
                        <div className="space-y-1">
                            <Label>{t('CPC Code')} *</Label>
                            <Input required value={ruleForm.data.cpc_code} onChange={(e) => ruleForm.setData('cpc_code', e.target.value)} placeholder="e.g. C400" />
                        </div>
                        <div className="space-y-1">
                            <Label>{t('Procedure Name')} *</Label>
                            <Input required value={ruleForm.data.procedure_name} onChange={(e) => ruleForm.setData('procedure_name', e.target.value)} placeholder="e.g. Direct Import for Home Use" />
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label>{t('Import Duty %')}</Label>
                                <Input type="number" step="0.01" value={ruleForm.data.import_duty_rate} onChange={(e) => ruleForm.setData('import_duty_rate', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('VAT %')}</Label>
                                <Input type="number" step="0.01" value={ruleForm.data.vat_rate} onChange={(e) => ruleForm.setData('vat_rate', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('Excise %')}</Label>
                                <Input type="number" step="0.01" value={ruleForm.data.excise_duty_rate} onChange={(e) => ruleForm.setData('excise_duty_rate', e.target.value)} />
                            </div>
                            <div className="space-y-1">
                                <Label>{t('RDL %')}</Label>
                                <Input type="number" step="0.01" value={ruleForm.data.rdl_rate} onChange={(e) => ruleForm.setData('rdl_rate', e.target.value)} />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setOpenRule(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={ruleForm.processing}>{t('Save Rule')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}

