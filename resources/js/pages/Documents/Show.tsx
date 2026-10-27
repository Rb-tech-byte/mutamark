import { FormEvent } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { formatDate } from '@/utils/helpers';

type Document = { id: number; title: string; document_number: string; description: string; creator_id: number; versions: { id: number; version_number: number; file_name: string; file_size: number; created_at: string; change_notes: string; uploader?: { name: string } }[] };
export default function Show({ document }: { document: Document }) {
    const { t } = useTranslation();
    useFlashMessages();
    const user = (usePage().props as any).auth?.user;
    const permissions: string[] = user?.permissions ?? [];
    const canEdit = document.creator_id === user?.id || permissions.includes('manage-any-media');
    const form = useForm({ file: null as File | null, change_notes: '' });
    const submit = (e: FormEvent) => { e.preventDefault(); form.post(route('documents.versions.store', document.id), { forceFormData: true, onSuccess: () => form.reset() }); };
    return <AuthenticatedLayout pageTitle={document.title} breadcrumbs={[{ label: t('Document Repository'), url: route('documents.index') }]} pageActions={<Button variant="outline" asChild><Link href={route('documents.index')}>{t('Back')}</Link></Button>}>
        <Head title={document.title} />
        <Card><CardHeader><CardTitle>{document.document_number}</CardTitle></CardHeader><CardContent className="space-y-4">
            <p>{document.description}</p>
            <Table><TableHeader><TableRow>{['Version', 'File', 'Uploaded By', 'Date', 'Change Notes', 'Actions'].map(label => <TableHead key={label}>{t(label)}</TableHead>)}</TableRow></TableHeader><TableBody>
                {document.versions.map(version => <TableRow key={version.id}><TableCell>{version.version_number}</TableCell><TableCell>{version.file_name}</TableCell><TableCell>{version.uploader?.name}</TableCell><TableCell>{formatDate(version.created_at)}</TableCell><TableCell>{version.change_notes}</TableCell><TableCell>{permissions.includes('download-media') && <Button variant="outline" size="sm" asChild><a href={route('documents.download', [document.id, version.id])}>{t('Download')}</a></Button>}</TableCell></TableRow>)}
            </TableBody></Table>
        </CardContent></Card>
        {canEdit && permissions.includes('create-media') && <Card className="mt-6"><CardHeader><CardTitle>{t('Upload New Version')}</CardTitle></CardHeader><CardContent>
            <form className="space-y-4" onSubmit={submit}><div><Label htmlFor="version-file">{t('File')} (20 MB)</Label><Input id="version-file" type="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp" onChange={e => form.setData('file', e.target.files?.[0] ?? null)} /></div><div><Label htmlFor="version-notes">{t('Change Notes')}</Label><Input id="version-notes" required value={form.data.change_notes} onChange={e => form.setData('change_notes', e.target.value)} /></div>
                {Object.entries(form.errors).map(([key, error]) => <p role="alert" className="text-sm text-destructive" key={key}>{error}</p>)}<Button type="submit" disabled={form.processing}>{t('Upload Version')}</Button>
            </form>
        </CardContent></Card>}
    </AuthenticatedLayout>;
}
