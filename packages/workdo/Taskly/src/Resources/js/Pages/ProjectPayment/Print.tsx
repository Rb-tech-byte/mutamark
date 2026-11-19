import React, { useEffect, useState, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate } from '@/utils/helpers';
import CorporateDocumentTemplate from '@/components/CorporateDocumentTemplate';
import type { ProjectPayment } from './types';

interface PrintProps {
    payment: ProjectPayment;
    [key: string]: any;
}

export default function Print() {
    const { t } = useTranslation();
    const { payment } = usePage<PrintProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);
    const downloadInitiatedRef = useRef(false);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf' && !downloadInitiatedRef.current) {
            downloadInitiatedRef.current = true;
            setTimeout(() => downloadPDF(), 500);
        }
    }, []);

    const downloadPDF = async () => {
        if (isDownloading) return;
        setIsDownloading(true);

        const printContent = document.querySelector('.payment-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `project-payment-${payment.payment_number}.pdf`,
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

    return (
        <div className="min-h-screen bg-white">
            <Head title={t('Project Payment')} />

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

            <div className="print:hidden max-w-[850px] mx-auto px-4 pt-4 flex justify-end gap-2">
                <button type="button" onClick={() => window.print()} className="rounded bg-[#1B365D] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90">
                    {t('Print')}
                </button>
                <button type="button" onClick={downloadPDF} className="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    {t('Download PDF')}
                </button>
            </div>

            <div className="payment-container bg-white max-w-4xl mx-auto p-2">
                <CorporateDocumentTemplate
                    type="payment_voucher"
                    documentTitle={t('PROJECT PAYMENT')}
                    documentNumber={payment.payment_number}
                    documentNumberLabel={t('Payment No')}
                    date={formatDate(payment.payment_date)}
                    dateLabel={t('Date')}
                    secondaryDate={formatDate(payment.due_date)}
                    secondaryDateLabel={t('Due Date')}
                    leftBoxTitle={t('BILL TO')}
                    leftBoxFields={[
                        { label: t('Customer / Company Name'), value: payment.customer?.name || '—' },
                        { label: t('Email'), value: payment.customer?.email || '—' },
                    ]}
                    rightBoxTitle={t('PROJECT DETAILS')}
                    rightBoxFields={[
                        { label: t('Project'), value: payment.project?.name || '—' },
                        { label: t('Status'), value: payment.status ? payment.status.toUpperCase() : '—' },
                    ]}
                    columns={[
                        { key: 'index', label: '#', align: 'center', width: '34px', render: (_, idx) => idx + 1 },
                        { key: 'milestone', label: t('Milestone / Description'), align: 'left', render: (item) => item.milestone?.title || '—' },
                        { key: 'price', label: t('Price'), align: 'right', width: '120px', render: (item) => formatCurrency(item.price || 0) },
                        { key: 'discount', label: t('Discount'), align: 'right', width: '110px', render: (item) => item.discount_amount > 0 ? `-${formatCurrency(item.discount_amount)}` : '0%' },
                        { key: 'total', label: t('Total'), align: 'right', width: '125px', render: (item) => formatCurrency(item.total_amount || 0) },
                    ]}
                    items={payment.items || []}
                    totals={[
                        { label: t('Subtotal'), value: formatCurrency(payment.subtotal || 0) },
                        ...(payment.discount_amount > 0 ? [{ label: t('Discount (if any)'), value: `-${formatCurrency(payment.discount_amount)}` }] : []),
                        { label: t('TOTAL'), value: formatCurrency(payment.total_amount || 0), isGrandTotal: true },
                    ]}
                    lowerLeftTitle={payment.payment_terms ? t('PAYMENT TERMS') : undefined}
                    lowerLeftContent={payment.payment_terms ? <p className="whitespace-pre-line">{payment.payment_terms}</p> : undefined}
                    lowerRightTitle={payment.notes ? t('NOTES') : undefined}
                    lowerRightContent={payment.notes ? <p className="whitespace-pre-line">{payment.notes}</p> : undefined}
                    signatures={[
                        { label: t('Prepared By'), dateLabel: t('Date'), dateValue: formatDate(payment.payment_date) },
                        { label: t('Authorised By'), dateLabel: t('Date') },
                    ]}
                    footerSlogan={t('Thank you for your business.')}
                />
            </div>
        </div>
    );
}
