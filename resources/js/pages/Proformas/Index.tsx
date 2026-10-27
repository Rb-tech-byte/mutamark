import { FormEvent, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Pagination } from '@/components/ui/pagination';
import { PaginatedData } from '@/types/common';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { useFlashMessages } from '@/hooks/useFlashMessages';

type Proforma = { id: number; proforma_number: string; customer_name: string; issue_date: string; expiry_date: string | null; total_amount: number; status: string; converted_to_invoice_id: number | null };
type Option = { id: number; name: string };
type Product = Option & { sale_price: number | string };
const line = () => ({ product_id: '', quantity: 1, unit_price: 0, discount_percentage: 0, tax_rate: 0 });
const today = () => { const d = new Date(); return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`; };

export default function Index({ invoices, customers, products, warehouses, filters }: { invoices: PaginatedData<Proforma>; customers: Option[]; products: Product[]; warehouses: Option[]; filters: { search?: string } }) {
    const { t } = useTranslation();
    useFlashMessages();
    const permissions: string[] = (usePage().props as any).auth?.user?.permissions ?? [];
    const [open, setOpen] = useState(false);
    const [selected, setSelected] = useState<Proforma | null>(null);
    const form = useForm({ customer_id: '', issue_date: today(), expiry_date: '', currency: 'TZS', payment_terms: '', notes: '', items: [line()] });
    const conversion = useForm({ warehouse_id: '', invoice_date: today(), due_date: today() });
    const submit = (e: FormEvent) => { e.preventDefault(); form.post(route('proformas.store'), { onSuccess: () => { setOpen(false); form.reset(); } }); };
    const updateLine = (index: number, field: string, value: string | number) => form.setData('items', form.data.items.map((item, i) => i === index ? { ...item, [field]: value } : item));
    const total = form.data.items.reduce((sum, item) => sum + item.quantity * item.unit_price * (1 - item.discount_percentage / 100) * (1 + item.tax_rate / 100), 0);
    return <AuthenticatedLayout pageTitle={t('Proforma Invoices')} pageActions={permissions.includes('create-sales-invoices') && <Button onClick={() => setOpen(true)}>{t('Create Proforma')}</Button>}>
        <Head title={t('Proforma Invoices')} />
        <Card><CardContent className="pt-6"><Table><TableHeader><TableRow>{['Number', 'Customer', 'Date', 'Expiry', 'Total', 'Status', 'Actions'].map(label => <TableHead key={label}>{t(label)}</TableHead>)}</TableRow></TableHeader><TableBody>
            {invoices.data.map(invoice => <TableRow key={invoice.id}><TableCell>{invoice.proforma_number}</TableCell><TableCell>{invoice.customer_name}</TableCell><TableCell>{formatDate(invoice.issue_date)}</TableCell><TableCell>{invoice.expiry_date ? formatDate(invoice.expiry_date) : '—'}</TableCell><TableCell>{formatCurrency(invoice.total_amount)}</TableCell><TableCell><Badge variant="secondary">{t(invoice.status)}</Badge></TableCell><TableCell><div className="flex flex-wrap gap-2">
                {permissions.includes('print-sales-invoices') && <Button variant="outline" size="sm" asChild><a href={route('proformas.print', invoice.id)} target="_blank" rel="noreferrer">{t('Print / PDF')}</a></Button>}
                {invoice.converted_to_invoice_id ? <Button size="sm" variant="outline" asChild><Link href={route('sales-invoices.show', invoice.converted_to_invoice_id)}>{t('View Invoice')}</Link></Button> : invoice.status !== 'cancelled' && permissions.includes('create-sales-invoices') && <Button size="sm" onClick={() => { conversion.clearErrors(); setSelected(invoice); }}>{t('Convert to Invoice')}</Button>}
                {!invoice.converted_to_invoice_id && invoice.status !== 'cancelled' && permissions.includes('edit-sales-invoices') && <Button size="sm" variant="outline" onClick={() => { if (window.confirm(t('Cancel this proforma?'))) router.post(route('proformas.cancel', invoice.id)); }}>{t('Cancel')}</Button>}
            </div></TableCell></TableRow>)}{!invoices.data.length && <TableRow><TableCell colSpan={7} className="text-center text-muted-foreground">{t('No proformas found.')}</TableCell></TableRow>}
        </TableBody></Table><Pagination data={invoices} routeName="proformas.index" filters={filters} /></CardContent></Card>
        <Dialog open={open} onOpenChange={setOpen}><DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto"><DialogHeader><DialogTitle>{t('Create Proforma')}</DialogTitle></DialogHeader><form className="space-y-4" onSubmit={submit}>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4"><div><Label>{t('Customer')}</Label><Select value={form.data.customer_id} onValueChange={v => form.setData('customer_id', v)}><SelectTrigger aria-label={t('Customer')}><SelectValue placeholder={t('Select customer')} /></SelectTrigger><SelectContent>{customers.map(c => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}</SelectContent></Select></div>
                <div><Label htmlFor="pi-date">{t('Issue Date')}</Label><Input id="pi-date" type="date" required value={form.data.issue_date} onChange={e => form.setData('issue_date', e.target.value)} /></div><div><Label htmlFor="pi-expiry">{t('Expiry Date')}</Label><Input id="pi-expiry" type="date" value={form.data.expiry_date} min={form.data.issue_date} onChange={e => form.setData('expiry_date', e.target.value)} /></div></div>
            {form.data.items.map((item, index) => <div key={index} className="grid grid-cols-2 sm:grid-cols-6 gap-2 border rounded-md p-3">
                <div className="col-span-2"><Label>{t('Product')}</Label><Select value={item.product_id} onValueChange={value => { const product = products.find(p => String(p.id) === value); form.setData('items', form.data.items.map((it, i) => i === index ? { ...it, product_id: value, unit_price: Number(product?.sale_price ?? 0) } : it)); }}><SelectTrigger aria-label={t('Product')}><SelectValue placeholder={t('Select product')} /></SelectTrigger><SelectContent>{products.map(p => <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}</SelectContent></Select></div>
                {(['quantity', 'unit_price', 'discount_percentage', 'tax_rate'] as const).map(field => <div key={field}><Label htmlFor={`pi-${index}-${field}`}>{t({ quantity: 'Quantity', unit_price: 'Unit Price', discount_percentage: 'Discount %', tax_rate: 'Tax %' }[field])}</Label><Input id={`pi-${index}-${field}`} type="number" min={field === 'quantity' ? 0.0001 : 0} step={field === 'quantity' ? '0.0001' : '0.01'} required max={field.endsWith('percentage') || field === 'tax_rate' ? 100 : undefined} value={item[field]} onChange={e => updateLine(index, field, Number(e.target.value))} /></div>)}
                {form.data.items.length > 1 && <Button type="button" variant="ghost" onClick={() => form.setData('items', form.data.items.filter((_, i) => i !== index))}>{t('Remove')}</Button>}
            </div>)}
            <Button type="button" variant="outline" onClick={() => form.setData('items', [...form.data.items, line()])}>{t('Add Item')}</Button><p className="font-semibold">{t('Total')}: {formatCurrency(total)}</p>
            <div><Label htmlFor="pi-terms">{t('Payment Terms')}</Label><Input id="pi-terms" value={form.data.payment_terms} onChange={e => form.setData('payment_terms', e.target.value)} /></div><div><Label htmlFor="pi-notes">{t('Notes')}</Label><Input id="pi-notes" value={form.data.notes} onChange={e => form.setData('notes', e.target.value)} /></div>
            {Object.entries(form.errors).map(([key, error]) => <p role="alert" className="text-sm text-destructive" key={key}>{error}</p>)}<Button type="submit" disabled={form.processing}>{t('Save Proforma')}</Button>
        </form></DialogContent></Dialog>
        <Dialog open={!!selected} onOpenChange={v => { if (!v) setSelected(null); }}><DialogContent><DialogHeader><DialogTitle>{t('Convert to Draft Invoice')}</DialogTitle></DialogHeader><form className="space-y-4" onSubmit={e => { e.preventDefault(); if (selected) conversion.post(route('proformas.convert', selected.id), { onSuccess: () => setSelected(null) }); }}>
            <div><Label>{t('Warehouse')}</Label><Select value={conversion.data.warehouse_id} onValueChange={v => conversion.setData('warehouse_id', v)}><SelectTrigger aria-label={t('Warehouse')}><SelectValue placeholder={t('Select warehouse')} /></SelectTrigger><SelectContent>{warehouses.map(w => <SelectItem key={w.id} value={String(w.id)}>{w.name}</SelectItem>)}</SelectContent></Select></div>
            <div><Label htmlFor="conversion-date">{t('Invoice Date')}</Label><Input id="conversion-date" type="date" required value={conversion.data.invoice_date} onChange={e => conversion.setData('invoice_date', e.target.value)} /></div><div><Label htmlFor="conversion-due">{t('Due Date')}</Label><Input id="conversion-due" type="date" required min={conversion.data.invoice_date} value={conversion.data.due_date} onChange={e => conversion.setData('due_date', e.target.value)} /></div>
            {Object.entries(conversion.errors).map(([key, error]) => <p role="alert" className="text-sm text-destructive" key={key}>{error}</p>)}<Button type="submit" disabled={conversion.processing}>{t('Convert')}</Button>
        </form></DialogContent></Dialog>
    </AuthenticatedLayout>;
}
