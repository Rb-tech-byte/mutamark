import { PaginatedData, ModalState, AuthContext, CreateProps } from '@/types/common';

export interface TransferItem {
    id: number;
    transfer_id: number;
    product_id: number;
    product?: {
        id: number;
        name: string;
        sku: string;
        purchase_price?: number;
    };
    requested_quantity: number | string;
    dispatched_quantity: number | string;
    received_quantity?: number | string | null;
    discrepancy_quantity?: number | string;
    from_bin_id?: number | null;
    to_bin_id?: number | null;
    from_bin?: {
        id: number;
        bin_code: string;
        zone: string;
    };
    to_bin?: {
        id: number;
        bin_code: string;
        zone: string;
    };
    unit_cost: number | string;
    notes?: string;
}

export interface Transfer {
    id: number;
    transfer_number?: string;
    from_warehouse: {
        id: number;
        name: string;
        code?: string;
        city?: string;
    };
    to_warehouse: {
        id: number;
        name: string;
        code?: string;
        city?: string;
    };
    product?: {
        id: number;
        name: string;
        sku: string;
    };
    quantity: string | number;
    date: string;
    status: 'draft' | 'in_transit' | 'received' | 'completed' | 'cancelled';
    driver_name?: string;
    vehicle_number?: string;
    shipping_carrier?: string;
    expected_delivery_date?: string;
    dispatched_at?: string;
    received_at?: string;
    transit_notes?: string;
    inspection_notes?: string;
    total_items_count?: number;
    total_transferred_qty?: string | number;
    items?: TransferItem[];
    receiver?: {
        id: number;
        name: string;
    };
    creator?: {
        id: number;
        name: string;
    };
    verification?: {
        id: number;
        verification_hash: string;
        qr_code_payload: string;
    };
    created_at: string;
}

export interface WarehouseLocation {
    id: number;
    warehouse_id: number;
    zone: string;
    bin_code: string;
}

export interface Warehouse {
    id: number;
    name: string;
    code?: string;
    city?: string;
    warehouse_type?: string;
    locations?: WarehouseLocation[];
}

export interface Product {
    id: number;
    name: string;
    sku: string;
    purchase_price?: number;
}

export interface CreateTransferFormData {
    from_warehouse: string;
    to_warehouse: string;
    product_id: string;
    quantity: string;
    date: string;
    expected_delivery_date: string;
    driver_name: string;
    vehicle_number: string;
    shipping_carrier: string;
    transit_notes: string;
    initial_status: 'draft' | 'in_transit' | 'completed';
    items: Array<{
        product_id: string;
        quantity: string;
        from_bin_id?: string;
        to_bin_id?: string;
        notes?: string;
    }>;
}

export interface CreateTransferProps extends CreateProps {}

export interface TransferFilters {
    product_name: string;
    from_warehouse: string;
    to_warehouse?: string;
    status?: string;
    search?: string;
}

export type PaginatedTransfers = PaginatedData<Transfer>;
export type TransferModalState = ModalState<Transfer>;

export interface WarehouseStock {
    id: number;
    product_id: number;
    warehouse_id: number;
    quantity: string;
    product: Product;
}

export interface TransfersIndexProps {
    transfers: PaginatedTransfers;
    warehouses: Warehouse[];
    products: Product[];
    warehouseStocks?: WarehouseStock[];
    statistics?: {
        total_transfers: number;
        in_transit: number;
        received: number;
        draft: number;
        total_quantity_moved: number;
        active_warehouses_count: number;
    };
    auth: AuthContext;
    [key: string]: any;
}