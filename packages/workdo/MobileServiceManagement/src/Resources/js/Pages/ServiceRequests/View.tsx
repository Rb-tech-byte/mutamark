import React from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import * as Icons from 'lucide-react';
import { formatDate, getImagePath } from '@/utils/helpers';

interface ServiceRequest {
  id: number;
  service_id: string;
  customer_name: string;
  email: string;
  mobile_no: string;
  address: string;
  mobile_model: string;
  priority: string;
  description: string;
  special_requests: string;
  attachment: string;
  is_approve: number | null;
  tracking_status: {
    id: number;
    status_name: string;
    status_color: string;
  } | string;
  tracking_status_log?: number[];
  mobile_service?: { name: string };
  device_brand?: { name: string };
  created_at: string;
}

interface TrackingStatus {
  id: number;
  status_name: string;
  status_color?: string;
  icon_name?: string;
  order?: number;
}

interface ShowProps {
  mobileservicerequest: ServiceRequest;
  trackingStatuses: TrackingStatus[];
  allTrackingStatuses: TrackingStatus[];
}

const Show: React.FC = () => {
  const { t } = useTranslation();
  const { mobileservicerequest, trackingStatuses, allTrackingStatuses } = usePage<ShowProps>().props;

  useFlashMessages();

  // Get next available status for update
  // Get next available status for update
  const getNextAvailableStatus = () => {
    // Use tracking_status_log from mobileservicerequest if available, ensure it's an array
    const completedStatusIds = Array.isArray(mobileservicerequest.tracking_status_log) ? mobileservicerequest.tracking_status_log : [];
    const sortedStatuses = allTrackingStatuses.sort((a, b) => (a.order || 0) - (b.order || 0));

    // Find the next status that hasn't been completed
    for (let i = 0; i < sortedStatuses.length; i++) {
      const statusId = sortedStatuses[i].id;
      if (!completedStatusIds.includes(statusId)) {
        return sortedStatuses[i];
      }
    }
    return null;
  };

  const nextStatus = getNextAvailableStatus();

  const handleUpdateTracking = () => {
    if (nextStatus) {
      router.post(route('mobile-service-management.service-requests.update-tracking', mobileservicerequest.id), {
        tracking_status: nextStatus.id.toString()
      }, {
        preserveState: true,
        preserveScroll: true
      });
    }
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

  return (
    <AuthenticatedLayout
      breadcrumbs={[
        { label: t('Mobile Service Management'), url: route('mobile-service.index') },
        { label: t('Service Requests'), url: route('mobile-service-management.service-requests.index') },
        { label: t('Service Request Detail') }
      ]}
      pageTitle={`${t('Service Request')} #${mobileservicerequest.service_id}`}
    >
      <Head title={`${t('Service Request')} #${mobileservicerequest.service_id}`} />

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
                <p className="mt-1 text-sm font-mono bg-blue-50 px-2 py-1 rounded">{mobileservicerequest.service_id}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-500">{t('Service')}</label>
                <p className="mt-1 text-sm">{mobileservicerequest.mobile_service?.name || '-'}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-500">{t('Device Model')}</label>
                <p className="mt-1 text-sm">{mobileservicerequest.mobile_model}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-500">{t('Device Brand')}</label>
                <p className="mt-1 text-sm">{mobileservicerequest.device_brand?.name || '-'}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-500">{t('Priority')}</label>
                <div className="mt-1">{getPriorityBadge(mobileservicerequest.priority)}</div>
              </div>
              <div>
                <label className="text-sm font-medium text-gray-500">{t('Tracking Status')}</label>
                <div className="mt-1">
                  {mobileservicerequest.tracking_status && typeof mobileservicerequest.tracking_status === 'object' && mobileservicerequest.tracking_status.status_name ? (
                    <Badge
                      className="text-white"
                      style={{
                        backgroundColor: mobileservicerequest.tracking_status.status_color || '#6b7280'
                      }}
                    >
                      {mobileservicerequest.tracking_status.status_name}
                    </Badge>
                  ) : (
                    <Badge className="!bg-gray-100 !text-gray-800">-</Badge>
                  )}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Customer Information */}
        <Card>
          <CardHeader>
            <CardTitle>{t('Customer Information')}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <div>
                  <label className="text-sm font-medium text-gray-500">{t('Customer Name')}</label>
                  <p className="mt-1 text-sm">{mobileservicerequest.customer_name}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-500">{t('Email')}</label>
                  <p className="mt-1 text-sm">{mobileservicerequest.email}</p>
                </div>

              </div>
              <div className="space-y-4">
                <div>
                  <label className="text-sm font-medium text-gray-500">{t('Mobile Number')}</label>
                  <p className="mt-1 text-sm">{mobileservicerequest.mobile_no}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-500">{t('Address')}</label>
                  <p className="mt-1 text-sm">{mobileservicerequest.address}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Issue & Special Requests */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>{t('Issue Description')}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm leading-relaxed">{mobileservicerequest.description}</p>
            </CardContent>
          </Card>

          {mobileservicerequest.special_requests && (
            <Card>
              <CardHeader>
                <CardTitle>{t('Special Requests')}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-sm leading-relaxed">{mobileservicerequest.special_requests}</p>
              </CardContent>
            </Card>
          )}
        </div>

        {/* Attachments */}
        {mobileservicerequest.attachment && JSON.parse(mobileservicerequest.attachment).length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle>{t('Attachments')}</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex flex-wrap gap-3">
                {JSON.parse(mobileservicerequest.attachment).map((attachment: string, index: number) => {
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
                            <DropdownMenuItem 
                              onClick={() => {
                                router.post(route('mobile-service-management.service-requests.delete-attachment', mobileservicerequest.id), {
                                  attachment: attachment
                                });
                              }}
                              className="text-red-600"
                            >
                              <Icons.Trash2 className="h-4 w-4 mr-2" />
                              {t('Delete')}
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
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle>{t('Service Tracking')}</CardTitle>
            {nextStatus && (
              <Button
                onClick={handleUpdateTracking}
                size="sm"
                className="h-10 w-10 p-0 bg-primary text-white rounded-full"
                title={t('Update to Next Status')}
              >
                <Icons.ChevronRight className="h-4 w-4" />
              </Button>
            )}
          </CardHeader>
          <CardContent>
            <div className="py-4">
              <div className="flex items-center justify-between relative">
                {(allTrackingStatuses || [])
                  .sort((a, b) => (a.order || 0) - (b.order || 0))
                  .map((status, index) => {
                    const completedStatusIds = Array.isArray(mobileservicerequest.tracking_status_log) ? mobileservicerequest.tracking_status_log : [];
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
};

export default Show;