import React, { useEffect, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import html2pdf from 'html2pdf.js';
import { getCompanySetting, getImagePath } from '@/utils/helpers';

interface LetterData {
    template: {
        title: string;
        doc_code: string;
        acceptance_block: boolean;
    };
    reference_number: string;
    subject: string;
    body: string;
    date: string;
    employee_name: string;
    employee_number: string;
    employee_address: string;
    signatory_name: string;
    signatory_title: string;
}

export default function Print() {
    const { letter, companyAllSetting = {}, baseUrl } = usePage<{ letter: LetterData; companyAllSetting?: any; baseUrl?: string }>().props;
    const printedRef = useRef(false);

    const companyName = getCompanySetting('company_name') || 'Mutamark Enterprises Ltd';
    const companyAddress = getCompanySetting('company_address') || '';
    const companyCity = getCompanySetting('company_city') || '';
    const companyPhone = getCompanySetting('company_telephone') || '';
    const companyEmail = getCompanySetting('company_email') || '';
    const companyLogo = getCompanySetting('logo_dark') || getCompanySetting('company_logo') || '';

    const isDigitalSigEnabled = companyAllSetting?.digital_signature_enabled === 'on';
    const isAutoSign = companyAllSetting?.digital_signature_auto_sign !== 'off';
    const originBase = (baseUrl || (typeof window !== 'undefined' ? window.location.origin : '')).replace(/\/$/, '');
    const sigUrl = companyAllSetting?.digital_signature_path
        ? `${originBase}/storage/${String(companyAllSetting.digital_signature_path).replace(/^\/+/, '')}`
        : '';
    const stampUrl = companyAllSetting?.digital_stamp_path
        ? `${originBase}/storage/${String(companyAllSetting.digital_stamp_path).replace(/^\/+/, '')}`
        : '';

    useEffect(() => {
        if (new URLSearchParams(window.location.search).get('download') === 'pdf' && !printedRef.current) {
            printedRef.current = true;
            setTimeout(() => {
                downloadPdf();
            }, 400);
        }
    }, []);

    const downloadPdf = async () => {
        try {
            const el = document.getElementById('hr-letter-root');
            if (!el) return;
            await html2pdf()
                .set({
                    margin: 0.5,
                    filename: `${(letter.reference_number || letter.template.doc_code).replace(/[^\w-]+/g, '-')}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, letterRendering: true },
                    jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                })
                .from(el)
                .save();
        } finally {
            if (new URLSearchParams(window.location.search).get('download') === 'pdf') {
                setTimeout(() => window.close(), 400);
            }
        }
    };

    return (
        <div className="min-h-screen bg-slate-100 p-6 print:bg-white print:p-0">
            <Head title={`${letter.template.title} - ${letter.reference_number}`} />

            <div className="print:hidden mx-auto max-w-3xl mb-4 flex justify-end gap-2">
                <button
                    type="button"
                    onClick={() => window.print()}
                    className="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    Print
                </button>
                <button
                    type="button"
                    onClick={downloadPdf}
                    className="rounded bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800"
                >
                    Download PDF
                </button>
            </div>

            <div id="hr-letter-root" className="mx-auto max-w-3xl bg-white p-10 shadow-sm print:shadow-none text-slate-900 text-sm leading-relaxed">
                {/* Official Letterhead */}
                <div className="flex items-start justify-between border-b-2 border-[#1e3a5f] pb-4 mb-6">
                    <div>
                        <h1 className="text-lg font-extrabold text-[#1e3a5f] uppercase tracking-wide">{companyName}</h1>
                        <div className="text-xs text-slate-600 mt-1 space-y-0.5">
                            {companyAddress && <div>{companyAddress}{companyCity ? `, ${companyCity}` : ''}</div>}
                            {(companyPhone || companyEmail) && <div>{companyPhone}{companyPhone && companyEmail ? ' | ' : ''}{companyEmail}</div>}
                        </div>
                    </div>
                    {companyLogo && (
                        <img src={getImagePath(companyLogo)} alt={companyName} className="max-h-14 object-contain" />
                    )}
                </div>

                {/* Ref & Date */}
                <div className="flex justify-between text-xs text-slate-700 mb-6">
                    <div>
                        <div><strong>Ref:</strong> {letter.reference_number}</div>
                        <div><strong>Doc Code:</strong> {letter.template.doc_code}</div>
                    </div>
                    <div><strong>Date:</strong> {letter.date}</div>
                </div>

                {/* Recipient */}
                {(letter.employee_name || letter.employee_number) && (
                    <div className="mb-5 text-sm">
                        <div className="font-semibold">{letter.employee_name}</div>
                        {letter.employee_number && <div className="text-xs text-slate-600">Employee No.: {letter.employee_number}</div>}
                        {letter.employee_address && <div className="text-xs text-slate-600">{letter.employee_address}</div>}
                    </div>
                )}

                {/* Subject */}
                <div className="font-bold underline text-slate-900 mb-4">
                    Re: {letter.subject}
                </div>

                {/* Body */}
                <div className="whitespace-pre-line mb-8">{letter.body}</div>

                {/* Signatory + Stamp */}
                <div className="mt-8 flex items-end justify-between gap-8">
                    <div>
                        <div className="mb-2">Yours sincerely,</div>
                        {isDigitalSigEnabled && isAutoSign && sigUrl && (
                            <img src={sigUrl} alt="Authorized Signature" className="max-h-14 object-contain mb-1" />
                        )}
                        <div className="font-bold">{letter.signatory_name}</div>
                        <div className="text-xs text-slate-600">{letter.signatory_title}</div>
                        <div className="text-xs text-slate-600">{companyName}</div>
                    </div>
                    {isDigitalSigEnabled && isAutoSign && stampUrl && (
                        <img src={stampUrl} alt="Official Stamp" className="max-h-16 object-contain" />
                    )}
                </div>

                {/* Acceptance / Acknowledgement Block */}
                {letter.template.acceptance_block && (
                    <div className="mt-10 border-t border-slate-300 pt-4 text-xs text-slate-700 grid grid-cols-2 gap-8">
                        <div>Accepted / Received by: ____________________________</div>
                        <div>Date: ____________________</div>
                    </div>
                )}
            </div>
        </div>
    );
}

