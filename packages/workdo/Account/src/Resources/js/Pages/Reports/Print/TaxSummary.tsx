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
                filename: `tax-summary-${filters.from_date}-to-${filters.to_date}.pdf`,
                image: { type: 'jpeg' as const, quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' as const }
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

    const collected = Array.isArray(data?.tax_collected?.items) ? data.tax_collected.items : [];
    const paid = Array.isArray(data?.tax_paid?.items) ? data.tax_paid.items : [];

    return (
        <div className="min-h-screen bg-white text-slate-900">
            <Head title={t('Tax Summary Report')} />
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

            <div className="print:hidden max-w-4xl mx-auto px-8 pt-4 flex justify-end gap-2">
                <button type="button" onClick={() => window.print()} className="rounded bg-[#1B365D] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90">
                    {t('Print')}
                </button>
                <button type="button" onClick={downloadPDF} className="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    {t('Download PDF')}
                </button>
            </div>

            <div className="report-container bg-white max-w-4xl mx-auto p-8 print:p-4" style={{ fontFamily: 'Arial, Helvetica, Inter, system-ui, sans-serif' }}>
                <CorporateDocumentHeader
                    title={t('TAX SUMMARY REPORT')}
                    metaItems={[
                        { label: t('Period'), value: `${formatDate(filters.from_date)} ${t('to')} ${formatDate(filters.to_date)}` },
                        { label: t('Generated On'), value: formatDate(new Date().toISOString()) },
                    ]}
                />

                <div className="border border-[#9EB3C8] overflow-hidden">
                    <table className="w-full border-collapse text-[11px]">
                        <thead>
                            <tr className="bg-[#1B365D] text-white">
                                <th className="text-left py-2 px-3 font-bold border-r border-[#334E73]">{t('Tax Category')}</th>
                                <th className="text-right py-2 px-3 font-bold w-44">{t('Amount')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr className="bg-[#EBF1F6] border-b border-[#9EB3C8]">
                                <td colSpan={2} className="py-1.5 px-3 font-extrabold text-[10.5px] uppercase tracking-wide text-[#1B365D]">
                                    {t('Tax Collected (Sales)')}
                                </td>
                            </tr>
                            {collected.length === 0 ? (
                                <tr className="h-[30px] border-b border-[#C5D3E0]">
                                    <td className="py-2 px-6 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="py-2 px-3 text-right">&nbsp;</td>
                                </tr>
                            ) : (
                                collected.map((item: any, idx: number) => (
                                    <tr key={idx} className="border-b border-[#C5D3E0]">
                                        <td className="py-2 px-6 border-r border-[#C5D3E0]">{item.tax_name}</td>
                                        <td className="py-2 px-3 text-right">{formatCurrency(item.amount)}</td>
                                    </tr>
                                ))
                            )}
                            <tr className="font-bold bg-slate-50 border-b border-[#9EB3C8]">
                                <td className="py-2 px-3 border-r border-[#C5D3E0]">{t('Total Tax Collected')}</td>
                                <td className="py-2 px-3 text-right">{formatCurrency(data.tax_collected?.total || 0)}</td>
                            </tr>

                            <tr className="bg-[#EBF1F6] border-b border-[#9EB3C8]">
                                <td colSpan={2} className="py-1.5 px-3 font-extrabold text-[10.5px] uppercase tracking-wide text-[#1B365D]">
                                    {t('Tax Paid (Purchases)')}
                                </td>
                            </tr>
                            {paid.length === 0 ? (
                                <tr className="h-[30px] border-b border-[#C5D3E0]">
                                    <td className="py-2 px-6 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="py-2 px-3 text-right">&nbsp;</td>
                                </tr>
                            ) : (
                                paid.map((item: any, idx: number) => (
                                    <tr key={idx} className="border-b border-[#C5D3E0]">
                                        <td className="py-2 px-6 border-r border-[#C5D3E0]">{item.tax_name}</td>
                                        <td className="py-2 px-3 text-right">{formatCurrency(item.amount)}</td>
                                    </tr>
                                ))
                            )}
                            <tr className="font-bold bg-slate-50 border-b border-[#9EB3C8]">
                                <td className="py-2 px-3 border-r border-[#C5D3E0]">{t('Total Tax Paid')}</td>
                                <td className="py-2 px-3 text-right">{formatCurrency(data.tax_paid?.total || 0)}</td>
                            </tr>

                            <tr className="font-extrabold bg-[#EAF5F1] border-t-[1.5px] border-t-[#0E7C5B] text-[12px]">
                                <td className="py-2.5 px-3 uppercase border-r border-[#C5D3E0]">{t('Net Tax Liability')}</td>
                                <td className="py-2.5 px-3 text-right">{formatCurrency(data.net_tax_liability || 0)}</td>
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
