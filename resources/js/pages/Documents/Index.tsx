import { FormEvent, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination } from '@/components/ui/pagination';
import { Badge } from '@/components/ui/badge';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { PaginatedData } from '@/types/common';

type Document = { id: number; document_number: string; title: string; category: string | null; access_level: string; versions_count: number; file_name: string };

export default function Index({ documents, filters }: { documents: PaginatedData<Document>; filters: { search?: string } }) {
    const { t } = useTranslation();
    useFlashMessages();
    const permissions: string[] = (usePage().props as any).auth?.user?.permissions ?? [];
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState(filters.search ?? '');
    const form = useForm({ title: '', category: '', description: '', access_level: 'company', expiry_date: '', file: null as File | null });
    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(route('documents.store'), { forceFormData: true, onSuccess: () => { setOpen(false); form.reset(); } });
    };
    return <AuthenticatedLayout pageTitle={t('Document Repository')} pageActions={permissions.includes('create-media') && <Button onClick={() => setOpen(true)}>{t('Upload Document')}</Button>}>
        <Head title={t('Document Repository')} />
        <Card><CardContent className="pt-6 space-y-4">
            <form className="flex gap-2" onSubmit={e => { e.preventDefault(); router.get(route('documents.index'), { search }, { preserveState: true, replace: true }); }}>
                <Input aria-label={t('Search documents')} value={search} onChange={e => setSearch(e.target.value)} placeholder={t('Search documents')} />
                <Button type="submit" variant="outline">{t('Search')}</Button>
            </form>
            <Table><TableHeader><TableRow>{['Number', 'Title', 'Category', 'Access', 'Versions', 'Actions'].map(label => <TableHead key={label}>{t(label)}</TableHead>)}</TableRow></TableHeader>
                <TableBody>{documents.data.map(doc => <TableRow key={doc.id}>
                    <TableCell>{doc.document_number}</TableCell><TableCell><Link className="text-primary underline" href={route('documents.show', doc.id)}>{doc.title}</Link><div className="text-xs text-muted-foreground">{doc.file_name}</div></TableCell>
                    <TableCell>{doc.category || '—'}</TableCell><TableCell><Badge variant="secondary">{t(doc.access_level)}</Badge></TableCell><TableCell>{doc.versions_count}</TableCell>
                    <TableCell><Button variant="outline" size="sm" asChild><Link href={route('documents.show', doc.id)}>{t('View')}</Link></Button></TableCell>
                </TableRow>)}{!documents.data.length && <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground">{t('No documents found.')}</TableCell></TableRow>}</TableBody>
            </Table><Pagination data={documents} routeName="documents.index" filters={filters} />
        </CardContent></Card>
        <Dialog open={open} onOpenChange={setOpen}><DialogContent className="max-h-[90vh] overflow-y-auto"><DialogHeader><DialogTitle>{t('Upload Document')}</DialogTitle></DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div><Label htmlFor="document-title">{t('Title')}</Label><Input id="document-title" required value={form.data.title} onChange={e => form.setData('title', e.target.value)} /></div>
                <div><Label htmlFor="document-category">{t('Category')}</Label><Input id="document-category" value={form.data.category} onChange={e => form.setData('category', e.target.value)} /></div>
                <div><Label htmlFor="document-description">{t('Description')}</Label><Textarea id="document-description" value={form.data.description} onChange={e => form.setData('description', e.target.value)} /></div>
                <div><Label>{t('Access')}</Label><Select value={form.data.access_level} onValueChange={v => form.setData('access_level', v)}><SelectTrigger aria-label={t('Access')}><SelectValue /></SelectTrigger><SelectContent><SelectItem value="company">{t('Company')}</SelectItem><SelectItem value="private">{t('Private')}</SelectItem></SelectContent></Select></div>
                <div><Label htmlFor="document-expiry">{t('Expiry Date')}</Label><Input id="document-expiry" type="date" value={form.data.expiry_date} onChange={e => form.setData('expiry_date', e.target.value)} /></div>
                <div><Label htmlFor="document-file">{t('File')} (20 MB)</Label><Input id="document-file" type="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp" onChange={e => form.setData('file', e.target.files?.[0] ?? null)} /></div>
                {Object.entries(form.errors).map(([key, error]) => <p role="alert" className="text-sm text-destructive" key={key}>{error}</p>)}
                {form.progress && <p role="status">{form.progress.percentage}%</p>}
                <Button disabled={form.processing} type="submit">{t('Upload')}</Button>
            </form>
        </DialogContent></Dialog>
    </AuthenticatedLayout>;
}
