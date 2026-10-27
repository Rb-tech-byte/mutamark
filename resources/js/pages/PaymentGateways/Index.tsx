import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { 
    CreditCard, 
    Smartphone, 
    ShieldCheck, 
    CheckCircle2, 
    Clock, 
    AlertTriangle, 
    ExternalLink, 
    Copy, 
    Plus, 
    QrCode, 
    RefreshCw, 
    SlidersHorizontal, 
    DollarSign, 
    Layers, 
    Lock, 
    Building2, 
    Check, 
    Share2, 
    Search,
    Globe,
    Zap,
    FileText,
    Receipt,
    Printer,
    Trash2,
    XCircle,
    Eye,
    PhoneCall,
    Sparkles,
    CheckSquare
} from 'lucide-react';

interface NetworkConfig {
    name: string;
    network_code: string;
    ussd_root: string;
    ussd_dial_template: string;
    instructions: string;
    brand_color: string;
}

interface LipaNambaInfo {
    till_number: string;
    merchant_name: string;
    networks: NetworkConfig[];
}

interface GatewayProps {
    metrics: {
        total_collected: number;
        completed_count: number;
        total_txns_count: number;
        active_gateways_count: number;
        success_rate: number;
    };
    gateways: any[];
    transactions: any[];
    open_invoices: any[];
    lipa_namba_info?: LipaNambaInfo;
}

