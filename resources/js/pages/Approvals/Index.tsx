import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import ApprovalDelegations from '@/components/approval-delegations';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { 
    CheckCircle2, 
    Clock, 
    AlertTriangle, 
    ShieldCheck, 
    Layers, 
    Send, 
    RotateCcw, 
    XCircle, 
    FileText, 
    Search, 
    SlidersHorizontal, 
    Plus, 
    Lock, 
    ExternalLink, 
    Check, 
    QrCode, 
    ShieldAlert, 
    User, 
    DollarSign,
    PenTool
} from 'lucide-react';

interface ApprovalProps {
    delegations?: any[];
    metrics: {
        pending_my_approval: number;
        total_pending: number;
        total_requests: number;
        approved_count: number;
        total_approved_amount: number;
    };
    my_pending: any[];
    all_requests: any[];
    policies: any[];
    recent_actions: any[];
    users: any[];
}

export default function ApprovalsIndex({
    metrics = {
        pending_my_approval: 0,
        total_pending: 0,
        total_requests: 0,
        approved_count: 0,
        total_approved_amount: 0,
    },
    my_pending = [],
    all_requests = [],
    policies = [],
    recent_actions = [],
    users = [],
    delegations = [],
}: ApprovalProps) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState('pending');
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [docTypeFilter, setDocTypeFilter] = useState('all');

    // Modals
    const [showActionModal, setShowActionModal] = useState(false);
    const [showPolicyModal, setShowPolicyModal] = useState(false);
    const [showSubmitModal, setShowSubmitModal] = useState(false);
    const [showDetailsModal, setShowDetailsModal] = useState(false);

    const [selectedRequest, setSelectedRequest] = useState<any>(null);
    const [actionType, setActionType] = useState<string>('approve');

    // Action Form
    const actionForm = useForm({
        action: 'approve',
        comments: '',
    });

    // Policy Form
    const policyForm = useForm({
        id: '',
        name: '',
        document_type: 'purchase_invoice',
        tier_level: '1',
        tier_name: '',
        min_amount: '0',
        max_amount: '',
        required_role: 'officer',
        approver_id: '',
        requires_digital_signature: false,
        enforce_segregation_of_duties: true,
        is_active: true,
    });

    // Submit Document Form
    const submitForm = useForm({
        document_type: 'purchase_invoice',
        document_id: '',
        notes: '',
    });

    // Open Action Modal
    const handleOpenAction = (request: any, action: string) => {
        setSelectedRequest(request);
        setActionType(action);
        actionForm.setData({
            action: action,
            comments: '',
        });
        setShowActionModal(true);
    };

    // Submit Action
    const handleSubmitAction = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedRequest) return;

        actionForm.post(route('approvals.action', selectedRequest.id), {
            onSuccess: () => {
                setShowActionModal(false);
                actionForm.reset();
            },
        });
    };

    // Submit Policy
    const handleSavePolicy = (e: React.FormEvent) => {
        e.preventDefault();
        policyForm.post(route('approvals.policies.store'), {
            onSuccess: () => {
                setShowPolicyModal(false);
                policyForm.reset();
            },
        });
    };

    // Toggle Policy Active
    const handleTogglePolicy = (id: number) => {
        router.post(route('approvals.policies.toggle', id));
    };

    // Submit Document
    const handleSubmitDocument = (e: React.FormEvent) => {
        e.preventDefault();
        submitForm.post(route('approvals.submit'), {
            onSuccess: () => {
                setShowSubmitModal(false);
                submitForm.reset();
            },
        });
    };

    // Filtered All Requests
    const filteredRequests = all_requests.filter((r) => {
        const matchesSearch = 
            r.request_number?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            r.document_number?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            r.title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            r.requester?.name?.toLowerCase().includes(searchTerm.toLowerCase());

        const matchesStatus = statusFilter === 'all' || r.status === statusFilter;
        const matchesDocType = docTypeFilter === 'all' || r.document_type === docTypeFilter;

        return matchesSearch && matchesStatus && matchesDocType;
    });

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'approved':
                return <Badge className="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{t('Approved')}</Badge>;
            case 'signed':
                return <Badge className="bg-purple-500/10 text-purple-600 border border-purple-500/20">{t('Signed & Sealed')}</Badge>;
            case 'under_review':
                return <Badge className="bg-amber-500/10 text-amber-600 border border-amber-500/20">{t('Under Review')}</Badge>;
            case 'submitted':
                return <Badge className="bg-blue-500/10 text-blue-600 border border-blue-500/20">{t('Submitted')}</Badge>;
            case 'returned':
                return <Badge className="bg-orange-500/10 text-orange-600 border border-orange-500/20">{t('Changes Requested')}</Badge>;
            case 'rejected':
                return <Badge className="bg-rose-500/10 text-rose-600 border border-rose-500/20">{t('Rejected')}</Badge>;
            case 'issued':
                return <Badge className="bg-indigo-500/10 text-indigo-600 border border-indigo-500/20">{t('Issued')}</Badge>;
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const getDocTypeLabel = (type: string) => {
        switch (type) {
            case 'purchase_invoice':
                return t('Supplier Invoice / Bill');
            case 'expense':
                return t('Operational Expense');
            case 'sales_proposal':
                return t('Sales Quotation / Proposal');
            case 'asset_disposal':
                return t('Asset Write-Off / Disposal');
            default:
                return type;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={t('Multi-Tier Approval Workflows')} />
            <ApprovalDelegations users={users} delegations={delegations} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
                            <CheckCircle2 className="h-7 w-7 text-primary" />
                            {t('Multi-Tier Approval Workflow Engine')}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {t('SRS Module 24: Escalation thresholds, segregation of duties, multi-tier approvals & cryptographic seals')}
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            onClick={() => {
                                policyForm.reset();
                                setShowPolicyModal(true);
                            }}
                            className="flex items-center gap-2"
                        >
                            <SlidersHorizontal className="h-4 w-4" />
                            {t('New Policy Rule')}
                        </Button>
                        <Button
                            onClick={() => {
                                submitForm.reset();
                                setShowSubmitModal(true);
                            }}
                            className="flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            <Plus className="h-4 w-4" />
                            {t('Submit Document')}
                        </Button>
                    </div>
                </div>

                {/* 4 Top KPI Metric Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Pending Action')}
                            </CardTitle>
                            <Clock className="h-4 w-4 text-amber-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {metrics.pending_my_approval}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Awaiting current reviewer tier')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Total In Pipeline')}
                            </CardTitle>
                            <Layers className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {metrics.total_pending} / {metrics.total_requests}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Active requests vs total initiated')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Approved Value (TZS)')}
                            </CardTitle>
                            <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-600">
                                {formatCurrency(metrics.total_approved_amount)}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {metrics.approved_count} {t('documents approved & sealed')}
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border bg-card">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {t('Active Policies')}
                            </CardTitle>
                            <ShieldCheck className="h-4 w-4 text-purple-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-foreground">
                                {policies.filter((p) => p.is_active).length}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                {t('Amount escalation tiers configured')}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Tabs Navigation */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <TabsList className="bg-muted p-1 rounded-lg">
                        <TabsTrigger value="pending" className="flex items-center gap-2">
                            <Clock className="h-4 w-4" />
                            {t('Pending Approvals')} ({my_pending.length})
                        </TabsTrigger>
                        <TabsTrigger value="all" className="flex items-center gap-2">
                            <Layers className="h-4 w-4" />
                            {t('All Workflow Requests')} ({all_requests.length})
                        </TabsTrigger>
                        <TabsTrigger value="policies" className="flex items-center gap-2">
                            <SlidersHorizontal className="h-4 w-4" />
                            {t('Escalation Policies')} ({policies.length})
                        </TabsTrigger>
                        <TabsTrigger value="audit" className="flex items-center gap-2">
                            <ShieldCheck className="h-4 w-4" />
                            {t('Audit Trail & Signatures')}
                        </TabsTrigger>
                    </TabsList>

                    {/* TAB 1: PENDING MY APPROVAL */}
                    <TabsContent value="pending" className="space-y-4">
                        {my_pending.length === 0 ? (
                            <Card className="border-border bg-card p-12 text-center">
                                <CheckCircle2 className="h-12 w-12 text-emerald-500 mx-auto mb-3" />
                                <h3 className="text-lg font-medium text-foreground">{t('No Pending Approvals')}</h3>
                                <p className="text-sm text-muted-foreground mt-1 max-w-md mx-auto">
                                    {t('All workflow requests assigned to your escalation tier have been reviewed. Good job!')}
                                </p>
                            </Card>
                        ) : (
                            <div className="grid grid-cols-1 gap-4">
                                {my_pending.map((req) => (
                                    <Card key={req.id} className="border-border bg-card shadow-sm hover:border-primary/40 transition-colors">
                                        <CardContent className="p-5 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                                            <div className="space-y-2 flex-1">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="font-mono text-xs font-semibold px-2.5 py-0.5 rounded bg-muted text-foreground">
                                                        {req.request_number}
                                                    </span>
                                                    <Badge variant="outline" className="text-xs">
                                                        {getDocTypeLabel(req.document_type)}: {req.document_number}
                                                    </Badge>
                                                    {getStatusBadge(req.status)}
                                                    <Badge className="bg-primary/10 text-primary border border-primary/20 text-xs">
                                                        {t('Tier')} {req.current_tier} / {req.max_tier}
                                                    </Badge>
                                                </div>

                                                <h3 className="text-base font-semibold text-foreground">
                                                    {req.title}
                                                </h3>

                                                <div className="flex flex-wrap items-center gap-x-6 gap-y-1 text-xs text-muted-foreground">
                                                    <span className="flex items-center gap-1">
                                                        <User className="h-3.5 w-3.5" />
                                                        {t('Initiated by')}: <strong className="text-foreground">{req.requester?.name || 'User'}</strong>
                                                    </span>
                                                    <span className="flex items-center gap-1">
                                                        <DollarSign className="h-3.5 w-3.5" />
                                                        {t('Amount')}: <strong className="text-foreground text-sm font-bold text-primary">{formatCurrency(req.amount)} {req.currency}</strong>
                                                    </span>
                                                    <span>
                                                        {t('Date')}: {formatDate(req.created_at)}
                                                    </span>
                                                </div>

                                                {req.notes && (
                                                    <p className="text-xs bg-muted/60 p-2 rounded text-muted-foreground border border-border/50">
                                                        <strong className="text-foreground">{t('Note')}:</strong> {req.notes}
                                                    </p>
                                                )}
                                            </div>

                                            {/* Action Buttons */}
                                            <div className="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-end pt-2 lg:pt-0 border-t lg:border-t-0 border-border">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => {
                                                        setSelectedRequest(req);
                                                        setShowDetailsModal(true);
                                                    }}
                                                >
                                                    <FileText className="h-4 w-4 mr-1" />
                                                    {t('History')}
                                                </Button>

                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    className="text-amber-600 border-amber-500/30 hover:bg-amber-500/10"
                                                    onClick={() => handleOpenAction(req, 'return')}
                                                >
                                                    <RotateCcw className="h-4 w-4 mr-1" />
                                                    {t('Request Changes')}
                                                </Button>

                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    className="text-rose-600 border-rose-500/30 hover:bg-rose-500/10"
                                                    onClick={() => handleOpenAction(req, 'reject')}
                                                >
                                                    <XCircle className="h-4 w-4 mr-1" />
                                                    {t('Reject')}
                                                </Button>

                                                <Button
                                                    size="sm"
                                                    className="bg-emerald-600 hover:bg-emerald-700 text-white"
                                                    onClick={() => handleOpenAction(req, 'approve')}
                                                >
                                                    <Check className="h-4 w-4 mr-1" />
                                                    {req.current_tier < req.max_tier ? t('Approve & Escalate') : t('Approve')}
                                                </Button>

                                                {req.status === 'approved' && (
                                                    <Button
                                                        size="sm"
                                                        className="bg-purple-600 hover:bg-purple-700 text-white"
                                                        onClick={() => handleOpenAction(req, 'sign')}
                                                    >
                                                        <PenTool className="h-4 w-4 mr-1" />
                                                        {t('Sign & Seal')}
                                                    </Button>
                                                )}
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </TabsContent>

                    {/* TAB 2: ALL WORKFLOW REQUESTS */}
                    <TabsContent value="all" className="space-y-4">
                        <Card className="border-border bg-card">
                            <CardHeader className="p-4 pb-2">
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                    <div className="relative w-full sm:w-80">
                                        <Search className="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder={t('Search requests, documents, users...')}
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-9"
                                        />
                                    </div>

                                    <div className="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                                        <Select value={statusFilter} onValueChange={setStatusFilter}>
                                            <SelectTrigger className="w-36">
                                                <SelectValue placeholder={t('Status')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">{t('All Statuses')}</SelectItem>
                                                <SelectItem value="submitted">{t('Submitted')}</SelectItem>
                                                <SelectItem value="under_review">{t('Under Review')}</SelectItem>
                                                <SelectItem value="approved">{t('Approved')}</SelectItem>
                                                <SelectItem value="signed">{t('Signed & Sealed')}</SelectItem>
                                                <SelectItem value="returned">{t('Returned')}</SelectItem>
                                                <SelectItem value="rejected">{t('Rejected')}</SelectItem>
                                            </SelectContent>
                                        </Select>

                                        <Select value={docTypeFilter} onValueChange={setDocTypeFilter}>
                                            <SelectTrigger className="w-44">
                                                <SelectValue placeholder={t('Document Type')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">{t('All Document Types')}</SelectItem>
                                                <SelectItem value="purchase_invoice">{t('Supplier Invoices')}</SelectItem>
                                                <SelectItem value="expense">{t('Operational Expenses')}</SelectItem>
                                                <SelectItem value="sales_proposal">{t('Sales Proposals')}</SelectItem>
                                                <SelectItem value="asset_disposal">{t('Asset Disposals')}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent className="p-0">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm text-left">
                                        <thead className="bg-muted text-muted-foreground uppercase text-[11px] font-semibold border-b border-border">
                                            <tr>
                                                <th className="px-4 py-3">{t('Request / Date')}</th>
                                                <th className="px-4 py-3">{t('Document Ref')}</th>
                                                <th className="px-4 py-3">{t('Title / Initiator')}</th>
                                                <th className="px-4 py-3 text-right">{t('Amount (TZS)')}</th>
                                                <th className="px-4 py-3 text-center">{t('Tier')}</th>
                                                <th className="px-4 py-3 text-center">{t('Status')}</th>
                                                <th className="px-4 py-3 text-center">{t('Digital Seal')}</th>
                                                <th className="px-4 py-3 text-right">{t('Actions')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {filteredRequests.length === 0 ? (
                                                <tr>
                                                    <td colSpan={8} className="p-8 text-center text-muted-foreground">
                                                        {t('No approval requests matching filters.')}
                                                    </td>
                                                </tr>
                                            ) : (
                                                filteredRequests.map((req) => (
                                                    <tr key={req.id} className="hover:bg-muted/30 transition-colors">
                                                        <td className="px-4 py-3 font-mono text-xs">
                                                            <div className="font-bold text-foreground">{req.request_number}</div>
                                                            <div className="text-[11px] text-muted-foreground">{formatDate(req.created_at)}</div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-semibold text-foreground text-xs">{req.document_number}</div>
                                                            <div className="text-[11px] text-muted-foreground">{getDocTypeLabel(req.document_type)}</div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="font-medium text-foreground max-w-xs truncate">{req.title}</div>
                                                            <div className="text-[11px] text-muted-foreground flex items-center gap-1">
                                                                <User className="h-3 w-3" />
                                                                {req.requester?.name || 'User'}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-right font-mono font-bold text-foreground">
                                                            {formatCurrency(req.amount)}
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            <Badge variant="outline" className="text-xs">
                                                                {req.current_tier} / {req.max_tier}
                                                            </Badge>
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            {getStatusBadge(req.status)}
                                                        </td>
                                                        <td className="px-4 py-3 text-center">
                                                            {req.verification_code ? (
                                                                <a
                                                                    href={`/verify/document/${req.verification_code}`}
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    className="inline-flex items-center gap-1 text-xs text-purple-600 hover:text-purple-700 font-mono font-medium"
                                                                    title={req.verification_code}
                                                                >
                                                                    <QrCode className="h-3.5 w-3.5" />
                                                                    {req.verification_code.substring(0, 8)}...
                                                                </a>
                                                            ) : (
                                                                <span className="text-muted-foreground text-xs">—</span>
                                                            )}
                                                        </td>
                                                        <td className="px-4 py-3 text-right">
                                                            <Button
                                                                size="sm"
                                                                variant="ghost"
                                                                onClick={() => {
                                                                    setSelectedRequest(req);
                                                                    setShowDetailsModal(true);
                                                                }}
                                                            >
                                                                <FileText className="h-4 w-4 mr-1" />
                                                                {t('Details')}
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 3: ESCALATION POLICIES */}
                    <TabsContent value="policies" className="space-y-4">
                        <Card className="border-border bg-card">
                            <CardHeader className="p-4 flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle className="text-base font-semibold">{t('Escalation Hierarchy & Approval Matrix')}</CardTitle>
                                    <p className="text-xs text-muted-foreground mt-0.5">
                                        {t('Defines authorization thresholds in TZS and required escalation roles per document domain.')}
                                    </p>
                                </div>
                                <Button
                                    size="sm"
                                    onClick={() => {
                                        policyForm.reset();
                                        setShowPolicyModal(true);
                                    }}
                                    className="flex items-center gap-1.5"
                                >
                                    <Plus className="h-4 w-4" />
                                    {t('Add Tier Rule')}
                                </Button>
                            </CardHeader>
                            <CardContent className="p-0">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm text-left">
                                        <thead className="bg-muted text-muted-foreground uppercase text-[11px] font-semibold border-b border-border">
                                            <tr>
                                                <th className="px-4 py-3">{t('Document Type')}</th>
                                                <th className="px-4 py-3">{t('Tier')}</th>
                                                <th className="px-4 py-3">{t('Designation')}</th>
                                                <th className="px-4 py-3 text-right">{t('Amount Threshold (TZS)')}</th>
                                                <th className="px-4 py-3">{t('Authorized Role')}</th>
                                                <th className="px-4 py-3 text-center">{t('Digital Seal')}</th>
                                                <th className="px-4 py-3 text-center">{t('Segregation')}</th>
                                                <th className="px-4 py-3 text-center">{t('Status')}</th>
                                                <th className="px-4 py-3 text-right">{t('Toggle')}</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-border">
                                            {policies.map((p) => (
                                                <tr key={p.id} className="hover:bg-muted/30 transition-colors">
                                                    <td className="px-4 py-3 font-medium text-foreground">
                                                        {getDocTypeLabel(p.document_type)}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        <Badge variant="outline" className="font-mono text-xs">
                                                            {t('Tier')} {p.tier_level}
                                                        </Badge>
                                                    </td>
                                                    <td className="px-4 py-3 font-semibold text-foreground">
                                                        {p.tier_name}
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-mono text-xs">
                                                        {p.min_amount > 0 ? formatCurrency(p.min_amount) : '0 TZS'}
                                                        {' → '}
                                                        {p.max_amount ? formatCurrency(p.max_amount) : 'Unlimited'}
                                                    </td>
                                                    <td className="px-4 py-3 capitalize text-muted-foreground">
                                                        <span className="font-medium text-foreground">{p.required_role || 'All'}</span>
                                                        {p.approver && (
                                                            <div className="text-[11px] text-muted-foreground">({p.approver.name})</div>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {p.requires_digital_signature ? (
                                                            <Badge className="bg-purple-500/10 text-purple-600 border border-purple-500/20 text-[11px]">
                                                                {t('Required')}
                                                            </Badge>
                                                        ) : (
                                                            <span className="text-muted-foreground text-xs">{t('Optional')}</span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {p.enforce_segregation_of_duties ? (
                                                            <Badge className="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 text-[11px]">
                                                                {t('Enforced')}
                                                            </Badge>
                                                        ) : (
                                                            <span className="text-muted-foreground text-xs">{t('Disabled')}</span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {p.is_active ? (
                                                            <Badge className="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 text-xs">
                                                                {t('Active')}
                                                            </Badge>
                                                        ) : (
                                                            <Badge variant="outline" className="text-xs text-muted-foreground">
                                                                {t('Inactive')}
                                                            </Badge>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-right">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() => handleTogglePolicy(p.id)}
                                                        >
                                                            {p.is_active ? t('Deactivate') : t('Activate')}
                                                        </Button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* TAB 4: AUDIT TRAIL */}
                    <TabsContent value="audit" className="space-y-4">
                        <Card className="border-border bg-card">
                            <CardHeader className="p-4">
                                <CardTitle className="text-base font-semibold">{t('Chronological Audit Trail & Signatures')}</CardTitle>
                                <p className="text-xs text-muted-foreground mt-0.5">
                                    {t('Immutable log of all submission, review, approval, and cryptographic signature actions.')}
                                </p>
                            </CardHeader>
                            <CardContent className="p-0">
                                <div className="divide-y divide-border">
                                    {recent_actions.length === 0 ? (
                                        <div className="p-8 text-center text-muted-foreground">
                                            {t('No audit actions logged yet.')}
                                        </div>
                                    ) : (
                                        recent_actions.map((act) => (
                                            <div key={act.id} className="p-4 hover:bg-muted/20 transition-colors flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                                                <div className="space-y-1">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <span className="font-mono text-xs font-bold text-foreground">
                                                            {act.approval_request?.request_number || 'APR'}
                                                        </span>
                                                        <Badge variant="outline" className="text-[11px] capitalize">
                                                            {act.action}
                                                        </Badge>
                                                        <span className="text-xs text-muted-foreground">
                                                            {act.previous_status} → <strong className="text-foreground">{act.new_status}</strong>
                                                        </span>
                                                        <Badge className="bg-primary/10 text-primary border border-primary/20 text-[10px]">
                                                            {t('Tier')} {act.tier_level}
                                                        </Badge>
                                                    </div>

                                                    {act.comments && (
                                                        <p className="text-xs text-foreground bg-muted/40 p-1.5 rounded max-w-2xl">
                                                            "{act.comments}"
                                                        </p>
                                                    )}

                                                    <div className="text-[11px] text-muted-foreground flex items-center gap-4">
                                                        <span>
                                                            {t('Actor')}: <strong className="text-foreground">{act.actor?.name || 'User'}</strong>
                                                        </span>
                                                        <span>
                                                            {t('IP')}: {act.ip_address || '127.0.0.1'}
                                                        </span>
                                                        <span>
                                                            {formatDate(act.created_at)}
                                                        </span>
                                                    </div>
                                                </div>

                                                {act.digital_signature && (
                                                    <div className="text-right flex flex-col items-end">
                                                        <div className="text-[10px] text-muted-foreground flex items-center gap-1 font-mono">
                                                            <ShieldCheck className="h-3 w-3 text-purple-600" />
                                                            {t('Cryptographic Seal')}:
                                                        </div>
                                                        <a
                                                            href={`/verify/document/${act.digital_signature}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-xs text-purple-600 hover:text-purple-700 font-mono font-medium"
                                                        >
                                                            {act.digital_signature.substring(0, 16)}...
                                                        </a>
                                                    </div>
                                                )}
                                            </div>
                                        ))
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>

                {/* MODAL 1: ACTION MODAL (APPROVE / RETURN / REJECT / SIGN) */}
                <Dialog open={showActionModal} onOpenChange={setShowActionModal}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle className="capitalize flex items-center gap-2">
                                {actionType === 'approve' && <Check className="h-5 w-5 text-emerald-500" />}
                                {actionType === 'sign' && <PenTool className="h-5 w-5 text-purple-500" />}
                                {actionType === 'return' && <RotateCcw className="h-5 w-5 text-amber-500" />}
                                {actionType === 'reject' && <XCircle className="h-5 w-5 text-rose-500" />}
                                {t(`${actionType.toUpperCase()} Approval Request`)}
                            </DialogTitle>
                        </DialogHeader>

                        {selectedRequest && (
                            <form onSubmit={handleSubmitAction} className="space-y-4">
                                <div className="bg-muted/40 p-3 rounded-lg space-y-1 text-xs">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Request Number')}:</span>
                                        <span className="font-mono font-bold text-foreground">{selectedRequest.request_number}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Document Ref')}:</span>
                                        <span className="font-semibold text-foreground">{selectedRequest.document_number}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Amount')}:</span>
                                        <span className="font-bold text-primary">{formatCurrency(selectedRequest.amount)} {selectedRequest.currency}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">{t('Current Tier')}:</span>
                                        <span className="font-medium text-foreground">{selectedRequest.current_tier} / {selectedRequest.max_tier}</span>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs font-semibold text-foreground">
                                        {actionType === 'return' || actionType === 'reject' 
                                            ? t('Reason / Comments (Required)') 
                                            : t('Reviewer Comments (Optional)')}
                                    </label>
                                    <textarea
                                        rows={3}
                                        value={actionForm.data.comments}
                                        onChange={(e) => actionForm.setData('comments', e.target.value)}
                                        placeholder={t('Enter review remarks or justification...')}
                                        className="w-full text-xs p-2.5 rounded-md border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary"
                                        required={actionType === 'return' || actionType === 'reject'}
                                    />
                                </div>

                                {actionType === 'sign' && (
                                    <div className="p-3 bg-purple-500/10 border border-purple-500/20 rounded text-xs text-purple-700 space-y-1">
                                        <div className="font-semibold flex items-center gap-1">
                                            <ShieldCheck className="h-4 w-4" />
                                            {t('Cryptographic SHA-256 Digital Signature')}
                                        </div>
                                        <p className="text-[11px] text-purple-600/90">
                                            {t('This document will be immutably sealed with your credentials, timestamped, and immediately verifiable via the public QR verification engine.')}
                                        </p>
                                    </div>
                                )}

                                <DialogFooter className="flex justify-end gap-2 pt-2">
                                    <Button type="button" variant="outline" onClick={() => setShowActionModal(false)}>
                                        {t('Cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={actionForm.processing}
                                        className={
                                            actionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' :
                                            actionType === 'sign' ? 'bg-purple-600 hover:bg-purple-700 text-white' :
                                            actionType === 'reject' ? 'bg-rose-600 hover:bg-rose-700 text-white' :
                                            'bg-amber-600 hover:bg-amber-700 text-white'
                                        }
                                    >
                                        {actionForm.processing ? t('Processing...') : t(`Confirm ${actionType.toUpperCase()}`)}
                                    </Button>
                                </DialogFooter>
                            </form>
                        )}
                    </DialogContent>
                </Dialog>

                {/* MODAL 2: POLICY RULE MODAL */}
                <Dialog open={showPolicyModal} onOpenChange={setShowPolicyModal}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle>{t('Configure Escalation Policy Tier')}</DialogTitle>
                        </DialogHeader>

                        <form onSubmit={handleSavePolicy} className="space-y-4 text-xs">
                            <div className="space-y-1">
                                <label className="font-semibold">{t('Policy Rule Name')}</label>
                                <Input
                                    placeholder={t('e.g. Supplier Invoices Tier 2')}
                                    value={policyForm.data.name}
                                    onChange={(e) => policyForm.setData('name', e.target.value)}
                                    required
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Document Type')}</label>
                                    <Select 
                                        value={policyForm.data.document_type} 
                                        onValueChange={(val) => policyForm.setData('document_type', val)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="purchase_invoice">{t('Supplier Invoices')}</SelectItem>
                                            <SelectItem value="expense">{t('Operational Expenses')}</SelectItem>
                                            <SelectItem value="sales_proposal">{t('Sales Proposals')}</SelectItem>
                                            <SelectItem value="asset_disposal">{t('Asset Disposals')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Tier Level (1-5)')}</label>
                                    <Input
                                        type="number"
                                        min={1}
                                        max={5}
                                        value={policyForm.data.tier_level}
                                        onChange={(e) => policyForm.setData('tier_level', e.target.value)}
                                        required
                                    />
                                </div>
                            </div>

                            <div className="space-y-1">
                                <label className="font-semibold">{t('Tier Designation Title')}</label>
                                <Input
                                    placeholder={t('e.g. Finance Manager, Managing Director')}
                                    value={policyForm.data.tier_name}
                                    onChange={(e) => policyForm.setData('tier_name', e.target.value)}
                                    required
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Min Amount (TZS)')}</label>
                                    <Input
                                        type="number"
                                        min={0}
                                        value={policyForm.data.min_amount}
                                        onChange={(e) => policyForm.setData('min_amount', e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Max Amount (TZS, blank=unlimited)')}</label>
                                    <Input
                                        type="number"
                                        value={policyForm.data.max_amount}
                                        onChange={(e) => policyForm.setData('max_amount', e.target.value)}
                                        placeholder={t('Unlimited')}
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Required Role')}</label>
                                    <Select 
                                        value={policyForm.data.required_role} 
                                        onValueChange={(val) => policyForm.setData('required_role', val)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="officer">{t('Officer / Supervisor')}</SelectItem>
                                            <SelectItem value="manager">{t('Department Manager')}</SelectItem>
                                            <SelectItem value="company">{t('Managing Director / Company')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-1">
                                    <label className="font-semibold">{t('Designated User (Optional)')}</label>
                                    <Select 
                                        value={policyForm.data.approver_id || 'none'} 
                                        onValueChange={(val) => policyForm.setData('approver_id', val === 'none' ? '' : val)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Any matching role')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">{t('Any matching role')}</SelectItem>
                                            {users.map((u) => (
                                                <SelectItem key={u.id} value={String(u.id)}>
                                                    {u.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div className="space-y-2 pt-2 border-t border-border">
                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={policyForm.data.requires_digital_signature}
                                        onChange={(e) => policyForm.setData('requires_digital_signature', e.target.checked)}
                                        className="rounded border-border"
                                    />
                                    <span>{t('Requires Cryptographic SHA-256 Digital Signature')}</span>
                                </label>

                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={policyForm.data.enforce_segregation_of_duties}
                                        onChange={(e) => policyForm.setData('enforce_segregation_of_duties', e.target.checked)}
                                        className="rounded border-border"
                                    />
                                    <span>{t('Enforce Segregation of Duties (Initiator cannot approve)')}</span>
                                </label>
                            </div>

                            <DialogFooter className="pt-2">
                                <Button type="button" variant="outline" onClick={() => setShowPolicyModal(false)}>
                                    {t('Cancel')}
                                </Button>
                                <Button type="submit" disabled={policyForm.processing}>
                                    {policyForm.processing ? t('Saving...') : t('Save Policy Rule')}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 3: SUBMIT DOCUMENT MODAL */}
                <Dialog open={showSubmitModal} onOpenChange={setShowSubmitModal}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <DialogTitle>{t('Submit Document to Approval Pipeline')}</DialogTitle>
                        </DialogHeader>

                        <form onSubmit={handleSubmitDocument} className="space-y-4 text-xs">
                            <div className="space-y-1">
                                <label className="font-semibold">{t('Document Type')}</label>
                                <Select 
                                    value={submitForm.data.document_type} 
                                    onValueChange={(val) => submitForm.setData('document_type', val)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="purchase_invoice">{t('Supplier Invoices / Bills')}</SelectItem>
                                        <SelectItem value="expense">{t('Operational Expenses')}</SelectItem>
                                        <SelectItem value="sales_proposal">{t('Sales Proposals / Quotes')}</SelectItem>
                                        <SelectItem value="asset_disposal">{t('Asset Disposals / Write-offs')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-1">
                                <label className="font-semibold">{t('Document Record ID')}</label>
                                <Input
                                    type="number"
                                    placeholder={t('Enter Database ID of document (e.g. 1, 2, 3...)')}
                                    value={submitForm.data.document_id}
                                    onChange={(e) => submitForm.setData('document_id', e.target.value)}
                                    required
                                />
                                <p className="text-[11px] text-muted-foreground">
                                    {t('The engine will automatically fetch the amount, verify thresholds, and route to Tier 1.')}
                                </p>
                            </div>

                            <div className="space-y-1">
                                <label className="font-semibold">{t('Submission Justification / Notes')}</label>
                                <textarea
                                    rows={3}
                                    value={submitForm.data.notes}
                                    onChange={(e) => submitForm.setData('notes', e.target.value)}
                                    placeholder={t('Brief notes regarding this submission...')}
                                    className="w-full text-xs p-2.5 rounded-md border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary"
                                />
                            </div>

                            <DialogFooter className="pt-2">
                                <Button type="button" variant="outline" onClick={() => setShowSubmitModal(false)}>
                                    {t('Cancel')}
                                </Button>
                                <Button type="submit" disabled={submitForm.processing}>
                                    {submitForm.processing ? t('Submitting...') : t('Submit for Approval')}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 4: DETAILS & AUDIT HISTORY MODAL */}
                <Dialog open={showDetailsModal} onOpenChange={setShowDetailsModal}>
                    <DialogContent className="max-w-xl">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <FileText className="h-5 w-5 text-primary" />
                                {t('Approval Request Details & Audit Trail')}
                            </DialogTitle>
                        </DialogHeader>

                        {selectedRequest && (
                            <div className="space-y-4 text-xs">
                                <div className="grid grid-cols-2 gap-3 bg-muted/40 p-3 rounded-lg">
                                    <div>
                                        <span className="text-muted-foreground">{t('Request Number')}:</span>
                                        <div className="font-mono font-bold text-foreground">{selectedRequest.request_number}</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('Status')}:</span>
                                        <div>{getStatusBadge(selectedRequest.status)}</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('Document')}:</span>
                                        <div className="font-semibold text-foreground">{selectedRequest.document_number} ({getDocTypeLabel(selectedRequest.document_type)})</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('Amount')}:</span>
                                        <div className="font-mono font-bold text-primary">{formatCurrency(selectedRequest.amount)} {selectedRequest.currency}</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('Initiator')}:</span>
                                        <div className="font-medium text-foreground">{selectedRequest.requester?.name || 'User'}</div>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">{t('Tier Progress')}:</span>
                                        <div className="font-semibold text-foreground">{selectedRequest.current_tier} / {selectedRequest.max_tier}</div>
                                    </div>
                                </div>

                                {selectedRequest.verification_code && (
                                    <div className="p-3 bg-purple-500/10 border border-purple-500/20 rounded space-y-1">
                                        <div className="font-semibold text-purple-700 flex items-center justify-between">
                                            <span className="flex items-center gap-1">
                                                <QrCode className="h-4 w-4" />
                                                {t('Cryptographic Verification Seal')}
                                            </span>
                                            <a
                                                href={`/verify/document/${selectedRequest.verification_code}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-[11px] underline flex items-center gap-0.5"
                                            >
                                                {t('Verify Publicly')}
                                                <ExternalLink className="h-3 w-3" />
                                            </a>
                                        </div>
                                        <div className="font-mono text-[11px] text-purple-800 break-all">
                                            {selectedRequest.verification_code}
                                        </div>
                                    </div>
                                )}

                                <div className="space-y-2">
                                    <h4 className="font-semibold text-foreground flex items-center gap-1.5">
                                        <Clock className="h-4 w-4 text-muted-foreground" />
                                        {t('Action History & Comments')}
                                    </h4>
                                    <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                                        {(selectedRequest.actions || []).length === 0 ? (
                                            <p className="text-muted-foreground italic">{t('No actions recorded.')}</p>
                                        ) : (
                                            selectedRequest.actions.map((a: any) => (
                                                <div key={a.id} className="p-2.5 rounded border border-border bg-card space-y-1">
                                                    <div className="flex justify-between items-center">
                                                        <span className="font-semibold capitalize text-foreground flex items-center gap-1">
                                                            <Check className="h-3.5 w-3.5 text-primary" />
                                                            {a.action} ({t('Tier')} {a.tier_level})
                                                        </span>
                                                        <span className="text-[11px] text-muted-foreground">{formatDate(a.created_at)}</span>
                                                    </div>
                                                    {a.comments && (
                                                        <p className="text-muted-foreground text-xs italic bg-muted/40 p-1.5 rounded">
                                                            "{a.comments}"
                                                        </p>
                                                    )}
                                                    <div className="text-[10px] text-muted-foreground">
                                                        {t('By')}: <strong className="text-foreground">{a.actor?.name || 'User'}</strong> | IP: {a.ip_address || '127.0.0.1'}
                                                    </div>
                                                </div>
                                            ))
                                        )}
                                    </div>
                                </div>

                                <DialogFooter className="pt-2">
                                    <Button variant="outline" onClick={() => setShowDetailsModal(false)}>
                                        {t('Close')}
                                    </Button>
                                </DialogFooter>
                            </div>
                        )}
                    </DialogContent>
                </Dialog>
            </div>
        </AuthenticatedLayout>
    );
}
