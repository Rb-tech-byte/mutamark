import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { 
    ShieldCheck, 
    ShieldAlert, 
    Copy, 
    Check, 
    Printer, 
    Landmark, 
    Smartphone, 
    Calendar, 
    Building2, 
    User, 
    FileText, 
    ExternalLink,
    Lock
} from 'lucide-react';
import { BrandProvider } from '@/contexts/brand-context';
import ApplicationLogo from '@/components/application-logo';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

interface Props {
    verification: DocumentVerificationData | null;
    hash: string;
    isVerified: boolean;
    appName?: string;
    companyName?: string;
}

function VerificationContent({ verification, hash, isVerified, appName = 'Mutamark ERP', companyName = 'Mutamark Enterprises' }: Props) {
    const { t } = useTranslation();
    const [copied, setCopied] = useState(false);

    const copyHash = () => {
        navigator.clipboard.writeText(hash);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const handlePrint = () => {
        window.print();
    };

    const lipaNamba = verification?.lipa_namba || '';

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-background text-foreground flex flex-col justify-between">
            {/* Top Navigation / Brand Header */}
            <header className="border-b bg-card/80 backdrop-blur sticky top-0 z-50">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                    <Link href="/" className="flex items-center gap-3">
                        <ApplicationLogo className="w-9 h-9 fill-current text-primary" />
                        <div>
                            <span className="font-bold text-lg tracking-tight block leading-none">{appName}</span>
                            <span className="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">
                                Wolinet Verification Engine
                            </span>
                        </div>
                    </Link>

                    <div className="flex items-center gap-2">
                        <Button variant="outline" size="sm" onClick={handlePrint} className="gap-2">
                            <Printer className="w-4 h-4" />
                            <span className="hidden sm:inline">{t('Print Certificate')}</span>
                        </Button>
                        <Button size="sm" asChild>
                            <Link href="/login">
                                <span>{t('ERP Portal')}</span>
                            </Link>
                        </Button>
                    </div>
                </div>
            </header>

            {/* Main Container */}
            <main className="max-w-4xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full space-y-6">
                {/* Status Hero */}
                {isVerified && verification ? (
                    <div className="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-emerald-50/30 dark:from-emerald-950/20 dark:via-background dark:to-emerald-950/10 p-6 sm:p-8 shadow-sm">
                        <div className="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                            <div className="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-600/20 shrink-0">
                                <ShieldCheck className="w-9 h-9" />
                            </div>
                            <div className="space-y-1.5 flex-1">
                                <div className="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                    <Badge className="bg-emerald-600 hover:bg-emerald-700 text-white font-bold uppercase tracking-wider text-xs px-3 py-0.5">
                                        {verification.signature_version === 2 ? 'Content integrity verified' : 'Registered legacy document'}
                                    </Badge>
                                    <Badge variant="outline" className="border-emerald-300 text-emerald-800 dark:text-emerald-300 text-xs">
                                        Mutamark Public Ledger
                                    </Badge>
                                </div>
                                <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                                    Official Document Verification
                                </h1>
                                <p className="text-sm text-muted-foreground max-w-2xl">
                                    This verification identifies the registered document. Current content integrity is checked for version-bound signatures. It was issued by{' '}
                                    <strong className="text-foreground">{verification.issuer_name || companyName}</strong> and registered by Mutamark ERP.
                                </p>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="rounded-2xl border border-rose-200 bg-gradient-to-br from-rose-50 via-white to-rose-50/30 dark:from-rose-950/20 dark:via-background dark:to-rose-950/10 p-6 sm:p-8 shadow-sm">
                        <div className="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                            <div className="w-16 h-16 rounded-2xl bg-rose-600 text-white flex items-center justify-center shadow-lg shadow-rose-600/20 shrink-0">
                                <ShieldAlert className="w-9 h-9" />
                            </div>
                            <div className="space-y-1.5 flex-1">
                                <Badge variant="destructive" className="font-bold uppercase tracking-wider text-xs px-3 py-0.5">
                                    Verification Failed
                                </Badge>
                                <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                                    Invalid or Unrecognized Document Hash
                                </h1>
                                <p className="text-sm text-muted-foreground max-w-2xl">
                                    No document record matches the hash provided. Please verify the QR code or hash parameter to ensure you are scanning an authentic document issued by Mutamark Enterprises.
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Document Details Cards (only if verified) */}
                {isVerified && verification && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {/* Primary Document Meta */}
                        <Card className="md:col-span-2 shadow-sm">
                            <CardHeader className="pb-3 border-b">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="text-lg font-bold flex items-center gap-2">
                                            <FileText className="w-5 h-5 text-primary" />
                                            <span>Document Specification</span>
                                        </CardTitle>
                                        <CardDescription>
                                            Official reference and party details
                                        </CardDescription>
                                    </div>
                                    <Badge variant="outline" className="capitalize text-xs font-semibold px-2.5 py-0.5">
                                        {verification.document_type.replace('_', ' ')}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="pt-4 space-y-4">
                                <div className="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span className="text-xs text-muted-foreground block font-medium">Document Number</span>
                                        <span className="font-mono font-bold text-base text-foreground">
                                            #{verification.document_number}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-xs text-muted-foreground block font-medium">Document Status</span>
                                        <Badge className="bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase font-semibold text-[11px] mt-0.5">
                                            {verification.status || 'Verified'}
                                        </Badge>
                                    </div>
                                    <div>
                                        <span className="text-xs text-muted-foreground block font-medium">Issue Date</span>
                                        <span className="font-medium text-foreground flex items-center gap-1.5 mt-0.5">
                                            <Calendar className="w-3.5 h-3.5 text-muted-foreground" />
                                            {verification.issue_date ? formatDate(verification.issue_date) : 'N/A'}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-xs text-muted-foreground block font-medium">Due Date</span>
                                        <span className="font-medium text-foreground flex items-center gap-1.5 mt-0.5">
                                            <Calendar className="w-3.5 h-3.5 text-muted-foreground" />
                                            {verification.due_date ? formatDate(verification.due_date) : 'N/A'}
                                        </span>
                                    </div>
                                </div>

                                <div className="border-t pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div className="bg-muted/40 p-3 rounded-lg border">
                                        <span className="text-xs text-muted-foreground font-semibold flex items-center gap-1 mb-1">
                                            <Building2 className="w-3.5 h-3.5" />
                                            <span>ISSUER</span>
                                        </span>
                                        <p className="font-bold text-foreground">{verification.issuer_name}</p>
                                        <p className="text-xs text-muted-foreground">TIN: {verification.tin_number || '100-200-300'}</p>
                                        <p className="text-xs text-muted-foreground">VRN: {verification.vrn_number || '40-001234-Z'}</p>
                                    </div>
                                    <div className="bg-muted/40 p-3 rounded-lg border">
                                        <span className="text-xs text-muted-foreground font-semibold flex items-center gap-1 mb-1">
                                            <User className="w-3.5 h-3.5" />
                                            <span>RECIPIENT / CLIENT</span>
                                        </span>
                                        <p className="font-bold text-foreground">{verification.party_name || 'Valued Customer'}</p>
                                        {verification.party_name && (
                                            <p className="text-xs text-muted-foreground">Registered Party</p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Financial Card */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-3 border-b">
                                <CardTitle className="text-lg font-bold">Financial Total</CardTitle>
                                <CardDescription>Verified transactional balance</CardDescription>
                            </CardHeader>
                            <CardContent className="pt-4 space-y-4">
                                <div className="bg-slate-900 text-white dark:bg-card p-4 rounded-xl border border-slate-800 text-center">
                                    <span className="text-xs text-slate-400 block font-medium">TOTAL VERIFIED AMOUNT</span>
                                    <div className="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1 text-emerald-400">
                                        {formatCurrency(verification.total_amount)}
                                    </div>
                                    <span className="text-[11px] text-slate-400 mt-1 block">
                                        Currency: {verification.currency || 'TZS'}
                                    </span>
                                </div>

                                {verification.tax_amount !== undefined && verification.tax_amount > 0 && (
                                    <div className="flex justify-between items-center text-xs px-2 py-1.5 rounded bg-muted/40 border">
                                        <span className="text-muted-foreground font-medium">VAT (18% Tanzanian Tax):</span>
                                        <span className="font-semibold text-foreground">{formatCurrency(verification.tax_amount)}</span>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Tanzanian Payment Instructions (SRS Appendix B) */}
                {isVerified && verification && (
                    <Card className="border-emerald-200 bg-emerald-50/40 dark:bg-card dark:border-emerald-950/50 shadow-sm">
                        <CardHeader className="pb-3 border-b border-emerald-100 dark:border-border">
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-base font-bold text-emerald-950 dark:text-emerald-400 flex items-center gap-2">
                                    <Smartphone className="w-4 h-4 text-emerald-600" />
                                    <span>Official Tanzanian Payment Instructions</span>
                                </CardTitle>
                                <Badge className="bg-emerald-600 text-white text-[11px]">Authorized Channels</Badge>
                            </div>
                        </CardHeader>
                        <CardContent className="pt-4">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {/* Lipa Namba */}
                                <div className="bg-white dark:bg-background p-4 rounded-xl border border-emerald-200 dark:border-border shadow-xs">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Smartphone className="w-5 h-5 text-emerald-600" />
                                        <h3 className="font-bold text-sm text-foreground">LIPA HAPA (TILL NUMBER)</h3>
                                    </div>
                                    <div className="bg-emerald-100/70 dark:bg-emerald-950/30 p-3 rounded-lg text-center border border-emerald-300 dark:border-emerald-800">
                                        <span className="font-mono text-2xl font-black tracking-widest text-emerald-900 dark:text-emerald-300">
                                            {lipaNamba}
                                        </span>
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-2 text-center">
                                        Vodacom M-Pesa â€¢ Tigo Pesa â€¢ Airtel Money â€¢ HaloPesa
                                    </p>
                                </div>

                                {/* Bank Account */}
                                <div className="bg-white dark:bg-background p-4 rounded-xl border border-blue-200 dark:border-border shadow-xs">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Landmark className="w-5 h-5 text-blue-600" />
                                        <h3 className="font-bold text-sm text-foreground">CRDB / NMB BANK TRANSFER</h3>
                                    </div>
                                    <div className="space-y-1.5 text-xs text-foreground bg-muted/30 p-2.5 rounded-lg border">
                                        <p><strong className="font-semibold text-muted-foreground">Account Name:</strong> MUTAMARK ENTERPRISES</p>
                                        <p><strong className="font-semibold text-muted-foreground">Account Number:</strong> 0150284758200</p>
                                        <p><strong className="font-semibold text-muted-foreground">Bank:</strong> CRDB Bank / NMB Bank</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Cryptographic Audit Trail */}
                <Card className="shadow-sm">
                    <CardHeader className="pb-3 border-b">
                        <CardTitle className="text-base font-bold flex items-center gap-2">
                            <Lock className="w-4 h-4 text-primary" />
                            <span>Cryptographic Audit Signature</span>
                        </CardTitle>
                        <CardDescription>
                            Deterministic SHA-256 validation record registered on Wolinet Security Infrastructure
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="pt-4 space-y-4">
                        <div className="bg-muted/50 p-3.5 rounded-xl border font-mono text-xs space-y-2">
                            <div className="flex items-center justify-between text-muted-foreground">
                                <span className="font-medium">SHA-256 Verification Hash:</span>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    onClick={copyHash}
                                    className="h-6 px-2 text-xs gap-1 font-sans"
                                >
                                    {copied ? <Check className="w-3 h-3 text-emerald-600" /> : <Copy className="w-3 h-3" />}
                                    <span>{copied ? 'Copied' : 'Copy'}</span>
                                </Button>
                            </div>
                            <p className="break-all font-bold text-foreground select-all bg-background p-2 rounded border">
                                {hash}
                            </p>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                            <div className="p-2.5 rounded-lg border bg-card">
                                <span className="text-muted-foreground block text-[11px]">Verification Timestamp</span>
                                <span className="font-semibold text-foreground">
                                    {verification?.verified_at ? formatDate(verification.verified_at) : 'Active Ledger'}
                                </span>
                            </div>
                            <div className="p-2.5 rounded-lg border bg-card">
                                <span className="text-muted-foreground block text-[11px]">Security Engine</span>
                                <span className="font-semibold text-foreground">
                                    Wolinet Cryptographic Core v2.0
                                </span>
                            </div>
                            <div className="p-2.5 rounded-lg border bg-card">
                                <span className="text-muted-foreground block text-[11px]">Authorized Signatory</span>
                                <span className="font-semibold text-foreground">
                                    {verification?.signatory_name || 'Mutamark Directorate'}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </main>

            {/* Footer */}
            <footer className="border-t bg-card py-6 text-center text-xs text-muted-foreground">
                <div className="max-w-4xl mx-auto px-4 space-y-1">
                    <p className="font-medium text-foreground">
                        {appName} â€¢ Official Enterprise Document Verification Engine
                    </p>
                    <p>
                        Powered by Wolinet Technology Ltd. All Rights Reserved.
                    </p>
                </div>
            </footer>
        </div>
    );
}

export default function Document(props: Props) {
    return (
        <BrandProvider>
            <Head title={`Verify Document - ${props.verification?.document_number || props.hash}`} />
            <VerificationContent {...props} />
        </BrandProvider>
    );
}
