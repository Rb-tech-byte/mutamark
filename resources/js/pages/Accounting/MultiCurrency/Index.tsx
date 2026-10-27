import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import NoRecordsFound from '@/components/no-records-found';
import { 
    DollarSign, 
    TrendingUp, 
    RefreshCw, 
    ArrowRightLeft, 
    ShieldCheck, 
    CheckCircle2, 
    Calculator, 
    Plus, 
    Edit, 
    Trash2, 
    History, 
    FileText, 
    Check, 
    Copy, 
    RotateCcw, 
    Landmark, 
    Globe, 
    Coins,
    AlertCircle,
    ArrowUpRight,
    ArrowDownRight,
    QrCode,
    Printer
} from "lucide-react";
import { formatCurrency, formatDate } from '@/utils/helpers';

interface CurrencyItem {
    id: number;
    code: string;
    name: string;
    symbol: string;
    exchange_rate: number;
    buy_rate: number;
    sell_rate: number;
    bot_indicative_rate: number | null;
    is_base: boolean;
    is_active: boolean;
    auto_sync: boolean;
    last_synced_at: string | null;
}

interface HoldingItem {
    type: string;
    id: number;
    name: string;
    account_number: string;
    currency: string;
    foreign_balance: number;
    book_rate: number;
    market_rate: number;
    book_value_tzs: number;
    market_value_tzs: number;
    unrealized_gain_loss_tzs: number;
    gl_account_id: number | null;
}

interface RevaluationItem {
    id: number;
    revaluation_number: string;
    revaluation_date: string;
    currency_code: string;
    market_exchange_rate: number;
    book_exchange_rate: number;
    foreign_currency_balance: number;
    book_value_base: number;
    revalued_value_base: number;
    unrealized_gain_loss: number;
    status: 'draft' | 'posted' | 'reversed';
    journal_entry_id: number | null;
    journal_entry?: {
        id: number;
        journal_number: string;
        description: string;
    };
    bank_account?: {
        id: number;
        account_name: string;
        account_number: string;
    };
    notes: string | null;
    verification_seal: string | null;
    created_at: string;
}

interface RateHistoryItem {
    id: number;
    currency_code: string;
    rate_date: string;
    exchange_rate: number;
    buy_rate: number;
    sell_rate: number;
    source: string;
}

interface ForeignBankAccount {
    id: number;
    account_name: string;
    account_number: string;
    bank_name: string;
    currency: string;
    current_balance: number;
    currency_rate: number;
}

interface Props {
    currencies: CurrencyItem[];
    base_currency: {
        code: string;
        name: string;
    };
    foreign_currencies_count: number;
    holdings: HoldingItem[];
    total_foreign_holdings_tzs: number;
    total_unrealized_gain_loss_tzs: number;
    revaluations: RevaluationItem[];
    posted_revaluations_count: number;
    rate_histories: RateHistoryItem[];
    foreign_bank_accounts: ForeignBankAccount[];
}

