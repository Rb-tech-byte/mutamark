import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog } from "@/components/ui/dialog";
import { FileText, Trash2, Plus, Edit as EditIcon, UserPlus, CheckCircle, Eye } from "lucide-react";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate } from '@/utils/helpers';


import AssignTechnician from './AssignTechnician';

interface RequestData {
    id: number;
    service_id: string;
    customer_name: string;
    email: string;
    mobile_no: string;
    mobile_model: string;
    priority: string;
    tracking_status: {
        id: number;
        status_name: string;
        status_color: string;
    } | string;
    mobile_service?: {
        id: number;
        name: string;
    };
    assigned_technicians?: Array<{
        technician?: {
            id: number;
            name: string;
        };
    }>;
    created_at: string;
}

interface PageProps {
    mobilerequests: {
        data: RequestData[];
        links: any[];
        meta: any;
    };
    auth: any;
    mobileservices?: Array<{id: number, name: string}>;
    devicebrands?: Array<{id: number, name: string}>;
    [key: string]: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { mobilerequests, auth, mobileservices = [], devicebrands = [], trackingstatuses = [] } = usePage<PageProps>().props;

    const urlParams = new URLSearchParams(window.location.search);
    const [searchTerm, setSearchTerm] = useState(urlParams.get('customer_name') || '');
    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');


    const [assignTechnicianModal, setAssignTechnicianModal] = useState<{isOpen: boolean, data: RequestData | null}>({isOpen: false, data: null});
    const [endRepairDialog, setEndRepairDialog] = useState<{isOpen: boolean, id: number | null}>({isOpen: false, id: null});

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'mobile-service-management.service-requests.destroy',
        defaultMessage: t('Are you sure you want to delete this service request?')
    });

    const handleFilter = () => {
        router.get(route('mobile-service-management.service-requests.index'), { customer_name: searchTerm, per_page: perPage, sort: sortField, direction: sortDirection }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('mobile-service-management.service-requests.index'), { customer_name: searchTerm, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        router.get(route('mobile-service-management.service-requests.index'), { per_page: perPage });
    };

    const openEndRepairDialog = (id: number) => {
        setEndRepairDialog({isOpen: true, id});
    };

    const closeEndRepairDialog = () => {
        setEndRepairDialog({isOpen: false, id: null});
    };

    const confirmEndRepair = () => {
        if (endRepairDialog.id) {
            router.post(route('mobile-service-management.service-requests.end-repair', endRepairDialog.id), {}, {
                preserveState: true,
                onSuccess: () => {
                    router.reload({ only: ['mobilerequests'] });
                    closeEndRepairDialog();
                }
            });
        }
    };

    const getStatusBadge = (status: string) => {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'approved': 'bg-green-100 text-green-800',
            'in_progress': 'bg-blue-100 text-blue-800',
            'completed': 'bg-gray-100 text-gray-800',
            'reject': 'bg-red-100 text-red-800',
        };
        return (
            <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusColors[status as keyof typeof statusColors] || 'bg-gray-100 text-gray-800'}`}>
                {status && (status.replace('_', ' ').charAt(0).toUpperCase() + status.replace('_', ' ').slice(1).toLowerCase())}
            </span>
        );
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
                auth.user?.permissions?.includes('view-mobile-service-requests') ? (
                    <span className="text-blue-600 hover:text-blue-700 cursor-pointer" onClick={() => router.get(route('mobile-service-management.service-requests.show', request.id))}>{value}</span>
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
            key: 'mobile_model',
            header: t('Device Model'),
            render: (value: string) => value || '-'
        },
        {
            key: 'assigned_technicians',
            header: t('Assigned Technician'),
            render: (value: any, request: any) => {
                if (request.assigned_technicians && request.assigned_technicians.length > 0) {
                    return request.assigned_technicians.map((assignment: any) => assignment.technician?.name).join(', ');
                }
                return 'Not Assigned';
            }
        },
        {
            key: 'priority',
            header: t('Priority'),
            sortable: true,
            render: (value: string) => getPriorityBadge(value)
        },
        {
            key: 'tracking_status',
            header: t('Status'),
            sortable: true,
            render: (value: string, request: RequestData) => {
                if (request.tracking_status && typeof request.tracking_status === 'object' && request.tracking_status.status_name) {
                    const statusColor = request.tracking_status.status_color || '#6b7280';
                    return (
                        <span
                            className="px-2 py-1 rounded-full text-xs font-medium text-white"
                            style={{ backgroundColor: statusColor }}
                        >
                            {request.tracking_status.status_name}
                        </span>
                    );
                }
                return getStatusBadge(value);
            }
        },
       
        {
            key: 'created_at',
            header: t('Created At'),
            sortable: true,
            render: (value: string) => formatDate(value)
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, request: RequestData) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                       
                        {auth.user?.permissions?.includes('assign-mobile-service-technician') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setAssignTechnicianModal({isOpen: true, data: request})}
                                        className="h-8 w-8 p-0 text-purple-600 hover:text-purple-700"
                                    >
                                        <UserPlus className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Assign Technician')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('end-mobile-service-repair') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openEndRepairDialog(request.id)}
                                        className="h-8 w-8 p-0 text-orange-600 hover:text-orange-700"
                                    >
                                        <CheckCircle className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('End Repair')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                         {auth.user?.permissions?.includes('view-mobile-service-requests') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => router.get(route('mobile-service-management.service-requests.show', request.id))}
                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
                                    >
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('edit-mobile-service-requests') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => router.get(route('mobile-service-management.service-requests.edit', request.id))}
                                        className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700"
                                    >
                                        <EditIcon className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Edit')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('delete-mobile-service-requests') && (
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
                        )}
                    </TooltipProvider>
                </div>
            )
        }
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service Management'), url: route('mobile-service.index') },
                { label: t('Service Requests') }
            ]}
            pageTitle={t('Manage Service Requests')}
            pageActions={
                <TooltipProvider>
                    {auth.user?.permissions?.includes('create-mobile-service-requests') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => router.get(route('mobile-service-management.service-requests.create'))}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Create')}</p>
                            </TooltipContent>
                        </Tooltip>
                    )}
                </TooltipProvider>
            }
        >
            <Head title={t('Service Requests')} />

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
                                routeName="mobile-service-management.service-requests.index"
                                filters={{ customer_name: searchTerm }}
                            />
                        </div>
                    </div>
                </CardContent>

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
                                    icon={FileText}
                                    title={t('No Service Requests found')}
                                    description={t('No approved service requests available.')}
                                    hasFilters={!!searchTerm}
                                    onClearFilters={clearFilters}
                                />
                            }
                        />
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={mobilerequests || { data: [], links: [], meta: {} }}
                        routeName="mobile-service-management.service-requests.index"
                        filters={{ customer_name: searchTerm, per_page: perPage }}
                    />
                </CardContent>
            </Card>





            <Dialog open={assignTechnicianModal.isOpen} onOpenChange={(open) => setAssignTechnicianModal({isOpen: open, data: null})}>
                {assignTechnicianModal.data && (
                    <AssignTechnician
                        mobileservicerequest={assignTechnicianModal.data}
                        onSuccess={() => setAssignTechnicianModal({isOpen: false, data: null})}
                    />
                )}
            </Dialog>

            <ConfirmationDialog
                open={endRepairDialog.isOpen}
                onOpenChange={closeEndRepairDialog}
                title={t('End Repair Request')}
                message={t('Are you sure you want to end this repair request?')}
                confirmText={t('End Repair')}
                onConfirm={confirmEndRepair}
                variant="default"
            />

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Service Request')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}