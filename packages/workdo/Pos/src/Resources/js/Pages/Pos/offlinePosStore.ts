export interface OfflineQueuedTransaction {
    client_tx_id: string;
    warehouse_id: number;
    customer_id: number | null;
    billing_counter_id: number | null;
    pos_date: string;
    payment_method: string;
    reference_number: string | null;
    paid_amount: number;
    total: number;
    items: Array<{
        id: number;
        quantity: number;
        price: number;
        item_discount_value: number;
        item_discount_amount: number;
    }>;
    created_at: string;
    status: 'pending' | 'synced' | 'duplicate' | 'conflict' | 'failed';
    sync_message?: string;
    conflicts?: Array<{
        product_id: number;
        product_name: string;
        requested_qty: number;
        available_stock: number;
    }>;
}

const PRODUCTS_CACHE_PREFIX = 'pos_offline_products_v1_';
const QUEUE_KEY = 'pos_offline_tx_queue_v1';

export function generateClientTxId(): string {
    const rand = typeof crypto !== 'undefined' && crypto.randomUUID
        ? crypto.randomUUID().slice(0, 8).toUpperCase()
        : Math.random().toString(36).substring(2, 10).toUpperCase();
    return `POS-OFF-${Date.now()}-${rand}`;
}

export function cacheWarehouseProducts(warehouseId: string, products: any[]): void {
    try {
        localStorage.setItem(`${PRODUCTS_CACHE_PREFIX}${warehouseId}`, JSON.stringify(products));
    } catch {
        // Ignore storage quota errors
    }
}

export function getCachedWarehouseProducts(warehouseId: string): any[] {
    try {
        const raw = localStorage.getItem(`${PRODUCTS_CACHE_PREFIX}${warehouseId}`);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

export function getOfflineQueue(): OfflineQueuedTransaction[] {
    try {
        const raw = localStorage.getItem(QUEUE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

export function saveOfflineQueue(queue: OfflineQueuedTransaction[]): void {
    try {
        localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
    } catch {
        // Ignore storage quota errors
    }
}

export function enqueueOfflineTransaction(tx: OfflineQueuedTransaction): OfflineQueuedTransaction[] {
    const current = getOfflineQueue();
    if (current.some((item) => item.client_tx_id === tx.client_tx_id)) {
        return current;
    }
    const updated = [tx, ...current];
    saveOfflineQueue(updated);
    return updated;
}

