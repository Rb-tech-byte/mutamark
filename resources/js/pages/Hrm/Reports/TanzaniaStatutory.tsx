import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import NoRecordsFound from '@/components/no-records-found';
import { 
    Calculator, 
    Download, 
    Printer, 
    FileSpreadsheet, 
    Building2, 
    ShieldCheck, 
    Landmark, 
    Receipt, 
    TrendingUp, 
    Percent, 
    CheckCircle2, 
    AlertCircle,
    Users,
    FileText,
    Plus,
    CreditCard,
    Edit,
    Trash2,
    QrCode,
    Check,
    Copy,
    DollarSign,
    Clock,
    FileCheck
} from "lucide-react";
import { formatCurrency, formatDate } from '@/utils/helpers';

interface PayrollItem {
    id: number;
    title: string;
    pay_period_start: string;
    pay_period_end: string;
    status: string;
}

interface StatutoryEntry {
    id: number;
    employee_record_id?: number;
    employee_name: string;
    employee_code: string;
    tin: string;
    nssf_number: string;
    nhif_number: string;
    department: string;
    designation: string;
    basic_salary: number;
    allowances: number;
    gross_pay: number;
    taxable_pay: number;
    paye: number;
    nssf_employee: number;
    nssf_employer: number;
    nssf_total: number;
    nhif: number;
    wcf_employer: number;
    sdl_employer: number;
    total_statutory: number;
    net_pay: number;
}

interface StatutorySummary {
    total_gross: number;
    total_taxable: number;
    total_paye: number;
    total_nssf_employee: number;
    total_nssf_employer: number;
    total_nssf_remittance: number;
    total_nhif: number;
    total_wcf: number;
    total_sdl: number;
    total_statutory_remittance: number;
    total_net_salary: number;
    employee_count: number;
}

interface StatutoryDeclarationItem {
    id: number;
    payroll_id: number | null;
    declaration_number: string;
    statutory_type: string;
    title: string;
    filing_month: string;
    due_date: string;
    gepg_control_number: string | null;
    total_taxable_gross: number;
    employee_deduction_amount: number;
    employer_contribution_amount: number;
    penalty_amount: number;
    total_payable_amount: number;
    payment_status: 'draft' | 'submitted' | 'paid' | 'reconciled';
    payment_date: string | null;
    payment_method: string | null;
    bank_reference: string | null;
    notes: string | null;
    verification_seal: string | null;
    created_at: string;
}

interface EmployeeItem {
    id: number;
    name: string;
    employee_id: string;
    department: string;
    tax_payer_id: string;
    nssf_number: string;
    nhif_number: string;
    wcf_exempt: boolean;
    sdl_exempt: boolean;
}

interface Props {
    payrolls: PayrollItem[];
    selected_payroll_id: number | null;
    selected_payroll: PayrollItem | null;
    entries: StatutoryEntry[];
    summary: StatutorySummary;
    declarations?: StatutoryDeclarationItem[];
    employees?: EmployeeItem[];
}