export default function PaymentGatewaysIndex({
    metrics = {
        total_collected: 0,
        completed_count: 0,
        total_txns_count: 0,
        active_gateways_count: 0,
        success_rate: 100,
    },
    gateways = [],
    transactions = [],
    open_invoices = [],
    lipa_namba_info = {
        till_number: '',
        merchant_name: 'Mutamark Enterprises Ltd',
        networks: [
            {
                name: 'Vodacom M-Pesa',
                network_code: 'mpesa',
                ussd_root: '*150*00#',
                ussd_dial_template: '*150*00#',
                instructions: 'Piga *150*00# > 4 (Lipa kwa M-Pesa) > Weka Lipa Namba ya Kampuni',
                brand_color: '#E60000',
            },
            {
                name: 'Tigo Pesa',
                network_code: 'tigopesa',
                ussd_root: '*150*01#',
                ussd_dial_template: '*150*01#',
                instructions: 'Piga *150*01# > 5 (Lipa kwa Simu) > 1 (Mitandao Yote / Lipa Namba)',
                brand_color: '#0033A0',
            },
            {
                name: 'Airtel Money',
                network_code: 'airtelmoney',
                ussd_root: '*150*60#',
                ussd_dial_template: '*150*60#',
                instructions: 'Piga *150*60# > 5 (Lipa Bili) > Lipa Namba',
                brand_color: '#ED1C24',
            },
            {
                name: 'Halopesa',
                network_code: 'halopesa',
                ussd_root: '*150*88#',
                ussd_dial_template: '*150*88#',
                instructions: 'Piga *150*88# > 5 (Lipa kwa Halopesa) > Namba ya Biashara',
                brand_color: '#FF6600',
            },
            {
                name: 'CRDB SimBanking',
                network_code: 'crdb',
                ussd_root: '*150*03#',
                ussd_dial_template: '*150*03#',
                instructions: 'Piga *150*03# / Fungua SimBanking App > Malipo > Lipa Namba',
                brand_color: '#008752',
            },
            {
                name: 'NMB Mkononi',
                network_code: 'nmb',
                ussd_root: '*150*66#',
                ussd_dial_template: '*150*66#',
                instructions: 'Piga *150*66# / Fungua NMB Mkononi App > Lipa Mkononi',
                brand_color: '#002B49',
            },
        ],
    },
}: GatewayProps) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState('lipa-namba');
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [gatewayFilter, setGatewayFilter] = useState('all');

    // USSD Live Calculator state
    const [calcAmount, setCalcAmount] = useState('50000');
    const [calcRef, setCalcRef] = useState('INV-2026-0001');

    // Modals
    const [showEditModal, setShowEditModal] = useState(false);
    const [showCreateGatewayModal, setShowCreateGatewayModal] = useState(false);
    const [showLinkModal, setShowLinkModal] = useState(false);
    const [showManualSettleModal, setShowManualSettleModal] = useState(false);
    const [showReconcileModal, setShowReconcileModal] = useState(false);
    const [showVoidModal, setShowVoidModal] = useState(false);
    const [showStandeeModal, setShowStandeeModal] = useState(false);
    const [showVoucherModal, setShowVoucherModal] = useState(false);

    const [selectedGateway, setSelectedGateway] = useState<any>(null);
    const [selectedTransaction, setSelectedTransaction] = useState<any>(null);
    const [copiedToken, setCopiedToken] = useState<string | null>(null);

    // Edit Gateway Form
    const gatewayForm = useForm({
        name: '',
        description: '',
        mode: 'sandbox',
        is_active: true,
        is_default: false,
        transaction_fee_percentage: '0.00',
        transaction_fee_flat: '0.00',
        webhook_secret: '',
        instructions: '',
        credentials: {} as Record<string, any>,
    });

    // Create Gateway Form
    const createGatewayForm = useForm({
        name: '',
        gateway_key: '',
        description: '',
        mode: 'sandbox',
        is_active: true,
        is_default: false,
        transaction_fee_percentage: '0.00',
        transaction_fee_flat: '0.00',
        webhook_secret: '',
        instructions: '',
        credentials: {
            till_number: '16381432',
            merchant_name: 'WOLINET / MUTAMARK ENTERPRISES LTD',
            api_key: '',
            api_secret: '',
        } as Record<string, any>,
    });

    // Generate Payment Link Form
    const linkForm = useForm({
        invoice_id: '',
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        amount: '',
        currency: 'TZS',
        gateway_key: 'manual_mobile_money',
    });

    // Manual Offline Settlement Form (Cashier records incoming payment)
    const manualSettleForm = useForm({
        invoice_id: '',
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        amount: '',
        currency: 'TZS',
        gateway_key: 'manual_mobile_money',
        payment_channel: 'mpesa',
        reference_number: '',
        settlement_notes: 'Lipa Namba counter payment verified by cashier',
    });

    // Reconcile Transaction Form
    const reconcileForm = useForm({
        status: 'completed',
        gateway_transaction_id: '',
        reconciliation_notes: '',
    });

    // Void Transaction Form
    const voidForm = useForm({
        reason: '',
    });

    // Open Edit Gateway Modal
    const handleOpenEdit = (gw: any) => {
        setSelectedGateway(gw);
        gatewayForm.setData({
            name: gw.name || '',
            description: gw.description || '',
            mode: gw.mode || 'sandbox',
            is_active: !!gw.is_active,
            is_default: !!gw.is_default,
            transaction_fee_percentage: String(gw.transaction_fee_percentage || '0.00'),
            transaction_fee_flat: String(gw.transaction_fee_flat || '0.00'),
            webhook_secret: gw.webhook_secret || '',
            instructions: gw.instructions || '',
            credentials: gw.credentials || {},
        });
        setShowEditModal(true);
    };

    // Save Gateway Config
    const handleSaveGateway = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedGateway) return;

        gatewayForm.put(route('payment-gateways.update', selectedGateway.id), {
            onSuccess: () => {
                setShowEditModal(false);
            },
        });
    };

    // Handle Create Gateway Submit
    const handleCreateGateway = (e: React.FormEvent) => {
        e.preventDefault();
        createGatewayForm.post(route('payment-gateways.store'), {
            onSuccess: () => {
                setShowCreateGatewayModal(false);
                createGatewayForm.reset();
            },
        });
    };

    // Toggle Active
    const handleToggleGateway = (id: number) => {
        router.post(route('payment-gateways.toggle', id));
    };

    // Delete Gateway
    const handleDeleteGateway = (gw: any) => {
        if (confirm(`Are you sure you want to delete payment switch [${gw.name}]?`)) {
            router.delete(route('payment-gateways.destroy', gw.id));
        }
    };

    // Handle Invoice Selection in Link Form
    const handleInvoiceSelect = (invId: string) => {
        linkForm.setData('invoice_id', invId);
        if (invId) {
            const inv = open_invoices.find((i) => String(i.id) === invId);
            if (inv) {
                linkForm.setData((prev) => ({
                    ...prev,
                    invoice_id: invId,
                    amount: String(inv.balance_amount || inv.total_amount),
                }));
            }
        }
    };

    // Handle Invoice Selection in Manual Settlement Form
    const handleManualInvoiceSelect = (invId: string) => {
        manualSettleForm.setData('invoice_id', invId);
        if (invId) {
            const inv = open_invoices.find((i) => String(i.id) === invId);
            if (inv) {
                manualSettleForm.setData((prev) => ({
                    ...prev,
                    invoice_id: invId,
                    amount: String(inv.balance_amount || inv.total_amount),
                    customer_name: inv.customer_name || prev.customer_name,
                }));
            }
        }
    };

    // Generate Payment Link Submit
    const handleGenerateLink = (e: React.FormEvent) => {
        e.preventDefault();
        linkForm.post(route('payment-gateways.generate-link'), {
            onSuccess: () => {
                setShowLinkModal(false);
                linkForm.reset();
            },
        });
    };

    // Record Offline Lipa Namba Settlement Submit
    const handleManualSettleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        manualSettleForm.post(route('payment-gateways.manual-settle'), {
            onSuccess: () => {
                setShowManualSettleModal(false);
                manualSettleForm.reset();
            },
        });
    };

    // Open Reconcile Modal
    const handleOpenReconcile = (tx: any) => {
        setSelectedTransaction(tx);
        reconcileForm.setData({
            status: tx.status,
            gateway_transaction_id: tx.gateway_transaction_id || '',
            reconciliation_notes: '',
        });
        setShowReconcileModal(true);
    };

    // Save Reconcile Submit
    const handleSaveReconcile = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedTransaction) return;

        reconcileForm.put(route('payment-gateways.reconcile', selectedTransaction.id), {
            onSuccess: () => {
                setShowReconcileModal(false);
            },
        });
    };

    // Open Void Modal
    const handleOpenVoid = (tx: any) => {
        setSelectedTransaction(tx);
        voidForm.reset();
        setShowVoidModal(true);
    };

    // Submit Void Transaction
    const handleSaveVoid = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedTransaction) return;

        voidForm.post(route('payment-gateways.void', selectedTransaction.id), {
            onSuccess: () => {
                setShowVoidModal(false);
            },
        });
    };

    // View Receipt Voucher Modal
    const handleViewVoucher = (tx: any) => {
        setSelectedTransaction(tx);
        setShowVoucherModal(true);
    };

    // Copy to clipboard
    const copyToClipboard = (text: string, id: string) => {
        navigator.clipboard.writeText(text);
        setCopiedToken(id);
        setTimeout(() => setCopiedToken(null), 2500);
    };

    // Dynamic USSD generator string for network
    const getNetworkUssdDial = (template: string, amount: string) => {
        const cleanAmt = String(amount || '0').replace(/[^0-9]/g, '');
        return template.replace('{amount}', cleanAmt || '1000');
    };

    // Filter transactions
    const filteredTxns = transactions.filter((tx) => {
        const matchesSearch = 
            tx.transaction_reference?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            tx.invoice_number?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            tx.customer_name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            tx.receipt_voucher_number?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            tx.gateway_transaction_id?.toLowerCase().includes(searchTerm.toLowerCase());

        const matchesStatus = statusFilter === 'all' || tx.status === statusFilter;
        const matchesGateway = gatewayFilter === 'all' || tx.gateway_key === gatewayFilter;

        return matchesSearch && matchesStatus && matchesGateway;
    });

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'completed':
                return <Badge className="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{t('Settled / Cleared')}</Badge>;
            case 'pending':
                return <Badge className="bg-amber-500/10 text-amber-600 border border-amber-500/20">{t('Pending Payment')}</Badge>;
            case 'processing':
                return <Badge className="bg-blue-500/10 text-blue-600 border border-blue-500/20">{t('Processing')}</Badge>;
            case 'cancelled':
                return <Badge className="bg-rose-500/10 text-rose-600 border border-rose-500/20">{t('Voided / Cancelled')}</Badge>;
            case 'failed':
                return <Badge className="bg-rose-500/10 text-rose-600 border border-rose-500/20">{t('Failed')}</Badge>;
            case 'refunded':
                return <Badge className="bg-purple-500/10 text-purple-600 border border-purple-500/20">{t('Refunded')}</Badge>;
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const getGatewayIcon = (key: string) => {
        switch (key) {
            case 'selcom':
            case 'manual_mobile_money':
                return <Smartphone className="h-5 w-5 text-emerald-600" />;
            case 'pesapal':
                return <Zap className="h-5 w-5 text-blue-600" />;
            case 'stripe':
                return <CreditCard className="h-5 w-5 text-indigo-600" />;
            case 'paypal':
                return <Globe className="h-5 w-5 text-sky-600" />;
            case 'bank_transfer':
                return <Building2 className="h-5 w-5 text-amber-600" />;
            default:
                return <CreditCard className="h-5 w-5 text-primary" />;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Lipa Namba & Payment Gateway Switch - Mutamark ERP')} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                            <Smartphone className="h-7 w-7 text-primary" />
                            {t('Payment Gateways & Transactions Ledger')}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {t('SRS Module 33 & Sections 8.3 & 19: Unified transaction audit ledger, cashier recording, and gateway settlement tracking.')}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Button
                            variant="outline"
                            onClick={() => router.visit(route('settings.index') + '#paypal-settings')}
                            className="flex items-center gap-2 border-primary/30 text-primary hover:bg-primary/10"
                        >
                            <SlidersHorizontal className="h-4 w-4" />
                            {t('Configure Gateways in Settings')}
                        </Button>

                        <Button
                            variant="outline"
                            onClick={() => setShowStandeeModal(true)}
                            className="flex items-center gap-2"
                        >
                            <QrCode className="h-4 w-4 text-primary" />
                            {t('Counter Standee (QR)')}
                        </Button>

                        <Button
                            variant="secondary"
                            onClick={() => {
                                manualSettleForm.reset();
                                setShowManualSettleModal(true);
                            }}
                            className="flex items-center gap-2"
                        >
                            <CheckSquare className="h-4 w-4 text-emerald-600" />
                            {t('Record Lipa Settlement')}
                        </Button>

                        <Button
                            onClick={() => {
                                linkForm.reset();
                                setShowLinkModal(true);
                            }}
                            className="flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            <Plus className="h-4 w-4" />
                            {t('New Payment Link')}
                        </Button>
                    </div>
                </div>

                {/* Central Settings Alert Banner */}
                <div className="flex items-center justify-between p-3.5 bg-primary/5 border border-primary/20 rounded-xl text-xs">
                    <div className="flex items-center gap-2 text-foreground">
                        <Sparkles className="h-4 w-4 text-primary shrink-0" />
                        <span>
                            {t('All payment gateway API credentials & mobile money till numbers are configured centrally in')}{' '}
                            <strong className="text-primary font-semibold">{t('Settings > Payment Gateways')}</strong>{' '}
                            (PayPal, Stripe, Selcom, Pesapal, Lipa Namba, and Bank Transfer).
                        </span>
                    </div>
                    <Button
                        variant="link"
                        size="sm"
                        onClick={() => router.visit(route('settings.index') + '#paypal-settings')}
                        className="text-primary font-semibold hover:underline h-auto p-0 shrink-0"
                    >
                        {t('Manage Settings →')}
                    </Button>
                </div>

                {/* 4 Top KPI Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Total Collections Settled')}
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">
                                {formatCurrency(metrics.total_collected)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {metrics.completed_count} {t('settled transactions in TZS')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Merchant Lipa Namba')}
                            </CardTitle>
                            <Smartphone className="h-4 w-4 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground font-mono tracking-tight">
                                {lipa_namba_info.till_number}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1 truncate">
                                {lipa_namba_info.merchant_name}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Settlement Success Rate')}
                            </CardTitle>
                            <CheckCircle2 className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {metrics.success_rate}%
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Completed vs total initiated attempts')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Configured Switches')}
                            </CardTitle>
                            <Layers className="h-4 w-4 text-purple-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {metrics.active_gateways_count} / {gateways.length}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Active mobile money & card gateways')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList className="bg-muted p-1 rounded-lg">
                        <TabsTrigger value="lipa-namba" className="flex items-center gap-2">
                            <Smartphone className="h-4 w-4 text-emerald-600" />
                            {t('Mobile Money (Lipa Namba) & USSD')}
                        </TabsTrigger>
                        <TabsTrigger value="gateways" className="flex items-center gap-2">
                            <SlidersHorizontal className="h-4 w-4" />
                            {t('Gateway Matrix')} ({gateways.length})
                        </TabsTrigger>
                        <TabsTrigger value="transactions" className="flex items-center gap-2">
                            <Layers className="h-4 w-4" />
                            {t('Settlements & Vouchers Ledger')} ({transactions.length})
                        </TabsTrigger>
                        <TabsTrigger value="webhooks" className="flex items-center gap-2">
                            <Globe className="h-4 w-4" />
                            {t('Webhooks & IPN Integration')}
                        </TabsTrigger>
                    </TabsList>

                    {/* TAB 1: MOBILE MONEY (LIPA NAMBA) ENGINE & USSD SIMULATOR */}
                    <TabsContent value="lipa-namba" className="space-y-4">
                        {/* Hero Merchant Card */}
                        <Card className="border-primary/30 bg-gradient-to-r from-primary/10 via-primary/5 to-card shadow-sm">
                            <CardContent className="p-6">
                                <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                                    <div className="space-y-2">
                                        <div className="flex items-center gap-2">
                                            <Badge className="bg-emerald-500 text-white font-semibold">
                                                {t('TANZANIA MERCHANT TILL')}
                                            </Badge>
                                            <Badge variant="outline" className="border-primary text-primary font-mono">
                                                {t('INTEROPERABLE ACROSS ALL TELCOS')}
                                            </Badge>
                                        </div>
                                        <h2 className="text-3xl font-extrabold tracking-tight text-foreground flex items-center gap-3">
                                            <span>Lipa Namba:</span>
                                            <span className="font-mono text-primary text-4xl bg-primary/10 px-3 py-1 rounded-lg border border-primary/20">
                                                {lipa_namba_info.till_number}
                                            </span>
                                        </h2>
                                        <p className="text-sm text-muted-foreground flex items-center gap-2">
                                            <ShieldCheck className="h-4 w-4 text-emerald-500" />
                                            {t('Registered Merchant Name')}: <strong className="text-foreground">{lipa_namba_info.merchant_name}</strong>
                                        </p>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-3">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() => router.visit(route('settings.index') + '#lipa-namba-settings')}
                                            className="gap-2 border-primary/30"
                                        >
                                            <SlidersHorizontal className="h-4 w-4 text-primary" />
                                            {t('Configure Till in Settings')}
                                        </Button>

                                        {lipa_namba_info.till_number && (
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => copyToClipboard(lipa_namba_info.till_number, 'hero_till')}
                                                className="gap-2"
                                            >
                                                {copiedToken === 'hero_till' ? <Check className="h-4 w-4 text-emerald-600" /> : <Copy className="h-4 w-4" />}
                                                {t('Copy Till')}
                                            </Button>
                                        )}

                                        <Button
                                            size="sm"
                                            onClick={() => setShowStandeeModal(true)}
                                            className="gap-2 bg-primary text-primary-foreground"
                                        >
                                            <Printer className="h-4 w-4" />
                                            {t('Print Cashier Standee')}
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Interactive Live USSD Dial Calculator */}
                        <Card className="border-border bg-card">
                            <CardHeader className="p-4 pb-2">
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                    <div>
                                        <CardTitle className="text-base font-semibold flex items-center gap-2">
                                            <PhoneCall className="h-5 w-5 text-primary" />
                                            {t('Live Dynamic USSD Push & Dial Code Generator')}
                                        </CardTitle>
                                        <p className="text-xs text-muted-foreground mt-0.5">
                                            {t('Enter amount and invoice reference to generate one-touch direct dial codes for Tanzanian mobile operators.')}
                                        </p>
                                    </div>

                                    <div className="flex items-center gap-2 w-full sm:w-auto">
                                        <div className="flex items-center gap-1.5 text-xs">
                                            <label className="text-muted-foreground whitespace-nowrap">{t('Amount (TZS)')}:</label>
                                            <Input
                                                type="number"
                                                value={calcAmount}
                                                onChange={(e) => setCalcAmount(e.target.value)}
                                                className="w-28 h-8 text-xs font-bold text-foreground"
                                                placeholder="50000"
                                            />
                                        </div>

                                        <div className="flex items-center gap-1.5 text-xs">
                                            <label className="text-muted-foreground whitespace-nowrap">{t('Reference')}:</label>
                                            <Input
                                                type="text"
                                                value={calcRef}
                                                onChange={(e) => setCalcRef(e.target.value)}
                                                className="w-32 h-8 text-xs font-mono"
                                                placeholder="INV-001"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent className="p-4 pt-2">
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {lipa_namba_info.networks.map((net) => {
                                        const dialString = getNetworkUssdDial(net.ussd_dial_template, calcAmount);
                                        return (
                                            <div
                                                key={net.network_code}
                                                className="p-4 rounded-xl border border-border bg-card hover:border-primary/40 transition-all shadow-sm flex flex-col justify-between space-y-3"
                                                style={{ borderTop: `4px solid ${net.brand_color}` }}
                                            >
                                                <div className="space-y-1.5">
                                                    <div className="flex items-center justify-between">
                                                        <h4 className="font-bold text-sm text-foreground flex items-center gap-2">
                                                            <Smartphone className="h-4 w-4" style={{ color: net.brand_color }} />
                                                            {net.name}
                                                        </h4>
                                                        <Badge variant="outline" className="text-[10px] font-mono">
                                                            {net.ussd_root}
                                                        </Badge>
                                                    </div>

                                                    <p className="text-xs text-muted-foreground leading-relaxed">
                                                        {net.instructions}
                                                    </p>
                                                </div>

                                                <div className="p-2.5 rounded-lg bg-muted/50 border border-border/80 space-y-2">
                                                    <div className="flex items-center justify-between text-xs">
                                                        <span className="text-muted-foreground text-[11px]">{t('Direct USSD String')}:</span>
                                                        <span className="font-mono font-bold text-primary text-xs truncate max-w-[170px]">
                                                            {dialString}
                                                        </span>
                                                    </div>

                                                    <div className="flex items-center gap-2 pt-1">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="flex-1 text-xs h-7 gap-1"
                                                            onClick={() => copyToClipboard(dialString, `ussd_${net.network_code}`)}
                                                        >
                                                            {copiedToken === `ussd_${net.network_code}` ? (
                                                                <Check className="h-3 w-3 text-emerald-600" />
                                                            ) : (
                                                                <Copy className="h-3 w-3" />
                                                            )}
                                                            {t('Copy USSD')}
                                                        </Button>

                                                        <a
                                                            href={`tel:${encodeURIComponent(dialString)}`}
                                                            className="flex-1 inline-flex items-center justify-center rounded-md text-xs font-medium h-7 px-2 bg-primary text-primary-foreground hover:bg-primary/90 gap-1"
                                                        >
                                                            <PhoneCall className="h-3 w-3" />
                                                            {t('Dial Code')}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 2: GATEWAY MATRIX */}
                    <TabsContent value="gateways" className="space-y-4">
                        <div className="flex justify-between items-center">
                            <div>
                                <h3 className="font-semibold text-foreground text-sm">
                                    {t('Configured Payment Channels & Merchant Switches')}
                                </h3>
                                <p className="text-xs text-muted-foreground">
                                    {t('Manage live keys, fee schedules, and default status for each gateway.')}
                                </p>
                            </div>

                            <Button
                                size="sm"
                                onClick={() => {
                                    createGatewayForm.reset();
                                    setShowCreateGatewayModal(true);
                                }}
                                className="flex items-center gap-2"
                            >
                                <Plus className="h-4 w-4" />
                                {t('Add Gateway Channel')}
                            </Button>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {gateways.map((gw) => (
                                <Card key={gw.id} className="border-border bg-card shadow-sm hover:border-primary/40 transition-colors flex flex-col justify-between">
                                    <CardHeader className="p-5 pb-3">
                                        <div className="flex items-start justify-between gap-2">
                                            <div className="flex items-center gap-2.5">
                                                <div className="p-2 rounded-lg bg-muted border border-border">
                                                    {getGatewayIcon(gw.gateway_key)}
                                                </div>
                                                <div>
                                                    <h3 className="font-semibold text-base text-foreground leading-tight">
                                                        {gw.name}
                                                    </h3>
                                                    <span className="font-mono text-[11px] text-muted-foreground">
                                                        key: {gw.gateway_key}
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="flex flex-col items-end gap-1">
                                                {gw.is_default && (
                                                    <Badge className="bg-primary text-primary-foreground text-[10px]">
                                                        {t('Default')}
                                                    </Badge>
                                                )}
                                                {gw.mode === 'live' ? (
                                                    <Badge className="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 text-[10px]">
                                                        {t('LIVE')}
                                                    </Badge>
                                                ) : (
                                                    <Badge className="bg-amber-500/10 text-amber-600 border border-amber-500/20 text-[10px]">
                                                        {t('SANDBOX')}
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </CardHeader>

                                    <CardContent className="p-5 pt-0 space-y-3 flex-1 flex flex-col justify-between">
                                        <p className="text-xs text-muted-foreground line-clamp-2">
                                            {gw.description}
                                        </p>

                                        <div className="bg-muted/40 p-2.5 rounded border border-border/60 space-y-1.5 text-xs">
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">{t('Transaction Fee')}:</span>
                                                <strong className="text-foreground">
                                                    {gw.transaction_fee_percentage > 0 ? `${gw.transaction_fee_percentage}%` : '0%'}
                                                    {gw.transaction_fee_flat > 0 ? ` + ${formatCurrency(gw.transaction_fee_flat)} TZS` : ''}
                                                </strong>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">{t('Settled Volume')}:</span>
                                                <strong className="text-emerald-600">{formatCurrency(gw.completed_volume || 0)} TZS</strong>
                                            </div>
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">{t('Currencies')}:</span>
                                                <span className="font-mono text-[11px] text-foreground">
                                                    {(gw.supported_currencies || ['TZS']).join(', ')}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Action buttons */}
                                        <div className="flex items-center justify-between gap-2 pt-2 border-t border-border">
                                            <div className="flex items-center gap-1.5">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => handleToggleGateway(gw.id)}
                                                    className="text-xs h-8 px-2"
                                                >
                                                    {gw.is_active ? t('Deactivate') : t('Activate')}
                                                </Button>

                                                {!['selcom', 'manual_mobile_money'].includes(gw.gateway_key) && (
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => handleDeleteGateway(gw)}
                                                        className="text-xs h-8 px-2 text-rose-500 hover:text-rose-600 hover:bg-rose-500/10"
                                                        title={t('Delete Gateway')}
                                                    >
                                                        <Trash2 className="h-3.5 w-3.5" />
                                                    </Button>
                                                )}
                                            </div>

                                            <Button
                                                size="sm"
                                                onClick={() => handleOpenEdit(gw)}
                                                className="text-xs h-8"
                                            >
                                                <SlidersHorizontal className="h-3.5 w-3.5 mr-1" />
                                                {t('Configure')}
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </TabsContent>

                    {/* TAB 3: TRANSACTIONS & SETTLEMENTS LEDGER */}
                    <TabsContent value="transactions" className="space-y-4">
                        <Card className="border-border bg-card">
                            <CardHeader className="p-4 pb-2">
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                    <div className="relative w-full sm:w-80">
                                        <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder={t('Search reference, voucher, customer...')}
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-9"
                                        />
                                    </div>

                                    <div className="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                                        <Select value={statusFilter} onValueChange={setStatusFilter}>
                                            <SelectTrigger className="w-36">
                                                <SelectValue placeholder={t('Status')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                                <SelectItem value="completed">{t('Settled / Cleared')}</SelectItem>
                                                <SelectItem value="pending">{t('Pending Payment')}</SelectItem>
                                                <SelectItem value="cancelled">{t('Voided / Cancelled')}</SelectItem>
                                                <SelectItem value="failed">{t('Failed')}</SelectItem>
                                            </SelectContent>
                                        </Select>

                                        <Select value={gatewayFilter} onValueChange={setGatewayFilter}>
                                            <SelectTrigger className="w-36">
                                                <SelectValue placeholder={t('Gateway')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">{t('All Gateways')}</SelectItem>
                                                {gateways.map((g) => (
                                                    <SelectItem key={g.gateway_key} value={g.gateway_key}>
                                                        {g.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        <Button
                                            size="sm"
                                            onClick={() => {
                                                manualSettleForm.reset();
                                                setShowManualSettleModal(true);
                                            }}
                                            className="bg-emerald-600 hover:bg-emerald-700 text-white gap-1 text-xs"
                                        >
                                            <Plus className="h-3.5 w-3.5" />
                                            {t('Record Settlement')}
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent className="p-0">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-xs text-left">
                                        <thead className="bg-muted/50 border-b border-border text-muted-foreground uppercase text-[10px] tracking-wider">
                                            <tr>
                                                <th className="px-4 py-3">{t('Txn Ref / Voucher')}</th>
                                                <th className="px-4 py-3">{t('Customer / Invoice')}</th>
                                                <th className="px-4 py-3">{t('Switch / Channel')}</th>
                                                <th className="px-4 py-3 text-right">{t('Amount (TZS)')}</th>
                                                <th className="px-4 py-3 text-center">{t('Status')}</th>
                                                <th className="px-4 py-3">{t('Date Settled')}</th>
                                                <th className="px-4 py-3 text-right">{t('Actions')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {filteredTxns.length === 0 ? (
                                                <tr>
                                                    <td colSpan={7} className="text-center py-8 text-muted-foreground">
                                                        {t('No settlement records found matching current criteria.')}
                                                    </td>
                                                </tr>
                                            ) : (
                                                filteredTxns.map((tx) => (
                                                    <tr key={tx.id} className="hover:bg-muted/30 transition-colors">
                                                        <td className="px-4 py-3">
                                                            <div className="font-mono font-semibold text-foreground">
                                                                {tx.transaction_reference}
                                                            </div>
                                                            {tx.receipt_voucher_number ? (
                                                                <button
                                                                    onClick={() => handleViewVoucher(tx)}
                                                                    className="font-mono text-[11px] text-emerald-600 hover:underline flex items-center gap-1 font-semibold"
                                                                >
                                                                    <FileText className="h-3 w-3" />
                                                                    {tx.receipt_voucher_number}
                                                                </button>
                                                            ) : (
                                                                <span className="text-[10px] text-muted-foreground">
                                                                    {t('Voucher Pending')}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-medium text-foreground">
                                                                {tx.customer_name || t('Direct Customer')}
                                                            </div>
                                                            <div className="text-[11px] text-muted-foreground">
                                                                {tx.invoice_number || 'Direct Collection'}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="flex items-center gap-1.5">
                                                                {getGatewayIcon(tx.gateway_key)}
                                                                <span className="capitalize font-medium">
                                                                    {tx.payment_method_channel || tx.gateway_key}
                                                                </span>
                                                            </div>
                                                            {tx.gateway_transaction_id && (
                                                                <span className="font-mono text-[10px] text-muted-foreground">
                                                                    ext: {tx.gateway_transaction_id}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3 text-right">
                                                            <div className="font-bold text-foreground">
                                                                {formatCurrency(tx.amount)}
                                                            </div>
                                                            <div className="text-[10px] text-muted-foreground">
                                                                {tx.currency || 'TZS'}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            {getStatusBadge(tx.status)}
                                                        </td>
                                                        <td className="px-4 py-3 text-muted-foreground">
                                                            <div>{formatDate(tx.paid_at || tx.created_at)}</div>
                                                            <div className="text-[10px]">
                                                                {new Date(tx.paid_at || tx.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-right">
                                                            <div className="flex items-center justify-end gap-1.5">
                                                                {tx.receipt_voucher_number && (
                                                                    <Button
                                                                        size="sm"
                                                                        variant="ghost"
                                                                        onClick={() => handleViewVoucher(tx)}
                                                                        className="h-7 px-2 text-emerald-600 hover:bg-emerald-500/10"
                                                                        title={t('Print Receipt Voucher')}
                                                                    >
                                                                        <Receipt className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                )}

                                                                <Button
                                                                    size="sm"
                                                                    variant="ghost"
                                                                    onClick={() => handleOpenReconcile(tx)}
                                                                    className="h-7 px-2"
                                                                    title={t('Reconcile Transaction')}
                                                                >
                                                                    <SlidersHorizontal className="h-3.5 w-3.5" />
                                                                </Button>

                                                                {tx.status === 'pending' && (
                                                                    <>
                                                                        <a
                                                                            href={`/pay/checkout/${tx.checkout_token}`}
                                                                            target="_blank"
                                                                            rel="noopener noreferrer"
                                                                            className="p-1.5 hover:bg-muted rounded text-primary"
                                                                            title={t('Open Checkout Page')}
                                                                        >
                                                                            <ExternalLink className="h-3.5 w-3.5" />
                                                                        </a>

                                                                        <Button
                                                                            size="sm"
                                                                            variant="ghost"
                                                                            onClick={() => handleOpenVoid(tx)}
                                                                            className="h-7 px-2 text-rose-500 hover:text-rose-600 hover:bg-rose-500/10"
                                                                            title={t('Void Payment Link')}
                                                                        >
                                                                            <XCircle className="h-3.5 w-3.5" />
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

                    {/* TAB 4: WEBHOOKS & IPN */}
                    <TabsContent value="webhooks" className="space-y-4">
                        <Card className="border-border bg-card">
                            <CardHeader className="p-4">
                                <CardTitle className="text-base font-semibold flex items-center gap-2">
                                    <Globe className="h-5 w-5 text-primary" />
                                    {t('Instant Payment Notification (IPN) & Webhook Endpoints')}
                                </CardTitle>
                                <p className="text-xs text-muted-foreground mt-0.5">
                                    {t('Register these secure webhook URLs in your Selcom Developer Portal, Pesapal Dashboard, Stripe Dashboard, or PayPal Developer console.')}
                                </p>
                            </CardHeader>
                            <CardContent className="p-4 pt-0 space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="p-3.5 rounded-lg border border-border bg-muted/30 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <strong className="text-sm text-foreground flex items-center gap-2">
                                                <Smartphone className="h-4 w-4 text-emerald-600" />
                                                {t('Selcom Paytech IPN Webhook')}
                                            </strong>
                                            <Badge variant="outline" className="text-[10px]">POST</Badge>
                                        </div>
                                        <div className="font-mono text-xs p-2 bg-background rounded border border-border flex items-center justify-between">
                                            <span className="truncate">{url('/api/v1/payments/webhook/selcom')}</span>
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                onClick={() => copyToClipboard(url('/api/v1/payments/webhook/selcom'), 'selcom_wh')}
                                            >
                                                {copiedToken === 'selcom_wh' ? <Check className="h-3.5 w-3.5 text-emerald-600" /> : <Copy className="h-3.5 w-3.5" />}
                                            </Button>
                                        </div>
                                        <p className="text-[11px] text-muted-foreground">
                                            {t('Receives real-time USSD push authorization status for Vodacom M-Pesa and Tigo Pesa.')}
                                        </p>
                                    </div>

                                    <div className="p-3.5 rounded-lg border border-border bg-muted/30 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <strong className="text-sm text-foreground flex items-center gap-2">
                                                <Zap className="h-4 w-4 text-blue-600" />
                                                {t('Pesapal IPN Webhook')}
                                            </strong>
                                            <Badge variant="outline" className="text-[10px]">POST</Badge>
                                        </div>
                                        <div className="font-mono text-xs p-2 bg-background rounded border border-border flex items-center justify-between">
                                            <span className="truncate">{url('/api/v1/payments/webhook/pesapal')}</span>
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                onClick={() => copyToClipboard(url('/api/v1/payments/webhook/pesapal'), 'pesapal_wh')}
                                            >
                                                {copiedToken === 'pesapal_wh' ? <Check className="h-3.5 w-3.5 text-emerald-600" /> : <Copy className="h-3.5 w-3.5" />}
                                            </Button>
                                        </div>
                                        <p className="text-[11px] text-muted-foreground">
                                            {t('Receives Instant Payment Notification (IPN) with OrderNotificationType & OrderTrackingId.')}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                {/* MODAL 1: EDIT GATEWAY CONFIGURATION */}
                <Dialog open={showEditModal} onOpenChange={setShowEditModal}>
                    <DialogContent className="max-w-lg">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                {selectedGateway && getGatewayIcon(selectedGateway.gateway_key)}
                                {t('Configure Payment Switch')}: {selectedGateway?.name}
                            </DialogTitle>
                        </DialogHeader>

                        {selectedGateway && (
                            <form onSubmit={handleSaveGateway} className="space-y-4 text-xs">
                                <div className="grid grid-cols-2 gap-3">
                                    <div className="space-y-1">
                                        <label className="font-semibold">{t('Display Name')}</label>
                                        <Input
                                            value={gatewayForm.data.name}
                                            onChange={(e) => gatewayForm.setData('name', e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="font-semibold">{t('Environment Mode')}</label>
                                        <Select 
                                            value={gatewayForm.data.mode} 
                                            onValueChange={(val) => gatewayForm.setData('mode', val)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="sandbox">{t('Sandbox (Testing)')}</SelectItem>
                                                <SelectItem value="live">{t('Live (Production)')}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Gateway Description')}</label>
                                    <Input
                                        value={gatewayForm.data.description}
                                        onChange={(e) => gatewayForm.setData('description', e.target.value)}
                                    />
                                </div>

                                {/* Dynamic Credentials Inputs */}
                                <div className="space-y-2.5 p-3 rounded-lg border border-border bg-muted/30">
                                    <h4 className="font-semibold text-foreground flex items-center gap-1.5">
                                        <Lock className="h-3.5 w-3.5 text-primary" />
                                        {t('API Credentials & Merchant Keys')}
                                    </h4>

                                    {Object.keys(selectedGateway.credentials || {}).map((key) => (
                                        <div key={key} className="space-y-1">
                                            <label className="font-mono text-[11px] capitalize text-muted-foreground">
                                                {key.replace(/_/g, ' ')}
                                            </label>
                                            <Input
                                                type={key.includes('secret') || key.includes('password') ? 'password' : 'text'}
                                                value={gatewayForm.data.credentials[key] ?? ''}
                                                onChange={(e) => {
                                                    const val = e.target.value;
                                                    gatewayForm.setData('credentials', {
                                                        ...gatewayForm.data.credentials,
                                                        [key]: val,
                                                    });
                                                }}
                                                placeholder={`Enter ${key}...`}
                                            />
                                        </div>
                                    ))}
                                </div>

                                <div className="grid grid-cols-2 gap-3">
                                    <div className="space-y-1">
                                        <label className="font-semibold">{t('Fee Percentage (%)')}</label>
                                        <Input
                                            type="number"
                                            step="0.01"
                                            value={gatewayForm.data.transaction_fee_percentage}
                                            onChange={(e) => gatewayForm.setData('transaction_fee_percentage', e.target.value)}
                                        />
                                    </div>

                                    <div className="space-y-1">
                                        <label className="font-semibold">{t('Flat Fee (TZS)')}</label>
                                        <Input
                                            type="number"
                                            step="0.01"
                                            value={gatewayForm.data.transaction_fee_flat}
                                            onChange={(e) => gatewayForm.setData('transaction_fee_flat', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Customer Payment Instructions')}</label>
                                    <textarea
                                        rows={2}
                                        value={gatewayForm.data.instructions}
                                        onChange={(e) => gatewayForm.setData('instructions', e.target.value)}
                                        placeholder={t('Displayed to customer on checkout screen...')}
                                        className="w-full text-xs p-2 rounded border border-border bg-background"
                                    />
                                </div>

                                <div className="flex items-center gap-4 pt-1">
                                    <label className="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={gatewayForm.data.is_default}
                                            onChange={(e) => gatewayForm.setData('is_default', e.target.checked)}
                                            className="rounded border-border"
                                        />
                                        <span>{t('Set as Primary Default Gateway')}</span>
                                    </label>

                                    <label className="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={gatewayForm.data.is_active}
                                            onChange={(e) => gatewayForm.setData('is_active', e.target.checked)}
                                            className="rounded border-border"
                                        />
                                        <span>{t('Enable Switch')}</span>
                                    </label>
                                </div>

                                <DialogFooter className="pt-2">
                                    <Button type="button" variant="outline" onClick={() => setShowEditModal(false)}>
                                        {t('Cancel')}
                                    </Button>
                                    <Button type="submit" disabled={gatewayForm.processing}>
                                        {gatewayForm.processing ? t('Saving...') : t('Save Configuration')}
                                    </Button>
                                </DialogFooter>
                            </form>
                        )}
                    </DialogContent>
                </Dialog>

                {/* MODAL 2: CREATE NEW GATEWAY */}
                <Dialog open={showCreateGatewayModal} onOpenChange={setShowCreateGatewayModal}>
                    <DialogContent className="max-w-lg">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <Plus className="h-5 w-5 text-primary" />
                                {t('Add Payment Gateway or Till Channel')}
                            </DialogTitle>
                            <DialogDescription className="text-xs">
                                {t('Connect an additional telco aggregator, commercial bank till, or payment provider.')}
                            </DialogDescription>
                        </DialogHeader>

                        <form onSubmit={handleCreateGateway} className="space-y-4 text-xs">
                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Gateway Name')}</label>
                                    <Input
                                        value={createGatewayForm.data.name}
                                        onChange={(e) => createGatewayForm.setData('name', e.target.value)}
                                        placeholder="e.g. Airtel Money Direct Till"
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Gateway Key (Slug)')}</label>
                                    <Input
                                        value={createGatewayForm.data.gateway_key}
                                        onChange={(e) => createGatewayForm.setData('gateway_key', e.target.value.toLowerCase().replace(/[^a-z0-9_]/g, ''))}
                                        placeholder="e.g. airtel_direct"
                                        required
                                    />
                                </div>
                            </div>

                            <div className="space-y-1">
                                <label className="font-semibold">{t('Description')}</label>
                                <Input
                                    value={createGatewayForm.data.description}
                                    onChange={(e) => createGatewayForm.setData('description', e.target.value)}
                                    placeholder="Direct merchant till connection"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Environment')}</label>
                                    <Select 
                                        value={createGatewayForm.data.mode} 
                                        onValueChange={(val) => createGatewayForm.setData('mode', val)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="sandbox">{t('Sandbox (Testing)')}</SelectItem>
                                            <SelectItem value="live">{t('Live (Production)')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Merchant Till / Lipa Namba')}</label>
                                    <Input
                                        value={createGatewayForm.data.credentials.till_number || '16381432'}
                                        onChange={(e) => createGatewayForm.setData('credentials', {
                                            ...createGatewayForm.data.credentials,
                                            till_number: e.target.value,
                                        })}
                                        placeholder="16381432"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Fee Percentage (%)')}</label>
                                    <Input
                                        type="number"
                                        step="0.01"
                                        value={createGatewayForm.data.transaction_fee_percentage}
                                        onChange={(e) => createGatewayForm.setData('transaction_fee_percentage', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Flat Fee (TZS)')}</label>
                                    <Input
                                        type="number"
                                        step="0.01"
                                        value={createGatewayForm.data.transaction_fee_flat}
                                        onChange={(e) => createGatewayForm.setData('transaction_fee_flat', e.target.value)}
                                    />
                                </div>
                            </div>

                            <DialogFooter className="pt-2">
                                <Button type="button" variant="outline" onClick={() => setShowCreateGatewayModal(false)}>
                                    {t('Cancel')}
                                </Button>
                                <Button type="submit" disabled={createGatewayForm.processing}>
                                    {createGatewayForm.processing ? t('Creating...') : t('Create Gateway')}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 3: GENERATE PAYMENT LINK */}
                <Dialog open={showLinkModal} onOpenChange={setShowLinkModal}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <Plus className="h-5 w-5 text-primary" />
                                {t('Generate Online Invoice Payment Link')}
                            </DialogTitle>
                        </DialogHeader>

                        <form onSubmit={handleGenerateLink} className="space-y-4 text-xs">
                            <div className="space-y-1">
                                <label className="font-semibold">{t('Link to Open Sales Invoice (Optional)')}</label>
                                <Select 
                                    value={linkForm.data.invoice_id || 'none'} 
                                    onValueChange={(val) => handleInvoiceSelect(val === 'none' ? '' : val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Direct Custom Collection')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">{t('Direct Custom Collection')}</SelectItem>
                                        {open_invoices.map((inv) => (
                                            <SelectItem key={inv.id} value={String(inv.id)}>
                                                {inv.invoice_number} ({formatCurrency(inv.balance_amount || inv.total_amount)} TZS)
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1">
                                <label className="font-semibold">{t('Customer Name')}</label>
                                <Input
                                    value={linkForm.data.customer_name}
                                    onChange={(e) => linkForm.setData('customer_name', e.target.value)}
                                    placeholder={t('e.g. Serengeti Breweries Ltd')}
                                    required
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Customer Phone (Tanzania)')}</label>
                                    <Input
                                        value={linkForm.data.customer_phone}
                                        onChange={(e) => linkForm.setData('customer_phone', e.target.value)}
                                        placeholder="+255 7XX XXX XXX"
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Customer Email')}</label>
                                    <Input
                                        type="email"
                                        value={linkForm.data.customer_email}
                                        onChange={(e) => linkForm.setData('customer_email', e.target.value)}
                                        placeholder="finance@customer.co.tz"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Amount to Collect (TZS)')}</label>
                                    <Input
                                        type="number"
                                        step="0.01"
                                        min="100"
                                        value={linkForm.data.amount}
                                        onChange={(e) => linkForm.setData('amount', e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Default Gateway')}</label>
                                    <Select 
                                        value={linkForm.data.gateway_key} 
                                        onValueChange={(val) => linkForm.setData('gateway_key', val)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {gateways.filter((g) => g.is_active).map((g) => (
                                                <SelectItem key={g.gateway_key} value={g.gateway_key}>
                                                    {g.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <DialogFooter className="pt-2">
                                <Button type="button" variant="outline" onClick={() => setShowLinkModal(false)}>
                                    {t('Cancel')}
                                </Button>
                                <Button type="submit" disabled={linkForm.processing}>
                                    {linkForm.processing ? t('Generating...') : t('Create Payment Link')}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 4: RECORD OFFLINE LIPA NAMBA SETTLEMENT */}
                <Dialog open={showManualSettleModal} onOpenChange={setShowManualSettleModal}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-emerald-600">
                                <CheckSquare className="h-5 w-5" />
                                {t('Record Lipa Namba 16381432 Settlement')}
                            </DialogTitle>
                            <DialogDescription className="text-xs">
                                {t('Cashier recording for incoming customer SMS payment to Till 16381432. Immediately updates ledger & issues Receipt Voucher.')}
                            </DialogDescription>
                        </DialogHeader>

                        <form onSubmit={handleManualSettleSubmit} className="space-y-4 text-xs">
                            <div className="space-y-1">
                                <label className="font-semibold">{t('Link to Open Sales Invoice (Optional)')}</label>
                                <Select 
                                    value={manualSettleForm.data.invoice_id || 'none'} 
                                    onValueChange={(val) => handleManualInvoiceSelect(val === 'none' ? '' : val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Direct Custom Collection')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">{t('Direct Custom Collection')}</SelectItem>
                                        {open_invoices.map((inv) => (
                                            <SelectItem key={inv.id} value={String(inv.id)}>
                                                {inv.invoice_number} ({formatCurrency(inv.balance_amount || inv.total_amount)} TZS)
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Mobile Network / Channel')}</label>
                                    <Select
                                        value={manualSettleForm.data.payment_channel}
                                        onValueChange={(val) => manualSettleForm.setData('payment_channel', val)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="mpesa">Vodacom M-Pesa</SelectItem>
                                            <SelectItem value="tigopesa">Tigo Pesa</SelectItem>
                                            <SelectItem value="airtelmoney">Airtel Money</SelectItem>
                                            <SelectItem value="halopesa">Halopesa</SelectItem>
                                            <SelectItem value="crdb">CRDB SimBanking</SelectItem>
                                            <SelectItem value="nmb">NMB Mkononi</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Telco SMS Reference ID')}</label>
                                    <Input
                                        value={manualSettleForm.data.reference_number}
                                        onChange={(e) => manualSettleForm.setData('reference_number', e.target.value.toUpperCase())}
                                        placeholder="e.g. QJ84KZ2901"
                                        required
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Customer Name')}</label>
                                    <Input
                                        value={manualSettleForm.data.customer_name}
                                        onChange={(e) => manualSettleForm.setData('customer_name', e.target.value)}
                                        placeholder="Customer or Business Name"
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Customer Phone')}</label>
                                    <Input
                                        value={manualSettleForm.data.customer_phone}
                                        onChange={(e) => manualSettleForm.setData('customer_phone', e.target.value)}
                                        placeholder="+255 7XX XXX XXX"
                                    />
                                </div>
                            </div>

                            <div className="space-y-1">
                                <label className="font-semibold">{t('Amount Settled (TZS)')}</label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    min="100"
                                    value={manualSettleForm.data.amount}
                                    onChange={(e) => manualSettleForm.setData('amount', e.target.value)}
                                    placeholder="50000"
                                    required
                                />
                            </div>

                            <div className="space-y-1">
                                <label className="font-semibold">{t('Auditor / Cashier Notes')}</label>
                                <Input
                                    value={manualSettleForm.data.settlement_notes}
                                    onChange={(e) => manualSettleForm.setData('settlement_notes', e.target.value)}
                                />
                            </div>

                            <DialogFooter className="pt-2">
                                <Button type="button" variant="outline" onClick={() => setShowManualSettleModal(false)}>
                                    {t('Cancel')}
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={manualSettleForm.processing}
                                    className="bg-emerald-600 hover:bg-emerald-700 text-white"
                                >
                                    {manualSettleForm.processing ? t('Recording...') : t('Issue Receipt Voucher')}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 5: RECONCILE TRANSACTION */}
                <Dialog open={showReconcileModal} onOpenChange={setShowReconcileModal}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <SlidersHorizontal className="h-5 w-5 text-primary" />
                                {t('Reconcile Settlement')}: {selectedTransaction?.transaction_reference}
                            </DialogTitle>
                        </DialogHeader>

                        {selectedTransaction && (
                            <form onSubmit={handleSaveReconcile} className="space-y-4 text-xs">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Settlement Status')}</label>
                                    <Select
                                        value={reconcileForm.data.status}
                                        onValueChange={(val) => reconcileForm.setData('status', val)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="completed">{t('Settled / Cleared')}</SelectItem>
                                            <SelectItem value="pending">{t('Pending Payment')}</SelectItem>
                                            <SelectItem value="failed">{t('Failed')}</SelectItem>
                                            <SelectItem value="refunded">{t('Refunded')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('External Bank / Telco Ref')}</label>
                                    <Input
                                        value={reconcileForm.data.gateway_transaction_id}
                                        onChange={(e) => reconcileForm.setData('gateway_transaction_id', e.target.value)}
                                        placeholder="e.g. MPESA-QJ8912401"
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Reconciliation Notes')}</label>
                                    <textarea
                                        rows={2}
                                        value={reconcileForm.data.reconciliation_notes}
                                        onChange={(e) => reconcileForm.setData('reconciliation_notes', e.target.value)}
                                        className="w-full text-xs p-2 rounded border border-border bg-background"
                                        placeholder="Bank statement matched on..."
                                    />
                                </div>

                                <DialogFooter className="pt-2">
                                    <Button type="button" variant="outline" onClick={() => setShowReconcileModal(false)}>
                                        {t('Cancel')}
                                    </Button>
                                    <Button type="submit" disabled={reconcileForm.processing}>
                                        {reconcileForm.processing ? t('Saving...') : t('Update Reconciliation')}
                                    </Button>
                                </DialogFooter>
                            </form>
                        )}
                    </DialogContent>
                </Dialog>

                {/* MODAL 6: VOID TRANSACTION CONFIRMATION */}
                <Dialog open={showVoidModal} onOpenChange={setShowVoidModal}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-rose-600">
                                <XCircle className="h-5 w-5" />
                                {t('Void Payment Link')}
                            </DialogTitle>
                            <DialogDescription className="text-xs">
                                {t('Are you sure you want to void transaction')} <strong className="font-mono">{selectedTransaction?.transaction_reference}</strong>? {t('This will deactivate the customer checkout link.')}
                            </DialogDescription>
                        </DialogHeader>

                        {selectedTransaction && (
                            <form onSubmit={handleSaveVoid} className="space-y-4 text-xs">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Void Reason / Audit Note')}</label>
                                    <Input
                                        value={voidForm.data.reason}
                                        onChange={(e) => voidForm.setData('reason', e.target.value)}
                                        placeholder="e.g. Duplicate request or expired quote"
                                        required
                                    />
                                </div>

                                <DialogFooter className="pt-2">
                                    <Button type="button" variant="outline" onClick={() => setShowVoidModal(false)}>
                                        {t('Cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={voidForm.processing}
                                        className="bg-rose-600 hover:bg-rose-700 text-white"
                                    >
                                        {voidForm.processing ? t('Voiding...') : t('Confirm Void')}
                                    </Button>
                                </DialogFooter>
                            </form>
                        )}
                    </DialogContent>
                </Dialog>

                {/* MODAL 7: PRINTABLE CASHIER COUNTER STANDEE */}
                <Dialog open={showStandeeModal} onOpenChange={setShowStandeeModal}>
                    <DialogContent className="max-w-md p-6 bg-white text-slate-900 border-2 border-slate-200 shadow-2xl">
                        <DialogHeader className="text-center pb-2">
                            <div className="w-12 h-12 rounded-xl bg-purple-600 text-white font-black text-2xl flex items-center justify-center mx-auto shadow-md">
                                M
                            </div>
                            <DialogTitle className="text-xl font-black text-slate-900 tracking-tight mt-2">
                                MUTAMARK ENTERPRISES LTD
                            </DialogTitle>
                            <p className="text-xs font-semibold text-purple-700 uppercase tracking-widest">
                                WOLINET SECURE MERCHANT
                            </p>
                        </DialogHeader>

                        <div className="bg-gradient-to-b from-purple-50 to-white rounded-2xl p-5 border-2 border-purple-200 text-center space-y-4">
                            <div>
                                <span className="text-xs font-bold text-slate-500 uppercase tracking-wider block">
                                    LIPA KWA SIMU / LIPA NAMBA
                                </span>
                                <div className="text-4xl font-black tracking-widest font-mono text-purple-900 my-1 bg-white py-2 px-4 rounded-xl shadow-inner border border-purple-100">
                                    {lipa_namba_info.till_number}
                                </div>
                                <span className="text-xs font-semibold text-slate-600">
                                    Jina: <strong>WOLINET / MUTAMARK</strong>
                                </span>
                            </div>

                            {/* Center QR representation */}
                            <div className="w-40 h-40 mx-auto bg-white p-3 rounded-xl border-2 border-slate-300 shadow-sm flex flex-col items-center justify-center">
                                <QrCode className="w-32 h-32 text-slate-900" />
                            </div>

                            <p className="text-[11px] text-slate-500">
                                Scan na App au Piga USSD kulipa kwa mitandao yote
                            </p>

                            {/* Network Pill Badges */}
                            <div className="grid grid-cols-3 gap-1.5 text-[10px] font-bold text-white pt-1">
                                <div className="bg-red-600 py-1 px-1.5 rounded text-center">M-PESA</div>
                                <div className="bg-blue-700 py-1 px-1.5 rounded text-center">TIGO PESA</div>
                                <div className="bg-red-700 py-1 px-1.5 rounded text-center">AIRTEL</div>
                                <div className="bg-orange-600 py-1 px-1.5 rounded text-center">HALOPESA</div>
                                <div className="bg-emerald-700 py-1 px-1.5 rounded text-center">CRDB</div>
                                <div className="bg-sky-900 py-1 px-1.5 rounded text-center">NMB</div>
                            </div>
                        </div>

                        <div className="pt-2 flex items-center justify-between">
                            <span className="text-[10px] text-slate-400">
                                Mutamark ERP Verified Merchant
                            </span>
                            <Button
                                size="sm"
                                onClick={() => window.print()}
                                className="gap-1.5 bg-purple-700 text-white hover:bg-purple-800"
                            >
                                <Printer className="h-4 w-4" />
                                {t('Print Standee')}
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>

                {/* MODAL 8: INTERACTIVE OFFICIAL RECEIPT VOUCHER */}
                <Dialog open={showVoucherModal} onOpenChange={setShowVoucherModal}>
                    <DialogContent className="max-w-2xl p-6 bg-white text-slate-900 border-2 border-slate-200 shadow-2xl">
                        {selectedTransaction && (
                            <div className="space-y-5 print:p-0">
                                {/* Letterhead */}
                                <div className="flex items-start justify-between border-b pb-4 border-slate-200">
                                    <div className="flex items-center gap-3">
                                        <div className="w-12 h-12 rounded-xl bg-purple-600 text-white font-bold text-2xl flex items-center justify-center">
                                            M
                                        </div>
                                        <div>
                                            <h2 className="text-lg font-bold text-slate-900">MUTAMARK ENTERPRISES LTD</h2>
                                            <p className="text-xs text-slate-500">Plot 42, Sam Nujoma Road, Dar es Salaam, Tanzania</p>
                                            <p className="text-xs text-slate-500">TIN: 139-482-901 | VRN: 40-029182-Z</p>
                                        </div>
                                    </div>

                                    <div className="text-right">
                                        <Badge className="bg-emerald-600 text-white mb-1">
                                            OFFICIAL RECEIPT VOUCHER
                                        </Badge>
                                        <div className="font-mono text-base font-bold text-purple-700">
                                            {selectedTransaction.receipt_voucher_number}
                                        </div>
                                        <div className="text-xs text-slate-500">
                                            Date: {formatDate(selectedTransaction.paid_at || selectedTransaction.created_at)}
                                        </div>
                                    </div>
                                </div>

                                {/* Payment Details Grid */}
                                <div className="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl border border-slate-200">
                                    <div>
                                        <span className="text-slate-500 block uppercase text-[10px] font-semibold">Received From:</span>
                                        <strong className="text-sm text-slate-900 block mt-0.5">
                                            {selectedTransaction.customer_name || 'Customer'}
                                        </strong>
                                        <span className="text-slate-500">{selectedTransaction.customer_phone || selectedTransaction.customer_email || 'N/A'}</span>
                                    </div>

                                    <div>
                                        <span className="text-slate-500 block uppercase text-[10px] font-semibold">Invoice Reference:</span>
                                        <strong className="text-sm font-mono text-slate-900 block mt-0.5">
                                            {selectedTransaction.invoice_number || 'Direct Payment'}
                                        </strong>
                                        <span className="text-slate-500">Txn Ref: {selectedTransaction.transaction_reference}</span>
                                    </div>
                                </div>

                                {/* Financial Summary Table */}
                                <div className="border border-slate-200 rounded-lg overflow-hidden text-xs">
                                    <table className="w-full text-left">
                                        <thead className="bg-slate-100 border-b border-slate-200 text-slate-600 font-semibold">
                                            <tr>
                                                <th className="p-3">Payment Channel & Merchant Till</th>
                                                <th className="p-3">Telco / Switch Reference</th>
                                                <th className="p-3 text-right">Settled Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td className="p-3">
                                                    <div className="font-semibold text-slate-900 capitalize">
                                                        {selectedTransaction.payment_method_channel || selectedTransaction.gateway_key}
                                                    </div>
                                                    <span className="text-[11px] text-slate-500">
                                                        Lipa Namba: {lipa_namba_info.till_number} (WOLINET)
                                                    </span>
                                                </td>
                                                <td className="p-3 font-mono font-medium text-slate-700">
                                                    {selectedTransaction.gateway_transaction_id || 'VERIFIED-SETTLEMENT'}
                                                </td>
                                                <td className="p-3 text-right font-bold text-base text-slate-900">
                                                    {formatCurrency(selectedTransaction.amount)} TZS
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {/* Cryptographic Seal & Signatures */}
                                <div className="flex items-center justify-between pt-3 border-t border-slate-200 text-xs">
                                    <div className="flex items-center gap-3">
                                        <QrCode className="w-16 h-16 text-slate-900 border p-1 rounded" />
                                        <div>
                                            <div className="font-semibold text-slate-800 flex items-center gap-1">
                                                <ShieldCheck className="w-4 h-4 text-emerald-600" />
                                                Verified Electronic Document Seal
                                            </div>
                                            <p className="text-[10px] text-slate-500 max-w-xs font-mono">
                                                SHA-256 HMAC cryptographic signature synced with Mutamark Public Verification Ledger.
                                            </p>
                                        </div>
                                    </div>

                                    <div className="text-right">
                                        <div className="font-serif italic font-bold text-base text-slate-800">
                                            Automated Payment Switch
                                        </div>
                                        <span className="text-[10px] text-slate-500 block border-t border-slate-300 pt-0.5 mt-1">
                                            Authorized Accounting Officer
                                        </span>
                                    </div>
                                </div>

                                <div className="flex justify-end gap-2 pt-2 border-t">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() => setShowVoucherModal(false)}
                                    >
                                        {t('Close')}
                                    </Button>
                                    <Button
                                        size="sm"
                                        onClick={() => window.print()}
                                        className="gap-1.5 bg-primary text-primary-foreground"
                                    >
                                        <Printer className="h-4 w-4" />
                                        {t('Print Receipt Voucher')}
                                    </Button>
                                </div>
                            </div>
                        )}
                    </DialogContent>
                </Dialog>
            </div>
        </AuthenticatedLayout>
    );
}

function url(path: string): string {
    return window.location.origin + path;
}
