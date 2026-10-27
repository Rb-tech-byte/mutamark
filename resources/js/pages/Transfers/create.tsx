import { useState } from 'react';
import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm, usePage } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { DatePicker } from "@/components/ui/date-picker";
import InputError from "@/components/ui/input-error";
import { Plus, Trash2, Truck, Boxes, MapPin } from 'lucide-react';
import { CreateTransferProps, CreateTransferFormData, TransfersIndexProps } from './types';

export default function Create({ onSuccess }: CreateTransferProps) {
    const { t } = useTranslation();
    const { warehouses, products, warehouseStocks } = usePage<TransfersIndexProps>().props;
    const [isMultiItem, setIsMultiItem] = useState(false);

    const { data, setData, post, processing, errors } = useForm<CreateTransferFormData>({
        from_warehouse: '',
        to_warehouse: '',
        product_id: '',
        quantity: '',
        date: new Date().toISOString().split('T')[0],
        expected_delivery_date: '',
        driver_name: '',
        vehicle_number: '',
        shipping_carrier: '',
        transit_notes: '',
        initial_status: 'in_transit',
        items: [],
    });

    const selectedFromWh = warehouses.find(w => w.id.toString() === data.from_warehouse);
    const selectedToWh = warehouses.find(w => w.id.toString() === data.to_warehouse);
    const availableToWarehouses = warehouses.filter(w => w.id.toString() !== data.from_warehouse);

    // Products available in source warehouse
    const availableProducts = warehouseStocks
        ?.filter(stock => stock.warehouse_id.toString() === data.from_warehouse && Number(stock.quantity) > 0)
        ?.map(stock => ({
            ...stock.product,
            available_quantity: stock.quantity
        })) || [];

    const handleFromWarehouseChange = (value: string) => {
        setData({
            ...data,
            from_warehouse: value,
            to_warehouse: data.to_warehouse === value ? '' : data.to_warehouse,
            product_id: '',
            quantity: '',
            items: [],
        });
    };

    const addItemRow = () => {
        setData('items', [
            ...data.items,
            { product_id: '', quantity: '1', from_bin_id: '', to_bin_id: '', notes: '' }
        ]);
    };

    const removeItemRow = (index: number) => {
        const next = [...data.items];
        next.splice(index, 1);
        setData('items', next);
    };

    const updateItemRow = (index: number, field: string, val: any) => {
        const next = [...data.items];
        next[index] = { ...next[index], [field]: val };
        setData('items', next);
    };

    const selectedProduct = availableProducts.find(p => p.id.toString() === data.product_id);
    const maxQuantity = selectedProduct?.available_quantity || 0;

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('transfers.store'), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle className="flex items-center gap-2 text-xl font-semibold">
                    <Truck className="h-5 w-5 text-primary" />
                    {t('New Stock Transfer Order (STO)')}
                </DialogTitle>
            </DialogHeader>

            <form onSubmit={submit} className="space-y-5">
                {/* Warehouse Routing */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl bg-muted/40 border border-border/60">
                    <div>
                        <Label htmlFor="from_warehouse" className="flex items-center gap-1.5 font-medium mb-1.5">
                            <MapPin className="h-4 w-4 text-amber-500" />
                            {t('Source Warehouse (Origin)')}
                        </Label>
                        <Select value={data.from_warehouse} onValueChange={handleFromWarehouseChange}>
                            <SelectTrigger>
                                <SelectValue placeholder={t('Select origin warehouse')} />
                            </SelectTrigger>
                            <SelectContent>
                                {warehouses.map((warehouse) => (
                                    <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                        <div className="flex flex-col text-left">
                                            <span className="font-medium">{warehouse.name}</span>
                                            {warehouse.code && <span className="text-xs text-muted-foreground">{warehouse.code} • {warehouse.city}</span>}
                                        </div>
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.from_warehouse} />
                    </div>

                    <div>
                        <Label htmlFor="to_warehouse" className="flex items-center gap-1.5 font-medium mb-1.5">
                            <MapPin className="h-4 w-4 text-emerald-500" />
                            {t('Destination Warehouse')}
                        </Label>
                        <Select
                            value={data.to_warehouse}
                            onValueChange={(value) => setData('to_warehouse', value)}
                            disabled={!data.from_warehouse}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder={data.from_warehouse ? t('Select destination warehouse') : t('Select origin first')} />
                            </SelectTrigger>
                            <SelectContent>
                                {availableToWarehouses.map((warehouse) => (
                                    <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                        <div className="flex flex-col text-left">
                                            <span className="font-medium">{warehouse.name}</span>
                                            {warehouse.code && <span className="text-xs text-muted-foreground">{warehouse.code} • {warehouse.city}</span>}
                                        </div>
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.to_warehouse} />
                    </div>
                </div>

                {/* Single vs Multi Item Toggle */}
                <div className="flex items-center justify-between border-b border-border/60 pb-2">
                    <Label className="text-sm font-semibold flex items-center gap-2">
                        <Boxes className="h-4 w-4 text-primary" />
                        {t('Transfer Items')}
                    </Label>
                    <div className="flex gap-2 text-xs">
                        <Button
                            type="button"
                            variant={!isMultiItem ? "default" : "outline"}
                            size="sm"
                            onClick={() => { setIsMultiItem(false); setData('items', []); }}
                        >
                            {t('Single Item')}
                        </Button>
                        <Button
                            type="button"
                            variant={isMultiItem ? "default" : "outline"}
                            size="sm"
                            disabled={!data.from_warehouse}
                            onClick={() => {
                                setIsMultiItem(true);
                                if (data.items.length === 0) addItemRow();
                            }}
                        >
                            {t('Multi-Item Order')}
                        </Button>
                    </div>
                </div>

                {!isMultiItem ? (
                    /* Single Product Selector */
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="product_id">{t('Product to Transfer')}</Label>
                            <Select
                                value={data.product_id}
                                onValueChange={(value) => setData('product_id', value)}
                                disabled={!data.from_warehouse}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={data.from_warehouse ? t('Select product') : t('Select warehouse first')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {availableProducts.map((product) => (
                                        <SelectItem key={product.id} value={product.id.toString()}>
                                            {product.name} ({t('Available')}: {product.available_quantity})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.product_id} />
                        </div>

                        <div>
                            <Label htmlFor="quantity">{t('Transfer Quantity')}</Label>
                            <Input
                                id="quantity"
                                type="number"
                                step="1"
                                min="1"
                                max={maxQuantity}
                                value={data.quantity}
                                onChange={(e) => setData('quantity', e.target.value)}
                                placeholder={selectedProduct ? `Available: ${maxQuantity}` : t('Select product first')}
                                disabled={!data.product_id}
                                required
                            />
                            <InputError message={errors.quantity} />
                        </div>
                    </div>
                ) : (
                    /* Multi-Line Item Table */
                    <div className="space-y-3">
                        <div className="border rounded-lg overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-muted text-muted-foreground text-xs uppercase font-semibold">
                                    <tr>
                                        <th className="p-2 text-left">{t('Product')}</th>
                                        <th className="p-2 w-28 text-left">{t('Qty')}</th>
                                        {selectedFromWh?.locations && selectedFromWh.locations.length > 0 && (
                                            <th className="p-2 text-left">{t('Origin Bin')}</th>
                                        )}
                                        <th className="p-2 w-12 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {data.items.map((row, idx) => (
                                        <tr key={idx} className="hover:bg-muted/20">
                                            <td className="p-2">
                                                <Select
                                                    value={row.product_id}
                                                    onValueChange={(val) => updateItemRow(idx, 'product_id', val)}
                                                >
                                                    <SelectTrigger className="h-9">
                                                        <SelectValue placeholder={t('Select item')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {availableProducts.map((p) => (
                                                            <SelectItem key={p.id} value={p.id.toString()}>
                                                                {p.name} (Stock: {p.available_quantity})
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </td>
                                            <td className="p-2">
                                                <Input
                                                    type="number"
                                                    min="1"
                                                    value={row.quantity}
                                                    onChange={(e) => updateItemRow(idx, 'quantity', e.target.value)}
                                                    className="h-9"
                                                />
                                            </td>
                                            {selectedFromWh?.locations && selectedFromWh.locations.length > 0 && (
                                                <td className="p-2">
                                                    <Select
                                                        value={row.from_bin_id || ''}
                                                        onValueChange={(val) => updateItemRow(idx, 'from_bin_id', val)}
                                                    >
                                                        <SelectTrigger className="h-9">
                                                            <SelectValue placeholder={t('Bin Location')} />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {selectedFromWh.locations.map((bin) => (
                                                                <SelectItem key={bin.id} value={bin.id.toString()}>
                                                                    {bin.bin_code} ({bin.zone})
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </td>
                                            )}
                                            <td className="p-2 text-center">
                                                {data.items.length > 1 && (
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        className="h-8 w-8 text-destructive hover:bg-destructive/10"
                                                        onClick={() => removeItemRow(idx)}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            className="text-xs flex items-center gap-1.5"
                            onClick={addItemRow}
                        >
                            <Plus className="h-3.5 w-3.5" />
                            {t('Add Product Line')}
                        </Button>
                    </div>
                )}

                {/* Transit & Dispatch Logistics */}
                <div className="space-y-3 pt-2 border-t border-border/60">
                    <Label className="text-sm font-semibold flex items-center gap-2">
                        <Truck className="h-4 w-4 text-primary" />
                        {t('Transit Logistics & Transport Details')}
                    </Label>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <Label htmlFor="date" className="text-xs">{t('Transfer Date')}</Label>
                            <DatePicker
                                value={data.date}
                                onChange={(value) => setData('date', value)}
                                placeholder={t('Transfer date')}
                            />
                            <InputError message={errors.date} />
                        </div>
                        <div>
                            <Label htmlFor="expected_delivery_date" className="text-xs">{t('Expected Delivery Date')}</Label>
                            <DatePicker
                                value={data.expected_delivery_date}
                                onChange={(value) => setData('expected_delivery_date', value)}
                                placeholder={t('Estimated arrival')}
                            />
                            <InputError message={errors.expected_delivery_date} />
                        </div>
                        <div>
                            <Label htmlFor="initial_status" className="text-xs">{t('Initial Workflow Status')}</Label>
                            <Select
                                value={data.initial_status}
                                onValueChange={(val: any) => setData('initial_status', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="in_transit">{t('Dispatch (In Transit)')}</SelectItem>
                                    <SelectItem value="draft">{t('Save as Draft')}</SelectItem>
                                    <SelectItem value="completed">{t('Instant Delivery')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <Label htmlFor="driver_name" className="text-xs">{t('Driver / Carrier Name')}</Label>
                            <Input
                                id="driver_name"
                                value={data.driver_name}
                                onChange={(e) => setData('driver_name', e.target.value)}
                                placeholder="e.g. Musa Hamisi"
                            />
                        </div>
                        <div>
                            <Label htmlFor="vehicle_number" className="text-xs">{t('Vehicle Plate Number')}</Label>
                            <Input
                                id="vehicle_number"
                                value={data.vehicle_number}
                                onChange={(e) => setData('vehicle_number', e.target.value)}
                                placeholder="e.g. T 482 DXB"
                            />
                        </div>
                        <div>
                            <Label htmlFor="shipping_carrier" className="text-xs">{t('Carrier / Logistics Co.')}</Label>
                            <Input
                                id="shipping_carrier"
                                value={data.shipping_carrier}
                                onChange={(e) => setData('shipping_carrier', e.target.value)}
                                placeholder="e.g. Internal Fleet / DHL"
                            />
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="transit_notes" className="text-xs">{t('Transit & Route Notes')}</Label>
                        <Input
                            id="transit_notes"
                            value={data.transit_notes}
                            onChange={(e) => setData('transit_notes', e.target.value)}
                            placeholder={t('Handling instructions, seals, or route information')}
                        />
                    </div>
                </div>

                <div className="flex justify-end gap-2 pt-3 border-t">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing} className="px-5">
                        {processing ? t('Saving...') : t('Create Transfer Order')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}