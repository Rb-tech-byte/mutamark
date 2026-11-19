import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Printer, ArrowLeft } from 'lucide-react';
import { formatDate } from '@/utils/helpers';
import CorporateDocumentTemplate from '@/components/CorporateDocumentTemplate';
import { Transfer } from './types';

interface WaybillProps {
    transfer: Transfer;
}

export default function Waybill({ transfer }: WaybillProps) {
    const { t } = useTranslation();

    const handlePrint = () => {
        window.print();
    };

    const waybillItems =
        transfer.items && transfer.items.length > 0
            ? transfer.items.map((it) => ({
                name: it.product?.name || '',
                sku: it.product?.sku || '',
                dispatched: Number(it.dispatched_quantity || 0).toLocaleString(),
                received:
                    it.received_quantity !== null && it.received_quantity !== undefined
                        ? Number(it.received_quantity).toLocaleString()
                        : '—',
            }))
            : transfer.product
                ? [
                    {
                        name: transfer.product.name || t('Goods'),
                        sku: transfer.product.sku || '',
                        dispatched: Number(transfer.quantity || 0).toLocaleString(),
                        received: Number(transfer.quantity || 0).toLocaleString(),
                    },
                ]
                : [];

    const docNumber = transfer.transfer_number || `STO-2026-${String(transfer.id).padStart(5, '0')}`;

    return (
        <div className="min-h-screen bg-slate-100 py-8 px-4 print:p-0 print:bg-white text-slate-900">
            <Head title={`${docNumber} - Official Transit Waybill`} />

            {/* Print Action Bar */}
            <div className="max-w-[850px] mx-auto mb-4 flex justify-between items-center print:hidden">
                <Button variant="outline" size="sm" onClick={() => window.history.back()}>
                    <ArrowLeft className="h-4 w-4 mr-2" />
                    {t('Back')}
                </Button>
                <Button onClick={handlePrint} size="sm" className="flex items-center gap-2">
                    <Printer className="h-4 w-4" />
                    {t('Print Transit Waybill')}
                </Button>
            </div>

            <CorporateDocumentTemplate
                type="delivery_note"
                documentTitle={t('GOODS TRANSIT WAYBILL')}
                documentNumber={docNumber}
                documentNumberLabel={t('Waybill No')}
                date={formatDate(transfer.created_at)}
                dateLabel={t('Issue Date')}
                tertiaryNumber={transfer.status ? transfer.status.replace('_', ' ').toUpperCase() : undefined}
                tertiaryNumberLabel={t('Status')}
                leftBoxTitle={t('SHIPPED FROM (ORIGIN)')}
                leftBoxFields={[
                    { label: t('Origin Warehouse'), value: transfer.from_warehouse?.name || '—' },
                    { label: t('Warehouse Code'), value: transfer.from_warehouse?.code || '—' },
                    { label: t('Location'), value: transfer.from_warehouse?.city || 'Tanzania' },
                    { label: t('Driver Name'), value: transfer.driver_name || '—' },
                ]}
                rightBoxTitle={t('DELIVER TO (DESTINATION)')}
                rightBoxFields={[
                    { label: t('Destination Warehouse'), value: transfer.to_warehouse?.name || '—' },
                    { label: t('Vehicle Plate No.'), value: transfer.vehicle_number || '—' },
                    { label: t('Carrier / Logistics'), value: transfer.shipping_carrier || 'Internal Fleet' },
                    {
                        label: t('Expected Delivery'),
                        value: transfer.expected_delivery_date ? formatDate(transfer.expected_delivery_date) : '—',
                    },
                ]}
                columns={[
                    { key: 'index', label: '#', align: 'center', width: '36px', render: (_, idx) => idx + 1 },
                    {
                        key: 'name',
                        label: t('Item Description'),
                        align: 'left',
                        render: (it) => (
                            <div className="font-semibold text-slate-900">{it.name}</div>
                        ),
                    },
                    { key: 'dispatched', label: t('Qty Dispatched'), align: 'right', width: '115px' },
                    { key: 'received', label: t('Qty Received'), align: 'right', width: '115px' },
                ]}
                items={waybillItems}
                totals={[
                    {
                        label: t('TOTAL UNITS TRANSFERRED'),
                        value: Number(transfer.total_transferred_qty || transfer.quantity || 0).toLocaleString(),
                        isGrandTotal: true,
                    },
                ]}
                lowerLeftTitle={t('DELIVERY CONDITION')}
                lowerLeftContent={
                    <div className="space-y-1">
                        <div><strong>{t('Received in good condition')}:</strong> Yes / No</div>
                        <div><strong>{t('Damages / shortages noted')}:</strong> _______________________</div>
                    </div>
                }
                lowerRightTitle={t('NOTES')}
                lowerRightContent={
                    <div className="space-y-1">
                        <p>{t('Please inspect goods upon receipt and note any discrepancies before signing.')}</p>
                        <p>{t('Report any shortage or damage promptly.')}</p>
                    </div>
                }
                signatures={[
                    { label: t('Origin Dispatcher'), designation: transfer.from_warehouse?.name || '', dateLabel: t('Date') },
                    { label: t('Driver / Carrier'), value: transfer.driver_name || '', dateLabel: t('Date') },
                    { label: t('Receiving Officer'), designation: transfer.to_warehouse?.name || '', dateLabel: t('Date') },
                ]}
                verification={transfer.verification as any}
                verificationHash={transfer.verification?.verification_hash}
                footerSlogan={t('Thank you for your business.')}
            />
        </div>
    );
}
