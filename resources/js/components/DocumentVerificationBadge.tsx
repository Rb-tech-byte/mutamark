import React, { useEffect, useState } from 'react';
import QRCode from 'qrcode';
import { ShieldCheck, ExternalLink, QrCode as QrIcon, Landmark, Smartphone, CheckCircle2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

export interface DocumentVerificationData {
    signature_version?: number;
    id?: number;
    verification_hash: string;
    document_type: string;
    document_number: string;
    qr_payload: string;
    issuer_name: string;
    party_name?: string;
    total_amount: number;
    tax_amount?: number;
    currency: string;
    issue_date?: string;
    due_date?: string;
    status: string;
    signatory_name?: string;
    signatory_title?: string;
    lipa_namba?: string;
    tin_number?: string;
    vrn_number?: string;
    bank_details?: string;
    verified_at?: string;
}

interface Props {
    verification?: DocumentVerificationData | null;
    mode?: 'view' | 'print';
    className?: string;
}

export default function DocumentVerificationBadge({
    verification,
    mode = 'view',
    className = '',
}: Props) {
    const [qrDataUrl, setQrDataUrl] = useState<string>('');

    const qrPayload = verification?.qr_payload || (
        verification?.verification_hash
            ? `${window.location.origin}/verify/document/${verification.verification_hash}`
            : ''
    );

    const lipaNamba = verification?.lipa_namba || '';
    const tin = verification?.tin_number || '100-200-300';
    const vrn = verification?.vrn_number || '40-001234-Z';

    useEffect(() => {
        if (!qrPayload) return;
        QRCode.toDataURL(qrPayload, {
            errorCorrectionLevel: 'M',
            margin: 1,
            width: mode === 'print' ? 140 : 120,
            color: {
                dark: '#0f172a',
                light: '#ffffff',
            },
        })
            .then((url) => setQrDataUrl(url))
            .catch((err) => console.error('Failed to generate verification QR code:', err));
    }, [qrPayload, mode]);

    if (!verification) {
        return null;
    }

    if (mode === 'print') {
        return (
            <div className={`border-2 border-slate-300 bg-white p-4 rounded-lg my-4 page-break-inside-avoid text-slate-800 ${className}`}>
                <div className="grid grid-cols-12 gap-4 items-center">
                    {/* QR Code */}
                    <div className="col-span-3 text-center border-r border-slate-200 pr-3">
                        {qrDataUrl ? (
                            <img
                                src={qrDataUrl}
                                alt="Official Verification QR"
                                className="w-24 h-24 mx-auto object-contain border border-slate-200 rounded"
                            />
                        ) : (
                            <div className="w-24 h-24 mx-auto border border-dashed border-slate-300 rounded flex items-center justify-center text-xs text-slate-400">
                                Generating QR...
                            </div>
                        )}
                        <p className="text-[10px] text-slate-500 font-medium mt-1">Scan to Verify Online</p>
                    </div>

                    {/* Official Payment & Tax Instructions */}
                    <div className="col-span-5 text-left border-r border-slate-200 pr-3">
                        <div className="flex items-center gap-1.5 mb-1 text-emerald-800 font-bold text-xs uppercase tracking-wide">
                            <Smartphone className="w-3.5 h-3.5" />
                            <span>Tanzania Official Payment</span>
                        </div>
                        <div className="bg-emerald-50 border border-emerald-300 rounded px-2.5 py-1.5 mb-1.5">
                            <span className="text-[11px] font-semibold text-emerald-900 block">
                                LIPA HAPA (TILL): <span className="font-mono text-xs font-black tracking-wider text-emerald-950">{lipaNamba}</span>
                            </span>
                            <span className="text-[9px] text-emerald-700 block">
                                M-Pesa / Tigo Pesa / Airtel Money / HaloPesa
                            </span>
                        </div>
                        <div className="text-[10px] text-slate-600 space-y-0.5">
                            <p><strong className="font-semibold text-slate-700">Bank:</strong> CRDB / NMB Bank</p>
                            <p><strong className="font-semibold text-slate-700">A/C Name:</strong> MUTAMARK ENTERPRISES</p>
                            <p><strong className="font-semibold text-slate-700">A/C No:</strong> 0150284758200</p>
                            <p className="text-[9px] text-slate-500 pt-0.5">TIN: {tin} | VRN: {vrn}</p>
                        </div>
                    </div>

                    {/* Verification Seal & Signatory */}
                    <div className="col-span-4 text-center pl-1">
                        <div className="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded-full mb-1">
                            <ShieldCheck className="w-3 h-3 text-emerald-700" />
                            <span>CRYPTOGRAPHICALLY VERIFIED</span>
                        </div>
                        <p className="text-[10px] font-bold text-slate-900 uppercase">
                            {verification.signatory_name || 'Managing Director'}
                        </p>
                        <p className="text-[9px] text-slate-500 italic">
                            {verification.signatory_title || 'Mutamark Enterprises Directorate'}
                        </p>
                        <div className="mt-1 pt-1 border-t border-slate-200">
                            <p className="text-[8px] font-mono text-slate-400 break-all leading-tight">
                                SHA-256: {verification.verification_hash.substring(0, 16)}...{verification.verification_hash.substring(verification.verification_hash.length - 8)}
                            </p>
                            <p className="text-[8px] text-slate-400 font-medium">Wolinet Security Engine</p>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // Interactive View Mode
    return (
        <div className={`rounded-xl border bg-card text-card-foreground shadow-sm p-4 ${className}`}>
            <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                {/* Left: QR Code */}
                <div className="flex items-center gap-4">
                    <div className="relative group bg-white p-2 rounded-lg border shadow-sm">
                        {qrDataUrl ? (
                            <img
                                src={qrDataUrl}
                                alt="Verification QR Code"
                                className="w-20 h-20 object-contain"
                            />
                        ) : (
                            <div className="w-20 h-20 flex items-center justify-center bg-muted rounded">
                                <QrIcon className="w-8 h-8 text-muted-foreground animate-pulse" />
                            </div>
                        )}
                        <span className="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[9px] px-1.5 py-0.5 rounded font-medium opacity-90 shadow">
                            QR VERIFIED
                        </span>
                    </div>

                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <Badge variant="outline" className="bg-emerald-50 text-emerald-700 border-emerald-200 font-semibold gap-1">
                                <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                                <span>Authentic Document</span>
                            </Badge>
                            <span className="text-xs text-muted-foreground">
                                Ref: #{verification.document_number}
                            </span>
                        </div>
                        <p className="text-sm font-medium text-foreground">
                            Wolinet Cryptographic Document Verification
                        </p>
                        <p className="text-xs font-mono text-muted-foreground">
                            SHA-256: {verification.verification_hash.substring(0, 12)}...{verification.verification_hash.substring(verification.verification_hash.length - 8)}
                        </p>
                    </div>
                </div>

                {/* Center: Tanzania Lipa Namba & Banking */}
                <div className="flex flex-wrap items-center gap-3 bg-muted/40 p-2.5 rounded-lg border text-xs">
                    <div className="space-y-0.5">
                        <div className="flex items-center gap-1.5 text-foreground font-semibold">
                            <Smartphone className="w-3.5 h-3.5 text-emerald-600" />
                            <span>Lipa Namba (Till):</span>
                            <span className="font-mono font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded text-sm">
                                {lipaNamba}
                            </span>
                        </div>
                        <p className="text-[11px] text-muted-foreground">
                            M-Pesa / Tigo Pesa / Airtel Money
                        </p>
                    </div>
                    <div className="hidden sm:block h-8 w-px bg-border" />
                    <div className="space-y-0.5">
                        <div className="flex items-center gap-1.5 text-foreground font-medium">
                            <Landmark className="w-3.5 h-3.5 text-blue-600" />
                            <span>CRDB / NMB: 0150284758200</span>
                        </div>
                        <p className="text-[11px] text-muted-foreground">
                            A/C: MUTAMARK ENTERPRISES
                        </p>
                    </div>
                </div>

                {/* Right: Public Verification Action Button */}
                <div>
                    <Button
                        size="sm"
                        variant="outline"
                        className="gap-2 border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 text-emerald-700 font-medium"
                        asChild
                    >
                        <a
                            href={qrPayload}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <ShieldCheck className="w-4 h-4 text-emerald-600" />
                            <span>Public Verification</span>
                            <ExternalLink className="w-3.5 h-3.5 opacity-70" />
                        </a>
                    </Button>
                </div>
            </div>
        </div>
    );
}
