<?php

namespace Workdo\Pos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Workdo\Pos\Events\CreatePos;
use Workdo\Pos\Models\Pos;
use Workdo\Pos\Models\PosBillingCounter;
use Workdo\Pos\Models\PosItem;
use Workdo\Pos\Models\PosOfflineSyncLog;
use Workdo\Pos\Models\PosPayment;
use Workdo\ProductService\Models\ProductServiceItem;
use Workdo\ProductService\Models\ProductServiceTax;
use Workdo\ProductService\Models\WarehouseStock;

class PosOfflineSyncController extends Controller
{
    public function logs(Request $request)
    {
        if (!Auth::user()->can('create-pos')) {
            return response()->json(['message' => __('Permission denied')], 403);
        }

        $logs = PosOfflineSyncLog::where('created_by', creatorId())
            ->latest()
            ->limit(50)
            ->get();

        return response()->json(['logs' => $logs]);
    }

    public function syncBatch(Request $request)
    {
        if (!Auth::user()->can('create-pos')) {
            return response()->json(['message' => __('Permission denied')], 403);
        }

        $validated = $request->validate([
            'transactions' => 'required|array|min:1|max:100',
            'transactions.*.client_tx_id' => 'required|string|max:100',
            'transactions.*.warehouse_id' => 'required|integer',
            'transactions.*.customer_id' => 'nullable|integer',
            'transactions.*.billing_counter_id' => 'nullable|integer',
            'transactions.*.pos_date' => 'nullable|date',
            'transactions.*.payment_method' => 'nullable|string|max:50',
            'transactions.*.reference_number' => 'nullable|string|max:120',
            'transactions.*.paid_amount' => 'nullable|numeric|min:0',
            'transactions.*.items' => 'required|array|min:1',
            'transactions.*.items.*.id' => 'required|integer',
            'transactions.*.items.*.quantity' => 'required|numeric|min:0.01',
            'transactions.*.items.*.price' => 'required|numeric|min:0',
            'transactions.*.items.*.item_discount_value' => 'nullable|numeric|min:0',
            'transactions.*.items.*.item_discount_amount' => 'nullable|numeric|min:0',
        ]);

        $creatorId = creatorId();
        $userId = Auth::id();
        $results = [];

        foreach ($validated['transactions'] as $tx) {
            $clientTxId = $tx['client_tx_id'];

            // 1. Idempotency check: prevent duplicate sales & payments
            $existingSale = Pos::where('client_tx_id', $clientTxId)->first();
            if ($existingSale) {
                $log = PosOfflineSyncLog::create([
                    'client_tx_id' => $clientTxId,
                    'pos_id' => $existingSale->id,
                    'warehouse_id' => $tx['warehouse_id'],
                    'status' => 'duplicate',
                    'message' => __('Duplicate transaction skipped. Already synced as :num.', ['num' => $existingSale->sale_number]),
                    'payload' => $tx,
                    'synced_at' => now(),
                    'creator_id' => $userId,
                    'created_by' => $creatorId,
                ]);
                $results[] = [
                    'client_tx_id' => $clientTxId,
                    'status' => 'duplicate',
                    'sale_number' => $existingSale->sale_number,
                    'message' => $log->message,
                ];
                continue;
            }

            // 2. Inventory conflict check: never silently overwrite server-side stock
            $conflicts = [];
            foreach ($tx['items'] as $item) {
                $stock = WarehouseStock::where('warehouse_id', $tx['warehouse_id'])
                    ->where('product_id', $item['id'])
                    ->first();
                $available = $stock ? (float) $stock->quantity : 0;
                $requested = (float) $item['quantity'];

                if ($available < $requested) {
                    $productName = ProductServiceItem::where('id', $item['id'])->value('name') ?? ('Product #' . $item['id']);
                    $conflicts[] = [
                        'product_id' => $item['id'],
                        'product_name' => $productName,
                        'requested_qty' => $requested,
                        'available_stock' => $available,
                    ];
                }
            }

            if (!empty($conflicts)) {
                $log = PosOfflineSyncLog::create([
                    'client_tx_id' => $clientTxId,
                    'warehouse_id' => $tx['warehouse_id'],
                    'status' => 'conflict',
                    'message' => __('Inventory conflict detected. Server stock is lower than offline requested quantity.'),
                    'payload' => $tx,
                    'conflict_details' => $conflicts,
                    'synced_at' => now(),
                    'creator_id' => $userId,
                    'created_by' => $creatorId,
                ]);
                $results[] = [
                    'client_tx_id' => $clientTxId,
                    'status' => 'conflict',
                    'message' => $log->message,
                    'conflicts' => $conflicts,
                ];
                continue;
            }

            // 3. Atomic commit for valid offline transaction
            try {
                $sale = DB::transaction(function () use ($tx, $clientTxId, $creatorId, $userId, $request) {
                    $bankAccountId = null;
                    if (!empty($tx['billing_counter_id'])) {
                        $counter = PosBillingCounter::where('created_by', $creatorId)->find($tx['billing_counter_id']);
                        $bankAccountId = $counter?->bank_account_id;
                    }

                    $sale = new Pos();
                    $sale->sale_number = Pos::generateSaleNumber();
                    $sale->client_tx_id = $clientTxId;
                    $sale->customer_id = $tx['customer_id'] ?? null;
                    $sale->warehouse_id = $tx['warehouse_id'];
                    $sale->billing_counter_id = $tx['billing_counter_id'] ?? null;
                    $sale->bank_account_id = $bankAccountId;
                    $sale->pos_date = $tx['pos_date'] ?? now()->toDateString();
                    $sale->creator_id = $userId;
                    $sale->created_by = $creatorId;
                    $sale->save();

                    $finalAmount = 0;
                    $totalDiscount = 0;

                    foreach ($tx['items'] as $item) {
                        $product = ProductServiceItem::where('created_by', $creatorId)->findOrFail($item['id']);
                        $subtotal = $item['quantity'] * $item['price'];

                        $taxAmount = 0;
                        $taxIds = null;
                        if ($product->tax_ids && is_array($product->tax_ids) && !empty($product->tax_ids)) {
                            $taxIds = $product->tax_ids;
                            $taxes = ProductServiceTax::whereIn('id', $taxIds)->where('created_by', $creatorId)->get();
                            $discountedSubtotal = $subtotal - ($item['item_discount_amount'] ?? 0);
                            foreach ($taxes as $tax) {
                                $taxAmount += $discountedSubtotal * ($tax->rate / 100);
                            }
                        }

                        $itemDiscountAmount = $item['item_discount_amount'] ?? 0;
                        $totalDiscount += $itemDiscountAmount;
                        $totalAmount = ($subtotal - $itemDiscountAmount) + $taxAmount;
                        $finalAmount += $totalAmount;

                        $saleItem = new PosItem();
                        $saleItem->pos_id = $sale->id;
                        $saleItem->product_id = $item['id'];
                        $saleItem->quantity = $item['quantity'];
                        $saleItem->price = $item['price'];
                        $saleItem->tax_ids = $taxIds;
                        $saleItem->subtotal = $subtotal;
                        $saleItem->tax_amount = $taxAmount;
                        $saleItem->total_amount = $totalAmount;
                        $saleItem->item_discount_value = $item['item_discount_value'] ?? 0;
                        $saleItem->item_discount_amount = $itemDiscountAmount;
                        $saleItem->creator_id = $userId;
                        $saleItem->created_by = $creatorId;
                        $saleItem->save();
                    }

                    $paidAmount = isset($tx['paid_amount']) ? (float) $tx['paid_amount'] : $finalAmount;
                    $posPayment = new PosPayment();
                    $posPayment->pos_id = $sale->id;
                    $posPayment->discount = $totalDiscount;
                    $posPayment->amount = $finalAmount;
                    $posPayment->discount_amount = $finalAmount;
                    $posPayment->payment_method = $tx['payment_method'] ?? 'cash';
                    $posPayment->reference_number = $tx['reference_number'] ?? $clientTxId;
                    $posPayment->paid_amount = $paidAmount;
                    $posPayment->change_amount = max(0, $paidAmount - $finalAmount);
                    $posPayment->creator_id = $userId;
                    $posPayment->created_by = $creatorId;
                    $posPayment->save();

                    try {
                        CreatePos::dispatch($request, $sale);
                    } catch (\Throwable $e) {
                        \Log::warning('Offline POS CreatePos dispatch warning: ' . $e->getMessage());
                    }

                    return $sale;
                });

                PosOfflineSyncLog::create([
                    'client_tx_id' => $clientTxId,
                    'pos_id' => $sale->id,
                    'warehouse_id' => $tx['warehouse_id'],
                    'status' => 'synced',
                    'message' => __('Synced successfully as :num.', ['num' => $sale->sale_number]),
                    'payload' => $tx,
                    'synced_at' => now(),
                    'creator_id' => $userId,
                    'created_by' => $creatorId,
                ]);

                $results[] = [
                    'client_tx_id' => $clientTxId,
                    'status' => 'synced',
                    'sale_number' => $sale->sale_number,
                    'message' => __('Synced successfully as :num.', ['num' => $sale->sale_number]),
                ];
            } catch (\Throwable $e) {
                PosOfflineSyncLog::create([
                    'client_tx_id' => $clientTxId,
                    'warehouse_id' => $tx['warehouse_id'],
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                    'payload' => $tx,
                    'synced_at' => now(),
                    'creator_id' => $userId,
                    'created_by' => $creatorId,
                ]);

                $results[] = [
                    'client_tx_id' => $clientTxId,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}

