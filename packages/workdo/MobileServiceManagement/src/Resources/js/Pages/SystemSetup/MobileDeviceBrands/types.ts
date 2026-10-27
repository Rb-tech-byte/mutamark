import { PaginatedData, ModalState, AuthContext, CreateProps, EditProps } from '@/types/common';

export interface MobileDeviceBrand {
    id: number;
    name: string;
    created_at: string;
}

export interface MobileDeviceBrandFormData {
    name: string;
}

export interface CreateMobileDeviceBrandProps extends CreateProps {
}

export interface EditMobileDeviceBrandProps extends EditProps<MobileDeviceBrand> {
}

export type PaginatedMobileDeviceBrands = PaginatedData<MobileDeviceBrand>;
export type MobileDeviceBrandModalState = ModalState<MobileDeviceBrand>;

export interface MobileDeviceBrandsIndexProps {
    mobiledevicebrands: PaginatedMobileDeviceBrands;
    auth: AuthContext;
    [key: string]: unknown;
}