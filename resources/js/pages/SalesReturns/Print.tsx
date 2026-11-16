import React, { useEffect, useRef, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { SalesReturn } from './types';
import CorporateDocumentTemplate, { TableColumn, TotalsRow, SignatureSlot } from '@/components/CorporateDocumentTemplate';
import { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

interface PrintProps {
    return: SalesReturn & {
        original_invoice?: { invoice_number?: string };
    };
    verification?: DocumentVerificationData;
    [key: string]: any;
}

export default function Print() {
    const { t } = useTranslation();
    const { return: salesReturn, verification } = usePage<PrintProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);
    const downloadInitiatedRef = useRef(false);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf' && !downloadInitiatedRef.current) {
            downloadInitiatedRef.current = true;
            setTimeout(() => {
                downloadPDF();
            }, 400);
        }
    }, []);

    const downloadPDF = async () => {
        if (isDownloading) return;
        setIsDownloading(true);
        try {
            const element = document.getElementById('corporate-document-root');
            if (element) {
                await html2pdf()
                    .set({
                        margin: 0.4,
                        filename: `${salesReturn.return_number || 'sales-return'}.pdf`,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true, letterRendering: true },
                        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                    })
                    .from(element)
                    .save();
            }
        } finally {
            setIsDownloading(false);
            if (new URLSearchParams(window.location.search).get('download') === 'pdf') {
                setTimeout(() => window.close(), 500);
            }
        }
    };

    const columns: TableColumn[] = [
        {
            key: 'index',
            label: '#',
            align: 'center',
            width: '40px',
            render: (_item, idx) => idx + 1,
        },
        {
            key: 'product',
            label: t('Product / Description'),
            render: (item) => (
                <div>
                    <div className="font-medium text-slate-900">{item.product?.name || '-'}</div>
                    {item.reason && <div className="text-[11px] text-slate-500">{t('Reason')}: {item.reason}</div>}
                </div>
            ),
        },
        {
            key: 'quantity',
            label: t('Return Qty'),
            align: 'right',
            render: (item) => item.return_quantity ?? item.quantity,
        },
        {
            key: 'unit_price',
            label: t('Unit Price'),
            align: 'right',
            render: (item) => formatCurrency(Number(item.unit_price || 0)),
        },
        {
            key: 'tax_amount',
            label: t('Tax'),
            align: 'right',
            render: (item) => formatCurrency(Number(item.tax_amount || 0)),
        },
        {
            key: 'total_amount',
            label: t('Total'),
            align: 'right',
            render: (item) => formatCurrency(Number(item.total_amount || 0)),
        },
    ];

    const totals: TotalsRow[] = [
        { label: t('Subtotal'), value: formatCurrency(Number(salesReturn.subtotal || 0)) },
        { label: t('Discount'), value: formatCurrency(Number(salesReturn.discount_amount || 0)) },
        { label: t('Tax'), value: formatCurrency(Number(salesReturn.tax_amount || 0)) },
        { label: t('Total Return Amount'), value: formatCurrency(Number(salesReturn.total_amount || 0)), isGrandTotal: true },
    ];

    const signatures: SignatureSlot[] = [
        {
            label: t('Prepared / Authorized By'),
            dateLabel: t('Date'),
            dateValue: formatDate(salesReturn.return_date),
        },
        {
            label: t('Customer Acknowledgement'),
        },
    ];

    return (
        <div className="min-h-screen bg-slate-100 p-4 print:bg-white print:p-0">
            <Head title={`${t('Sales Return')} - ${salesReturn.return_number}`} />
            <div id="corporate-document-root" className="mx-auto max-w-4xl">
                <CorporateDocumentTemplate
                    type="invoice"
                    documentTitle={t('SALES RETURN / CREDIT ADVICE')}
                    documentNumber={salesReturn.return_number}
                    documentNumberLabel={t('Return #')}
                    date={formatDate(salesReturn.return_date)}
                    dateLabel={t('Return Date')}
                    tertiaryNumber={salesReturn.original_invoice?.invoice_number}
                    tertiaryNumberLabel={t('Original Invoice #')}
                    leftBoxTitle={t('Customer Details')}
                    leftBoxFields={[
                        { label: t('Name'), value: salesReturn.customer_details?.company_name || salesReturn.customer?.name || '-' },
                        { label: t('Email'), value: salesReturn.customer?.email || '-' },
                        { label: t('Tax / TIN'), value: salesReturn.customer_details?.tax_number || '-' },
                    ]}
                    rightBoxTitle={t('Return Summary')}
                    rightBoxFields={[
                        { label: t('Status'), value: String(salesReturn.status || 'draft').toUpperCase() },
                        { label: t('Warehouse'), value: salesReturn.warehouse?.name || '-' },
                    ]}
                    columns={columns}
                    items={salesReturn.items || []}
                    totals={totals}
                    lowerLeftTitle={t('Notes')}
                    lowerLeftContent={<p className="text-xs text-slate-600">{salesReturn.notes || t('No additional notes.')}</p>}
                    signatures={signatures}
                    verification={verification}
                />
            </div>
        </div>
    );
}

