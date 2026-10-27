import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Plus, Edit as EditIcon, Trash2, Eye, FileText as FileTextIcon, Download, FileImage } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { ListGridToggle } from '@/components/ui/list-grid-toggle';
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import Create from './Create';
import EditMobileServicePolicy from './Edit';
import View from './View';
import NoRecordsFound from '@/components/no-records-found';
import { MobileServicePolicy, MobileServicePoliciesIndexProps, MobileServicePolicyFilters, MobileServicePolicyModalState } from './types';
import { formatDate, formatTime, formatDateTime, formatCurrency, getImagePath } from '@/utils/helpers';

export default function Index() {
    const { t } = useTranslation();
    const { mobileservicepolicies, auth } = usePage<MobileServicePoliciesIndexProps>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState<MobileServicePolicyFilters>({
        policy_name: urlParams.get('policy_name') || '',
        description: urlParams.get('description') || '',
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [viewMode, setViewMode] = useState<'list' | 'grid'>(urlParams.get('view') as 'list' | 'grid' || 'list');
    const [modalState, setModalState] = useState<MobileServicePolicyModalState>({
        isOpen: false,
        mode: '',
        data: null
    });
    const [viewingItem, setViewingItem] = useState<MobileServicePolicy | null>(null);

    const [showFilters, setShowFilters] = useState(false);



    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'mobile-service-management.mobile-service-policies.destroy',
        defaultMessage: t('Are you sure you want to delete this service policy?')
    });

    const handleFilter = () => {
        router.get(route('mobile-service-management.mobile-service-policies.index'), { ...filters, per_page: perPage, sort: sortField, direction: sortDirection, view: viewMode }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('mobile-service-management.mobile-service-policies.index'), { ...filters, per_page: perPage, sort: field, direction, view: viewMode }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({
            policy_name: '',
            description: '',
        });
        router.get(route('mobile-service-management.mobile-service-policies.index'), { per_page: perPage, view: viewMode });
    };

    const openModal = (mode: 'add' | 'edit', data: MobileServicePolicy | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const tableColumns = [
        {
            key: 'policy_name',
            header: t('Policy Name'),
            sortable: true
        },

        {
            key: 'response_time',
            header: t('Response Time'),
            sortable: false,
            render: (value: string) => value ? formatTime(value) : '-'
        },
        {
            key: 'resolution_time',
            header: t('Resolution Time'),
            sortable: false,
            render: (value: string) => value ? formatTime(value) : '-'
        },
        ...(auth.user?.permissions?.some((p: string) => ['view-mobile-service-policies', 'edit-mobile-service-policies', 'delete-mobile-service-policies'].includes(p)) ? [{
            key: 'actions',
            header: t('Actions'),
            render: (_: any, mobileservicepolicy: MobileServicePolicy) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth.user?.permissions?.includes('view-mobile-service-policies') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => setViewingItem(mobileservicepolicy)} className="h-8 w-8 p-0 text-green-600 hover:text-green-700">
                                        <Eye className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('View')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('edit-mobile-service-policies') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="sm" onClick={() => openModal('edit', mobileservicepolicy)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                        <EditIcon className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Edit')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('delete-mobile-service-policies') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(mobileservicepolicy.id)}
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
        }] : [])
    ];

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('Services Policies') }
            ]}
            pageTitle={t('Manage Service Policies')}
            pageActions={
                <TooltipProvider>
                    {auth.user?.permissions?.includes('create-mobile-service-policies') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => openModal('add')}>
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
            <Head title={t('Services Policies')} />

            {/* Main Content Card */}
            <Card className="shadow-sm">
                {/* Search & Controls Header */}
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.policy_name}
                                onChange={(value) => setFilters({ ...filters, policy_name: value })}
                                onSearch={handleFilter}
                                placeholder={t('Search Service Policies...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="mobile-service-management.mobile-service-policies.index"
                                filters={{ ...filters }}
                            />
                        </div>
                    </div>
                </CardContent>



                {/* Table Content */}
                <CardContent className="p-0">
                    <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[70vh] rounded-none w-full">
                        <div className="min-w-[800px]">
                            <DataTable
                                data={mobileservicepolicies?.data || []}
                                columns={tableColumns}
                                onSort={handleSort}
                                sortKey={sortField}
                                sortDirection={sortDirection as 'asc' | 'desc'}
                                className="rounded-none"
                                emptyState={
                                    <NoRecordsFound
                                        icon={FileTextIcon}
                                        title={t('No Service Policies found')}
                                        description={t('Get started by creating your first Service Policy.')}
                                        hasFilters={!!(filters.policy_name || filters.description)}
                                        onClearFilters={clearFilters}
                                        createPermission="create-mobile-service-policies"
                                        onCreateClick={() => openModal('add')}
                                        createButtonText={t('Create Service Policy')}
                                        className="h-auto"
                                    />
                                }
                            />
                        </div>
                    </div>
                </CardContent>

                {/* Pagination Footer */}
                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={mobileservicepolicies || { data: [], links: [], meta: {} }}
                        routeName="mobile-service-management.mobile-service-policies.index"
                        filters={{ ...filters, per_page: perPage, view: viewMode }}
                    />
                </CardContent>
            </Card>

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'add' && (
                    <Create onSuccess={closeModal} />
                )}
                {modalState.mode === 'edit' && modalState.data && (
                    <EditMobileServicePolicy
                        mobileservicepolicy={modalState.data}
                        onSuccess={closeModal}
                    />
                )}
            </Dialog>

            <Dialog open={!!viewingItem} onOpenChange={() => setViewingItem(null)}>
                {viewingItem && <View mobileservicepolicy={viewingItem} />}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Service Policy')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}