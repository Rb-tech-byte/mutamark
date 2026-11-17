import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import html2pdf from 'html2pdf.js';
import { formatCurrency } from '@/utils/helpers';
import JsBarcode from 'jsbarcode';

interface BarcodeProduct {
    id: number;
    name: string;
    sku: string;
    price: number;
}

export default function Print() {
    const { products = [], copies = {} } = usePage<{ products: BarcodeProduct[]; copies: Record<number, number> }>().props;
    const [barcodeImages, setBarcodeImages] = useState<Record<string, string>>({});

    useEffect(() => {
        const images: Record<string, string> = {};
        (products || []).forEach((product) => {
            const copyCount = Math.max(1, Number(copies?.[product.id] || 1));
            const codeValue = String(product.sku || product.id || '').trim();
            if (!codeValue) return;

            for (let i = 0; i < copyCount; i++) {
                try {
                    const canvas = document.createElement('canvas');
                    JsBarcode(canvas, codeValue, { format: 'CODE128', width: 2, height: 50, displayValue: true, fontSize: 11 });
                    images[`${product.id}-${i}`] = canvas.toDataURL('image/png');
                } catch {
                    // Fallback to empty if barcode value cannot be encoded
                }
            }
        });
        setBarcodeImages(images);

        const isDownload = new URLSearchParams(window.location.search).get('download') === 'pdf';
        if (isDownload && (products || []).length > 0) {
            setTimeout(() => {
                downloadPdf();
            }, 500);
        }
    }, []);

    const downloadPdf = async () => {
        try {
            const el = document.querySelector('.barcode-container') as HTMLElement | null;
            if (!el) return;
            await html2pdf()
                .set({
                    margin: 0.4,
                    filename: 'product-barcodes.pdf',
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
        <div className="min-h-screen bg-white p-6">
            <div className="print:hidden mb-4 flex items-center justify-end gap-2">
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

            <div className="barcode-container">
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '10px' }}>
                    {(products || []).map((product) =>
                        Array.from({ length: Math.max(1, Number(copies?.[product.id] || 1)) }).map((_, i) => (
                            <div
                                key={`${product.id}-${i}`}
                                style={{
                                    border: '1px solid #ccc',
                                    padding: '8px',
                                    textAlign: 'center',
                                    height: '130px',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    justifyContent: 'space-between',
                                    breakInside: 'avoid',
                                }}
                            >
                                <div style={{ fontSize: '9px', fontWeight: 'bold', lineHeight: '1.2', maxHeight: '22px', overflow: 'hidden' }}>
                                    {product.name}
                                </div>
                                {barcodeImages[`${product.id}-${i}`] ? (
                                    <img src={barcodeImages[`${product.id}-${i}`]} alt={product.sku} style={{ maxWidth: '100%', height: '56px', margin: '0 auto' }} />
                                ) : (
                                    <div style={{ fontSize: '10px', fontFamily: 'monospace' }}>{product.sku}</div>
                                )}
                                <div style={{ fontSize: '9px', color: '#444', fontWeight: 600 }}>{formatCurrency(product.price)}</div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
}
