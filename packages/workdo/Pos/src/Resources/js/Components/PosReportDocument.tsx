import React, { useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Printer, Download, FileSpreadsheet } from 'lucide-react';
import CorporateDocumentTemplate, {
    MetaField,
    TableColumn,
    TotalsRow,
    SignatureSlot,
} from '@/components/CorporateDocumentTemplate';

export interface PosReportDocumentProps {
    documentTitle: string;
    documentNumber: string;
    reportDate: string;
    leftBoxTitle: string;
    leftBoxFields: MetaField[];
    rightBoxTitle: string;
    rightBoxFields: MetaField[];
    columns: TableColumn[];
    items: any[];
    totals?: TotalsRow[];
    signatures?: SignatureSlot[];
    csvFilename?: string;
    csvHeaders?: string[];
    csvRows?: (string | number)[][];
    pdfFilename?: string;
}

export default function PosReportDocument({
    documentTitle,
    documentNumber,
    reportDate,
    leftBoxTitle,
    leftBoxFields,
    rightBoxTitle,
    rightBoxFields,
    columns,
    items,
    totals,
    signatures,
    csvFilename = 'report.csv',
    csvHeaders,
    csvRows,
    pdfFilename = 'report.pdf',
}: PosReportDocumentProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const docRef = useRef<HTMLDivElement>(null);
    const [downloading, setDownloading] = useState(false);

    const handlePrint = () => {
        window.print();
    };

    const handleDownloadPdf = async () => {
        if (!docRef.current) return;
        setDownloading(true);
        try {
            const html2pdfModule = await import('html2pdf.js');
            const html2pdf = html2pdfModule.default;
            await html2pdf()
                .set({
                    margin: [8, 8, 8, 8],
                    filename: pdfFilename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                })
                .from(docRef.current)
                .save();
        } catch (err) {
            console.error('PDF export failed:', err);
            window.print();
        } finally {
            setDownloading(false);
        }
    };

    const handleExportCsv = () => {
        const headers = csvHeaders || columns.map((c) => c.label);
        const rows =
            csvRows ||
            items.map((item, idx) =>
                columns.map((col) => {
                    const val = item[col.key];
                    if (val === null || val === undefined) return '';
                    return String(val);
                })
            );
        const escapeCell = (val: string | number) => {
            const str = String(val ?? '');
            return `"${str.replace(/"/g, '""')}"`;
        };
        const csvContent = [
            headers.map(escapeCell).join(','),
            ...rows.map((r) => r.map(escapeCell).join(',')),
        ].join('\n');

        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', csvFilename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    };

    const defaultSignatures: SignatureSlot[] = signatures || [
        {
            label: t('Prepared By'),
            value: auth?.user?.name || t('System Administrator'),
            dateLabel: t('Date'),
            dateValue: reportDate,
        },
        {
            label: t('Verified / Approved By'),
            value: '',
            dateLabel: t('Date'),
            dateValue: '',
        },
    ];

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-center justify-end gap-2 print:hidden">
                <Button type="button" variant="outline" size="sm" onClick={handleExportCsv}>
                    <FileSpreadsheet className="h-4 w-4 mr-2" />
                    {t('Export CSV')}
                </Button>
                <Button type="button" variant="outline" size="sm" onClick={handlePrint}>
                    <Printer className="h-4 w-4 mr-2" />
                    {t('Print Document')}
                </Button>
                <Button type="button" size="sm" onClick={handleDownloadPdf} disabled={downloading}>
                    <Download className="h-4 w-4 mr-2" />
                    {downloading ? t('Generating PDF...') : t('Download PDF')}
                </Button>
            </div>

            <div className="bg-white rounded-lg border shadow-sm overflow-x-auto print:border-0 print:shadow-none">
                <div ref={docRef} className="min-w-[750px] p-4 print:p-0">
                    <CorporateDocumentTemplate
                        type="report"
                        documentTitle={documentTitle}
                        documentNumber={documentNumber}
                        documentNumberLabel={t('Report Ref')}
                        date={reportDate}
                        dateLabel={t('Generated On')}
                        leftBoxTitle={leftBoxTitle}
                        leftBoxFields={leftBoxFields}
                        rightBoxTitle={rightBoxTitle}
                        rightBoxFields={rightBoxFields}
                        columns={columns}
                        items={items}
                        totals={totals}
                        signatures={defaultSignatures}
                    />
                </div>
            </div>
        </div>
    );
}

