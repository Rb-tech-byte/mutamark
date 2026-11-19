import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { jsPDF } from 'jspdf';
import JsBarcode from 'jsbarcode';
import { formatCurrency } from '@/utils/helpers';

interface BarcodeProduct {
    id: number;
    name: string;
    sku: string;
    price: number;
}

interface BarcodeGraphic {
    dataUrl: string;
    width: number;
    height: number;
}

interface LabelEntry {
    key: string;
    product: BarcodeProduct;
    codeValue: string;
}

export default function Print() {
    const page = usePage<{ products: BarcodeProduct[]; copies: Record<number | string, number> }>();
    const pageProps = page.props;
    const products = Array.isArray(pageProps.products) ? pageProps.products : [];
    const copies = pageProps.copies || {};

    const [barcodeGraphics, setBarcodeGraphics] = useState<Record<number, BarcodeGraphic>>({});
    const [ready, setReady] = useState(false);
    const [downloading, setDownloading] = useState(false);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const autoActionTriggered = useRef(false);

    const labels = useMemo<LabelEntry[]>(() => {
        const list: LabelEntry[] = [];
        products.forEach((product) => {
            const rawCopies = copies[product.id] ?? copies[String(product.id)] ?? 1;
            const copyCount = Math.max(1, Math.min(200, Number(rawCopies) || 1));
            const codeValue = String(product.sku || product.id || '').trim();
            for (let i = 0; i < copyCount; i++) {
                list.push({
                    key: `${product.id}-${i}`,
                    product,
                    codeValue,
                });
            }
        });
        return list;
    }, [products, copies]);

    useEffect(() => {
        const generated: Record<number, BarcodeGraphic> = {};

        products.forEach((product) => {
            const codeValue = String(product.sku || product.id || '').trim();
            if (!codeValue) return;

            try {
                const canvas = document.createElement('canvas');
                JsBarcode(canvas, codeValue, {
                    format: 'CODE128',
                    width: 2,
                    height: 55,
                    displayValue: true,
                    fontSize: 12,
                    margin: 6,
                    background: '#ffffff',
                    lineColor: '#000000',
                });
                generated[product.id] = {
                    dataUrl: canvas.toDataURL('image/png', 1.0),
                    width: canvas.width,
                    height: canvas.height,
                };
            } catch {
                // Fallback to plain SKU text when barcode cannot be encoded
            }
        });

        setBarcodeGraphics(generated);
        setReady(true);
    }, [products]);

    const downloadPdf = useCallback(() => {
        if (labels.length === 0) return;

        setDownloading(true);
        setErrorMessage(null);

        try {
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4',
            });

            const pageWidth = 210;
            const pageHeight = 297;
            const marginX = 10;
            const marginY = 10;
            const cols = 3;
            const rows = 7;
            const gapX = 4;
            const gapY = 4;
            const cardW = (pageWidth - marginX * 2 - gapX * (cols - 1)) / cols;
            const cardH = (pageHeight - marginY * 2 - gapY * (rows - 1)) / rows;
            const perPage = cols * rows;

            labels.forEach((entry, index) => {
                if (index > 0 && index % perPage === 0) {
                    doc.addPage();
                }

                const slot = index % perPage;
                const col = slot % cols;
                const row = Math.floor(slot / cols);
                const x = marginX + col * (cardW + gapX);
                const y = marginY + row * (cardH + gapY);

                // Label border
                doc.setDrawColor(200, 200, 200);
                doc.setLineWidth(0.25);
                doc.roundedRect(x, y, cardW, cardH, 1.5, 1.5, 'S');

                // Product name (max 2 lines)
                doc.setTextColor(17, 24, 39);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(7.5);
                const nameLines = doc.splitTextToSize(String(entry.product.name || ''), cardW - 5).slice(0, 2);
                doc.text(nameLines, x + cardW / 2, y + 4.5, { align: 'center', lineHeightFactor: 1.15 });

                // Crisp lossless PNG barcode preserving aspect ratio
                const graphic = barcodeGraphics[entry.product.id];
                const barcodeTop = y + 10.5;
                const maxBarcodeW = cardW - 5;
                const maxBarcodeH = cardH - 18;

                if (graphic && graphic.width > 0 && graphic.height > 0) {
                    const ratio = graphic.width / graphic.height;
                    let drawW = maxBarcodeW;
                    let drawH = drawW / ratio;
                    if (drawH > maxBarcodeH) {
                        drawH = maxBarcodeH;
                        drawW = drawH * ratio;
                    }
                    const imgX = x + (cardW - drawW) / 2;
                    const imgY = barcodeTop + (maxBarcodeH - drawH) / 2;
                    doc.addImage(graphic.dataUrl, 'PNG', imgX, imgY, drawW, drawH, undefined, 'NONE');
                } else {
                    doc.setFont('courier', 'normal');
                    doc.setFontSize(8);
                    doc.text(entry.codeValue, x + cardW / 2, barcodeTop + maxBarcodeH / 2, { align: 'center' });
                }

                // Price footer
                const formattedPrice = formatCurrency(entry.product.price || 0, pageProps);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(8);
                doc.setTextColor(55, 65, 81);
                doc.text(formattedPrice, x + cardW / 2, y + cardH - 2.8, { align: 'center' });
            });

            doc.save('product-barcodes.pdf');
        } catch (error) {
            console.error('Barcode PDF generation failed:', error);
            setErrorMessage('Could not generate the barcode PDF. Please use the Print button instead.');
        } finally {
            setDownloading(false);
        }
    }, [labels, barcodeGraphics, pageProps]);

    useEffect(() => {
        if (!ready || labels.length === 0 || autoActionTriggered.current) return;

        const searchParams = new URLSearchParams(window.location.search);
        const shouldDownload = searchParams.get('download') === 'pdf';
        const shouldAutoPrint = searchParams.get('autoprint') === '1';

        if (!shouldDownload && !shouldAutoPrint) return;

        autoActionTriggered.current = true;
        const rafId = window.requestAnimationFrame(() => {
            window.setTimeout(() => {
                if (shouldDownload) {
                    downloadPdf();
                } else if (shouldAutoPrint) {
                    window.print();
                }
            }, 250);
        });

        return () => window.cancelAnimationFrame(rafId);
    }, [ready, labels.length, downloadPdf]);

    return (
        <div className="min-h-screen bg-white p-6 text-slate-900">
            <Head title="Print Product Barcodes" />

            <style>{`
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 10mm;
                    }
                    body {
                        background: #ffffff !important;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .barcode-print-page {
                        padding: 0 !important;
                    }
                }
            `}</style>

            <div className="print:hidden mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h1 className="text-base font-semibold text-slate-900">Product Barcodes</h1>
                    <p className="text-xs text-slate-500">
                        {labels.length} {labels.length === 1 ? 'label' : 'labels'} ready for printing or PDF download
                    </p>
                </div>
                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={() => window.print()}
                        disabled={labels.length === 0}
                        className="rounded bg-slate-900 px-3.5 py-1.5 text-xs font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                    >
                        Print Barcodes
                    </button>
                    <button
                        type="button"
                        onClick={downloadPdf}
                        disabled={labels.length === 0 || downloading}
                        className="rounded border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                    >
                        {downloading ? 'Generating PDF...' : 'Download PDF'}
                    </button>
                </div>
            </div>

            {errorMessage && (
                <div className="print:hidden mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                    {errorMessage}
                </div>
            )}

            {labels.length === 0 ? (
                <div className="py-16 text-center text-sm text-slate-500">
                    No products selected for barcode printing.
                </div>
            ) : (
                <div className="barcode-container barcode-print-page">
                    <div
                        style={{
                            display: 'grid',
                            gridTemplateColumns: 'repeat(3, minmax(0, 1fr))',
                            gap: '10px',
                        }}
                    >
                        {labels.map((entry) => {
                            const graphic = barcodeGraphics[entry.product.id];
                            return (
                                <div
                                    key={entry.key}
                                    style={{
                                        border: '1px solid #cbd5e1',
                                        borderRadius: '4px',
                                        padding: '8px',
                                        textAlign: 'center',
                                        height: '132px',
                                        display: 'flex',
                                        flexDirection: 'column',
                                        justifyContent: 'space-between',
                                        alignItems: 'center',
                                        breakInside: 'avoid',
                                        pageBreakInside: 'avoid',
                                        backgroundColor: '#ffffff',
                                    }}
                                >
                                    <div
                                        style={{
                                            fontSize: '10px',
                                            fontWeight: 700,
                                            lineHeight: '1.2',
                                            maxHeight: '24px',
                                            overflow: 'hidden',
                                            width: '100%',
                                            color: '#0f172a',
                                        }}
                                    >
                                        {entry.product.name}
                                    </div>
                                    {graphic ? (
                                        <img
                                            src={graphic.dataUrl}
                                            alt={entry.codeValue}
                                            style={{
                                                maxWidth: '100%',
                                                maxHeight: '64px',
                                                objectFit: 'contain',
                                                imageRendering: 'crisp-edges',
                                            }}
                                        />
                                    ) : (
                                        <div style={{ fontSize: '11px', fontFamily: 'monospace' }}>
                                            {entry.codeValue}
                                        </div>
                                    )}
                                    <div style={{ fontSize: '10px', color: '#334155', fontWeight: 600 }}>
                                        {formatCurrency(entry.product.price || 0, pageProps)}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}
