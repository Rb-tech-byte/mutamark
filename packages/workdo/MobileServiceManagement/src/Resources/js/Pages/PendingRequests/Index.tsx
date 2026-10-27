import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Clock, Check, X, Trash2 , Eye} from "lucide-react";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FilterButton } from '@/components/ui/filter-button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate } from '@/utils/helpers';

interface RequestData {
    id: number;
    service_id: string;
    customer_name: string;
    email: string;
    mobile_no: string;
    mobile_model: string;
    priority: string;
    tracking_status: string;
    is_approve: number | null;
    mobile_service?: {
        id: number;
        name: string;
    };
    created_at: string;
}

interface PageProps {
    mobilerequests: {
        data: RequestData[];
        links: any[];
        meta: any;
    };
    mobileServices?: Array<{id: number, name: string}>;
    auth: any;
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { mobilerequests, mobileServices = [], auth } = usePage<PageProps>().props;

    const urlParams = new URLSearchParams(window.location.search);

    const [searchTerm, setSearchTerm] = useState(urlParams.get('customer_name') || '');
    const [showFilters, setShowFilters] = useState(false);
    const [filters, setFilters] = useState({
        mobile_service_id: urlParams.get('mobile_service_id') || '',
        priority: urlParams.get('priority') || '',
        request_status: urlParams.get('request_status') || ''
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [approveDialog, setApproveDialog] = useState<{isOpen: boolean, id: number | null}>({isOpen: false, id: null});
    const [rejectDialog, setRejectDialog] = useState<{isOpen: boolean, id: number | null}>({isOpen: false, id: null});


    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'mobile-service-management.pending-service-requests.destroy',
        defaultMessage: t('Are you sure you want to delete this pending request?')
    });

    const handleFilter = () => {
        router.get(route('mobile-service-management.pending-service-requests.index'), { customer_name: searchTerm, ...filters, per_page: perPage, sort: sortField, direction: sortDirection }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('mobile-service-management.pending-service-requests.index'), { customer_name: searchTerm, ...filters, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setFilters({ mobile_service_id: '', priority: '', request_status: '' });
        router.get(route('mobile-service-management.pending-service-requests.index'), { per_page: perPage });
    };

    const openApproveDialog = (id: number) => {
        setApproveDialog({isOpen: true, id});
    };

    const closeApproveDialog = () => {
        setApproveDialog({isOpen: false, id: null});
    };

    const confirmApprove = () => {
        if (approveDialog.id) {
            router.post(route('mobile-service-management.pending-service-requests.approve', approveDialog.id), {}, {
                preserveState: true,
                onSuccess: () => {
                    router.reload({ only: ['mobilerequests'] });
                    closeApproveDialog();
                }
            });
        }
    };

    const openRejectDialog = (id: number) => {
        setRejectDialog({isOpen: true, id});
    };

    const closeRejectDialog = () => {
        setRejectDialog({isOpen: false, id: null});
    };

    const confirmReject = () => {
        if (rejectDialog.id) {
            router.post(route('mobile-service-management.pending-service-requests.reject', rejectDialog.id), {}, {
                preserveState: true,
                onSuccess: () => {
                    router.reload({ only: ['mobilerequests'] });
                    closeRejectDialog();
                }
            });
        }
    };

    const getPriorityBadge = (priority: string) => {
        const priorityColors = {
            'emergency': 'bg-red-100 text-red-800',
            'priority': 'bg-purple-100 text-purple-800',
            'standard': 'bg-gray-100 text-gray-800',
        };
        return (
            <span className={`px-2 py-1 rounded-full text-xs font-medium ${priorityColors[priority as keyof typeof priorityColors] || 'bg-gray-100 text-gray-800'}`}>
                {priority && priority.charAt(0).toUpperCase() + priority.slice(1)}
            </span>
        );
    };

    const tableColumns = [
        {
            key: 'service_id',
            header: t('Service ID'),
            sortable: false,
            render: (value: string, request: RequestData) =>
                auth.user?.permissions?.includes('view-mobile-pending-requests') ? (
                    <span className="text-blue-600 hover:text-blue-700 cursor-pointer" onClick={() => router.get(route('mobile-service-management.pending-service-requests.show', request.id))}>{value}</span>
                ) : (
                    value
                )
        },
        {
            key: 'customer_name',
            header: t('Customer'),
            sortable: true
        },
        {
            key: 'email',
            header: t('E-mail'),
            sortable: false
        },
        {
            key: 'mobile_service',
            header: t('Service'),
            render: (value: any) => value?.name || '-'
        },
        {
            key: 'priority',
            header: t('Priority'),
            sortable: true,
            render: (value: string) => getPriorityBadge(value)
        },
        {
            key: 'is_approve',
            header: t('Request Status'),
            sortable: false,
            render: (value: any, request: RequestData) => {
                const status = request.is_approve === 0 ? 'Pending' : (request.is_approve === 1 ? 'Approved' : 'Rejected');
                return (
                    <span className={`px-2 py-1 rounded-full text-sm ${
                        request.is_approve === 1 ? 'bg-green-100 text-green-800' :
                        request.is_approve === 2 ? 'bg-red-100 text-red-800' :
                        'bg-yellow-100 text-yellow-800'
                    }`}>
                        {status}
                    </span>
                );
            }
        },
        {
            key: 'created_at',
            header: t('Date'),
            sortable: true,
            render: (value: string) => formatDate(value)
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, request: RequestData) => (
                <div className="flex gap-1">
                    {request.is_approve === 0 && auth.user?.permissions?.includes('approve-mobile-pending-requests') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openApproveDialog(request.id)}
                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                        disabled={request.tracking_status === 'approved'}
                                    >
                                        <Check className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Approve')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                    {request.is_approve === 0 && auth.user?.permissions?.includes('reject-mobile-pending-requests') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openRejectDialog(request.id)}
                                        className="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                                        disabled={request.tracking_status === 'rejected'}
                                    >
                                        <X className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Reject')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                    {auth.user?.permissions?.includes('view-mobile-pending-requests') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => router.get(route('mobile-service-management.pending-service-requests.show', request.id))}
                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                        disabled={request.tracking_status === 'approved'}
                                    >
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                    {auth.user?.permissions?.includes('delete-mobile-pending-requests') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(request.id)}
                                        className="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Delete')}</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                </div>
            )
        }
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service Management'), url: route('mobile-service.index') },
                { label: t('Pending Requests') }
            ]}
            pageTitle={t('Manage Pending Requests')}
        >
            <Head title={t('Pending Requests')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={searchTerm}
                                onChange={(value) => setSearchTerm(value)}
                                onSearch={handleFilter}
                                placeholder={t('Search requests...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="mobile-service-management.pending-service-requests.index"
                                filters={{ customer_name: searchTerm, ...filters }}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [filters.mobile_service_id, filters.priority, filters.request_status].filter(Boolean).length;
                                    return activeFilters > 0 && (
                                        <span className="absolute -top-2 -right-2 bg-primary text-primary-foreground text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                            {activeFilters}
                                        </span>
                                    );
                                })()}
                            </div>
                        </div>
                    </div>
                </CardContent>

