import { useState, useRef } from 'react';
import { Head, useForm, usePage } from "@inertiajs/react";
import { Download, Upload, FileSpreadsheet } from "lucide-react";
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useFormFields } from '@/hooks/useFormFields';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { MultiSelectEnhanced } from "@/components/ui/multi-select-enhanced";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Dialog } from "@/components/ui/dialog";
import MediaPicker from "@/components/MediaPicker";
import InputError from "@/components/ui/input-error";
import { RichTextEditor } from "@/components/ui/rich-text-editor";
import { CreateItemPageProps, ItemFormData } from './types';

export default function Create() {
    const { t } = useTranslation();
    const { taxes, categories, units, warehouses } = usePage<CreateItemPageProps>().props;
    const [activeTab, setActiveTab] = useState('details');
    const [showBulkImport, setShowBulkImport] = useState(false);
    const [fileTypeError, setFileTypeError] = useState<string>('');
    const [detectedFileInfo, setDetectedFileInfo] = useState<string>('');
    const fileInputRef = useRef<HTMLInputElement>(null);

    const importForm = useForm<{ file: File | null; update_existing: boolean }>({
        file: null,
        update_existing: false,
    });

    const handleImportFileChange = (file: File | null) => {
        setFileTypeError('');
        setDetectedFileInfo('');
        if (!file) {
            importForm.setData('file', null);
            return;
        }
        const ext = file.name.split('.').pop()?.toLowerCase() || '';
        const allowedExts = ['csv', 'xlsx', 'xls'];
        if (!allowedExts.includes(ext)) {
            importForm.setData('file', null);
            if (fileInputRef.current) {
                fileInputRef.current.value = '';
            }
            setFileTypeError(
                t('Unsupported file type (.{{ext}}). Required file types: .csv, .xlsx, or .xls', { ext: ext || 'unknown' })
            );
            return;
        }
        const typeLabel = ext === 'csv' ? 'CSV (.csv)' : `Excel (.${ext})`;
        const sizeKb = (file.size / 1024).toFixed(1);
        setDetectedFileInfo(`${typeLabel} • ${sizeKb} KB`);
        importForm.setData('file', file);
    };

    const submitImport = (e: React.FormEvent) => {
        e.preventDefault();
        if (!importForm.data.file || fileTypeError) return;
        importForm.post(route('product-service.items.import'), {
            forceFormData: true,
            onSuccess: () => {
                importForm.reset();
                setDetectedFileInfo('');
                setFileTypeError('');
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
        });
    };

    useFlashMessages();

    const { data, setData, post, processing, errors } = useForm<ItemFormData>({
        name: '',
        sku: '',
        tax_ids: [],
        tax_type: 'exclusive',
        category_id: '',
        description: '',
        long_description: '',
        sale_price: '',
        purchase_price: '',
        unit: '',
        quantity: '',
        image: '',
        images: [],
        warehouse_id: '',
        type: 'product',
        has_warranty: 0,
        warranty_type: '',
        warranty_duration: '',
        warranty_terms: ''
    });

    // Get custom fields using useFormFields hook
    const customFields = useFormFields('getCustomFields', { ...data, module: 'ProductService', sub_module: 'Items' }, setData, errors, 'create', t);

    // AI hook for short description
    const descriptionAI = useFormFields('aiField', data, setData, errors, 'create', 'description', 'Short Description', 'productservice', 'item');
    // Inventory fields hook
    const inventoryFields = useFormFields('inventoryFields', data, setData, errors, 'create');
    // Warranty fields hook
    const warrantyFields = useFormFields('warrantyFields', data, setData, errors, 'create');

    const validateDetailsTab = () => {
        return data.name.trim() !== '' &&
               data.sku.trim() !== '';
    };

    const validatePricingTab = () => {
        return data.sale_price.trim() !== '' &&
               data.purchase_price.trim() !== '';
    };

    const nextTab = () => {
        if (activeTab === 'details') {
            if (!validateDetailsTab()) {
                return; // Don't proceed if validation fails
            }
            setActiveTab('pricing');
        }
        else if (activeTab === 'pricing') {
            if (!validatePricingTab()) {
                return;
            }
            setActiveTab('media');
        }
        else if (activeTab === 'media') setActiveTab('warehouse');
    };

    const prevTab = () => {
        if (activeTab === 'pricing') setActiveTab('details');
        else if (activeTab === 'media') setActiveTab('pricing');
        else if (activeTab === 'warehouse') setActiveTab('media');
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('product-service.items.store'), {
            transform: (data) => {
                if (data.type === 'service') {
                    const { quantity, warehouse_id, ...serviceData } = data;
                    return serviceData;
                }

                return data;
            }
        });
    };

    return (
        <Dialog>
            <AuthenticatedLayout
                breadcrumbs={[
                    {label: t('Items'), url: route('product-service.items.index')},
                    {label: t('Create')}
                ]}
                pageTitle={
                    <div className="flex items-center justify-between">
                        <span>{t('Create Item')}</span>
                    </div>
                }
                pageActions={
                    <div className="flex items-center gap-2">
                        <Button asChild variant="outline" size="sm" className="gap-1.5">
                            <a href={route('product-service.items.import.template', { format: 'xlsx' })} download>
                                <Download className="h-4 w-4" />
                                {t('Excel Template')}
                            </a>
                        </Button>
                        <Button asChild variant="outline" size="sm" className="gap-1.5">
                            <a href={route('product-service.items.import.template', { format: 'csv' })} download>
                                <Download className="h-4 w-4" />
                                {t('CSV Template')}
                            </a>
                        </Button>
                        <Button
                            type="button"
                            variant={showBulkImport ? "secondary" : "default"}
                            size="sm"
                            className="gap-1.5"
                            onClick={() => setShowBulkImport((prev) => !prev)}
                        >
                            <Upload className="h-4 w-4" />
                            {t('Bulk Import')}
                        </Button>
                    </div>
                }
            >
                <Head title={t('Create Item')} />

                {showBulkImport && (
                    <Card className="mb-6 border-primary/30 bg-muted/20">
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <FileSpreadsheet className="h-5 w-5 text-primary" />
                                {t('Bulk Import Multiple Products & Services (CSV / Excel)')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submitImport} className="space-y-4">
                                <div className="text-xs text-muted-foreground space-y-1">
                                    <p>
                                        {t('Download the Excel (.xlsx) or CSV (.csv) template, fill in multiple products/services, and upload it below. Accepted formats: .csv, .xlsx, .xls (max 10 MB).')}
                                    </p>
                                    <p>
                                        <span className="font-medium text-foreground">{t('Columns')}:</span>{' '}
                                        <code>name*</code>, <code>sku*</code>, <code>type</code> (product/service/part),{' '}
                                        <code>sale_price*</code>, <code>purchase_price*</code>, <code>category</code>,{' '}
                                        <code>unit</code>, <code>taxes</code>, <code>tax_type</code> (exclusive/inclusive), <code>warehouse</code>, <code>quantity</code>,{' '}
                                        <code>description</code>, <code>long_description</code>
                                    </p>
                                </div>

                                <div className="flex flex-col sm:flex-row sm:items-end gap-4">
                                    <div className="flex-1 space-y-1.5">
                                        <Label htmlFor="bulk-import-file">{t('Spreadsheet File (.csv, .xlsx, .xls)')}</Label>
                                        <Input
                                            id="bulk-import-file"
                                            ref={fileInputRef}
                                            type="file"
                                            accept=".csv,.xlsx,.xls,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                            onChange={(e) => handleImportFileChange(e.target.files?.[0] || null)}
                                        />
                                        {detectedFileInfo && !fileTypeError && (
                                            <p className="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                                {t('Detected')}: {detectedFileInfo}
                                            </p>
                                        )}
                                        <InputError message={fileTypeError || importForm.errors.file} />
                                    </div>

                                    <div className="flex items-center gap-2 pb-2">
                                        <input
                                            id="update-existing-sku"
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-gray-300"
                                            checked={importForm.data.update_existing}
                                            onChange={(e) => importForm.setData('update_existing', e.target.checked)}
                                        />
                                        <Label htmlFor="update-existing-sku" className="text-sm font-normal cursor-pointer">
                                            {t('Update existing if SKU matches')}
                                        </Label>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <Button asChild type="button" variant="outline" size="sm" className="gap-1.5">
                                            <a href={route('product-service.items.import.template', { format: 'xlsx' })} download>
                                                <Download className="h-4 w-4" />
                                                {t('.xlsx')}
                                            </a>
                                        </Button>
                                        <Button asChild type="button" variant="outline" size="sm" className="gap-1.5">
                                            <a href={route('product-service.items.import.template', { format: 'csv' })} download>
                                                <Download className="h-4 w-4" />
                                                {t('.csv')}
                                            </a>
                                        </Button>
                                        <Button
                                            type="submit"
                                            size="sm"
                                            disabled={!importForm.data.file || !!fileTypeError || importForm.processing}
                                            className="gap-1.5"
                                        >
                                            <Upload className="h-4 w-4" />
                                            {importForm.processing ? t('Importing...') : t('Import Products')}
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardContent>
                        <form onSubmit={submit} className="pt-5">
                            <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                                <TabsContent value="details" className="space-y-6 mt-6">
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="type">{t('Item Type')}</Label>
                                            <Select value={data.type || ''} onValueChange={(value) => {
                                                setData('type', value);
                                                if (value === 'service') {
                                                    setData('quantity', '');
                                                    setData('warehouse_id', '');
                                                }
                                            }}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Select Type')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="product">{t('Product')}</SelectItem>
                                                    <SelectItem value="service">{t('Service')}</SelectItem>
                                                    <SelectItem value="part">{t('Part')}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.type} />
                                        </div>
                                        <div>
                                            <Label htmlFor="name">{t('Name')}</Label>
                                            <Input
                                                id="name"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                placeholder={t('Enter Name')}
                                                required
                                            />
                                            <InputError message={errors.name} />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="sku">{t('SKU')}</Label>
                                            <div className="flex gap-2">
                                                <Input
                                                    id="sku"
                                                    value={data.sku}
                                                    onChange={(e) => setData('sku', e.target.value)}
                                                    placeholder={t('Enter SKU')}
                                                    required
                                                />
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onClick={() => setData('sku', 'SKU-' + Date.now())}
                                                >
                                                    {t('Generate')}
                                                </Button>
                                            </div>
                                            <InputError message={errors.sku} />
                                        </div>
                                        <div>
                                            <Label htmlFor="category_id" required>{t('Category')}</Label>
                                            <Select value={data.category_id} onValueChange={(value) => setData('category_id', value)} required>
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Select Category')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {categories.map((category) => (
                                                        <SelectItem key={category.id} value={category.id.toString()}>
                                                            {category.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.category_id} />
                                        </div>
                                        <div>
                                            <Label htmlFor="tax_ids" required>{t('Tax')}</Label>
                                            <MultiSelectEnhanced
                                                options={taxes.map(tax => ({
                                                    value: tax.id.toString(),
                                                    label: `${tax.tax_name} (${tax.rate}%)`
                                                }))}
                                                value={data.tax_ids}
                                                onValueChange={(value) => setData('tax_ids', value)}
                                                placeholder={t('Select Taxes')}
                                            />
                                            <InputError message={errors.tax_ids} />
                                        </div>
                                        <div>
                                            <Label htmlFor="tax_type" required>{t('Tax Type')}</Label>
                                            <Select value={data.tax_type || 'exclusive'} onValueChange={(value) => setData('tax_type', value)}>
                                                <SelectTrigger id="tax_type">
                                                    <SelectValue placeholder={t('Select Tax Type')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="exclusive">{t('Tax Exclusive (Added on top of price)')}</SelectItem>
                                                    <SelectItem value="inclusive">{t('Tax Inclusive (Included in price)')}</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.tax_type} />
                                        </div>
                                    </div>

                                    <div>
                                        <div className="flex items-center justify-between mb-2">
                                            <Label htmlFor="description">{t('Short Description')}</Label>
                                            <div className="flex gap-2">
                                                {descriptionAI.map(field => <div key={field.id}>{field.component}</div>)}
                                            </div>
                                        </div>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder={t('Enter Short Description')}
                                            rows={3}
                                        />
                                        <InputError message={errors.description} />
                                    </div>

                                    <div>
                                        <Label htmlFor="long_description">{t('Description')}</Label>
                                        <RichTextEditor
                                            content={data.long_description || ''}
                                            onChange={(value) => setData('long_description', value)}
                                            placeholder={t('Enter Description')}
                                        />
                                        <InputError message={errors.long_description} />
                                    </div>

                                    {/* Custom Fields */}
                                    {customFields && customFields.length > 0 && (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            {customFields.map(field => field.component)}
                                        </div>
                                    )}

                                    {/* Warranty Fields */}
                                    {warrantyFields && warrantyFields.length > 0 && (
                                        <div className="border-t pt-6">
                                            <h3 className="text-lg font-medium mb-4">{t('Warranty Information')}</h3>
                                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                {warrantyFields.map(field => field.component)}
                                            </div>
                                        </div>
                                    )}

                                    <div className="flex justify-end">
                                        <Button
                                            type="button"
                                            onClick={nextTab}
                                            disabled={!validateDetailsTab()}
                                        >
                                            {t('Next')}
                                        </Button>
                                    </div>
                                </TabsContent>

                                <TabsContent value="pricing" className="space-y-6 mt-6">
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="sale_price">{t('Sale Price')}</Label>
                                            <Input
                                                id="sale_price"
                                                type="number"
                                                step="0.01"
                                                value={data.sale_price}
                                                onChange={(e) => setData('sale_price', e.target.value)}
                                                placeholder={t('Enter Sale Price')}
                                                required
                                            />
                                            <InputError message={errors.sale_price} />
                                        </div>
                                        <div>
                                            <Label htmlFor="purchase_price">{t('Purchase Price')}</Label>
                                            <Input
                                                id="purchase_price"
                                                type="number"
                                                step="0.01"
                                                value={data.purchase_price}
                                                onChange={(e) => setData('purchase_price', e.target.value)}
                                                placeholder={t('Enter Purchase Price')}
                                                required
                                            />
                                            <InputError message={errors.purchase_price} />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div>
                                            <Label htmlFor="unit" required>{t('Unit')}</Label>
                                            <Select value={data.unit} onValueChange={(value) => setData('unit', value)} required>
                                                <SelectTrigger>
                                                    <SelectValue placeholder={t('Select Unit')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {units.map((unit) => (
                                                        <SelectItem key={unit.id} value={unit.id.toString()}>
                                                            {unit.unit_name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.unit} />
                                        </div>
                                        {data.type !== 'service' && (
                                            <div>
                                                <Label htmlFor="quantity">{t('Quantity')}</Label>
                                                <Input
                                                    id="quantity"
                                                    type="number"
                                                    value={data.quantity}
                                                    onChange={(e) => setData('quantity', e.target.value)}
                                                    placeholder={t('Enter Quantity')}
                                                    required
                                                />
                                                <InputError message={errors.quantity} />
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex justify-between">
                                        <Button type="button" variant="outline" onClick={prevTab}>
                                            {t('Previous')}
                                        </Button>
                                        <Button
                                            type="button"
                                            onClick={nextTab}
                                            disabled={!validatePricingTab()}
                                        >
                                            {t('Next')}
                                        </Button>
                                    </div>
                                </TabsContent>

                                <TabsContent value="media" className="space-y-6 mt-6">
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div>
                                            <MediaPicker
                                                label={t('Product Image')}
                                                value={data.image}
                                                onChange={(value) => setData('image', value)}
                                                placeholder={t('Select image...')}
                                                showPreview={true}
                                            />
                                            <InputError message={errors.image} />
                                        </div>
                                        <div>
                                            <MediaPicker
                                                label={t('Additional Images')}
                                                value={data.images}
                                                onChange={(value) => setData('images', Array.isArray(value) ? value : [value].filter(Boolean))}
                                                multiple={true}
                                                placeholder={t('Select multiple images')}
                                                showPreview={false}
                                            />
                                            <InputError message={errors.images} />
                                        </div>
                                    </div>
                                    <div className="flex justify-between">
                                        <Button type="button" variant="outline" onClick={prevTab}>
                                            {t('Previous')}
                                        </Button>
                                        {data.type === 'service' ? (
                                            <div className="flex gap-2">
                                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                                    {t('Cancel')}
                                                </Button>
                                                <Button type="submit" disabled={processing}>
                                                    {processing ? t('Creating...') : t('Create')}
                                                </Button>
                                            </div>
                                        ) : (
                                            <Button type="button" onClick={nextTab}>
                                                {t('Next')}
                                            </Button>
                                        )}
                                    </div>
                                </TabsContent>

                                <TabsContent value="warehouse" className="space-y-6 mt-6">
                                    {data.type !== 'service' && (
                                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                            <div>
                                                <Label htmlFor="warehouse_id" required>{t('Warehouse')}</Label>
                                                <Select value={data.warehouse_id} onValueChange={(value) => setData('warehouse_id', value)} required>
                                                    <SelectTrigger>
                                                        <SelectValue placeholder={t('Select Warehouse')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {warehouses.map((warehouse) => (
                                                            <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                                {warehouse.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                <InputError message={errors.warehouse_id} />
                                            </div>
                                        </div>
                                    )}

                                    {/* Inventory Fields */}
                                    {data.type !== 'service' && inventoryFields.length > 0 && (
                                        <div>
                                            {inventoryFields.map(field => (
                                                <div key={field.id}>{field.component}</div>
                                            ))}
                                        </div>
                                    )}
                                    <div className="flex justify-between">
                                        <Button type="button" variant="outline" onClick={prevTab}>
                                            {t('Previous')}
                                        </Button>
                                        <div className="flex gap-2">
                                            <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                                {t('Cancel')}
                                            </Button>
                                            <Button type="submit" disabled={processing}>
                                                {processing ? t('Creating...') : t('Create')}
                                            </Button>
                                        </div>
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </form>
                    </CardContent>
                </Card>
            </AuthenticatedLayout>
        </Dialog>
    );
}
