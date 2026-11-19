import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { CorporateDocumentHeader } from '@/components/CorporateDocumentTemplate';

export default function PrintIncomeStatement() {
    const { t } = useTranslation();
    const { data } = usePage<any>().props;

    const revenue = Array.isArray(data?.revenue) ? data.revenue : [];
    const expenses = Array.isArray(data?.expenses) ? data.expenses : [];

    return (
        <div className="min-h-screen bg-white text-slate-900">
            <Head title={t('Income Statement')} />

            <div className="print:hidden max-w-4xl mx-auto px-8 pt-4 flex justify-end gap-2">
                <button type="button" onClick={() => window.print()} className="rounded bg-[#1B365D] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90">
                    {t('Print')}
                </button>
            </div>

            <div className="report-container bg-white max-w-4xl mx-auto p-8 print:p-4" style={{ fontFamily: 'Arial, Helvetica, Inter, system-ui, sans-serif' }}>
                <CorporateDocumentHeader
                    title={t('INCOME STATEMENT')}
                    metaItems={[
                        { label: t('Period'), value: `${formatDate(data.from_date)} ${t('to')} ${formatDate(data.to_date)}` },
                        { label: t('Generated On'), value: formatDate(new Date().toISOString()) },
                    ]}
                />

                <div className="border border-[#9EB3C8] overflow-hidden">
                    <table className="w-full border-collapse text-[11px]">
                        <thead>
                            <tr className="bg-[#1B365D] text-white">
                                <th className="text-left py-2 px-3 font-bold border-r border-[#334E73]">{t('Account / Category')}</th>
                                <th className="text-right py-2 px-3 font-bold w-44">{t('Amount')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr className="bg-[#EBF1F6] border-b border-[#9EB3C8]">
                                <td colSpan={2} className="px-3 py-1.5 font-extrabold text-[10.5px] uppercase tracking-wide text-[#1B365D]">
                                    {t('Revenue')}
                                </td>
                            </tr>
                            {revenue.length === 0 ? (
                                <tr className="h-[30px] border-b border-[#C5D3E0]">
                                    <td className="px-6 py-2 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="px-3 py-2 text-right">&nbsp;</td>
                                </tr>
                            ) : (
                                revenue.map((item: any, idx: number) => (
                                    <tr key={idx} className="border-b border-[#C5D3E0]">
                                        <td className="px-6 py-2 border-r border-[#C5D3E0]">{item.category}</td>
                                        <td className="px-3 py-2 text-right">{formatCurrency(item.amount)}</td>
                                    </tr>
                                ))
                            )}
                            <tr className="font-bold bg-slate-50 border-b border-[#9EB3C8]">
                                <td className="px-3 py-2 border-r border-[#C5D3E0]">{t('Total Revenue')}</td>
                                <td className="px-3 py-2 text-right">{formatCurrency(data.total_revenue || 0)}</td>
                            </tr>

                            <tr className="bg-[#EBF1F6] border-b border-[#9EB3C8]">
                                <td colSpan={2} className="px-3 py-1.5 font-extrabold text-[10.5px] uppercase tracking-wide text-[#1B365D]">
                                    {t('Expenses')}
                                </td>
                            </tr>
                            {expenses.length === 0 ? (
                                <tr className="h-[30px] border-b border-[#C5D3E0]">
                                    <td className="px-6 py-2 border-r border-[#C5D3E0]">&nbsp;</td>
                                    <td className="px-3 py-2 text-right">&nbsp;</td>
                                </tr>
                            ) : (
                                expenses.map((item: any, idx: number) => (
                                    <tr key={idx} className="border-b border-[#C5D3E0]">
                                        <td className="px-6 py-2 border-r border-[#C5D3E0]">{item.category}</td>
                                        <td className="px-3 py-2 text-right">{formatCurrency(item.amount)}</td>
                                    </tr>
                                ))
                            )}
                            <tr className="font-bold bg-slate-50 border-b border-[#9EB3C8]">
                                <td className="px-3 py-2 border-r border-[#C5D3E0]">{t('Total Expenses')}</td>
                                <td className="px-3 py-2 text-right">{formatCurrency(data.total_expenses || 0)}</td>
                            </tr>

                            <tr className="font-extrabold bg-[#EAF5F1] border-t-[1.5px] border-t-[#0E7C5B] text-[12px]">
                                <td className="px-3 py-2.5 uppercase border-r border-[#C5D3E0]">{t('Net Income')}</td>
                                <td className="px-3 py-2.5 text-right">{formatCurrency(data.net_income || 0)}</td>
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
