import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Dialog } from "@/components/ui/dialog";
import { CreditCard, Trash2, Plus, Edit as EditIcon, Eye, CheckCircle, X, Download } from "lucide-react";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FilterButton } from '@/components/ui/filter-button';
import { DateRangePicker } from '@/components/ui/date-range-picker';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate,formatCurrency } from '@/utils/helpers';
import Create from './Create';
import Edit from './Edit';

interface Payment {
    id: number;
    payment_date: string;
    price: string;
    payment_type: string;
    payment_status: string;
    description?: string;
    mobile_service_request: {
        id: number;
        service_id: string;
        customer_name: string;
    };
    created_at: string;
}

interface ServiceRequest {
    id: number;
    service_id: string;
    customer_name: string;
}

interface PageProps {
    payments: {
        data: Payment[];
        links: any[];
        meta: any;
    };
    serviceRequests?: ServiceRequest[];
    auth: any;
    [key: string]: any;
}

interface ModalState {
    isOpen: boolean;
    mode: 'create' | 'edit' | '';
    data: Payment | null;
}

export default function Index() {
    const { t } = useTranslation();
    const { payments, serviceRequests = [], auth } = usePage<PageProps>().props;

    const urlParams = new URLSearchParams(window.location.search);
    const [searchTerm, setSearchTerm] = useState(urlParams.get('search') || '');
    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [showFilters, setShowFilters] = useState(false);
    const [filters, setFilters] = useState({
        status: urlParams.get('status') || '',
        payment_date: urlParams.get('payment_date') || ''
    });
    const [modalState, setModalState] = useState<ModalState>({
        isOpen: false,
        mode: '',
        data: null
    });

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'mobile-service-management.payments.destroy',
        defaultMessage: t('Are you sure you want to delete this payment?')
    });

    const handleFilter = () => {
        router.get(route('mobile-service-management.payments.index'), { search: searchTerm, ...filters, per_page: perPage, sort: sortField, direction: sortDirection }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('mobile-service-management.payments.index'), { search: searchTerm, ...filters, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setFilters({ status: '', payment_date: '' });
        router.get(route('mobile-service-management.payments.index'), { per_page: perPage });
    };

    const openModal = (mode: 'create' | 'edit', data: Payment | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const handleStatusUpdate = (paymentId: number, status: string) => {
        router.patch(route('mobile-service-management.payments.update-status', paymentId), { status });
    };

    const getStatusBadge = (status: string) => {
        const statusColors = {
            'cleared': 'bg-green-100 text-green-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'cancelled': 'bg-red-100 text-red-800',
        };
        return (
            <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusColors[status as keyof typeof statusColors] || 'bg-gray-100 text-gray-800'}`}>
                {status && status.charAt(0).toUpperCase() + status.slice(1)}
            </span>
        );
    };

    const tableColumns = [
        {
            key: 'payment_number',
            header: t('Payment Number'),
            sortable: false,
            render: (value: string, payment: Payment) => (
                auth.user?.permissions?.includes('view-mobile-service-payments') ? (
                    <span className="text-blue-600 hover:text-blue-700 cursor-pointer" onClick={() => router.get(route('mobile-service-management.payments.show', payment.id))}>{value || '-'}</span>
                ) : (
                    <span>{value || '-'}</span>
                )
            )
        },
        {
            key: 'mobile_service_request.customer_name',
            header: t('Customer'),
            sortable: true,
            render: (value: any, payment: Payment) => payment.mobile_service_request?.customer_name || '-'
        },
        {
            key: 'payment_amount',
            header: t('Total Amount'),
            sortable: true,
            render: (value: string) => formatCurrency(value)
        },
        {
            key: 'payment_status',
            header: t('Status'),
            sortable: true,
            render: (value: string) => getStatusBadge(value)
        },
        {
            key: 'payment_date',
            header: t('Payment Date'),
            sortable: true,
            render: (value: string) => formatDate(value)
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
            render: (_: any, payment: Payment) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {payment.payment_status === 'pending' && auth.user?.permissions?.includes('cleared-mobile-service-payments') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleStatusUpdate(payment.id, 'cleared')}
                                        className="h-8 w-8 p-0 text-purple-600 hover:text-purple-700"
                                    >
                                        <CheckCircle className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Mark as Cleared')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {payment.payment_status === 'pending' && auth.user?.permissions?.includes('cancelled-mobile-service-payments') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleStatusUpdate(payment.id, 'cancelled')}
                                        className="h-8 w-8 p-0 text-red-600 hover:text-red-700"
                                    >
                                        <X className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Cancel Payment')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('print-mobile-service-payments') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => {
                                            const printUrl = route('mobile-service-management.payments.print', payment.id) + '?download=pdf';
                                            window.open(printUrl, '_blank');
                                        }}
                                        className="h-8 w-8 p-0 text-orange-600 hover:text-orange-700"
                                    >
                                        <Download className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>{t('Download PDF')}</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        {auth.user?.permissions?.includes('view-mobile-service-payments') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => router.get(route('mobile-service-management.payments.show', payment.id))}
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
                        {auth.user?.permissions?.includes('edit-mobile-service-payments') && payment.payment_status === 'pending' && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openModal('edit', payment)}
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
                        {auth.user?.permissions?.includes('delete-mobile-service-payments') && payment.payment_status === 'pending' && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(payment.id)}
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
                { label: t('Manage Payments') }
            ]}
            pageTitle={t('Manage Payments')}
            pageActions={
                <TooltipProvider>
                    {auth.user?.permissions?.includes('create-mobile-service-payments') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button size="sm" onClick={() => openModal('create')}>
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
            <Head title={t('Payments')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={searchTerm}
                                onChange={(value) => setSearchTerm(value)}
                                onSearch={handleFilter}
                                placeholder={t('Search payments...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="mobile-service-management.payments.index"
                                filters={{ search: searchTerm, ...filters }}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [filters.status, filters.payment_date].filter(Boolean).length;
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
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Status')}</label>
                                <Select value={filters.status} onValueChange={(value) => setFilters({...filters, status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">{t('Pending')}</SelectItem>
                                        <SelectItem value="cleared">{t('Cleared')}</SelectItem>
                                        <SelectItem value="cancelled">{t('Cancelled')}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Payment Date')}</label>
                                <DateRangePicker
                                    value={filters.payment_date}
                                    onChange={(value) => setFilters({...filters, payment_date: value})}
                                    placeholder={t('Select date range')}
                                />
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
                            data={payments?.data || []}
                            columns={tableColumns}
                            onSort={handleSort}
                            sortKey={sortField}
                            sortDirection={sortDirection as 'asc' | 'desc'}
                            emptyState={
                                <NoRecordsFound
                                    icon={CreditCard}
                                    title={t('No Payments found')}
                                    description={t('No payments available.')}
                                    hasFilters={!!searchTerm}
                                    onClearFilters={clearFilters}
                                />
                            }
                        />
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={payments || { data: [], links: [], meta: {} }}
                        routeName="mobile-service-management.payments.index"
                        filters={{ search: searchTerm, ...filters, per_page: perPage }}
                    />
                </CardContent>
            </Card>

            <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                {modalState.mode === 'create' && (
                    <Create
                        serviceRequests={serviceRequests}
                        onSuccess={closeModal}
                    />
                )}
                {modalState.mode === 'edit' && modalState.data && (
                    <Edit
                        payment={modalState.data}
                        serviceRequests={serviceRequests}
                        onSuccess={closeModal}
                    />
                )}
            </Dialog>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Payment')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}