                {showFilters && (
                    <CardContent className="p-6 bg-blue-50/30 border-b">
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Service')}</label>
                                <Select value={filters.mobile_service_id} onValueChange={(value) => setFilters({...filters, mobile_service_id: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by service')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {mobileServices.map((service) => (
                                            <SelectItem key={service.id} value={service.id.toString()}>{service.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Priority')}</label>
                                <Select value={filters.priority} onValueChange={(value) => setFilters({...filters, priority: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by priority')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="emergency">{t('Emergency')}</SelectItem>
                                        <SelectItem value="priority">{t('Priority')}</SelectItem>
                                        <SelectItem value="standard">{t('Standard')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Request Status')}</label>
                                <Select value={filters.request_status} onValueChange={(value) => setFilters({...filters, request_status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">{t('Pending')}</SelectItem>
                                        <SelectItem value="rejected">{t('Rejected')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-end gap-2">
                                <Button onClick={handleFilter} size="sm">{t('Apply')}</Button>
                                <Button variant="outline" onClick={clearFilters} size="sm">{t('Clear')}</Button>
                            </div>
                        </div>
                    </CardContent>
                )}

                <CardContent className="p-0">
                    <div className="overflow-y-auto max-h-[70vh] w-full">
                        <DataTable
                            data={mobilerequests?.data || []}
                            columns={tableColumns}
                            onSort={handleSort}
                            sortKey={sortField}
                            sortDirection={sortDirection as 'asc' | 'desc'}
                            emptyState={
                                <NoRecordsFound
                                    icon={Clock}
                                    title={t('No Requests found')}
                                    description={t('No service requests available.')}
                                    hasFilters={!!searchTerm || Object.values(filters).some(Boolean)}
                                    onClearFilters={clearFilters}
                                />
                            }
                        />
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={mobilerequests || { data: [], links: [], meta: {} }}
                        routeName="mobile-service-management.pending-service-requests.index"
                        filters={{ customer_name: searchTerm, ...filters, per_page: perPage }}
                    />
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Pending Request')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />

            <ConfirmationDialog
                open={approveDialog.isOpen}
                onOpenChange={closeApproveDialog}
                title={t('Approve Request')}
                message={t('Are you sure you want to approve this request?')}
                confirmText={t('Approve')}
                onConfirm={confirmApprove}
            />

            <ConfirmationDialog
                open={rejectDialog.isOpen}
                onOpenChange={closeRejectDialog}
                title={t('Reject Request')}
                message={t('Are you sure you want to reject this request?')}
                confirmText={t('Reject')}
                onConfirm={confirmReject}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}