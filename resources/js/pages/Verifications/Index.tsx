import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { 
    ShieldCheck, 
    FileText, 
    Search, 
    Copy, 
    Check, 
    ExternalLink, 
    Eye, 
    QrCode, 
    Smartphone, 
    Coins, 
    Lock, 
    FileCheck2,
    Calendar,
    ArrowUpRight
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    verifications: PaginatedData<DocumentVerificationData>;
    metrics: {
        total_verified: number;
        total_volume: number;
        invoice_count: number;
        quotation_count: number;
        purchase_count: number;
        lipa_namba: string;
    };
    filters: {
        search: string;
        type: string;
    };
}

export default function Index({ verifications, metrics, filters }: Props) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedType, setSelectedType] = useState(filters.type || 'all');
    const [copiedHash, setCopiedHash] = useState<string | null>(null);
    const [instantHashInput, setInstantHashInput] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('document-verifications.index'), {
            search: searchTerm,
            type: selectedType
        }, { preserveState: true });
    };

    const handleTypeChange = (type: string) => {
        setSelectedType(type);
        router.get(route('document-verifications.index'), {
            search: searchTerm,
            type: type
        }, { preserveState: true });
    };

    const copyToClipboard = (text: string, id: string) => {
        navigator.clipboard.writeText(text);
        setCopiedHash(id);
        setTimeout(() => setCopiedHash(null), 2000);
    };

    const handleInstantVerify = (e: React.FormEvent) => {
        e.preventDefault();
        if (!instantHashInput.trim()) return;
        window.open(`/verify/document/${instantHashInput.trim()}`, '_blank');
    };

    const getTypeBadge = (type: string) => {
        switch (type.toLowerCase()) {
            case 'invoice':
                return <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200">{t('Tax Invoice')}</Badge>;
            case 'quotation':
                return <Badge variant="outline" className="bg-purple-50 text-purple-700 border-purple-200">{t('Quotation / Proposal')}</Badge>;
            case 'purchase_order':
                return <Badge variant="outline" className="bg-amber-50 text-amber-700 border-amber-200">{t('Purchase Order')}</Badge>;
            default:
                return <Badge variant="outline" className="capitalize">{type.replace('_', ' ')}</Badge>;
        }
    };

    const getDocumentViewRoute = (item: DocumentVerificationData) => {
        if (!item.id && !item.document_number) return null;
        switch (item.document_type.toLowerCase()) {
            case 'invoice':
                return route('sales-invoices.index');
            case 'quotation':
                return route('sales-proposals.index');
            case 'purchase_order':
                return route('purchase-invoices.index');
            default:
                return null;
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Dashboard'), url: route('dashboard') },
                { label: t('Verification Ledger') }
            ]}
            pageTitle={t('Document Verification Ledger')}
        >
            <Head title={t('Cryptographic Verification Ledger')} />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="rounded-2xl border bg-gradient-to-r from-slate-900 via-slate-800 to-slate-950 text-white p-6 shadow-sm">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div className="space-y-2">
                            <div className="inline-flex items-center gap-2 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1 rounded-full text-xs font-semibold">
                                <ShieldCheck className="w-4 h-4 text-emerald-400" />
                                <span>Mutamark Cryptographic Ledger • Wolinet Core v2.0</span>
                            </div>
                            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                {t('Document Verification & Audit Ledger')}
                            </h1>
                            <p className="text-slate-300 text-sm max-w-2xl">
                                {t('Track, inspect, and verify the cryptographic integrity of all official invoices, proposals, and purchase orders with Tanzania Lipa Namba and SHA-256 digital seals.')}
                            </p>
                        </div>

                        {/* Instant Hash Verifier */}
                        <form onSubmit={handleInstantVerify} className="bg-slate-800/80 p-3 rounded-xl border border-slate-700 shrink-0 w-full md:w-80 space-y-2">
                            <label className="text-xs font-medium text-slate-300 flex items-center gap-1.5">
                                <Lock className="w-3.5 h-3.5 text-emerald-400" />
                                <span>{t('Instant Hash Verification')}</span>
                            </label>
                            <div className="flex gap-2">
                                <Input
                                    placeholder={t('Enter SHA-256 Hash...')}
                                    value={instantHashInput}
                                    onChange={(e) => setInstantHashInput(e.target.value)}
                                    className="h-8 text-xs bg-slate-900/90 border-slate-700 text-white placeholder:text-slate-500 focus-visible:ring-emerald-500"
                                />
                                <Button type="submit" size="sm" className="h-8 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs gap-1 font-semibold">
                                    <span>Verify</span>
                                    <ArrowUpRight className="w-3.5 h-3.5" />
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>

                {/* Metrics Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="shadow-xs">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">{t('Total Verified Documents')}</CardTitle>
                            <FileCheck2 className="w-4 h-4 text-emerald-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{metrics.total_verified}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {metrics.invoice_count} {t('Invoices')} • {metrics.quotation_count} {t('Proposals')} • {metrics.purchase_count} {t('Purchases')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="shadow-xs">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">{t('Verified Transaction Volume')}</CardTitle>
                            <Coins className="w-4 h-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(metrics.total_volume)}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Cryptographically Sealed in TZS')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="shadow-xs">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">{t('Authorized Lipa Namba')}</CardTitle>
                            <Smartphone className="w-4 h-4 text-emerald-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <span className="font-mono text-2xl font-black text-emerald-700 dark:text-emerald-400">
                                    {metrics.lipa_namba || '—'}
                                </span>
                                {metrics.lipa_namba && (
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        onClick={() => copyToClipboard(metrics.lipa_namba, 'till')}
                                        className="h-7 w-7 p-0"
                                    >
                                        {copiedHash === 'till' ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5" />}
                                    </Button>
                                )}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('M-Pesa, Tigo Pesa, Airtel Money')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="shadow-xs">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">{t('Authenticity Integrity')}</CardTitle>
                            <ShieldCheck className="w-4 h-4 text-emerald-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">100% Valid</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Deterministic SHA-256 Signatures')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter and Table Card */}
                <Card className="shadow-sm">
                    <CardHeader className="pb-4">
                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <CardTitle className="text-lg font-bold">{t('Verified Document Ledger')}</CardTitle>
                                <CardDescription>{t('All official Mutamark ERP registered documents with public validation links')}</CardDescription>
                            </div>

                            {/* Type Filter Buttons */}
                            <div className="flex flex-wrap items-center gap-1.5 bg-muted/60 p-1 rounded-lg border text-xs">
                                {[
                                    { key: 'all', label: t('All') },
                                    { key: 'invoice', label: t('Invoices') },
                                    { key: 'quotation', label: t('Proposals') },
                                    { key: 'purchase_order', label: t('Purchase Orders') },
                                ].map((tab) => (
                                    <button
                                        key={tab.key}
                                        onClick={() => handleTypeChange(tab.key)}
                                        className={`px-3 py-1.5 rounded-md font-medium transition-colors ${
                                            selectedType === tab.key
                                                ? 'bg-background text-foreground shadow-xs'
                                                : 'text-muted-foreground hover:text-foreground'
                                        }`}
                                    >
                                        {tab.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Search Bar */}
                        <form onSubmit={handleSearch} className="flex gap-2 pt-3">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input
                                    placeholder={t('Search by document #, customer/vendor, or verification hash...')}
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pl-9 text-sm"
                                />
                            </div>
                            <Button type="submit" variant="secondary" className="gap-1.5">
                                <Search className="w-4 h-4" />
                                <span>{t('Search')}</span>
                            </Button>
                        </form>
                    </CardHeader>

                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/30">
                                        <TableHead className="w-[180px] font-semibold">{t('Document #')}</TableHead>
                                        <TableHead className="font-semibold">{t('Type')}</TableHead>
                                        <TableHead className="font-semibold">{t('Counterparty / Client')}</TableHead>
                                        <TableHead className="text-right font-semibold">{t('Total Amount')}</TableHead>
                                        <TableHead className="font-semibold">{t('Issue Date')}</TableHead>
                                        <TableHead className="font-semibold">{t('Cryptographic Hash')}</TableHead>
                                        <TableHead className="font-semibold">{t('Status')}</TableHead>
                                        <TableHead className="text-right font-semibold pr-6">{t('Actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {verifications.data.length > 0 ? (
                                        verifications.data.map((item) => (
                                            <TableRow key={item.id || item.verification_hash} className="hover:bg-muted/40 transition-colors">
                                                <TableCell className="font-mono font-bold text-sm">
                                                    #{item.document_number}
                                                </TableCell>
                                                <TableCell>
                                                    {getTypeBadge(item.document_type)}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="font-medium text-sm text-foreground">
                                                        {item.party_name || t('General Client')}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {item.issuer_name}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <span className="font-bold text-sm">
                                                        {formatCurrency(item.total_amount)}
                                                    </span>
                                                    {item.tax_amount ? (
                                                        <span className="block text-[11px] text-muted-foreground">
                                                            VAT: {formatCurrency(item.tax_amount)}
                                                        </span>
                                                    ) : null}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    <div className="flex items-center gap-1.5 text-muted-foreground">
                                                        <Calendar className="w-3.5 h-3.5" />
                                                        <span>{item.issue_date ? formatDate(item.issue_date) : 'N/A'}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center gap-1.5">
                                                        <span className="font-mono text-xs text-muted-foreground bg-muted/60 px-2 py-0.5 rounded border">
                                                            {item.verification_hash.substring(0, 10)}...{item.verification_hash.substring(item.verification_hash.length - 6)}
                                                        </span>
                                                        <Button
                                                            size="sm"
                                                            variant="ghost"
                                                            onClick={() => copyToClipboard(item.verification_hash, item.verification_hash)}
                                                            className="h-6 w-6 p-0"
                                                        >
                                                            {copiedHash === item.verification_hash ? (
                                                                <Check className="w-3 h-3 text-emerald-600" />
                                                            ) : (
                                                                <Copy className="w-3 h-3 text-muted-foreground" />
                                                            )}
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge className="bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 font-semibold text-xs gap-1">
                                                        <ShieldCheck className="w-3 h-3 text-emerald-600" />
                                                        <span>{t('VERIFIED')}</span>
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right pr-6">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        {/* Public Verification Link */}
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="h-8 px-2.5 text-xs text-emerald-700 border-emerald-200 hover:bg-emerald-50 gap-1 font-medium"
                                                            asChild
                                                        >
                                                            <a
                                                                href={`/verify/document/${item.verification_hash}`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                title={t('Open Public Verification Certificate')}
                                                            >
                                                                <QrCode className="w-3.5 h-3.5" />
                                                                <span>{t('Verify')}</span>
                                                                <ExternalLink className="w-3 h-3 opacity-60" />
                                                            </a>
                                                        </Button>

                                                        {/* View in Module */}
                                                        {getDocumentViewRoute(item) && (
                                                            <Button
                                                                size="sm"
                                                                variant="ghost"
                                                                className="h-8 px-2 text-xs"
                                                                asChild
                                                            >
                                                                <Link href={getDocumentViewRoute(item)!} title={t('Open in Module')}>
                                                                    <Eye className="w-3.5 h-3.5" />
                                                                </Link>
                                                            </Button>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={8} className="text-center py-12 text-muted-foreground">
                                                <ShieldCheck className="w-10 h-10 mx-auto text-muted-foreground/40 mb-3" />
                                                <p className="font-semibold text-base text-foreground">{t('No Verified Documents Found')}</p>
                                                <p className="text-sm mt-1">{t('Documents will appear here automatically when created or viewed.')}</p>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {verifications.links && verifications.links.length > 3 && (
                            <div className="flex items-center justify-between px-6 py-4 border-t">
                                <div className="text-xs text-muted-foreground">
                                    {t('Showing')} {verifications.data.length} {t('of')} {verifications.total} {t('records')}
                                </div>
                                <div className="flex items-center gap-1">
                                    {verifications.links.map((link, idx) => (
                                        <Button
                                            key={idx}
                                            size="sm"
                                            variant={link.active ? 'default' : 'outline'}
                                            disabled={!link.url}
                                            className="h-8 min-w-8 px-2 text-xs"
                                            asChild={!!link.url}
                                        >
                                            {link.url ? (
                                                <Link href={link.url}>
                                                    <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                                </Link>
                                            ) : (
                                                <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                            )}
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
