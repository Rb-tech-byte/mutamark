import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { 
    Clock, 
    DollarSign, 
    CheckCircle2, 
    Calendar, 
    Plus, 
    Search, 
    User, 
    FolderKanban, 
    FileText,
    Check,
    X
} from 'lucide-react';

interface TimesheetItem {
    id: number;
    user_id: number;
    project_id: number;
    task_id?: number;
    date: string;
    hours: number;
    minutes: number;
    hourly_rate: number;
    total_cost: number;
    type: string;
    status: string;
    notes?: string;
    user?: { id: number; name: string; email: string };
    project?: { id: number; name: string };
    task?: { id: number; title: string };
}

interface TimesheetsProps {
    timesheets: TimesheetItem[];
    projects: Array<{ id: number; name: string; budget: number }>;
    employees: Array<{ id: number; name: string; email: string }>;
    tasks: Array<{ id: number; title: string; project_id: number }>;
    stats: {
        total_hours: number;
        total_cost: number;
        approved_entries: number;
        pending_entries: number;
    };
}

export default function Timesheets({
    timesheets = [],
    projects = [],
    employees = [],
    tasks = [],
    stats = {
        total_hours: 0,
        total_cost: 0,
        approved_entries: 0,
        pending_entries: 0,
    }
}: TimesheetsProps) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState('');
    const [filterProject, setFilterProject] = useState('all');
    const [modalOpen, setModalOpen] = useState(false);

    const form = useForm({
        user_id: employees[0]?.id?.toString() || '',
        project_id: projects[0]?.id?.toString() || '',
        task_id: '',
        date: new Date().toISOString().split('T')[0],
        hours: '8',
        minutes: '0',
        hourly_rate: '25000', // Standard engineering rate in TZS
        type: 'Project Milestone Execution',
        notes: '',
    });

    const handleCreateTimesheet = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('project.timesheets.store'), {
            onSuccess: () => {
                setModalOpen(false);
                form.reset();
            }
        });
    };

    const handleStatusChange = (id: number, status: string) => {
        router.patch(route('project.timesheets.status', id), { status });
    };

    const projectFilteredTasks = tasks.filter(task => 
        !form.data.project_id || task.project_id.toString() === form.data.project_id
    );

    const filteredTimesheets = timesheets.filter(item => {
        const matchesSearch = 
            item.user?.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            item.project?.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            item.notes?.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesProject = filterProject === 'all' || item.project_id?.toString() === filterProject;
        return matchesSearch && matchesProject;
    });

    const calculatedCostPreview = (parseFloat(form.data.hours || '0') + (parseFloat(form.data.minutes || '0') / 60)) * parseFloat(form.data.hourly_rate || '0');

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Projects'), url: route('project.index') },
                { label: t('Project Timesheets & Costing') }
            ]}
            pageTitle={t('Project Timesheets & Labor Costing')}
        >
            <Head title={t('Project Timesheets & Costing')} />

            <div className="space-y-6">
                {/* Metric Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-l-4 border-l-blue-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Total Hours Logged')}</p>
                                <h3 className="text-2xl font-bold mt-1 text-foreground">{stats.total_hours} hrs</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Billable engineering labor')}</p>
                            </div>
                            <div className="p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-600 rounded-xl">
                                <Clock className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-emerald-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Total Labor Cost')}</p>
                                <h3 className="text-xl font-bold mt-1 text-emerald-600">{formatCurrency(stats.total_cost)}</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Allocated to project budgets')}</p>
                            </div>
                            <div className="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 rounded-xl">
                                <DollarSign className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-teal-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Approved Logs')}</p>
                                <h3 className="text-2xl font-bold mt-1 text-foreground">{stats.approved_entries}</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Verified by project lead')}</p>
                            </div>
                            <div className="p-3 bg-teal-50 dark:bg-teal-950/40 text-teal-600 rounded-xl">
                                <CheckCircle2 className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-indigo-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Active Projects')}</p>
                                <h3 className="text-2xl font-bold mt-1 text-foreground">{projects.length}</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Tracked enterprise ventures')}</p>
                            </div>
                            <div className="p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 rounded-xl">
                                <FolderKanban className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter and Action Bar */}
                <Card>
                    <CardHeader className="p-4 border-b flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                        <div className="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center flex-1">
                            <div className="relative flex-1 sm:max-w-xs">
                                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder={t('Search employee or task...')}
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pl-8 h-9 text-xs"
                                />
                            </div>
                            <Select value={filterProject} onValueChange={setFilterProject}>
                                <SelectTrigger className="w-full sm:w-60 h-9 text-xs">
                                    <SelectValue placeholder={t('Filter by Project')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('All Projects')}</SelectItem>
                                    {projects.map(p => (
                                        <SelectItem key={p.id} value={p.id.toString()}>
                                            {p.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <Button size="sm" onClick={() => setModalOpen(true)} className="gap-1.5 bg-primary">
                            <Plus className="h-4 w-4" />
                            {t('Log Timesheet & Cost')}
                        </Button>
                    </CardHeader>
                    <CardContent className="p-0 overflow-x-auto">
                        <table className="w-full text-sm text-left">
                            <thead className="bg-muted/40 text-muted-foreground text-xs uppercase border-b">
                                <tr>
                                    <th className="px-4 py-3">{t('Date')}</th>
                                    <th className="px-4 py-3">{t('Resource / Employee')}</th>
                                    <th className="px-4 py-3">{t('Project & Task')}</th>
                                    <th className="px-4 py-3 text-center">{t('Duration')}</th>
                                    <th className="px-4 py-3 text-right">{t('Hourly Rate (TZS)')}</th>
                                    <th className="px-4 py-3 text-right">{t('Total Cost (TZS)')}</th>
                                    <th className="px-4 py-3 text-center">{t('Status')}</th>
                                    <th className="px-4 py-3 text-right">{t('Action')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {filteredTimesheets.map((ts) => (
                                    <tr key={ts.id} className="hover:bg-muted/20">
                                        <td className="px-4 py-3 text-muted-foreground font-mono text-xs">
                                            {formatDate(ts.date)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-foreground">{ts.user?.name || '-'}</div>
                                            <div className="text-xs text-muted-foreground">{ts.user?.email}</div>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-primary">{ts.project?.name || '-'}</div>
                                            <div className="text-xs text-muted-foreground">
                                                {ts.task?.title ? `${t('Task')}: ${ts.task.title}` : ts.notes || '-'}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-center font-mono">
                                            <Badge variant="outline" className="font-mono">
                                                {ts.hours}h {ts.minutes > 0 ? `${ts.minutes}m` : ''}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right font-mono text-muted-foreground">
                                            {formatCurrency(ts.hourly_rate || 0)}
                                        </td>
                                        <td className="px-4 py-3 text-right font-bold text-emerald-600 font-mono">
                                            {formatCurrency(ts.total_cost || 0)}
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <Badge className={
                                                ts.status === 'Approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' :
                                                'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400'
                                            }>
                                                {ts.status || 'Approved'}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {ts.status !== 'Approved' ? (
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    className="h-7 text-xs text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                                                    onClick={() => handleStatusChange(ts.id, 'Approved')}
                                                >
                                                    <Check className="h-3.5 w-3.5 mr-1" />
                                                    {t('Approve')}
                                                </Button>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">✓ {t('Verified')}</span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                                {filteredTimesheets.length === 0 && (
                                    <tr>
                                        <td colSpan={8} className="text-center py-8 text-muted-foreground">
                                            {t('No project timesheet logs found.')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>

            {/* Modal Dialog: Log Timesheet */}
            <Dialog open={modalOpen} onOpenChange={setModalOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Log Timesheet & Labor Cost')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateTimesheet} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Employee / Team Member')}</label>
                            <Select
                                value={form.data.user_id}
                                onValueChange={(val) => form.setData('user_id', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('Select Employee')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {employees.map(e => (
                                        <SelectItem key={e.id} value={e.id.toString()}>
                                            {e.name} ({e.email})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Project')}</label>
                            <Select
                                value={form.data.project_id}
                                onValueChange={(val) => form.setData('project_id', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('Select Project')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {projects.map(p => (
                                        <SelectItem key={p.id} value={p.id.toString()}>
                                            {p.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        {projectFilteredTasks.length > 0 && (
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Task')} ({t('Optional')})</label>
                                <Select
                                    value={form.data.task_id}
                                    onValueChange={(val) => form.setData('task_id', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Select Task')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {projectFilteredTasks.map(t => (
                                            <SelectItem key={t.id} value={t.id.toString()}>
                                                {t.title}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Work Date')}</label>
                                <Input
                                    type="date"
                                    value={form.data.date}
                                    onChange={(e) => form.setData('date', e.target.value)}
                                    required
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Hourly Cost Rate (TZS)')}</label>
                                <Input
                                    type="number"
                                    min="0"
                                    value={form.data.hourly_rate}
                                    onChange={(e) => form.setData('hourly_rate', e.target.value)}
                                    placeholder="25000"
                                    required
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Hours Worked')}</label>
                                <Input
                                    type="number"
                                    min="0"
                                    max="24"
                                    value={form.data.hours}
                                    onChange={(e) => form.setData('hours', e.target.value)}
                                    required
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Minutes')}</label>
                                <Input
                                    type="number"
                                    min="0"
                                    max="59"
                                    value={form.data.minutes}
                                    onChange={(e) => form.setData('minutes', e.target.value)}
                                />
                            </div>
                        </div>

                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Work Notes & Deliverables')}</label>
                            <Input
                                value={form.data.notes}
                                onChange={(e) => form.setData('notes', e.target.value)}
                                placeholder="Splice fiber tray 48-core and optical power loss tests"
                            />
                        </div>

                        {calculatedCostPreview > 0 && (
                            <div className="p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg border border-emerald-200 dark:border-emerald-800 text-xs flex justify-between items-center">
                                <span className="font-medium text-emerald-800 dark:text-emerald-300">{t('Total Labor Cost Charged')}:</span>
                                <span className="text-sm font-bold text-emerald-700 dark:text-emerald-400 font-mono">
                                    {formatCurrency(calculatedCostPreview)}
                                </span>
                            </div>
                        )}

                        <div className="flex justify-end gap-2 pt-2 border-t">
                            <Button type="button" variant="outline" onClick={() => setModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={form.processing} className="bg-primary">
                                {t('Save Timesheet')}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
