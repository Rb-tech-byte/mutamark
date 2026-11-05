import React from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Ship,
    ArrowLeft,
    CheckCircle2,
    FileText,
    Container,
    Anchor,
    Truck
} from 'lucide-react';
import { UserSummary } from '../types';

interface CreateProps {
    customers: UserSummary[];
    staff: UserSummary[];
    suggestedJobNumber: string;
}

export default function Create({ customers, staff, suggestedJobNumber }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: customers.length > 0 ? customers[0].id : '',
        consignee_name: '',
        consignor_name: '',
        service_type: 'import',
        transport_mode: 'sea',
        customs_regime: 'home_use',
        priority: 'normal',
        customer_reference: '',
        case_owner_id: staff.length > 0 ? staff[0].id : '',
        notes: '',

        // Shipment particulars
        bl_awb_number: '',
        vessel_flight_truck: '',
        voyage_number: '',
        shipping_line_airline: '',
        origin_country: '',
        port_of_loading: '',
        port_of_discharge: 'Dar es Salaam Port',
        terminal_icd: 'TICTS Terminal',
        final_destination: 'Dar es Salaam, Tanzania',
        eta: '',

        // Cargo item
        commodity_description: '',
        package_type: 'cartons',
        package_count: 1,
        gross_weight_kg: 0,
        cbm: 0,
        hs_code: '',
        customs_value: 0,
        currency: 'USD',

        // Container details
        container_number: '',
        size_type: '40HC',
        seal_number: '',
        free_days: 14,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('clearing-forwarding.jobs.store'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Open New Shipment Job File" />

            <div className="max-w-5xl mx-auto p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between border-b pb-4">
                    <div className="flex items-center gap-3">
                        <Link href={route('clearing-forwarding.jobs.index')}>
                            <Button variant="ghost" size="icon" className="h-9 w-9">
                                <ArrowLeft className="w-5 h-5 text-slate-600" />
                            </Button>
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-xl font-bold text-slate-900">
                                    Open New Shipment Job File
                                </h1>
                                <span className="bg-indigo-100 text-indigo-800 font-mono text-xs font-bold px-2 py-0.5 rounded">
                                    {suggestedJobNumber}
                                </span>
                            </div>
                            <p className="text-xs text-slate-500">
                                Capture instruction, parties, transport leg, cargo lines, and containers to initialize electronic case file.
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Section 1: Customer Instruction & Job Parameters */}
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="bg-slate-50/70 border-b pb-3">
                            <CardTitle className="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <FileText className="w-4 h-4 text-indigo-600" />
                                1. Instruction & Client Particulars
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <Label className="text-xs font-semibold">Client / Account *</Label>
                                <select
                                    value={data.customer_id}
                                    onChange={(e) => setData('customer_id', e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                    required
                                >
                                    {customers.map((c) => (
                                        <option key={c.id} value={c.id}>{c.name} ({c.email})</option>
                                    ))}
                                </select>
                                {errors.customer_id && <p className="text-red-500 text-[11px] mt-1">{errors.customer_id}</p>}
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Consignee Name</Label>
                                <Input
                                    placeholder="Receiving entity name"
                                    value={data.consignee_name}
                                    onChange={(e) => setData('consignee_name', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Consignor / Shipper Name</Label>
                                <Input
                                    placeholder="Origin supplier / exporter"
                                    value={data.consignor_name}
                                    onChange={(e) => setData('consignor_name', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Service Type *</Label>
                                <select
                                    value={data.service_type}
                                    onChange={(e) => setData('service_type', e.target.value as any)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="import">Import Clearance</option>
                                    <option value="export">Export Forwarding</option>
                                    <option value="transit">Transit Cargo (EAC Corridors)</option>
                                    <option value="domestic">Domestic Haulage</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Transport Mode *</Label>
                                <select
                                    value={data.transport_mode}
                                    onChange={(e) => setData('transport_mode', e.target.value as any)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="sea">Sea Freight (Ocean)</option>
                                    <option value="air">Air Cargo</option>
                                    <option value="road">Cross-Border Road Haulage</option>
                                    <option value="rail">Rail Transport</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Customs Regime *</Label>
                                <select
                                    value={data.customs_regime}
                                    onChange={(e) => setData('customs_regime', e.target.value as any)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="home_use">Home Use (IM4 Direct Clearance)</option>
                                    <option value="transit">Transit (T1 Bonded Corridor)</option>
                                    <option value="warehousing">Warehousing (IM7 Bonded Depot)</option>
                                    <option value="temporary_import">Temporary Importation</option>
                                    <option value="export">Commercial Export (EX1)</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Client Reference / PO No</Label>
                                <Input
                                    placeholder="PO-2026-REF"
                                    value={data.customer_reference}
                                    onChange={(e) => setData('customer_reference', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Priority</Label>
                                <select
                                    value={data.priority}
                                    onChange={(e) => setData('priority', e.target.value as any)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="normal">Normal (Standard TAT)</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="critical">Critical (Expedited Clearance)</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Assigned Case Officer</Label>
                                <select
                                    value={data.case_owner_id}
                                    onChange={(e) => setData('case_owner_id', e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    {staff.map((s) => (
                                        <option key={s.id} value={s.id}>{s.name}</option>
                                    ))}
                                </select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Section 2: Shipment & Manifest Details */}
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="bg-slate-50/70 border-b pb-3">
                            <CardTitle className="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <Ship className="w-4 h-4 text-blue-600" />
                                2. Shipment Particulars & Routing
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <Label className="text-xs font-semibold">Bill of Lading / Air Waybill No</Label>
                                <Input
                                    placeholder="e.g. MSKU9817261"
                                    value={data.bl_awb_number}
                                    onChange={(e) => setData('bl_awb_number', e.target.value)}
                                    className="text-xs font-mono mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Vessel / Flight / Truck</Label>
                                <Input
                                    placeholder="MV MAERSK ATLANTIC"
                                    value={data.vessel_flight_truck}
                                    onChange={(e) => setData('vessel_flight_truck', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Voyage / Flight No</Label>
                                <Input
                                    placeholder="V-2601E"
                                    value={data.voyage_number}
                                    onChange={(e) => setData('voyage_number', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Shipping Line / Carrier</Label>
                                <Input
                                    placeholder="Maersk, MSC, CMA CGM, Ethiopian..."
                                    value={data.shipping_line_airline}
                                    onChange={(e) => setData('shipping_line_airline', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Port of Loading (POL)</Label>
                                <Input
                                    placeholder="Shanghai Port, China"
                                    value={data.port_of_loading}
                                    onChange={(e) => setData('port_of_loading', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Port of Discharge (POD)</Label>
                                <Input
                                    placeholder="Dar es Salaam Port"
                                    value={data.port_of_discharge}
                                    onChange={(e) => setData('port_of_discharge', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Terminal / ICD / CFS</Label>
                                <Input
                                    placeholder="TICTS / DICD Ubungo / PACT"
                                    value={data.terminal_icd}
                                    onChange={(e) => setData('terminal_icd', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Final Destination</Label>
                                <Input
                                    placeholder="Dar es Salaam or Kigali, Rwanda"
                                    value={data.final_destination}
                                    onChange={(e) => setData('final_destination', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Estimated Arrival Date (ETA)</Label>
                                <Input
                                    type="date"
                                    value={data.eta}
                                    onChange={(e) => setData('eta', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Section 3: Cargo Line & Packaging */}
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="bg-slate-50/70 border-b pb-3">
                            <CardTitle className="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <Anchor className="w-4 h-4 text-emerald-600" />
                                3. Cargo Specification & Valuation
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="md:col-span-3">
                                <Label className="text-xs font-semibold">Commodity Description *</Label>
                                <Textarea
                                    placeholder="Detailed cargo description matching commercial invoice and packing list..."
                                    value={data.commodity_description}
                                    onChange={(e) => setData('commodity_description', e.target.value)}
                                    className="text-xs mt-1 h-16"
                                    required
                                />
                                {errors.commodity_description && <p className="text-red-500 text-[11px] mt-1">{errors.commodity_description}</p>}
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Package Count & Type</Label>
                                <div className="flex gap-2 mt-1">
                                    <Input
                                        type="number"
                                        min="1"
                                        value={data.package_count}
                                        onChange={(e) => setData('package_count', Number(e.target.value))}
                                        className="text-xs w-24"
                                    />
                                    <select
                                        value={data.package_type}
                                        onChange={(e) => setData('package_type', e.target.value)}
                                        className="text-xs border border-slate-300 rounded-md px-2 bg-white flex-1"
                                    >
                                        <option value="cartons">Cartons</option>
                                        <option value="pallets">Pallets</option>
                                        <option value="drums">Drums</option>
                                        <option value="bags">Bags</option>
                                        <option value="pieces">Pieces / Units</option>
                                        <option value="crates">Wooden Crates</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Gross Weight (KG)</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    placeholder="14500.00"
                                    value={data.gross_weight_kg}
                                    onChange={(e) => setData('gross_weight_kg', Number(e.target.value))}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Volume (CBM)</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    placeholder="58.5"
                                    value={data.cbm}
                                    onChange={(e) => setData('cbm', Number(e.target.value))}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Proposed HS Code</Label>
                                <Input
                                    placeholder="e.g. 8517.62.00"
                                    value={data.hs_code}
                                    onChange={(e) => setData('hs_code', e.target.value)}
                                    className="text-xs font-mono mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Customs / Invoice Value</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    placeholder="84500.00"
                                    value={data.customs_value}
                                    onChange={(e) => setData('customs_value', Number(e.target.value))}
                                    className="text-xs mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Currency</Label>
                                <select
                                    value={data.currency}
                                    onChange={(e) => setData('currency', e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="USD">USD ($)</option>
                                    <option value="TZS">TZS (Tanzanian Shilling)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                </select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Section 4: Container Tracker (if containerized) */}
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="bg-slate-50/70 border-b pb-3">
                            <CardTitle className="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <Container className="w-4 h-4 text-amber-600" />
                                4. Container Tracker & Demurrage Setup (Optional)
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-5 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Label className="text-xs font-semibold">Container Number</Label>
                                <Input
                                    placeholder="e.g. MSKU7829104"
                                    value={data.container_number}
                                    onChange={(e) => setData('container_number', e.target.value.toUpperCase())}
                                    className="text-xs font-mono mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Size / Type</Label>
                                <select
                                    value={data.size_type}
                                    onChange={(e) => setData('size_type', e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="20FT">20FT Standard</option>
                                    <option value="40FT">40FT Standard</option>
                                    <option value="40HC">40FT High Cube (40HC)</option>
                                    <option value="REEFER">Reefer Container</option>
                                    <option value="FLAT_RACK">Flat Rack / Open Top</option>
                                </select>
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Seal Number</Label>
                                <Input
                                    placeholder="e.g. ML-TZ-99201"
                                    value={data.seal_number}
                                    onChange={(e) => setData('seal_number', e.target.value)}
                                    className="text-xs font-mono mt-1"
                                />
                            </div>

                            <div>
                                <Label className="text-xs font-semibold">Demurrage Free Days</Label>
                                <Input
                                    type="number"
                                    min="0"
                                    value={data.free_days}
                                    onChange={(e) => setData('free_days', Number(e.target.value))}
                                    className="text-xs mt-1"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Action buttons */}
                    <div className="flex items-center justify-end gap-3 pt-4 border-t">
                        <Link href={route('clearing-forwarding.jobs.index')}>
                            <Button variant="outline" type="button">
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            disabled={processing}
                            className="bg-[#1e3a5f] hover:bg-[#152a45] text-white font-semibold gap-1.5 px-6 shadow"
                        >
                            <CheckCircle2 className="w-4 h-4" /> Open Job File
                        </Button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

