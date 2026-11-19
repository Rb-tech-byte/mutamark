import { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { CorporateDocumentHeader } from '@/components/CorporateDocumentTemplate';

export default function Print() {
    const { t } = useTranslation();
    const { data, filters } = usePage<any>().props;
    const [isDownloading, setIsDownloading] = useState(false);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf') {
            downloadPDF();
        }
    }, []);

    const downloadPDF = async () => {
        setIsDownloading(true);
        const printContent = document.querySelector('.report-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `vendor-balance-${filters.as_of_date}.pdf`,
                image: { type: 'jpeg' as const, quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' as const }
            };
            try {
                await html2pdf().set(opt).from(printContent as HTMLElement).save();
                setTimeout(() => window.close(), 1000);
            } catch (error) {
                console.error('PDF generation failed:', error);
            }
        }
        setIsDownloading(false);
    };

    const vendors = Array.isArray(data?.vendors) ? data.vendors : [];

    return (
        <div className="min-h-screen bg-white text-slate-900">
            <Head title={t('Vendor Balance Summary')} />
            {isDownloading && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 print:hidden">
                    <div className="bg-white p-6 rounded-lg shadow-lg">
                        <div className="flex items-center space-x-3">
                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-[#1B365D]"></div>
                            <p className="text-sm font-semibold text-gray-700">{t('Generating PDF...')}</p>
                        </div>
                    </div>
                </div>
            )}

            <div className="print:hidden max-w-6xl mx-auto px-8 pt-4 flex justify-end gap-2">
                <button type="button" onClick={() => window.print()} className="rounded bg-[#1B365D] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90">
                    {t('Print')}
                </button>
                <button type="button" onClick={downloadPDF} className="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    {t('Download PDF')}
                </button>
            </div>

            <div className="report-container bg-white max-w-6xl mx-auto p-8 print:p-4" style={{ fontFamily: 'Arial, Helvetica, Inter, system-ui, sans-serif' }}>
                <CorporateDocumentHeader
                    title={t('VENDOR BALANCE SUMMARY')}
                    metaItems={[
                        { label: t('As of'), value: formatDate(filters.as_of_date) },
                        { label: t('Generated On'), value: formatDate(new Date().toISOString()) },
                    ]}
                />

                <div className="border border-[#9EB3C8] overflow-hidden">
                    <table className="w-full border-collapse text-[11px]">
                        <thead>
                            <tr className="bg-[#1B365D] text-white">
                                <th className="text-center py-2 px-2 font-bold border-r border-[#334E73] w-10">#</th>
                                <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Vendor')}</th>
                                <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Email')}</th>
                                <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Total Billed')}</th>
                                <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Total Returns & Debit Notes')}</th>
                                <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Total Paid')}</th>
                                <th className="text-right py-2 px-2.5 font-bold">{t('Balance')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {vendors.length === 0 ? (
                                <tr className="h-[32px] border-b border-[#C5D3E0]">
                                    <td className="py-2 px-2 text-center border-r border-[#C5D3E0] text-slate-400">1</td>
                                    <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="py-2 px-2.5">&nbsp;</td>
                                </tr>
                            ) : (
                                vendors.map((vendor: any, idx: number) => (
                                    <tr key={idx} className="border-b border-[#C5D3E0]">
                                        <td className="py-2 px-2 text-center border-r border-[#C5D3E0]">{idx + 1}</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0] font-medium">{vendor.vendor_name}</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{vendor.vendor_email}</td>
                                        <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(vendor.total_billed)}</td>
                                        <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(vendor.total_returns)}</td>
                                        <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(vendor.total_paid)}</td>
                                        <td className="py-2 px-2.5 text-right font-semibold">{formatCurrency(vendor.balance)}</td>
                                    </tr>
                                ))
                            )}
                            <tr className="bg-[#EAF5F1] border-t-[1.5px] border-t-[#0E7C5B] font-bold">
                                <td colSpan={3} className="py-2 px-2.5 uppercase border-r border-[#C5D3E0]">{t('TOTAL')}</td>
                                <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(vendors.reduce((sum: number, v: any) => sum + (Number(v.total_billed) || 0), 0))}</td>
                                <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(vendors.reduce((sum: number, v: any) => sum + (Number(v.total_returns) || 0), 0))}</td>
                                <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(vendors.reduce((sum: number, v: any) => sum + (Number(v.total_paid) || 0), 0))}</td>
                                <td className="py-2 px-2.5 text-right">{formatCurrency(data.total_balance || 0)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div className="mt-6 text-center text-[11px] font-bold text-[#0E7C5B]">
                    {t('Thank you for your business.')}
                </div>
            </div>
        </div>
    );
}