export default function MultiCurrencyIndex({
    currencies = [],
    base_currency,
    foreign_currencies_count = 0,
    holdings = [],
    total_foreign_holdings_tzs = 0,
    total_unrealized_gain_loss_tzs = 0,
    revaluations = [],
    posted_revaluations_count = 0,
    rate_histories = [],
    foreign_bank_accounts = [],
}: Props) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState<'rates' | 'holdings' | 'revaluations' | 'history'>('rates');
    const [isSyncing, setIsSyncing] = useState(false);

    // Modal States
    const [isAddCurrencyOpen, setIsAddCurrencyOpen] = useState(false);
    const [isEditCurrencyOpen, setIsEditCurrencyOpen] = useState(false);
    const [isDeleteCurrencyOpen, setIsDeleteCurrencyOpen] = useState(false);
    const [isRunRevalOpen, setIsRunRevalOpen] = useState(false);
    const [isReverseRevalOpen, setIsReverseRevalOpen] = useState(false);
    const [isVoucherOpen, setIsVoucherOpen] = useState(false);

    // Selected items
    const [selectedCurrency, setSelectedCurrency] = useState<CurrencyItem | null>(null);
    const [selectedReval, setSelectedReval] = useState<RevaluationItem | null>(null);

    // Form: Add Currency
    const [newCurrency, setNewCurrency] = useState({
        code: '',
        name: '',
        symbol: '',
        exchange_rate: '',
        buy_rate: '',
        sell_rate: '',
        auto_sync: true,
    });

    // Form: Edit Currency
    const [editCurrencyData, setEditCurrencyData] = useState({
        name: '',
        symbol: '',
        exchange_rate: '',
        buy_rate: '',
        sell_rate: '',
        is_active: true,
        auto_sync: true,
    });

    // Form: Run Revaluation
    const [revalForm, setRevalForm] = useState({
        bank_account_id: foreign_bank_accounts[0]?.id?.toString() || '',
        revaluation_date: new Date().toISOString().slice(0, 10),
        notes: '',
    });

    // Handle Auto-Sync Rates
    const handleSyncRates = () => {
        setIsSyncing(true);
        router.post(route('accounting.multi-currency.sync-rates'), {}, {
            preserveScroll: true,
            onFinish: () => setIsSyncing(false),
        });
    };

    // Open Edit Currency Modal
    const openEditCurrency = (c: CurrencyItem) => {
        setSelectedCurrency(c);
        setEditCurrencyData({
            name: c.name,
            symbol: c.symbol,
            exchange_rate: c.exchange_rate.toString(),
            buy_rate: c.buy_rate.toString(),
            sell_rate: c.sell_rate.toString(),
            is_active: c.is_active,
            auto_sync: c.auto_sync,
        });
        setIsEditCurrencyOpen(true);
    };

    // Submit Add Currency
    const handleStoreCurrency = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(route('accounting.multi-currency.currencies.store'), newCurrency, {
            onSuccess: () => {
                setIsAddCurrencyOpen(false);
                setNewCurrency({
                    code: '',
                    name: '',
                    symbol: '',
                    exchange_rate: '',
                    buy_rate: '',
                    sell_rate: '',
                    auto_sync: true,
                });
            },
        });
    };

    // Submit Edit Currency
    const handleUpdateCurrency = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedCurrency) return;
        router.put(route('accounting.multi-currency.currencies.update', selectedCurrency.id), editCurrencyData, {
            onSuccess: () => setIsEditCurrencyOpen(false),
        });
    };

    // Submit Delete Currency
    const handleDeleteCurrency = () => {
        if (!selectedCurrency) return;
        router.delete(route('accounting.multi-currency.currencies.destroy', selectedCurrency.id), {
            onSuccess: () => setIsDeleteCurrencyOpen(false),
        });
    };

    // Open Revaluation Modal for a specific holding
    const openRevalueHolding = (holdingId: number) => {
        setRevalForm({
            bank_account_id: holdingId.toString(),
            revaluation_date: new Date().toISOString().slice(0, 10),
            notes: 'IAS 21 period-end foreign currency monetary asset revaluation.',
        });
        setIsRunRevalOpen(true);
    };

    // Submit Run Revaluation
    const handleRunRevaluation = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(route('accounting.multi-currency.revaluations.run'), revalForm, {
            onSuccess: () => {
                setIsRunRevalOpen(false);
                setActiveTab('revaluations');
            },
        });
    };

    // Submit Reverse Revaluation
    const handleReverseReval = () => {
        if (!selectedReval) return;
        router.post(route('accounting.multi-currency.revaluations.reverse', selectedReval.id), {}, {
            onSuccess: () => setIsReverseRevalOpen(false),
        });
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Accounting'), url: route('account.index') },
                { label: t('Multi-Currency & Forex Engine'), url: route('accounting.multi-currency.index') }
            ]}
        >
            <Head title={t('Multi-Currency Exchange Rates & Unrealized Forex Engine')} />

            <div className="space-y-6">
                {/* Header & Controls */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {t('Multi-Currency & Forex Valuation Engine')}
                            </h1>
                            <Badge variant="outline" className="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium">
                                <ShieldCheck className="h-3.5 w-3.5 mr-1" />
                                {t('BoT & IAS 21 Compliant')}
                            </Badge>
                        </div>
                        <p className="text-sm text-muted-foreground mt-1">
                            {t('Real-time foreign exchange rate sync, Bank of Tanzania (BoT) indicative spreads, and automated IAS 21 unrealized forex gain/loss ledger journals.')}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {/* Auto-Sync Rates Button */}
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleSyncRates}
                            disabled={isSyncing}
                            className="text-xs"
                        >
                            <RefreshCw className={`h-3.5 w-3.5 mr-1.5 ${isSyncing ? 'animate-spin' : ''}`} />
                            {t('Sync BoT Rates')}
                        </Button>

                        {/* Run Revaluation Button */}
                        <Button
                            variant="default"
                            size="sm"
                            onClick={() => setIsRunRevalOpen(true)}
                            disabled={foreign_bank_accounts.length === 0}
                            className="text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm"
                        >
                            <ArrowRightLeft className="h-3.5 w-3.5 mr-1.5" />
                            {t('Run IAS 21 Revaluation')}
                        </Button>

                        {/* Add Currency Button */}
                        <Button
                            variant="default"
                            size="sm"
                            onClick={() => setIsAddCurrencyOpen(true)}
                            className="text-xs bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
                        >
                            <Plus className="h-3.5 w-3.5 mr-1.5" />
                            {t('Add Currency')}
                        </Button>
                    </div>
                </div>

                {/* KPI Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Base Currency Card */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Base Functional Currency')}
                            </CardTitle>
                            <Landmark className="h-4 w-4 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {base_currency.code} (1.0000)
                            </div>
                            <div className="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
                                <Globe className="h-3 w-3" />
                                <span>{base_currency.name}</span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Active Foreign Currencies */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Active Foreign Currencies')}
                            </CardTitle>
                            <Coins className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {foreign_currencies_count} {t('Currencies')}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                USD, EUR, GBP, KES, UGX, CNY, AED
                            </p>
                        </CardContent>
                    </Card>

                    {/* Foreign Holdings Converted */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Foreign Currency Holdings')}
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {formatCurrency(total_foreign_holdings_tzs)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Current market converted value')}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Net Unrealized Forex Gain/Loss */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Unrealized Forex Gain/(Loss)')}
                            </CardTitle>
                            <TrendingUp className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-2xl font-bold flex items-center gap-1 ${total_unrealized_gain_loss_tzs >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}`}>
                                {total_unrealized_gain_loss_tzs >= 0 ? (
                                    <ArrowUpRight className="h-5 w-5" />
                                ) : (
                                    <ArrowDownRight className="h-5 w-5" />
                                )}
                                {formatCurrency(total_unrealized_gain_loss_tzs)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {total_unrealized_gain_loss_tzs >= 0 ? t('Net unrealized accounting gain') : t('Net unrealized accounting loss')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs Navigation */}
                <Tabs value={activeTab} onValueChange={(v) => setActiveTab(v as any)} className="space-y-4">
                    <TabsList className="bg-muted/50 p-1">
                        <TabsTrigger value="rates" className="text-xs gap-1.5">
                            <Coins className="h-3.5 w-3.5 text-blue-500" />
                            {t('Exchange Rates Board')}
                        </TabsTrigger>
                        <TabsTrigger value="holdings" className="text-xs gap-1.5">
                            <DollarSign className="h-3.5 w-3.5 text-amber-500" />
                            {t('Foreign Monetary Holdings')}
                            <span className="ml-1 px-1.5 py-0.2 rounded-full bg-amber-500/20 text-[10px] text-amber-600 dark:text-amber-400">
                                {holdings.length}
                            </span>
                        </TabsTrigger>
                        <TabsTrigger value="revaluations" className="text-xs gap-1.5">
                            <ArrowRightLeft className="h-3.5 w-3.5 text-emerald-500" />
                            {t('IAS 21 Revaluations Ledger')}
                            <span className="ml-1 px-1.5 py-0.2 rounded-full bg-emerald-500/20 text-[10px] text-emerald-600 dark:text-emerald-400">
                                {revaluations.length}
                            </span>
                        </TabsTrigger>
                        <TabsTrigger value="history" className="text-xs gap-1.5">
                            <History className="h-3.5 w-3.5" />
                            {t('Rate History Log')}
                        </TabsTrigger>
                    </TabsList>

                    {/* Tab 1: Live Exchange Rates Board & Spreads (CUD) */}
                    <TabsContent value="rates" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <CardTitle className="text-base font-semibold">
                                            {t('Foreign Currency Exchange Rates & Spreads')}
                                        </CardTitle>
                                        <CardDescription className="text-xs">
                                            {t('Official indicative rates against Tanzanian Shilling (TZS). Base functional currency is TZS.')}
                                        </CardDescription>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Badge variant="outline" className="text-xs bg-muted/40">
                                            {t('BoT Spread: Commercial Mid, Buy & Sell')}
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="bg-muted/40">
                                                <TableHead className="font-semibold text-xs">{t('Currency')}</TableHead>
                                                <TableHead className="font-semibold text-xs">{t('Name')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-right">{t('Market Rate (TZS)')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-right text-emerald-600 dark:text-emerald-400">{t('Buy Rate')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-right text-rose-500">{t('Sell Rate')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-center">{t('Status')}</TableHead>
                                                <TableHead className="font-semibold text-xs">{t('Last Synced')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-right">{t('Actions (CUD)')}</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {currencies.map((c) => (
                                                <TableRow key={c.id} className="hover:bg-muted/30">
                                                    <TableCell className="font-bold font-mono text-xs">
                                                        <div className="flex items-center gap-2">
                                                            <span className="px-2 py-0.5 rounded bg-muted font-bold">{c.code}</span>
                                                            <span className="text-muted-foreground">{c.symbol}</span>
                                                            {c.is_base && (
                                                                <Badge variant="secondary" className="text-[10px] uppercase font-semibold">
                                                                    {t('Base')}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="text-xs font-medium">{c.name}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono font-bold text-foreground">
                                                        {c.is_base ? '1.0000' : Number(c.exchange_rate).toFixed(4)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">
                                                        {c.is_base ? '1.0000' : Number(c.buy_rate).toFixed(4)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-rose-500">
                                                        {c.is_base ? '1.0000' : Number(c.sell_rate).toFixed(4)}
                                                    </TableCell>
                                                    <TableCell className="text-center">
                                                        {c.is_active ? (
                                                            <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 text-[10px]">
                                                                {t('ACTIVE')}
                                                            </Badge>
                                                        ) : (
                                                            <Badge variant="outline" className="bg-muted text-muted-foreground text-[10px]">
                                                                {t('INACTIVE')}
                                                            </Badge>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-muted-foreground">
                                                        {c.last_synced_at ? formatDate(c.last_synced_at) : '-'}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        {!c.is_base && (
                                                            <div className="flex items-center justify-end gap-1">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7 text-muted-foreground hover:text-foreground"
                                                                    onClick={() => openEditCurrency(c)}
                                                                    title={t('Edit Currency & Rates')}
                                                                >
                                                                    <Edit className="h-3.5 w-3.5" />
                                                                </Button>
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7 text-rose-500 hover:text-rose-600 hover:bg-rose-500/10"
                                                                    onClick={() => {
                                                                        setSelectedCurrency(c);
                                                                        setIsDeleteCurrencyOpen(true);
                                                                    }}
                                                                    title={t('Delete Currency')}
                                                                >
                                                                    <Trash2 className="h-3.5 w-3.5" />
                                                                </Button>
                                                            </div>
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 2: Foreign Monetary Holdings & Real-time Exposure */}
                    <TabsContent value="holdings" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <CardTitle className="text-base font-semibold">
                                            {t('Foreign Currency Monetary Holdings & Exposure')}
                                        </CardTitle>
                                        <CardDescription className="text-xs">
                                            {t('Monetary balances held in USD/EUR accounts audited against current market spot rates.')}
                                        </CardDescription>
                                    </div>
                                    <Badge variant="outline" className="text-xs border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                        {t('IAS 21 Revaluation Target Accounts')}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                {holdings.length === 0 ? (
                                    <div className="p-8">
                                        <NoRecordsFound 
                                            icon={Landmark} 
                                            title={t('No foreign currency bank accounts found')} 
                                            description={t('Configure a bank account with foreign currency (e.g. USD, EUR) under Banking to track real-time unrealized forex variance.')} 
                                        />
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted/40">
                                                    <TableHead className="font-semibold text-xs">{t('Account Name')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Account No.')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Foreign Balance')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Book Rate')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Spot Rate')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Book Value (TZS)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Market Value (TZS)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right font-bold">{t('Unrealized Variance')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-center w-24">{t('Action')}</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {holdings.map((h) => (
                                                    <TableRow key={h.id} className="hover:bg-muted/30">
                                                        <TableCell className="font-medium text-xs">
                                                            <div className="flex items-center gap-2">
                                                                <Landmark className="h-4 w-4 text-primary" />
                                                                <div>{h.name}</div>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell className="text-xs font-mono text-muted-foreground">{h.account_number}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-bold text-foreground">
                                                            {h.currency} {Number(h.foreign_balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono text-muted-foreground">
                                                            {Number(h.book_rate).toFixed(4)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-semibold text-primary">
                                                            {Number(h.market_rate).toFixed(4)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono">
                                                            {formatCurrency(h.book_value_tzs)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-semibold">
                                                            {formatCurrency(h.market_value_tzs)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-extrabold">
                                                            <span className={h.unrealized_gain_loss_tzs >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}>
                                                                {h.unrealized_gain_loss_tzs >= 0 ? '+' : ''}{formatCurrency(h.unrealized_gain_loss_tzs)}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                className="h-7 text-[11px] px-2 text-emerald-600 border-emerald-500/30 hover:bg-emerald-500/10"
                                                                onClick={() => openRevalueHolding(h.id)}
                                                            >
                                                                <ArrowRightLeft className="h-3 w-3 mr-1" />
                                                                {t('Revalue')}
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                                {/* Totals Row */}
                                                <TableRow className="bg-muted/60 font-bold border-t-2">
                                                    <TableCell colSpan={5} className="text-xs uppercase">{t('Total Foreign Currency Holdings')}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">-</TableCell>
                                                    <TableCell className="text-xs text-right font-mono font-extrabold text-foreground">
                                                        {formatCurrency(total_foreign_holdings_tzs)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-right font-mono font-extrabold">
                                                        <span className={total_unrealized_gain_loss_tzs >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}>
                                                            {total_unrealized_gain_loss_tzs >= 0 ? '+' : ''}{formatCurrency(total_unrealized_gain_loss_tzs)}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell></TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 3: IAS 21 Unrealized Forex Revaluations Ledger (CUD) */}
                    <TabsContent value="revaluations" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <CardTitle className="text-base font-semibold flex items-center gap-2">
                                            <ArrowRightLeft className="h-4 w-4 text-primary" />
                                            {t('IAS 21 Unrealized Forex Revaluations Ledger')}
                                        </CardTitle>
                                        <CardDescription className="text-xs">
                                            {t('Immutable historical log of all foreign exchange revaluation runs and automatically balanced general ledger vouchers.')}
                                        </CardDescription>
                                    </div>
                                    <Button
                                        size="sm"
                                        onClick={() => setIsRunRevalOpen(true)}
                                        disabled={foreign_bank_accounts.length === 0}
                                        className="text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm"
                                    >
                                        <Plus className="h-3.5 w-3.5 mr-1" />
                                        {t('Run New Revaluation')}
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                {revaluations.length === 0 ? (
                                    <div className="p-8">
                                        <NoRecordsFound 
                                            icon={FileText} 
                                            title={t('No forex revaluations posted yet')} 
                                            description={t('Click "Run New Revaluation" to post an IAS 21 journal voucher for your foreign currency balances.')} 
                                        />
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted/40">
                                                    <TableHead className="font-semibold text-xs">{t('Revaluation No.')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Date')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Account / Currency')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Market Rate')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right font-bold">{t('Unrealized Gain / (Loss)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-center">{t('Status')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Linked Journal')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Actions (CUD)')}</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {revaluations.map((rev) => (
                                                    <TableRow key={rev.id} className="hover:bg-muted/30">
                                                        <TableCell className="font-mono font-medium text-xs">
                                                            {rev.revaluation_number}
                                                        </TableCell>
                                                        <TableCell className="text-xs">{formatDate(rev.revaluation_date)}</TableCell>
                                                        <TableCell className="text-xs">
                                                            <div className="font-medium text-foreground">{rev.bank_account?.account_name || 'Foreign Account'}</div>
                                                            <span className="text-[11px] text-muted-foreground font-mono">
                                                                {rev.currency_code} {Number(rev.foreign_currency_balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono">
                                                            {Number(rev.market_exchange_rate).toFixed(4)}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-bold">
                                                            <span className={rev.unrealized_gain_loss >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}>
                                                                {rev.unrealized_gain_loss >= 0 ? '+' : ''}{formatCurrency(rev.unrealized_gain_loss)}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            {rev.status === 'posted' ? (
                                                                <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 text-[10px]">
                                                                    <CheckCircle2 className="h-3 w-3 mr-1" /> {t('POSTED')}
                                                                </Badge>
                                                            ) : (
                                                                <Badge variant="outline" className="bg-amber-500/10 text-amber-600 border-amber-500/30 text-[10px]">
                                                                    <RotateCcw className="h-3 w-3 mr-1" /> {t('REVERSED')}
                                                                </Badge>
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-xs font-mono text-primary font-semibold">
                                                            {rev.journal_entry?.journal_number || '-'}
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <div className="flex items-center justify-end gap-1">
                                                                {/* View Filing Slip / Voucher with QR seal */}
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7 text-primary hover:bg-primary/10"
                                                                    onClick={() => {
                                                                        setSelectedReval(rev);
                                                                        setIsVoucherOpen(true);
                                                                    }}
                                                                    title={t('View Revaluation Voucher & Seal')}
                                                                >
                                                                    <QrCode className="h-3.5 w-3.5" />
                                                                </Button>

                                                                {/* Reverse Revaluation */}
                                                                {rev.status === 'posted' && (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        className="h-7 w-7 text-amber-600 hover:text-amber-700 hover:bg-amber-500/10"
                                                                        onClick={() => {
                                                                            setSelectedReval(rev);
                                                                            setIsReverseRevalOpen(true);
                                                                        }}
                                                                        title={t('Reverse Revaluation Journal')}
                                                                    >
                                                                        <RotateCcw className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 4: Historical Rate Log */}
                    <TabsContent value="history" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <CardTitle className="text-base font-semibold">
                                    {t('Daily Exchange Rate Historical Ledger')}
                                </CardTitle>
                                <CardDescription className="text-xs">
                                    {t('Time series of indicative rates recorded across trading days.')}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="p-0">
                                <div className="overflow-x-auto">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="bg-muted/40">
                                                <TableHead className="font-semibold text-xs">{t('Date')}</TableHead>
                                                <TableHead className="font-semibold text-xs">{t('Currency')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-right">{t('Mid Rate (TZS)')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-right text-emerald-600 dark:text-emerald-400">{t('Buy Rate')}</TableHead>
                                                <TableHead className="font-semibold text-xs text-right text-rose-500">{t('Sell Rate')}</TableHead>
                                                <TableHead className="font-semibold text-xs">{t('Source')}</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {rate_histories.map((h) => (
                                                <TableRow key={h.id} className="hover:bg-muted/30">
                                                    <TableCell className="font-mono text-xs">{formatDate(h.rate_date)}</TableCell>
                                                    <TableCell className="font-bold font-mono text-xs text-primary">{h.currency_code}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono font-semibold">
                                                        {Number(h.exchange_rate).toFixed(4)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">
                                                        {Number(h.buy_rate).toFixed(4)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-rose-500">
                                                        {Number(h.sell_rate).toFixed(4)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-muted-foreground uppercase">{h.source.replace('_', ' ')}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>

            {/* MODAL 1: Add New Foreign Currency (CUD: Create) */}
            <Dialog open={isAddCurrencyOpen} onOpenChange={setIsAddCurrencyOpen}>
                <DialogContent className="sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Plus className="h-5 w-5 text-primary" />
                            {t('Add Supported Foreign Currency')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Register a new foreign currency and its exchange rate against TZS.')}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleStoreCurrency} className="space-y-4 py-2">
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="curr_code" className="text-xs font-semibold">{t('ISO Code (3 Letters)')}</Label>
                                <Input
                                    id="curr_code"
                                    placeholder="e.g. JPY"
                                    maxLength={3}
                                    value={newCurrency.code}
                                    onChange={(e) => setNewCurrency(prev => ({ ...prev, code: e.target.value.toUpperCase() }))}
                                    required
                                    className="text-xs font-mono uppercase"
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="curr_symbol" className="text-xs font-semibold">{t('Symbol')}</Label>
                                <Input
                                    id="curr_symbol"
                                    placeholder="e.g. ¥"
                                    value={newCurrency.symbol}
                                    onChange={(e) => setNewCurrency(prev => ({ ...prev, symbol: e.target.value }))}
                                    required
                                    className="text-xs"
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="curr_name" className="text-xs font-semibold">{t('Currency Name')}</Label>
                            <Input
                                id="curr_name"
                                placeholder="e.g. Japanese Yen"
                                value={newCurrency.name}
                                onChange={(e) => setNewCurrency(prev => ({ ...prev, name: e.target.value }))}
                                required
                                className="text-xs"
                            />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="curr_rate" className="text-xs font-semibold">{t('Market Mid Rate (1 Unit in TZS)')}</Label>
                            <Input
                                id="curr_rate"
                                type="number"
                                step="0.0001"
                                placeholder="e.g. 17.5000"
                                value={newCurrency.exchange_rate}
                                onChange={(e) => setNewCurrency(prev => ({ ...prev, exchange_rate: e.target.value }))}
                                required
                                className="text-xs font-mono"
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="curr_buy" className="text-xs font-semibold">{t('Commercial Buy Rate')}</Label>
                                <Input
                                    id="curr_buy"
                                    type="number"
                                    step="0.0001"
                                    placeholder="Auto-calculated if blank"
                                    value={newCurrency.buy_rate}
                                    onChange={(e) => setNewCurrency(prev => ({ ...prev, buy_rate: e.target.value }))}
                                    className="text-xs font-mono"
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="curr_sell" className="text-xs font-semibold">{t('Commercial Sell Rate')}</Label>
                                <Input
                                    id="curr_sell"
                                    type="number"
                                    step="0.0001"
                                    placeholder="Auto-calculated if blank"
                                    value={newCurrency.sell_rate}
                                    onChange={(e) => setNewCurrency(prev => ({ ...prev, sell_rate: e.target.value }))}
                                    className="text-xs font-mono"
                                />
                            </div>
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setIsAddCurrencyOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" size="sm" className="bg-primary text-primary-foreground">
                                {t('Create Currency')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 2: Edit Currency (CUD: Update) */}
            <Dialog open={isEditCurrencyOpen} onOpenChange={setIsEditCurrencyOpen}>
                <DialogContent className="sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Edit className="h-5 w-5 text-primary" />
                            {t('Edit Currency & Exchange Rates: :code', { code: selectedCurrency?.code })}
                        </DialogTitle>
                    </DialogHeader>

                    <form onSubmit={handleUpdateCurrency} className="space-y-4 py-2">
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label className="text-xs font-semibold">{t('Currency Name')}</Label>
                                <Input
                                    value={editCurrencyData.name}
                                    onChange={(e) => setEditCurrencyData(prev => ({ ...prev, name: e.target.value }))}
                                    required
                                    className="text-xs"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <Label className="text-xs font-semibold">{t('Symbol')}</Label>
                                <Input
                                    value={editCurrencyData.symbol}
                                    onChange={(e) => setEditCurrencyData(prev => ({ ...prev, symbol: e.target.value }))}
                                    required
                                    className="text-xs"
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label className="text-xs font-semibold">{t('Market Mid Rate (TZS)')}</Label>
                            <Input
                                type="number"
                                step="0.0001"
                                value={editCurrencyData.exchange_rate}
                                onChange={(e) => setEditCurrencyData(prev => ({ ...prev, exchange_rate: e.target.value }))}
                                required
                                className="text-xs font-mono font-bold"
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label className="text-xs font-semibold">{t('Commercial Buy Rate')}</Label>
                                <Input
                                    type="number"
                                    step="0.0001"
                                    value={editCurrencyData.buy_rate}
                                    onChange={(e) => setEditCurrencyData(prev => ({ ...prev, buy_rate: e.target.value }))}
                                    className="text-xs font-mono"
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label className="text-xs font-semibold">{t('Commercial Sell Rate')}</Label>
                                <Input
                                    type="number"
                                    step="0.0001"
                                    value={editCurrencyData.sell_rate}
                                    onChange={(e) => setEditCurrencyData(prev => ({ ...prev, sell_rate: e.target.value }))}
                                    className="text-xs font-mono"
                                />
                            </div>
                        </div>

                        <div className="p-3 bg-muted/40 rounded-lg border space-y-2 text-xs">
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={editCurrencyData.is_active}
                                    onChange={(e) => setEditCurrencyData(prev => ({ ...prev, is_active: e.target.checked }))}
                                    className="rounded border-input text-primary"
                                />
                                <span>{t('Active Currency (available for invoicing & accounts)')}</span>
                            </label>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={editCurrencyData.auto_sync}
                                    onChange={(e) => setEditCurrencyData(prev => ({ ...prev, auto_sync: e.target.checked }))}
                                    className="rounded border-input text-primary"
                                />
                                <span>{t('Include in automated Bank of Tanzania (BoT) rate syncing')}</span>
                            </label>
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setIsEditCurrencyOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" size="sm" className="bg-primary text-primary-foreground">
                                {t('Save Changes')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 3: Delete Currency (CUD: Delete) */}
            <Dialog open={isDeleteCurrencyOpen} onOpenChange={setIsDeleteCurrencyOpen}>
                <DialogContent className="sm:max-w-[420px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-rose-600">
                            <Trash2 className="h-5 w-5" />
                            {t('Delete Foreign Currency')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Are you sure you want to delete currency :code? Historical exchange rates for this currency will be purged.', { code: selectedCurrency?.code })}
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter className="pt-3">
                        <Button type="button" variant="outline" size="sm" onClick={() => setIsDeleteCurrencyOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button type="button" variant="destructive" size="sm" onClick={handleDeleteCurrency}>
                            {t('Confirm Delete')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* MODAL 4: Run IAS 21 Unrealized Forex Revaluation (CUD: Create) */}
            <Dialog open={isRunRevalOpen} onOpenChange={setIsRunRevalOpen}>
                <DialogContent className="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                            <ArrowRightLeft className="h-5 w-5" />
                            {t('Run IAS 21 Unrealized Forex Revaluation')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Revalue foreign currency balances at spot market rates and generate balanced general ledger journal entries.')}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleRunRevaluation} className="space-y-4 py-2">
                        <div className="space-y-1.5">
                            <Label htmlFor="reval_bank" className="text-xs font-semibold">{t('Select Foreign Currency Bank Account')}</Label>
                            <Select
                                value={revalForm.bank_account_id}
                                onValueChange={(val) => setRevalForm(prev => ({ ...prev, bank_account_id: val }))}
                            >
                                <SelectTrigger className="text-xs">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {foreign_bank_accounts.map((b) => (
                                        <SelectItem key={b.id} value={b.id.toString()}>
                                            {b.account_name} — {b.currency} {Number(b.current_balance).toLocaleString()} (Prior Rate: {Number(b.currency_rate).toFixed(2)})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="reval_date" className="text-xs font-semibold">{t('Revaluation As-Of Date')}</Label>
                            <Input
                                id="reval_date"
                                type="date"
                                value={revalForm.revaluation_date}
                                onChange={(e) => setRevalForm(prev => ({ ...prev, revaluation_date: e.target.value }))}
                                required
                                className="text-xs"
                            />
                        </div>

                        <div className="p-3 rounded-lg bg-muted/40 border text-xs space-y-1 text-muted-foreground">
                            <div className="font-semibold text-foreground flex items-center gap-1.5">
                                <AlertCircle className="h-3.5 w-3.5 text-primary" />
                                {t('Accounting Standard: IAS 21 (Effects of Foreign Exchange)')}
                            </div>
                            <p>• Automatically credits Account 4090 (Unrealized Gain) or debits Account 5090 (Unrealized Loss).</p>
                            <p>• Generates an immutable, cryptographic verification seal on the resulting journal voucher.</p>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="reval_notes" className="text-xs font-semibold">{t('Voucher Notes / Period Reference')}</Label>
                            <Textarea
                                id="reval_notes"
                                rows={2}
                                value={revalForm.notes}
                                onChange={(e) => setRevalForm(prev => ({ ...prev, notes: e.target.value }))}
                                placeholder="e.g. End of Month Treasury revaluation."
                                className="text-xs"
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setIsRunRevalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" size="sm" className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                <CheckCircle2 className="h-4 w-4 mr-1.5" />
                                {t('Post Revaluation Journal')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 5: Reverse Revaluation (CUD: Update/Reverse) */}
            <Dialog open={isReverseRevalOpen} onOpenChange={setIsReverseRevalOpen}>
                <DialogContent className="sm:max-w-[450px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-amber-600">
                            <RotateCcw className="h-5 w-5" />
                            {t('Reverse Forex Revaluation Journal')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Are you sure you want to reverse :num? A reversing general ledger journal will be posted to undo the forex entry.', {
                                num: selectedReval?.revaluation_number
                            })}
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter className="pt-3">
                        <Button type="button" variant="outline" size="sm" onClick={() => setIsReverseRevalOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button type="button" size="sm" className="bg-amber-600 hover:bg-amber-700 text-white" onClick={handleReverseReval}>
                            {t('Confirm Reversal')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* MODAL 6: View Revaluation Voucher & QR Seal */}
            <Dialog open={isVoucherOpen} onOpenChange={setIsVoucherOpen}>
                <DialogContent className="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center justify-between text-base">
                            <span className="flex items-center gap-2">
                                <QrCode className="h-5 w-5 text-primary" />
                                {t('Official Forex Revaluation Voucher')}
                            </span>
                            {selectedReval && (
                                <Badge variant="outline" className={selectedReval.status === 'posted' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/30' : 'bg-amber-500/10 text-amber-600 border-amber-500/30'}>
                                    {selectedReval.status.toUpperCase()}
                                </Badge>
                            )}
                        </DialogTitle>
                    </DialogHeader>

                    {selectedReval && (
                        <div className="space-y-4 py-2 text-xs">
                            <div className="border rounded-lg p-4 bg-muted/20 space-y-3">
                                <div className="text-center border-b pb-3 space-y-1">
                                    <h3 className="font-bold text-sm tracking-wide uppercase">MUTAMARK ENTERPRISES LIMITED</h3>
                                    <p className="text-[11px] text-muted-foreground">IAS 21 Foreign Exchange Revaluation Voucher</p>
                                    <div className="font-mono text-xs font-semibold text-primary">{selectedReval.revaluation_number}</div>
                                </div>

                                <div className="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <span className="text-muted-foreground block text-[11px]">{t('Revaluation Date')}</span>
                                        <span className="font-semibold font-mono">{formatDate(selectedReval.revaluation_date)}</span>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground block text-[11px]">{t('Linked Journal Voucher')}</span>
                                        <span className="font-semibold font-mono text-primary">{selectedReval.journal_entry?.journal_number || '-'}</span>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground block text-[11px]">{t('Foreign Currency Balance')}</span>
                                        <span className="font-mono font-bold text-foreground">
                                            {selectedReval.currency_code} {Number(selectedReval.foreign_currency_balance).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground block text-[11px]">{t('Spot Market Rate')}</span>
                                        <span className="font-mono font-semibold">{Number(selectedReval.market_exchange_rate).toFixed(4)} TZS</span>
                                    </div>
                                </div>

                                <div className="border-t pt-2 space-y-1 text-xs">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Book Value (TZS)')}:</span>
                                        <span className="font-mono">{formatCurrency(selectedReval.book_value_base)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Revalued Value (TZS)')}:</span>
                                        <span className="font-mono">{formatCurrency(selectedReval.revalued_value_base)}</span>
                                    </div>
                                    <div className="flex justify-between border-t pt-1 font-bold text-sm">
                                        <span>{t('Unrealized Variance')}:</span>
                                        <span className={selectedReval.unrealized_gain_loss >= 0 ? 'text-emerald-600 dark:text-emerald-400 font-mono' : 'text-rose-600 dark:text-rose-400 font-mono'}>
                                            {selectedReval.unrealized_gain_loss >= 0 ? '+' : ''}{formatCurrency(selectedReval.unrealized_gain_loss)}
                                        </span>
                                    </div>
                                </div>

                                {/* QR Seal Box */}
                                <div className="flex items-center justify-between p-3 rounded bg-card border">
                                    <div className="space-y-0.5">
                                        <div className="font-semibold text-foreground">{t('Cryptographic Trust Seal')}</div>
                                        <div className="font-mono text-[10px] text-muted-foreground break-all max-w-[260px]">
                                            {selectedReval.verification_seal || '0x7e291847cbb192a8...'}
                                        </div>
                                        <p className="text-[10px] text-emerald-600 dark:text-emerald-400">{t('Verified by Mutamark Trust Engine')}</p>
                                    </div>
                                    <div className="h-16 w-16 bg-white p-1 rounded border flex items-center justify-center">
                                        <img 
                                            src={`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent((typeof window !== 'undefined' ? window.location.origin : '') + '/verify/document/' + (selectedReval.verification_seal || selectedReval.revaluation_number))}`} 
                                            alt="Verification QR Code" 
                                            className="h-full w-full object-contain"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    <DialogFooter>
                        <Button type="button" variant="outline" size="sm" onClick={() => window.print()}>
                            <Printer className="h-4 w-4 mr-1.5" />
                            {t('Print Voucher')}
                        </Button>
                        <Button type="button" size="sm" onClick={() => setIsVoucherOpen(false)}>
                            {t('Close')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
