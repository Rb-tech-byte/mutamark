import React, { useState, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { getCompanySetting, getImagePath } from '@/utils/helpers';
import ApplicationLogo from '@/components/application-logo';
import { DocumentVerificationData } from '@/components/DocumentVerificationBadge';

export type DocumentKind =
    | 'purchase_requisition'
    | 'rfq'
    | 'purchase_order'
    | 'delivery_note'
    | 'goods_received_note'
    | 'invoice'
    | 'quotation'
    | 'credit_note'
    | 'payment_voucher'
    | 'report';

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
    designation?: string | null;
    dateLabel?: string;
    dateValue?: string | null;
    extraLabel?: string;
    extraValue?: string | null;
    showStamp?: boolean;
}

export interface CorporateDocumentHeaderProps {
    title: string;
    metaItems?: Array<{ label?: string; value?: React.ReactNode }>;
    companyLogo?: string;
    showCompanyLogo?: boolean;
    companyName?: string;
    companyAddress?: string;
    companyCity?: string;
    companyPhone?: string;
    companyEmail?: string;
    companyTin?: string;
    companyVrn?: string;
    className?: string;
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
    companyLogo?: string;
    showCompanyLogo?: boolean;
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
    padEmptyRows?: boolean;

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

function cleanLabel(label?: string): string {
    return (label || '').replace(/:\s*$/, '').trim();
}

export function CorporateDocumentHeader({
    title,
    metaItems = [],
    companyLogo,
    showCompanyLogo = true,
    companyName,
    companyAddress,
    companyCity,
    companyPhone,
    companyEmail,
    companyTin,
    companyVrn,
    className = '',
}: CorporateDocumentHeaderProps) {
    const [logoFailed, setLogoFailed] = useState(false);

    const rawLogoSetting =
        companyLogo ||
        getCompanySetting('company_logo') ||
        getCompanySetting('logo_dark') ||
        getCompanySetting('logo_light') ||
        'mutamark_logo_dark.png';

    const resolvedLogoSrc = useMemo(() => {
        if (!rawLogoSetting) return '';
        if (
            rawLogoSetting.startsWith('http://') ||
            rawLogoSetting.startsWith('https://') ||
            rawLogoSetting.startsWith('data:')
        ) {
            return rawLogoSetting;
        }
        return getImagePath(rawLogoSetting);
    }, [rawLogoSetting]);

    const displayCompanyName =
        companyName ||
        getCompanySetting('company_name') ||
        'MUTAMARK ENTERPRISES LTD';

    const displayAddress =
        companyAddress ||
        getCompanySetting('company_address') ||
        'Mabibo-Ubungo, Dar Es Salaam';

    const displayCity =
        companyCity ||
        getCompanySetting('company_city') ||
        'Dar es Salaam, Tanzania';

    const displayPhone =
        companyPhone ||
        getCompanySetting('company_telephone') ||
        '0695207885';

    const displayEmail =
        companyEmail ||
        getCompanySetting('company_email') ||
        'info@mutamark.co.tz';

    const displayTin =
        companyTin ||
        getCompanySetting('registration_number') ||
        '572382';

    const displayVrn =
        companyVrn ||
        getCompanySetting('tax_number') ||
        '158628023';

    const fullAddress = useMemo(() => {
        const addr = (displayAddress || '').trim();
        const city = (displayCity || '').trim();
        if (!addr) return city;
        if (!city || addr.toLowerCase().includes(city.toLowerCase())) return addr;
        return `${addr}, ${city}`;
    }, [displayAddress, displayCity]);

    const contactParts = [
        displayPhone ? `Phone: ${displayPhone}` : null,
        displayEmail ? `Email: ${displayEmail}` : null,
        displayTin ? `TIN: ${displayTin}` : null,
        displayVrn ? `VRN: ${displayVrn}` : null,
    ].filter(Boolean);

    const visibleMeta = metaItems.filter(
        (item) => item && (item.value !== undefined && item.value !== null && String(item.value).trim() !== '')
    );

    return (
        <div className={`text-center ${className}`}>
            {showCompanyLogo && (
                <div className="flex justify-center mb-1.5">
                    {resolvedLogoSrc && !logoFailed ? (
                        <img
                            src={resolvedLogoSrc}
                            alt={displayCompanyName}
                            className="h-10 max-h-[42px] max-w-[200px] w-auto object-contain print:max-h-[38px]"
                            onError={() => setLogoFailed(true)}
                        />
                    ) : (
                        <div className="h-9 w-9 rounded border border-slate-200 bg-slate-50 flex items-center justify-center p-1">
                            <ApplicationLogo className="h-full w-full text-[#1B365D]" />
                        </div>
                    )}
                </div>
            )}

            <h1 className="text-[15px] font-extrabold uppercase tracking-wide text-[#1B365D] leading-tight">
                {displayCompanyName}
            </h1>

            <div className="mt-0.5 space-y-0.5 text-[10.5px] text-slate-500 leading-snug">
                {fullAddress && <p>{fullAddress}</p>}
                {contactParts.length > 0 && <p>{contactParts.join(' | ')}</p>}
            </div>

            <h2 className="mt-3 text-[18px] font-extrabold uppercase tracking-wide text-[#1B365D] leading-tight">
                {title}
            </h2>

            {visibleMeta.length > 0 && (
                <div className="mt-1 flex flex-wrap items-center justify-center gap-x-2 gap-y-0.5 text-[11px] text-slate-800">
                    {visibleMeta.map((item, idx) => (
                        <React.Fragment key={idx}>
                            {idx > 0 && <span className="text-slate-400">|</span>}
                            <span>
                                {item.label && (
                                    <span className="font-bold text-slate-900">
                                        {cleanLabel(item.label)}:{' '}
                                    </span>
                                )}
                                <span className="text-slate-800">{item.value}</span>
                            </span>
                        </React.Fragment>
                    ))}
                </div>
            )}

            <div className="border-t-[1.5px] border-[#1B365D] mt-3 mb-4" />
        </div>
    );
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

    companyLogo,
    showCompanyLogo = true,
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
    const { companyAllSetting = {}, baseUrl } = (usePage().props as any) || {};

    const isDigitalSigEnabled = companyAllSetting?.digital_signature_enabled === 'on';
    const isAutoSign = companyAllSetting?.digital_signature_auto_sign !== 'off';
    const digitalSigPath = companyAllSetting?.digital_signature_path || '';
    const digitalStampPath = companyAllSetting?.digital_stamp_path || '';
    const digitalSigName = companyAllSetting?.digital_signature_name || '';
    const digitalSigTitle = companyAllSetting?.digital_signature_title || 'Authorized Signatory';
    const originBase = (baseUrl || (typeof window !== 'undefined' ? window.location.origin : '')).replace(/\/$/, '');
    const digitalSigUrl = digitalSigPath ? `${originBase}/storage/${digitalSigPath.replace(/^\/+/, '')}` : '';
    const digitalStampUrl = digitalStampPath ? `${originBase}/storage/${digitalStampPath.replace(/^\/+/, '')}` : '';

    const [isDigitallySigned, setIsDigitallySigned] = useState<boolean>(
        () => isDigitalSigEnabled && isAutoSign
    );

    const typeConfig: Record<
        DocumentKind,
        {
            title: string;
            numLabel: string;
            dateLabel: string;
            secDateLabel?: string;
            tertiaryLabel?: string;
            slogan: string;
        }
    > = {
        purchase_requisition: {
            title: 'PURCHASE REQUISITION',
            numLabel: 'PR No',
            dateLabel: 'Date',
            secDateLabel: 'Date Required',
            slogan: 'Please attach supporting documents to this requisition.',
        },
        rfq: {
            title: 'REQUEST FOR QUOTATION',
            numLabel: 'RFQ No',
            dateLabel: 'Issue Date',
            secDateLabel: 'Closing Date',
            slogan: 'Thank you for your quotation.',
        },
        purchase_order: {
            title: 'PURCHASE ORDER',
            numLabel: 'PO No',
            dateLabel: 'Order Date',
            secDateLabel: 'Delivery Date',
            slogan: 'Please acknowledge receipt of this order.',
        },
        delivery_note: {
            title: 'DELIVERY NOTE',
            numLabel: 'DN No',
            dateLabel: 'Delivery Date',
            tertiaryLabel: 'PO / Order No',
            slogan: 'Thank you for your business.',
        },
        goods_received_note: {
            title: 'GOODS RECEIVED NOTE',
            numLabel: 'GRN No',
            dateLabel: 'Date Received',
            tertiaryLabel: 'PO No',
            slogan: 'Distribution: Accounts / Store / Procurement',
        },
        invoice: {
            title: 'INVOICE',
            numLabel: 'Invoice No',
            dateLabel: 'Invoice Date',
            secDateLabel: 'Due Date',
            slogan: 'Thank you for your business.',
        },
        quotation: {
            title: 'QUOTATION',
            numLabel: 'Quote No',
            dateLabel: 'Quote Date',
            secDateLabel: 'Valid Until',
            slogan: 'Thank you for the opportunity to quote.',
        },
        credit_note: {
            title: 'CREDIT NOTE',
            numLabel: 'Credit Note No',
            dateLabel: 'Date',
            tertiaryLabel: 'Invoice Ref',
            slogan: 'Thank you for your business.',
        },
        payment_voucher: {
            title: 'PAYMENT VOUCHER',
            numLabel: 'PV No',
            dateLabel: 'Date',
            tertiaryLabel: 'Cheque / Ref No',
            slogan: 'Payment must be supported by original documents.',
        },
        report: {
            title: 'MANAGEMENT REPORT',
            numLabel: 'Report Ref',
            dateLabel: 'Generated On',
            slogan: 'Thank you for your business.',
        },
    };

    const currentConfig = typeConfig[type] || typeConfig.invoice;
    const resolvedTitle = documentTitle || currentConfig.title;
    const resolvedNumLabel = documentNumberLabel || currentConfig.numLabel;
    const resolvedDateLabel = dateLabel || currentConfig.dateLabel;
    const resolvedSecDateLabel = secondaryDateLabel || currentConfig.secDateLabel;
    const resolvedTertiaryLabel = tertiaryNumberLabel || currentConfig.tertiaryLabel;
    const resolvedSlogan = footerSlogan || currentConfig.slogan;

    const headerMetaItems = [
        documentNumber ? { label: resolvedNumLabel, value: documentNumber } : null,
        date ? { label: resolvedDateLabel, value: date } : null,
        secondaryDate && resolvedSecDateLabel ? { label: resolvedSecDateLabel, value: secondaryDate } : null,
        tertiaryNumber && resolvedTertiaryLabel ? { label: resolvedTertiaryLabel, value: tertiaryNumber } : null,
    ].filter(Boolean) as Array<{ label: string; value: React.ReactNode }>;

    const formatTotalValue = (val: string | number) => {
        if (typeof val === 'number') {
            return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return val;
    };

    const sigGridCols =
        !signatures || signatures.length <= 1
            ? 'grid-cols-1'
            : signatures.length === 2
            ? 'grid-cols-2'
            : signatures.length === 3
            ? 'grid-cols-3'
            : 'grid-cols-4';

    const firstColIsIndex =
        columns.length > 0 &&
        (columns[0].key === 'index' || columns[0].label.trim() === '#');

    return (
        <div
            className={`corporate-document-container bg-white text-slate-900 w-full max-w-[850px] mx-auto p-8 shadow-sm border border-slate-200 text-[11.5px] font-sans print:shadow-none print:border-none print:m-0 print:p-4 print:max-w-none ${className}`}
            style={{ fontFamily: 'Arial, Helvetica, Inter, system-ui, sans-serif' }}
        >
            {/* 1. CENTERED CORPORATE LETTERHEAD & DOCUMENT TITLE */}
            <CorporateDocumentHeader
                title={resolvedTitle}
                metaItems={headerMetaItems}
                companyLogo={companyLogo}
                showCompanyLogo={showCompanyLogo}
                companyName={companyName}
                companyAddress={companyAddress}
                companyCity={companyCity}
                companyPhone={companyPhone}
                companyEmail={companyEmail}
                companyTin={companyTin}
                companyVrn={companyVrn}
            />

            {/* 2. TWO-COLUMN METADATA BOX */}
            <div className="grid grid-cols-2 border border-[#9EB3C8] mb-4">
                {/* Left Box */}
                <div className="border-r border-[#9EB3C8]">
                    <div className="bg-[#EBF1F6] px-3 py-1.5 border-b border-[#9EB3C8]">
                        <span className="font-extrabold text-[10.5px] text-[#1B365D] uppercase tracking-wide">
                            {leftBoxTitle}
                        </span>
                    </div>
                    <div className="p-3 space-y-1 text-[11px] leading-snug">
                        {leftBoxFields.map((field, idx) => (
                            <div key={idx} className="break-words">
                                <span className="font-bold text-slate-900">
                                    {cleanLabel(field.label)}:{' '}
                                </span>
                                <span className="text-slate-800">{field.value || '—'}</span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Right Box */}
                <div>
                    <div className="bg-[#EBF1F6] px-3 py-1.5 border-b border-[#9EB3C8]">
                        <span className="font-extrabold text-[10.5px] text-[#1B365D] uppercase tracking-wide">
                            {rightBoxTitle}
                        </span>
                    </div>
                    <div className="p-3 space-y-1 text-[11px] leading-snug">
                        {rightBoxFields.map((field, idx) => (
                            <div key={idx} className="break-words">
                                <span className="font-bold text-slate-900">
                                    {cleanLabel(field.label)}:{' '}
                                </span>
                                <span className="text-slate-800">{field.value || '—'}</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* 3. ITEMS / DATA TABLE (Dynamic row count; leaves 1 empty row when no items) */}
            <div className="border border-[#9EB3C8] mb-4 overflow-hidden">
                <table className="w-full border-collapse text-[11px]">
                    <thead>
                        <tr className="bg-[#1B365D] text-white">
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    style={{ width: col.width }}
                                    className={`py-2 px-2.5 font-bold text-[10.5px] border-r border-[#334E73] last:border-r-0 ${
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
                    <tbody>
                        {items.length === 0 ? (
                            <tr className="h-[32px]">
                                {columns.map((col, colIdx) => (
                                    <td
                                        key={`empty-${col.key}-${colIdx}`}
                                        className={`py-2 px-2.5 border-r border-[#C5D3E0] last:border-r-0 text-slate-500 ${
                                            colIdx === 0 && firstColIsIndex ? 'text-center' : ''
                                        }`}
                                    >
                                        {colIdx === 0 && firstColIsIndex ? '1' : '\u00A0'}
                                    </td>
                                ))}
                            </tr>
                        ) : (
                            items.map((item, index) => (
                                <tr
                                    key={index}
                                    className="border-b border-[#C5D3E0] last:border-b-0 hover:bg-slate-50/50"
                                >
                                    {columns.map((col) => (
                                        <td
                                            key={col.key}
                                            className={`py-2 px-2.5 border-r border-[#C5D3E0] last:border-r-0 align-top text-slate-800 ${
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
                                                : ''}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {/* 4. RIGHT-ALIGNED TOTALS BLOCK */}
            {totals && totals.length > 0 && (
                <div className="flex justify-end mb-4">
                    <table className="w-[360px] border-collapse text-[11px]">
                        <tbody>
                            {totals.map((tot, idx) => {
                                if (tot.isGrandTotal) {
                                    return (
                                        <tr
                                            key={idx}
                                            className="bg-[#EAF5F1] border-t-[1.5px] border-t-[#0E7C5B] border-b border-b-[#1B365D]"
                                        >
                                            <td className="py-1.5 pl-3 pr-4 text-right font-extrabold uppercase text-slate-900">
                                                {tot.label}
                                            </td>
                                            <td className="py-1.5 px-2.5 text-right font-extrabold text-slate-900 w-[145px] whitespace-nowrap">
                                                {formatTotalValue(tot.value)}
                                            </td>
                                        </tr>
                                    );
                                }
                                return (
                                    <tr key={idx}>
                                        <td className="py-1.5 pl-3 pr-4 text-right text-slate-800">
                                            {tot.label}
                                        </td>
                                        <td className="py-1.5 px-2.5 text-right font-medium text-slate-900 border-b border-[#1B365D]/40 w-[145px] whitespace-nowrap">
                                            {formatTotalValue(tot.value)}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            )}

            {/* 5. LOWER TWO-COLUMN DETAILS BOX */}
            {(lowerLeftTitle || lowerRightTitle) && (
                <div className="grid grid-cols-2 border border-[#9EB3C8] mb-5">
                    <div className="border-r border-[#9EB3C8]">
                        {lowerLeftTitle && (
                            <div className="bg-[#EBF1F6] px-3 py-1.5 border-b border-[#9EB3C8]">
                                <span className="font-extrabold text-[10.5px] text-[#1B365D] uppercase tracking-wide">
                                    {lowerLeftTitle}
                                </span>
                            </div>
                        )}
                        <div className="p-3 text-[11px] leading-relaxed text-slate-800">
                            {lowerLeftContent}
                        </div>
                    </div>

                    <div>
                        {lowerRightTitle && (
                            <div className="bg-[#EBF1F6] px-3 py-1.5 border-b border-[#9EB3C8]">
                                <span className="font-extrabold text-[10.5px] text-[#1B365D] uppercase tracking-wide">
                                    {lowerRightTitle}
                                </span>
                            </div>
                        )}
                        <div className="p-3 text-[11px] leading-relaxed text-slate-800">
                            {lowerRightContent}
                        </div>
                    </div>
                </div>
            )}

            {/* 6. AUTHORISATION & SIGNATORIES */}
            {isDigitalSigEnabled && !isAutoSign && !isDigitallySigned && (
                <div className="print:hidden mb-3 flex justify-end">
                    <button
                        type="button"
                        onClick={() => setIsDigitallySigned(true)}
                        className="rounded bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-emerald-700"
                    >
                        Sign Document
                    </button>
                </div>
            )}

            {signatures && signatures.length > 0 && (
                <div className="mb-4 break-inside-avoid">
                    <div className="text-[10.5px] font-extrabold uppercase tracking-wide text-[#1B365D] mb-1.5">
                        AUTHORISATION &amp; SIGNATORIES
                    </div>
                    <div className={`grid ${sigGridCols} border border-[#9EB3C8] divide-x divide-[#9EB3C8]`}>
                        {signatures.map((sig, sIdx) => {
                            const labelClean = cleanLabel(sig.label);
                            const isExternalParty = /vendor|supplier|customer|client|receiver|payee|accepted/i.test(labelClean);
                            const isAuthorisedSlot = /authori[sz]ed|approved|signature|verified|issued/i.test(labelClean);
                            const showAutoSigInSlot =
                                isDigitalSigEnabled &&
                                isDigitallySigned &&
                                !isExternalParty &&
                                (isAuthorisedSlot || (sIdx === 0 && signatures.length === 1));
                            const showStampInSlot =
                                sig.showStamp !== false &&
                                !isExternalParty &&
                                isDigitalSigEnabled &&
                                isDigitallySigned &&
                                !!digitalStampUrl &&
                                (isAuthorisedSlot || sIdx === 0);

                            const resolvedName =
                                sig.value || (showAutoSigInSlot ? digitalSigName : '') || '';
                            const resolvedDesignation =
                                sig.designation ||
                                (sig.extraLabel && !/stamp/i.test(sig.extraLabel) ? sig.extraValue : '') ||
                                (showAutoSigInSlot ? digitalSigTitle : '') ||
                                '';

                            return (
                                <div key={sIdx} className="p-3 flex flex-col justify-between text-[10.5px]">
                                    <div>
                                        <div className="font-extrabold uppercase tracking-wide text-[#1B365D] mb-2">
                                            {labelClean}
                                        </div>

                                        <div className="space-y-1.5">
                                            <div className="flex items-end gap-1.5">
                                                <span className="font-bold text-slate-900 whitespace-nowrap">Signature:</span>
                                                <span className="flex-1 border-b border-slate-700 min-h-[16px] flex items-end justify-center pb-0.5">
                                                    {showAutoSigInSlot && digitalSigUrl ? (
                                                        <img
                                                            src={digitalSigUrl}
                                                            alt="Signature"
                                                            className="max-h-7 object-contain"
                                                        />
                                                    ) : null}
                                                </span>
                                            </div>

                                            <div className="flex items-end gap-1.5">
                                                <span className="font-bold text-slate-900 whitespace-nowrap">Name:</span>
                                                <span className="flex-1 border-b border-slate-700 min-h-[15px] text-slate-800 px-1 truncate">
                                                    {resolvedName}
                                                </span>
                                            </div>

                                            <div className="flex items-end gap-1.5">
                                                <span className="font-bold text-slate-900 whitespace-nowrap">Designation:</span>
                                                <span className="flex-1 border-b border-slate-700 min-h-[15px] text-slate-800 px-1 truncate">
                                                    {resolvedDesignation}
                                                </span>
                                            </div>

                                            <div className="flex items-end gap-1.5">
                                                <span className="font-bold text-slate-900 whitespace-nowrap">
                                                    {cleanLabel(sig.dateLabel || 'Date')}:
                                                </span>
                                                <span className="flex-1 border-b border-slate-700 min-h-[15px] text-slate-800 px-1">
                                                    {sig.dateValue || ''}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Stamp Box */}
                                    <div className="mt-3 h-14 border border-[#C5D3E0] px-2 py-1 flex items-end justify-between bg-white">
                                        <span className="text-[9.5px] text-slate-400">Stamp</span>
                                        {showStampInSlot && digitalStampUrl && (
                                            <img
                                                src={digitalStampUrl}
                                                alt="Official Company Stamp"
                                                className="max-h-12 object-contain"
                                            />
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {(!signatures || signatures.length === 0) && isDigitalSigEnabled && isDigitallySigned && (digitalSigUrl || digitalSigName) && (
                <div className="mb-4 pt-2 flex items-end justify-between border-t border-slate-200">
                    <div>
                        {digitalSigUrl && (
                            <img src={digitalSigUrl} alt="Digital Signature" className="max-h-10 object-contain" />
                        )}
                        <div className="text-[10.5px] font-semibold text-slate-800">
                            {digitalSigName || 'Authorized Signatory'}
                            {digitalSigTitle ? ` — ${digitalSigTitle}` : ''}
                        </div>
                    </div>
                    {digitalStampUrl && (
                        <img src={digitalStampUrl} alt="Official Stamp" className="max-h-14 object-contain" />
                    )}
                </div>
            )}

            {/* Verification Hash (if applicable) */}
            {(verification || verificationHash) && (
                <div className="flex justify-between items-center pt-2 pb-1 border-t border-slate-200 text-[9.5px] text-slate-500">
                    <div className="flex items-center gap-1.5">
                        <span className="inline-block w-2 h-2 rounded-full bg-[#0E7C5B]"></span>
                        <span className="font-semibold text-slate-700">Verified Document</span>
                    </div>
                    {verificationHash && (
                        <div className="font-mono text-[9px]">
                            HASH: {verificationHash.substring(0, 32)}...
                        </div>
                    )}
                </div>
            )}

            {/* 7. CENTERED BRAND-GREEN FOOTER SLOGAN */}
            {resolvedSlogan && (
                <div className="text-center pt-3 pb-1 text-[11px] font-bold text-[#0E7C5B]">
                    {resolvedSlogan}
                </div>
            )}
        </div>
    );
}
