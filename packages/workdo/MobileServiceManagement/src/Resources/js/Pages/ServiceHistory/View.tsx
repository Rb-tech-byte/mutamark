import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from '@/components/ui/badge';
import * as Icons from 'lucide-react';
import { formatDate, formatTime, getImagePath } from '@/utils/helpers';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";

interface ServiceHistoryItem {
    id: number;
    service_id: string;
    customer_name: string;
    mobile_no: string;
    email: string;
    address: string;
    mobile_service: {
        name: string;
    };
    device_brand: {
        name: string;
    };
    mobile_model: string;
    priority: string;
    payment_status:number;
    description: string;
    special_requests?: string;
    created_at: string;
    updated_at: string;
    total_amount?: number;
    attachment?: string;
    tracking_status_log?: number[];
}

interface TrackingStatus {
    id: number;
    status_name: string;
    status_color?: string;
    icon_name?: string;
    order?: number;
}

export default function View() {
    const { t } = useTranslation();
    const { serviceHistoryItem, allTrackingStatuses } = usePage().props as {
        serviceHistoryItem: ServiceHistoryItem;
        allTrackingStatuses: TrackingStatus[];
    };

    const getPriorityBadge = (priority: string) => {
        const priorityMap = {
            'emergency': { label: t('Emergency'), class: '!bg-red-100 !text-red-800' },
            'priority': { label: t('Priority'), class: '!bg-purple-100 !text-purple-800' },
            'standard': { label: t('Standard'), class: '!bg-gray-100 !text-gray-800' }
        };
        const priorityInfo = priorityMap[priority as keyof typeof priorityMap] || { label: priority, class: '!bg-gray-100 !text-gray-800' };
        return <Badge className={priorityInfo.class}>{priorityInfo.label}</Badge>;
    };

    const getPaymentStatusBadge = (status: number) => {
        const statusConfig = {
            0: { class: 'bg-yellow-100 text-yellow-800', label: t('Pending') },
            1: { class: 'bg-green-100 text-green-800', label: t('Cleared') },
            2: { class: 'bg-red-100 text-red-800', label: t('Cancelled') }
        };
        
        const config = statusConfig[status as keyof typeof statusConfig] || statusConfig[0];
        
        return (
            <Badge className={config.class}>
                {config.label}
            </Badge>
        );
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service Management'), url: route('mobile-service.index') },
                { label: t('Service History'), url: route('mobile-service-management.service-history.index') },
                { label: t('Service History Detail') }
            ]}
            pageTitle={`${t('Service History')} #${serviceHistoryItem.service_id}`}
        >
            <Head title={`${t('Service History')} #${serviceHistoryItem.service_id}`} />

            <div className="space-y-6">
                {/* Service Request Details */}
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Service Request Information')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Service ID')}</label>
                                <p className="mt-1 text-sm font-mono bg-blue-50 px-2 py-1 rounded">{serviceHistoryItem.service_id}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Service')}</label>
                                <p className="mt-1 text-sm">{serviceHistoryItem.mobile_service?.name || '-'}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Device Model')}</label>
                                <p className="mt-1 text-sm">{serviceHistoryItem.mobile_model}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Device Brand')}</label>
                                <p className="mt-1 text-sm">{serviceHistoryItem.device_brand?.name || '-'}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Priority')}</label>
                                <div className="mt-1">{getPriorityBadge(serviceHistoryItem.priority)}</div>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Payment Status')}</label>
                                <div className="mt-1">{getPaymentStatusBadge(serviceHistoryItem.payment_status)}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Customer & Technician Information */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Customer Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Customer Information')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">{t('Customer Name')}</label>
                                        <p className="mt-1 text-sm">{serviceHistoryItem.customer_name}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">{t('Email')}</label>
                                        <p className="mt-1 text-sm">{serviceHistoryItem.email}</p>
                                    </div>
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">{t('Mobile Number')}</label>
                                        <p className="mt-1 text-sm">{serviceHistoryItem.mobile_no}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">{t('Address')}</label>
                                        <p className="mt-1 text-sm">{serviceHistoryItem.address}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Technician Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Technician Information')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {serviceHistoryItem.assigned_technicians && serviceHistoryItem.assigned_technicians.length > 0 ? (
                                serviceHistoryItem.assigned_technicians.map((assignment, index) => (
                                    <div key={index} className={`${index > 0 ? 'border-t pt-4 mt-4' : ''} space-y-4`}>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">{t('Technician Name')}</label>
                                                <p className="mt-1 text-sm">{assignment.technician.name}</p>
                                            </div>
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">{t('Email')}</label>
                                                <p className="mt-1 text-sm">{assignment.technician.email || '-'}</p>
                                            </div>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">{t('Phone Number')}</label>
                                                <p className="mt-1 text-sm">{assignment.technician.mobile_no || '-'}</p>
                                            </div>
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">{t('Estimated Completion Date')}</label>
                                                <p className="mt-1 text-sm">{formatDate(assignment.completion_date) || '-'}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p className="text-sm text-gray-500">{t('No technician assigned')}</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Issue & Special Requests */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Issue Description')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm leading-relaxed">{serviceHistoryItem.description}</p>
                        </CardContent>
                    </Card>

                    {serviceHistoryItem.special_requests && (
                        <Card>
                            <CardHeader>
                                <CardTitle>{t('Special Requests')}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm leading-relaxed">{serviceHistoryItem.special_requests}</p>
                            </CardContent>
                        </Card>
                    )}
                </div>

                {/* Attachments */}
                {serviceHistoryItem.attachment && JSON.parse(serviceHistoryItem.attachment).length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Attachments')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap gap-3">
                                {JSON.parse(serviceHistoryItem.attachment).map((attachment: string, index: number) => {
                                    const isImage = /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(attachment);
                                    const fileUrl = getImagePath(attachment);
                                    return (
                                        <div key={index} className="relative w-24 h-24 group">
                                            {isImage ? (
                                                <img
                                                    src={fileUrl}
                                                    alt={`Attachment ${index + 1}`}
                                                    className="w-24 h-24 object-contain rounded-lg border"
                                                />
                                            ) : (
                                                <div className="w-24 h-24 bg-gray-100 rounded-lg border flex items-center justify-center">
                                                    <Icons.FileText className="w-8 h-8 text-gray-600" />
                                                </div>
                                            )}
                                            <div className="absolute top-1 right-1">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm" className="h-6 w-6 p-0 bg-white/80 hover:bg-white">
                                                            <Icons.MoreVertical className="h-3 w-3" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem onClick={() => window.open(fileUrl, '_blank')}>
                                                            <Icons.Eye className="h-4 w-4 mr-2" />
                                                            {t('View')}
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Service Tracking Progress */}
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Service Tracking')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="py-4">
                            <div className="flex items-center justify-between relative">
                                {(allTrackingStatuses || [])
                                    .sort((a, b) => (a.order || 0) - (b.order || 0))
                                    .map((status, index) => {
                                        const completedStatusIds = Array.isArray(serviceHistoryItem.tracking_status_log) ? serviceHistoryItem.tracking_status_log : [];
                                        const isActive = completedStatusIds.includes(status.id);
                                        const isLast = index === allTrackingStatuses.length - 1;

                                        return (
                                            <React.Fragment key={status.id}>
                                                <div className="flex flex-col items-center relative z-10">
                                                    <div
                                                        className={`w-10 h-10 rounded-full flex items-center justify-center border-2 ${isActive ? 'text-white' : 'bg-gray-200 border-gray-300 text-gray-500'
                                                            }`}
                                                        style={isActive ? {
                                                            backgroundColor: status.status_color || '#32CD32',
                                                            borderColor: status.status_color || '#32CD32'
                                                        } : {}}
                                                    >
                                                        {(() => {
                                                            if (status.icon_name) {
                                                                const IconComponent = Icons[status.icon_name as keyof typeof Icons] as React.ComponentType<any>;
                                                                return IconComponent ? <IconComponent size={16} /> : (index + 1);
                                                            }
                                                            return index + 1;
                                                        })()}
                                                    </div>
                                                    <span
                                                        className={`mt-2 text-xs font-medium text-center max-w-20 ${isActive ? '' : 'text-gray-500'
                                                            }`}
                                                        style={isActive ? { color: status.status_color || '#32CD32' } : {}}
                                                    >
                                                        {status.status_name}
                                                    </span>
                                                </div>
                                                {!isLast && (
                                                    <div
                                                        className={`flex-1 h-0.5 mx-2 ${isActive ? '' : 'bg-gray-300'
                                                            }`}
                                                        style={isActive ? { backgroundColor: status.status_color || '#32CD32' } : {}}
                                                    />
                                                )}
                                            </React.Fragment>
                                        );
                                    })}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}