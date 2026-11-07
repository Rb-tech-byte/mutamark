import React, { useState } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogDescription,
} from '@/components/ui/dialog';
import {
    Ship,
    Truck,
    ArrowLeft,
    CheckCircle2,
    Clock,
    AlertTriangle,
    FileText,
    Container,
    Anchor,
    Printer,
    DollarSign,
    ShieldCheck,
    MapPin,
    Calendar,
    Layers,
    UserCheck,
    Check,
    X,
    ExternalLink,
    Send,
    AlertCircle,
    Calculator,
    PackageCheck,
    Plus,
    Download
} from 'lucide-react';
import { CFJob, CFDocumentChecklist, UserSummary } from '../types';

interface ShowProps {
    job: CFJob;
    users: UserSummary[];
}

export default function Show({ job, users }: ShowProps) {
    const [activeTab, setActiveTab] = useState('overview');

    // Dialog states
    const [isStatusDialogOpen, setIsStatusDialogOpen] = useState(false);
    const [isDisbursementDialogOpen, setIsDisbursementDialogOpen] = useState(false);
    const [isTransportDialogOpen, setIsTransportDialogOpen] = useState(false);
    const [isMilestoneDialogOpen, setIsMilestoneDialogOpen] = useState(false);
    const [isDeliveryDialogOpen, setIsDeliveryDialogOpen] = useState(false);
    const [isDeclarationDialogOpen, setIsDeclarationDialogOpen] = useState(false);
    const [isReconcileDialogOpen, setIsReconcileDialogOpen] = useState(false);
    const [selectedDoc, setSelectedDoc] = useState<CFDocumentChecklist | null>(null);

    // Form handlers
    const statusForm = useForm({
        status: job.status,
        notes: '',
    });

    const disbursementForm = useForm({
        payee_name: '',
        charge_category: 'statutory_duty',
        description: '',
        payment_method: 'control_number_gepg',
        disbursed_amount: '',
        currency: 'TZS',
        payment_reference: '',
        payment_date: new Date().toISOString().split('T')[0],
    });

    const transportForm = useForm({
        transporter_type: 'internal_fleet',
        carrier_name: 'Mutamark Heavy Haulage Fleet',
        driver_name: '',
        driver_phone: '',
        vehicle_plate: '',
        pickup_location: job.shipment?.port_of_discharge || 'Dar es Salaam Port',
        delivery_destination: job.shipment?.final_destination || 'Customer Premises',
        route_corridor: 'Central Corridor',
        agreed_freight_cost: '',
    });

    const milestoneForm = useForm({
        milestone_code: 'checkpoint_cleared',
        location_name: '',
        remarks: '',
        is_customer_visible: true,
    });

    const deliveryForm = useForm({
        delivery_address: job.shipment?.final_destination || 'Customer Premises',
        packages_delivered: job.cargo_lines?.[0]?.package_count || 1,
        cargo_condition: 'sound_and_intact',
        condition_notes: 'Cargo inspected and delivered in sound, sound condition with all seals verified intact.',
        recipient_name: job.consignee_name || job.customer?.name || '',
        recipient_id_type: 'national_id_nida',
        recipient_id_number: '',
        recipient_phone: '',
        signature_data: 'DIGITALLY_ENDORSED_ON_DEVICE',
    });

    const [isEventDialogOpen, setIsEventDialogOpen] = useState(false);

    const declarationForm = useForm({
        customs_office: job.declarations?.[0]?.customs_office || 'Dar es Salaam Port (TICTS)',
        declaration_type: job.declarations?.[0]?.declaration_type || 'TANSAD',
        declaration_number: job.declarations?.[0]?.declaration_number || '',
        tancis_reference: job.declarations?.[0]?.tancis_reference || '',
        regime_code: job.declarations?.[0]?.regime_code || 'IM4',
        assessment_number: job.declarations?.[0]?.assessment_number || '',
        assessed_amount_tzs: job.declarations?.[0]?.assessed_amount_tzs || '',
        import_duty_assessed_tzs: job.declarations?.[0]?.import_duty_assessed_tzs || '',
        vat_assessed_tzs: job.declarations?.[0]?.vat_assessed_tzs || '',
        rdl_assessed_tzs: job.declarations?.[0]?.rdl_assessed_tzs || '',
        cpf_assessed_tzs: job.declarations?.[0]?.cpf_assessed_tzs || '',
        payment_control_number: job.declarations?.[0]?.payment_control_number || '',
        release_order_number: job.declarations?.[0]?.release_order_number || '',
        status: job.declarations?.[0]?.status || 'draft',
        customs_channel: job.declarations?.[0]?.customs_channel || 'green',
        external_submission_time: job.declarations?.[0]?.external_submission_time ? String(job.declarations[0].external_submission_time).substring(0, 10) : '',
        lodgement_evidence: null as File | null,
        assessment_document: null as File | null,
        release_order: null as File | null,
    });

    const eventForm = useForm({
        event_type: 'query_raised',
        officer_name: '',
        location: 'Customs Examination Yard',
        instructions: '',
        findings: '',
        outcome: '',
    });

    const reconcileForm = useForm({
        customer_advance_received: job.job_costing?.customer_advance_received || 0,
        agency_fee_revenue: job.job_costing?.agency_fee_revenue || 4500000,
        is_financially_reconciled: true,
    });

    const formatMoney = (val: number | string) => {
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
            <Head title={`Job ${job.job_number} - Electronic Job File`} />

            <div className="p-6 space-y-6">
                {/* Top Action & Summary Bar */}
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b pb-5">
                    <div className="flex items-start gap-3">
                        <Link href={route('clearing-forwarding.jobs.index')}>
                            <Button variant="ghost" size="icon" className="h-9 w-9 mt-0.5">
                                <ArrowLeft className="w-5 h-5 text-slate-600" />
                            </Button>
                        </Link>
                        <div>
                            <div className="flex items-center gap-2.5 flex-wrap">
                                <h1 className="text-2xl font-bold tracking-tight text-slate-900 font-mono">
                                    {job.job_number}
                                </h1>
                                {getStatusBadge(job.status)}
                                <Badge variant="secondary" className="uppercase font-semibold text-[10px]">
                                    {job.service_type} &bull; {job.transport_mode}
                                </Badge>
                                <Badge variant="outline" className="text-slate-600 text-[10px] uppercase font-mono">
                                    {job.customs_regime}
                                </Badge>
                            </div>
                            <p className="text-xs text-slate-500 mt-1">
                                Client: <span className="font-semibold text-slate-800">{job.customer?.name}</span> &bull; Consignee: <span className="font-semibold text-slate-800">{job.consignee_name || 'Designated Party'}</span> &bull; Opened: {new Date(job.created_at).toLocaleDateString()}
                            </p>
                        </div>
                    </div>

                    {/* Quick Print & Action Buttons */}
                    <div className="flex items-center gap-2 flex-wrap">
                        <Button
                            variant="outline"
                            onClick={() => setIsStatusDialogOpen(true)}
                            className="text-xs gap-1 border-slate-300 shadow-sm"
                        >
                            Update Status
                        </Button>

                        {/* Print Document Dropdown / Quick Links */}
                        <div className="flex items-center gap-1.5">
                            <a
                                href={route('clearing-forwarding.jobs.print', { cfJob: job.id, type: 'quotation' })}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <Button size="sm" variant="outline" className="text-xs gap-1">
                                    <Printer className="w-3.5 h-3.5 text-slate-600" /> Quotation
                                </Button>
                            </a>
                            <a
                                href={route('clearing-forwarding.jobs.print', { cfJob: job.id, type: 'funding_request' })}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <Button size="sm" variant="outline" className="text-xs gap-1">
                                    <DollarSign className="w-3.5 h-3.5 text-indigo-600" /> Funding Request
                                </Button>
                            </a>
                            <a
                                href={route('clearing-forwarding.jobs.print', { cfJob: job.id, type: 'delivery_note' })}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <Button size="sm" variant="outline" className="text-xs gap-1">
                                    <FileText className="w-3.5 h-3.5 text-emerald-600" /> Delivery Note
                                </Button>
                            </a>
                            <a
                                href={route('clearing-forwarding.jobs.print', { cfJob: job.id, type: 'final_account' })}
                                target="_blank"
                                rel="noreferrer"
                            >
                                <Button size="sm" className="bg-[#1e3a5f] hover:bg-[#152a45] text-white text-xs gap-1">
                                    <Printer className="w-3.5 h-3.5 text-white" /> Final Account
                                </Button>
                            </a>
                        </div>
                    </div>
                </div>

                {/* Sub-module Navigation Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList className="bg-slate-100 p-1 flex-wrap h-auto gap-1">
                        <TabsTrigger value="overview" className="text-xs gap-1.5 py-1.5">
                            <Anchor className="w-3.5 h-3.5" /> Overview & Shipment
                        </TabsTrigger>
                        <TabsTrigger value="cargo" className="text-xs gap-1.5 py-1.5">
                            <Container className="w-3.5 h-3.5" /> Cargo & Containers ({job.containers?.length || 0})
                        </TabsTrigger>
                        <TabsTrigger value="documents" className="text-xs gap-1.5 py-1.5">
                            <FileText className="w-3.5 h-3.5" /> Documents & Compliance ({job.documents?.length || 0})
                        </TabsTrigger>
                        <TabsTrigger value="customs" className="text-xs gap-1.5 py-1.5">
                            <ShieldCheck className="w-3.5 h-3.5" /> Customs & TANCIS
                        </TabsTrigger>
                        <TabsTrigger value="transport" className="text-xs gap-1.5 py-1.5">
                            <Truck className="w-3.5 h-3.5" /> Transport & Haulage ({job.transport_orders?.length || 0})
                        </TabsTrigger>
                        <TabsTrigger value="tracking" className="text-xs gap-1.5 py-1.5">
                            <Clock className="w-3.5 h-3.5" /> In-Transit Milestones ({job.tracking_events?.length || 0})
                        </TabsTrigger>
                        <TabsTrigger value="delivery" className="text-xs gap-1.5 py-1.5">
                            <CheckCircle2 className="w-3.5 h-3.5" /> Delivery & POD ({job.deliveries?.length || 0})
                        </TabsTrigger>
                        <TabsTrigger value="costing" className="text-xs gap-1.5 py-1.5">
                            <DollarSign className="w-3.5 h-3.5" /> Costing & Reconciliation
                        </TabsTrigger>
                    </TabsList>

                    {/* Tab 1: Overview & Shipment */}
                    <TabsContent value="overview" className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {/* Parties Card */}
                            <Card className="border-slate-200 shadow-sm">
                                <CardHeader className="bg-slate-50 pb-2 border-b">
                                    <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                        <UserCheck className="w-4 h-4 text-indigo-600" /> Parties & Authorization
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-4 space-y-3 text-xs">
                                    <div>
                                        <span className="text-slate-500 font-medium">Customer / Principal:</span>
                                        <div className="font-semibold text-slate-900 text-sm mt-0.5">{job.customer?.name}</div>
                                        <div className="text-slate-500">{job.customer?.email}</div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Consignee:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">{job.consignee_name || 'As addressed in BL'}</div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Consignor / Exporter:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">{job.consignor_name || 'Origin Shipper'}</div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Case Officer:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">{job.case_owner?.name || 'Unassigned'}</div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Transport Leg Card */}
                            <Card className="border-slate-200 shadow-sm md:col-span-2">
                                <CardHeader className="bg-slate-50 pb-2 border-b">
                                    <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                        <Ship className="w-4 h-4 text-blue-600" /> Shipment Leg & Vessel Routing
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                                    <div>
                                        <span className="text-slate-500 font-medium">BL / AWB Number:</span>
                                        <div className="font-mono font-bold text-slate-900 text-sm mt-0.5">
                                            {job.shipment?.bl_awb_number || 'Pending Submission'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Vessel / Flight / Vehicle:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.vessel_flight_truck || 'Awaiting Carrier'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Voyage / Flight No:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.voyage_number || 'N/A'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Shipping Line / Carrier:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.shipping_line_airline || 'Direct Carrier'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Port of Loading (POL):</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.port_of_loading || 'Origin Terminal'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Port of Discharge (POD):</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.port_of_discharge || 'Dar es Salaam Port'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Terminal / ICD:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.terminal_icd || 'TICTS Terminal'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">Final Destination:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.final_destination || 'Customer Store'}
                                        </div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-medium">ETA / ATA:</span>
                                        <div className="font-semibold text-slate-800 mt-0.5">
                                            {job.shipment?.ata ? `ATA: ${job.shipment.ata}` : job.shipment?.eta ? `ETA: ${job.shipment.eta}` : 'Pending Notice'}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Notes & Special Instructions */}
                        {job.notes && (
                            <Card className="border-slate-200 shadow-sm bg-slate-50/50">
                                <CardContent className="p-4 text-xs text-slate-700">
                                    <span className="font-semibold text-slate-900 block mb-1">Operational Case Notes:</span>
                                    <p className="leading-relaxed">{job.notes}</p>
                                </CardContent>
                            </Card>
                        )}
                    </TabsContent>

                    {/* Tab 2: Cargo Lines & Containers */}
                    <TabsContent value="cargo" className="space-y-4">
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b">
                                <CardTitle className="text-xs font-bold uppercase text-slate-700">
                                    Cargo Manifest & Package Inventory
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50/50">
                                            <TableHead className="text-xs">#</TableHead>
                                            <TableHead className="text-xs">Description & Specifications</TableHead>
                                            <TableHead className="text-xs text-center">Packages</TableHead>
                                            <TableHead className="text-xs text-right">Gross Wt (KG)</TableHead>
                                            <TableHead className="text-xs text-right">CBM</TableHead>
                                            <TableHead className="text-xs font-mono">HS Code</TableHead>
                                            <TableHead className="text-xs text-right">Declared Value</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {job.cargo_lines?.map((line, idx) => (
                                            <TableRow key={line.id}>
                                                <TableCell className="text-xs font-semibold">{idx + 1}</TableCell>
                                                <TableCell className="text-xs font-medium text-slate-900">
                                                    {line.commodity_description}
                                                    {line.marks_numbers && (
                                                        <div className="text-[10px] text-slate-500 font-mono">Marks: {line.marks_numbers}</div>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-xs text-center">
                                                    {Number(line.package_count).toLocaleString()} {line.package_type}
                                                </TableCell>
                                                <TableCell className="text-xs text-right">
                                                    {Number(line.gross_weight_kg).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                                </TableCell>
                                                <TableCell className="text-xs text-right">
                                                    {line.cbm ? Number(line.cbm).toFixed(2) : '-'}
                                                </TableCell>
                                                <TableCell className="text-xs font-mono font-bold text-indigo-700">
                                                    {line.hs_code || 'Unclassified'}
                                                </TableCell>
                                                <TableCell className="text-xs text-right font-semibold">
                                                    {line.currency} {Number(line.customs_value).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>

                        {/* Containers & Free Days Counter */}
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b flex flex-row items-center justify-between">
                                <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                    <Container className="w-4 h-4 text-amber-600" /> Container Demurrage & Gate Tracker
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50/50">
                                            <TableHead className="text-xs">Container No</TableHead>
                                            <TableHead className="text-xs">Size / Type</TableHead>
                                            <TableHead className="text-xs">Seal No</TableHead>
                                            <TableHead className="text-xs">Shipping Line</TableHead>
                                            <TableHead className="text-xs text-center">Free Days</TableHead>
                                            <TableHead className="text-xs">Return Deadline</TableHead>
                                            <TableHead className="text-xs">Status</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {job.containers && job.containers.length > 0 ? (
                                            job.containers.map((c) => (
                                                <TableRow key={c.id}>
                                                    <TableCell className="font-mono font-bold text-xs text-slate-900">
                                                        {c.container_number}
                                                    </TableCell>
                                                    <TableCell className="text-xs font-medium">{c.size_type}</TableCell>
                                                    <TableCell className="text-xs font-mono">{c.seal_number || 'N/A'}</TableCell>
                                                    <TableCell className="text-xs">{c.shipping_line || 'Maersk'}</TableCell>
                                                    <TableCell className="text-xs text-center font-semibold">{c.free_days} days</TableCell>
                                                    <TableCell className="text-xs">
                                                        {c.empty_return_deadline ? (
                                                            <span className="font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                                {c.empty_return_deadline}
                                                            </span>
                                                        ) : (
                                                            'Not set'
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-xs capitalize">
                                                        <Badge variant="outline" className="capitalize text-[10px]">
                                                            {c.status.replace('_', ' ')}
                                                        </Badge>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell colSpan={7} className="text-center py-6 text-slate-500 text-xs">
                                                    No containerized cargo recorded for this assignment.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 3: Documents & Compliance */}
                    <TabsContent value="documents" className="space-y-4">
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b">
                                <CardTitle className="text-xs font-bold uppercase text-slate-700">
                                    Document Checklist & Verification Register
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50/50">
                                            <TableHead className="text-xs">Required Document</TableHead>
                                            <TableHead className="text-xs">Type</TableHead>
                                            <TableHead className="text-xs">Mandatory</TableHead>
                                            <TableHead className="text-xs">Reference No</TableHead>
                                            <TableHead className="text-xs">Verification</TableHead>
                                            <TableHead className="text-xs">Verified By</TableHead>
                                            <TableHead className="text-xs text-right">Action</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {job.documents?.map((doc) => (
                                            <TableRow key={doc.id}>
                                                <TableCell className="text-xs font-semibold text-slate-900">
                                                    {doc.document_name}
                                                </TableCell>
                                                <TableCell className="text-xs text-slate-500 capitalize">
                                                    {doc.document_type.replace('_', ' ')}
                                                </TableCell>
                                                <TableCell className="text-xs">
                                                    {doc.is_mandatory ? (
                                                        <span className="text-rose-700 font-semibold text-[10px]">Mandatory</span>
                                                    ) : (
                                                        <span className="text-slate-400 text-[10px]">Optional</span>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-xs font-mono">{doc.document_reference || '-'}</TableCell>
                                                <TableCell className="text-xs">
                                                    {doc.verification_status === 'verified' ? (
                                                        <Badge className="bg-emerald-100 text-emerald-800 border-emerald-200 text-[10px] gap-1">
                                                            <Check className="w-3 h-3" /> Verified
                                                        </Badge>
                                                    ) : doc.verification_status === 'rejected' ? (
                                                        <Badge className="bg-rose-100 text-rose-800 border-rose-200 text-[10px] gap-1">
                                                            <X className="w-3 h-3" /> Rejected
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="outline" className="text-amber-700 border-amber-300 text-[10px]">
                                                            Pending Verification
                                                        </Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-xs text-slate-600">
                                                    {doc.verifier?.name || '-'}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end gap-1">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="h-6 text-[10px] px-2 text-emerald-700 hover:bg-emerald-50"
                                                            onClick={() => {
                                                                router.patch(route('clearing-forwarding.documents.verify', doc.id), {
                                                                    verification_status: 'verified',
                                                                });
                                                            }}
                                                        >
                                                            Verify
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="h-6 text-[10px] px-2 text-rose-700 hover:bg-rose-50"
                                                            onClick={() => {
                                                                const reason = prompt('Enter rejection reason:');
                                                                if (reason) {
                                                                    router.patch(route('clearing-forwarding.documents.verify', doc.id), {
                                                                        verification_status: 'rejected',
                                                                        rejection_reason: reason,
                                                                    });
                                                                }
                                                            }}
                                                        >
                                                            Reject
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>

                        {/* Permits & Regulatory Certificates */}
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b">
                                <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                    <ShieldCheck className="w-4 h-4 text-emerald-600" /> Permits & Regulatory Certificates (TBS, TMDA, GCLA)
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-4">
                                {job.permits && job.permits.length > 0 ? (
                                    <div className="space-y-2">
                                        {job.permits.map((p) => (
                                            <div key={p.id} className="p-3 border rounded-lg bg-slate-50 flex items-center justify-between text-xs">
                                                <div>
                                                    <div className="font-bold text-slate-900">{p.agency} - {p.permit_type}</div>
                                                    <div className="text-slate-500 font-mono mt-0.5">Permit No: {p.permit_number || 'Under Application'}</div>
                                                    {p.special_conditions && <div className="text-[11px] text-slate-600 mt-1 italic">{p.special_conditions}</div>}
                                                </div>
                                                <div>
                                                    <Badge className="bg-emerald-100 text-emerald-800 capitalize">{p.status}</Badge>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-xs text-slate-500 py-3">No specialized agency permits recorded. Standard customs clearance applies.</p>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 4: Customs & TANCIS */}
                    <TabsContent value="customs" className="space-y-4">
                        {/* Permanent Mandatory Operating Indicator (SRS Addendum §17-18, INT-MAN-001) */}
                        <div className="p-3.5 bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-800 rounded-xl flex items-start gap-3">
                            <ShieldCheck className="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                            <div className="text-xs text-amber-950 dark:text-amber-200">
                                <div className="font-bold flex items-center gap-2 uppercase tracking-wide">
                                    <span>OPERATING INDICATOR: MANUAL TRA/TANCIS PROCESSING – NO DIRECT API INTEGRATION</span>
                                    <Badge className="bg-amber-200 text-amber-900 border-amber-300 text-[10px] font-mono">
                                        SRS Addendum §17-18
                                    </Badge>
                                </div>
                                <p className="mt-1 leading-relaxed text-amber-900/90 dark:text-amber-300">
                                    Mutamark ERP operates as an internal customs declaration preparation, valuation, and tracking workspace. Declarants prepare worksheets internally, log in separately to the official TRA/TANCIS web portal to submit declarations, and subsequently record verified references, assessment notices, and release evidence into this electronic job file.
                                </p>
                            </div>
                        </div>

                        {/* Top Action & Worksheet Bar */}
                        <div className="flex flex-wrap items-center justify-between gap-2 p-3 bg-slate-50 dark:bg-slate-900 border rounded-xl">
                            <div className="flex items-center gap-2">
                                <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Worksheet Status:
                                </span>
                                <Badge variant="outline" className="text-xs font-mono bg-white dark:bg-slate-800">
                                    Version v{job.declarations?.[0]?.worksheet_version || 1}
                                </Badge>
                                <span className="text-[11px] text-muted-foreground">
                                    (Generated: {job.declarations?.[0]?.worksheet_generated_at ? new Date(job.declarations[0].worksheet_generated_at).toLocaleDateString() : 'Draft'})
                                </span>
                            </div>

                            <div className="flex items-center gap-2">
                                <a
                                    href={route('clearing-forwarding.jobs.declarations.worksheet', job.id)}
                                    className="inline-flex"
                                >
                                    <Button size="sm" variant="outline" className="text-xs gap-1.5 h-8 bg-white dark:bg-slate-800">
                                        <FileText className="w-3.5 h-3.5 text-indigo-600" />
                                        Generate New Worksheet Version
                                    </Button>
                                </a>

                                <a
                                    href={route('clearing-forwarding.jobs.print', { cfJob: job.id, type: 'worksheet' })}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-flex"
                                >
                                    <Button size="sm" variant="outline" className="text-xs gap-1.5 h-8 bg-white dark:bg-slate-800">
                                        <Printer className="w-3.5 h-3.5 text-slate-600" />
                                        Print TANCIS Worksheet
                                    </Button>
                                </a>

                                <a
                                    href={route('clearing-forwarding.jobs.declarations.export-csv', job.id)}
                                    download
                                    className="inline-flex"
                                >
                                    <Button size="sm" variant="outline" className="text-xs gap-1.5 h-8 bg-white dark:bg-slate-800">
                                        <Download className="w-3.5 h-3.5 text-emerald-600" />
                                        Export CSV / Excel
                                    </Button>
                                </a>

                                <Button
                                    size="sm"
                                    onClick={() => setIsDeclarationDialogOpen(true)}
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs gap-1.5 h-8"
                                >
                                    <ShieldCheck className="w-3.5 h-3.5" />
                                    Update TANCIS Lodgement & Assessment
                                </Button>

                                <Button
                                    size="sm"
                                    variant="outline"
                                    onClick={() => setIsEventDialogOpen(true)}
                                    className="text-xs gap-1.5 h-8"
                                >
                                    <AlertCircle className="w-3.5 h-3.5 text-amber-600" />
                                    Record Customs Query / Event
                                </Button>
                            </div>
                        </div>

                        {/* Comparative Cards: Preliminary Estimates vs Official Assessed (SRS INT-MAN-009) */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Card A: Preliminary Estimated Customs Charges */}
                            <Card className="border-slate-200 shadow-sm border-t-4 border-t-amber-500">
                                <CardHeader className="bg-slate-50/70 pb-2 border-b">
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="text-xs font-bold uppercase text-slate-800 flex items-center gap-1.5">
                                            <Calculator className="w-4 h-4 text-amber-600" />
                                            Preliminary Estimated Customs Charges
                                        </CardTitle>
                                        <Badge className="bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 text-[10px] font-semibold">
                                            Estimate – Not official TRA assessment
                                        </Badge>
                                    </div>
                                    <CardDescription className="text-[11px]">
                                        Calculated based on verified invoice CIF value and HS code tariff schedule.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="p-4 space-y-3 text-xs">
                                    {(() => {
                                        const estCustomsVal = job.cargo_lines?.reduce((sum, it) => sum + (Number(it.customs_value) * 2650), 0) || 120000000;
                                        const estDuty = estCustomsVal * 0.25;
                                        const estVat = (estCustomsVal + estDuty) * 0.18;
                                        const estRdl = estCustomsVal * 0.015;
                                        const estCpf = 50000;
                                        const estTotal = estDuty + estVat + estRdl + estCpf;

                                        return (
                                            <div className="space-y-2">
                                                <div className="flex justify-between py-1 border-b border-dashed">
                                                    <span className="text-muted-foreground">Customs CIF Value (TZS):</span>
                                                    <span className="font-semibold font-mono">{formatMoney(estCustomsVal)}</span>
                                                </div>
                                                <div className="flex justify-between py-1 border-b border-dashed">
                                                    <span className="text-muted-foreground">Import Duty (Est. 25%):</span>
                                                    <span className="font-semibold font-mono">{formatMoney(estDuty)}</span>
                                                </div>
                                                <div className="flex justify-between py-1 border-b border-dashed">
                                                    <span className="text-muted-foreground">Value Added Tax (VAT 18%):</span>
                                                    <span className="font-semibold font-mono">{formatMoney(estVat)}</span>
                                                </div>
                                                <div className="flex justify-between py-1 border-b border-dashed">
                                                    <span className="text-muted-foreground">Railway Development Levy (RDL 1.5%):</span>
                                                    <span className="font-semibold font-mono">{formatMoney(estRdl)}</span>
                                                </div>
                                                <div className="flex justify-between py-1 border-b border-dashed">
                                                    <span className="text-muted-foreground">Customs Processing Fee (CPF):</span>
                                                    <span className="font-semibold font-mono">{formatMoney(estCpf)}</span>
                                                </div>
                                                <div className="flex justify-between pt-2 text-sm font-bold text-amber-900 dark:text-amber-300">
                                                    <span>Total Estimated Statutory Outflow:</span>
                                                    <span className="font-mono">{formatMoney(estTotal)}</span>
                                                </div>
                                            </div>
                                        );
                                    })()}
                                </CardContent>
                            </Card>

                            {/* Card B: Official Assessed Charges (TRA/TANCIS Assessment Notice) */}
                            <Card className="border-slate-200 shadow-sm border-t-4 border-t-indigo-600">
                                <CardHeader className="bg-slate-50/70 pb-2 border-b">
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="text-xs font-bold uppercase text-slate-800 flex items-center gap-1.5">
                                            <ShieldCheck className="w-4 h-4 text-indigo-600" />
                                            Official TRA Assessed Charges (TANCIS)
                                        </CardTitle>
                                        <Badge className={`text-[10px] ${
                                            job.declarations?.[0]?.is_assessment_verified
                                                ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                                                : 'bg-slate-100 text-slate-700'
                                        }`}>
                                            {job.declarations?.[0]?.is_assessment_verified ? 'Verified by Finance' : 'Verification Pending'}
                                        </Badge>
                                    </div>
                                    <CardDescription className="text-[11px]">
                                        Captured from official TRA Assessment Notice and GePG payment control number.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="p-4 space-y-3 text-xs">
                                    {job.declarations && job.declarations.length > 0 ? (
                                        (() => {
                                            const decl = job.declarations[0];
                                            return (
                                                <div className="space-y-2">
                                                    <div className="flex justify-between py-1 border-b border-dashed">
                                                        <span className="text-muted-foreground">TANCIS Declaration Reference:</span>
                                                        <span className="font-mono font-bold text-indigo-700">{decl.tancis_reference || decl.declaration_number || 'Pending'}</span>
                                                    </div>
                                                    <div className="flex justify-between py-1 border-b border-dashed">
                                                        <span className="text-muted-foreground">Assessment Notice Number:</span>
                                                        <span className="font-mono font-semibold">{decl.assessment_number || 'Pending Notice'}</span>
                                                    </div>
                                                    <div className="flex justify-between py-1 border-b border-dashed">
                                                        <span className="text-muted-foreground">GePG Payment Control Number:</span>
                                                        <span className="font-mono font-bold text-slate-900 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                                            {decl.payment_control_number || 'Pending GePG'}
                                                        </span>
                                                    </div>
                                                    <div className="flex justify-between py-1 border-b border-dashed">
                                                        <span className="text-muted-foreground">Assessed Import Duty & VAT:</span>
                                                        <span className="font-mono font-semibold">
                                                            {decl.import_duty_assessed_tzs ? formatMoney(Number(decl.import_duty_assessed_tzs) + Number(decl.vat_assessed_tzs || 0)) : formatMoney(decl.assessed_amount_tzs)}
                                                        </span>
                                                    </div>
                                                    <div className="flex justify-between pt-2 text-sm font-bold text-indigo-900 dark:text-indigo-300">
                                                        <span>Total Amount Payable (TZS):</span>
                                                        <span className="font-mono">{formatMoney(decl.assessed_amount_tzs)}</span>
                                                    </div>

                                                    <div className="pt-2 border-t flex items-center justify-between">
                                                        <div className="text-[11px] text-muted-foreground">
                                                            Source: <span className="font-medium text-slate-700">{decl.manual_entry_source || 'Manual entry from TANCIS'}</span>
                                                        </div>
                                                        {!decl.is_assessment_verified ? (
                                                            <Button
                                                                size="sm"
                                                                className="h-7 text-[11px] bg-emerald-600 hover:bg-emerald-700 text-white"
                                                                onClick={() => {
                                                                    router.post(route('clearing-forwarding.declarations.verify-assessment', decl.id));
                                                                }}
                                                            >
                                                                Verify Assessment (Finance Officer)
                                                            </Button>
                                                        ) : (
                                                            <span className="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                                <CheckCircle2 className="w-3.5 h-3.5" /> Assessment Verified
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            );
                                        })()
                                    ) : (
                                        <p className="text-slate-500 py-3 text-center">
                                            No customs declaration logged yet. Enter assessment particulars above.
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Customs Release & Port Exit Authorization Card */}
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50/70 pb-2 border-b flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle className="text-xs font-bold uppercase text-slate-800 flex items-center gap-1.5">
                                        <PackageCheck className="w-4 h-4 text-emerald-600" />
                                        Official Customs Release Order & Gate-Out Verification
                                    </CardTitle>
                                    <CardDescription className="text-[11px]">
                                        Cargo release requires verified official TRA Release Order reference and document upload before dispatch.
                                    </CardDescription>
                                </div>
                                {job.declarations?.[0] && !job.declarations[0].is_release_verified && job.declarations[0].release_order_number && (
                                    <Button
                                        size="sm"
                                        className="h-8 text-xs bg-emerald-700 hover:bg-emerald-800 text-white"
                                        onClick={() => {
                                            const declId = job.declarations?.[0]?.id;
                                            if (declId) {
                                                router.post(route('clearing-forwarding.declarations.verify-release', declId));
                                            }
                                        }}
                                    >
                                        Verify Customs Release Order
                                    </Button>
                                )}
                            </CardHeader>
                            <CardContent className="p-4">
                                {job.declarations && job.declarations[0] ? (
                                    <div className="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                                        <div className="p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border">
                                            <span className="text-muted-foreground font-medium">Release Order Ref:</span>
                                            <div className="font-mono font-bold text-sm text-slate-900 dark:text-slate-100 mt-1">
                                                {job.declarations[0].release_order_number || 'Awaiting Release'}
                                            </div>
                                        </div>
                                        <div className="p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border">
                                            <span className="text-muted-foreground font-medium">Customs Channel:</span>
                                            <div className="mt-1">
                                                <Badge className={`uppercase text-[10px] font-bold ${
                                                    job.declarations[0].customs_channel === 'green' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' :
                                                    job.declarations[0].customs_channel === 'yellow' ? 'bg-amber-100 text-amber-800 border-amber-300' :
                                                    job.declarations[0].customs_channel === 'red' ? 'bg-rose-100 text-rose-800 border-rose-300' :
                                                    'bg-blue-100 text-blue-800 border-blue-300'
                                                }`}>
                                                    {job.declarations[0].customs_channel || 'Green Channel'}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div className="p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border">
                                            <span className="text-muted-foreground font-medium">Release Date:</span>
                                            <div className="font-semibold text-sm mt-1">
                                                {job.declarations[0].release_date ? new Date(job.declarations[0].release_date).toLocaleDateString() : 'Pending'}
                                            </div>
                                        </div>
                                        <div className="p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border">
                                            <span className="text-muted-foreground font-medium">Compliance Verification:</span>
                                            <div className="mt-1 font-semibold text-emerald-700 flex items-center gap-1">
                                                {job.declarations[0].is_release_verified ? (
                                                    <>
                                                        <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                                                        <span>Authorized & Verified</span>
                                                    </>
                                                ) : (
                                                    <span className="text-amber-700">Pending Officer Verification</span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-xs text-muted-foreground text-center py-2">
                                        Customs release details will be unlocked once declaration is lodged.
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Customs Queries, Examination & Scan Events Table (SRS INT-MAN-012, INT-MAN-013) */}
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b flex flex-row items-center justify-between">
                                <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                    <Clock className="w-4 h-4 text-indigo-600" />
                                    Customs Examination, Queries & Incident Trail
                                </CardTitle>
                                <Button size="sm" variant="outline" onClick={() => setIsEventDialogOpen(true)} className="text-xs gap-1">
                                    <Plus className="w-3.5 h-3.5" /> Record Event
                                </Button>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50/50 text-xs">
                                            <TableHead>Event Type</TableHead>
                                            <TableHead>Customs Officer</TableHead>
                                            <TableHead>Location</TableHead>
                                            <TableHead>Instructions & Queries</TableHead>
                                            <TableHead>Outcome / Findings</TableHead>
                                            <TableHead>Timestamp</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {job.declarations?.[0]?.events && job.declarations[0].events.length > 0 ? (
                                            job.declarations[0].events.map((ev) => (
                                                <TableRow key={ev.id} className="text-xs">
                                                    <TableCell>
                                                        <Badge variant="outline" className="capitalize text-[10px] font-semibold">
                                                            {ev.event_type.replace('_', ' ')}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="font-medium text-slate-800">{ev.officer_name || 'TRA Examining Officer'}</TableCell>
                                                    <TableCell>{ev.location || 'TICTS Examination Yard'}</TableCell>
                                                    <TableCell className="max-w-xs">{ev.instructions || 'Routine compliance check'}</TableCell>
                                                    <TableCell className="text-emerald-700 font-semibold">{ev.findings || ev.outcome || 'Endorsed Satisfactory'}</TableCell>
                                                    <TableCell className="text-[11px] text-muted-foreground font-mono">
                                                        {new Date(ev.event_time).toLocaleString()}
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell colSpan={6} className="text-center py-6 text-slate-500 text-xs">
                                                    No customs queries or inspection holds recorded. Consignment processing smoothly.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 5: Transport & Haulage */}
                    <TabsContent value="transport" className="space-y-4">
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                        <Truck className="w-4 h-4 text-blue-600" /> Haulage Orders & Vehicle Dispatch
                                    </CardTitle>
                                    <CardDescription className="text-xs text-slate-500">
                                        Assigned transporters, truck plates, drivers, corridors, and freight costs.
                                    </CardDescription>
                                </div>
                                <Button size="sm" onClick={() => setIsTransportDialogOpen(true)} className="bg-[#1e3a5f] hover:bg-[#152a45] text-white text-xs gap-1">
                                    Issue Transport Order
                                </Button>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50/50">
                                            <TableHead className="text-xs">Order No</TableHead>
                                            <TableHead className="text-xs">Carrier / Transporter</TableHead>
                                            <TableHead className="text-xs">Truck & Driver</TableHead>
                                            <TableHead className="text-xs">Pickup & Destination</TableHead>
                                            <TableHead className="text-xs text-right">Freight Rate</TableHead>
                                            <TableHead className="text-xs">Status</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {job.transport_orders && job.transport_orders.length > 0 ? (
                                            job.transport_orders.map((t) => (
                                                <TableRow key={t.id}>
                                                    <TableCell className="font-mono font-bold text-xs text-indigo-700">
                                                        {t.order_number}
                                                    </TableCell>
                                                    <TableCell className="text-xs font-medium">{t.carrier_name}</TableCell>
                                                    <TableCell className="text-xs">
                                                        <div className="font-semibold text-slate-900">{t.vehicle_plate}</div>
                                                        <div className="text-[11px] text-slate-500">{t.driver_name} ({t.driver_phone})</div>
                                                    </TableCell>
                                                    <TableCell className="text-xs">
                                                        <div>{t.pickup_location}</div>
                                                        <div className="text-[11px] text-slate-500">&rarr; {t.delivery_destination}</div>
                                                    </TableCell>
                                                    <TableCell className="text-xs text-right font-semibold">
                                                        {formatMoney(t.agreed_freight_cost)}
                                                    </TableCell>
                                                    <TableCell className="text-xs">
                                                        <Badge variant="outline" className="capitalize text-[10px]">
                                                            {t.status}
                                                        </Badge>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell colSpan={6} className="text-center py-6 text-slate-500 text-xs">
                                                    No transport orders issued yet. Click "Issue Transport Order" to dispatch vehicle.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 6: In-Transit Tracking Events */}
                    <TabsContent value="tracking" className="space-y-4">
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b flex flex-row items-center justify-between">
                                <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                    <Clock className="w-4 h-4 text-slate-700" /> Milestone Tracking Timeline
                                </CardTitle>
                                <Button size="sm" variant="outline" onClick={() => setIsMilestoneDialogOpen(true)} className="text-xs gap-1">
                                    Log Milestone
                                </Button>
                            </CardHeader>
                            <CardContent className="p-5">
                                {job.tracking_events && job.tracking_events.length > 0 ? (
                                    <div className="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                                        {job.tracking_events.map((ev) => (
                                            <div key={ev.id} className="relative group">
                                                <div className="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-indigo-600 border-2 border-white ring-4 ring-indigo-50" />
                                                <div className="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs">
                                                    <div className="flex items-center justify-between">
                                                        <span className="font-bold text-slate-900 uppercase tracking-wide">
                                                            {ev.milestone_code.replace('_', ' ')}
                                                        </span>
                                                        <span className="text-[10px] text-slate-400">
                                                            {new Date(ev.event_time).toLocaleString()}
                                                        </span>
                                                    </div>
                                                    <div className="text-slate-600 font-medium mt-1 flex items-center gap-1">
                                                        <MapPin className="w-3 h-3 text-slate-400" /> {ev.location_name}
                                                    </div>
                                                    {ev.remarks && <p className="text-slate-700 mt-1.5 leading-relaxed">{ev.remarks}</p>}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-xs text-slate-500 py-4 text-center">No milestone events logged yet.</p>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 7: Delivery & POD */}
                    <TabsContent value="delivery" className="space-y-4">
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                        <CheckCircle2 className="w-4 h-4 text-emerald-600" /> Delivery Notes & Signed Proof of Delivery (POD)
                                    </CardTitle>
                                    <CardDescription className="text-xs text-slate-500">
                                        Official cargo handover, condition verification, recipient acknowledgment, and tamper-proof hash.
                                    </CardDescription>
                                </div>
                                <Button size="sm" onClick={() => setIsDeliveryDialogOpen(true)} className="bg-emerald-600 hover:bg-emerald-700 text-white text-xs gap-1">
                                    Record Delivery & POD
                                </Button>
                            </CardHeader>
                            <CardContent className="p-5">
                                {job.deliveries && job.deliveries.length > 0 ? (
                                    <div className="space-y-4">
                                        {job.deliveries.map((d) => (
                                            <div key={d.id} className="p-4 border rounded-xl bg-slate-50 space-y-3">
                                                <div className="flex items-center justify-between border-b pb-2">
                                                    <div>
                                                        <span className="font-mono font-bold text-sm text-slate-900">{d.delivery_number}</span>
                                                        <span className="text-xs text-slate-500 ml-2">&bull; Delivered: {d.delivery_date}</span>
                                                    </div>
                                                    <Badge className="bg-emerald-100 text-emerald-800 capitalize">{d.status.replace('_', ' ')}</Badge>
                                                </div>

                                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                                    <div>
                                                        <span className="text-slate-500 font-medium">Delivery Point:</span>
                                                        <div className="font-semibold text-slate-800 mt-0.5">{d.delivery_address}</div>
                                                    </div>
                                                    <div>
                                                        <span className="text-slate-500 font-medium">Packages Delivered:</span>
                                                        <div className="font-semibold text-slate-800 mt-0.5">{d.packages_delivered} units</div>
                                                    </div>
                                                    <div>
                                                        <span className="text-slate-500 font-medium">Cargo Condition:</span>
                                                        <div className="font-semibold text-emerald-800 mt-0.5 capitalize">{d.cargo_condition.replace('_', ' ')}</div>
                                                    </div>
                                                </div>

                                                {d.proof_of_delivery && (
                                                    <div className="p-3 bg-white rounded-lg border border-slate-200 text-xs space-y-1">
                                                        <div className="font-bold text-slate-900 flex items-center justify-between">
                                                            <span>Received by: {d.proof_of_delivery.recipient_name} ({d.proof_of_delivery.recipient_id_type}: {d.proof_of_delivery.recipient_id_number || 'Verified'})</span>
                                                            <span className="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                                                Official Stamp & E-Signature Confirmed
                                                            </span>
                                                        </div>
                                                        <div className="text-[10px] text-slate-400 font-mono">
                                                            Verification Hash: {d.proof_of_delivery.verification_hash}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-6 text-slate-500 text-xs">
                                        No delivery or POD recorded yet. Use the button above upon destination handover.
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 8: Disbursements & Job Costing */}
                    <TabsContent value="costing" className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <Card className="border-slate-200 shadow-sm">
                                <CardContent className="p-4">
                                    <span className="text-xs text-slate-500 font-semibold uppercase">Customer Advance</span>
                                    <div className="text-xl font-bold text-indigo-900 mt-1">
                                        {formatMoney(job.job_costing?.customer_advance_received || 0)}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="border-slate-200 shadow-sm">
                                <CardContent className="p-4">
                                    <span className="text-xs text-slate-500 font-semibold uppercase">Total Disbursements</span>
                                    <div className="text-xl font-bold text-slate-800 mt-1">
                                        {formatMoney(job.job_costing?.total_disbursements_and_direct_costs || 0)}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="border-slate-200 shadow-sm">
                                <CardContent className="p-4">
                                    <span className="text-xs text-slate-500 font-semibold uppercase">Agency Fee Revenue</span>
                                    <div className="text-xl font-bold text-emerald-700 mt-1">
                                        {formatMoney(job.job_costing?.total_service_revenue || 0)}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="border-slate-200 shadow-sm">
                                <CardContent className="p-4">
                                    <span className="text-xs text-slate-500 font-semibold uppercase">Balance Due</span>
                                    <div className="text-xl font-bold text-blue-900 mt-1">
                                        {formatMoney(job.job_costing?.balance_due_from_customer || 0)}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Disbursements Table */}
                        <Card className="border-slate-200 shadow-sm">
                            <CardHeader className="bg-slate-50 pb-2 border-b flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle className="text-xs font-bold uppercase text-slate-700">
                                        Statutory & Third-Party Disbursements
                                    </CardTitle>
                                    <CardDescription className="text-xs text-slate-500">
                                        Actual payments made on behalf of customer (TRA duties, TPA port wharfage, shipping DO, etc.).
                                    </CardDescription>
                                </div>
                                <div className="flex gap-2">
                                    <Button size="sm" variant="outline" onClick={() => setIsReconcileDialogOpen(true)} className="text-xs">
                                        Reconcile Advance
                                    </Button>
                                    <Button size="sm" onClick={() => setIsDisbursementDialogOpen(true)} className="bg-slate-900 hover:bg-slate-800 text-white text-xs gap-1">
                                        Record Disbursement
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-slate-50/50">
                                            <TableHead className="text-xs">Date</TableHead>
                                            <TableHead className="text-xs">Payee / Authority</TableHead>
                                            <TableHead className="text-xs">Category</TableHead>
                                            <TableHead className="text-xs">Description</TableHead>
                                            <TableHead className="text-xs font-mono">Reference</TableHead>
                                            <TableHead className="text-xs text-right">Amount (TZS)</TableHead>
                                            <TableHead className="text-xs text-center">Status</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {job.disbursements && job.disbursements.length > 0 ? (
                                            job.disbursements.map((d) => (
                                                <TableRow key={d.id}>
                                                    <TableCell className="text-xs">{d.payment_date}</TableCell>
                                                    <TableCell className="text-xs font-semibold text-slate-900">{d.payee_name}</TableCell>
                                                    <TableCell className="text-xs capitalize text-slate-600">{d.charge_category.replace('_', ' ')}</TableCell>
                                                    <TableCell className="text-xs">{d.description}</TableCell>
                                                    <TableCell className="text-xs font-mono">{d.payment_reference || '-'}</TableCell>
                                                    <TableCell className="text-xs text-right font-semibold">
                                                        {formatMoney(d.amount_tzs)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-center">
                                                        <Badge className="bg-emerald-100 text-emerald-800 text-[10px]">Reconciled</Badge>
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        ) : (
                                            <TableRow>
                                                <TableCell colSpan={7} className="text-center py-6 text-slate-500 text-xs">
                                                    No disbursements recorded for this job.
                                                </TableCell>
                                            </TableRow>
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>

            {/* Dialog: Update Status */}
            <Dialog open={isStatusDialogOpen} onOpenChange={setIsStatusDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Update Job Status</DialogTitle>
                        <DialogDescription>
                            Advance this cargo assignment to the next operational milestone.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            statusForm.patch(route('clearing-forwarding.jobs.update-status', job.id), {
                                onSuccess: () => setIsStatusDialogOpen(false),
                            });
                        }}
                        className="space-y-4"
                    >
                        <div>
                            <Label className="text-xs font-semibold">New Operational Status</Label>
                            <select
                                value={statusForm.data.status}
                                onChange={(e) => statusForm.setData('status', e.target.value as any)}
                                className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                            >
                                <option value="documents_pending">Documents Pending</option>
                                <option value="compliance_check">Compliance Check</option>
                                <option value="ready_for_declaration">Ready for Declaration</option>
                                <option value="declaration_submitted">Declaration Submitted (TANCIS)</option>
                                <option value="assessed">Customs Assessed</option>
                                <option value="duties_paid">Duties Paid / Settled</option>
                                <option value="cargo_released">Cargo Released at Port / Terminal</option>
                                <option value="in_transit">In Transit Haulage</option>
                                <option value="delivered">Delivered (Handover Complete)</option>
                                <option value="completed">Completed & Closed</option>
                            </select>
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Status Update Remarks</Label>
                            <Textarea
                                value={statusForm.data.notes}
                                onChange={(e) => statusForm.setData('notes', e.target.value)}
                                placeholder="Optional operational notes..."
                                className="text-xs mt-1"
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsStatusDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" className="bg-[#1e3a5f] hover:bg-[#152a45] text-white">Save Status</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Dialog: Record Disbursement */}
            <Dialog open={isDisbursementDialogOpen} onOpenChange={setIsDisbursementDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Record Statutory or Third-Party Disbursement</DialogTitle>
                        <DialogDescription>
                            Log official payment made on behalf of customer.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            disbursementForm.post(route('clearing-forwarding.jobs.disbursements.store', job.id), {
                                onSuccess: () => {
                                    setIsDisbursementDialogOpen(false);
                                    disbursementForm.reset();
                                },
                            });
                        }}
                        className="space-y-3"
                    >
                        <div>
                            <Label className="text-xs font-semibold">Payee Authority / Entity *</Label>
                            <Input
                                placeholder="TRA, TPA, TICTS, Maersk..."
                                value={disbursementForm.data.payee_name}
                                onChange={(e) => disbursementForm.setData('payee_name', e.target.value)}
                                className="text-xs mt-1"
                                required
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">Category</Label>
                                <select
                                    value={disbursementForm.data.charge_category}
                                    onChange={(e) => disbursementForm.setData('charge_category', e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="statutory_duty">TRA Customs Duty & VAT</option>
                                    <option value="port_charges">TPA Wharfage & Port Fees</option>
                                    <option value="shipping_line">Shipping Line Delivery Order</option>
                                    <option value="terminal_handling">ICD / Terminal Handling</option>
                                    <option value="inspection_fee">TBS / TMDA Inspection</option>
                                </select>
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">Amount (TZS) *</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    placeholder="4500000"
                                    value={disbursementForm.data.disbursed_amount}
                                    onChange={(e) => disbursementForm.setData('disbursed_amount', e.target.value)}
                                    className="text-xs mt-1"
                                    required
                                />
                            </div>
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Payment Reference / Control No</Label>
                            <Input
                                placeholder="e.g. GePG 991200881920 or Cheque No"
                                value={disbursementForm.data.payment_reference}
                                onChange={(e) => disbursementForm.setData('payment_reference', e.target.value)}
                                className="text-xs mt-1"
                            />
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Description</Label>
                            <Input
                                placeholder="Settlement details..."
                                value={disbursementForm.data.description}
                                onChange={(e) => disbursementForm.setData('description', e.target.value)}
                                className="text-xs mt-1"
                                required
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsDisbursementDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" className="bg-[#1e3a5f] hover:bg-[#152a45] text-white">Save Disbursement</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Dialog: Issue Transport Order */}
            <Dialog open={isTransportDialogOpen} onOpenChange={setIsTransportDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Issue Transport Order & Dispatch</DialogTitle>
                        <DialogDescription>
                            Assign haulage truck, driver, corridor, and delivery instructions.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            transportForm.post(route('clearing-forwarding.jobs.transport-orders.store', job.id), {
                                onSuccess: () => {
                                    setIsTransportDialogOpen(false);
                                    transportForm.reset();
                                },
                            });
                        }}
                        className="space-y-3"
                    >
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">Truck Plate *</Label>
                                <Input
                                    placeholder="T 842 DKM / T 119 CWB"
                                    value={transportForm.data.vehicle_plate}
                                    onChange={(e) => transportForm.setData('vehicle_plate', e.target.value)}
                                    className="text-xs mt-1"
                                    required
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">Driver Name *</Label>
                                <Input
                                    placeholder="Driver full name"
                                    value={transportForm.data.driver_name}
                                    onChange={(e) => transportForm.setData('driver_name', e.target.value)}
                                    className="text-xs mt-1"
                                    required
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">Driver Phone</Label>
                                <Input
                                    placeholder="+255 754 000 000"
                                    value={transportForm.data.driver_phone}
                                    onChange={(e) => transportForm.setData('driver_phone', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">Agreed Freight Cost (TZS)</Label>
                                <Input
                                    type="number"
                                    placeholder="1400000"
                                    value={transportForm.data.agreed_freight_cost}
                                    onChange={(e) => transportForm.setData('agreed_freight_cost', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Pickup Location *</Label>
                            <Input
                                value={transportForm.data.pickup_location}
                                onChange={(e) => transportForm.setData('pickup_location', e.target.value)}
                                className="text-xs mt-1"
                                required
                            />
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Delivery Destination *</Label>
                            <Input
                                value={transportForm.data.delivery_destination}
                                onChange={(e) => transportForm.setData('delivery_destination', e.target.value)}
                                className="text-xs mt-1"
                                required
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsTransportDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" className="bg-[#1e3a5f] hover:bg-[#152a45] text-white">Dispatch Vehicle</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Dialog: Log Milestone */}
            <Dialog open={isMilestoneDialogOpen} onOpenChange={setIsMilestoneDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Log In-Transit Milestone</DialogTitle>
                        <DialogDescription>
                            Record checkpoint clearance, border crossing, or arrival.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            milestoneForm.post(route('clearing-forwarding.jobs.tracking-events.store', job.id), {
                                onSuccess: () => {
                                    setIsMilestoneDialogOpen(false);
                                    milestoneForm.reset();
                                },
                            });
                        }}
                        className="space-y-3"
                    >
                        <div>
                            <Label className="text-xs font-semibold">Milestone</Label>
                            <select
                                value={milestoneForm.data.milestone_code}
                                onChange={(e) => milestoneForm.setData('milestone_code', e.target.value)}
                                className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                            >
                                <option value="checkpoint_cleared">Checkpoint / Weighbridge Cleared</option>
                                <option value="border_arrived">Arrived at Border Post</option>
                                <option value="border_cleared">Border Clearance Endorsed</option>
                                <option value="arrived_destination">Arrived Destination City / Yard</option>
                                <option value="customs_inspection">Physical Examination Conducted</option>
                            </select>
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Location Name *</Label>
                            <Input
                                placeholder="e.g. Isaka Dry Port, Rusumo OSBP, etc."
                                value={milestoneForm.data.location_name}
                                onChange={(e) => milestoneForm.setData('location_name', e.target.value)}
                                className="text-xs mt-1"
                                required
                            />
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Remarks</Label>
                            <Textarea
                                placeholder="Condition, weight, transit seal status..."
                                value={milestoneForm.data.remarks}
                                onChange={(e) => milestoneForm.setData('remarks', e.target.value)}
                                className="text-xs mt-1"
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsMilestoneDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" className="bg-[#1e3a5f] hover:bg-[#152a45] text-white">Save Milestone</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Dialog: Record Delivery & POD */}
            <Dialog open={isDeliveryDialogOpen} onOpenChange={setIsDeliveryDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Record Destination Delivery & Signed POD</DialogTitle>
                        <DialogDescription>
                            Confirm cargo handover, recipient verification, and archive proof of delivery.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            deliveryForm.post(route('clearing-forwarding.jobs.deliveries.store', job.id), {
                                onSuccess: () => {
                                    setIsDeliveryDialogOpen(false);
                                    deliveryForm.reset();
                                },
                            });
                        }}
                        className="space-y-3"
                    >
                        <div>
                            <Label className="text-xs font-semibold">Recipient Full Name *</Label>
                            <Input
                                value={deliveryForm.data.recipient_name}
                                onChange={(e) => deliveryForm.setData('recipient_name', e.target.value)}
                                className="text-xs mt-1"
                                required
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">ID Type</Label>
                                <select
                                    value={deliveryForm.data.recipient_id_type}
                                    onChange={(e) => deliveryForm.setData('recipient_id_type', e.target.value)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="national_id_nida">National ID (NIDA)</option>
                                    <option value="passport">Passport</option>
                                    <option value="driving_license">Driving License</option>
                                    <option value="staff_id">Official Company Staff ID</option>
                                </select>
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">ID Number</Label>
                                <Input
                                    placeholder="NIDA / ID number"
                                    value={deliveryForm.data.recipient_id_number}
                                    onChange={(e) => deliveryForm.setData('recipient_id_number', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">Packages Handed Over *</Label>
                                <Input
                                    type="number"
                                    value={deliveryForm.data.packages_delivered}
                                    onChange={(e) => deliveryForm.setData('packages_delivered', Number(e.target.value))}
                                    className="text-xs mt-1"
                                    required
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">Cargo Condition *</Label>
                                <select
                                    value={deliveryForm.data.cargo_condition}
                                    onChange={(e) => deliveryForm.setData('cargo_condition', e.target.value as any)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-white mt-1"
                                >
                                    <option value="sound_and_intact">Sound & Intact (Clean Receipt)</option>
                                    <option value="shortage">Shortage Detected</option>
                                    <option value="damaged">Damage Observed</option>
                                    <option value="seal_broken">Seal Discrepancy</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Delivery Point / Address</Label>
                            <Input
                                value={deliveryForm.data.delivery_address}
                                onChange={(e) => deliveryForm.setData('delivery_address', e.target.value)}
                                className="text-xs mt-1"
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsDeliveryDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" className="bg-emerald-600 hover:bg-emerald-700 text-white">Confirm Delivery & POD</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Dialog: Declaration & TANCIS Evidence (SRS Section 18) */}
            <Dialog open={isDeclarationDialogOpen} onOpenChange={setIsDeclarationDialogOpen}>
                <DialogContent className="max-w-lg max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <ShieldCheck className="w-5 h-5 text-indigo-600" />
                            Update Customs Declaration & TANCIS Lodgement
                        </DialogTitle>
                        <DialogDescription className="text-xs">
                            Record external TANCIS submission particulars, official assessment breakdown, and release documents.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            declarationForm.post(route('clearing-forwarding.jobs.declarations.store', job.id), {
                                onSuccess: () => setIsDeclarationDialogOpen(false),
                            });
                        }}
                        className="space-y-3"
                    >
                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">TANSAD / Decl No</Label>
                                <Input
                                    placeholder="26DAR-IM4-009182"
                                    value={declarationForm.data.declaration_number}
                                    onChange={(e) => declarationForm.setData('declaration_number', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">TANCIS Ref</Label>
                                <Input
                                    placeholder="TANCIS-2026-08129"
                                    value={declarationForm.data.tancis_reference}
                                    onChange={(e) => declarationForm.setData('tancis_reference', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">Customs Station</Label>
                                <Input
                                    value={declarationForm.data.customs_office}
                                    onChange={(e) => declarationForm.setData('customs_office', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">TANCIS Submission Date</Label>
                                <Input
                                    type="date"
                                    value={declarationForm.data.external_submission_time}
                                    onChange={(e) => declarationForm.setData('external_submission_time', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                        </div>

                        <div className="p-2.5 bg-slate-50 dark:bg-slate-900 rounded-lg border space-y-2">
                            <Label className="text-xs font-bold text-slate-800 dark:text-slate-200">
                                Official Assessed Tax Breakdown (TZS)
                            </Label>
                            <div className="grid grid-cols-2 gap-2">
                                <div>
                                    <Label className="text-[11px]">Total Assessed Payable *</Label>
                                    <Input
                                        type="number"
                                        placeholder="Total Payable"
                                        value={declarationForm.data.assessed_amount_tzs}
                                        onChange={(e) => declarationForm.setData('assessed_amount_tzs', e.target.value)}
                                        className="text-xs mt-1 font-semibold"
                                    />
                                </div>
                                <div>
                                    <Label className="text-[11px]">GePG Control Number *</Label>
                                    <Input
                                        placeholder="991200881920"
                                        value={declarationForm.data.payment_control_number}
                                        onChange={(e) => declarationForm.setData('payment_control_number', e.target.value)}
                                        className="text-xs mt-1 font-mono font-bold"
                                    />
                                </div>
                            </div>
                            <div className="grid grid-cols-3 gap-2 pt-1 text-[11px]">
                                <div>
                                    <Label className="text-[10px]">Import Duty (TZS)</Label>
                                    <Input
                                        type="number"
                                        value={declarationForm.data.import_duty_assessed_tzs}
                                        onChange={(e) => declarationForm.setData('import_duty_assessed_tzs', e.target.value)}
                                        className="text-xs mt-0.5 h-8"
                                    />
                                </div>
                                <div>
                                    <Label className="text-[10px]">VAT (18% TZS)</Label>
                                    <Input
                                        type="number"
                                        value={declarationForm.data.vat_assessed_tzs}
                                        onChange={(e) => declarationForm.setData('vat_assessed_tzs', e.target.value)}
                                        className="text-xs mt-0.5 h-8"
                                    />
                                </div>
                                <div>
                                    <Label className="text-[10px]">RDL & CPF (TZS)</Label>
                                    <Input
                                        type="number"
                                        value={declarationForm.data.rdl_assessed_tzs}
                                        onChange={(e) => declarationForm.setData('rdl_assessed_tzs', e.target.value)}
                                        className="text-xs mt-0.5 h-8"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">Release Order No</Label>
                                <Input
                                    placeholder="REL-TRA-DAR-2026-778"
                                    value={declarationForm.data.release_order_number}
                                    onChange={(e) => declarationForm.setData('release_order_number', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">Declaration Status (SRS §19)</Label>
                                <select
                                    value={declarationForm.data.status}
                                    onChange={(e) => declarationForm.setData('status', e.target.value as any)}
                                    className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-background mt-1"
                                >
                                    <option value="draft">Draft Preparation</option>
                                    <option value="worksheet_generated">Worksheet Generated</option>
                                    <option value="tancis_submitted_pending_evidence">Submitted in TANCIS (Evidence Pending)</option>
                                    <option value="lodgement_evidence_recorded">Lodgement Evidence Recorded</option>
                                    <option value="queried">Queried by Customs</option>
                                    <option value="amended">Declaration Amended</option>
                                    <option value="assessed">Official Assessment Recorded</option>
                                    <option value="payment_pending">Payment Pending</option>
                                    <option value="paid">Duties Paid / Settled</option>
                                    <option value="customs_released">Customs Released</option>
                                </select>
                            </div>
                        </div>

                        <div className="border-t pt-2 space-y-2 text-xs">
                            <span className="font-semibold text-slate-800 dark:text-slate-200 block">
                                Evidence Document Uploads (PDF / Scans)
                            </span>
                            <div className="grid grid-cols-2 gap-2">
                                <div>
                                    <Label className="text-[11px]">Lodgement Evidence</Label>
                                    <input
                                        type="file"
                                        className="text-[10px] mt-1 w-full"
                                        onChange={(e) => declarationForm.setData('lodgement_evidence', e.target.files?.[0] || null)}
                                    />
                                </div>
                                <div>
                                    <Label className="text-[11px]">Assessment Notice</Label>
                                    <input
                                        type="file"
                                        className="text-[10px] mt-1 w-full"
                                        onChange={(e) => declarationForm.setData('assessment_document', e.target.files?.[0] || null)}
                                    />
                                </div>
                            </div>
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setIsDeclarationDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" disabled={declarationForm.processing} className="bg-indigo-700 hover:bg-indigo-800 text-white">
                                Save TANCIS Declaration
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Dialog: Customs Query / Incident Event (SRS INT-MAN-013) */}
            <Dialog open={isEventDialogOpen} onOpenChange={setIsEventDialogOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <AlertCircle className="w-5 h-5 text-amber-600" />
                            Record Customs Event / Query
                        </DialogTitle>
                        <DialogDescription className="text-xs">
                            Log official customs examination, scan result, query notice, or compliance instructions.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            if (!job.declarations?.[0]) return;
                            eventForm.post(route('clearing-forwarding.jobs.declarations.events.store', { cfJob: job.id, declaration: job.declarations[0].id }), {
                                onSuccess: () => {
                                    setIsEventDialogOpen(false);
                                    eventForm.reset();
                                },
                            });
                        }}
                        className="space-y-3"
                    >
                        <div>
                            <Label className="text-xs font-semibold">Event Type *</Label>
                            <select
                                value={eventForm.data.event_type}
                                onChange={(e) => eventForm.setData('event_type', e.target.value as any)}
                                className="w-full h-9 text-xs border border-slate-300 rounded-md px-2 bg-background mt-1"
                            >
                                <option value="query_raised">Customs Query Raised</option>
                                <option value="query_answered">Query Response Submitted</option>
                                <option value="physical_inspection">Physical Examination Conducted</option>
                                <option value="scanning_scheduled">Container Scanning Scheduled</option>
                                <option value="release_granted">Release Granted at Gate</option>
                                <option value="customs_hold">Customs Administrative Hold</option>
                            </select>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <div>
                                <Label className="text-xs font-semibold">Examining Officer</Label>
                                <Input
                                    placeholder="Officer Name / ID"
                                    value={eventForm.data.officer_name}
                                    onChange={(e) => eventForm.setData('officer_name', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                            <div>
                                <Label className="text-xs font-semibold">Location</Label>
                                <Input
                                    value={eventForm.data.location}
                                    onChange={(e) => eventForm.setData('location', e.target.value)}
                                    className="text-xs mt-1"
                                />
                            </div>
                        </div>

                        <div>
                            <Label className="text-xs font-semibold">Instructions / Queries</Label>
                            <Textarea
                                rows={2}
                                placeholder="Describe query raised or verification instructions..."
                                value={eventForm.data.instructions}
                                onChange={(e) => eventForm.setData('instructions', e.target.value)}
                                className="text-xs mt-1"
                            />
                        </div>

                        <div>
                            <Label className="text-xs font-semibold">Findings / Outcome</Label>
                            <Textarea
                                rows={2}
                                placeholder="Inspection results or declarant response..."
                                value={eventForm.data.findings}
                                onChange={(e) => eventForm.setData('findings', e.target.value)}
                                className="text-xs mt-1"
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setIsEventDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" disabled={eventForm.processing} className="bg-amber-600 hover:bg-amber-700 text-white">
                                Record Event
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Dialog: Reconcile Costing */}
            <Dialog open={isReconcileDialogOpen} onOpenChange={setIsReconcileDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Reconcile Customer Advance & Costing</DialogTitle>
                        <DialogDescription>
                            Allocate customer advances and balance the final job account.
                        </DialogDescription>
                    </DialogHeader>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            reconcileForm.post(route('clearing-forwarding.jobs.reconcile', job.id), {
                                onSuccess: () => setIsReconcileDialogOpen(false),
                            });
                        }}
                        className="space-y-3"
                    >
                        <div>
                            <Label className="text-xs font-semibold">Customer Advance Received (TZS) *</Label>
                            <Input
                                type="number"
                                value={reconcileForm.data.customer_advance_received}
                                onChange={(e) => reconcileForm.setData('customer_advance_received', Number(e.target.value))}
                                className="text-xs mt-1"
                                required
                            />
                        </div>
                        <div>
                            <Label className="text-xs font-semibold">Agency Service Revenue (TZS)</Label>
                            <Input
                                type="number"
                                value={reconcileForm.data.agency_fee_revenue}
                                onChange={(e) => reconcileForm.setData('agency_fee_revenue', Number(e.target.value))}
                                className="text-xs mt-1"
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsReconcileDialogOpen(false)}>Cancel</Button>
                            <Button type="submit" className="bg-indigo-600 hover:bg-indigo-700 text-white">Reconcile Account</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}

