import { FormEvent } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

export default function ApprovalDelegations({ users, delegations }: { users: { id: number; name: string }[]; delegations: any[] }) {
    const { t } = useTranslation();
    const form = useForm({ delegate_id: '', start_date: '', end_date: '', reason: '', document_type: '' });
    const submit = (event: FormEvent) => { event.preventDefault(); form.post(route('approvals.delegations.store'), { preserveScroll: true, onSuccess: () => form.reset() }); };
    return <Card className="my-6"><CardHeader><CardTitle>{t('My Approval Delegations')}</CardTitle></CardHeader><CardContent className="space-y-4">
        <form className="space-y-3" onSubmit={submit}><div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div><Label>{t('Delegate')}</Label><Select value={form.data.delegate_id} onValueChange={v => form.setData('delegate_id', v)}><SelectTrigger aria-label={t('Delegate')}><SelectValue placeholder={t('Select user')} /></SelectTrigger><SelectContent>{users.map(user => <SelectItem key={user.id} value={String(user.id)}>{user.name}</SelectItem>)}</SelectContent></Select></div>
            <div><Label htmlFor="delegate-start">{t('Start Date')}</Label><Input id="delegate-start" type="date" required value={form.data.start_date} onChange={e => form.setData('start_date', e.target.value)} /></div>
            <div><Label htmlFor="delegate-end">{t('End Date')}</Label><Input id="delegate-end" type="date" required min={form.data.start_date} value={form.data.end_date} onChange={e => form.setData('end_date', e.target.value)} /></div>
        </div><div><Label htmlFor="delegate-reason">{t('Reason')}</Label><Input id="delegate-reason" required value={form.data.reason} onChange={e => form.setData('reason', e.target.value)} /></div>
            {Object.entries(form.errors).map(([key, error]) => <p role="alert" className="text-sm text-destructive" key={key}>{error}</p>)}<Button type="submit" disabled={form.processing}>{t('Save Delegation')}</Button>
        </form><Table><TableHeader><TableRow>{['Delegate', 'Start Date', 'End Date', 'Status', 'Actions'].map(label => <TableHead key={label}>{t(label)}</TableHead>)}</TableRow></TableHeader><TableBody>{delegations.map(item => <TableRow key={item.id}><TableCell>{item.delegate?.name}</TableCell><TableCell>{String(item.start_date).slice(0, 10)}</TableCell><TableCell>{String(item.end_date).slice(0, 10)}</TableCell><TableCell>{t(item.is_active ? 'Active' : 'Revoked')}</TableCell><TableCell>{item.is_active && <Button type="button" variant="outline" size="sm" onClick={() => router.post(route('approvals.delegations.revoke', item.id), {}, { preserveScroll: true })}>{t('Revoke')}</Button>}</TableCell></TableRow>)}</TableBody></Table>
    </CardContent></Card>;
}
