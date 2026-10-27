import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { DataTable } from "@/components/ui/data-table";
import { MessageCircle, Trash2 } from "lucide-react";
import { Pagination } from "@/components/ui/pagination";
import { SearchInput } from "@/components/ui/search-input";
import { PerPageSelector } from '@/components/ui/per-page-selector';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { FilterButton } from '@/components/ui/filter-button';
import NoRecordsFound from '@/components/no-records-found';
import { formatDate } from '@/utils/helpers';

interface ContactData {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    subject: string;
    message: string;
    created_at: string;
}

interface PageProps {
    mobileservicecontacts: {
        data: ContactData[];
        links: any[];
        meta: any;
    };
}

export default function Index() {
    const { t } = useTranslation();
    const { mobileservicecontacts, auth } = usePage<PageProps & { auth: any }>().props;
    const urlParams = new URLSearchParams(window.location.search);

    const [filters, setFilters] = useState({
        name: urlParams.get('name') || '',
        email: urlParams.get('email') || '',
        subject: urlParams.get('subject') || '',
    });

    const [perPage] = useState(urlParams.get('per_page') || '10');
    const [sortField, setSortField] = useState(urlParams.get('sort') || '');
    const [sortDirection, setSortDirection] = useState(urlParams.get('direction') || 'asc');
    const [showFilters, setShowFilters] = useState(false);

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'mobile-service-management.mobile-service-contacts.destroy',
        defaultMessage: t('Are you sure you want to delete this contact?')
    });

    const handleFilter = () => {
        router.get(route('mobile-service-management.mobile-service-contacts.index'), { 
            name: filters.name,
            per_page: perPage, 
            sort: sortField, 
            direction: sortDirection 
        }, {
            preserveState: true,
            replace: true
        });
    };

    const handleSort = (field: string) => {
        const direction = sortField === field && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortField(field);
        setSortDirection(direction);
        router.get(route('mobile-service-management.mobile-service-contacts.index'), { ...filters, per_page: perPage, sort: field, direction }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilters({ name: '', email: '', subject: '' });
        router.get(route('mobile-service-management.mobile-service-contacts.index'), { per_page: perPage });
    };

    const tableColumns = [
        {
            key: 'first_name',
            header: t('First Name'),
            sortable: true
        },
        {
            key: 'last_name',
            header: t('Last Name'),
            sortable: true
        },
        {
            key: 'email',
            header: t('Email'),
            sortable: false
        },
        {
            key: 'phone',
            header: t('Phone'),
            render: (value: string) => value || '-'
        },
        {
            key: 'subject',
            header: t('Subject'),
            sortable: false
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
            render: (_: any, contact: ContactData) => (
                <div className="flex gap-1">
                    {auth.user?.permissions?.includes('delete-mobile-service-contacts') && (
                        <TooltipProvider>
                            <Tooltip delayDuration={0}>
                                <TooltipTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => openDeleteDialog(contact.id)}
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
                { label: t('Contacts') }
            ]}
            pageTitle={t('Manage Contacts')}
        >
            <Head title={t('Contacts')} />

            <Card className="shadow-sm">
                <CardContent className="p-6 border-b bg-gray-50/50">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex-1 max-w-md">
                            <SearchInput
                                value={filters.name}
                                onChange={(value) => setFilters({ ...filters, name: value })}
                                onSearch={handleFilter}
                                placeholder={t('Search by name, email, or subject...')}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <PerPageSelector
                                routeName="mobile-service-management.mobile-service-contacts.index"
                                filters={{ name: filters.name }}
                            />
                        </div>
                    </div>
                </CardContent>


                <CardContent className="p-0">
                    <div className="overflow-y-auto max-h-[70vh] w-full">
                        <DataTable
                            data={mobileservicecontacts?.data || []}
                            columns={tableColumns}
                            onSort={handleSort}
                            sortKey={sortField}
                            sortDirection={sortDirection as 'asc' | 'desc'}
                            emptyState={
                                <NoRecordsFound
                                    icon={MessageCircle}
                                    title={t('No Contacts found')}
                                    description={t('No contact messages available.')}
                                    hasFilters={!!(filters.name)}
                                    onClearFilters={clearFilters}
                                />
                            }
                        />
                    </div>
                </CardContent>

                <CardContent className="px-4 py-2 border-t bg-gray-50/30">
                    <Pagination
                        data={mobileservicecontacts || { data: [], links: [], meta: {} }}
                        routeName="mobile-service-management.mobile-service-contacts.index"
                        filters={{ name: filters.name, per_page: perPage }}
                    />
                </CardContent>
            </Card>

            <ConfirmationDialog
                open={deleteState.isOpen}
                onOpenChange={closeDeleteDialog}
                title={t('Delete Contact')}
                message={deleteState.message}
                confirmText={t('Delete')}
                onConfirm={confirmDelete}
                variant="destructive"
            />
        </AuthenticatedLayout>
    );
}