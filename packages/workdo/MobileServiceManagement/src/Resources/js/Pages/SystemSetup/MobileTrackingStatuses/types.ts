import { PaginatedData, ModalState, AuthContext, CreateProps, EditProps } from '@/types/common';

export interface MobileTrackingStatus {
    id: number;
    icon_name: any;
    status_color: any;
    status_name: string;
    order?: number;
    created_at: string;
}

export interface MobileTrackingStatusFormData {
    icon_name: any;
    status_color: any;
    status_name: string;
}

export interface CreateMobileTrackingStatusProps extends CreateProps {
}

export interface EditMobileTrackingStatusProps extends EditProps<MobileTrackingStatus> {
}

export type PaginatedMobileTrackingStatuses = PaginatedData<MobileTrackingStatus>;
export type MobileTrackingStatusModalState = ModalState<MobileTrackingStatus>;

export interface MobileTrackingStatusesIndexProps {
    mobiletrackingstatuses: PaginatedMobileTrackingStatuses;
    auth: AuthContext;
    [key: string]: unknown;
}