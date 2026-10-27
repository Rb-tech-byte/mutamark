import { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
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
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/components/ui/dialog";
import { Pagination } from "@/components/ui/pagination";
import NoRecordsFound from '@/components/no-records-found';
import { 
    ShieldAlert, 
    Lock, 
    CheckCircle2, 
    RefreshCw, 
    Download, 
    FileText, 
    Eye, 
    Search, 
    X, 
    Copy, 
    ExternalLink, 
    ShieldCheck, 
    AlertTriangle, 
    Key, 
    Boxes, 
    RotateCcw,
    Layers,
    Clock,
    Globe
} from "lucide-react";
import { formatDate } from '@/utils/helpers';
import { toast } from 'sonner';

interface AuditLogItem {
    id: number;
    audit_number: string;
    event_type: string;
    module: string;
    reference_type?: string;
    reference_id?: number;
    reference_number: string;
    actor_id?: number;
    actor_name: string;
    actor_role?: string;
    ip_address?: string;
    user_agent?: string;
    action: string;
    details?: string;
    old_values?: any;
    new_values?: any;
    severity: 'info' | 'warning' | 'critical' | 'compliance_alert';
    previous_hash?: string;
    current_hash: string;
    is_verified: boolean;
    created_at: string;
}

interface ChainIntegrity {
    is_valid: boolean;
    total_logs: number;
    verified_count: number;
    tampered_records: any[];
    latest_block_hash: string;
    verification_timestamp: string;
}

interface Kpis {
    total_logs: number;
    immutability_blocks: number;
    reversals_count: number;
    negative_stock_blocks: number;
    chain_integrity: ChainIntegrity;
}

interface Props {
    integrityExceptions?: { id:number; source_table:string; source_id:number; exception_type:string; resolution:string; recorded_at:string }[];
    logs: {
        data: AuditLogItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search: string;
        event_type: string;
        module: string;
        severity: string;
    };
    kpis: Kpis;
}

export default function AuditTrailIndex({ logs, filters, kpis, integrityExceptions = [] }: Props) {
    const reconciliation = useForm({ invoice_id:'', reason:'' });
    const { t } = useTranslation();

    // Filter states
    const [search, setSearch] = useState(filters.search || '');
    const [eventType, setEventType] = useState(filters.event_type || 'all');
    const [moduleFilter, setModuleFilter] = useState(filters.module || 'all');
    const [severityFilter, setSeverityFilter] = useState(filters.severity || 'all');

    // Modals
    const [selectedLog, setSelectedLog] = useState<AuditLogItem | null>(null);
    const [isDetailModalOpen, setIsDetailModalOpen] = useState(false);
    const [isReversalModalOpen, setIsReversalModalOpen] = useState(false);
    const [isChainVerifyModalOpen, setIsChainVerifyModalOpen] = useState(false);
    const [liveChainReport, setLiveChainReport] = useState<ChainIntegrity | null>(null);
    const [isVerifyingChain, setIsVerifyingChain] = useState(false);

    // Reversal Form
    const { data: revData, setData: setRevData, post: postRev, processing: revProcessing, reset: revReset, errors: revErrors } = useForm({
        journal_entry_id: '',
        reason: '',
    });

    const applyFilters = (newParams: Partial<typeof filters>) => {
        const query: Record<string, string> = {
            search: search,
            event_type: eventType === 'all' ? '' : eventType,
            module: moduleFilter === 'all' ? '' : moduleFilter,
            severity: severityFilter === 'all' ? '' : severityFilter,
            ...newParams,
        };

        // Remove empty keys
        Object.keys(query).forEach((k) => {
            if (!query[k]) delete query[k];
        });

        router.get(route('accounting.audit-trail.index'), query, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilters({ search });
    };

    const handleClearFilters = () => {
        setSearch('');
        setEventType('all');
        setModuleFilter('all');
        setSeverityFilter('all');
        router.get(route('accounting.audit-trail.index'));
    };

    const handleCopy = (text: string, label: string) => {
        navigator.clipboard.writeText(text);
        toast.success(t(`${label} copied to clipboard!`));
    };

    const handleExportCsv = () => {
        window.location.href = route('accounting.audit-trail.export');
    };

    const handleVerifyChain = async () => {
        setIsVerifyingChain(true);
        setIsChainVerifyModalOpen(true);
        try {
            const res = await fetch(route('api.compliance.verify-audit-chain'));
            const json = await res.json();
            if (json.success) {
                setLiveChainReport(json.data);
            }
        } catch (e) {
            toast.error(t('Failed to verify cryptographic chain'));
        } finally {
            setIsVerifyingChain(false);
        }
    };

    const handleExecuteReversal = (e: React.FormEvent) => {
        e.preventDefault();
        postRev(route('accounting.audit-trail.reverse-journal'), {
            onSuccess: () => {
                setIsReversalModalOpen(false);
                revReset();
            },
        });
    };

    const getSeverityBadge = (sev: string) => {
        switch (sev) {
            case 'critical':
                return <Badge variant="destructive" className="font-semibold text-[11px] uppercase tracking-wide">CRITICAL</Badge>;
            case 'warning':
                return <Badge variant="outline" className="border-amber-500/40 text-amber-600 dark:text-amber-400 bg-amber-500/10 font-medium text-[11px] uppercase">WARNING</Badge>;
            case 'compliance_alert':
                return <Badge variant="outline" className="border-purple-500/40 text-purple-600 dark:text-purple-400 bg-purple-500/10 font-medium text-[11px] uppercase">COMPLIANCE</Badge>;
            default:
                return <Badge variant="secondary" className="text-muted-foreground text-[11px] uppercase">INFO</Badge>;
        }
    };

    const getEventTypeBadge = (type: string) => {
        switch (type) {
            case 'IMMUTABILITY_BLOCKED':
                return (
                    <Badge variant="outline" className="border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400 font-medium text-xs">
                        <Lock className="h-3 w-3 mr-1" />
                        Immutability Block
                    </Badge>
                );
            case 'NEGATIVE_STOCK_BLOCKED':
                return (
                    <Badge variant="outline" className="border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-medium text-xs">
                        <AlertTriangle className="h-3 w-3 mr-1" />
                        Stock Guard Block
                    </Badge>
                );
            case 'JOURNAL_REVERSED':
                return (
                    <Badge variant="outline" className="border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium text-xs">
                        <RotateCcw className="h-3 w-3 mr-1" />
                        Journal Reversal (RJV)
                    </Badge>
                );
            case 'JOURNAL_POSTED':
                return (
                    <Badge variant="outline" className="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium text-xs">
                        <CheckCircle2 className="h-3 w-3 mr-1" />
                        Journal Posted
                    </Badge>
                );
            case 'INVOICE_LOCKED':
                return (
                    <Badge variant="outline" className="border-purple-500/30 bg-purple-500/10 text-purple-600 dark:text-purple-400 font-medium text-xs">
                        <ShieldCheck className="h-3 w-3 mr-1" />
                        Invoice Locked
                    </Badge>
                );
            default:
                return <Badge variant="outline" className="text-xs">{type}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Accounting'), url: route('account.index') },
                { label: t('Financial Audit & Controls'), url: route('accounting.audit-trail.index') }
            ]}
        >
            <Head title={t('Financial Controls & Audit Trail Ledger')} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground">
                                {t('Financial Controls & Cryptographic Audit Ledger')}
                            </h1>
                            <Badge variant="outline" className="border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium">
                                <Key className="h-3.5 w-3.5 mr-1" />
                                {t('SHA-256 Tamper-Evident Chain')}
                            </Badge>
                        </div>
                        <p className="text-sm text-muted-foreground mt-1">
                            {t('Enforcing Appendix B financial immutability, negative stock guards, Reversal Journal Vouchers (RJV), and regulatory compliance logs.')}
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Button 
                            variant="outline" 
                            size="sm" 
                            onClick={handleVerifyChain}
                            className="text-xs"
                        >
                            <ShieldCheck className="h-4 w-4 mr-1.5 text-emerald-500" />
                            {t('Verify Chain Integrity')}
                        </Button>

                        <Button 
                            variant="outline" 
                            size="sm" 
                            onClick={() => setIsReversalModalOpen(true)}
                            className="text-xs"
                        >
                            <RotateCcw className="h-4 w-4 mr-1.5 text-blue-500" />
                            {t('Execute Reversal (RJV)')}
                        </Button>

                        <Button 
                            variant="outline" 
                            size="sm" 
                            onClick={handleExportCsv}
                            className="text-xs"
                        >
                            <Download className="h-4 w-4 mr-1.5 text-primary" />
                            {t('Export Audit Ledger (CSV)')}
                        </Button>
                    </div>
                </div>

                {/* KPI Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {/* Total Events */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Total Audit Records')}
                            </CardTitle>
                            <Layers className="h-4 w-4 text-primary" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {kpis.total_logs}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Continuous blockchain-style event log')}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Immutability Blocks */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Immutability Blocks')}
                            </CardTitle>
                            <Lock className="h-4 w-4 text-rose-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-rose-600 dark:text-rose-400">
                                {kpis.immutability_blocks}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Prohibited invoice & journal alterations prevented')}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Reversals Executed */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Reversal Vouchers (RJV)')}
                            </CardTitle>
                            <RotateCcw className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {kpis.reversals_count}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Authorized ledger reversals with audit trail')}
                            </p>
                        </CardContent>
                    </Card>

                    {/* Chain Integrity */}
                    <Card className="bg-card/60 backdrop-blur-sm border shadow-sm">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                {t('Cryptographic Integrity')}
                            </CardTitle>
                            <ShieldCheck className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                <span>{kpis.chain_integrity.is_valid ? t('100% Valid') : t('Compromised')}</span>
                            </div>
                            <p className="text-xs text-muted-foreground mt-1 font-mono truncate">
                                {kpis.chain_integrity.verified_count} {t('blocks verified chained')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters Bar */}
                <Card className="border shadow-sm bg-card">
                    <CardContent className="p-4">
                        <form onSubmit={handleSearchSubmit} className="flex flex-col lg:flex-row gap-3 items-center justify-between">
                            <div className="flex flex-1 w-full flex-col sm:flex-row gap-3 items-center">
                                {/* Search Input */}
                                <div className="relative w-full sm:w-72">
                                    <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        type="text"
                                        placeholder={t('Search reference, audit #, actor...')}
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="pl-9 text-xs"
                                    />
                                    {search && (
                                        <button
                                            type="button"
                                            onClick={() => { setSearch(''); applyFilters({ search: '' }); }}
                                            className="absolute right-2.5 top-2.5 text-muted-foreground hover:text-foreground"
                                        >
                                            <X className="h-3.5 w-3.5" />
                                        </button>
                                    )}
                                </div>

                                {/* Module Filter */}
                                <div className="w-full sm:w-44">
                                    <Select 
                                        value={moduleFilter} 
                                        onValueChange={(val) => {
                                            setModuleFilter(val);
                                            applyFilters({ module: val === 'all' ? '' : val });
                                        }}
                                    >
                                        <SelectTrigger className="text-xs">
                                            <SelectValue placeholder={t('Filter Module')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">{t('All Modules')}</SelectItem>
                                            <SelectItem value="Accounting">{t('Accounting')}</SelectItem>
                                            <SelectItem value="Sales">{t('Sales & Invoicing')}</SelectItem>
                                            <SelectItem value="Procurement">{t('Procurement')}</SelectItem>
                                            <SelectItem value="Inventory">{t('Inventory')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Event Type Filter */}
                                <div className="w-full sm:w-52">
                                    <Select 
                                        value={eventType} 
                                        onValueChange={(val) => {
                                            setEventType(val);
                                            applyFilters({ event_type: val === 'all' ? '' : val });
                                        }}
                                    >
                                        <SelectTrigger className="text-xs">
                                            <SelectValue placeholder={t('Filter Event Type')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">{t('All Events')}</SelectItem>
                                            <SelectItem value="IMMUTABILITY_BLOCKED">{t('Immutability Block')}</SelectItem>
                                            <SelectItem value="NEGATIVE_STOCK_BLOCKED">{t('Negative Stock Guard')}</SelectItem>
                                            <SelectItem value="JOURNAL_REVERSED">{t('Journal Reversal (RJV)')}</SelectItem>
                                            <SelectItem value="JOURNAL_POSTED">{t('Journal Posted')}</SelectItem>
                                            <SelectItem value="INVOICE_LOCKED">{t('Invoice Locked')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Severity Filter */}
                                <div className="w-full sm:w-40">
                                    <Select 
                                        value={severityFilter} 
                                        onValueChange={(val) => {
                                            setSeverityFilter(val);
                                            applyFilters({ severity: val === 'all' ? '' : val });
                                        }}
                                    >
                                        <SelectTrigger className="text-xs">
                                            <SelectValue placeholder={t('Severity')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">{t('All Severities')}</SelectItem>
                                            <SelectItem value="critical">{t('Critical')}</SelectItem>
                                            <SelectItem value="warning">{t('Warning')}</SelectItem>
                                            <SelectItem value="compliance_alert">{t('Compliance Alert')}</SelectItem>
                                            <SelectItem value="info">{t('Info')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div className="flex items-center gap-2 w-full lg:w-auto justify-end">
                                <Button type="submit" size="sm" variant="secondary" className="text-xs">
                                    <Search className="h-3.5 w-3.5 mr-1" />
                                    {t('Apply')}
                                </Button>
                                {(filters.search || filters.module || filters.event_type || filters.severity) && (
                                    <Button type="button" size="sm" variant="ghost" onClick={handleClearFilters} className="text-xs text-muted-foreground">
                                        {t('Reset')}
                                    </Button>
                                )}
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Main Audit Log Table */}
                <Card className="border shadow-sm bg-card">
                    <CardHeader className="pb-3">
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-base font-semibold">
                                    {t('Immutable Financial Audit Trail')}
                                </CardTitle>
                                <CardDescription className="text-xs">
                                    {t('All ledger operations are cryptographically chained with SHA-256 signatures to prevent tampering.')}
                                </CardDescription>
                            </div>
                            <Badge variant="outline" className="text-xs font-mono">
                                {logs.total} {t('Records')}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {logs.data.length === 0 ? (
                            <div className="p-8">
                                <NoRecordsFound 
                                    icon={ShieldAlert} 
                                    title={t('No financial audit logs found')} 
                                    description={t('No audit events matched your search filters.')} 
                                />
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-muted/40">
                                            <TableHead className="font-semibold text-xs">{t('Audit Ref')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('Timestamp')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('Event Type')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('Doc Ref')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('Module')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('Actor')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('Action Summary')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('Severity')}</TableHead>
                                            <TableHead className="font-semibold text-xs">{t('SHA-256 Hash')}</TableHead>
                                            <TableHead className="font-semibold text-xs text-right">{t('Action')}</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {logs.data.map((item) => (
                                            <TableRow key={item.id} className="hover:bg-muted/30">
                                                <TableCell className="font-mono text-xs font-semibold">
                                                    <div className="flex items-center gap-1.5">
                                                        <span>{item.audit_number}</span>
                                                        <button 
                                                            type="button" 
                                                            onClick={() => handleCopy(item.audit_number, 'Audit #')}
                                                            className="text-muted-foreground hover:text-foreground opacity-60 hover:opacity-100"
                                                        >
                                                            <Copy className="h-3 w-3" />
                                                        </button>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-xs text-muted-foreground whitespace-nowrap">
                                                    {formatDate(item.created_at)}
                                                </TableCell>
                                                <TableCell className="text-xs whitespace-nowrap">
                                                    {getEventTypeBadge(item.event_type)}
                                                </TableCell>
                                                <TableCell className="text-xs font-mono font-medium">
                                                    {item.reference_number}
                                                </TableCell>
                                                <TableCell className="text-xs">
                                                    <Badge variant="outline" className="text-[11px] bg-muted/30">
                                                        {item.module}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-xs">
                                                    <div className="font-medium">{item.actor_name}</div>
                                                    <span className="text-[11px] text-muted-foreground">{item.actor_role ?? 'Staff'}</span>
                                                </TableCell>
                                                <TableCell className="text-xs max-w-xs truncate" title={item.action}>
                                                    {item.action}
                                                </TableCell>
                                                <TableCell className="text-xs whitespace-nowrap">
                                                    {getSeverityBadge(item.severity)}
                                                </TableCell>
                                                <TableCell className="text-xs font-mono text-muted-foreground">
                                                    <div className="flex items-center gap-1">
                                                        <span className="truncate w-16" title={item.current_hash}>
                                                            {item.current_hash.substring(0, 8)}...
                                                        </span>
                                                        <button 
                                                            type="button" 
                                                            onClick={() => handleCopy(item.current_hash, 'Hash')}
                                                            className="text-muted-foreground hover:text-foreground"
                                                        >
                                                            <Copy className="h-3 w-3" />
                                                        </button>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-xs text-right whitespace-nowrap">
                                                    <Button 
                                                        variant="ghost" 
                                                        size="sm" 
                                                        onClick={() => { setSelectedLog(item); setIsDetailModalOpen(true); }}
                                                        className="h-8 w-8 p-0"
                                                    >
                                                        <Eye className="h-4 w-4 text-primary" />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}

                        {logs.total > logs.per_page && (
                            <div className="p-4 border-t">
                                <Pagination 
                                    data={logs} 
                                    routeName="accounting.audit-trail.index" 
                                    filters={{
                                        search: filters.search,
                                        module: filters.module,
                                        event_type: filters.event_type,
                                        severity: filters.severity,
                                    }} 
                                />
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Detail Inspector Modal */}
                <Dialog open={isDetailModalOpen} onOpenChange={setIsDetailModalOpen}>
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-base">
                                <ShieldAlert className="h-5 w-5 text-primary" />
                                {t('Financial Audit Log Entry Inspector')}
                            </DialogTitle>
                            <DialogDescription className="text-xs">
                                {t('Cryptographic record details and state snapshot.')}
                            </DialogDescription>
                        </DialogHeader>

                        {selectedLog && (
                            <div className="space-y-4 text-xs">
                                <div className="grid grid-cols-2 gap-3 p-3 rounded-lg border bg-muted/30">
                                    <div>
                                        <span className="text-muted-foreground">{t('Audit Reference')}:</span>
                                        <div className="font-mono font-bold mt-0.5">{selectedLog.audit_number}</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('Document Reference')}:</span>
                                        <div className="font-mono font-bold mt-0.5">{selectedLog.reference_number}</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('Actor')}:</span>
                                        <div className="font-medium mt-0.5">{selectedLog.actor_name} ({selectedLog.actor_role ?? 'Staff'})</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('IP Address & Time')}:</span>
                                        <div className="font-mono mt-0.5">{selectedLog.ip_address ?? '127.0.0.1'} | {formatDate(selectedLog.created_at)}</div>
                                    </div>
                                </div>

                                <div>
                                    <span className="font-semibold text-foreground">{t('Action Summary')}:</span>
                                    <p className="mt-1 p-2 rounded bg-muted/40 border">{selectedLog.action}</p>
                                </div>

                                {selectedLog.details && (
                                    <div>
                                        <span className="font-semibold text-foreground">{t('Compliance Justification / Details')}:</span>
                                        <p className="mt-1 p-2 rounded bg-muted/40 border text-muted-foreground">{selectedLog.details}</p>
                                    </div>
                                )}

                                {/* Old vs New state diffs */}
                                {(selectedLog.old_values || selectedLog.new_values) && (
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <span className="font-semibold text-rose-500">{t('Previous State (Before)')}:</span>
                                            <pre className="mt-1 p-2 rounded bg-muted/50 border font-mono text-[11px] overflow-x-auto max-h-36">
                                                {JSON.stringify(selectedLog.old_values, null, 2) || t('None')}
                                            </pre>
                                        </div>
                                        <div>
                                            <span className="font-semibold text-emerald-500">{t('New / Attempted State (After)')}:</span>
                                            <pre className="mt-1 p-2 rounded bg-muted/50 border font-mono text-[11px] overflow-x-auto max-h-36">
                                                {JSON.stringify(selectedLog.new_values, null, 2) || t('None')}
                                            </pre>
                                        </div>
                                    </div>
                                )}

                                {/* Cryptographic Signature Block */}
                                <div className="p-3 rounded-lg border bg-card space-y-1.5 font-mono text-[11px]">
                                    <div className="flex items-center justify-between">
                                        <span className="text-muted-foreground">{t('Previous Block SHA-256')}:</span>
                                        <span className="text-muted-foreground truncate w-64 text-right">{selectedLog.previous_hash ?? 'GENESIS_HASH_MUTAMARK_ERP_2026'}</span>
                                    </div>
                                    <div className="flex items-center justify-between border-t pt-1">
                                        <span className="font-bold text-foreground">{t('Current Block SHA-256')}:</span>
                                        <span className="font-bold text-emerald-600 dark:text-emerald-400 truncate w-64 text-right">{selectedLog.current_hash}</span>
                                    </div>
                                </div>
                            </div>
                        )}

                        <DialogFooter>
                            <Button variant="outline" size="sm" onClick={() => setIsDetailModalOpen(false)}>
                                {t('Close Inspector')}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* Journal Reversal Modal */}
                <Dialog open={isReversalModalOpen} onOpenChange={setIsReversalModalOpen}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-base">
                                <RotateCcw className="h-5 w-5 text-blue-500" />
                                {t('Execute Reversal Journal Voucher (RJV)')}
                            </DialogTitle>
                            <DialogDescription className="text-xs">
                                {t('Under Appendix B, posted journal vouchers cannot be deleted. Execute an offsetting RJV with reversed debits and credits.')}
                            </DialogDescription>
                        </DialogHeader>

                        <form onSubmit={handleExecuteReversal} className="space-y-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="journal_entry_id" className="text-xs">{t('Journal Entry ID to Reverse')}</Label>
                                <Input
                                    id="journal_entry_id"
                                    type="number"
                                    placeholder="e.g. 1"
                                    value={revData.journal_entry_id}
                                    onChange={(e) => setRevData('journal_entry_id', e.target.value)}
                                    required
                                    className="text-xs font-mono"
                                />
                                {revErrors.journal_entry_id && (
                                    <p className="text-[11px] text-destructive">{revErrors.journal_entry_id}</p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="reason" className="text-xs">{t('Audit Justification & Reason')}</Label>
                                <Textarea
                                    id="reason"
                                    placeholder={t('Explain the correction or reason for ledger reversal...')}
                                    value={revData.reason}
                                    onChange={(e) => setRevData('reason', e.target.value)}
                                    required
                                    rows={3}
                                    className="text-xs"
                                />
                                {revErrors.reason && (
                                    <p className="text-[11px] text-destructive">{revErrors.reason}</p>
                                )}
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" size="sm" onClick={() => setIsReversalModalOpen(false)}>
                                    {t('Cancel')}
                                </Button>
                                <Button type="submit" size="sm" disabled={revProcessing} className="bg-blue-600 hover:bg-blue-700 text-white">
                                    {revProcessing ? t('Processing...') : t('Confirm & Post RJV')}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* Cryptographic Chain Integrity Verification Modal */}
                <Dialog open={isChainVerifyModalOpen} onOpenChange={setIsChainVerifyModalOpen}>
                    <DialogContent className="max-w-lg">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-base">
                                <ShieldCheck className="h-5 w-5 text-emerald-500" />
                                {t('Cryptographic SHA-256 Audit Chain Validation')}
                            </DialogTitle>
                            <DialogDescription className="text-xs">
                                {t('Real-time mathematical verification of the sequential blockchain-style ledger.')}
                            </DialogDescription>
                        </DialogHeader>

                        {isVerifyingChain ? (
                            <div className="p-8 text-center space-y-2">
                                <RefreshCw className="h-8 w-8 animate-spin mx-auto text-primary" />
                                <p className="text-xs text-muted-foreground">{t('Validating hash chain across all historical audit entries...')}</p>
                            </div>
                        ) : liveChainReport ? (
                            <div className="space-y-4 text-xs">
                                <div className={`p-4 rounded-lg border flex items-center gap-3 ${
                                    liveChainReport.is_valid 
                                        ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-400' 
                                        : 'bg-rose-500/10 border-rose-500/30 text-rose-700 dark:text-rose-400'
                                }`}>
                                    <CheckCircle2 className="h-6 w-6 shrink-0" />
                                    <div>
                                        <div className="font-bold text-sm">
                                            {liveChainReport.is_valid ? t('Integrity Verified: No Tampering Detected') : t('Integrity Alert: Chain Break Detected')}
                                        </div>
                                        <p className="text-xs opacity-90 mt-0.5">
                                            {liveChainReport.verified_count} of {liveChainReport.total_logs} {t('audit blocks mathematically verified in sequence.')}
                                        </p>
                                    </div>
                                </div>

                                <div className="space-y-2 font-mono text-[11px] p-3 rounded-lg border bg-muted/30">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Total Historical Blocks')}:</span>
                                        <span className="font-bold">{liveChainReport.total_logs}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Verified Consecutive Hashes')}:</span>
                                        <span className="font-bold text-emerald-600 dark:text-emerald-400">{liveChainReport.verified_count}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Compromised Blocks')}:</span>
                                        <span className="font-bold">{liveChainReport.tampered_records.length}</span>
                                    </div>
                                    <div className="border-t pt-1 flex justify-between">
                                        <span className="text-muted-foreground">{t('Latest Block Hash')}:</span>
                                        <span className="truncate w-48 text-right font-medium">{liveChainReport.latest_block_hash.substring(0, 16)}...</span>
                                    </div>
                                </div>
                            </div>
                        ) : null}

                        <DialogFooter>
                            <Button variant="outline" size="sm" onClick={() => setIsChainVerifyModalOpen(false)}>
                                {t('Done')}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
            <Card className="mt-6"><CardHeader><CardTitle>{t('Finance integrity exceptions')}</CardTitle><CardDescription>{t('Historical records are preserved. Verify settlement evidence before assigning an invoice or advance.')}</CardDescription></CardHeader><CardContent>
                <Table><TableHeader><TableRow><TableHead>{t('Record')}</TableHead><TableHead>{t('Issue')}</TableHead><TableHead>{t('Review required')}</TableHead></TableRow></TableHeader><TableBody>{integrityExceptions.map(issue=><TableRow key={issue.id}><TableCell>{issue.source_table} #{issue.source_id}</TableCell><TableCell>{t(issue.exception_type)}</TableCell><TableCell>{issue.resolution}</TableCell></TableRow>)}</TableBody></Table>
                <form className="mt-4 space-y-3" onSubmit={event=>{event.preventDefault();reconciliation.post(route('accounting.audit-trail.reconcile-invoice',reconciliation.data.invoice_id));}}><Label htmlFor="reconcile-invoice">{t('Reconcile invoice ID to cleared allocations')}</Label><Input id="reconcile-invoice" type="number" min="1" required value={reconciliation.data.invoice_id} onChange={event=>reconciliation.setData('invoice_id',event.target.value)} /><Label htmlFor="reconcile-reason">{t('Reason')}</Label><Textarea id="reconcile-reason" required minLength={10} value={reconciliation.data.reason} onChange={event=>reconciliation.setData('reason',event.target.value)} />{Object.values(reconciliation.errors).map((error,index)=><p role="alert" className="text-sm text-destructive" key={index}>{error}</p>)}<Button disabled={reconciliation.processing}>{t('Reconcile derived balances')}</Button></form>
            </CardContent></Card>
        </AuthenticatedLayout>
    );
}
