import React, { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate } from '@/utils/helpers';
import CorporateDocumentTemplate from '@/components/CorporateDocumentTemplate';

interface PosItem {
    id: number;
    product_id: number;
    quantity: number;
    price: number;
    subtotal: number;
    item_discount_amount: number;
    tax_amount: number;
    total_amount: number;
    product: {
        id: number;
        name: string;
        sku?: string;
    };
    taxes?: Array<{
        id: number;
        tax_name: string;
        rate: number;
    }>;
}

interface PosSale {
    id: number;
    sale_number: string;
    customer?: {
        name: string;
        email?: string;
    };
    warehouse?: {
        name: string;
    };
    subtotal: number;
    discount_amount: number;
    tax_amount?: number;
    total_amount: number;
    created_at: string;
    items: PosItem[];
}

interface PrintProps {
    sale: PosSale;
    [key: string]: any;
}

export default function Print() {
    const { t } = useTranslation();
    const { sale } = usePage<PrintProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf') {
            downloadPDF();
        }
    }, []);

    const downloadPDF = async () => {
        setIsDownloading(true);
        const printContent = document.querySelector('.sale-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `pos-sale-${sale.sale_number}.pdf`,
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
            <Head title={t('POS Sale')} />

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

            <div className="sale-container bg-white max-w-4xl mx-auto p-2">
                <CorporateDocumentTemplate
                    type="invoice"
                    documentTitle={t('POS SALE RECEIPT / INVOICE')}
                    documentNumber={sale.sale_number}
                    documentNumberLabel={t('Sale No')}
                    date={formatDate(sale.created_at)}
                    dateLabel={t('Date')}
                    leftBoxTitle={t('CUSTOMER')}
                    leftBoxFields={[
                        { label: t('Customer / Company Name'), value: sale.customer?.name || t('Walk-in Customer') },
                        { label: t('Email'), value: sale.customer?.email || '—' },
                    ]}
                    rightBoxTitle={t('SALE DETAILS')}
                    rightBoxFields={[
                        { label: t('Warehouse'), value: sale.warehouse?.name || '—' },
                        { label: t('Sale Ref'), value: sale.sale_number },
                    ]}
                    columns={[
                        { key: 'index', label: '#', align: 'center', width: '34px', render: (_, idx) => idx + 1 },
                        {
                            key: 'product',
                            label: t('Item / Service Description'),
                            align: 'left',
                            render: (item: PosItem) => (
                                <div>
                                    <div className="font-semibold text-slate-900">{item.product?.name}</div>
                                    {item.product?.sku && (
                                        <div className="text-[10px] text-slate-500 font-mono">{t('SKU')}: {item.product.sku}</div>
                                    )}
                                </div>
                            ),
                        },
                        { key: 'quantity', label: t('Qty'), align: 'center', width: '50px', render: (item: PosItem) => item.quantity },
                        { key: 'price', label: t('Unit Price'), align: 'right', width: '105px', render: (item: PosItem) => formatCurrency(item.price) },
                        { key: 'discount', label: t('Discount'), align: 'right', width: '90px', render: (item: PosItem) => item.item_discount_amount > 0 ? `-${formatCurrency(item.item_discount_amount)}` : '—' },
                        { key: 'tax', label: t('Tax'), align: 'right', width: '90px', render: (item: PosItem) => formatCurrency(item.tax_amount || 0) },
                        { key: 'total', label: t('Amount'), align: 'right', width: '110px', render: (item: PosItem) => formatCurrency(item.total_amount) },
                    ]}
                    items={sale.items || []}
                    totals={[
                        { label: t('Subtotal'), value: formatCurrency(sale.subtotal || 0) },
                        { label: t('Discount (if any)'), value: sale.discount_amount > 0 ? `-${formatCurrency(sale.discount_amount)}` : formatCurrency(0) },
                        { label: t('VAT (if applicable)'), value: formatCurrency(sale.tax_amount || 0) },
                        { label: t('TOTAL PAID'), value: formatCurrency(sale.total_amount || 0), isGrandTotal: true },
                    ]}
                    signatures={[
                        { label: t('Prepared By'), dateLabel: t('Date'), dateValue: formatDate(sale.created_at) },
                        { label: t('Customer Signature'), dateLabel: t('Date') },
                    ]}
                    footerSlogan={t('Thank you for your business.')}
                />
            </div>
        </div>
    );
}
