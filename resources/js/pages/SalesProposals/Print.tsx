import React, { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import html2pdf from 'html2pdf.js';
import { formatCurrency, formatDate, getCompanySetting } from '@/utils/helpers';
import { useFormFields } from '@/hooks/useFormFields';
import CorporateDocumentTemplate from '@/components/CorporateDocumentTemplate';

interface SalesProposal {
    id: number;
    proposal_number: string;
    proposal_date: string;
    due_date: string;
    customer: { id: number; name: string; email: string };
    subtotal: number;
    tax_amount: number;
    discount_amount: number;
    total_amount: number;
    status: string;
    payment_terms?: string;
    notes?: string;
    warehouse?: { id: number; name: string };
    items?: Array<{
        id: number;
        product_id: number;
        quantity: number;
        unit_price: number;
        discount_percentage: number;
        discount_amount: number;
        tax_percentage: number;
        tax_amount: number;
        total_amount: number;
        product?: {
            id: number;
            name: string;
            sku?: string;
        };
        taxes?: Array<{
            id: number;
            tax_name: string;
            tax_rate: number;
        }>;
    }>;
}

import DocumentVerificationBadge, { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

interface PrintProps {
    proposal: SalesProposal;
    verification?: DocumentVerificationData;
    [key: string]: any;
}

export default function Print() {
    const { t } = useTranslation();
    const { proposal, verification, documentTitle = 'Sales Proposal' } = usePage<PrintProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);
    const [fieldsLoaded, setFieldsLoaded] = useState(false);

    const customFields = useFormFields('getCustomFields', { ...proposal, module: 'General', sub_module: 'Proposal', id: proposal.id, isPrint: true }, () => {}, {}, 'view', t);

    useEffect(() => {
        if (customFields && customFields.length >= 0) {
            setFieldsLoaded(true);
        }
    }, [customFields]);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf' && fieldsLoaded) {
            setTimeout(() => downloadPDF(), 1000);
        }
    }, [fieldsLoaded]);

    const downloadPDF = async () => {
        setIsDownloading(true);

        const printContent = document.querySelector('.proposal-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `${documentTitle}-${proposal.proposal_number}.pdf`,
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
            <Head title={t(documentTitle)} />

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

            <div className="proposal-container bg-white max-w-4xl mx-auto p-2">
                <CorporateDocumentTemplate
                    type="invoice"
                    documentTitle={t(documentTitle).toUpperCase()}
                    documentNumber={proposal.proposal_number}
                    documentNumberLabel="Ref No:"
                    date={formatDate(proposal.proposal_date)}
                    dateLabel="Date:"
                    secondaryDate={formatDate(proposal.due_date)}
                    secondaryDateLabel="Valid Until:"

                    leftBoxTitle="PROPOSAL / QUOTATION TO"
                    leftBoxFields={[
                        { label: 'Customer / Company Name', value: proposal.customer?.name },
                        { label: 'Address', value: 'Dar es Salaam, Tanzania' },
                        { label: 'Phone / Email', value: proposal.customer?.email || 'customer@client.co.tz' },
                        { label: 'TIN / VRN (if applicable)', value: '119-823-455' },
                    ]}

                    rightBoxTitle="DELIVERY & LOGISTICS"
                    rightBoxFields={[
                        { label: 'Delivery Warehouse', value: proposal.warehouse?.name || 'Main Warehouse' },
                        { label: 'Payment Terms', value: proposal.payment_terms || '30 days Net' },
                        { label: 'Quotation Validity', value: '30 Calendar Days' },
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
                    items={proposal.items || []}
                    minRows={8}

                    totals={[
                        { label: 'Subtotal', value: Number(proposal.subtotal) },
                        ...(proposal.discount_amount > 0 ? [{ label: 'Discount (if any)', value: -Number(proposal.discount_amount) }] : [{ label: 'Discount (if any)', value: 0 }]),
                        { label: 'VAT (if applicable)', value: Number(proposal.tax_amount || 0) },
                        { label: 'TOTAL ESTIMATED (TZS)', value: Number(proposal.total_amount), isGrandTotal: true },
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

                    lowerRightTitle="Notes / Commercial Terms"
                    lowerRightContent={
                        <div className="space-y-1 text-[11px] text-slate-700">
                            <p>Prices are firm and valid for 30 calendar days from issue date.</p>
                            <p>Delivery within Dar es Salaam: 2-3 business days upon confirmation.</p>
                            <p>Payment terms strictly as stated above upon invoice presentation.</p>
                        </div>
                    }

                    signatures={[
                        { label: 'Prepared by', value: 'Commercial Sales Specialist', extraLabel: 'Name', extraValue: '' },
                        { label: 'Authorized signature', value: '', dateLabel: 'Date', dateValue: formatDate(new Date()) },
                    ]}

                    footerSlogan="Thank you for your business."
                    verification={verification}
                    verificationHash={(proposal as any)?.verification_hash}
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

                .proposal-container {
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

                    .proposal-container {
                        box-shadow: none;
                    }
                }
            `}</style>
        </div>
    );
}
