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
                filename: `customer-detail-${data.customer?.name || 'report'}-${filters.start_date || 'all'}.pdf`,
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

    const invoices = Array.isArray(data?.invoices) ? data.invoices : [];
    const returns = Array.isArray(data?.returns) ? data.returns : [];
    const creditNotes = Array.isArray(data?.credit_notes) ? data.credit_notes : [];
    const payments = Array.isArray(data?.payments) ? data.payments : [];

    return (
        <div className="min-h-screen bg-white text-slate-900">
            <Head title={t('Customer Detail Report')} />
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

            <div className="print:hidden max-w-5xl mx-auto px-8 pt-4 flex justify-end gap-2">
                <button type="button" onClick={() => window.print()} className="rounded bg-[#1B365D] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90">
                    {t('Print')}
                </button>
                <button type="button" onClick={downloadPDF} className="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    {t('Download PDF')}
                </button>
            </div>

            <div className="report-container bg-white max-w-5xl mx-auto p-8 print:p-4" style={{ fontFamily: 'Arial, Helvetica, Inter, system-ui, sans-serif' }}>
                <CorporateDocumentHeader
                    title={t('CUSTOMER DETAIL REPORT')}
                    metaItems={[
                        { label: t('Customer'), value: data.customer?.name },
                        filters.start_date
                            ? { label: t('Period'), value: `${formatDate(filters.start_date)} - ${formatDate(filters.end_date)}` }
                            : null,
                        { label: t('Generated On'), value: formatDate(new Date().toISOString()) },
                    ].filter(Boolean) as Array<{ label: string; value: any }>}
                />

                <div className="mb-5">
                    <div className="text-[10.5px] font-extrabold uppercase tracking-wide text-[#1B365D] mb-1.5">{t('SALES INVOICES')}</div>
                    <div className="border border-[#9EB3C8] overflow-hidden">
                        <table className="w-full border-collapse text-[11px]">
                            <thead>
                                <tr className="bg-[#1B365D] text-white">
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Invoice Number')}</th>
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Date')}</th>
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Due Date')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Subtotal')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Tax')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Total')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold">{t('Balance')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {invoices.length === 0 ? (
                                    <tr className="h-[30px]">
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5">&nbsp;</td>
                                    </tr>
                                ) : (
                                    invoices.map((invoice: any, idx: number) => (
                                        <tr key={idx} className="border-b border-[#C5D3E0] last:border-b-0">
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{invoice.invoice_number}</td>
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{formatDate(invoice.date)}</td>
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{formatDate(invoice.due_date)}</td>
                                            <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(invoice.subtotal)}</td>
                                            <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(invoice.tax_amount)}</td>
                                            <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(invoice.total_amount)}</td>
                                            <td className="py-2 px-2.5 text-right font-semibold">{formatCurrency(invoice.balance_amount)}</td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="mb-5">
                    <div className="text-[10.5px] font-extrabold uppercase tracking-wide text-[#1B365D] mb-1.5">{t('SALES RETURNS')}</div>
                    <div className="border border-[#9EB3C8] overflow-hidden">
                        <table className="w-full border-collapse text-[11px]">
                            <thead>
                                <tr className="bg-[#1B365D] text-white">
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Return Number')}</th>
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Date')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Subtotal')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Tax')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold">{t('Total')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {returns.length === 0 ? (
                                    <tr className="h-[30px]">
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5">&nbsp;</td>
                                    </tr>
                                ) : (
                                    returns.map((ret: any, idx: number) => (
                                        <tr key={idx} className="border-b border-[#C5D3E0] last:border-b-0">
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{ret.return_number}</td>
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{formatDate(ret.date)}</td>
                                            <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(ret.subtotal)}</td>
                                            <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(ret.tax_amount)}</td>
                                            <td className="py-2 px-2.5 text-right font-semibold">{formatCurrency(ret.total_amount)}</td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="mb-5">
                    <div className="text-[10.5px] font-extrabold uppercase tracking-wide text-[#1B365D] mb-1.5">{t('CREDIT NOTES')}</div>
                    <div className="border border-[#9EB3C8] overflow-hidden">
                        <table className="w-full border-collapse text-[11px]">
                            <thead>
                                <tr className="bg-[#1B365D] text-white">
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Credit Note Number')}</th>
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Date')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Total')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Applied')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold">{t('Balance')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {creditNotes.length === 0 ? (
                                    <tr className="h-[30px]">
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5">&nbsp;</td>
                                    </tr>
                                ) : (
                                    creditNotes.map((note: any, idx: number) => (
                                        <tr key={idx} className="border-b border-[#C5D3E0] last:border-b-0">
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{note.credit_note_number}</td>
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{formatDate(note.date)}</td>
                                            <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(note.total_amount)}</td>
                                            <td className="py-2 px-2.5 text-right border-r border-[#C5D3E0]">{formatCurrency(note.applied_amount)}</td>
                                            <td className="py-2 px-2.5 text-right font-semibold">{formatCurrency(note.balance_amount)}</td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="mb-5">
                    <div className="text-[10.5px] font-extrabold uppercase tracking-wide text-[#1B365D] mb-1.5">{t('CUSTOMER PAYMENTS')}</div>
                    <div className="border border-[#9EB3C8] overflow-hidden">
                        <table className="w-full border-collapse text-[11px]">
                            <thead>
                                <tr className="bg-[#1B365D] text-white">
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Payment Number')}</th>
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Date')}</th>
                                    <th className="text-left py-2 px-2.5 font-bold border-r border-[#334E73]">{t('Bank Account')}</th>
                                    <th className="text-right py-2 px-2.5 font-bold">{t('Amount')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {payments.length === 0 ? (
                                    <tr className="h-[30px]">
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5 border-r border-[#C5D3E0]">&nbsp;</td>
                                        <td className="py-2 px-2.5">&nbsp;</td>
                                    </tr>
                                ) : (
                                    payments.map((payment: any, idx: number) => (
                                        <tr key={idx} className="border-b border-[#C5D3E0] last:border-b-0">
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{payment.payment_number}</td>
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{formatDate(payment.date)}</td>
                                            <td className="py-2 px-2.5 border-r border-[#C5D3E0]">{payment.bank_account || '-'}</td>
                                            <td className="py-2 px-2.5 text-right font-semibold">{formatCurrency(payment.amount)}</td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="flex justify-end mb-5">
                    <table className="w-[360px] border-collapse text-[11px]">
                        <tbody>
                            <tr>
                                <td className="py-1.5 pl-3 pr-4 text-right text-slate-800">{t('Total Invoiced')}</td>
                                <td className="py-1.5 px-2.5 text-right font-medium border-b border-[#1B365D]/40 w-[145px]">{formatCurrency(data.summary?.total_invoiced || 0)}</td>
                            </tr>
                            <tr>
                                <td className="py-1.5 pl-3 pr-4 text-right text-slate-800">{t('Total Returns')}</td>
                                <td className="py-1.5 px-2.5 text-right font-medium border-b border-[#1B365D]/40 w-[145px]">{formatCurrency(data.summary?.total_returns || 0)}</td>
                            </tr>
                            <tr>
                                <td className="py-1.5 pl-3 pr-4 text-right text-slate-800">{t('Total Credit Notes')}</td>
                                <td className="py-1.5 px-2.5 text-right font-medium border-b border-[#1B365D]/40 w-[145px]">{formatCurrency(data.summary?.total_credit_notes || 0)}</td>
                            </tr>
                            <tr>
                                <td className="py-1.5 pl-3 pr-4 text-right text-slate-800">{t('Total Payments')}</td>
                                <td className="py-1.5 px-2.5 text-right font-medium border-b border-[#1B365D]/40 w-[145px]">{formatCurrency(data.summary?.total_payments || 0)}</td>
                            </tr>
                            <tr className="bg-[#EAF5F1] border-t-[1.5px] border-t-[#0E7C5B] border-b border-b-[#1B365D]">
                                <td className="py-1.5 pl-3 pr-4 text-right font-extrabold uppercase">{t('Balance')}</td>
                                <td className="py-1.5 px-2.5 text-right font-extrabold w-[145px]">{formatCurrency(data.summary?.balance || 0)}</td>
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
