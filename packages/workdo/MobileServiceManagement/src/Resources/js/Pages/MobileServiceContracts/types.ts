import { PaginatedData, ModalState, AuthContext } from '@/types/common';



export interface MobileServiceContract {
    id: number;
    customer_name: string;
    contract_type: string;
    start_date: string;
    end_date: string;
    contract_details?: string;
    created_at: string;
}

export interface CreateMobileServiceContractFormData {
    customer_name: string;
    contract_type: string;
    start_date: string;
    end_date: string;
    contract_details: string;
}

export interface EditMobileServiceContractFormData {
    customer_name: string;
    contract_type: string;
    start_date: string;
    end_date: string;
    contract_details: string;
}

export interface MobileServiceContractFilters {
    customer_name: string;
    contract_details: string;
    contract_type: string;
}

export type PaginatedMobileServiceContracts = PaginatedData<MobileServiceContract>;
export type MobileServiceContractModalState = ModalState<MobileServiceContract>;

export interface MobileServiceContractsIndexProps {
    mobileservicecontracts: PaginatedMobileServiceContracts;
    auth: AuthContext;
    [key: string]: unknown;
}

export interface CreateMobileServiceContractProps {
    onSuccess: () => void;
}

export interface EditMobileServiceContractProps {
    mobileservicecontract: MobileServiceContract;
    onSuccess: () => void;
}

export interface MobileServiceContractShowProps {
    mobileservicecontract: MobileServiceContract;
    [key: string]: unknown;
}