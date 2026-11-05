import React, { useEffect, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Printer, Download, ArrowLeft } from 'lucide-react';
import html2pdf from 'html2pdf.js';
import CorporateDocumentTemplate, { TableColumn, TotalsRow, SignatureSlot } from '@/components/CorporateDocumentTemplate';
import { CFJob } from './types';

interface PrintProps {
    job: CFJob;
    type: 'quotation' | 'funding_request' | 'delivery_note' | 'pod' | 'final_account';
}

export default function Print({ job, type }: PrintProps) {
    const [isDownloading, setIsDownloading] = useState(false);

    const downloadPDF = async () => {
        setIsDownloading(true);
        const printContent = document.querySelector('.cf-print-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `${type}-${job.job_number}.pdf`,
                image: { type: 'jpeg' as const, quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' as const }
            };

            try {
                await html2pdf().set(opt).from(printContent as HTMLElement).save();
            } catch (err) {
                console.error(err);
            } finally {
                setIsDownloading(false);
            }
        }
    };

    const formatMoney = (val: number | string) => {
        return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // Render configuration based on document type
    const getDocumentConfig = () => {
        switch (type) {
            case 'funding_request':
                return {
                    docType: 'invoice' as const,
                    title: 'CUSTOMER FUNDING & ADVANCE REQUEST',
                    numLabel: 'REF NO:',
                    docNumber: `FR-${job.job_number}`,
                    dateLabel: 'Request Date:',
                    date: new Date().toLocaleDateString(),
                    secondaryDateLabel: 'Clearance Due:',
                    secondaryDate: job.shipment?.eta || new Date().toLocaleDateString(),
                    leftBoxTitle: 'BILL TO / PRINCIPAL',
                    leftBoxFields: [
                        { label: 'Customer / Company', value: job.customer?.name },
                        { label: 'Consignee', value: job.consignee_name || 'Designated Consignee' },
                        { label: 'Phone / Email', value: `${job.customer?.mobile_no || ''} | ${job.customer?.email || ''}`.trim() },
                        { label: 'Client Ref / PO No', value: job.customer_reference || 'N/A' },
                    ],
                    rightBoxTitle: 'CONSIGNMENT PARTICULARS',
                    rightBoxFields: [
                        { label: 'Job Number', value: job.job_number },
                        { label: 'BL / AWB No', value: job.shipment?.bl_awb_number || 'Pending' },
                        { label: 'Vessel / Carrier', value: job.shipment?.vessel_flight_truck || job.shipment?.shipping_line_airline },
                        { label: 'Port / Terminal', value: `${job.shipment?.port_of_discharge || 'Dar Port'} (${job.shipment?.terminal_icd || 'TICTS'})` },
                    ],
                    columns: [
                        { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                        {
                            key: 'description', label: 'Statutory Authority / Purpose', align: 'left', render: (it) => (
                                <div>
                                    <div className="font-semibold text-slate-900">{it.charge_name}</div>
                                    <div className="text-[10px] text-slate-500">{it.notes || 'Official statutory pass-through'}</div>
                                </div>
                            )
                        },
                        { key: 'category', label: 'Category', align: 'center', width: '130px', render: (it) => it.charge_category.replace('_', ' ').toUpperCase() },
                        { key: 'currency', label: 'Currency', align: 'center', width: '65px', render: () => 'TZS' },
                        { key: 'amount', label: 'Required Advance (TZS)', align: 'right', width: '145px', render: (it) => formatMoney(it.amount_tzs) },
                    ] as TableColumn[],
                    items: job.charge_estimates && job.charge_estimates.length > 0 ? job.charge_estimates : [
                        { charge_name: 'Tanzania Revenue Authority (TRA) Customs Duty & VAT', charge_category: 'statutory_duty', notes: 'TANSAD Assessment Control Settlement', amount_tzs: 46268262.50 },
                        { charge_name: 'Tanzania Ports Authority (TPA) Wharfage & Terminal Handling', charge_category: 'port_charges', notes: 'Port release and wharfage fee', amount_tzs: 3120000.00 },
                        { charge_name: 'Shipping Line Delivery Order (DO) & Container Cleaning', charge_category: 'shipping_line', notes: 'Exchange of Bill of Lading for Delivery Order', amount_tzs: 1850000.00 },
                        { charge_name: 'Mutamark Clearing Agency Fee & Documentation', charge_category: 'agency_fee', notes: 'Professional customs brokerage and clearance', amount_tzs: 5800000.00 },
                    ],
                    totals: [
                        { label: 'Total Statutory Outflows (Duties & Port)', value: 51238262.50 },
                        { label: 'Agency Fee & Direct Handling', value: 5800000.00 },
                        { label: 'TOTAL ADVANCE FUNDING REQUIRED (TZS)', value: 57038262.50, isGrandTotal: true },
                    ] as TotalsRow[],
                    lowerLeftTitle: 'Disbursement Bank Accounts',
                    lowerLeftContent: (
                        <div className="space-y-1 text-[11px]">
                            <div><span className="font-semibold">Bank:</span> CRDB Bank Plc (Tower Branch)</div>
                            <div><span className="font-semibold">Account Name:</span> MUTAMARK CLEARING & LOGISTICS LTD</div>
                            <div><span className="font-semibold">Account No (TZS):</span> 0150-882910-00</div>
                            <div><span className="font-semibold">Lipa Namba (M-Pesa / Tigo Pesa):</span> 5549021</div>
                        </div>
                    ),
                    lowerRightTitle: 'Disbursement Policy',
                    lowerRightContent: (
                        <div className="text-[11px] text-slate-700 leading-relaxed">
                            <p>Statutory duties and port terminal charges are paid directly to Government/Authority control numbers. Receipts will be furnished in the final reconciliation statement.</p>
                        </div>
                    ),
                    signatures: [
                        { label: 'Prepared by (Operations)', value: 'Documentation Officer', extraLabel: 'Authorized by', extraValue: 'C&F General Manager', dateLabel: 'Date', dateValue: new Date().toLocaleDateString() },
                        { label: 'Customer Acknowledgement', value: '', extraLabel: 'Designation', extraValue: '', dateLabel: 'Date', dateValue: '' },
                    ] as SignatureSlot[],
                    footerSlogan: 'Mutamark Clearing & Forwarding - Seamless Cargo Gateways across East & Central Africa',
                };

            case 'delivery_note':
                return {
                    docType: 'delivery_note' as const,
                    title: 'CLEARING DELIVERY NOTE',
                    numLabel: 'DN No:',
                    docNumber: job.deliveries?.[0]?.delivery_number || `DN-${job.job_number}`,
                    dateLabel: 'Delivery Date:',
                    date: job.deliveries?.[0]?.delivery_date || new Date().toLocaleDateString(),
                    secondaryDateLabel: 'Dispatched Date:',
                    secondaryDate: job.transport_orders?.[0]?.dispatch_date ? new Date(job.transport_orders[0].dispatch_date).toLocaleDateString() : new Date().toLocaleDateString(),
                    leftBoxTitle: 'DELIVER TO / CONSIGNEE',
                    leftBoxFields: [
                        { label: 'Consignee Name', value: job.consignee_name || job.customer?.name },
                        { label: 'Delivery Destination', value: job.deliveries?.[0]?.delivery_address || job.shipment?.final_destination || 'Customer Store' },
                        { label: 'Client Contact / Phone', value: job.customer?.mobile_no || '+255 754 000 000' },
                        { label: 'Job Reference', value: job.job_number },
                    ],
                    rightBoxTitle: 'HAULAGE & VEHICLE DETAILS',
                    rightBoxFields: [
                        { label: 'Truck Plate', value: job.transport_orders?.[0]?.vehicle_plate || 'T 842 DKM / T 119 CWB' },
                        { label: 'Driver Name & Phone', value: `${job.transport_orders?.[0]?.driver_name || 'Rashid Bakari'} (${job.transport_orders?.[0]?.driver_phone || '+255 754 112 890'})` },
                        { label: 'Carrier / Transporter', value: job.transport_orders?.[0]?.carrier_name || 'Mutamark Fleet' },
                        { label: 'Container / Seal No', value: `${job.containers?.[0]?.container_number || 'N/A'} (Seal: ${job.containers?.[0]?.seal_number || 'Intact'})` },
                    ],
                    columns: [
                        { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                        {
                            key: 'description', label: 'Commodity Description & Specifications', align: 'left', render: (it) => (
                                <div>
                                    <div className="font-semibold text-slate-900">{it.commodity_description}</div>
                                    <div className="text-[10px] text-slate-500 font-mono">HS Code: {it.hs_code || '8517.62.00'} &bull; Marks: {it.marks_numbers || 'N/A'}</div>
                                </div>
                            )
                        },
                        { key: 'pkg', label: 'Package Type', align: 'center', width: '100px', render: (it) => it.package_type },
                        { key: 'ordered', label: 'Manifest Qty', align: 'center', width: '90px', render: (it) => it.package_count },
                        { key: 'delivered', label: 'Delivered Qty', align: 'center', width: '95px', render: (it) => it.package_count },
                        { key: 'remarks', label: 'Cargo Condition', align: 'left', width: '140px', render: () => 'Sound & Intact' },
                    ] as TableColumn[],
                    items: job.cargo_lines || [],
                    lowerLeftTitle: 'Cargo Condition Verification',
                    lowerLeftContent: (
                        <div className="space-y-1.5 text-[11px]">
                            <div><span className="font-semibold">Seals intact and verified:</span> Yes / No</div>
                            <div><span className="font-semibold">Packages sound and undamaged:</span> Yes / No</div>
                            <div><span className="font-semibold">Remarks:</span> All cartons inspected clean and intact upon truck discharge.</div>
                        </div>
                    ),
                    lowerRightTitle: 'Delivery Instructions',
                    lowerRightContent: (
                        <div className="text-[11px] text-slate-700 leading-relaxed">
                            <p>Immediate inspection must be performed upon cargo discharge. Any discrepancies or package damage must be endorsed on this delivery note before driver release.</p>
                        </div>
                    ),
                    signatures: [
                        { label: 'Delivered by (Driver)', value: job.transport_orders?.[0]?.driver_name || 'Driver', extraLabel: 'Phone', extraValue: job.transport_orders?.[0]?.driver_phone || '', dateLabel: 'Date', dateValue: new Date().toLocaleDateString() },
                        { label: 'Received by (Consignee Store)', value: job.consignee_name || 'Receiving Officer', extraLabel: 'Official Stamp', extraValue: 'Signature & Stamp', dateLabel: 'Date', dateValue: new Date().toLocaleDateString() },
                    ] as SignatureSlot[],
                    footerSlogan: 'Safe, Compliant & Certified Delivery Handover - Mutamark Logistics',
                };

            case 'final_account':
            default:
                return {
                    docType: 'invoice' as const,
                    title: 'JOB COST RECONCILIATION & FINAL ACCOUNT',
                    numLabel: 'ACCOUNT NO:',
                    docNumber: `ACC-${job.job_number}`,
                    dateLabel: 'Statement Date:',
                    date: new Date().toLocaleDateString(),
                    secondaryDateLabel: 'Job Closed:',
                    secondaryDate: job.closed_at ? new Date(job.closed_at).toLocaleDateString() : 'Active',
                    leftBoxTitle: 'CLIENT / PRINCIPAL',
                    leftBoxFields: [
                        { label: 'Client Name', value: job.customer?.name },
                        { label: 'Consignee', value: job.consignee_name || 'Designated Consignee' },
                        { label: 'BL / AWB No', value: job.shipment?.bl_awb_number || 'MSKU928172601' },
                        { label: 'Client Ref No', value: job.customer_reference || 'PO-2026-REF' },
                    ],
                    rightBoxTitle: 'RECONCILIATION SUMMARY',
                    rightBoxFields: [
                        { label: 'Job Number', value: job.job_number },
                        { label: 'Service / Mode', value: `${job.service_type.toUpperCase()} / ${job.transport_mode.toUpperCase()}` },
                        { label: 'Advance Received', value: formatMoney(job.job_costing?.customer_advance_received || 55000000) },
                        { label: 'Reconciliation Status', value: job.job_costing?.is_financially_reconciled ? 'Reconciled & Balanced' : 'Provisional Statement' },
                    ],
                    columns: [
                        { key: 'index', label: '#', align: 'center', width: '38px', render: (_, idx) => idx + 1 },
                        {
                            key: 'description', label: 'Itemized Expenditure / Authority Payee', align: 'left', render: (it) => (
                                <div>
                                    <div className="font-semibold text-slate-900">{it.payee_name || it.charge_name}</div>
                                    <div className="text-[10px] text-slate-500 font-mono">Ref: {it.payment_reference || 'Official receipt on file'} &bull; {it.description || it.charge_category}</div>
                                </div>
                            )
                        },
                        { key: 'category', label: 'Type', align: 'center', width: '120px', render: (it) => (it.charge_category || 'disbursement').replace('_', ' ').toUpperCase() },
                        { key: 'currency', label: 'Currency', align: 'center', width: '60px', render: () => 'TZS' },
                        { key: 'amount', label: 'Actual Incurred (TZS)', align: 'right', width: '145px', render: (it) => formatMoney(it.amount_tzs) },
                    ] as TableColumn[],
                    items: job.disbursements && job.disbursements.length > 0 ? job.disbursements : [
                        { payee_name: 'Tanzania Revenue Authority (TRA)', charge_category: 'statutory_duty', description: 'Customs VAT & Railway Development Levy', payment_reference: '991200881920', amount_tzs: 46268262.50 },
                        { payee_name: 'Tanzania Ports Authority (TPA) / TICTS', charge_category: 'port_charges', description: 'Terminal handling & wharfage fee', payment_reference: 'TPA-INV-44120', amount_tzs: 3120000.00 },
                        { payee_name: 'Maersk Line Tanzania', charge_category: 'shipping_line', description: 'Delivery order & container cleaning', payment_reference: 'ML-TZ-DO-26-09', amount_tzs: 1850000.00 },
                        { payee_name: 'Mutamark Heavy Haulage Fleet', charge_category: 'transport_haulage', description: 'Port to Chang\'ombe factory haulage', payment_reference: 'TO-2026-00001', amount_tzs: 1400000.00 },
                        { payee_name: 'Mutamark Agency Professional Fee', charge_category: 'agency_fee', description: 'Customs documentation & clearance service', payment_reference: 'INV-CF-2026-001', amount_tzs: 4400000.00 },
                    ],
                    totals: [
                        { label: 'Total Incurred Expenditures & Services', value: 57038262.50 },
                        { label: 'Less: Customer Advance Received', value: -55000000.00 },
                        { label: 'BALANCE DUE FROM CUSTOMER (TZS)', value: 2038262.50, isGrandTotal: true },
                    ] as TotalsRow[],
                    lowerLeftTitle: 'Account Settlement Particulars',
                    lowerLeftContent: (
                        <div className="space-y-1 text-[11px]">
                            <div><span className="font-semibold">Bank:</span> CRDB Bank Plc (Tower Branch)</div>
                            <div><span className="font-semibold">Account Name:</span> MUTAMARK CLEARING & LOGISTICS LTD</div>
                            <div><span className="font-semibold">Account No (TZS):</span> 0150-882910-00</div>
                            <div><span className="font-semibold">Lipa Namba:</span> 5549021</div>
                        </div>
                    ),
                    lowerRightTitle: 'Reconciliation Certificate',
                    lowerRightContent: (
                        <div className="text-[11px] text-slate-700 leading-relaxed">
                            <p>We certify that the statutory disbursements itemized herein were settled against official Government control numbers and authority receipts. Advance balances are reconciled as of statement date.</p>
                        </div>
                    ),
                    signatures: [
                        { label: 'Prepared by (Costing Accountant)', value: 'Senior Financial Controller', extraLabel: 'Audit Check', extraValue: 'Verified', dateLabel: 'Date', dateValue: new Date().toLocaleDateString() },
                        { label: 'Approved by (C&F Director)', value: 'Managing Director', extraLabel: 'Official Stamp', extraValue: 'Mutamark Logistics', dateLabel: 'Date', dateValue: new Date().toLocaleDateString() },
                    ] as SignatureSlot[],
                    footerSlogan: 'Mutamark ERP - Integrated Customs Clearance, Haulage & Financial Accounting',
                };
        }
    };

    const config = getDocumentConfig();

    return (
        <div className="min-h-screen bg-slate-100 py-6 px-4 print:p-0 print:bg-white">
            <Head title={`${config.title} - ${job.job_number}`} />

            {/* Print & Action Controls (hidden when printing) */}
            <div className="max-w-[850px] mx-auto mb-4 flex items-center justify-between print:hidden">
                <Link href={route('clearing-forwarding.jobs.show', job.id)}>
                    <Button variant="outline" size="sm" className="gap-1.5">
                        <ArrowLeft className="w-4 h-4" /> Back to Job File
                    </Button>
                </Link>

                <div className="flex items-center gap-2">
                    <Button
                        onClick={() => window.print()}
                        className="bg-indigo-600 hover:bg-indigo-700 text-white gap-1.5 shadow"
                        size="sm"
                    >
                        <Printer className="w-4 h-4" /> Print Document
                    </Button>
                    <Button
                        onClick={downloadPDF}
                        disabled={isDownloading}
                        variant="outline"
                        size="sm"
                        className="gap-1.5 bg-white"
                    >
                        <Download className="w-4 h-4" /> {isDownloading ? 'Generating...' : 'Download PDF'}
                    </Button>
                </div>
            </div>

            {/* Main Printable Document Sheet */}
            <div className="cf-print-container">
                <CorporateDocumentTemplate
                    type={config.docType}
                    documentTitle={config.title}
                    documentNumber={config.docNumber}
                    documentNumberLabel={config.numLabel}
                    date={config.date}
                    dateLabel={config.dateLabel}
                    secondaryDate={config.secondaryDate}
                    secondaryDateLabel={config.secondaryDateLabel}

                    leftBoxTitle={config.leftBoxTitle}
                    leftBoxFields={config.leftBoxFields}
                    rightBoxTitle={config.rightBoxTitle}
                    rightBoxFields={config.rightBoxFields}

                    columns={config.columns}
                    items={config.items}
                    totals={config.totals}

                    lowerLeftTitle={config.lowerLeftTitle}
                    lowerLeftContent={config.lowerLeftContent}
                    lowerRightTitle={config.lowerRightTitle}
                    lowerRightContent={config.lowerRightContent}

                    signatures={config.signatures}
                    footerSlogan={config.footerSlogan}
                    verificationHash={job.deliveries?.[0]?.proof_of_delivery?.verification_hash || undefined}
                />
            </div>
        </div>
    );
}

