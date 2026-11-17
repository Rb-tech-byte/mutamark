import React, { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { FileText, Printer, Download } from 'lucide-react';

interface TemplateField {
    key: string;
    label: string;
    type: 'text' | 'date' | 'textarea';
    default?: string;
}

interface HrTemplate {
    code: string;
    doc_code: string;
    category: string;
    title: string;
    reference_prefix: string;
    requires_employee: boolean;
    fields: TemplateField[];
    subject: string;
    body: string;
    acceptance_block: boolean;
}

interface EmployeeOption {
    id: number;
    employee_id: string;
    name: string;
    email: string;
    address: string;
    department: string;
    designation: string;
    branch: string;
    salary: string | number;
}

interface Props {
    categories: Record<string, string>;
    templates: HrTemplate[];
    employees: EmployeeOption[];
}

export default function Index({ categories, templates, employees }: Props) {
    const { t } = useTranslation();
    const categoryKeys = Object.keys(categories);
    const [activeCategory, setActiveCategory] = useState(categoryKeys[0] || 'employment');

    const filteredTemplates = useMemo(
        () => templates.filter((tpl) => tpl.category === activeCategory),
        [templates, activeCategory]
    );

    const [selectedCode, setSelectedCode] = useState(templates[0]?.code || 'EMP-APP');
    const selectedTemplate = useMemo(
        () => templates.find((tpl) => tpl.code === selectedCode) || filteredTemplates[0] || templates[0],
        [templates, filteredTemplates, selectedCode]
    );

    const [selectedEmployeeId, setSelectedEmployeeId] = useState('');
    const [formValues, setFormValues] = useState<Record<string, string>>({});

    const handleCategoryChange = (cat: string) => {
        setActiveCategory(cat);
        const first = templates.find((tpl) => tpl.category === cat);
        if (first) {
            setSelectedCode(first.code);
            const defaults: Record<string, string> = {};
            first.fields.forEach((f) => {
                if (f.default) defaults[f.key] = f.default;
            });
            setFormValues((prev) => ({ ...defaults, ...prev }));
        }
    };

    const handleTemplateSelect = (tpl: HrTemplate) => {
        setSelectedCode(tpl.code);
        const defaults: Record<string, string> = {};
        tpl.fields.forEach((f) => {
            if (f.default) defaults[f.key] = f.default;
        });
        setFormValues((prev) => ({ ...defaults, ...prev }));
    };

    const handleEmployeeChange = (empId: string) => {
        setSelectedEmployeeId(empId);
        const emp = employees.find((e) => String(e.id) === empId);
        if (!emp) return;
        setFormValues((prev) => ({
            ...prev,
            employee_name: emp.name,
            employee_number: emp.employee_id,
            employee_address: emp.address || emp.email,
            job_title: emp.designation || prev.job_title || '',
            current_title: emp.designation || prev.current_title || '',
            department: emp.department || prev.department || '',
            office_location: emp.branch || prev.office_location || '',
            gross_salary: emp.salary ? String(emp.salary) : prev.gross_salary || '',
        }));
    };

    const interpolate = (text: string) =>
        text.replace(/\{\{(\w+)\}\}/g, (_m, k) => formValues[k] || `[${k}]`);

    const openPrint = (downloadPdf: boolean) => {
        if (!selectedTemplate) return;
        const payload: Record<string, string> = { ...formValues };
        selectedTemplate.fields.forEach((f) => {
            if (!payload[f.key] && f.default) payload[f.key] = f.default;
        });
        const params = new URLSearchParams({
            code: selectedTemplate.code,
            payload: JSON.stringify(payload),
        });
        if (downloadPdf) params.set('download', 'pdf');
        window.open(`${route('hrm.letters.print')}?${params.toString()}`, '_blank');
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('HRM'), url: route('hrm.employees.index') },
                { label: t('HR Template Letters & Documents') },
            ]}
            pageTitle={t('HR Template Letters & Documents')}
        >
            <Head title={t('HR Template Letters & Documents')} />

            <div className="space-y-6">
                <Tabs value={activeCategory} onValueChange={handleCategoryChange}>
                    <TabsList className="flex flex-wrap h-auto gap-1">
                        {categoryKeys.map((key) => (
                            <TabsTrigger key={key} value={key} className="text-xs">
                                {categories[key]}
                            </TabsTrigger>
                        ))}
                    </TabsList>
                </Tabs>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {/* Left: Template List */}
                    <Card className="lg:col-span-4">
                        <CardHeader className="pb-3">
                            <CardTitle className="text-sm font-semibold">{t('Available Templates')}</CardTitle>
                            <CardDescription className="text-xs">{categories[activeCategory]}</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            {filteredTemplates.map((tpl) => (
                                <button
                                    key={tpl.code}
                                    type="button"
                                    onClick={() => handleTemplateSelect(tpl)}
                                    className={`w-full text-left rounded-md border p-3 transition-colors ${
                                        selectedTemplate?.code === tpl.code
                                            ? 'border-indigo-600 bg-indigo-50/70'
                                            : 'border-slate-200 hover:bg-slate-50'
                                    }`}
                                >
                                    <div className="flex items-center justify-between gap-2">
                                        <span className="font-medium text-sm text-slate-900">{tpl.title}</span>
                                        <Badge variant="outline" className="text-[10px] font-mono">{tpl.doc_code}</Badge>
                                    </div>
                                    <div className="text-[11px] text-slate-500 mt-1 font-mono">
                                        {tpl.reference_prefix}/{new Date().getFullYear()}/###
                                    </div>
                                </button>
                            ))}
                        </CardContent>
                    </Card>

                    {/* Right: Fields & Live Preview */}
                    <div className="lg:col-span-8 space-y-6">
                        {selectedTemplate && (
                            <>
                                <Card>
                                    <CardHeader className="flex flex-row items-center justify-between gap-4">
                                        <div>
                                            <CardTitle className="text-base flex items-center gap-2">
                                                <FileText className="h-4 w-4 text-indigo-600" />
                                                {selectedTemplate.title}
                                            </CardTitle>
                                            <CardDescription className="text-xs">
                                                {t('Document Code')}: {selectedTemplate.doc_code}
                                            </CardDescription>
                                        </div>
                                        <div className="flex gap-2">
                                            <Button size="sm" variant="outline" onClick={() => openPrint(false)} className="gap-1.5">
                                                <Printer className="h-4 w-4" /> {t('Print')}
                                            </Button>
                                            <Button size="sm" onClick={() => openPrint(true)} className="gap-1.5">
                                                <Download className="h-4 w-4" /> {t('Download PDF')}
                                            </Button>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        {selectedTemplate.requires_employee && (
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 rounded-md border bg-slate-50 p-3">
                                                <div>
                                                    <Label className="text-xs">{t('Select Employee (Auto-fill)')}</Label>
                                                    <select
                                                        className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm mt-1"
                                                        value={selectedEmployeeId}
                                                        onChange={(e) => handleEmployeeChange(e.target.value)}
                                                    >
                                                        <option value="">{t('Choose employee...')}</option>
                                                        {employees.map((emp) => (
                                                            <option key={emp.id} value={emp.id}>
                                                                {emp.name} ({emp.employee_id})
                                                            </option>
                                                        ))}
                                                    </select>
                                                </div>
                                                <div>
                                                    <Label className="text-xs">{t('Reference Number (Optional)')}</Label>
                                                    <Input
                                                        className="mt-1"
                                                        value={formValues.reference_number || ''}
                                                        onChange={(e) => setFormValues({ ...formValues, reference_number: e.target.value })}
                                                        placeholder={`${selectedTemplate.reference_prefix}/${new Date().getFullYear()}/001`}
                                                    />
                                                </div>
                                            </div>
                                        )}

                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            {selectedTemplate.fields.map((f) => (
                                                <div key={f.key} className={f.type === 'textarea' ? 'md:col-span-2' : ''}>
                                                    <Label className="text-xs">{f.label}</Label>
                                                    {f.type === 'textarea' ? (
                                                        <Textarea
                                                            rows={3}
                                                            className="mt-1"
                                                            value={formValues[f.key] ?? f.default ?? ''}
                                                            onChange={(e) => setFormValues({ ...formValues, [f.key]: e.target.value })}
                                                        />
                                                    ) : (
                                                        <Input
                                                            type={f.type}
                                                            className="mt-1"
                                                            value={formValues[f.key] ?? f.default ?? ''}
                                                            onChange={(e) => setFormValues({ ...formValues, [f.key]: e.target.value })}
                                                        />
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>

                                <Card>
                                    <CardHeader className="pb-2">
                                        <CardTitle className="text-sm">{t('Live Letter Preview')}</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="rounded border bg-white p-6 text-sm leading-relaxed text-slate-800 whitespace-pre-line">
                                            <div className="font-bold text-slate-900 mb-3">
                                                Re: {interpolate(selectedTemplate.subject)}
                                            </div>
                                            {interpolate(selectedTemplate.body)}
                                        </div>
                                    </CardContent>
                                </Card>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

