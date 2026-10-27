import { useEffect, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import QRCode from 'qrcode';
import { Button } from '@/components/ui/button';
import { Printer, ArrowLeft, ShieldCheck, QrCode as QrIcon } from 'lucide-react';
import { formatDate } from '@/utils/helpers';

interface ReceiptProps {
    receipt: {
        id: number;
        receipt_number: string;
        source_type: string;
        source_id: number;
        invoice_number: string;
        customer_name: string;
        customer_tin: string;
        customer_vrn?: string;
        total_tax_exclusive_amount: number;
        tax_amount_a: number;
        total_tax_inclusive_amount: number;
        z_number: string;
        daily_receipt_counter: number;
        rct_verification_code: string;
        qr_payload: string;
        vfd_device_serial: string;
        status: string;
        fiscalized_at: string;
        invoice?: {
            id: number;
            items?: Array<{
                id: number;
                product?: {
                    name: string;
                };
                quantity: number;
                unit_price: number;
                total: number;
            }>;
        };
        verification?: {
            verification_hash: string;
        };
    };
}

export default function Receipt({ receipt }: ReceiptProps) {
    const { t } = useTranslation();
    const [qrDataUrl, setQrDataUrl] = useState<string>('');

    useEffect(() => {
        if (receipt.qr_payload) {
            QRCode.toDataURL(receipt.qr_payload, {
                errorCorrectionLevel: 'M',
                margin: 1,
                width: 140,
                color: {
                    dark: '#000000',
                    light: '#ffffff',
                },
            })
                .then(url => setQrDataUrl(url))
                .catch(err => console.error('Failed to generate QR code:', err));
        }
    }, [receipt.qr_payload]);

    const handlePrint = () => {
        window.print();
    };

    return (
        <div className="min-h-screen bg-slate-100 dark:bg-slate-900 py-8 px-4 print:p-0 print:bg-white text-slate-900">
            <Head title={`${receipt.receipt_number} - TRA Official Fiscal Receipt`} />

            {/* Actions Bar */}
            <div className="max-w-md mx-auto mb-4 flex justify-between items-center print:hidden">
                <Button variant="outline" size="sm" asChild>
                    <Link href={route('tra-fiscal.index')}>
                        <ArrowLeft className="h-4 w-4 mr-1.5" />
                        {t('Back to VFD Dashboard')}
                    </Link>
                </Button>
                <Button onClick={handlePrint} size="sm" className="bg-slate-900 text-white flex items-center gap-1.5">
                    <Printer className="h-4 w-4" />
                    {t('Print Fiscal Slip')}
                </Button>
            </div>

            {/* Official 80mm TRA Thermal Fiscal Slip */}
            <div className="max-w-[380px] mx-auto bg-white p-6 shadow-md print:shadow-none print:p-0 border border-slate-300 print:border-none rounded-sm font-mono text-xs leading-relaxed">
                {/* Header */}
                <div className="text-center space-y-0.5 border-b border-dashed border-slate-400 pb-3 mb-3">
                    <h2 className="font-bold text-sm tracking-wide">START OF LEGAL RECEIPT</h2>
                    <h1 className="font-black text-base uppercase">MUTAMARK ENTERPRISES</h1>
                    <p className="text-[11px]">SAMORA TOWER, DAR ES SALAAM</p>
                    <p className="text-[11px]">TEL: +255 22 212 3456</p>
                    <div className="pt-1 text-[11px] font-semibold">
                        <p>TIN: 100-200-300</p>
                        <p>VRN: 40-001234-Z</p>
                        <p>SERIAL: {receipt.vfd_device_serial}</p>
                    </div>
                </div>

                {/* Customer & Receipt Meta */}
                <div className="space-y-1 border-b border-dashed border-slate-400 pb-3 mb-3 text-[11px]">
                    <div className="flex justify-between">
                        <span>RECEIPT NO:</span>
                        <span className="font-bold">{receipt.receipt_number}</span>
                    </div>
                    <div className="flex justify-between">
                        <span>DATE / TIME:</span>
                        <span>{formatDate(receipt.fiscalized_at)}</span>
                    </div>
                    <div className="flex justify-between">
                        <span>Z-REPORT NO:</span>
                        <span>{receipt.z_number}</span>
                    </div>
                    <div className="flex justify-between">
                        <span>DAILY COUNTER:</span>
                        <span>#{receipt.daily_receipt_counter}</span>
                    </div>
                    <div className="pt-1 flex justify-between">
                        <span>CUSTOMER:</span>
                        <span className="font-semibold">{receipt.customer_name}</span>
                    </div>
                    <div className="flex justify-between">
                        <span>CUSTOMER TIN:</span>
                        <span>{receipt.customer_tin}</span>
                    </div>
                    {receipt.invoice_number && (
                        <div className="flex justify-between">
                            <span>INVOICE REF:</span>
                            <span>{receipt.invoice_number}</span>
                        </div>
                    )}
                </div>

                {/* Items Breakdown */}
                <div className="border-b border-dashed border-slate-400 pb-3 mb-3">
                    <div className="flex justify-between font-bold border-b border-slate-200 pb-1 mb-1 text-[11px]">
                        <span>DESCRIPTION</span>
                        <span>AMOUNT (TZS)</span>
                    </div>

                    {receipt.invoice?.items && receipt.invoice.items.length > 0 ? (
                        receipt.invoice.items.map((it, idx) => (
                            <div key={idx} className="flex justify-between text-[11px] py-0.5">
                                <span className="truncate max-w-[200px]">
                                    {it.product?.name || 'Item'} (x{it.quantity})
                                </span>
                                <span className="font-medium">{Number(it.total).toLocaleString()}</span>
                            </div>
                        ))
                    ) : (
                        <div className="flex justify-between text-[11px] py-0.5">
                            <span>COMMERCIAL INVOICE ITEMS</span>
                            <span className="font-medium">
                                {Number(receipt.total_tax_inclusive_amount).toLocaleString()}
                            </span>
                        </div>
                    )}
                </div>

                {/* Tax Categories Breakdown */}
                <div className="space-y-1 border-b border-dashed border-slate-400 pb-3 mb-3 text-[11px]">
                    <div className="flex justify-between">
                        <span>TOTAL EXCL. TAX:</span>
                        <span>{Number(receipt.total_tax_exclusive_amount).toLocaleString()} TZS</span>
                    </div>
                    <div className="flex justify-between font-semibold">
                        <span>TOTAL VAT (A - 18.00%):</span>
                        <span>{Number(receipt.tax_amount_a).toLocaleString()} TZS</span>
                    </div>
                    <div className="flex justify-between font-black text-sm pt-1 border-t border-slate-300">
                        <span>TOTAL INCL. TAX:</span>
                        <span>{Number(receipt.total_tax_inclusive_amount).toLocaleString()} TZS</span>
                    </div>
                </div>

                {/* TRA Verification QR Code & Code */}
                <div className="text-center space-y-2 pt-1">
                    <div className="flex justify-center">
                        {qrDataUrl ? (
                            <img src={qrDataUrl} alt="TRA EFDMS QR Code" className="w-28 h-28 mx-auto" />
                        ) : (
                            <div className="w-28 h-28 border border-dashed border-slate-300 flex items-center justify-center text-[10px]">
                                Loading QR...
                            </div>
                        )}
                    </div>

                    <div className="space-y-0.5">
                        <div className="text-[10px] text-slate-600 font-semibold uppercase tracking-wider">
                            RECEIPT VERIFICATION CODE:
                        </div>
                        <div className="font-bold text-xs tracking-wider text-slate-900 bg-slate-100 py-1 rounded">
                            {receipt.rct_verification_code}
                        </div>
                    </div>

                    <div className="text-[10px] text-slate-500 pt-1">
                        SCAN QR CODE OR VISIT VERIFY.TRA.GO.TZ TO VERIFY THIS RECEIPT.
                    </div>
                    <div className="font-bold text-[11px] pt-1">
                        END OF LEGAL RECEIPT
                    </div>
                </div>
            </div>
        </div>
    );
}
