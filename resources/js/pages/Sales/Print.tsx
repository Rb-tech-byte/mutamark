import React, { useEffect, useState, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate, getCompanySetting } from '@/utils/helpers';
import { SalesInvoice } from './types';
import { usePageButtons } from '@/hooks/usePageButtons';
import { useFormFields } from '@/hooks/useFormFields';
import CorporateDocumentTemplate, { TableColumn, TotalsRow, SignatureSlot } from '@/components/CorporateDocumentTemplate';
import DocumentVerificationBadge, { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

interface PrintProps {
    invoice: SalesInvoice;
    verification?: DocumentVerificationData;
    [key: string]: any;
}

export default function Print() {
    const { t } = useTranslation();
    const { invoice, verification } = usePage<PrintProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);
    const [fieldsLoaded, setFieldsLoaded] = useState(false);
    const downloadInitiatedRef = useRef(false);

    const signaturePrintButtons = usePageButtons('signaturePrintBtn', {
        invoice: invoice,
        invoiceType: 'sales'
    });

    // Custom fields view mode hook
    const customFields = useFormFields('getCustomFields', { ...invoice, module: 'General', sub_module: 'Sales Invoice', id: invoice.id, isPrint: true }, () => { }, {}, 'view', t);
    const pageButtons = usePageButtons('zatcaQRCodeBtn', invoice);

    useEffect(() => {
        // Set fields loaded when custom fields are available
        if (customFields && customFields.length >= 0) {
            setFieldsLoaded(true);
        }
    }, [customFields]);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf' && fieldsLoaded && !downloadInitiatedRef.current) {
            downloadInitiatedRef.current = true;
            let attempts = 0;
            const maxAttempts = 60;
            let initialPageButtonsLength = pageButtons.length;

            const checkQRCodeOrDownload = () => {
                const qrContainer = document.getElementById('zatca-qr-container');
                const qrImg = document.querySelector('[data-zatca-qr] img[src*="data:image"]') as HTMLImageElement;
                const loadingDiv = document.querySelector('[data-zatca-qr] .animate-spin');
                const currentPageButtonsLength = pageButtons.length;

                // Check if ZATCA is enabled (pageButtons populated at any point)
                const isZatcaEnabled = currentPageButtonsLength > 0 || initialPageButtonsLength > 0 || qrContainer !== null;

                // Case 1: Valid QR code loaded and ready
                if (qrImg && qrImg.complete && qrImg.naturalWidth > 0) {
                    if (qrContainer) qrContainer.classList.remove('hidden');
                    setTimeout(() => downloadPDF(), 1000);
                    return;
                }

                // Case 2: ZATCA disabled (no page buttons and no container)
                if (!isZatcaEnabled && attempts > 5) {
                    setTimeout(() => downloadPDF(), 300);
                    return;
                }

                // Case 3: QR container exists but empty after sufficient attempts (validation failed)
                if (isZatcaEnabled && qrContainer && !qrImg && !loadingDiv && attempts > 15) {
                    if (qrContainer) qrContainer.classList.add('hidden');
                    setTimeout(() => downloadPDF(), 300);
                    return;
                }

                // Case 4: Still loading - continue waiting
                if (loadingDiv && attempts <= 40) {
                    attempts++;
                    setTimeout(checkQRCodeOrDownload, 300);
                    return;
                }

                // Case 5: Max attempts reached - proceed anyway
                if (attempts >= maxAttempts) {
                    if (qrContainer && !qrImg) {
                        qrContainer.classList.add('hidden');
                    }
                    setTimeout(() => downloadPDF(), 300);
                    return;
                }

                // Continue checking
                attempts++;
                setTimeout(checkQRCodeOrDownload, 250);
            };

            setTimeout(checkQRCodeOrDownload, 500);
        }
    }, [fieldsLoaded]);

    const downloadPDF = async () => {
        if (isDownloading) return;
        setIsDownloading(true);

        const printContent = document.querySelector('.invoice-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `sales-invoice-${invoice.invoice_number}.pdf`,
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
            <Head title={t('Sales Invoice')} />

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
                    documentTitle="INVOICE"
                    documentNumber={invoice.invoice_number}
                    documentNumberLabel="Invoice No:"
                    date={formatDate(invoice.invoice_date)}
                    dateLabel="Invoice Date:"
                    secondaryDate={formatDate(invoice.due_date)}
                    secondaryDateLabel="Due Date:"

                    leftBoxTitle="BILL TO"
                    leftBoxFields={[
                        { label: 'Customer / Company Name', value: invoice.customer?.name },
                        {
                            label: 'Address',
                            value: invoice.customer_details?.billing_address
                                ? `${invoice.customer_details.billing_address.address_line_1 || ''}, ${invoice.customer_details.billing_address.city || ''} ${invoice.customer_details.billing_address.state || ''}`.trim()
                                : ((invoice.customer as any)?.address || 'Dar es Salaam, Tanzania')
                        },
                        {
                            label: 'Phone / Email',
                            value: `${(invoice.customer as any)?.mobile_no || ''} ${invoice.customer?.email ? '| ' + invoice.customer.email : ''}`.trim()
                        },
                        {
                            label: 'TIN / VRN (if applicable)',
                            value: (invoice.customer as any)?.tax_number || (invoice.customer as any)?.tin || '119-823-455'
                        },
                    ]}

                    rightBoxTitle="DELIVERY / REFERENCE DETAILS"
                    rightBoxFields={[
                        { label: 'Customer PO No', value: (invoice as any)?.order_number || (invoice as any)?.reference || 'PO-2026-REF' },
                        { label: 'Delivery Note No', value: (invoice as any)?.delivery_number || (invoice as any)?.dn_number || 'DN-0001' },
                        { label: 'Payment Terms', value: invoice.payment_terms || '30 days' },
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
                        { label: 'TOTAL DUE (TZS)', value: Number(invoice.total_amount), isGrandTotal: true },
                    ]}

                    lowerLeftTitle="Payment Details"
                    lowerLeftContent={
                        <div className="space-y-1 text-[11px]">
                            <div><span className="font-semibold">Bank:</span> CRDB Bank Plc / NMB Bank Plc</div>
                            <div><span className="font-semibold">Account Name:</span> {getCompanySetting('company_name') || 'MUTAMARK ENTERPRISES LTD'}</div>
                            <div><span className="font-semibold">Account No:</span> 0150-2938-4100 (TZS) / 0250-2938-4101 (USD)</div>
                            <div><span className="font-semibold">Mobile Money:</span> Lipa Namba: 5492810 (MUTAMARK) — M-Pesa / Tigo Pesa / Airtel Money</div>
                        </div>
                    }

                    lowerRightTitle="Notes / Terms"
                    lowerRightContent={
                        <div className="space-y-1 text-[11px] text-slate-700">
                            <p>Payment due within 30 days unless agreed otherwise.</p>
                            <p>Please quote the invoice number when paying.</p>
                            <p>Goods/services supplied as described above.</p>
                        </div>
                    }

                    signatures={[
                        { label: 'Prepared by', value: (invoice as any)?.creator?.name || 'Accounts Officer', extraLabel: 'Name', extraValue: '' },
                        { label: 'Authorised signature', value: '', dateLabel: 'Date', dateValue: formatDate(new Date()) },
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