export default function TanzaniaStatutory({
    payrolls = [],
    selected_payroll_id,
    selected_payroll,
    entries = [],
    summary,
    declarations = [],
    employees = [],
}: Props) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState<'paye' | 'nssf' | 'liabilities' | 'summary' | 'declarations'>('paye');
    const [copiedControlNo, setCopiedControlNo] = useState<string | null>(null);

    // Modal States for CUD
    const [isCreateDecOpen, setIsCreateDecOpen] = useState(false);
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [isEditDecOpen, setIsEditDecOpen] = useState(false);
    const [isDeleteDecOpen, setIsDeleteDecOpen] = useState(false);
    const [isVoucherOpen, setIsVoucherOpen] = useState(false);
    const [isEditEmpOpen, setIsEditEmpOpen] = useState(false);

    // Active Selection States
    const [selectedDec, setSelectedDec] = useState<StatutoryDeclarationItem | null>(null);
    const [selectedEmp, setSelectedEmp] = useState<EmployeeItem | null>(null);

    // Form Data States for Declarations (Create)
    const [newDec, setNewDec] = useState({
        payroll_id: selected_payroll_id?.toString() || '',
        statutory_type: 'tra_paye',
        title: selected_payroll ? `${selected_payroll.title} - TRA Monthly PAYE Return` : 'Tanzania Statutory Monthly Return',
        filing_month: new Date().toISOString().slice(0, 7),
        due_date: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 7).toISOString().slice(0, 10),
        gepg_control_number: '',
        total_taxable_gross: summary?.total_gross || 0,
        employee_deduction_amount: summary?.total_paye || 0,
        employer_contribution_amount: summary?.total_sdl || 0,
        penalty_amount: 0,
        notes: '',
    });

    // Form Data States for Recording Payment (Update)
    const [paymentData, setPaymentData] = useState({
        payment_date: new Date().toISOString().slice(0, 10),
        payment_method: 'bank_transfer',
        bank_reference: '',
        notes: '',
    });

    // Form Data States for Editing Declaration (Update)
    const [editDecData, setEditDecData] = useState({
        gepg_control_number: '',
        due_date: '',
        payment_status: 'draft',
        notes: '',
    });

    // Form Data States for Employee Statutory Profile (Update)
    const [empData, setEmpData] = useState({
        tax_payer_id: '',
        nssf_number: '',
        nhif_number: '',
        wcf_exempt: false,
        sdl_exempt: false,
    });

    const handlePayrollChange = (val: string) => {
        router.get(route('hrm.reports.tanzania-statutory'), { payroll_id: val }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleExport = (type: 'tra' | 'nssf') => {
        if (!selected_payroll_id) return;
        window.location.href = route('hrm.reports.tanzania-statutory.export', {
            payroll_id: selected_payroll_id,
            type: type,
        });
    };

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text);
        setCopiedControlNo(text);
        setTimeout(() => setCopiedControlNo(null), 2000);
    };

    // Open Create Declaration Modal with auto-filled values
    const openCreateDeclaration = (type: string = 'tra_paye') => {
        let empDeduct = 0;
        let emprLiab = 0;
        let titleSuffix = 'TRA Monthly PAYE Return';
        let defaultDueDay = 7;

        if (type === 'tra_paye') {
            empDeduct = summary.total_paye;
            emprLiab = summary.total_sdl;
            titleSuffix = 'TRA Monthly PAYE & SDL Return';
            defaultDueDay = 7;
        } else if (type === 'nssf_pension') {
            empDeduct = summary.total_nssf_employee;
            emprLiab = summary.total_nssf_employer;
            titleSuffix = 'NSSF Monthly 20% Pension Contribution';
            defaultDueDay = 15;
        } else if (type === 'wcf_fund') {
            empDeduct = 0;
            emprLiab = summary.total_wcf;
            titleSuffix = 'WCF Workers Compensation Fund (0.5%)';
            defaultDueDay = 30;
        } else if (type === 'combined_return') {
            empDeduct = summary.total_paye + summary.total_nssf_employee + summary.total_nhif;
            emprLiab = summary.total_nssf_employer + summary.total_wcf + summary.total_sdl;
            titleSuffix = 'Consolidated Tanzania Statutory Payroll Remittance';
            defaultDueDay = 7;
        }

        const now = new Date();
        const dueDate = new Date(now.getFullYear(), now.getMonth() + 1, defaultDueDay).toISOString().slice(0, 10);

        setNewDec({
            payroll_id: selected_payroll_id?.toString() || '',
            statutory_type: type,
            title: selected_payroll ? `${selected_payroll.title} - ${titleSuffix}` : titleSuffix,
            filing_month: now.toISOString().slice(0, 7),
            due_date: dueDate,
            gepg_control_number: '99' + Math.floor(1000000000 + Math.random() * 9000000000),
            total_taxable_gross: summary.total_gross,
            employee_deduction_amount: empDeduct,
            employer_contribution_amount: emprLiab,
            penalty_amount: 0,
            notes: `Auto-generated from payroll cycle ${selected_payroll?.title || 'current'}.`,
        });
        setIsCreateDecOpen(true);
    };

    // Submit Create Declaration (CUD: Create)
    const handleStoreDeclaration = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(route('hrm.reports.tanzania-statutory.declarations.store'), newDec, {
            onSuccess: () => {
                setIsCreateDecOpen(false);
                setActiveTab('declarations');
            },
        });
    };

    // Open Record Payment Modal (CUD: Update)
    const openRecordPayment = (dec: StatutoryDeclarationItem) => {
        setSelectedDec(dec);
        setPaymentData({
            payment_date: new Date().toISOString().slice(0, 10),
            payment_method: 'bank_transfer',
            bank_reference: 'CRDB-TXN-' + Math.floor(1000000 + Math.random() * 9000000),
            notes: `Paid via GePG Control Number ${dec.gepg_control_number || 'N/A'}.`,
        });
        setIsPaymentModalOpen(true);
    };

    // Submit Record Payment (CUD: Update)
    const handleRecordPayment = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedDec) return;

        router.put(route('hrm.reports.tanzania-statutory.declarations.update', selectedDec.id), {
            payment_status: 'paid',
            payment_date: paymentData.payment_date,
            payment_method: paymentData.payment_method,
            bank_reference: paymentData.bank_reference,
            notes: paymentData.notes,
        }, {
            onSuccess: () => setIsPaymentModalOpen(false),
        });
    };

    // Open Edit Declaration Modal (CUD: Update)
    const openEditDeclaration = (dec: StatutoryDeclarationItem) => {
        setSelectedDec(dec);
        setEditDecData({
            gepg_control_number: dec.gepg_control_number || '',
            due_date: dec.due_date ? dec.due_date.slice(0, 10) : '',
            payment_status: dec.payment_status,
            notes: dec.notes || '',
        });
        setIsEditDecOpen(true);
    };

    // Submit Edit Declaration (CUD: Update)
    const handleUpdateDeclaration = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedDec) return;

        router.put(route('hrm.reports.tanzania-statutory.declarations.update', selectedDec.id), editDecData, {
            onSuccess: () => setIsEditDecOpen(false),
        });
    };

    // Open Delete Declaration Modal (CUD: Delete)
    const openDeleteDeclaration = (dec: StatutoryDeclarationItem) => {
        setSelectedDec(dec);
        setIsDeleteDecOpen(true);
    };

    // Submit Delete Declaration (CUD: Delete)
    const handleDeleteDeclaration = () => {
        if (!selectedDec) return;
        router.delete(route('hrm.reports.tanzania-statutory.declarations.destroy', selectedDec.id), {
            onSuccess: () => setIsDeleteDecOpen(false),
        });
    };

    // Open Employee Edit Modal (CUD: Update Employee Statutory Identifiers)
    const openEditEmployee = (entry: StatutoryEntry) => {
        const emp = employees.find(e => e.id === entry.employee_record_id) || {
            id: entry.employee_record_id || entry.id,
            name: entry.employee_name,
            employee_id: entry.employee_code,
            department: entry.department,
            tax_payer_id: entry.tin,
            nssf_number: entry.nssf_number,
            nhif_number: entry.nhif_number || 'NHIF-00928371',
            wcf_exempt: false,
            sdl_exempt: false,
        };
        setSelectedEmp(emp);
        setEmpData({
            tax_payer_id: emp.tax_payer_id,
            nssf_number: emp.nssf_number,
            nhif_number: emp.nhif_number,
            wcf_exempt: emp.wcf_exempt,
            sdl_exempt: emp.sdl_exempt,
        });
        setIsEditEmpOpen(true);
    };

    // Submit Employee Statutory Update (CUD: Update)
    const handleUpdateEmployee = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedEmp) return;

        router.post(route('hrm.reports.tanzania-statutory.employees.update', selectedEmp.id), empData, {
            onSuccess: () => setIsEditEmpOpen(false),
        });
    };

    // Open Filing Slip / Voucher with QR Seal
    const openVoucher = (dec: StatutoryDeclarationItem) => {
        setSelectedDec(dec);
        setIsVoucherOpen(true);
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'paid':
            case 'reconciled':
                return <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 flex items-center gap-1"><CheckCircle2 className="h-3 w-3" /> {status.toUpperCase()}</Badge>;
            case 'submitted':
                return <Badge variant="outline" className="bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/30 flex items-center gap-1"><Clock className="h-3 w-3" /> AWAITING PAYMENT</Badge>;
            default:
                return <Badge variant="outline" className="bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30 flex items-center gap-1"><AlertCircle className="h-3 w-3" /> DRAFT</Badge>;
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Human Resources & Payroll'), url: route('hrm.payrolls.index') },
                { label: t('Tanzania Statutory Reports'), url: route('hrm.reports.tanzania-statutory') }
            ]}
        >
            <Head title={t('Tanzania Statutory Payroll & Remittance Schedule')} />

            <div className="space-y-6">
                {/* Header & Controls */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {t('Tanzania Statutory Remittance Engine')}
                            </h1>
                            <Badge variant="outline" className="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium">
                                <ShieldCheck className="h-3.5 w-3.5 mr-1" />
                                {t('TRA & NSSF Compliant (TZS)')}
                            </Badge>
                        </div>
                        <p className="text-sm text-muted-foreground mt-1">
                            {t('Monthly statutory returns for TRA PAYE, NSSF pension (20%), NHIF, WCF (0.5%), and SDL (3.5%) with GePG e-Filing & CUD declarations.')}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2 print:hidden">
                        {/* Payroll Selector */}
                        {payrolls.length > 0 && (
                            <div className="w-56">
                                <Select
                                    value={selected_payroll_id?.toString() || ''}
                                    onValueChange={handlePayrollChange}
                                >
                                    <SelectTrigger className="bg-card">
                                        <SelectValue placeholder={t('Select payroll cycle...')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {payrolls.map((p) => (
                                            <SelectItem key={p.id} value={p.id.toString()}>
                                                {p.title} ({p.status.toUpperCase()})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        {/* File Declaration CUD Trigger */}
                        <Button 
                            variant="default"
                            size="sm"
                            onClick={() => openCreateDeclaration('tra_paye')}
                            className="text-xs bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
                        >
                            <Plus className="h-4 w-4 mr-1.5" />
                            {t('File Declaration')}
                        </Button>

                        <Button 
                            variant="outline" 
                            size="sm" 
                            onClick={() => handleExport('tra')}
                            disabled={!selected_payroll_id || entries.length === 0}
                            className="text-xs"
                        >
                            <FileSpreadsheet className="h-4 w-4 mr-1.5 text-blue-500" />
                            {t('TRA e-Filing CSV')}
                        </Button>

                        <Button 
                            variant="outline" 
                            size="sm" 
                            onClick={() => handleExport('nssf')}
                            disabled={!selected_payroll_id || entries.length === 0}
                            className="text-xs"
                        >
                            <Download className="h-4 w-4 mr-1.5 text-emerald-500" />
                            {t('NSSF Portal CSV')}
                        </Button>

                        <Button 
                            variant="outline" 
                            size="sm" 
                            onClick={() => window.print()}
                            className="text-xs"
                        >
                            <Printer className="h-4 w-4 mr-1.5" />
                            {t('Print Returns')}
                        </Button>
                    </div>
                </div>

                {/* Print Return Letterhead */}
                <div className="hidden print:block border-b pb-4 mb-4">
                    <div className="text-center space-y-1">
                        <h2 className="text-xl font-bold uppercase tracking-wider">Mutamark Enterprises Limited</h2>
                        <p className="text-xs text-muted-foreground">Tanzania Revenue Authority & Social Security Statutory Schedule</p>
                        <p className="text-xs font-semibold">
                            {selected_payroll?.title} | Period: {selected_payroll?.pay_period_start ? formatDate(selected_payroll.pay_period_start) : '-'} to {selected_payroll?.pay_period_end ? formatDate(selected_payroll.pay_period_end) : '-'}
                        </p>
                    </div>
                </div>

                {/* KPI Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Gross Payroll */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Total Gross Payroll')}
                            </CardTitle>
                            <Landmark className="h-4 w-4 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {formatCurrency(summary.total_gross)}
                            </div>
                            <div className="flex items-center gap-1.5 mt-1 text-xs text-muted-foreground">
                                <Users className="h-3 w-3" />
                                <span>{summary.employee_count} {t('registered employees')}</span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* TRA PAYE Tax */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('TRA Monthly PAYE')}
                            </CardTitle>
                            <Receipt className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {formatCurrency(summary.total_paye)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Taxable Pay')}: {formatCurrency(summary.total_taxable)}
                            </p>
                        </CardContent>
                    </Card>

                    {/* NSSF 20% Contribution */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('NSSF Remittance (20%)')}
                            </CardTitle>
                            <ShieldCheck className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                {formatCurrency(summary.total_nssf_remittance)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                10% Emp ({formatCurrency(summary.total_nssf_employee)}) + 10% Empr ({formatCurrency(summary.total_nssf_employer)})
                            </p>
                        </CardContent>
                    </Card>

                    {/* Total Employer Levies */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Employer Levies (WCF + SDL)')}
                            </CardTitle>
                            <Building2 className="h-4 w-4 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                {formatCurrency(summary.total_wcf + summary.total_sdl)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                WCF 0.5% ({formatCurrency(summary.total_wcf)}) + SDL 3.5% ({formatCurrency(summary.total_sdl)})
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Tabs Navigation */}
                <Tabs value={activeTab} onValueChange={(v) => setActiveTab(v as any)} className="space-y-4">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-2">
                        <TabsList className="bg-muted/50 p-1">
                            <TabsTrigger value="paye" className="text-xs gap-1.5">
                                <Receipt className="h-3.5 w-3.5 text-blue-500" />
                                {t('TRA PAYE')}
                            </TabsTrigger>
                            <TabsTrigger value="nssf" className="text-xs gap-1.5">
                                <ShieldCheck className="h-3.5 w-3.5 text-emerald-500" />
                                {t('NSSF 20%')}
                            </TabsTrigger>
                            <TabsTrigger value="liabilities" className="text-xs gap-1.5">
                                <Percent className="h-3.5 w-3.5 text-amber-500" />
                                {t('WCF & SDL')}
                            </TabsTrigger>
                            <TabsTrigger value="summary" className="text-xs gap-1.5">
                                <Calculator className="h-3.5 w-3.5" />
                                {t('Net Salary')}
                            </TabsTrigger>
                            <TabsTrigger value="declarations" className="text-xs gap-1.5 font-semibold">
                                <FileCheck className="h-3.5 w-3.5 text-primary" />
                                {t('Statutory Declarations (CUD)')}
                                <span className="ml-1 px-1.5 py-0.2 rounded-full bg-primary/20 text-[10px] text-primary">
                                    {declarations.length}
                                </span>
                            </TabsTrigger>
                        </TabsList>

                        <div className="text-xs text-muted-foreground hidden sm:block">
                            {selected_payroll ? `${selected_payroll.title} (${selected_payroll.status.toUpperCase()})` : t('No cycle selected')}
                        </div>
                    </div>

                    {/* Tab 1: TRA PAYE Return Schedule */}
                    <TabsContent value="paye" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <CardTitle className="text-base font-semibold">
                                            {t('Tanzania Revenue Authority - PAYE Return Schedule')}
                                        </CardTitle>
                                        <CardDescription className="text-xs">
                                            {t('Progressive monthly tax calculation under Income Tax Act with allowable 10% pension deduction.')}
                                        </CardDescription>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Badge variant="outline" className="text-xs bg-muted/40">
                                            {t('Bracket: Up to 30% top marginal rate')}
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                {entries.length === 0 ? (
                                    <div className="p-8">
                                        <NoRecordsFound 
                                            icon={Users} 
                                            title={t('No payroll entries found')} 
                                            description={t('No entries recorded for this payroll cycle.')} 
                                        />
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted/40">
                                                    <TableHead className="font-semibold text-xs">{t('Employee')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('TIN Number')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Department')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Basic Salary')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Allowances')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Gross Pay')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('NSSF Relief (10%)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Taxable Pay')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-blue-600 dark:text-blue-400">{t('Monthly PAYE Tax')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-center w-16">{t('Edit')}</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {entries.map((entry) => (
                                                    <TableRow key={entry.id} className="hover:bg-muted/30">
                                                        <TableCell className="font-medium text-xs">
                                                            <div>{entry.employee_name}</div>
                                                            <span className="text-[11px] text-muted-foreground">{entry.employee_code}</span>
                                                        </TableCell>
                                                        <TableCell className="text-xs font-mono">{entry.tin}</TableCell>
                                                        <TableCell className="text-xs">
                                                            <div>{entry.department}</div>
                                                            <span className="text-[11px] text-muted-foreground">{entry.designation}</span>
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono">{formatCurrency(entry.basic_salary)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono text-muted-foreground">{formatCurrency(entry.allowances)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-medium">{formatCurrency(entry.gross_pay)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">-{formatCurrency(entry.nssf_employee)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-medium">{formatCurrency(entry.taxable_pay)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-bold text-blue-600 dark:text-blue-400">{formatCurrency(entry.paye)}</TableCell>
                                                        <TableCell className="text-center">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="h-7 w-7 text-muted-foreground hover:text-foreground"
                                                                onClick={() => openEditEmployee(entry)}
                                                                title={t('Edit Employee Statutory Profile')}
                                                            >
                                                                <Edit className="h-3.5 w-3.5" />
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                                {/* Totals Row */}
                                                <TableRow className="bg-muted/60 font-bold border-t-2">
                                                    <TableCell colSpan={3} className="text-xs uppercase">{t('Total TRA Remittance')}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">-</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">-</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">{formatCurrency(summary.total_gross)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">-{formatCurrency(summary.total_nssf_employee)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">{formatCurrency(summary.total_taxable)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-blue-600 dark:text-blue-400 font-extrabold">{formatCurrency(summary.total_paye)}</TableCell>
                                                    <TableCell></TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Statutory Reference Callout */}
                        <div className="p-4 rounded-lg border bg-card/60 backdrop-blur-sm text-xs space-y-1.5 text-muted-foreground">
                            <div className="font-semibold text-foreground flex items-center gap-1.5">
                                <AlertCircle className="h-4 w-4 text-primary" />
                                {t('Tanzania 2026 Monthly PAYE Statutory Tax Rates Reference')}
                            </div>
                            <p>• 0 - 270,000 TZS: <strong>0%</strong> (Tax-free threshold)</p>
                            <p>• 270,001 - 520,000 TZS: <strong>8%</strong> on amount exceeding TZS 270,000</p>
                            <p>• 520,001 - 760,000 TZS: <strong>TZS 20,000 + 20%</strong> on amount exceeding TZS 520,000</p>
                            <p>• 760,001 - 1,000,000 TZS: <strong>TZS 68,000 + 25%</strong> on amount exceeding TZS 760,000</p>
                            <p>• Above 1,000,000 TZS: <strong>TZS 128,000 + 30%</strong> on amount exceeding TZS 1,000,000</p>
                        </div>
                    </TabsContent>

                    {/* Tab 2: NSSF Monthly Contribution Register */}
                    <TabsContent value="nssf" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <CardTitle className="text-base font-semibold">
                                            {t('National Social Security Fund (NSSF) - Monthly Contribution Return')}
                                        </CardTitle>
                                        <CardDescription className="text-xs">
                                            {t('Statutory pension contributions split equally: 10% employee deduction and 10% employer liability.')}
                                        </CardDescription>
                                    </div>
                                    <Badge variant="outline" className="text-xs border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        {t('Combined Rate: 20.0%')}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                {entries.length === 0 ? (
                                    <div className="p-8">
                                        <NoRecordsFound 
                                            icon={Users} 
                                            title={t('No payroll entries found')} 
                                            description={t('No entries recorded for this payroll cycle.')} 
                                        />
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted/40">
                                                    <TableHead className="font-semibold text-xs">{t('Employee')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('NSSF Member No.')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('TIN')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Gross Wage (TZS)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-emerald-600 dark:text-emerald-400">{t('Employee 10%')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-emerald-600 dark:text-emerald-400">{t('Employer 10%')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right font-bold">{t('Total NSSF 20%')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-center w-16">{t('Edit')}</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {entries.map((entry) => (
                                                    <TableRow key={entry.id} className="hover:bg-muted/30">
                                                        <TableCell className="font-medium text-xs">
                                                            <div>{entry.employee_name}</div>
                                                            <span className="text-[11px] text-muted-foreground">{entry.employee_code}</span>
                                                        </TableCell>
                                                        <TableCell className="text-xs font-mono font-medium">{entry.nssf_number}</TableCell>
                                                        <TableCell className="text-xs font-mono text-muted-foreground">{entry.tin}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono">{formatCurrency(entry.gross_pay)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-medium text-emerald-600 dark:text-emerald-400">{formatCurrency(entry.nssf_employee)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-medium text-emerald-600 dark:text-emerald-400">{formatCurrency(entry.nssf_employer)}</TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-bold">{formatCurrency(entry.nssf_total)}</TableCell>
                                                        <TableCell className="text-center">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="h-7 w-7 text-muted-foreground hover:text-foreground"
                                                                onClick={() => openEditEmployee(entry)}
                                                                title={t('Edit Employee Statutory Profile')}
                                                            >
                                                                <Edit className="h-3.5 w-3.5" />
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                                {/* Totals Row */}
                                                <TableRow className="bg-muted/60 font-bold border-t-2">
                                                    <TableCell colSpan={3} className="text-xs uppercase">{t('Total NSSF Remittance')}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">{formatCurrency(summary.total_gross)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">{formatCurrency(summary.total_nssf_employee)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">{formatCurrency(summary.total_nssf_employer)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono font-extrabold text-foreground">{formatCurrency(summary.total_nssf_remittance)}</TableCell>
                                                    <TableCell></TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 3: Employer Statutory Liabilities (WCF & SDL) */}
                    <TabsContent value="liabilities" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div>
                                        <CardTitle className="text-base font-semibold">
                                            {t('Employer Statutory Levies & Liabilities')}
                                        </CardTitle>
                                        <CardDescription className="text-xs">
                                            {t('Workers Compensation Fund (0.5%), Skills Development Levy (3.5%), and Employer NSSF (10%).')}
                                        </CardDescription>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Badge variant="outline" className="text-xs border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                            {t('Total Employer Rate: 14.0%')}
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                {entries.length === 0 ? (
                                    <div className="p-8">
                                        <NoRecordsFound 
                                            icon={Users} 
                                            title={t('No payroll entries found')} 
                                            description={t('No entries recorded for this payroll cycle.')} 
                                        />
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted/40">
                                                    <TableHead className="font-semibold text-xs">{t('Employee')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Department')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Gross Pay (TZS)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-amber-600 dark:text-amber-400">{t('WCF (0.5%)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-amber-600 dark:text-amber-400">{t('SDL (3.5%)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-emerald-600 dark:text-emerald-400">{t('NSSF Employer (10%)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right font-bold">{t('Total Employer Cost')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-center w-16">{t('Edit')}</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {entries.map((entry) => {
                                                    const employerTotal = entry.wcf_employer + entry.sdl_employer + entry.nssf_employer;
                                                    return (
                                                        <TableRow key={entry.id} className="hover:bg-muted/30">
                                                            <TableCell className="font-medium text-xs">
                                                                <div>{entry.employee_name}</div>
                                                                <span className="text-[11px] text-muted-foreground">{entry.employee_code}</span>
                                                            </TableCell>
                                                            <TableCell className="text-xs">{entry.department}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono">{formatCurrency(entry.gross_pay)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono text-amber-600 dark:text-amber-400">{formatCurrency(entry.wcf_employer)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono text-amber-600 dark:text-amber-400">{formatCurrency(entry.sdl_employer)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">{formatCurrency(entry.nssf_employer)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono font-bold">{formatCurrency(employerTotal)}</TableCell>
                                                            <TableCell className="text-center">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7 text-muted-foreground hover:text-foreground"
                                                                    onClick={() => openEditEmployee(entry)}
                                                                    title={t('Edit Employee Statutory Profile')}
                                                                >
                                                                    <Edit className="h-3.5 w-3.5" />
                                                                </Button>
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })}
                                                {/* Totals Row */}
                                                <TableRow className="bg-muted/60 font-bold border-t-2">
                                                    <TableCell colSpan={2} className="text-xs uppercase">{t('Total Employer Liabilities')}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">{formatCurrency(summary.total_gross)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-amber-600 dark:text-amber-400">{formatCurrency(summary.total_wcf)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-amber-600 dark:text-amber-400">{formatCurrency(summary.total_sdl)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">{formatCurrency(summary.total_nssf_employer)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono font-extrabold text-foreground">
                                                        {formatCurrency(summary.total_wcf + summary.total_sdl + summary.total_nssf_employer)}
                                                    </TableCell>
                                                    <TableCell></TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 4: Net Salary Ledger */}
                    <TabsContent value="summary" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div>
                                    <CardTitle className="text-base font-semibold">
                                        {t('Net Take-Home Pay & Total Deductions Distribution')}
                                    </CardTitle>
                                    <CardDescription className="text-xs">
                                        {t('Breakdown of statutory deductions (PAYE, NSSF, NHIF) and resulting net pay for all employees.')}
                                    </CardDescription>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                {entries.length === 0 ? (
                                    <div className="p-8">
                                        <NoRecordsFound 
                                            icon={Users} 
                                            title={t('No payroll entries found')} 
                                            description={t('No entries recorded for this payroll cycle.')} 
                                        />
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted/40">
                                                    <TableHead className="font-semibold text-xs">{t('Employee')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Gross Pay')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-blue-600 dark:text-blue-400">{t('TRA PAYE')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-emerald-600 dark:text-emerald-400">{t('NSSF (10%)')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right text-purple-600 dark:text-purple-400">{t('NHIF Health')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right font-medium">{t('Total Deductions')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right font-bold text-emerald-600 dark:text-emerald-400">{t('Net Salary')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-center w-16">{t('Edit')}</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {entries.map((entry) => {
                                                    const totalDeductions = entry.paye + entry.nssf_employee + entry.nhif;
                                                    return (
                                                        <TableRow key={entry.id} className="hover:bg-muted/30">
                                                            <TableCell className="font-medium text-xs">
                                                                <div>{entry.employee_name}</div>
                                                                <span className="text-[11px] text-muted-foreground">{entry.employee_code}</span>
                                                            </TableCell>
                                                            <TableCell className="text-xs text-right font-mono">{formatCurrency(entry.gross_pay)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono text-blue-600 dark:text-blue-400">{formatCurrency(entry.paye)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">{formatCurrency(entry.nssf_employee)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono text-purple-600 dark:text-purple-400">{formatCurrency(entry.nhif)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono font-medium text-rose-500">-{formatCurrency(totalDeductions)}</TableCell>
                                                            <TableCell className="text-xs text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">{formatCurrency(entry.net_pay)}</TableCell>
                                                            <TableCell className="text-center">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7 text-muted-foreground hover:text-foreground"
                                                                    onClick={() => openEditEmployee(entry)}
                                                                    title={t('Edit Employee Statutory Profile')}
                                                                >
                                                                    <Edit className="h-3.5 w-3.5" />
                                                                </Button>
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                })}
                                                {/* Totals Row */}
                                                <TableRow className="bg-muted/60 font-bold border-t-2">
                                                    <TableCell className="text-xs uppercase">{t('Grand Total')}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono">{formatCurrency(summary.total_gross)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-blue-600 dark:text-blue-400">{formatCurrency(summary.total_paye)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-emerald-600 dark:text-emerald-400">{formatCurrency(summary.total_nssf_employee)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-purple-600 dark:text-purple-400">{formatCurrency(summary.total_nhif)}</TableCell>
                                                    <TableCell className="text-xs text-right font-mono text-rose-500">
                                                        -{formatCurrency(summary.total_paye + summary.total_nssf_employee + summary.total_nhif)}
                                                    </TableCell>
                                                    <TableCell className="text-xs text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400">
                                                        {formatCurrency(summary.total_net_salary)}
                                                    </TableCell>
                                                    <TableCell></TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 5: Statutory Declarations & Remittance Filings (NEW - Full CUD Engine) */}
                    <TabsContent value="declarations" className="space-y-4">
                        <Card className="border shadow-sm bg-card">
                            <CardHeader className="pb-3">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <CardTitle className="text-base font-semibold flex items-center gap-2">
                                            <FileCheck className="h-4 w-4 text-primary" />
                                            {t('Official Statutory Declarations & Remittance Register')}
                                        </CardTitle>
                                        <CardDescription className="text-xs">
                                            {t('Manage, track, verify, and settle monthly statutory tax declarations filed with TRA, NSSF, WCF, and SDL authorities.')}
                                        </CardDescription>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Button
                                            size="sm"
                                            onClick={() => openCreateDeclaration('tra_paye')}
                                            className="text-xs bg-blue-600 hover:bg-blue-700 text-white"
                                        >
                                            <Receipt className="h-3.5 w-3.5 mr-1" />
                                            {t('+ TRA PAYE/SDL')}
                                        </Button>
                                        <Button
                                            size="sm"
                                            onClick={() => openCreateDeclaration('nssf_pension')}
                                            className="text-xs bg-emerald-600 hover:bg-emerald-700 text-white"
                                        >
                                            <ShieldCheck className="h-3.5 w-3.5 mr-1" />
                                            {t('+ NSSF 20%')}
                                        </Button>
                                        <Button
                                            size="sm"
                                            onClick={() => openCreateDeclaration('combined_return')}
                                            className="text-xs bg-primary hover:bg-primary/90 text-primary-foreground"
                                        >
                                            <Plus className="h-3.5 w-3.5 mr-1" />
                                            {t('Consolidated Return')}
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="p-0">
                                {declarations.length === 0 ? (
                                    <div className="p-8">
                                        <NoRecordsFound 
                                            icon={FileText} 
                                            title={t('No statutory declarations filed yet')} 
                                            description={t('Click one of the buttons above to create and file your first statutory remittance declaration.')} 
                                        />
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted/40">
                                                    <TableHead className="font-semibold text-xs">{t('Declaration No.')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Type & Title')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Month / Due Date')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('GePG Control No.')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Payable Amount')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-center">{t('Status')}</TableHead>
                                                    <TableHead className="font-semibold text-xs">{t('Payment Ref')}</TableHead>
                                                    <TableHead className="font-semibold text-xs text-right">{t('Actions (CUD)')}</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {declarations.map((dec) => (
                                                    <TableRow key={dec.id} className="hover:bg-muted/30">
                                                        <TableCell className="font-mono font-medium text-xs">
                                                            {dec.declaration_number}
                                                        </TableCell>
                                                        <TableCell className="text-xs">
                                                            <div className="font-medium text-foreground">{dec.title}</div>
                                                            <span className="text-[11px] text-muted-foreground uppercase">{dec.statutory_type.replace('_', ' ')}</span>
                                                        </TableCell>
                                                        <TableCell className="text-xs">
                                                            <div className="font-mono font-semibold">{dec.filing_month}</div>
                                                            <span className="text-[11px] text-muted-foreground">Due: {formatDate(dec.due_date)}</span>
                                                        </TableCell>
                                                        <TableCell className="text-xs font-mono">
                                                            {dec.gepg_control_number ? (
                                                                <button
                                                                    onClick={() => copyToClipboard(dec.gepg_control_number!)}
                                                                    className="flex items-center gap-1 text-primary hover:underline group"
                                                                    title="Click to copy GePG Control Number"
                                                                >
                                                                    <span>{dec.gepg_control_number}</span>
                                                                    {copiedControlNo === dec.gepg_control_number ? (
                                                                        <Check className="h-3 w-3 text-emerald-500" />
                                                                    ) : (
                                                                        <Copy className="h-3 w-3 opacity-50 group-hover:opacity-100" />
                                                                    )}
                                                                </button>
                                                            ) : (
                                                                <span className="text-muted-foreground italic">{t('Pending')}</span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-xs text-right font-mono font-bold">
                                                            {formatCurrency(dec.total_payable_amount)}
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            {getStatusBadge(dec.payment_status)}
                                                        </TableCell>
                                                        <TableCell className="text-xs">
                                                            {dec.payment_date ? (
                                                                <div>
                                                                    <div className="font-medium text-foreground">{dec.bank_reference || t('Direct Settle')}</div>
                                                                    <span className="text-[11px] text-muted-foreground">{formatDate(dec.payment_date)}</span>
                                                                </div>
                                                            ) : (
                                                                <span className="text-muted-foreground text-[11px]">-</span>
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <div className="flex items-center justify-end gap-1">
                                                                {/* View Filing Voucher & QR Seal */}
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7 text-primary hover:bg-primary/10"
                                                                    onClick={() => openVoucher(dec)}
                                                                    title={t('View Filing Voucher & QR Code')}
                                                                >
                                                                    <QrCode className="h-3.5 w-3.5" />
                                                                </Button>

                                                                {/* Record Payment (CUD: Update) */}
                                                                {dec.payment_status !== 'paid' && (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        className="h-7 w-7 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-500/10"
                                                                        onClick={() => openRecordPayment(dec)}
                                                                        title={t('Record Remittance Payment')}
                                                                    >
                                                                        <CreditCard className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                )}

                                                                {/* Edit Declaration (CUD: Update) */}
                                                                <Button
                                                                    variant="ghost"
                                                                    size="icon"
                                                                    className="h-7 w-7 text-muted-foreground hover:text-foreground"
                                                                    onClick={() => openEditDeclaration(dec)}
                                                                    title={t('Edit Declaration')}
                                                                >
                                                                    <Edit className="h-3.5 w-3.5" />
                                                                </Button>

                                                                {/* Delete Declaration (CUD: Delete) */}
                                                                {dec.payment_status !== 'paid' && (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        className="h-7 w-7 text-rose-500 hover:text-rose-600 hover:bg-rose-500/10"
                                                                        onClick={() => openDeleteDeclaration(dec)}
                                                                        title={t('Delete Declaration')}
                                                                    >
                                                                        <Trash2 className="h-3.5 w-3.5" />
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>

            {/* MODAL 1: Create Statutory Declaration (CUD: Create) */}
            <Dialog open={isCreateDecOpen} onOpenChange={setIsCreateDecOpen}>
                <DialogContent className="sm:max-w-[550px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Plus className="h-5 w-5 text-primary" />
                            {t('File New Statutory Declaration')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Create an official statutory remittance return with a GePG Control Number.')}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleStoreDeclaration} className="space-y-4 py-2">
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="statutory_type" className="text-xs font-semibold">{t('Authority / Type')}</Label>
                                <Select
                                    value={newDec.statutory_type}
                                    onValueChange={(val) => {
                                        setNewDec(prev => ({ ...prev, statutory_type: val }));
                                    }}
                                >
                                    <SelectTrigger className="text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="tra_paye">{t('TRA PAYE & SDL (TRA)')}</SelectItem>
                                        <SelectItem value="nssf_pension">{t('NSSF 20% Pension (NSSF)')}</SelectItem>
                                        <SelectItem value="wcf_fund">{t('WCF Fund 0.5% (WCF)')}</SelectItem>
                                        <SelectItem value="sdl_levy">{t('SDL Levy 3.5% (TRA)')}</SelectItem>
                                        <SelectItem value="combined_return">{t('Consolidated Return')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="filing_month" className="text-xs font-semibold">{t('Filing Month')}</Label>
                                <Input
                                    id="filing_month"
                                    type="month"
                                    value={newDec.filing_month}
                                    onChange={(e) => setNewDec(prev => ({ ...prev, filing_month: e.target.value }))}
                                    required
                                    className="text-xs"
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="title" className="text-xs font-semibold">{t('Declaration Title')}</Label>
                            <Input
                                id="title"
                                value={newDec.title}
                                onChange={(e) => setNewDec(prev => ({ ...prev, title: e.target.value }))}
                                required
                                className="text-xs"
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="due_date" className="text-xs font-semibold">{t('Statutory Due Date')}</Label>
                                <Input
                                    id="due_date"
                                    type="date"
                                    value={newDec.due_date}
                                    onChange={(e) => setNewDec(prev => ({ ...prev, due_date: e.target.value }))}
                                    required
                                    className="text-xs"
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="gepg_control_number" className="text-xs font-semibold">{t('GePG Control Number')}</Label>
                                <Input
                                    id="gepg_control_number"
                                    placeholder="e.g. 991238475821"
                                    value={newDec.gepg_control_number}
                                    onChange={(e) => setNewDec(prev => ({ ...prev, gepg_control_number: e.target.value }))}
                                    className="text-xs font-mono"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3 p-3 bg-muted/40 rounded-lg border text-xs">
                            <div>
                                <Label className="text-[11px] text-muted-foreground">{t('Employee Deduction')}</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    value={newDec.employee_deduction_amount}
                                    onChange={(e) => setNewDec(prev => ({ ...prev, employee_deduction_amount: parseFloat(e.target.value) || 0 }))}
                                    className="font-mono mt-1 text-xs"
                                    required
                                />
                            </div>
                            <div>
                                <Label className="text-[11px] text-muted-foreground">{t('Employer Liability')}</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    value={newDec.employer_contribution_amount}
                                    onChange={(e) => setNewDec(prev => ({ ...prev, employer_contribution_amount: parseFloat(e.target.value) || 0 }))}
                                    className="font-mono mt-1 text-xs"
                                    required
                                />
                            </div>
                        </div>

                        <div className="flex justify-between items-center text-xs p-2.5 bg-primary/10 rounded-md border border-primary/20">
                            <span className="font-semibold text-foreground">{t('Total Payable to Authority')}:</span>
                            <span className="font-mono font-bold text-sm text-primary">
                                {formatCurrency(Number(newDec.employee_deduction_amount) + Number(newDec.employer_contribution_amount) + Number(newDec.penalty_amount))}
                            </span>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="notes" className="text-xs font-semibold">{t('Filing Notes / Remarks')}</Label>
                            <Textarea
                                id="notes"
                                rows={2}
                                value={newDec.notes}
                                onChange={(e) => setNewDec(prev => ({ ...prev, notes: e.target.value }))}
                                className="text-xs"
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setIsCreateDecOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" size="sm" className="bg-primary text-primary-foreground">
                                {t('Create Declaration')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 2: Record Payment (CUD: Update) */}
            <Dialog open={isPaymentModalOpen} onOpenChange={setIsPaymentModalOpen}>
                <DialogContent className="sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                            <CreditCard className="h-5 w-5" />
                            {t('Record Statutory Remittance Payment')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Mark declaration :num as officially settled.', { num: selectedDec?.declaration_number })}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleRecordPayment} className="space-y-4 py-2">
                        <div className="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-xs space-y-1">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">{t('Payable Amount')}:</span>
                                <span className="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {selectedDec ? formatCurrency(selectedDec.total_payable_amount) : '-'}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">{t('GePG Control Number')}:</span>
                                <span className="font-mono font-semibold">{selectedDec?.gepg_control_number || t('None')}</span>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="payment_date" className="text-xs font-semibold">{t('Payment Date')}</Label>
                                <Input
                                    id="payment_date"
                                    type="date"
                                    value={paymentData.payment_date}
                                    onChange={(e) => setPaymentData(prev => ({ ...prev, payment_date: e.target.value }))}
                                    required
                                    className="text-xs"
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="payment_method" className="text-xs font-semibold">{t('Channel')}</Label>
                                <Select
                                    value={paymentData.payment_method}
                                    onValueChange={(val) => setPaymentData(prev => ({ ...prev, payment_method: val }))}
                                >
                                    <SelectTrigger className="text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="bank_transfer">{t('Bank Transfer / RTGS')}</SelectItem>
                                        <SelectItem value="gepg_gateway">{t('GePG Portal (Online)')}</SelectItem>
                                        <SelectItem value="mobile_money">{t('M-Pesa / Airtel Money')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="bank_reference" className="text-xs font-semibold">{t('Bank Reference / TXN ID')}</Label>
                            <Input
                                id="bank_reference"
                                placeholder="e.g. CRDB-TXN-8829104"
                                value={paymentData.bank_reference}
                                onChange={(e) => setPaymentData(prev => ({ ...prev, bank_reference: e.target.value }))}
                                required
                                className="text-xs font-mono"
                            />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="pay_notes" className="text-xs font-semibold">{t('Settlement Notes')}</Label>
                            <Textarea
                                id="pay_notes"
                                rows={2}
                                value={paymentData.notes}
                                onChange={(e) => setPaymentData(prev => ({ ...prev, notes: e.target.value }))}
                                className="text-xs"
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setIsPaymentModalOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" size="sm" className="bg-emerald-600 hover:bg-emerald-700 text-white">
                                <CheckCircle2 className="h-4 w-4 mr-1.5" />
                                {t('Confirm Payment')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 3: Edit Declaration (CUD: Update) */}
            <Dialog open={isEditDecOpen} onOpenChange={setIsEditDecOpen}>
                <DialogContent className="sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Edit className="h-5 w-5 text-primary" />
                            {t('Edit Statutory Declaration')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Update filing control number or declaration notes.')}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleUpdateDeclaration} className="space-y-4 py-2">
                        <div className="space-y-1.5">
                            <Label htmlFor="edit_gepg" className="text-xs font-semibold">{t('GePG Control Number')}</Label>
                            <Input
                                id="edit_gepg"
                                value={editDecData.gepg_control_number}
                                onChange={(e) => setEditDecData(prev => ({ ...prev, gepg_control_number: e.target.value }))}
                                className="text-xs font-mono"
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1.5">
                                <Label htmlFor="edit_due" className="text-xs font-semibold">{t('Due Date')}</Label>
                                <Input
                                    id="edit_due"
                                    type="date"
                                    value={editDecData.due_date}
                                    onChange={(e) => setEditDecData(prev => ({ ...prev, due_date: e.target.value }))}
                                    className="text-xs"
                                />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="edit_status" className="text-xs font-semibold">{t('Filing Status')}</Label>
                                <Select
                                    value={editDecData.payment_status}
                                    onValueChange={(val) => setEditDecData(prev => ({ ...prev, payment_status: val }))}
                                >
                                    <SelectTrigger className="text-xs">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">{t('Draft')}</SelectItem>
                                        <SelectItem value="submitted">{t('Submitted')}</SelectItem>
                                        <SelectItem value="paid">{t('Paid')}</SelectItem>
                                        <SelectItem value="reconciled">{t('Reconciled')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="edit_notes" className="text-xs font-semibold">{t('Notes')}</Label>
                            <Textarea
                                id="edit_notes"
                                rows={3}
                                value={editDecData.notes}
                                onChange={(e) => setEditDecData(prev => ({ ...prev, notes: e.target.value }))}
                                className="text-xs"
                            />
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setIsEditDecOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" size="sm" className="bg-primary text-primary-foreground">
                                {t('Save Changes')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 4: Delete Declaration (CUD: Delete) */}
            <Dialog open={isDeleteDecOpen} onOpenChange={setIsDeleteDecOpen}>
                <DialogContent className="sm:max-w-[420px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2 text-rose-600">
                            <Trash2 className="h-5 w-5" />
                            {t('Delete Statutory Declaration')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Are you sure you want to delete declaration :num? This action cannot be undone.', { num: selectedDec?.declaration_number })}
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter className="pt-3">
                        <Button type="button" variant="outline" size="sm" onClick={() => setIsDeleteDecOpen(false)}>
                            {t('Cancel')}
                        </Button>
                        <Button type="button" variant="destructive" size="sm" onClick={handleDeleteDeclaration}>
                            {t('Confirm Delete')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* MODAL 5: Employee Statutory Profile Setup (CUD: Update Employee) */}
            <Dialog open={isEditEmpOpen} onOpenChange={setIsEditEmpOpen}>
                <DialogContent className="sm:max-w-[480px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <Users className="h-5 w-5 text-primary" />
                            {t('Employee Statutory Profile')}
                        </DialogTitle>
                        <DialogDescription>
                            {t('Configure statutory tax and social security parameters for :name (:code)', {
                                name: selectedEmp?.name,
                                code: selectedEmp?.employee_id,
                            })}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleUpdateEmployee} className="space-y-4 py-2">
                        <div className="space-y-1.5">
                            <Label htmlFor="emp_tin" className="text-xs font-semibold">{t('TRA Taxpayer Identification Number (TIN)')}</Label>
                            <Input
                                id="emp_tin"
                                placeholder="e.g. 123-456-789"
                                value={empData.tax_payer_id}
                                onChange={(e) => setEmpData(prev => ({ ...prev, tax_payer_id: e.target.value }))}
                                className="text-xs font-mono"
                            />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="emp_nssf" className="text-xs font-semibold">{t('NSSF Member Number')}</Label>
                            <Input
                                id="emp_nssf"
                                placeholder="e.g. NSSF-20819241"
                                value={empData.nssf_number}
                                onChange={(e) => setEmpData(prev => ({ ...prev, nssf_number: e.target.value }))}
                                className="text-xs font-mono"
                            />
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="emp_nhif" className="text-xs font-semibold">{t('NHIF Health Insurance Card No.')}</Label>
                            <Input
                                id="emp_nhif"
                                placeholder="e.g. NHIF-70192831"
                                value={empData.nhif_number}
                                onChange={(e) => setEmpData(prev => ({ ...prev, nhif_number: e.target.value }))}
                                className="text-xs font-mono"
                            />
                        </div>

                        <div className="p-3 bg-muted/40 rounded-lg border space-y-2 text-xs">
                            <div className="font-semibold text-foreground">{t('Statutory Levy Exemptions')}</div>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={empData.wcf_exempt}
                                    onChange={(e) => setEmpData(prev => ({ ...prev, wcf_exempt: e.target.checked }))}
                                    className="rounded border-input text-primary"
                                />
                                <span>{t('Exempt from Workers Compensation Fund (WCF 0.5%)')}</span>
                            </label>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={empData.sdl_exempt}
                                    onChange={(e) => setEmpData(prev => ({ ...prev, sdl_exempt: e.target.checked }))}
                                    className="rounded border-input text-primary"
                                />
                                <span>{t('Exempt from Skills Development Levy (SDL 3.5%)')}</span>
                            </label>
                        </div>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" size="sm" onClick={() => setIsEditEmpOpen(false)}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" size="sm" className="bg-primary text-primary-foreground">
                                {t('Save Profile')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* MODAL 6: View Official Filing Voucher with Scannable QR Seal */}
            <Dialog open={isVoucherOpen} onOpenChange={setIsVoucherOpen}>
                <DialogContent className="sm:max-w-[520px]">
                    <DialogHeader>
                        <DialogTitle className="flex items-center justify-between text-base">
                            <span className="flex items-center gap-2">
                                <QrCode className="h-5 w-5 text-primary" />
                                {t('Official Statutory Filing Voucher')}
                            </span>
                            {selectedDec && getStatusBadge(selectedDec.payment_status)}
                        </DialogTitle>
                    </DialogHeader>

                    {selectedDec && (
                        <div className="space-y-4 py-2 text-xs">
                            <div className="border rounded-lg p-4 bg-muted/20 space-y-3">
                                <div className="text-center border-b pb-3 space-y-1">
                                    <h3 className="font-bold text-sm tracking-wide uppercase">MUTAMARK ENTERPRISES LIMITED</h3>
                                    <p className="text-[11px] text-muted-foreground">Tanzania Statutory Remittance Filing Slip</p>
                                    <div className="font-mono text-xs font-semibold text-primary">{selectedDec.declaration_number}</div>
                                </div>

                                <div className="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <span className="text-muted-foreground block text-[11px]">{t('Filing Month')}</span>
                                        <span className="font-semibold font-mono">{selectedDec.filing_month}</span>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground block text-[11px]">{t('Statutory Due Date')}</span>
                                        <span className="font-semibold font-mono">{formatDate(selectedDec.due_date)}</span>
                                    </div>
                                    <div className="col-span-2">
                                        <span className="text-muted-foreground block text-[11px]">{t('GePG Control Number')}</span>
                                        <span className="font-mono text-base font-extrabold text-blue-600 dark:text-blue-400">
                                            {selectedDec.gepg_control_number || 'PENDING ASSIGNMENT'}
                                        </span>
                                    </div>
                                </div>

                                <div className="border-t pt-2 space-y-1 text-xs">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Employee Deduction')}:</span>
                                        <span className="font-mono">{formatCurrency(selectedDec.employee_deduction_amount)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Employer Contribution')}:</span>
                                        <span className="font-mono">{formatCurrency(selectedDec.employer_contribution_amount)}</span>
                                    </div>
                                    <div className="flex justify-between border-t pt-1 font-bold text-sm">
                                        <span>{t('Total Remittance Payable')}:</span>
                                        <span className="text-primary font-mono">{formatCurrency(selectedDec.total_payable_amount)}</span>
                                    </div>
                                </div>

                                {selectedDec.payment_date && (
                                    <div className="p-2.5 rounded bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 space-y-0.5">
                                        <div className="font-semibold flex items-center gap-1.5">
                                            <CheckCircle2 className="h-3.5 w-3.5" />
                                            {t('Payment Settled & Acknowledged')}
                                        </div>
                                        <div>Ref: {selectedDec.bank_reference} ({formatDate(selectedDec.payment_date)})</div>
                                    </div>
                                )}

                                {/* QR Seal Box */}
                                <div className="flex items-center justify-between p-3 rounded bg-card border">
                                    <div className="space-y-0.5">
                                        <div className="font-semibold text-foreground">{t('Cryptographic Seal')}</div>
                                        <div className="font-mono text-[10px] text-muted-foreground break-all max-w-[280px]">
                                            {selectedDec.verification_seal || '0x49f82847a98b11c9...'}
                                        </div>
                                        <p className="text-[10px] text-emerald-600 dark:text-emerald-400">{t('Verified by Mutamark Trust Engine')}</p>
                                    </div>
                                    <div className="h-16 w-16 bg-white p-1 rounded border flex items-center justify-center">
                                        <img 
                                            src={`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent((typeof window !== 'undefined' ? window.location.origin : '') + '/verify/document/' + (selectedDec.verification_seal || selectedDec.declaration_number))}`} 
                                            alt="Verification QR Code" 
                                            className="h-full w-full object-contain"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    <DialogFooter>
                        <Button type="button" variant="outline" size="sm" onClick={() => window.print()}>
                            <Printer className="h-4 w-4 mr-1.5" />
                            {t('Print Voucher')}
                        </Button>
                        <Button type="button" size="sm" onClick={() => setIsVoucherOpen(false)}>
                            {t('Close')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
