import { useState, useRef } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useDeleteHandler } from '@/hooks/useDeleteHandler';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { Dialog } from "@/components/ui/dialog";
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
import { Plus, Edit, Trash2, Smartphone, GripVertical } from "lucide-react";
import * as LucideIcons from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { toast } from 'sonner';

import Create from './Create';
import EditMobileTrackingStatus from './Edit';
import NoRecordsFound from '@/components/no-records-found';
import { MobileTrackingStatus, MobileTrackingStatusesIndexProps, MobileTrackingStatusModalState } from './types';
import SystemSetupSidebar from "../SystemSetupSidebar";

export default function Index() {
    const { t } = useTranslation();
    const { mobiletrackingstatuses, auth } = usePage<MobileTrackingStatusesIndexProps>().props;

    const [modalState, setModalState] = useState<MobileTrackingStatusModalState>({
        isOpen: false,
        mode: '',
        data: null
    });

    const [draggedIndex, setDraggedIndex] = useState<number | null>(null);
    const [dragOverIndex, setDragOverIndex] = useState<number | null>(null);

    useFlashMessages();

    const { deleteState, openDeleteDialog, closeDeleteDialog, confirmDelete } = useDeleteHandler({
        routeName: 'mobile-service.tracking-statuses.destroy',
        defaultMessage: t('Are you sure you want to delete this Mobile Tracking status?')
    });

    const openModal = (mode: 'add' | 'edit', data: MobileTrackingStatus | null = null) => {
        setModalState({ isOpen: true, mode, data });
    };

    const closeModal = () => {
        setModalState({ isOpen: false, mode: '', data: null });
    };

    const updateStatusOrder = (newOrder: number[]) => {
        router.post(route('mobile-service.tracking-statuses.update-order'), {
            status_ids: newOrder
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Success message will be shown via flash message
            },
            onError: () => {
                toast.error(t('Failed to update status order'));
            }
        });
    };

    const renderIcon = (iconName: string, size: number = 16) => {
        if (!iconName) return null;

        const IconComponent = LucideIcons[iconName as keyof typeof LucideIcons] as React.ComponentType<{ size?: number }>;
        if (IconComponent) {
            return <IconComponent size={size} />;
        }

        return null;
    };


    return (
        <TooltipProvider>
            <AuthenticatedLayout
                breadcrumbs={[
                    {label: t('Mobile Service'),url:route('mobile-service.index')},
                    {label: t('System Setup'),url:route('mobile-service.device-brands.index')},
                    {label: t('Tracking statuses')}
                ]}
                pageTitle={t('System Setup')}
            >
                <Head title={t('Tracking statuses')} />

                <div className="flex flex-col md:flex-row gap-8">
                    <div className="md:w-64 flex-shrink-0">
                        <SystemSetupSidebar activeItem="mobile-tracking-statuses" />
                    </div>

                    <div className="flex-1">
                        <Card className="shadow-sm">
                            <CardContent className="p-6">
                                <div className="flex justify-between items-center mb-6">
                                    <h3 className="text-lg font-medium">{t('Tracking statuses')}</h3>
                                    {auth.user?.permissions?.includes('create-mobile-tracking-statuses') && (
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
                                </div>
                                <div className="overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 max-h-[75vh] rounded-none w-full" style={{ WebkitUserSelect: 'none', userSelect: 'none' }}>
                                    {mobiletrackingstatuses.length > 0 ? (
                                        <div className="space-y-3">
                                            {mobiletrackingstatuses
                                                .sort((a: MobileTrackingStatus, b: MobileTrackingStatus) => (a.order || 0) - (b.order || 0))
                                                .map((status: MobileTrackingStatus, index: number) => {
                                                    const isProtected = status.status_name === 'Received' || status.status_name === 'Completed';
                                                    return (
                                                        <div
                                                            key={status.id}
                                                            draggable={!isProtected}
                                                            onDragStart={(e) => {
                                                                if (!isProtected) {
                                                                    setDraggedIndex(index);
                                                                    e.dataTransfer.effectAllowed = 'move';
                                                                    e.dataTransfer.setData('text/plain', index.toString());
                                                                    const dragImage = e.currentTarget.cloneNode(true) as HTMLElement;
                                                                    dragImage.style.transform = 'rotate(5deg)';
                                                                    dragImage.style.opacity = '0.8';
                                                                    document.body.appendChild(dragImage);
                                                                    e.dataTransfer.setDragImage(dragImage, 0, 0);
                                                                    setTimeout(() => document.body.removeChild(dragImage), 0);
                                                                } else {
                                                                    e.preventDefault();
                                                                }
                                                            }}
                                                            onDragEnd={() => {
                                                                setDraggedIndex(null);
                                                                setDragOverIndex(null);
                                                            }}
                                                            onDragOver={(e) => {
                                                                if (!isProtected) {
                                                                    e.preventDefault();
                                                                    e.dataTransfer.dropEffect = 'move';
                                                                    setDragOverIndex(index);
                                                                }
                                                            }}
                                                            onDragLeave={() => {
                                                                setDragOverIndex(null);
                                                            }}
                                                            onDrop={(e) => {
                                                                e.preventDefault();
                                                                const dragIndex = parseInt(e.dataTransfer.getData('text/plain'));
                                                                if (dragIndex === index || isProtected) return;

                                                                const sortedStatuses = [...mobiletrackingstatuses].sort((a: MobileTrackingStatus, b: MobileTrackingStatus) => (a.order || 0) - (b.order || 0));
                                                                const newOrder = [...sortedStatuses];
                                                                const draggedItem = newOrder[dragIndex];

                                                                newOrder.splice(dragIndex, 1);
                                                                newOrder.splice(index, 0, draggedItem);

                                                                const statusIds = newOrder.map((s: MobileTrackingStatus) => s.id);
                                                                updateStatusOrder(statusIds);

                                                                setDraggedIndex(null);
                                                                setDragOverIndex(null);
                                                            }}
                                                            className={`flex items-center gap-3 p-4 border rounded-lg transition-all select-none ${
                                                                draggedIndex === index
                                                                    ? 'opacity-50 scale-95 bg-blue-50 border-blue-300'
                                                                    : dragOverIndex === index
                                                                        ? 'bg-blue-50 border-blue-300 shadow-lg'
                                                                        : 'bg-white border-gray-200 hover:shadow-md'
                                                            } ${!isProtected ? 'cursor-move' : 'cursor-default'}`}
                                                            style={{ userSelect: 'none' }}
                                                        >
                                                            {!isProtected && <GripVertical className={`h-5 w-5 transition-colors ${draggedIndex === index ? 'text-blue-500' : 'text-gray-400'}`} />}
                                                            {isProtected && <div className="w-5 h-5" />}
                                                            <div className="flex-1 flex items-center justify-between">
                                                                <div className="flex items-center gap-3">
                                                                    <div
                                                                        className="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2"
                                                                        style={{
                                                                            backgroundColor: status.status_color || '#FF6B6B',
                                                                            borderColor: status.status_color || '#FF6B6B',
                                                                            color: '#fff'
                                                                        }}
                                                                    >
                                                                        {status.icon_name && renderIcon(status.icon_name, 16)}
                                                                    </div>
                                                                    <div>
                                                                        <h4 className="font-medium text-gray-900 pointer-events-none">{status.status_name}</h4>
                                                                    </div>
                                                                </div>
                                                                <div className="flex gap-1">
                                                                    <TooltipProvider>
                                                                        {auth.user?.permissions?.includes('edit-mobile-tracking-statuses') && !isProtected && (
                                                                            <Tooltip delayDuration={0}>
                                                                                <TooltipTrigger asChild>
                                                                                    <Button variant="ghost" size="sm" onClick={() => openModal('edit', status)} className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700">
                                                                                        <Edit className="h-4 w-4" />
                                                                                    </Button>
                                                                                </TooltipTrigger>
                                                                                <TooltipContent>
                                                                                    <p>{t('Edit')}</p>
                                                                                </TooltipContent>
                                                                            </Tooltip>
                                                                        )}
                                                                        {auth.user?.permissions?.includes('delete-mobile-tracking-statuses') && !isProtected && (
                                                                            <Tooltip delayDuration={0}>
                                                                                <TooltipTrigger asChild>
                                                                                    <Button
                                                                                        variant="ghost"
                                                                                        size="sm"
                                                                                        onClick={() => openDeleteDialog(status.id)}
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
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                        </div>
                                    ) : (
                                        <NoRecordsFound
                                            icon={Smartphone}
                                            title={t('No Mobile Tracking Statuses found')}
                                            description={t('Get started by creating your first Mobile Tracking Status.')}
                                            createPermission="create-mobile-tracking-statuses"
                                            onCreateClick={() => openModal('add')}
                                            createButtonText={t('Create Mobile Tracking Status')}
                                            className="h-auto"
                                        />
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <Dialog open={modalState.isOpen} onOpenChange={closeModal}>
                    {modalState.mode === 'add' && (
                        <Create onSuccess={closeModal} />
                    )}
                    {modalState.mode === 'edit' && modalState.data && (
                        <EditMobileTrackingStatus
                            mobiletrackingstatus={modalState.data}
                            onSuccess={closeModal}
                        />
                    )}
                </Dialog>

                <ConfirmationDialog
                    open={deleteState.isOpen}
                    onOpenChange={closeDeleteDialog}
                    title={t('Delete Tracking status')}
                    message={deleteState.message}
                    confirmText={t('Delete')}
                    onConfirm={confirmDelete}
                    variant="destructive"
                />
            </AuthenticatedLayout>
        </TooltipProvider>
    );
}