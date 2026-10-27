import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { 
    Cpu, 
    Layers, 
    TrendingDown, 
    ShieldCheck, 
    Wrench, 
    ArrowRightLeft, 
    Trash2, 
    Plus, 
    Search, 
    CheckCircle2, 
    Clock, 
    AlertTriangle, 
    Building2, 
    User, 
    DollarSign,
    RefreshCw,
    QrCode,
    FileText,
    SlidersHorizontal
} from 'lucide-react';

interface AssetProps {
    metrics: {
        total_assets: number;
        total_cost: number;
        total_current_value: number;
        total_depreciation: number;
        active_warranties_count: number;
        open_tickets_count: number;
    };
    assets: any[];
    categories: any[];
    custodians: any[];
    depreciations: any[];
    transfers: any[];
    disposals: any[];
    warranties: any[];
    service_tickets: any[];
}

export default function AssetsIndex({
    metrics = {
        total_assets: 0,
        total_cost: 0,
        total_current_value: 0,
        total_depreciation: 0,
        active_warranties_count: 0,
        open_tickets_count: 0,
    },
    assets = [],
    categories = [],
    custodians = [],
    depreciations = [],
    transfers = [],
    disposals = [],
    warranties = [],
    service_tickets = [],
}: AssetProps) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState('assets');
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');

    // Modals
    const [showAssetModal, setShowAssetModal] = useState(false);
    const [showDepreciationModal, setShowDepreciationModal] = useState(false);
    const [showTransferModal, setShowTransferModal] = useState(false);
    const [showDisposalModal, setShowDisposalModal] = useState(false);
    const [showWarrantyModal, setShowWarrantyModal] = useState(false);
    const [showTicketModal, setShowTicketModal] = useState(false);
    const [selectedAsset, setSelectedAsset] = useState<any>(null);

    // Form: Create Asset
    const assetForm = useForm({
        name: '',
        asset_category_id: '',
        purchase_date: new Date().toISOString().split('T')[0],
        purchase_cost: '',
        salvage_value: '0',
        depreciation_method: 'straight_line',
        depreciation_rate: '20',
        useful_life_years: '5',
        location: '',
        custodian_id: '',
        serial_number: '',
        warranty_expiry_date: '',
        notes: '',
    });

    // Form: Run Depreciation
    const depForm = useForm({
        period: new Date().toISOString().slice(0, 7), // YYYY-MM
        asset_id: '',
    });

    // Form: Transfer Asset
    const transferForm = useForm({
        asset_id: '',
        transfer_date: new Date().toISOString().split('T')[0],
        to_location: '',
        to_custodian_id: '',
        reason: '',
    });

    // Form: Dispose Asset
    const disposalForm = useForm({
        asset_id: '',
        disposal_date: new Date().toISOString().split('T')[0],
        disposal_type: 'sale',
        sale_amount: '0',
        reason: '',
    });

    // Form: Register Warranty
    const warrantyForm = useForm({
        serial_number: '',
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        start_date: new Date().toISOString().split('T')[0],
        warranty_period_months: '12',
        warranty_terms: 'Standard Hardware Warranty',
    });

    // Form: Service Ticket
    const ticketForm = useForm({
        product_name: '',
        serial_number: '',
        customer_name: '',
        contact_phone: '',
        service_type: 'warranty_claim',
        issue_reported: '',
        vendor_repair: false,
        vendor_name: '',
        estimated_cost: '0',
    });

    const handleCreateAsset = (e: React.FormEvent) => {
        e.preventDefault();
        assetForm.post(route('assets.store'), {
            onSuccess: () => {
                setShowAssetModal(false);
                assetForm.reset();
            },
        });
    };

    const handleRunDepreciation = (e: React.FormEvent) => {
        e.preventDefault();
        depForm.post(route('assets.depreciation.run'), {
            onSuccess: () => {
                setShowDepreciationModal(false);
            },
        });
    };

    const handleTransfer = (e: React.FormEvent) => {
        e.preventDefault();
        transferForm.post(route('assets.transfers.store'), {
            onSuccess: () => {
                setShowTransferModal(false);
                transferForm.reset();
            },
        });
    };

    const handleDisposal = (e: React.FormEvent) => {
        e.preventDefault();
        disposalForm.post(route('assets.disposals.store'), {
            onSuccess: () => {
                setShowDisposalModal(false);
                disposalForm.reset();
            },
        });
    };

    const handleRegisterWarranty = (e: React.FormEvent) => {
        e.preventDefault();
        warrantyForm.post(route('assets.warranties.store'), {
            onSuccess: () => {
                setShowWarrantyModal(false);
                warrantyForm.reset();
            },
        });
    };

    const handleCreateTicket = (e: React.FormEvent) => {
        e.preventDefault();
        ticketForm.post(route('assets.service-tickets.store'), {
            onSuccess: () => {
                setShowTicketModal(false);
                ticketForm.reset();
            },
        });
    };

    const handleUpdateTicketStatus = (ticketId: number, status: string) => {
        router.patch(route('assets.service-tickets.status', ticketId), {
            status,
        });
    };

    const openTransfer = (asset: any) => {
        setSelectedAsset(asset);
        transferForm.setData({
            asset_id: asset.id.toString(),
            transfer_date: new Date().toISOString().split('T')[0],
            to_location: asset.location || '',
            to_custodian_id: asset.custodian_id ? asset.custodian_id.toString() : '',
            reason: '',
        });
        setShowTransferModal(true);
    };

    const openDisposal = (asset: any) => {
        setSelectedAsset(asset);
        disposalForm.setData({
            asset_id: asset.id.toString(),
            disposal_date: new Date().toISOString().split('T')[0],
            disposal_type: 'sale',
            sale_amount: '0',
            reason: '',
        });
        setShowDisposalModal(true);
    };

    // Filter assets
    const filteredAssets = assets.filter((item) => {
        const matchesSearch = 
            (item.name || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
            (item.asset_code || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
            (item.location || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
            (item.serial_number || '').toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = statusFilter === 'all' || item.status === statusFilter;
        return matchesSearch && matchesStatus;
    });

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'active':
                return <Badge className="bg-emerald-500/10 text-emerald-600 border-emerald-500/20">{t('Active')}</Badge>;
            case 'under_maintenance':
                return <Badge className="bg-amber-500/10 text-amber-600 border-amber-500/20">{t('In Maintenance')}</Badge>;
            case 'transferred':
                return <Badge className="bg-blue-500/10 text-blue-600 border-blue-500/20">{t('Transferred')}</Badge>;
            case 'disposed':
                return <Badge className="bg-purple-500/10 text-purple-600 border-purple-500/20">{t('Disposed')}</Badge>;
            case 'written_off':
                return <Badge className="bg-red-500/10 text-red-600 border-red-500/20">{t('Written Off')}</Badge>;
            case 'expired':
                return <Badge className="bg-slate-500/10 text-slate-600 border-slate-500/20">{t('Expired')}</Badge>;
            case 'received':
                return <Badge className="bg-sky-500/10 text-sky-600 border-sky-500/20">{t('Received')}</Badge>;
            case 'diagnosing':
                return <Badge className="bg-amber-500/10 text-amber-600 border-amber-500/20">{t('Diagnosing')}</Badge>;
            case 'sent_to_vendor':
                return <Badge className="bg-indigo-500/10 text-indigo-600 border-indigo-500/20">{t('Vendor RMA')}</Badge>;
            case 'repaired':
                return <Badge className="bg-emerald-500/10 text-emerald-600 border-emerald-500/20">{t('Repaired')}</Badge>;
            case 'delivered_to_customer':
                return <Badge className="bg-teal-500/10 text-teal-600 border-teal-500/20">{t('Delivered')}</Badge>;
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Fixed Assets & Warranty Servicing')} />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                            <Cpu className="h-7 w-7 text-primary" />
                            {t('Fixed Assets & Equipment Servicing')}
                        </h1>
                        <p className="text-sm text-muted-foreground mt-1">
                            {t('Enterprise fixed asset lifecycle, automated depreciation in TZS, warranty tracking, and equipment repair RMA')}
                        </p>
                    </div>
                    <div className="flex flex-wrap items-center gap-3">
                        <Button 
                            variant="outline" 
                            className="flex items-center gap-2"
                            onClick={() => setShowDepreciationModal(true)}
                        >
                            <TrendingDown className="h-4 w-4 text-amber-500" />
                            {t('Run Depreciation')}
                        </Button>
                        <Button 
                            variant="outline"
                            className="flex items-center gap-2"
                            onClick={() => setShowWarrantyModal(true)}
                        >
                            <ShieldCheck className="h-4 w-4 text-emerald-500" />
                            {t('Register Warranty')}
                        </Button>
                        <Button 
                            variant="outline"
                            className="flex items-center gap-2"
                            onClick={() => setShowTicketModal(true)}
                        >
                            <Wrench className="h-4 w-4 text-blue-500" />
                            {t('Service Ticket (RMA)')}
                        </Button>
                        <Button 
                            className="flex items-center gap-2 bg-primary text-primary-foreground"
                            onClick={() => setShowAssetModal(true)}
                        >
                            <Plus className="h-4 w-4" />
                            {t('Register Asset')}
                        </Button>
                    </div>
                </div>

                {/* 4 Top KPI Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="border-border">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Total Fixed Assets Cost')}
                            </CardTitle>
                            <Cpu className="h-5 w-5 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {formatCurrency(metrics.total_cost)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {metrics.total_assets} {t('registered physical assets')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Net Book Value (Current)')}
                            </CardTitle>
                            <Layers className="h-5 w-5 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">
                                {formatCurrency(metrics.total_current_value)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Net carrying value on Balance Sheet')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Accumulated Depreciation')}
                            </CardTitle>
                            <TrendingDown className="h-5 w-5 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-600">
                                {formatCurrency(metrics.total_depreciation)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Depreciated to date in TZS')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Active Warranties & RMA')}
                            </CardTitle>
                            <ShieldCheck className="h-5 w-5 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {metrics.active_warranties_count} / {metrics.open_tickets_count}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {metrics.active_warranties_count} {t('warranties')} • {metrics.open_tickets_count} {t('open repair tickets')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs Navigation */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList className="grid w-full grid-cols-2 md:grid-cols-5 bg-muted/60 p-1">
                        <TabsTrigger value="assets" className="flex items-center gap-2">
                            <Cpu className="h-4 w-4" />
                            {t('Asset Register')}
                        </TabsTrigger>
                        <TabsTrigger value="depreciation" className="flex items-center gap-2">
                            <TrendingDown className="h-4 w-4" />
                            {t('Depreciation Schedule')}
                        </TabsTrigger>
                        <TabsTrigger value="transfers" className="flex items-center gap-2">
                            <ArrowRightLeft className="h-4 w-4" />
                            {t('Transfers & Disposals')}
                        </TabsTrigger>
                        <TabsTrigger value="warranty" className="flex items-center gap-2">
                            <ShieldCheck className="h-4 w-4" />
                            {t('Warranty Registry')}
                        </TabsTrigger>
                        <TabsTrigger value="servicing" className="flex items-center gap-2">
                            <Wrench className="h-4 w-4" />
                            {t('Service Tickets (RMA)')}
                        </TabsTrigger>
                    </TabsList>

                    {/* TAB 1: ASSET REGISTER */}
                    <TabsContent value="assets" className="space-y-4">
                        <Card className="border-border">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <CardTitle className="text-lg font-medium">
                                        {t('Fixed Assets Register')} ({filteredAssets.length})
                                    </CardTitle>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <div className="relative w-64">
                                            <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                            <Input
                                                placeholder={t('Search code, name, serial...')}
                                                value={searchTerm}
                                                onChange={(e) => setSearchTerm(e.target.value)}
                                                className="pl-8"
                                            />
                                        </div>
                                        <Select value={statusFilter} onValueChange={setStatusFilter}>
                                            <SelectTrigger className="w-[150px]">
                                                <SelectValue placeholder={t('Filter Status')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                                <SelectItem value="active">{t('Active')}</SelectItem>
                                                <SelectItem value="under_maintenance">{t('Maintenance')}</SelectItem>
                                                <SelectItem value="transferred">{t('Transferred')}</SelectItem>
                                                <SelectItem value="disposed">{t('Disposed')}</SelectItem>
                                                <SelectItem value="written_off">{t('Written Off')}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border overflow-x-auto">
                                    <table className="w-full text-sm text-left">
                                        <thead className="bg-muted/50 text-muted-foreground text-xs uppercase font-medium">
                                            <tr>
                                                <th className="px-4 py-3">{t('Asset Code')}</th>
                                                <th className="px-4 py-3">{t('Asset Name & Category')}</th>
                                                <th className="px-4 py-3">{t('Purchase Cost')}</th>
                                                <th className="px-4 py-3">{t('Book Value')}</th>
                                                <th className="px-4 py-3">{t('Depreciation Method')}</th>
                                                <th className="px-4 py-3">{t('Location & Custodian')}</th>
                                                <th className="px-4 py-3">{t('Status')}</th>
                                                <th className="px-4 py-3 text-right">{t('Actions')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {filteredAssets.length === 0 ? (
                                                <tr>
                                                    <td colSpan={8} className="text-center py-8 text-muted-foreground">
                                                        {t('No fixed assets registered yet.')}
                                                    </td>
                                                </tr>
                                            ) : (
                                                filteredAssets.map((asset) => (
                                                    <tr key={asset.id} className="hover:bg-muted/30 transition-colors">
                                                        <td className="px-4 py-3 font-semibold text-primary">
                                                            {asset.asset_code}
                                                            {asset.serial_number && (
                                                                <div className="text-xs font-mono text-muted-foreground">
                                                                    S/N: {asset.serial_number}
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-medium text-foreground">{asset.name}</div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {asset.category ? asset.category.name : t('Uncategorized')}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 font-medium">
                                                            {formatCurrency(asset.purchase_cost)}
                                                            <div className="text-xs text-muted-foreground">
                                                                {formatDate(asset.purchase_date)}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 font-bold text-emerald-600">
                                                            {formatCurrency(asset.current_value)}
                                                            <div className="text-xs text-muted-foreground">
                                                                Acc: {formatCurrency(asset.accumulated_depreciation)}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="capitalize text-xs font-medium">
                                                                {asset.depreciation_method.replace('_', ' ')}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {asset.depreciation_rate}% / yr ({asset.useful_life_years} yrs)
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="text-xs flex items-center gap-1 font-medium">
                                                                <Building2 className="h-3.5 w-3.5 text-muted-foreground" />
                                                                {asset.location || t('Main HQ')}
                                                            </div>
                                                            <div className="text-xs flex items-center gap-1 text-muted-foreground mt-0.5">
                                                                <User className="h-3.5 w-3.5" />
                                                                {asset.custodian ? asset.custodian.name : t('Unassigned')}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            {getStatusBadge(asset.status)}
                                                        </td>
                                                        <td className="px-4 py-3 text-right">
                                                            <div className="flex items-center justify-end gap-1.5">
                                                                {asset.status === 'active' && (
                                                                    <>
                                                                        <Button
                                                                            size="sm"
                                                                            variant="ghost"
                                                                            className="h-8 px-2 text-xs text-blue-600 hover:text-blue-700"
                                                                            onClick={() => openTransfer(asset)}
                                                                            title={t('Transfer Location / Custodian')}
                                                                        >
                                                                            <ArrowRightLeft className="h-3.5 w-3.5 mr-1" />
                                                                            {t('Transfer')}
                                                                        </Button>
                                                                        <Button
                                                                            size="sm"
                                                                            variant="ghost"
                                                                            className="h-8 px-2 text-xs text-red-600 hover:text-red-700"
                                                                            onClick={() => openDisposal(asset)}
                                                                            title={t('Dispose / Write-Off')}
                                                                        >
                                                                            <Trash2 className="h-3.5 w-3.5 mr-1" />
                                                                            {t('Dispose')}
                                                                        </Button>
                                                                    </>
                                                                )}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 2: DEPRECIATION SCHEDULE */}
                    <TabsContent value="depreciation" className="space-y-4">
                        <Card className="border-border">
                            <CardHeader className="flex flex-row items-center justify-between pb-3">
                                <div>
                                    <CardTitle className="text-lg font-medium">
                                        {t('Depreciation Posting History & Journal Links')}
                                    </CardTitle>
                                    <p className="text-xs text-muted-foreground mt-1">
                                        {t('Monthly straight-line and reducing-balance calculations logged in TZS')}
                                    </p>
                                </div>
                                <Button
                                    onClick={() => setShowDepreciationModal(true)}
                                    className="flex items-center gap-1.5"
                                >
                                    <RefreshCw className="h-4 w-4" />
                                    {t('Execute Depreciation Cycle')}
                                </Button>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border overflow-x-auto">
                                    <table className="w-full text-sm text-left">
                                        <thead className="bg-muted/50 text-muted-foreground text-xs uppercase font-medium">
                                            <tr>
                                                <th className="px-4 py-3">{t('Period')}</th>
                                                <th className="px-4 py-3">{t('Asset Code & Name')}</th>
                                                <th className="px-4 py-3">{t('Opening Book Value')}</th>
                                                <th className="px-4 py-3">{t('Depreciation Amount')}</th>
                                                <th className="px-4 py-3">{t('Closing Book Value')}</th>
                                                <th className="px-4 py-3">{t('Posted Date')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {depreciations.length === 0 ? (
                                                <tr>
                                                    <td colSpan={6} className="text-center py-8 text-muted-foreground">
                                                        {t('No depreciation entries posted yet. Click "Execute Depreciation Cycle" to run monthly calculations.')}
                                                    </td>
                                                </tr>
                                            ) : (
                                                depreciations.map((dep) => (
                                                    <tr key={dep.id} className="hover:bg-muted/30">
                                                        <td className="px-4 py-3 font-semibold text-foreground">
                                                            {dep.period}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-medium text-primary">
                                                                {dep.asset?.asset_code}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {dep.asset?.name}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            {formatCurrency(dep.opening_value)}
                                                        </td>
                                                        <td className="px-4 py-3 font-bold text-amber-600">
                                                            - {formatCurrency(dep.depreciation_amount)}
                                                        </td>
                                                        <td className="px-4 py-3 font-bold text-emerald-600">
                                                            {formatCurrency(dep.closing_value)}
                                                        </td>
                                                        <td className="px-4 py-3 text-xs text-muted-foreground">
                                                            {formatDate(dep.posted_at || dep.created_at)}
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 3: TRANSFERS & DISPOSALS */}
                    <TabsContent value="transfers" className="space-y-4">
                        <div className="grid gap-6 md:grid-cols-2">
                            {/* Transfers Card */}
                            <Card className="border-border">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base font-medium flex items-center gap-2">
                                        <ArrowRightLeft className="h-4 w-4 text-blue-500" />
                                        {t('Asset Location & Custodian Transfers')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="rounded-md border overflow-x-auto">
                                        <table className="w-full text-xs text-left">
                                            <thead className="bg-muted/50 text-muted-foreground uppercase font-medium">
                                                <tr>
                                                    <th className="px-3 py-2.5">{t('Transfer Ref')}</th>
                                                    <th className="px-3 py-2.5">{t('Asset')}</th>
                                                    <th className="px-3 py-2.5">{t('Route')}</th>
                                                    <th className="px-3 py-2.5">{t('Date')}</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-border">
                                                {transfers.length === 0 ? (
                                                    <tr>
                                                        <td colSpan={4} className="text-center py-6 text-muted-foreground">
                                                            {t('No transfer logs recorded.')}
                                                        </td>
                                                    </tr>
                                                ) : (
                                                    transfers.map((trf) => (
                                                        <tr key={trf.id}>
                                                            <td className="px-3 py-2 font-semibold text-primary">
                                                                {trf.transfer_number}
                                                            </td>
                                                            <td className="px-3 py-2">
                                                                {trf.asset?.asset_code}
                                                            </td>
                                                            <td className="px-3 py-2">
                                                                <span className="text-muted-foreground">{trf.from_location || 'HQ'}</span> &rarr; <span className="font-medium text-foreground">{trf.to_location}</span>
                                                            </td>
                                                            <td className="px-3 py-2 text-muted-foreground">
                                                                {formatDate(trf.transfer_date)}
                                                            </td>
                                                        </tr>
                                                    ))
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Disposals Card */}
                            <Card className="border-border">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base font-medium flex items-center gap-2">
                                        <Trash2 className="h-4 w-4 text-red-500" />
                                        {t('Asset Disposals & Write-Offs')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="rounded-md border overflow-x-auto">
                                        <table className="w-full text-xs text-left">
                                            <thead className="bg-muted/50 text-muted-foreground uppercase font-medium">
                                                <tr>
                                                    <th className="px-3 py-2.5">{t('Disposal Ref')}</th>
                                                    <th className="px-3 py-2.5">{t('Asset')}</th>
                                                    <th className="px-3 py-2.5">{t('Type')}</th>
                                                    <th className="px-3 py-2.5">{t('Gain / Loss')}</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-border">
                                                {disposals.length === 0 ? (
                                                    <tr>
                                                        <td colSpan={4} className="text-center py-6 text-muted-foreground">
                                                            {t('No disposal write-offs recorded.')}
                                                        </td>
                                                    </tr>
                                                ) : (
                                                    disposals.map((dsp) => (
                                                        <tr key={dsp.id}>
                                                            <td className="px-3 py-2 font-semibold text-primary">
                                                                {dsp.disposal_number}
                                                            </td>
                                                            <td className="px-3 py-2">
                                                                {dsp.asset?.asset_code}
                                                            </td>
                                                            <td className="px-3 py-2 capitalize">
                                                                {dsp.disposal_type}
                                                            </td>
                                                            <td className={`px-3 py-2 font-medium ${dsp.gain_loss_amount >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                                                                {formatCurrency(dsp.gain_loss_amount)}
                                                            </td>
                                                        </tr>
                                                    ))
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    {/* TAB 4: WARRANTY REGISTRY */}
                    <TabsContent value="warranty" className="space-y-4">
                        <Card className="border-border">
                            <CardHeader className="flex flex-row items-center justify-between pb-3">
                                <div>
                                    <CardTitle className="text-lg font-medium">
                                        {t('Product & Hardware Warranty Registry')}
                                    </CardTitle>
                                    <p className="text-xs text-muted-foreground mt-1">
                                        {t('Serial-number-linked warranty tracking with expiration verification')}
                                    </p>
                                </div>
                                <Button
                                    onClick={() => setShowWarrantyModal(true)}
                                    className="flex items-center gap-1.5"
                                >
                                    <ShieldCheck className="h-4 w-4" />
                                    {t('Register New Warranty')}
                                </Button>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border overflow-x-auto">
                                    <table className="w-full text-sm text-left">
                                        <thead className="bg-muted/50 text-muted-foreground text-xs uppercase font-medium">
                                            <tr>
                                                <th className="px-4 py-3">{t('Warranty Code')}</th>
                                                <th className="px-4 py-3">{t('Serial Number / IMEI')}</th>
                                                <th className="px-4 py-3">{t('Customer / Beneficiary')}</th>
                                                <th className="px-4 py-3">{t('Valid Period')}</th>
                                                <th className="px-4 py-3">{t('Duration')}</th>
                                                <th className="px-4 py-3">{t('Status')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {warranties.length === 0 ? (
                                                <tr>
                                                    <td colSpan={6} className="text-center py-8 text-muted-foreground">
                                                        {t('No warranties registered.')}
                                                    </td>
                                                </tr>
                                            ) : (
                                                warranties.map((war) => (
                                                    <tr key={war.id} className="hover:bg-muted/30">
                                                        <td className="px-4 py-3 font-semibold text-primary">
                                                            {war.warranty_code}
                                                        </td>
                                                        <td className="px-4 py-3 font-mono font-medium text-foreground">
                                                            {war.serial_number}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-medium text-foreground">{war.customer_name}</div>
                                                            {war.customer_phone && (
                                                                <div className="text-xs text-muted-foreground">{war.customer_phone}</div>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3 text-xs">
                                                            {formatDate(war.start_date)} &rarr; <span className="font-medium">{formatDate(war.end_date)}</span>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            {war.warranty_period_months} {t('Months')}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            {getStatusBadge(war.status)}
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 5: SERVICE & REPAIR TICKETS (RMA) */}
                    <TabsContent value="servicing" className="space-y-4">
                        <Card className="border-border">
                            <CardHeader className="flex flex-row items-center justify-between pb-3">
                                <div>
                                    <CardTitle className="text-lg font-medium">
                                        {t('Service & Repair Tickets (RMA Workflow)')}
                                    </CardTitle>
                                    <p className="text-xs text-muted-foreground mt-1">
                                        {t('Track warranty claims, in-house technical repairs, and manufacturer vendor dispatches')}
                                    </p>
                                </div>
                                <Button
                                    onClick={() => setShowTicketModal(true)}
                                    className="flex items-center gap-1.5"
                                >
                                    <Wrench className="h-4 w-4" />
                                    {t('New Service Ticket')}
                                </Button>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border overflow-x-auto">
                                    <table className="w-full text-sm text-left">
                                        <thead className="bg-muted/50 text-muted-foreground text-xs uppercase font-medium">
                                            <tr>
                                                <th className="px-4 py-3">{t('Ticket Ref')}</th>
                                                <th className="px-4 py-3">{t('Product & S/N')}</th>
                                                <th className="px-4 py-3">{t('Customer')}</th>
                                                <th className="px-4 py-3">{t('Service Type')}</th>
                                                <th className="px-4 py-3">{t('Issue Description')}</th>
                                                <th className="px-4 py-3">{t('Vendor / Status')}</th>
                                                <th className="px-4 py-3 text-right">{t('Update Status')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {service_tickets.length === 0 ? (
                                                <tr>
                                                    <td colSpan={7} className="text-center py-8 text-muted-foreground">
                                                        {t('No service repair tickets registered.')}
                                                    </td>
                                                </tr>
                                            ) : (
                                                service_tickets.map((tkt) => (
                                                    <tr key={tkt.id} className="hover:bg-muted/30">
                                                        <td className="px-4 py-3 font-semibold text-primary">
                                                            {tkt.ticket_number}
                                                            {tkt.warranty_id && (
                                                                <Badge className="ml-1 text-[10px] bg-emerald-500/10 text-emerald-600">
                                                                    {t('Warranty Linked')}
                                                                </Badge>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-medium text-foreground">{tkt.product_name}</div>
                                                            {tkt.serial_number && (
                                                                <div className="text-xs font-mono text-muted-foreground">
                                                                    S/N: {tkt.serial_number}
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-medium">{tkt.customer_name}</div>
                                                            {tkt.contact_phone && (
                                                                <div className="text-xs text-muted-foreground">{tkt.contact_phone}</div>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3 capitalize text-xs">
                                                            {tkt.service_type.replace('_', ' ')}
                                                        </td>
                                                        <td className="px-4 py-3 max-w-[200px] truncate text-xs text-muted-foreground" title={tkt.issue_reported}>
                                                            {tkt.issue_reported}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div>{getStatusBadge(tkt.status)}</div>
                                                            {tkt.vendor_repair && tkt.vendor_name && (
                                                                <div className="text-[11px] text-muted-foreground mt-0.5">
                                                                    Vendor: {tkt.vendor_name}
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3 text-right">
                                                            <Select
                                                                value={tkt.status}
                                                                onValueChange={(val) => handleUpdateTicketStatus(tkt.id, val)}
                                                            >
                                                                <SelectTrigger className="w-[140px] h-8 text-xs ml-auto">
                                                                    <SelectValue />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    <SelectItem value="received">{t('Received')}</SelectItem>
                                                                    <SelectItem value="diagnosing">{t('Diagnosing')}</SelectItem>
                                                                    <SelectItem value="sent_to_vendor">{t('Sent to Vendor')}</SelectItem>
                                                                    <SelectItem value="repaired">{t('Repaired')}</SelectItem>
                                                                    <SelectItem value="delivered_to_customer">{t('Delivered')}</SelectItem>
                                                                    <SelectItem value="cancelled">{t('Cancelled')}</SelectItem>
                                                                </SelectContent>
                                                            </Select>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>

            {/* MODAL: REGISTER ASSET */}
            <Dialog open={showAssetModal} onOpenChange={setShowAssetModal}>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Cpu className="h-5 w-5 text-primary" />
                            {t('Register New Fixed Asset')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateAsset} className="space-y-4 pt-2">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Asset Name')} *</label>
                                <Input
                                    required
                                    placeholder={t('e.g. Dell PowerEdge R750 Server')}
                                    value={assetForm.data.name}
                                    onChange={(e) => assetForm.setData('name', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Category')} *</label>
                                <Select
                                    value={assetForm.data.asset_category_id}
                                    onValueChange={(val) => assetForm.setData('asset_category_id', val)}
                                >
                                    <SelectTrigger className="mt-1">
                                        <SelectValue placeholder={t('Select Category')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {categories.map((cat) => (
                                            <SelectItem key={cat.id} value={cat.id.toString()}>
                                                {cat.name} ({cat.code})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Purchase Date')} *</label>
                                <Input
                                    type="date"
                                    required
                                    value={assetForm.data.purchase_date}
                                    onChange={(e) => assetForm.setData('purchase_date', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Purchase Cost (TZS)')} *</label>
                                <Input
                                    type="number"
                                    required
                                    min="0"
                                    step="1000"
                                    placeholder="e.g. 15000000"
                                    value={assetForm.data.purchase_cost}
                                    onChange={(e) => assetForm.setData('purchase_cost', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Salvage Value (TZS)')}</label>
                                <Input
                                    type="number"
                                    min="0"
                                    step="1000"
                                    placeholder="e.g. 1000000"
                                    value={assetForm.data.salvage_value}
                                    onChange={(e) => assetForm.setData('salvage_value', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Depreciation Method')} *</label>
                                <Select
                                    value={assetForm.data.depreciation_method}
                                    onValueChange={(val) => assetForm.setData('depreciation_method', val)}
                                >
                                    <SelectTrigger className="mt-1">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="straight_line">{t('Straight Line')}</SelectItem>
                                        <SelectItem value="reducing_balance">{t('Reducing Balance')}</SelectItem>
                                        <SelectItem value="none">{t('None (Land / Artwork)')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Annual Rate (%)')} *</label>
                                <Input
                                    type="number"
                                    required
                                    min="0"
                                    max="100"
                                    step="0.5"
                                    value={assetForm.data.depreciation_rate}
                                    onChange={(e) => assetForm.setData('depreciation_rate', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Useful Life (Years)')} *</label>
                                <Input
                                    type="number"
                                    required
                                    min="1"
                                    value={assetForm.data.useful_life_years}
                                    onChange={(e) => assetForm.setData('useful_life_years', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Physical Location')}</label>
                                <Input
                                    placeholder={t('e.g. Dar HQ - Server Room Rack A')}
                                    value={assetForm.data.location}
                                    onChange={(e) => assetForm.setData('location', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Custodian / Employee')}</label>
                                <Select
                                    value={assetForm.data.custodian_id}
                                    onValueChange={(val) => assetForm.setData('custodian_id', val)}
                                >
                                    <SelectTrigger className="mt-1">
                                        <SelectValue placeholder={t('Assign Custodian')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {custodians.map((u) => (
                                            <SelectItem key={u.id} value={u.id.toString()}>
                                                {u.name} ({u.email})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Hardware Serial Number / Tag')}</label>
                                <Input
                                    placeholder={t('e.g. SN-PE750-TZ-88421')}
                                    value={assetForm.data.serial_number}
                                    onChange={(e) => assetForm.setData('serial_number', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Warranty Expiry Date')}</label>
                                <Input
                                    type="date"
                                    value={assetForm.data.warranty_expiry_date}
                                    onChange={(e) => assetForm.setData('warranty_expiry_date', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                        </div>

                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Asset Description & Notes')}</label>
                            <Input
                                placeholder={t('Technical specifications, supplier warranty notes...')}
                                value={assetForm.data.notes}
                                onChange={(e) => assetForm.setData('notes', e.target.value)}
                                className="mt-1"
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setShowAssetModal(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={assetForm.processing} className="bg-primary text-primary-foreground">
                                {t('Register Asset')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL: RUN DEPRECIATION */}
            <Dialog open={showDepreciationModal} onOpenChange={setShowDepreciationModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <TrendingDown className="h-5 w-5 text-amber-500" />
                            {t('Run Monthly Depreciation')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleRunDepreciation} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Accounting Period (YYYY-MM)')} *</label>
                            <Input
                                required
                                placeholder="2026-10"
                                value={depForm.data.period}
                                onChange={(e) => depForm.setData('period', e.target.value)}
                                className="mt-1"
                            />
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Depreciation will be computed across all active fixed assets according to their assigned depreciation formula.')}
                            </p>
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setShowDepreciationModal(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={depForm.processing} className="bg-amber-600 text-white hover:bg-amber-700">
                                {t('Post Depreciation in TZS')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL: TRANSFER ASSET */}
            <Dialog open={showTransferModal} onOpenChange={setShowTransferModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <ArrowRightLeft className="h-5 w-5 text-blue-500" />
                            {t('Transfer Asset Location / Custodian')}
                        </DialogTitle>
                    </DialogHeader>
                    {selectedAsset && (
                        <div className="p-3 bg-muted/50 rounded text-xs space-y-1">
                            <div><strong>{t('Asset')}:</strong> {selectedAsset.asset_code} - {selectedAsset.name}</div>
                            <div><strong>{t('Current Location')}:</strong> {selectedAsset.location || 'HQ'}</div>
                        </div>
                    )}
                    <form onSubmit={handleTransfer} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Transfer Date')} *</label>
                            <Input
                                type="date"
                                required
                                value={transferForm.data.transfer_date}
                                onChange={(e) => transferForm.setData('transfer_date', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Destination Location')} *</label>
                            <Input
                                required
                                placeholder={t('e.g. Arusha Tech & Logistics Center')}
                                value={transferForm.data.to_location}
                                onChange={(e) => transferForm.setData('to_location', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('New Custodian / Employee')}</label>
                            <Select
                                value={transferForm.data.to_custodian_id}
                                onValueChange={(val) => transferForm.setData('to_custodian_id', val)}
                            >
                                <SelectTrigger className="mt-1">
                                    <SelectValue placeholder={t('Assign New Custodian')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {custodians.map((u) => (
                                        <SelectItem key={u.id} value={u.id.toString()}>
                                            {u.name} ({u.email})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Reason for Transfer')}</label>
                            <Input
                                placeholder={t('Operational relocation, department switch...')}
                                value={transferForm.data.reason}
                                onChange={(e) => transferForm.setData('reason', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setShowTransferModal(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={transferForm.processing} className="bg-primary text-primary-foreground">
                                {t('Confirm Transfer')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL: DISPOSAL */}
            <Dialog open={showDisposalModal} onOpenChange={setShowDisposalModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Trash2 className="h-5 w-5 text-red-500" />
                            {t('Asset Disposal & Write-Off')}
                        </DialogTitle>
                    </DialogHeader>
                    {selectedAsset && (
                        <div className="p-3 bg-muted/50 rounded text-xs space-y-1">
                            <div><strong>{t('Asset')}:</strong> {selectedAsset.asset_code} - {selectedAsset.name}</div>
                            <div><strong>{t('Current Book Value')}:</strong> {formatCurrency(selectedAsset.current_value)}</div>
                        </div>
                    )}
                    <form onSubmit={handleDisposal} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Disposal Date')} *</label>
                            <Input
                                type="date"
                                required
                                value={disposalForm.data.disposal_date}
                                onChange={(e) => disposalForm.setData('disposal_date', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Disposal Type')} *</label>
                            <Select
                                value={disposalForm.data.disposal_type}
                                onValueChange={(val) => disposalForm.setData('disposal_type', val)}
                            >
                                <SelectTrigger className="mt-1">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="sale">{t('Sale (Cash Realized)')}</SelectItem>
                                    <SelectItem value="write_off">{t('Damaged / Write-Off')}</SelectItem>
                                    <SelectItem value="scrapped">{t('Scrapped')}</SelectItem>
                                    <SelectItem value="donated">{t('Donated')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Sale Amount Realized (TZS)')}</label>
                            <Input
                                type="number"
                                min="0"
                                step="1000"
                                value={disposalForm.data.sale_amount}
                                onChange={(e) => disposalForm.setData('sale_amount', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Disposal Reason')}</label>
                            <Input
                                placeholder={t('End of economic life, irrecoverable hardware failure...')}
                                value={disposalForm.data.reason}
                                onChange={(e) => disposalForm.setData('reason', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setShowDisposalModal(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={disposalForm.processing} className="bg-red-600 text-white hover:bg-red-700">
                                {t('Execute Disposal')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL: REGISTER WARRANTY */}
            <Dialog open={showWarrantyModal} onOpenChange={setShowWarrantyModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <ShieldCheck className="h-5 w-5 text-emerald-500" />
                            {t('Register Product / Asset Warranty')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleRegisterWarranty} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Serial Number / IMEI')} *</label>
                            <Input
                                required
                                placeholder="e.g. SN-SRV-2026-9941"
                                value={warrantyForm.data.serial_number}
                                onChange={(e) => warrantyForm.setData('serial_number', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Customer / Custodian Name')} *</label>
                            <Input
                                required
                                placeholder={t('e.g. Mutamark Telecom Division')}
                                value={warrantyForm.data.customer_name}
                                onChange={(e) => warrantyForm.setData('customer_name', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Phone Number')}</label>
                                <Input
                                    placeholder="+255 747 003 357"
                                    value={warrantyForm.data.customer_phone}
                                    onChange={(e) => warrantyForm.setData('customer_phone', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Duration (Months)')} *</label>
                                <Input
                                    type="number"
                                    required
                                    min="1"
                                    value={warrantyForm.data.warranty_period_months}
                                    onChange={(e) => warrantyForm.setData('warranty_period_months', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Start Date')} *</label>
                            <Input
                                type="date"
                                required
                                value={warrantyForm.data.start_date}
                                onChange={(e) => warrantyForm.setData('start_date', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Terms & Conditions')}</label>
                            <Input
                                placeholder={t('Covers parts and factory labor. Excludes water damage.')}
                                value={warrantyForm.data.warranty_terms}
                                onChange={(e) => warrantyForm.setData('warranty_terms', e.target.value)}
                                className="mt-1"
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setShowWarrantyModal(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={warrantyForm.processing} className="bg-emerald-600 text-white hover:bg-emerald-700">
                                {t('Issue Certificate')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL: SERVICE TICKET (RMA) */}
            <Dialog open={showTicketModal} onOpenChange={setShowTicketModal}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Wrench className="h-5 w-5 text-blue-500" />
                            {t('Create Service & Repair Ticket (RMA)')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateTicket} className="space-y-4 pt-2">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Product / Equipment')} *</label>
                                <Input
                                    required
                                    placeholder={t('e.g. Mikrotik CCR2116 Router')}
                                    value={ticketForm.data.product_name}
                                    onChange={(e) => ticketForm.setData('product_name', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Serial Number / IMEI')}</label>
                                <Input
                                    placeholder="e.g. SN-RB-4011-TZ"
                                    value={ticketForm.data.serial_number}
                                    onChange={(e) => ticketForm.setData('serial_number', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Customer / Requester')} *</label>
                                <Input
                                    required
                                    placeholder={t('e.g. Tanzania Ports Authority')}
                                    value={ticketForm.data.customer_name}
                                    onChange={(e) => ticketForm.setData('customer_name', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-foreground">{t('Contact Phone')}</label>
                                <Input
                                    placeholder="+255 747 003 357"
                                    value={ticketForm.data.contact_phone}
                                    onChange={(e) => ticketForm.setData('contact_phone', e.target.value)}
                                    className="mt-1"
                                />
                            </div>
                        </div>

                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Service Type')} *</label>
                            <Select
                                value={ticketForm.data.service_type}
                                onValueChange={(val) => ticketForm.setData('service_type', val)}
                            >
                                <SelectTrigger className="mt-1">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="warranty_claim">{t('Warranty Claim (Free)')}</SelectItem>
                                    <SelectItem value="paid_repair">{t('Paid Repair / Out-of-Warranty')}</SelectItem>
                                    <SelectItem value="preventive_maintenance">{t('Scheduled Preventive Maintenance')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Reported Issue / Fault Description')} *</label>
                            <Input
                                required
                                placeholder={t('Power supply failure, optical SFP port flapping...')}
                                value={ticketForm.data.issue_reported}
                                onChange={(e) => ticketForm.setData('issue_reported', e.target.value)}
                                className="mt-1"
                            />
                        </div>

                        <div className="rounded border p-3 space-y-2 bg-muted/20">
                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="vendor_repair"
                                    checked={ticketForm.data.vendor_repair}
                                    onChange={(e) => ticketForm.setData('vendor_repair', e.target.checked)}
                                    className="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                                />
                                <label htmlFor="vendor_repair" className="text-xs font-medium text-foreground cursor-pointer">
                                    {t('Dispatch to Vendor / Manufacturer Repair (Vendor RMA)')}
                                </label>
                            </div>
                            {ticketForm.data.vendor_repair && (
                                <Input
                                    placeholder={t('Vendor / Factory Partner (e.g. Redington TZ / Cisco)')}
                                    value={ticketForm.data.vendor_name}
                                    onChange={(e) => ticketForm.setData('vendor_name', e.target.value)}
                                    className="mt-1 text-xs"
                                />
                            )}
                        </div>

                        <div>
                            <label className="text-xs font-medium text-foreground">{t('Estimated Repair Cost (TZS)')}</label>
                            <Input
                                type="number"
                                min="0"
                                step="1000"
                                value={ticketForm.data.estimated_cost}
                                onChange={(e) => ticketForm.setData('estimated_cost', e.target.value)}
                                className="mt-1"
                            />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setShowTicketModal(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={ticketForm.processing} className="bg-primary text-primary-foreground">
                                {t('Create Ticket')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
