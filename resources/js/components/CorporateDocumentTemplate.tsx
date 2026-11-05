import React from 'react';
import { getCompanySetting } from '@/utils/helpers';
import DocumentVerificationBadge, { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

export type DocumentKind =
    | 'purchase_requisition'
    | 'rfq'
    | 'purchase_order'
    | 'delivery_note'
    | 'goods_received_note'
    | 'invoice';

export interface MetaField {
    label: string;
    value: React.ReactNode;
}

export interface TableColumn {
    key: string;
    label: string;
    align?: 'left' | 'center' | 'right';
    width?: string;
    render?: (item: any, index: number) => React.ReactNode;
}

export interface TotalsRow {
    label: string;
    value: string | number;
    isGrandTotal?: boolean;
}

export interface SignatureSlot {
    label: string;
    value?: string | null;
    dateLabel?: string;
    dateValue?: string | null;
    extraLabel?: string;
    extraValue?: string | null;
}

export interface CorporateDocumentTemplateProps {
    type: DocumentKind;
    documentTitle?: string;
    documentNumber?: string;
    documentNumberLabel?: string;
    date?: string;
    dateLabel?: string;
    secondaryDate?: string;
    secondaryDateLabel?: string;
    tertiaryNumber?: string;
    tertiaryNumberLabel?: string;

    // Company Header Override (defaults to system company settings)
    companyName?: string;
    companyAddress?: string;
    companyCity?: string;
    companyPhone?: string;
    companyEmail?: string;
    companyTin?: string;
    companyVrn?: string;

    // Section 1: Two Column Information Grid
    leftBoxTitle: string;
    leftBoxFields: MetaField[];
    rightBoxTitle: string;
    rightBoxFields: MetaField[];

    // Section 2: Items Table
    columns: TableColumn[];
    items: any[];
    minRows?: number;

    // Section 3: Totals Block (optional, for financial/valued documents)
    totals?: TotalsRow[];
    currency?: string;

    // Section 4: Lower Two-Column Information Box
    lowerLeftTitle?: string;
    lowerLeftContent?: React.ReactNode;
    lowerRightTitle?: string;
    lowerRightContent?: React.ReactNode;

    // Section 5: Signature Row
    signatures?: SignatureSlot[];

    // Section 6: Footer Slogan
    footerSlogan?: string;

    // Digital Verification
    verification?: DocumentVerificationData;
    verificationHash?: string;

    className?: string;
}

export default function CorporateDocumentTemplate({
    type,
    documentTitle,
    documentNumber = 'DOC-0001',
    documentNumberLabel,
    date = 'DD/MM/YYYY',
    dateLabel,
    secondaryDate,
    secondaryDateLabel,
    tertiaryNumber,
    tertiaryNumberLabel,

    companyName,
    companyAddress,
    companyCity,
    companyPhone,
    companyEmail,
    companyTin,
    companyVrn,

    leftBoxTitle,
    leftBoxFields,
    rightBoxTitle,
    rightBoxFields,

    columns,
    items = [],
    minRows = 8,

    totals,
    currency = 'TZS',

    lowerLeftTitle,
    lowerLeftContent,
    lowerRightTitle,
    lowerRightContent,

    signatures,
    footerSlogan,

    verification,
    verificationHash,

    className = '',
}: CorporateDocumentTemplateProps) {
    // Resolve Company details from props or system settings
    const displayCompanyName =
        companyName ||
        getCompanySetting('company_name') ||
        'MUTAMARK ENTERPRISES LIMITED';

    const displayAddress =
        companyAddress ||
        getCompanySetting('company_address') ||
        'Plot 42, Nyerere Road Industrial Area, P.O. Box 7192';

    const displayCity =
        companyCity ||
        getCompanySetting('company_city') ||
        'Dar es Salaam, Tanzania';

    const displayPhone =
        companyPhone ||
        getCompanySetting('company_telephone') ||
        '+255 22 286 1900 / +255 754 000 111';

    const displayEmail =
        companyEmail ||
        getCompanySetting('company_email') ||
        'accounts@mutamark.co.tz';

    const displayTin =
        companyTin ||
        getCompanySetting('registration_number') ||
        '104-892-411';

    const displayVrn =
        companyVrn ||
        getCompanySetting('tax_number') ||
        '40019283-Z';

    // Defaults per document type matching the PDF templates exactly
    const typeConfig: Record<
        DocumentKind,
        {
            title: string;
            numLabel: string;
            dateLabel: string;
            secDateLabel?: string;
            tertiaryLabel?: string;
            slogan: string;
            defaultMinRows: number;
        }
    > = {
        purchase_requisition: {
            title: 'PURCHASE REQUISITION',
            numLabel: 'PR No:',
            dateLabel: 'Date:',
            secDateLabel: 'Date Required:',
            slogan: 'Please attach supporting documents to this requisition.',
            defaultMinRows: 8,
        },
        rfq: {
            title: 'REQUEST FOR QUOTATION',
            numLabel: 'RFQ No:',
            dateLabel: 'Issue Date:',
            secDateLabel: 'Closing Date:',
            slogan: 'Thank you for your quotation.',
            defaultMinRows: 8,
        },
        purchase_order: {
            title: 'PURCHASE ORDER',
            numLabel: 'PO No:',
            dateLabel: 'Order Date:',
            secDateLabel: 'Delivery Date:',
            slogan: 'Please acknowledge receipt of this order.',
            defaultMinRows: 8,
        },
        delivery_note: {
            title: 'DELIVERY NOTE',
            numLabel: 'DN No:',
            dateLabel: 'Delivery Date:',
            secDateLabel: undefined,
            tertiaryLabel: 'PO / Order No:',
            slogan: 'Thank you for your business.',
            defaultMinRows: 11,
        },
        goods_received_note: {
            title: 'GOODS RECEIVED NOTE',
            numLabel: 'GRN No:',
            dateLabel: 'Date Received:',
            secDateLabel: undefined,
            tertiaryLabel: 'PO No:',
            slogan: 'Distribution: Accounts / Store / Procurement',
            defaultMinRows: 11,
        },
        invoice: {
            title: 'INVOICE',
            numLabel: 'Invoice No:',
            dateLabel: 'Invoice Date:',
            secDateLabel: 'Due Date:',
            slogan: 'Thank you for your business.',
            defaultMinRows: 8,
        },
    };

    const currentConfig = typeConfig[type];
    const resolvedTitle = documentTitle || currentConfig.title;
    const resolvedNumLabel = documentNumberLabel || currentConfig.numLabel;
    const resolvedDateLabel = dateLabel || currentConfig.dateLabel;
    const resolvedSecDateLabel = secondaryDateLabel || currentConfig.secDateLabel;
    const resolvedTertiaryLabel = tertiaryNumberLabel || currentConfig.tertiaryLabel;
    const resolvedSlogan = footerSlogan || currentConfig.slogan;
    const effectiveMinRows = minRows || currentConfig.defaultMinRows;

    // Pad rows so table height and layout match the PDF look exactly
    const paddedRowsCount = Math.max(0, effectiveMinRows - items.length);
    const emptyRows = Array.from({ length: paddedRowsCount });

    return (
        <div
            className={`corporate-document-container bg-white text-slate-900 w-full max-w-[850px] mx-auto p-8 shadow-sm border border-slate-200 text-[12px] font-sans print:shadow-none print:border-none print:m-0 print:p-6 print:max-w-none ${className}`}
            style={{ fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }}
        >
            {/* 1. TOP HEADER */}
            <div className="flex justify-between items-start pb-2">
                {/* Left: Company Particulars */}
                <div className="w-[58%]">
                    <h1 className="text-[20px] font-extrabold uppercase tracking-tight text-[#1e3a5f] leading-tight">
                        {displayCompanyName}
                    </h1>
                    <p className="text-[11px] text-slate-600 mt-1 leading-snug">
                        {displayAddress}, {displayCity}
                    </p>
                    <p className="text-[11px] text-slate-600 leading-snug">
                        Phone: {displayPhone} | Email: {displayEmail}
                    </p>
                    <p className="text-[11px] text-slate-600 leading-snug">
                        TIN: <span className="font-semibold text-slate-800">{displayTin}</span> | VRN: <span className="font-semibold text-slate-800">{displayVrn}</span>
                    </p>
                </div>

                {/* Right: Document Title & Identifiers */}
                <div className="w-[42%] text-right">
                    <h2 className="text-[20px] font-extrabold uppercase tracking-tight text-[#1e3a5f] leading-tight">
                        {resolvedTitle}
                    </h2>
                    <div className="mt-2 space-y-1 text-[11px]">
                        <div className="flex justify-end gap-1">
                            <span className="font-bold text-slate-800">{resolvedNumLabel}</span>
                            <span className="font-mono font-bold text-slate-900">{documentNumber}</span>
                        </div>
                        <div className="flex justify-end gap-1">
                            <span className="font-bold text-slate-800">{resolvedDateLabel}</span>
                            <span className="text-slate-800">{date}</span>
                        </div>
                        {secondaryDate && resolvedSecDateLabel && (
                            <div className="flex justify-end gap-1">
                                <span className="font-bold text-slate-800">{resolvedSecDateLabel}</span>
                                <span className="text-slate-800">{secondaryDate}</span>
                            </div>
                        )}
                        {tertiaryNumber && resolvedTertiaryLabel && (
                            <div className="flex justify-end gap-1">
                                <span className="font-bold text-slate-800">{resolvedTertiaryLabel}</span>
                                <span className="font-mono font-semibold text-slate-900">{tertiaryNumber}</span>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Separator Line */}
            <div className="border-t-[1.5px] border-slate-700 mt-3 mb-4" />

            {/* 2. TWO-COLUMN METADATA BOX */}
            <div className="grid grid-cols-2 border border-slate-300 rounded-sm overflow-hidden mb-4">
                {/* Left Box */}
                <div className="border-r border-slate-300">
                    <div className="bg-[#eef4f9] px-3 py-1.5 border-b border-slate-300">
                        <span className="font-extrabold text-[11px] text-[#1e3a5f] uppercase tracking-wider">
                            {leftBoxTitle}
                        </span>
                    </div>
                    <div className="p-3 space-y-1.5 text-[11px]">
                        {leftBoxFields.map((field, idx) => (
                            <div key={idx} className="flex items-baseline leading-snug">
                                <span className="text-slate-700 font-semibold min-w-[130px] flex-shrink-0">
                                    {field.label}:
                                </span>
                                <span className="text-slate-900 font-medium break-words flex-1">
                                    {field.value || '__________________________________'}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Right Box */}
                <div>
                    <div className="bg-[#eef4f9] px-3 py-1.5 border-b border-slate-300">
                        <span className="font-extrabold text-[11px] text-[#1e3a5f] uppercase tracking-wider">
                            {rightBoxTitle}
                        </span>
                    </div>
                    <div className="p-3 space-y-1.5 text-[11px]">
                        {rightBoxFields.map((field, idx) => (
                            <div key={idx} className="flex items-baseline leading-snug">
                                <span className="text-slate-700 font-semibold min-w-[140px] flex-shrink-0">
                                    {field.label}:
                                </span>
                                <span className="text-slate-900 font-medium break-words flex-1">
                                    {field.value || '__________________________________'}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* 3. ITEMS TABLE */}
            <div className="border border-slate-300 rounded-sm overflow-hidden mb-4">
                <table className="w-full border-collapse text-[11px]">
                    <thead>
                        <tr className="bg-[#1a365d] text-white">
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    style={{ width: col.width }}
                                    className={`py-2 px-2.5 font-bold uppercase tracking-wider text-[10.5px] border-r border-[#2b4c77] last:border-r-0 ${
                                        col.align === 'right'
                                            ? 'text-right'
                                            : col.align === 'center'
                                            ? 'text-center'
                                            : 'text-left'
                                    }`}
                                >
                                    {col.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200">
                        {items.map((item, index) => (
                            <tr key={index} className="hover:bg-slate-50/60 transition-colors">
                                {columns.map((col) => (
                                    <td
                                        key={col.key}
                                        className={`py-2 px-2.5 border-r border-slate-200 last:border-r-0 align-top text-slate-800 ${
                                            col.align === 'right'
                                                ? 'text-right'
                                                : col.align === 'center'
                                                ? 'text-center'
                                                : 'text-left'
                                        }`}
                                    >
                                        {col.render
                                            ? col.render(item, index)
                                            : item[col.key] !== undefined && item[col.key] !== null
                                            ? String(item[col.key])
                                            : '-'}
                                    </td>
                                ))}
                            </tr>
                        ))}

                        {/* Render blank padded rows so the layout maintains exact PDF height */}
                        {emptyRows.map((_, padIdx) => (
                            <tr key={`pad-${padIdx}`} className="h-[28px]">
                                {columns.map((col, colIdx) => (
                                    <td
                                        key={`pad-col-${colIdx}`}
                                        className={`py-1.5 px-2.5 border-r border-slate-200 last:border-r-0 text-slate-400 ${
                                            colIdx === 0 ? 'text-center font-mono text-[10px]' : ''
                                        }`}
                                    >
                                        {colIdx === 0 ? items.length + padIdx + 1 : ''}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* 4. TOTALS BLOCK (FOR REQUISITIONS, RFQS, POs, INVOICES) */}
            {totals && totals.length > 0 && (
                <div className="flex justify-end mb-4">
                    <div className="w-[340px] space-y-1.5 text-[11px]">
                        {totals.map((tot, idx) => {
                            if (tot.isGrandTotal) {
                                return (
                                    <div
                                        key={idx}
                                        className="flex justify-between items-center bg-[#eef4f9] border border-slate-300 px-3 py-2 mt-2 rounded-sm"
                                    >
                                        <span className="font-extrabold uppercase text-[11px] text-[#1e3a5f]">
                                            {tot.label}
                                        </span>
                                        <span className="font-extrabold text-[12px] font-mono text-[#1e3a5f]">
                                            {typeof tot.value === 'number'
                                                ? `${tot.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`
                                                : tot.value}
                                        </span>
                                    </div>
                                );
                            }
                            return (
                                <div key={idx} className="flex justify-between items-center px-3 py-0.5">
                                    <span className="text-slate-700 font-medium">{tot.label}</span>
                                    <span className="font-mono text-slate-900 font-semibold">
                                        {typeof tot.value === 'number'
                                            ? `${tot.value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`
                                            : tot.value}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* 5. LOWER TWO-COLUMN DETAILS BOX */}
            {(lowerLeftTitle || lowerRightTitle) && (
                <div className="grid grid-cols-2 border border-slate-300 rounded-sm overflow-hidden mb-5">
                    {/* Lower Left */}
                    <div className="border-r border-slate-300">
                        {lowerLeftTitle && (
                            <div className="bg-[#eef4f9] px-3 py-1.5 border-b border-slate-300">
                                <span className="font-extrabold text-[11px] text-[#1e3a5f] uppercase tracking-wider">
                                    {lowerLeftTitle}
                                </span>
                            </div>
                        )}
                        <div className="p-3 text-[11px] leading-relaxed text-slate-700">
                            {lowerLeftContent}
                        </div>
                    </div>

                    {/* Lower Right */}
                    <div>
                        {lowerRightTitle && (
                            <div className="bg-[#eef4f9] px-3 py-1.5 border-b border-slate-300">
                                <span className="font-extrabold text-[11px] text-[#1e3a5f] uppercase tracking-wider">
                                    {lowerRightTitle}
                                </span>
                            </div>
                        )}
                        <div className="p-3 text-[11px] leading-relaxed text-slate-700">
                            {lowerRightContent}
                        </div>
                    </div>
                </div>
            )}

            {/* 6. SIGNATURE AND STAMP ROWS */}
            {signatures && signatures.length > 0 && (
                <div className="mb-4 pt-2">
                    <div className="grid grid-cols-2 gap-x-8 gap-y-4">
                        {signatures.map((sig, sIdx) => (
                            <div key={sIdx} className="space-y-1.5 text-[11px]">
                                <div className="flex items-baseline gap-2">
                                    <span className="font-bold text-slate-800 min-w-[130px] flex-shrink-0">
                                        {sig.label}:
                                    </span>
                                    <span className="border-b border-slate-400 flex-1 font-medium text-slate-900 pb-0.5 min-h-[18px]">
                                        {sig.value || ''}
                                    </span>
                                </div>
                                {sig.dateLabel && (
                                    <div className="flex items-baseline gap-2">
                                        <span className="text-slate-600 min-w-[130px] flex-shrink-0">
                                            {sig.dateLabel}:
                                        </span>
                                        <span className="border-b border-slate-400 flex-1 text-slate-800 pb-0.5 min-h-[18px]">
                                            {sig.dateValue || ''}
                                        </span>
                                    </div>
                                )}
                                {sig.extraLabel && (
                                    <div className="flex items-baseline gap-2">
                                        <span className="text-slate-600 min-w-[130px] flex-shrink-0">
                                            {sig.extraLabel}:
                                        </span>
                                        <span className="border-b border-slate-400 flex-1 text-slate-800 pb-0.5 min-h-[18px]">
                                            {sig.extraValue || ''}
                                        </span>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Verification Hash / Seal (if applicable) */}
            {(verification || verificationHash) && (
                <div className="flex justify-between items-center pt-2 pb-1 border-t border-slate-200 text-[10px] text-slate-500">
                    <div className="flex items-center gap-1.5">
                        <span className="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span className="font-semibold text-slate-700">Cryptographically Verified Document</span>
                    </div>
                    {verificationHash && (
                        <div className="font-mono text-[9.5px]">
                            HASH: {verificationHash.substring(0, 32)}...
                        </div>
                    )}
                </div>
            )}

            {/* 7. BOTTOM SLOGAN */}
            {resolvedSlogan && (
                <div className="text-center pt-3 pb-1 text-[11px] font-bold text-[#0d9488] tracking-wide">
                    {resolvedSlogan}
                </div>
            )}
        </div>
    );
}

