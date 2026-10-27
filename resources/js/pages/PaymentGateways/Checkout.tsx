import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { 
    ShieldCheck, 
    CreditCard, 
    Smartphone, 
    Building2, 
    CheckCircle2, 
    AlertCircle, 
    Lock, 
    ArrowRight, 
    Receipt, 
    Clock, 
    PhoneCall, 
    FileText, 
    Sparkles,
    Send
} from 'lucide-react';

interface GatewayOption {
    gateway_key: string;
    name: string;
    description: string;
    mode: string;
    instructions: string | null;
    is_default: boolean;
    credentials?: Record<string, any> | null;
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
    checkout_token: string;
    receipt_voucher_number: string | null;
    created_at: string;
}

interface CheckoutProps {
    transaction: TransactionData;
    gateways: GatewayOption[];
}

export default function Checkout({ transaction, gateways = [] }: CheckoutProps) {
    useFlashMessages();
    const isAlreadyPaid = transaction.status === 'completed';

    // Pick initial default gateway
    const defaultGw = gateways.find(g => g.is_default) || gateways[0] || {
        gateway_key: 'selcom',
        name: 'Selcom Paytech',
        description: 'Tanzania Mobile Money & Cards',
        mode: 'sandbox',
        instructions: null,
        is_default: true,
    };

    const [selectedGatewayKey, setSelectedGatewayKey] = useState<string>(defaultGw?.gateway_key || 'selcom');
    const [selectedChannel, setSelectedChannel] = useState<string>('mpesa');

    const form = useForm({
        gateway_key: selectedGatewayKey,
        payment_channel: selectedChannel,
        phone_number: transaction.customer_phone || '',
        reference_number: '',
    });

    const activeGateway = gateways.find(g => g.gateway_key === selectedGatewayKey) || defaultGw;

    const handleSelectGateway = (gw: GatewayOption) => {
        setSelectedGatewayKey(gw.gateway_key);
        form.setData('gateway_key', gw.gateway_key);

        // Set sensible default channel based on gateway
        if (gw.gateway_key === 'selcom' || gw.gateway_key === 'manual_mobile_money') {
            setSelectedChannel('mpesa');
            form.setData('payment_channel', 'mpesa');
        } else if (gw.gateway_key === 'bank_transfer') {
            setSelectedChannel('bank_transfer');
            form.setData('payment_channel', 'bank_transfer');
        } else {
            setSelectedChannel('card');
            form.setData('payment_channel', 'card');
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(`/pay/checkout/${transaction.checkout_token}`);
    };

    const numAmount = Number(transaction.amount) || 0;
    const numFee = Number(transaction.fee_amount) || 0;
    const totalPayable = numAmount;

    return (
        <div className="min-h-screen bg-gradient-to-b from-background via-muted/30 to-background flex flex-col justify-between text-foreground">
            <Head title={`Secure Checkout - ${transaction.transaction_reference} | Mutamark ERP`} />
            {transaction.status === 'pending_verification' && <div role="status" className="p-4 text-center bg-muted text-foreground">Your payment reference is awaiting verification. Your invoice balance will update after confirmation.</div>}

            {/* Top Navigation / Trust Banner */}
            <header className="border-b border-border bg-card/80 backdrop-blur-md sticky top-0 z-30 px-4 py-3">
                <div className="max-w-5xl mx-auto flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-bold text-lg shadow-sm">
                            M
                        </div>
                        <div>
                            <span className="font-semibold text-base tracking-tight block">Mutamark Secure Pay</span>
                            <span className="text-xs text-muted-foreground flex items-center gap-1">
                                <ShieldCheck className="w-3.5 h-3.5 text-emerald-500" /> 256-Bit TLS Bank Grade Encryption
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Badge variant="outline" className="text-xs bg-muted/50 border-border text-muted-foreground px-2.5 py-1">
                            TZS Settlement
                        </Badge>
                        <Badge variant="outline" className="text-xs bg-emerald-500/10 text-emerald-600 border-emerald-500/20 px-2.5 py-1 flex items-center gap-1">
                            <Lock className="w-3 h-3" /> PCI-DSS Certified
                        </Badge>
                    </div>
                </div>
            </header>

            {/* Main Checkout Body */}
            <main className="max-w-5xl mx-auto w-full px-4 py-8 flex-1">
                {isAlreadyPaid ? (
                    <Card className="max-w-xl mx-auto border-emerald-500/30 bg-emerald-500/5 shadow-xl text-center py-8">
                        <CardContent className="space-y-5">
                            <div className="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
                                <CheckCircle2 className="w-10 h-10" />
                            </div>
                            <div>
                                <h2 className="text-2xl font-bold text-foreground">Payment Already Settled</h2>
                                <p className="text-sm text-muted-foreground mt-1">
                                    Transaction <span className="font-mono font-medium text-foreground">{transaction.transaction_reference}</span> has been processed and reconciled.
                                </p>
                            </div>

                            <div className="bg-card border border-border rounded-xl p-4 text-left max-w-md mx-auto text-sm space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Receipt Voucher:</span>
                                    <span className="font-mono font-bold text-primary">{transaction.receipt_voucher_number || 'Generated'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Invoice Reference:</span>
                                    <span className="font-medium text-foreground">{transaction.invoice_number}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Settled Amount:</span>
                                    <span className="font-bold text-foreground">{formatCurrency(numAmount, 'TZS')}</span>
                                </div>
                            </div>

                            <div>
                                <Button asChild size="lg" className="gap-2">
                                    <Link href={`/pay/checkout/${transaction.checkout_token}/success`}>
                                        <Receipt className="w-4 h-4" /> View Official Receipt Voucher
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        {/* Left Column: Payment Channel & Form (7 cols) */}
                        <div className="lg:col-span-7 space-y-6">
                            {/* Step Title */}
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight text-foreground">Select Payment Method</h1>
                                <p className="text-sm text-muted-foreground mt-1">
                                    Choose your preferred gateway to complete the payment for <strong className="text-foreground">{transaction.invoice_number}</strong>.
                                </p>
                            </div>

                            {/* Gateway Provider Cards */}
                            <div className="space-y-3">
                                <label className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                    Available Payment Gateways
                                </label>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    {gateways.map((gw) => {
                                        const isSelected = selectedGatewayKey === gw.gateway_key;
                                        return (
                                            <button
                                                key={gw.gateway_key}
                                                type="button"
                                                onClick={() => handleSelectGateway(gw)}
                                                className={`text-left p-4 rounded-xl border transition-all relative flex flex-col justify-between ${
                                                    isSelected
                                                        ? 'border-primary bg-primary/5 shadow-md ring-1 ring-primary'
                                                        : 'border-border bg-card hover:border-primary/40 hover:bg-muted/40'
                                                }`}
                                            >
                                                <div className="flex items-start justify-between gap-2">
                                                    <div className="flex items-center gap-2.5">
                                                        <div className={`p-2 rounded-lg ${
                                                            isSelected ? 'bg-primary text-primary-foreground' : 'bg-muted text-foreground'
                                                        }`}>
                                                            {gw.gateway_key === 'selcom' && <Smartphone className="w-5 h-5" />}
                                                            {gw.gateway_key === 'pesapal' && <CreditCard className="w-5 h-5" />}
                                                            {gw.gateway_key === 'manual_mobile_money' && <Smartphone className="w-5 h-5" />}
                                                            {gw.gateway_key === 'bank_transfer' && <Building2 className="w-5 h-5" />}
                                                            {gw.gateway_key === 'stripe' && <CreditCard className="w-5 h-5" />}
                                                            {gw.gateway_key === 'paypal' && <Sparkles className="w-5 h-5" />}
                                                        </div>
                                                        <div>
                                                            <div className="font-semibold text-sm text-foreground">{gw.name}</div>
                                                            <div className="text-xs text-muted-foreground line-clamp-1">{gw.description}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div className="mt-3 flex items-center justify-between text-xs">
                                                    <Badge variant="outline" className="text-[10px] px-1.5 py-0 border-border">
                                                        {gw.mode === 'live' ? 'Live Gateway' : 'Instant Sandbox'}
                                                    </Badge>
                                                    {isSelected && (
                                                        <span className="text-primary font-medium flex items-center gap-1 text-[11px]">
                                                            <CheckCircle2 className="w-3.5 h-3.5" /> Selected
                                                        </span>
                                                    )}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Active Gateway Form & Instructions */}
                            <Card className="border-border bg-card shadow-sm">
                                <CardHeader className="pb-3">
                                    <CardTitle className="text-base flex items-center justify-between">
                                        <span>Payment Details</span>
                                        <Badge variant="secondary" className="font-normal text-xs">
                                            {activeGateway.name}
                                        </Badge>
                                    </CardTitle>
                                    <CardDescription className="text-xs">
                                        {activeGateway.instructions || 'Provide payment credentials to settle this transaction in TZS.'}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <form onSubmit={handleSubmit} className="space-y-4">
                                        {/* Mobile Money Channels for Selcom & Manual Till */}
                                        {(selectedGatewayKey === 'selcom' || selectedGatewayKey === 'manual_mobile_money') && (
                                            <div className="space-y-3">
                                                <label className="text-xs font-medium text-muted-foreground block">
                                                    Mobile Network Operator
                                                </label>
                                                <div className="grid grid-cols-3 gap-2">
                                                    {[
                                                        { id: 'mpesa', name: 'M-Pesa', color: 'border-red-500/40 hover:bg-red-500/5' },
                                                        { id: 'tigopesa', name: 'Tigo Pesa', color: 'border-blue-500/40 hover:bg-blue-500/5' },
                                                        { id: 'airtelmoney', name: 'Airtel Money', color: 'border-red-600/40 hover:bg-red-600/5' },
                                                    ].map((channel) => (
                                                        <button
                                                            key={channel.id}
                                                            type="button"
                                                            onClick={() => {
                                                                setSelectedChannel(channel.id);
                                                                form.setData('payment_channel', channel.id);
                                                            }}
                                                            className={`p-2.5 rounded-lg border text-xs font-semibold text-center transition-all ${
                                                                selectedChannel === channel.id
                                                                    ? 'border-primary bg-primary/10 text-primary ring-1 ring-primary'
                                                                    : `border-border bg-muted/40 text-foreground ${channel.color}`
                                                            }`}
                                                        >
                                                            {channel.name}
                                                        </button>
                                                    ))}
                                                </div>

                                                {selectedGatewayKey === 'manual_mobile_money' ? (
                                                    <div className="p-3.5 rounded-xl bg-muted/50 border border-border text-xs space-y-2 mt-2">
                                                        <div className="font-semibold text-foreground flex items-center gap-1.5">
                                                            <PhoneCall className="w-4 h-4 text-primary" /> Mobile Money (Lipa Namba) Payment Steps:
                                                        </div>
                                                        <ol className="list-decimal list-inside space-y-1 text-muted-foreground">
                                                            <li>Dial USSD (*150*00# for M-Pesa / *150*01# for Tigo Pesa / *150*60# for Airtel / *150*88# for Halopesa).</li>
                                                            <li>Choose Lipa kwa Simu / Lipa Namba.</li>
                                                            <li>Enter Till Number: <strong className="font-mono text-primary font-bold text-sm">{activeGateway.credentials?.till_number || 'Company Till'}</strong> ({activeGateway.credentials?.merchant_name || 'Merchant Account'}).</li>
                                                            <li>Enter Exact Amount: <strong className="text-foreground font-bold">{formatCurrency(numAmount, 'TZS')}</strong>.</li>
                                                            <li>Enter PIN and copy the SMS Reference Code below.</li>
                                                        </ol>
                                                    </div>
                                                ) : (
                                                    <div className="p-3 rounded-lg bg-primary/5 border border-primary/20 text-xs text-muted-foreground flex items-center gap-2">
                                                        <Sparkles className="w-4 h-4 text-primary shrink-0" />
                                                        <span>USSD Push prompt will be dispatched instantly to your phone upon clicking Pay.</span>
                                                    </div>
                                                )}

                                                <div className="space-y-1.5 pt-1">
                                                    <label className="text-xs font-medium text-foreground">
                                                        Customer Phone Number (+255)
                                                    </label>
                                                    <div className="relative">
                                                        <Input
                                                            type="tel"
                                                            value={form.data.phone_number}
                                                            onChange={(e) => form.setData('phone_number', e.target.value)}
                                                            placeholder="e.g. 0755123456 or +255755123456"
                                                            required
                                                            className="pl-9"
                                                        />
                                                        <Smartphone className="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2" />
                                                    </div>
                                                    {form.errors.phone_number && (
                                                        <p className="text-xs text-destructive">{form.errors.phone_number}</p>
                                                    )}
                                                </div>

                                                {selectedGatewayKey === 'manual_mobile_money' && (
                                                    <div className="space-y-1.5 pt-1">
                                                        <label className="text-xs font-medium text-foreground">
                                                            Mobile Money SMS Reference / Transaction ID
                                                        </label>
                                                        <Input
                                                            type="text"
                                                            value={form.data.reference_number}
                                                            onChange={(e) => form.setData('reference_number', e.target.value)}
                                                            placeholder="e.g. 9LK349XX21"
                                                            required
                                                        />
                                                        <p className="text-[11px] text-muted-foreground">
                                                            Enter the confirmation code received in the operator SMS.
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        )}

                                        {/* Bank Transfer Instructions & Ref */}
                                        {selectedGatewayKey === 'bank_transfer' && (
                                            <div className="space-y-3">
                                                <div className="p-3.5 rounded-xl bg-muted/50 border border-border text-xs space-y-2">
                                                    <div className="font-semibold text-foreground flex items-center gap-1.5">
                                                        <Building2 className="w-4 h-4 text-primary" /> Bank Wire Account Details:
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-2 text-xs">
                                                        <div>
                                                            <span className="text-muted-foreground block text-[11px]">Bank:</span>
                                                            <strong className="text-foreground">{activeGateway.credentials?.bank_name || 'CRDB Bank Plc'}</strong>
                                                        </div>
                                                        <div>
                                                            <span className="text-muted-foreground block text-[11px]">Account Name:</span>
                                                            <strong className="text-foreground">{activeGateway.credentials?.account_name || 'Mutamark Enterprises Ltd'}</strong>
                                                        </div>
                                                        <div>
                                                            <span className="text-muted-foreground block text-[11px]">Account Number:</span>
                                                            <strong className="font-mono text-primary font-bold">{activeGateway.credentials?.account_number || '0150283920100'}</strong>
                                                        </div>
                                                        <div>
                                                            <span className="text-muted-foreground block text-[11px]">Branch / Swift:</span>
                                                            <span className="text-foreground">{activeGateway.credentials?.branch || 'Clock Tower Branch'} ({activeGateway.credentials?.swift_code || 'CORUTZTZ'})</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div className="space-y-1.5">
                                                    <label className="text-xs font-medium text-foreground">
                                                        Bank Deposit Slip / EFT Reference
                                                    </label>
                                                    <Input
                                                        type="text"
                                                        value={form.data.reference_number}
                                                        onChange={(e) => form.setData('reference_number', e.target.value)}
                                                        placeholder="e.g. EFT-2026-902182"
                                                        required
                                                    />
                                                    <p className="text-[11px] text-muted-foreground">
                                                        Please input your wire transfer reference code for automated reconciliation.
                                                    </p>
                                                </div>
                                            </div>
                                        )}

                                        {/* Card / Pesapal / Stripe / PayPal */}
                                        {(selectedGatewayKey === 'pesapal' || selectedGatewayKey === 'stripe' || selectedGatewayKey === 'paypal') && (
                                            <div className="space-y-3">
                                                <div className="p-3 rounded-lg bg-muted/40 border border-border text-xs space-y-1.5">
                                                    <div className="font-semibold text-foreground flex items-center gap-1.5">
                                                        <CreditCard className="w-4 h-4 text-primary" /> Online Card & Wallet Switch
                                                    </div>
                                                    <p className="text-muted-foreground">
                                                        Accepts Visa, MasterCard, UnionPay, American Express and East African Mobile Wallets with 3D Secure 2.0 authentication.
                                                    </p>
                                                </div>

                                                <div className="space-y-1.5">
                                                    <label className="text-xs font-medium text-foreground">
                                                        Cardholder Contact Number
                                                    </label>
                                                    <Input
                                                        type="tel"
                                                        value={form.data.phone_number}
                                                        onChange={(e) => form.setData('phone_number', e.target.value)}
                                                        placeholder="e.g. +255 755 000 000"
                                                    />
                                                </div>
                                            </div>
                                        )}

                                        {/* Submit Button */}
                                        <div className="pt-2">
                                            <Button
                                                type="submit"
                                                size="lg"
                                                disabled={form.processing}
                                                className="w-full text-base font-semibold py-6 shadow-lg shadow-primary/20 gap-2"
                                            >
                                                {form.processing ? (
                                                    <>
                                                        <Clock className="w-5 h-5 animate-spin" /> Processing Payment...
                                                    </>
                                                ) : (
                                                    <>
                                                        <Lock className="w-4 h-4" /> Pay {formatCurrency(totalPayable, 'TZS')} Securely
                                                        <ArrowRight className="w-4 h-4 ml-1" />
                                                    </>
                                                )}
                                            </Button>
                                        </div>
                                    </form>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Right Column: Order & Invoice Summary (5 cols) */}
                        <div className="lg:col-span-5 space-y-4">
                            <Card className="border-border bg-card shadow-sm sticky top-20">
                                <CardHeader className="pb-3 border-b border-border">
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="text-base font-semibold">Payment Summary</CardTitle>
                                        <Badge variant="outline" className="font-mono text-xs text-primary border-primary/30">
                                            {transaction.transaction_reference}
                                        </Badge>
                                    </div>
                                    <CardDescription className="text-xs">
                                        Invoice #{transaction.invoice_number}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="pt-4 space-y-4">
                                    {/* Customer Box */}
                                    <div className="p-3 rounded-lg bg-muted/40 border border-border text-xs space-y-1">
                                        <div className="text-muted-foreground font-medium">Billed To:</div>
                                        <div className="font-bold text-foreground text-sm">{transaction.customer_name}</div>
                                        {transaction.customer_email && (
                                            <div className="text-muted-foreground">{transaction.customer_email}</div>
                                        )}
                                        {transaction.customer_phone && (
                                            <div className="text-muted-foreground font-mono">{transaction.customer_phone}</div>
                                        )}
                                    </div>

                                    {/* Breakdown */}
                                    <div className="space-y-2 text-sm">
                                        <div className="flex justify-between text-muted-foreground">
                                            <span>Invoice Subtotal:</span>
                                            <span className="font-medium text-foreground">{formatCurrency(numAmount, 'TZS')}</span>
                                        </div>
                                        <div className="flex justify-between text-muted-foreground">
                                            <span>Gateway Processing Fee:</span>
                                            <span className="text-emerald-600 font-medium">TZS 0.00 (Merchant Absorbed)</span>
                                        </div>
                                        <div className="pt-2 border-t border-border flex justify-between items-baseline">
                                            <span className="font-bold text-base text-foreground">Total Payable:</span>
                                            <span className="font-bold text-xl text-primary">{formatCurrency(totalPayable, 'TZS')}</span>
                                        </div>
                                    </div>

                                    {/* Trust Badges */}
                                    <div className="pt-3 border-t border-border space-y-2.5">
                                        <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                            <ShieldCheck className="w-4 h-4 text-emerald-500 shrink-0" />
                                            <span>Official Receipt Voucher (RV-YYYY-#####) issued automatically upon clearance.</span>
                                        </div>
                                        <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                            <Lock className="w-4 h-4 text-emerald-500 shrink-0" />
                                            <span>Central Bank of Tanzania (BOT) National Payment Systems compliance.</span>
                                        </div>
                                    </div>

                                    <div className="text-[11px] text-center text-muted-foreground pt-2">
                                        Need assistance? Contact finance support at{' '}
                                        <strong className="text-foreground">support@mutamark.co.tz</strong>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                )}
            </main>

            {/* Public Footer */}
            <footer className="border-t border-border py-4 px-4 text-center text-xs text-muted-foreground bg-card/40">
                <div className="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                    <div>
                        &copy; {new Date().getFullYear()} Mutamark Enterprises Ltd. All rights reserved.
                    </div>
                    <div className="flex items-center gap-4 text-[11px]">
                        <span>Terms & Conditions</span>
                        <span>Privacy Policy</span>
                        <span>Payment Guarantee</span>
                    </div>
                </div>
            </footer>
        </div>
    );
}
