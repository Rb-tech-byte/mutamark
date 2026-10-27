import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { FileText, Eye, Trash2 } from "lucide-react";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FilterButton } from '@/components/ui/filter-button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate, formatCurrency } from '@/utils/helpers';

interface ServiceHistoryItem {
    id: number;
    service_id: string;
    customer_name: string;
    mobile_no: string;
    email: string;
    mobile_service: {
        name: string;
    };
    device_brand: {
        name: string;
    };
    mobile_model: string;
    priority: string;
    description: string;
    payment_status: number;
    assigned_technicians?: Array<{
        technician: {
            name: string;
        };
    }>;
    created_at: string;
    updated_at: string;
    total_amount?: number;
}

interface PageProps {
    serviceHistory: {
        data: ServiceHistoryItem[];
        links: any[];
        meta: any;
    };
    mobileServices?: Array<{id: number, name: string}>;
    auth: any;
}

export default function Index() {
    const { t } = useTranslation();
    const { serviceHistory, mobileServices = [], auth } = usePage<PageProps>().props;

    const urlParams = new URLSearchParams(window.location.search);
    const [searchTerm, setSearchTerm] = useState(urlParams.get('customer_name') || '');
    const [showFilters, setShowFilters] = useState(false);
    const [filters, setFilters] = useState({
        mobile_service_id: urlParams.get('mobile_service_id') || '',
        payment_status: urlParams.get('payment_status') || '',
        priority: urlParams.get('priority') || ''
    });
    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [itemToDelete, setItemToDelete] = useState<ServiceHistoryItem | null>(null);

    useFlashMessages();

    const handleFilter = () => {
        router.get(route('mobile-service-management.service-history.index'), { customer_name: searchTerm, ...filters, per_page: perPage, sort: sortField, direction: sortDirection }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('mobile-service-management.service-history.index'), { customer_name: searchTerm, ...filters, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setFilters({ mobile_service_id: '', payment_status: '', priority: '' });
        router.get(route('mobile-service-management.service-history.index'), { per_page: perPage });
    };

    const handleDelete = (item: ServiceHistoryItem) => {
        setItemToDelete(item);
        setDeleteDialogOpen(true);
    };

    const confirmDelete = () => {
        if (itemToDelete) {
            router.delete(route('mobile-service-management.service-history.destroy', itemToDelete.id), {
                onSuccess: () => {
                    setDeleteDialogOpen(false);
                    setItemToDelete(null);
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

    const getPaymentStatusBadge = (status: number) => {
        const statusConfig = {
            0: { class: 'bg-yellow-100 text-yellow-800', label: t('Pending') },
            1: { class: 'bg-green-100 text-green-800', label: t('Cleared') },
            2: { class: 'bg-red-100 text-red-800', label: t('Cancelled') }
        };
        
        const config = statusConfig[status as keyof typeof statusConfig] || statusConfig[0];
        
        return (
            <span className={`px-2 py-1 rounded-full text-xs font-medium ${config.class}`}>
                {config.label}
            </span>
        );
    };

    const tableColumns = [
        {
            key: 'service_id',
            header: t('Service ID'),
            sortable: false,
            render: (value: string, request: ServiceHistoryItem) => (
                auth.user?.permissions?.includes('view-mobile-service-history') ? (
                    <span className="text-blue-600 hover:text-blue-700 cursor-pointer" onClick={() => router.get(route('mobile-service-management.service-history.show', request.id))}>{value}</span>
                ) : (
                    <span>{value}</span>
                )
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
            key: 'payment_status',
            header: t('Payment Status'),
            sortable: true,
            render: (value: number) => getPaymentStatusBadge(value)
        },
        {
            key: 'assigned_technicians',
            header: t('Assigned Technician'),
            render: (value: any, request: ServiceHistoryItem) => {
                if (request.assigned_technicians && request.assigned_technicians.length > 0) {
                    return request.assigned_technicians.map(tech => tech.technician?.name || '-').join(', ');
                }
                return '-';
            }
        },
        {
            key: 'created_at',
            header: t('Created At'),
            sortable: true,
            render: (value: string) => formatDate(value)
        },
        {
            key: 'updated_at',
            header: t('Completed At'),
            sortable: true,
            render: (value: string) => formatDate(value)
        },
        {
            key: 'actions',
            header: t('Actions'),
            render: (_: any, request: ServiceHistoryItem) => (
                <div className="flex gap-1">
                    <TooltipProvider>
                        {auth.user?.permissions?.includes('view-mobile-service-history') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => router.get(route('mobile-service-management.service-history.show', request.id))}
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
                        {auth.user?.permissions?.includes('delete-mobile-service-history') && (
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handleDelete(request)}
                                        className="h-8 w-8 p-0 text-red-600 hover:text-red-700"
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
                { label: t('Service History') }
            ]}
            pageTitle={t('Manage Service History')}
        >
            <Head title={t('Service History')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={searchTerm}
                                onChange={(value) => setSearchTerm(value)}
                                onSearch={handleFilter}
                                placeholder={t('Search service history...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="mobile-service-management.service-history.index"
                                filters={{ customer_name: searchTerm, ...filters }}
                            />
                            <div className="relative">
                                <FilterButton
                                    showFilters={showFilters}
                                    onToggle={() => setShowFilters(!showFilters)}
                                />
                                {(() => {
                                    const activeFilters = [filters.mobile_service_id, filters.payment_status, filters.priority].filter(Boolean).length;
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
                                <label className="block text-sm font-medium text-gray-700 mb-2">{t('Payment Status')}</label>
                                <Select value={filters.payment_status} onValueChange={(value) => setFilters({...filters, payment_status: value})}>
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('Filter by payment status')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">{t('Unpaid')}</SelectItem>
                                        <SelectItem value="1">{t('Paid')}</SelectItem>
                                        <SelectItem value="2">{t('Cancelled')}</SelectItem>
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
                            data={serviceHistory?.data || []}
                            columns={tableColumns}
                            onSort={handleSort}
                            sortKey={sortField}
                            sortDirection={sortDirection as 'asc' | 'desc'}
                            emptyState={
                                <NoRecordsFound
                                    icon={FileText}
                                    title={t('No Service History found')}
                                    description={t('No completed service requests available.')}
                                    hasFilters={!!searchTerm || Object.values(filters).some(Boolean)}
                                    onClearFilters={clearFilters}
                                />
                            }
                        />
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={serviceHistory || { data: [], links: [], meta: {} }}
                        routeName="mobile-service-management.service-history.index"
                        filters={{ customer_name: searchTerm, ...filters, per_page: perPage }}
                    />
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={deleteDialogOpen}
                onOpenChange={setDeleteDialogOpen}
                title={t('Delete Service History')}
                description={t('Are you sure you want to delete this service history? This action cannot be undone.')}
                confirmText={t('Delete')}
                cancelText={t('Cancel')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}