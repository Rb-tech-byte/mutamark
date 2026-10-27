import { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Printer, ArrowLeft, ShieldCheck, QrCode } from 'lucide-react';
import { formatDate } from '@/utils/helpers';
import { Transfer } from './types';

interface WaybillProps {
    transfer: Transfer;
}

export default function Waybill({ transfer }: WaybillProps) {
    const { t } = useTranslation();

    const handlePrint = () => {
        window.print();
    };

    return (
        <div className="min-h-screen bg-slate-100 dark:bg-slate-900 py-8 px-4 print:p-0 print:bg-white text-slate-900">
            <Head title={`${transfer.transfer_number || 'STO'} - Official Transit Waybill`} />

            {/* Print Action Bar */}
            <div className="max-w-4xl mx-auto mb-6 flex justify-between items-center print:hidden">
                <Button variant="outline" size="sm" onClick={() => window.history.back()}>
                    <ArrowLeft className="h-4 w-4 mr-2" />
                    {t('Back')}
                </Button>
                <Button onClick={handlePrint} className="flex items-center gap-2">
                    <Printer className="h-4 w-4" />
                    {t('Print Transit Waybill')}
                </Button>
            </div>

            {/* Official Waybill Sheet (A4) */}
            <div className="max-w-4xl mx-auto bg-white p-8 md:p-12 shadow-lg print:shadow-none print:p-0 border border-slate-200 print:border-none rounded-lg">
                {/* Header */}
                <div className="flex justify-between items-start border-b border-slate-200 pb-6">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-slate-900">MUTAMARK ENTERPRISE ERP</h1>
                        <p className="text-xs text-slate-500 uppercase tracking-widest mt-1">
                            {t('Official Inter-Warehouse Goods Transit Waybill')}
                        </p>
                        <p className="text-xs text-slate-600 mt-2">
                            United Republic of Tanzania • Logistics & Supply Chain Division
                        </p>
                    </div>
                    <div className="text-right">
                        <div className="text-xl font-mono font-bold text-slate-800">
                            {transfer.transfer_number || `STO-2026-${String(transfer.id).padStart(5, '0')}`}
                        </div>
                        <div className="text-xs text-slate-500 mt-1">
                            {t('Issue Date')}: {formatDate(transfer.created_at)}
                        </div>
                        <div className="inline-block mt-2 px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider bg-slate-100 text-slate-800 border">
                            {transfer.status.replace('_', ' ')}
                        </div>
                    </div>
                </div>

                {/* Origin vs Destination Routing Grid */}
                <div className="grid grid-cols-2 gap-6 my-6 p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <span className="font-bold text-slate-500 uppercase tracking-wider block mb-1">
                            {t('Shipped From (Origin)')}
                        </span>
                        <div className="text-sm font-bold text-slate-900">{transfer.from_warehouse?.name}</div>
                        {transfer.from_warehouse?.code && <div className="text-slate-600 font-mono">Code: {transfer.from_warehouse.code}</div>}
                        <div className="text-slate-600">{transfer.from_warehouse?.city || 'Tanzania'}</div>
                    </div>
                    <div>
                        <span className="font-bold text-slate-500 uppercase tracking-wider block mb-1">
                            {t('Deliver To (Destination)')}
                        </span>
                        <div className="text-sm font-bold text-slate-900">{transfer.to_warehouse?.name}</div>
                        {transfer.to_warehouse?.code && <div className="text-slate-600 font-mono">Code: {transfer.to_warehouse.code}</div>}
                        <div className="text-slate-600">{transfer.to_warehouse?.city || 'Tanzania'}</div>
                    </div>
                </div>

                {/* Logistics Info Bar */}
                <div className="grid grid-cols-4 gap-4 p-3 bg-slate-50/60 rounded border border-slate-200 text-xs mb-6">
                    <div>
                        <span className="text-slate-500 block">{t('Driver Name')}</span>
                        <span className="font-medium">{transfer.driver_name || '-'}</span>
                    </div>
                    <div>
                        <span className="text-slate-500 block">{t('Vehicle Plate No.')}</span>
                        <span className="font-medium font-mono">{transfer.vehicle_number || '-'}</span>
                    </div>
                    <div>
                        <span className="text-slate-500 block">{t('Carrier / Logistics')}</span>
                        <span className="font-medium">{transfer.shipping_carrier || 'Internal Fleet'}</span>
                    </div>
                    <div>
                        <span className="text-slate-500 block">{t('Expected Delivery')}</span>
                        <span className="font-medium">
                            {transfer.expected_delivery_date ? formatDate(transfer.expected_delivery_date) : '-'}
                        </span>
                    </div>
                </div>

                {/* Line Items Table */}
                <table className="w-full text-left text-xs mb-8 border border-slate-200">
                    <thead className="bg-slate-100 text-slate-700 uppercase font-semibold">
                        <tr className="border-b border-slate-200">
                            <th className="p-2.5 w-12">#</th>
                            <th className="p-2.5">{t('Item Description')}</th>
                            <th className="p-2.5">{t('SKU')}</th>
                            <th className="p-2.5 text-right w-28">{t('Dispatched')}</th>
                            <th className="p-2.5 text-right w-28">{t('Received')}</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200">
                        {transfer.items && transfer.items.length > 0 ? (
                            transfer.items.map((it, idx) => (
                                <tr key={it.id}>
                                    <td className="p-2.5 text-slate-500">{idx + 1}</td>
                                    <td className="p-2.5 font-medium text-slate-900">{it.product?.name}</td>
                                    <td className="p-2.5 font-mono text-slate-600">{it.product?.sku || '-'}</td>
                                    <td className="p-2.5 text-right font-semibold">{Number(it.dispatched_quantity).toLocaleString()}</td>
                                    <td className="p-2.5 text-right">
                                        {it.received_quantity !== null && it.received_quantity !== undefined 
                                            ? Number(it.received_quantity).toLocaleString() 
                                            : '-'}
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td className="p-2.5 text-slate-500">1</td>
                                <td className="p-2.5 font-medium text-slate-900">{transfer.product?.name || t('Goods')}</td>
                                <td className="p-2.5 font-mono text-slate-600">{transfer.product?.sku || '-'}</td>
                                <td className="p-2.5 text-right font-semibold">{Number(transfer.quantity).toLocaleString()}</td>
                                <td className="p-2.5 text-right">{Number(transfer.quantity).toLocaleString()}</td>
                            </tr>
                        )}
                    </tbody>
                    <tfoot className="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colSpan={3} className="p-2.5 text-right uppercase">{t('Total Units Transferred')}:</td>
                            <td className="p-2.5 text-right font-mono">
                                {Number(transfer.total_transferred_qty || transfer.quantity).toLocaleString()}
                            </td>
                            <td className="p-2.5 text-right font-mono">
                                {transfer.status === 'received' || transfer.status === 'completed'
                                    ? Number(transfer.total_transferred_qty || transfer.quantity).toLocaleString()
                                    : '-'}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                {/* Cryptographic Seal & Verification Block */}
                {transfer.verification && (
                    <div className="p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs mb-8 flex items-center justify-between">
                        <div className="space-y-1">
                            <div className="flex items-center gap-1.5 font-bold text-slate-800">
                                <ShieldCheck className="h-4 w-4 text-emerald-600" />
                                {t('Cryptographic Verification Seal (SHA-256)')}
                            </div>
                            <div className="font-mono text-[11px] text-slate-600">
                                Hash: {transfer.verification.verification_hash}
                            </div>
                            <div className="text-[11px] text-slate-500">
                                {t('Scan to verify authentic delivery seal against Mutamark Central Ledger.')}
                            </div>
                        </div>
                        <div className="p-2 bg-white rounded border border-slate-200 shrink-0 text-center">
                            <QrCode className="h-12 w-12 text-slate-800" />
                            <span className="text-[9px] font-mono text-slate-500">VERIFIED</span>
                        </div>
                    </div>
                )}

                {/* Sign-off Handover Blocks */}
                <div className="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200 text-xs text-slate-700">
                    <div className="space-y-8">
                        <div>
                            <span className="font-bold block">{t('Origin Dispatcher')}</span>
                            <span className="text-[11px] text-slate-500">{transfer.from_warehouse?.name}</span>
                        </div>
                        <div className="border-b border-dashed border-slate-400"></div>
                        <div className="text-[11px] text-slate-500">{t('Signature & Stamp')}</div>
                    </div>

                    <div className="space-y-8">
                        <div>
                            <span className="font-bold block">{t('Driver / Carrier')}</span>
                            <span className="text-[11px] text-slate-500">{transfer.driver_name || t('Transport Lead')}</span>
                        </div>
                        <div className="border-b border-dashed border-slate-400"></div>
                        <div className="text-[11px] text-slate-500">{t('Signature & Date')}</div>
                    </div>

                    <div className="space-y-8">
                        <div>
                            <span className="font-bold block">{t('Destination Receiving Officer')}</span>
                            <span className="text-[11px] text-slate-500">{transfer.to_warehouse?.name}</span>
                        </div>
                        <div className="border-b border-dashed border-slate-400"></div>
                        <div className="text-[11px] text-slate-500">{t('Signature & Stamp')}</div>
                    </div>
                </div>
            </div>
        </div>
    );
}
