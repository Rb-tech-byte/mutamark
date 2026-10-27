import { PaginatedData, ModalState, AuthContext } from '@/types/common';



export interface MobileServicePolicy {
    id: number;
    policy_name: string;
    description?: string;
    response_time: number;
    resolution_time: number;
    created_by?: number;
    creator_id?: number;
    created_at: string;
}

export interface CreateMobileServicePolicyFormData {
    policy_name: string;
    description: string;
    response_time: string;
    resolution_time: string;
}

export interface EditMobileServicePolicyFormData {
    policy_name: string;
    description: string;
    response_time: string;
    resolution_time: string;
}

export interface MobileServicePolicyFilters {
    policy_name: string;
    description: string;
}

export type PaginatedMobileServicePolicies = PaginatedData<MobileServicePolicy>;
export type MobileServicePolicyModalState = ModalState<MobileServicePolicy>;

export interface MobileServicePoliciesIndexProps {
    mobileservicepolicies: PaginatedMobileServicePolicies;
    auth: AuthContext;
    [key: string]: unknown;
}

export interface CreateMobileServicePolicyProps {
    onSuccess: () => void;
}

export interface EditMobileServicePolicyProps {
    mobileservicepolicy: MobileServicePolicy;
    onSuccess: () => void;
}

export interface MobileServicePolicyShowProps {
    mobileservicepolicy: MobileServicePolicy;
    [key: string]: unknown;
}