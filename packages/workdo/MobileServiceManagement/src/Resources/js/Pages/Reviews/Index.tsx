import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { Star, Trash2, Eye } from "lucide-react";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Button } from '@/components/ui/button';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import NoRecordsFound from '@/components/no-records-found';
import { formatDate } from '@/utils/helpers';

interface ReviewData {
    id: number;
    name: string;
    email: string;
    rating: number;
    review: string;
    mobile_service?: {
        id: number;
        name: string;
    };
    created_at: string;
}

interface PageProps {
    mobileservicereviews: {
        data: ReviewData[];
        links: any[];
        meta: any;
    };
    mobileservices: Array<{
        id: number;
        name: string;
    }>;
}

export default function Index() {
    const { t } = useTranslation();
    const { mobileservicereviews,auth } = usePage<PageProps & { auth: any }>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState({
        name: urlParams.get('name') || '',
        rating: urlParams.get('rating') || '',
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [selectedReview, setSelectedReview] = useState<ReviewData | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'mobile-service-management.mobile-service-reviews.destroy',
        defaultMessage: t('Are you sure you want to delete this review?')
    });

    const openViewModal = (review: ReviewData) => {
        setSelectedReview(review);
        setIsModalOpen(true);
    };

    const closeViewModal = () => {
        setSelectedReview(null);
        setIsModalOpen(false);
    };

    const handleFilter = () => {
        router.get(route('mobile-service-management.mobile-service-reviews.index'), { ...filters, per_page: perPage, sort: sortField, direction: sortDirection }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('mobile-service-management.mobile-service-reviews.index'), { ...filters, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ name: '', rating: '' });
        router.get(route('mobile-service-management.mobile-service-reviews.index'), { per_page: perPage });
    };

    const renderStars = (rating: number) => {
        return (
            <div className="flex">
                {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                        key={star}
                        className={`h-4 w-4 ${star <= rating ? 'text-yellow-400 fill-current' : 'text-gray-300'}`}
                    />
                ))}
            </div>
        );
    };

    const tableColumns = [
        {
            key: 'name',
            header: t('Name'),
            sortable: true
        },
        {
            key: 'email',
            header: t('Email'),
            sortable: true
        },
        {
            key: 'rating',
            header: t('Rating'),
            sortable: true,
            render: (value: number) => renderStars(value)
        },
        {
            key: 'mobile_service',
            header: t('Service'),
            render: (value: any) => value?.name || '-'
        },
        {
            key: 'review',
            header: t('Review'),
            render: (value: string) => (
                <div className="max-w-xs truncate" title={value}>
                    {value}
                </div>
            )
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
            render: (_: any, review: ReviewData) => (
                <div className="flex gap-1">
                    {auth.user?.permissions?.includes('view-mobile-service-reviews') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openViewModal(review)}
                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700"
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
                    {auth.user?.permissions?.includes('delete-mobile-service-reviews') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(review.id)}
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
                { label: t('Reviews') }
            ]}
            pageTitle={t('Manage Reviews')}
        >
            <Head title={t('Reviews')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.name}
                                onChange={(value) => setFilters({ ...filters, name: value })}
                                onSearch={handleFilter}
                                placeholder={t('Search Reviews...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="mobile-service-management.mobile-service-reviews.index"
                                filters={{ ...filters }}
                            />
                        </div>
                    </div>
                </CardContent>

                <CardContent className="p-0">
                    <div className="overflow-y-auto max-h-[70vh] w-full">
                        <DataTable
                            data={mobileservicereviews?.data || []}
                            columns={tableColumns}
                            onSort={handleSort}
                            sortKey={sortField}
                            sortDirection={sortDirection as 'asc' | 'desc'}
                            emptyState={
                                <NoRecordsFound
                                    icon={Star}
                                    title={t('No Reviews found')}
                                    description={t('No customer reviews available.')}
                                    hasFilters={!!(filters.name || filters.rating)}
                                    onClearFilters={clearFilters}
                                />
                            }
                        />
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={mobileservicereviews || { data: [], links: [], meta: {} }}
                        routeName="mobile-service-management.mobile-service-reviews.index"
                        filters={{ ...filters, per_page: perPage }}
                    />
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Review')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />

            <Dialog open={isModalOpen} onOpenChange={closeViewModal}>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader className="pb-4 border-b">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-primary/10 rounded-lg">
                                <Star className="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <DialogTitle className="text-xl font-semibold">{t('Review Details')}</DialogTitle>
                            </div>
                        </div>
                    </DialogHeader>

                    {selectedReview && (
                        <div className="overflow-y-auto flex-1 p-4 space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <label className="text-sm font-medium text-gray-700">{t('Name')}</label>
                                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{selectedReview.name}</p>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-sm font-medium text-gray-700">{t('Email')}</label>
                                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{selectedReview.email}</p>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-sm font-medium text-gray-700">{t('Rating')}</label>
                                    <div className="bg-gray-50 p-2 rounded flex items-center gap-2">
                                        {renderStars(selectedReview.rating)}
                                        <span className="font-medium text-sm text-gray-900">({selectedReview.rating}/5)</span>
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-sm font-medium text-gray-700">{t('Service')}</label>
                                    <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded">{selectedReview.mobile_service?.name || '-'}</p>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-gray-700">{t('Review')}</label>
                                <p className="text-sm text-gray-900 bg-gray-50 p-2 rounded whitespace-pre-wrap">{selectedReview.review}</p>
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}