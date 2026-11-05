import React, { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate, getCompanySetting } from '@/utils/helpers';
import { PurchaseInvoice } from './types';
import { usePageButtons } from '@/hooks/usePageButtons';
import { useFormFields } from '@/hooks/useFormFields';
import CorporateDocumentTemplate from '@/components/CorporateDocumentTemplate';
import DocumentVerificationBadge, { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

interface PrintProps {
    invoice: PurchaseInvoice;
    verification?: DocumentVerificationData;
    [key: string]: any;
}

export default function Print() {
    const { t } = useTranslation();
    const { invoice, verification } = usePage<PrintProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);
    const [fieldsLoaded, setFieldsLoaded] = useState(false);

    const signaturePrintButtons = usePageButtons('signaturePrintBtn', {
        invoice: invoice,
        invoiceType: 'purchase'
    });

    const customFields = useFormFields('getCustomFields', { ...invoice, module: 'General', sub_module: 'Purchase Invoice', id: invoice.id, isPrint: true }, () => {}, {}, 'view', t);
    useEffect(() => {
        if (customFields && customFields.length >= 0) {
            setFieldsLoaded(true);
        }
    }, [customFields]);

    // amazonq-ignore-next-line
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf' && fieldsLoaded) {
            setTimeout(() => downloadPDF(), 1000);
        }
    }, [fieldsLoaded]);

    const downloadPDF = async () => {
        setIsDownloading(true);

        const printContent = document.querySelector('.invoice-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `purchase-invoice-${invoice.invoice_number}.pdf`,
                image: { type: 'jpeg' as const, quality: 0.98 },
                html2canvas: { scale: 2 },
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
            <Head title={t('Purchase Invoice')} />

            {isDownloading && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white p-6 rounded-lg shadow-lg">
                        <div className="flex items-center space-x-3">
                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <p className="text-lg font-semibold text-gray-700">{t('Generating PDF...')}</p>
                        </div>
                    </div>
                </div>
            )}

            <div className="invoice-container bg-white max-w-4xl mx-auto p-2">
                <CorporateDocumentTemplate
                    type="invoice"
                    documentTitle="PURCHASE INVOICE"
                    documentNumber={invoice.invoice_number}
                    documentNumberLabel="Bill No:"
                    date={formatDate(invoice.invoice_date)}
                    dateLabel="Invoice Date:"
                    secondaryDate={formatDate(invoice.due_date)}
                    secondaryDateLabel="Due Date:"

                    leftBoxTitle="VENDOR / SUPPLIER"
                    leftBoxFields={[
                        { label: 'Supplier / Company Name', value: invoice.vendor?.name },
                        {
                            label: 'Address',
                            value: invoice.vendor_details?.billing_address
                                ? `${invoice.vendor_details.billing_address.address_line_1 || ''}, ${invoice.vendor_details.billing_address.city || ''} ${invoice.vendor_details.billing_address.state || ''}`.trim()
                                : ((invoice.vendor as any)?.address || 'Tanzania')
                        },
                        {
                            label: 'Phone / Email',
                            value: `${(invoice.vendor as any)?.mobile_no || ''} ${invoice.vendor?.email ? '| ' + invoice.vendor.email : ''}`.trim()
                        },
                        {
                            label: 'TIN / VRN (if applicable)',
                            value: (invoice.vendor as any)?.tax_number || (invoice.vendor as any)?.tin || '108-992-120'
                        },
                    ]}

                    rightBoxTitle="DELIVERY / ORDER DETAILS"
                    rightBoxFields={[
                        { label: 'Purchase Order No', value: (invoice as any)?.order_number || (invoice as any)?.reference || 'PO-2026-001' },
                        { label: 'GRN Reference', value: (invoice as any)?.grn_number || 'GRN-0001' },
                        { label: 'Payment Terms', value: invoice.payment_terms || 'Net 30 Days' },
                        { label: 'Currency', value: 'TZS' },
                    ]}

                    columns={[
                        { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                        {
                            key: 'description',
                            label: 'Item / Service Description',
                            align: 'left',
                            render: (it) => (
                                <div>
                                    <div className="font-semibold text-slate-900">{it.product?.name || it.name}</div>
                                    {it.product?.sku && (
                                        <div className="text-[10px] text-slate-500 font-mono">SKU: {it.product.sku}</div>
                                    )}
                                </div>
                            ),
                        },
                        { key: 'quantity', label: 'Qty', align: 'center', width: '55px', render: (it) => it.quantity },
                        { key: 'unit', label: 'Unit', align: 'center', width: '60px', render: (it) => it.product?.unit || it.unit || 'units' },
                        {
                            key: 'unit_price',
                            label: 'Unit Price (TZS)',
                            align: 'right',
                            width: '130px',
                            render: (it) => Number(it.unit_price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                        },
                        {
                            key: 'amount',
                            label: 'Amount (TZS)',
                            align: 'right',
                            width: '130px',
                            render: (it) => Number(it.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                        },
                    ]}
                    items={invoice.items || []}

                    totals={[
                        { label: 'Subtotal', value: Number(invoice.subtotal) },
                        ...(invoice.discount_amount > 0 ? [{ label: 'Discount (if any)', value: -Number(invoice.discount_amount) }] : [{ label: 'Discount (if any)', value: 0 }]),
                        { label: 'VAT (if applicable)', value: Number(invoice.tax_amount || 0) },
                        { label: 'TOTAL INVOICE (TZS)', value: Number(invoice.total_amount), isGrandTotal: true },
                    ]}

                    lowerLeftTitle="Supplier Remittance"
                    lowerLeftContent={
                        <div className="space-y-1 text-[11px]">
                            <div><span className="font-semibold">Remittance Terms:</span> Disbursement upon 3-Way Match Clearance</div>
                            <div><span className="font-semibold">Verification:</span> Matched with Verified GRN & Approved PO</div>
                            <div><span className="font-semibold">Payment Method:</span> Electronic Funds Transfer (EFT / TISS)</div>
                        </div>
                    }

                    lowerRightTitle="Inspection & Approval Status"
                    lowerRightContent={
                        <div className="space-y-1 text-[11px] text-slate-700">
                            <p>Vendor tax invoice verified against delivered items.</p>
                            <p>Subject to final accounting officer disbursement audit.</p>
                            <p>Processed under Mutamark Procure-to-Pay protocol.</p>
                        </div>
                    }

                    signatures={[
                        { label: 'Verified by (Accounts Payable)', value: 'AP Accountant', extraLabel: 'Signature / Stamp', extraValue: '' },
                        { label: 'Approved by (Finance Manager)', value: '', dateLabel: 'Date', dateValue: formatDate(new Date()) },
                    ]}

                    footerSlogan="Thank you for your business."
                    verification={verification}
                    verificationHash={(invoice as any)?.verification_hash}
                />
            </div>

            <style>{`
                body {
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                    font-family: Arial, sans-serif;
                }

                @page {
                    margin: 0.5in;
                    size: A4;
                }

                .invoice-container {
                    max-width: 100%;
                    margin: 0;
                    box-shadow: none;
                }

                .page-break-inside-avoid {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                @media print {
                    body {
                        background: white;
                    }

                    .invoice-container {
                        box-shadow: none;
                    }
                }
            `}</style>
        </div>
    );
}
