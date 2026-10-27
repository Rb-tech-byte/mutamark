import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from '@/components/ui/button';
import { Check, X, Smartphone, User, Settings, FileText, Image, MoreVertical, Eye, Trash2 } from "lucide-react";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { formatDate, getImagePath } from '@/utils/helpers';

interface RequestData {
    id: number;
    service_id: string;
    customer_name: string;
    email: string;
    mobile_no: string;
    address: string;
    mobile_model: string;
    priority: string;
    tracking_status: string;
    is_approve: number | null;
    description: string;
    special_requests: string;
    attachment: string;
    mobile_service?: {
        id: number;
        name: string;
    };
    get_service_created_name?: {
        id: number;
        name: string;
    };
    created_at: string;
}

interface PageProps {
    pendingservicerequest: RequestData;
    auth: any;
}

export default function View() {
    const { t } = useTranslation();
    const { pendingservicerequest, auth } = usePage<PageProps>().props;

    useFlashMessages();

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

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service Management'), url: route('mobile-service.index') },
                { label: t('Pending Requests'), url: route('mobile-service-management.pending-service-requests.index') },
                { label: pendingservicerequest.service_id }
            ]}
            pageTitle={t('Service Request Details')}
            pageActions={
                <div className="flex gap-2">

                    <TooltipProvider>
                    {pendingservicerequest.is_approve === 0 && auth.user?.permissions?.includes('approve-mobile-pending-requests') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button
                                    size="sm"
                                    onClick={() => router.post(route('mobile-service-management.pending-service-requests.approve', pendingservicerequest.id))}
                                    className="bg-green-500 hover:bg-green-600"
                                >
                                    <Check className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Approve')}</p>
                            </TooltipContent>
                        </Tooltip>
                        )}
                    {pendingservicerequest.is_approve === 0 && auth.user?.permissions?.includes('reject-mobile-pending-requests') && (
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    onClick={() => router.post(route('mobile-service-management.pending-service-requests.reject', pendingservicerequest.id))}
                                >
                                    <X className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{t('Reject')}</p>
                            </TooltipContent>
                        </Tooltip>
                        )}
                    </TooltipProvider>
                </div>
            }
        >
            <Head title={pendingservicerequest.service_id} />

            {/* Request Summary */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <Card className="shadow-sm">
                    <CardHeader className="p-6 border-b bg-gray-50/50">
                        <CardTitle className="text-lg flex items-center gap-2">
                            <User className="h-5 w-5 text-blue-600" />
                            {t('Customer Information')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-6">
                        <div className="space-y-4">
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Customer Name')}</span>
                                <span className="text-sm text-gray-900 text-right">{pendingservicerequest.customer_name}</span>
                            </div>
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Email')}</span>
                                <span className="text-sm text-gray-900 text-right">{pendingservicerequest.email}</span>
                            </div>
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Phone')}</span>
                                <span className="text-sm text-gray-900 text-right">{pendingservicerequest.mobile_no}</span>
                            </div>
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Created By')}</span>
                                <span className="text-sm text-gray-900 text-right">{pendingservicerequest.get_service_created_name?.name || '-'}</span>
                            </div>
                            {pendingservicerequest.address && (
                                <div className="pt-2 border-t border-gray-100">
                                    <span className="text-sm font-medium text-gray-600 block mb-2">{t('Address')}</span>
                                    <p className="text-sm text-gray-900 leading-relaxed">{pendingservicerequest.address}</p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardHeader className="p-6 border-b bg-gray-50/50">
                        <CardTitle className="text-lg flex items-center gap-2">
                            <Smartphone className="h-5 w-5 text-blue-600" />
                            {t('Service Details')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-6">
                        <div className="space-y-4">
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Service')}</span>
                                <span className="text-sm text-gray-900 text-right">{pendingservicerequest.mobile_service?.name || '-'}</span>
                            </div>
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Device Model')}</span>
                                <span className="text-sm text-gray-900 text-right">{pendingservicerequest.mobile_model}</span>
                            </div>
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Priority')}</span>
                                <div className="text-right">{getPriorityBadge(pendingservicerequest.priority)}</div>
                            </div>
                            <div className="flex justify-between items-start">
                                <span className="text-sm font-medium text-gray-600 min-w-0 flex-shrink-0">{t('Request Status')}</span>
                                <div className="text-right">
                                    <span className={`px-2 py-1 rounded-full text-sm ${
                                        pendingservicerequest.is_approve === 1 ? 'bg-green-100 text-green-800' :
                                        pendingservicerequest.is_approve === 2 ? 'bg-red-100 text-red-800' :
                                        'bg-yellow-100 text-yellow-800'
                                    }`}>
                                        {pendingservicerequest.is_approve === 0 ? 'Pending' : (pendingservicerequest.is_approve === 1 ? 'Approved' : 'Rejected')}
                                    </span>
                                </div>
                            </div>
                            
                            <div className="pt-2 border-t border-gray-100">
                                <span className="text-sm font-medium text-gray-600 block mb-2">{t('Issue Description')}</span>
                                <p className="text-sm text-gray-900 leading-relaxed">{pendingservicerequest.description}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Special Requests */}
            {(pendingservicerequest.special_requests || (pendingservicerequest.attachment && JSON.parse(pendingservicerequest.attachment).length > 0)) && (
                <Card className="shadow-sm mt-6">
                    <CardHeader className="p-6 border-b bg-gray-50/50">
                        <CardTitle className="text-lg flex items-center gap-2">
                            <Settings className="h-5 w-5 text-orange-600" />
                            {t('Special Requests')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-6">
                        {pendingservicerequest.special_requests && (
                            <div className="bg-gray-50 rounded-lg p-4 mb-4">
                                <p className="text-gray-900 whitespace-pre-wrap leading-relaxed">{pendingservicerequest.special_requests}</p>
                            </div>
                        )}
                        {pendingservicerequest.attachment && JSON.parse(pendingservicerequest.attachment).length > 0 && (
                            <div>
                                <h4 className="text-sm font-medium text-gray-600 mb-3">{t('Attachments')}</h4>
                                <div className="flex flex-wrap gap-3">
                                    {JSON.parse(pendingservicerequest.attachment).map((attachment: string, index: number) => {
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
                                                        <FileText className="w-8 h-8 text-gray-600" />
                                                    </div>
                                                )}
                                                <div className="absolute top-1 right-1">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="sm" className="h-6 w-6 p-0 bg-white/80 hover:bg-white">
                                                                <MoreVertical className="h-3 w-3" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem onClick={() => window.open(fileUrl, '_blank')}>
                                                                <Eye className="h-4 w-4 mr-2" />
                                                                {t('View')}
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem 
                                                                onClick={() => {
                                                                    router.post(route('mobile-service-management.pending-service-requests.delete-attachment', pendingservicerequest.id), {
                                                                        attachment: attachment
                                                                    });
                                                                }}
                                                                className="text-red-600"
                                                            >
                                                                <Trash2 className="h-4 w-4 mr-2" />
                                                                {t('Delete')}
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            )}
        </AuthenticatedLayout>
    );
}