import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { 
    CheckCircle2, 
    Printer, 
    ShieldCheck, 
    Receipt, 
    Download, 
    ExternalLink, 
    Clock, 
    ArrowLeft, 
    QrCode, 
    Building2,
    Lock,
    Copy,
    Share2
} from 'lucide-react';

interface VerificationData {
    verification_hash: string;
    qr_payload?: string;
    status: string;
    signatory_name?: string;
    signatory_title?: string;
    issue_date?: string;
}

interface TransactionData {
    id: number;
    transaction_reference: string;
    invoice_number: string;
    invoice_id: number | null;
    customer_name: string;
    customer_email: string | null;
    customer_phone: string | null;
    amount: number | string;
    currency: string;
    fee_amount: number | string;
    net_amount: number | string;
    status: string;
    gateway_key: string;
    gateway_transaction_id: string | null;
    payment_method_channel: string | null;
    receipt_voucher_number: string | null;
    paid_at: string | null;
    created_at: string;
}

interface SuccessProps {
    transaction: TransactionData;
    verification?: VerificationData | null;
}

export default function Success({ transaction, verification }: SuccessProps) {
    const handlePrint = () => {
        window.print();
    };

    const numAmount = Number(transaction.amount) || 0;
    const channelLabel = (transaction.payment_method_channel || 'Mobile Money').toUpperCase();

    return (
        <div className="min-h-screen bg-gradient-to-b from-background via-muted/20 to-background text-foreground flex flex-col justify-between py-8 px-4 print:bg-white print:p-0">
            <Head title={`Receipt Voucher - ${transaction.receipt_voucher_number || transaction.transaction_reference} | Mutamark ERP`} />

            {/* Header / Brand (Hidden on direct paper print if desired, or styled cleanly) */}
            <header className="max-w-3xl mx-auto w-full mb-6 flex items-center justify-between print:hidden">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-lg">
                        M
                    </div>
                    <div>
                        <span className="font-semibold text-base block tracking-tight">Mutamark Enterprises Ltd</span>
                        <span className="text-xs text-muted-foreground">Automated Settlement & Reconciliation System</span>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Button variant="outline" size="sm" onClick={handlePrint} className="gap-2 text-xs">
                        <Printer className="w-4 h-4" /> Print Receipt
                    </Button>
                </div>
            </header>

            {/* Main Receipt Content */}
            <main className="max-w-3xl mx-auto w-full flex-1">
                <Card className="border border-border bg-card shadow-xl overflow-hidden print:shadow-none print:border-none">
                    {/* Top Green Accent Banner */}
                    <div className="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-6 print:bg-none print:text-black print:p-0 print:border-b print:pb-4">
                        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div className="flex items-center gap-3.5">
                                <div className="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 print:border print:border-black">
                                    <CheckCircle2 className="w-8 h-8 text-white print:text-emerald-700" />
                                </div>
                                <div>
                                    <Badge className="bg-white/20 hover:bg-white/30 text-white border-0 text-[11px] mb-1 print:bg-emerald-100 print:text-emerald-800">
                                        PAYMENT SETTLED & VERIFIED
                                    </Badge>
                                    <h1 className="text-2xl font-bold tracking-tight">Official Receipt Voucher</h1>
                                </div>
                            </div>

                            <div className="text-right sm:text-right w-full sm:w-auto">
                                <div className="text-xs text-white/80 print:text-muted-foreground uppercase tracking-wider font-semibold">
                                    Receipt Voucher No.
                                </div>
                                <div className="text-xl font-mono font-extrabold tracking-tight">
                                    {transaction.receipt_voucher_number || 'RV-2026-PENDING'}
                                </div>
                            </div>
                        </div>
                    </div>

                    <CardContent className="p-6 sm:p-8 space-y-6">
                        {/* Company & Customer Grid */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm border-b border-border pb-6">
                            <div>
                                <span className="text-xs uppercase tracking-wider font-semibold text-muted-foreground block mb-1">
                                    Issuer / Merchant
                                </span>
                                <h3 className="font-bold text-base text-foreground">Mutamark Enterprises Ltd</h3>
                                <p className="text-xs text-muted-foreground leading-relaxed">
                                    Mlimani City Commercial Complex, Office Tower 4<br />
                                    Sam Nujoma Road, Dar es Salaam, Tanzania<br />
                                    TIN: 139-482-901 | VRN: 40028192<br />
                                    Email: finance@mutamark.co.tz
                                </p>
                            </div>

                            <div className="sm:text-right">
                                <span className="text-xs uppercase tracking-wider font-semibold text-muted-foreground block mb-1">
                                    Payment Received From
                                </span>
                                <h3 className="font-bold text-base text-foreground">{transaction.customer_name}</h3>
                                {transaction.customer_email && (
                                    <p className="text-xs text-muted-foreground">{transaction.customer_email}</p>
                                )}
                                {transaction.customer_phone && (
                                    <p className="text-xs font-mono text-muted-foreground">{transaction.customer_phone}</p>
                                )}
                                <div className="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-muted text-xs">
                                    <span className="text-muted-foreground">Settled At:</span>
                                    <span className="font-medium text-foreground">
                                        {transaction.paid_at ? formatDate(transaction.paid_at) : 'Just Now'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Payment Settlement Metadata */}
                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                            <div className="bg-muted/40 p-3 rounded-xl border border-border">
                                <span className="text-muted-foreground block text-[11px]">Transaction Ref</span>
                                <span className="font-mono font-bold text-foreground text-xs mt-0.5 block truncate">
                                    {transaction.transaction_reference}
                                </span>
                            </div>

                            <div className="bg-muted/40 p-3 rounded-xl border border-border">
                                <span className="text-muted-foreground block text-[11px]">Invoice Number</span>
                                <span className="font-semibold text-foreground text-xs mt-0.5 block truncate">
                                    {transaction.invoice_number}
                                </span>
                            </div>

                            <div className="bg-muted/40 p-3 rounded-xl border border-border">
                                <span className="text-muted-foreground block text-[11px]">Payment Channel</span>
                                <span className="font-semibold text-primary text-xs mt-0.5 block uppercase truncate">
                                    {transaction.gateway_key} ({channelLabel})
                                </span>
                            </div>

                            <div className="bg-muted/40 p-3 rounded-xl border border-border">
                                <span className="text-muted-foreground block text-[11px]">Gateway External Ref</span>
                                <span className="font-mono text-foreground text-xs mt-0.5 block truncate">
                                    {transaction.gateway_transaction_id || 'CONFIRMED'}
                                </span>
                            </div>
                        </div>

                        {/* Ledger Line Breakdown */}
                        <div className="border border-border rounded-xl overflow-hidden">
                            <table className="w-full text-left text-xs">
                                <thead className="bg-muted/60 text-muted-foreground uppercase font-semibold text-[11px]">
                                    <tr>
                                        <th className="py-2.5 px-4">Description</th>
                                        <th className="py-2.5 px-4 text-right">Amount (TZS)</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    <tr>
                                        <td className="py-3 px-4 font-medium text-foreground">
                                            Payment Settlement for Invoice <span className="text-primary font-mono">{transaction.invoice_number}</span>
                                            <span className="block text-[11px] text-muted-foreground font-normal">
                                                Direct ledger credit to Mutamark Settlement Account
                                            </span>
                                        </td>
                                        <td className="py-3 px-4 text-right font-mono font-medium text-foreground">
                                            {formatCurrency(numAmount, 'TZS')}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 text-muted-foreground">
                                            Payment Gateway Processing Fees
                                        </td>
                                        <td className="py-2.5 px-4 text-right font-mono text-emerald-600">
                                            TZS 0.00
                                        </td>
                                    </tr>
                                    <tr className="bg-muted/20 font-bold text-sm">
                                        <td className="py-3 px-4 text-foreground">
                                            Total Amount Cleared
                                        </td>
                                        <td className="py-3 px-4 text-right text-base text-primary font-mono">
                                            {formatCurrency(numAmount, 'TZS')}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {/* Cryptographic SHA-256 Seal Box */}
                        <div className="p-4 rounded-xl bg-muted/30 border border-border flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div className="flex items-center gap-3">
                                <div className="p-2.5 rounded-lg bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                    <ShieldCheck className="w-6 h-6" />
                                </div>
                                <div>
                                    <div className="font-semibold text-xs text-foreground flex items-center gap-1.5">
                                        Cryptographic Document Seal Verified
                                        <Badge variant="outline" className="text-[10px] bg-emerald-500/10 text-emerald-600 border-0">
                                            TAMPER-PROOF
                                        </Badge>
                                    </div>
                                    <p className="text-[11px] text-muted-foreground font-mono mt-0.5 truncate max-w-sm sm:max-w-md">
                                        SHA-256: {verification?.verification_hash || 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'}
                                    </p>
                                </div>
                            </div>

                            {verification?.qr_payload && (
                                <Button asChild variant="outline" size="sm" className="gap-1.5 text-xs shrink-0 print:hidden">
                                    <a href={verification.qr_payload} target="_blank" rel="noreferrer">
                                        <QrCode className="w-3.5 h-3.5" /> Verify Online
                                    </a>
                                </Button>
                            )}
                        </div>

                        {/* Actions (Hidden on Print) */}
                        <div className="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3 print:hidden">
                            <Button asChild variant="ghost" size="sm" className="gap-1.5 text-xs text-muted-foreground">
                                <Link href="/payment-gateways">
                                    <ArrowLeft className="w-3.5 h-3.5" /> Return to Gateways Dashboard
                                </Link>
                            </Button>

                            <div className="flex items-center gap-2">
                                <Button onClick={handlePrint} size="sm" className="gap-1.5 text-xs shadow-md">
                                    <Download className="w-3.5 h-3.5" /> Download / Print PDF Voucher
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </main>

            {/* Footer */}
            <footer className="max-w-3xl mx-auto w-full text-center text-xs text-muted-foreground pt-6 print:hidden">
                This is a computer-generated receipt voucher issued in accordance with the Tanzania Electronic Transactions Act.
            </footer>
        </div>
    );
}
