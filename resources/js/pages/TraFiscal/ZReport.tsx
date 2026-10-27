import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Printer, ArrowLeft, ShieldCheck, CheckCircle2 } from 'lucide-react';
import { formatDate } from '@/utils/helpers';

interface ZReportProps {
    zReport: {
        id: number;
        z_report_number: string;
        report_date: string;
        opening_receipt_number?: string;
        closing_receipt_number?: string;
        total_receipts_count: number;
        gross_turnover: number;
        net_taxable_turnover: number;
        total_vat_18: number;
        total_vat_zero_rated: number;
        total_vat_exempt: number;
        cumulative_turnover_to_date: number;
        status: string;
        tra_ack_token?: string;
        closed_at?: string;
        closed_by_user?: {
            id: number;
            name: string;
        };
    };
}

export default function ZReport({ zReport }: ZReportProps) {
    const { t } = useTranslation();

    const handlePrint = () => {
        window.print();
    };

    return (
        <div className="min-h-screen bg-slate-100 dark:bg-slate-900 py-8 px-4 print:p-0 print:bg-white text-slate-900">
            <Head title={`${zReport.z_report_number} - Daily Fiscal Z-Report`} />

            {/* Print Header */}
            <div className="max-w-2xl mx-auto mb-6 flex justify-between items-center print:hidden">
                <Button variant="outline" size="sm" asChild>
                    <Link href={route('tra-fiscal.index')}>
                        <ArrowLeft className="h-4 w-4 mr-1.5" />
                        {t('Back')}
                    </Link>
                </Button>
                <Button onClick={handlePrint} className="flex items-center gap-2">
                    <Printer className="h-4 w-4" />
                    {t('Print Statutory Z-Report')}
                </Button>
            </div>

            {/* Official Z-Report Document */}
            <div className="max-w-2xl mx-auto bg-white p-8 md:p-12 shadow-lg print:shadow-none print:p-0 border border-slate-200 print:border-none rounded-lg font-mono text-xs">
                {/* Header */}
                <div className="text-center space-y-1 border-b-2 border-slate-900 pb-4 mb-6">
                    <h1 className="text-base font-bold tracking-wider">TANZANIA REVENUE AUTHORITY</h1>
                    <h2 className="text-sm font-semibold tracking-wide">ELECTRONIC FISCAL DEVICE MANAGEMENT SYSTEM (EFDMS)</h2>
                    <h3 className="text-lg font-black pt-2 uppercase">DAILY STATUTORY FISCAL Z-REPORT</h3>
                    <p className="text-[11px] text-slate-600">VIRTUAL FISCAL DEVICE (VFD) CLOSURE</p>
                </div>

                {/* Taxpayer Meta */}
                <div className="grid grid-cols-2 gap-4 border-b border-slate-300 pb-4 mb-4">
                    <div>
                        <div className="font-bold text-sm">MUTAMARK ENTERPRISES</div>
                        <div className="text-slate-600">TIN: 100-200-300</div>
                        <div className="text-slate-600">VRN: 40-001234-Z</div>
                        <div className="text-slate-600">SERIAL: MUTAMARK-VFD-01</div>
                    </div>
                    <div className="text-right space-y-0.5">
                        <div><strong className="font-bold">Z-REPORT NO:</strong> {zReport.z_report_number}</div>
                        <div><strong>REPORT DATE:</strong> {formatDate(zReport.report_date)}</div>
                        <div><strong>CLOSED AT:</strong> {zReport.closed_at ? formatDate(zReport.closed_at) : 'OPEN'}</div>
                        <div><strong>STATUS:</strong> <span className="uppercase font-bold">{zReport.status}</span></div>
                    </div>
                </div>

                {/* Receipt Ranges */}
                <div className="bg-slate-50 p-3 rounded border border-slate-200 mb-6 flex justify-between">
                    <div>
                        <span className="text-slate-500 block text-[10px]">OPENING RECEIPT:</span>
                        <span className="font-semibold">{zReport.opening_receipt_number || '-'}</span>
                    </div>
                    <div>
                        <span className="text-slate-500 block text-[10px]">CLOSING RECEIPT:</span>
                        <span className="font-semibold">{zReport.closing_receipt_number || '-'}</span>
                    </div>
                    <div className="text-right">
                        <span className="text-slate-500 block text-[10px]">TOTAL RECEIPTS:</span>
                        <span className="font-bold text-sm">{zReport.total_receipts_count}</span>
                    </div>
                </div>

                {/* Tax Categories Breakdown Table */}
                <div className="mb-6">
                    <h4 className="font-bold uppercase tracking-wider mb-2 text-[11px] border-b pb-1">
                        TURNOVER & TAX CATEGORIES SUMMARY
                    </h4>
                    <table className="w-full text-left">
                        <thead className="bg-slate-100 uppercase text-[10px]">
                            <tr>
                                <th className="p-2">TAX CATEGORY</th>
                                <th className="p-2 text-right">NET TURNOVER (TZS)</th>
                                <th className="p-2 text-right">TAX AMOUNT (TZS)</th>
                                <th className="p-2 text-right">GROSS TURNOVER (TZS)</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-200">
                            <tr>
                                <td className="p-2 font-medium">VAT A (Standard Rate 18.00%)</td>
                                <td className="p-2 text-right font-mono">{Number(zReport.net_taxable_turnover).toLocaleString()}</td>
                                <td className="p-2 text-right font-mono font-semibold text-emerald-700">{Number(zReport.total_vat_18).toLocaleString()}</td>
                                <td className="p-2 text-right font-mono font-bold">{Number(zReport.gross_turnover).toLocaleString()}</td>
                            </tr>
                            <tr>
                                <td className="p-2 font-medium">VAT B (Special Rate)</td>
                                <td className="p-2 text-right font-mono">0.00</td>
                                <td className="p-2 text-right font-mono">0.00</td>
                                <td className="p-2 text-right font-mono">0.00</td>
                            </tr>
                            <tr>
                                <td className="p-2 font-medium">VAT C (Zero Rated 0.00%)</td>
                                <td className="p-2 text-right font-mono">{Number(zReport.total_vat_zero_rated).toLocaleString()}</td>
                                <td className="p-2 text-right font-mono">0.00</td>
                                <td className="p-2 text-right font-mono">{Number(zReport.total_vat_zero_rated).toLocaleString()}</td>
                            </tr>
                            <tr>
                                <td className="p-2 font-medium">VAT D (Exempt 0.00%)</td>
                                <td className="p-2 text-right font-mono">{Number(zReport.total_vat_exempt).toLocaleString()}</td>
                                <td className="p-2 text-right font-mono">0.00</td>
                                <td className="p-2 text-right font-mono">{Number(zReport.total_vat_exempt).toLocaleString()}</td>
                            </tr>
                        </tbody>
                        <tfoot className="border-t-2 border-slate-800 font-bold bg-slate-50 text-[11px]">
                            <tr>
                                <td className="p-2">DAILY TOTALS:</td>
                                <td className="p-2 text-right font-mono">{Number(zReport.net_taxable_turnover).toLocaleString()}</td>
                                <td className="p-2 text-right font-mono text-emerald-800">{Number(zReport.total_vat_18).toLocaleString()}</td>
                                <td className="p-2 text-right font-mono">{Number(zReport.gross_turnover).toLocaleString()}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {/* Fiscal Memory Cumulative Accumulation */}
                <div className="p-4 rounded bg-slate-50 border border-slate-300 mb-8 space-y-1.5">
                    <div className="flex justify-between">
                        <span className="font-bold">CUMULATIVE TURNOVER TO DATE:</span>
                        <span className="font-bold font-mono text-sm">
                            {Number(zReport.cumulative_turnover_to_date).toLocaleString()} TZS
                        </span>
                    </div>
                    {zReport.tra_ack_token && (
                        <div className="flex justify-between pt-1 border-t text-[11px] text-slate-600">
                            <span>TRA SERVER ACKNOWLEDGMENT:</span>
                            <span className="font-mono font-semibold text-emerald-700">{zReport.tra_ack_token}</span>
                        </div>
                    )}
                </div>

                {/* Sign-off Handover */}
                <div className="grid grid-cols-2 gap-8 pt-6 border-t border-slate-300">
                    <div className="space-y-6">
                        <div>
                            <span className="font-bold block">FISCAL COMPLIANCE OFFICER</span>
                            <span className="text-[10px] text-slate-500">Mutamark Financial Controllership</span>
                        </div>
                        <div className="border-b border-dashed border-slate-400"></div>
                        <div className="text-[10px] text-slate-500">Signature & Official Stamp</div>
                    </div>

                    <div className="space-y-6 text-right">
                        <div>
                            <span className="font-bold block">CLOSED BY</span>
                            <span className="text-[10px] text-slate-500">{zReport.closed_by_user?.name || 'Chief Accountant'}</span>
                        </div>
                        <div className="border-b border-dashed border-slate-400"></div>
                        <div className="text-[10px] text-slate-500">Verification Timestamp</div>
                    </div>
                </div>
            </div>
        </div>
    );
}
