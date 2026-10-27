<?php

namespace App\Services;

use App\Models\DocumentVerification;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseInvoice;
use App\Models\ThreeWayMatchAuthorization;
use App\Models\ThreeWayMatchConfig;
use Illuminate\Support\Facades\DB;

class ThreeWayMatchService
{
    public static function createPurchaseOrder(array $data): \App\Models\PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $vendor = \Workdo\Account\Models\Vendor::where('created_by', creatorId())->findOrFail($data['vendor_id']);
            abort_unless($vendor->user_id, 422, 'The supplier must have a linked user account.');
            \App\Models\Warehouse::where('created_by', creatorId())->findOrFail($data['warehouse_id']);
            $order = \App\Models\PurchaseOrder::create([
                'po_number' => DocumentNumberService::next('PO'), 'po_date' => now()->toDateString(), 'vendor_id' => $vendor->id,
                'warehouse_id' => $data['warehouse_id'], 'requisition_id' => $data['requisition_id'] ?? null, 'rfq_id' => $data['rfq_id'] ?? null,
                'currency' => 'TZS', 'status' => 'draft', 'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null, 'creator_id' => auth()->id(), 'created_by' => creatorId(),
            ]);
            $subtotal = $tax = 0;
            foreach ($data['items'] as $line) {
                $product = \Workdo\ProductService\Models\ProductServiceItem::where('created_by', creatorId())->findOrFail($line['product_id']);
                $taxIds = is_array($product->tax_ids) ? $product->tax_ids : (json_decode($product->tax_ids ?? '[]', true) ?: []);
                $rate = (float) \Workdo\ProductService\Models\ProductServiceTax::where('created_by', creatorId())->whereIn('id', $taxIds)->sum('rate');
                $base = round((float) $line['quantity'] * (float) $line['unit_price'], 2); $lineTax = round($base * $rate / 100, 2);
                abort_if((float) $line['quantity'] <= 0 || (float) $line['unit_price'] < 0, 422, 'Invalid purchase order quantities or prices.');
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $line['quantity'], 'unit_price' => $line['unit_price'], 'tax_rate' => $rate, 'tax_amount' => $lineTax, 'total_amount' => $base + $lineTax]);
                $subtotal += $base; $tax += $lineTax;
            }
            $order->update(['subtotal' => $subtotal, 'tax_amount' => $tax, 'discount_amount' => 0, 'total_amount' => $subtotal + $tax]);
            return $order->load('items', 'vendor');
        }, 3);
    }

    public static function invoicePurchaseOrder(\App\Models\PurchaseOrder $order): PurchaseInvoice
    {
        CompanyAccess::requirePermission('create-purchase-invoices'); CompanyAccess::check($order);
        return DB::transaction(function () use ($order) {
            $order = \App\Models\PurchaseOrder::where('created_by', creatorId())->lockForUpdate()->findOrFail($order->id);
            abort_unless(in_array($order->status, ['issued','partially_received','received']), 409, 'Issue the purchase order before invoicing.');
            if ($invoice = PurchaseInvoice::where('created_by', creatorId())->where('purchase_order_id', $order->id)->first()) return $invoice;
            $invoice = PurchaseInvoice::create(['purchase_order_id' => $order->id, 'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(30)->toDateString(), 'vendor_id' => $order->vendor->user_id, 'warehouse_id' => $order->warehouse_id, 'status' => 'draft', 'subtotal' => $order->subtotal, 'tax_amount' => $order->tax_amount, 'total_amount' => $order->total_amount, 'balance_amount' => $order->total_amount, 'paid_amount' => 0, 'creator_id' => auth()->id(), 'created_by' => creatorId(), 'notes' => $order->po_number]);
            foreach ($order->items as $line) $invoice->items()->create(['product_id' => $line->product_id, 'quantity' => $line->quantity, 'unit_price' => $line->unit_price, 'discount_percentage' => 0, 'tax_percentage' => $line->tax_rate]);
            GoodsReceivedNote::where('created_by', creatorId())->where('purchase_order_id', $order->id)->update(['purchase_invoice_id' => $invoice->id]);
            return $invoice->load('items');
        }, 3);
    }

    public static function assertInvoicePayable(PurchaseInvoice $invoice): void
    {
        CompanyAccess::check($invoice);
        $order = \App\Models\PurchaseOrder::where('created_by', creatorId())->lockForUpdate()->find($invoice->purchase_order_id);
        $fail = fn () => throw \Illuminate\Validation\ValidationException::withMessages(['invoice' => 'Supplier payment is held until PO, accepted receipts and invoice quantities/prices match.']);
        if (!$order || !in_array($order->status, ['issued','partially_received','received']) || (int) $order->vendor->user_id !== (int) $invoice->vendor_id) $fail();
        $grns = GoodsReceivedNote::where('created_by', creatorId())->where('purchase_order_id', $order->id)->where('status', 'posted')->with('items')->lockForUpdate()->get();
        if ($grns->isEmpty() || ThreeWayMatchAuthorization::whereIn('grn_id', $grns->pluck('id'))->where('authorization_status','disputed')->exists()) $fail();
        $config = ThreeWayMatchConfig::getForCompany(creatorId());
        if ($config->require_qc_passed && $grns->contains(fn ($grn) => $grn->inspection_status !== 'inspected_passed')) $fail();
        $billedProducts = $invoice->items->pluck('product_id')->unique()->sort()->values()->all();
        if ($billedProducts !== $order->items->pluck('product_id')->unique()->sort()->values()->all()) $fail();
        foreach ($order->items->groupBy('product_id') as $productId => $lines) {
            $ordered = (float) $lines->sum('quantity');
            $billed = (float) $invoice->items->where('product_id',$productId)->sum('quantity');
            $received = (float) $grns->flatMap->items->where('product_id',$productId)->sum('accepted_quantity');
            $qtyTolerance = $ordered * (float) $config->qty_tolerance_percent / 100;
            if (abs($billed - $ordered) > $qtyTolerance + 0.0001 || abs($received - $billed) > $qtyTolerance + 0.0001) $fail();
            $poPrice = (float) $lines->first()->unit_price;
            foreach ($invoice->items->where('product_id',$productId) as $line) if (abs((float) $line->unit_price - $poPrice) > $poPrice * (float) $config->price_tolerance_percent / 100 + 0.01) $fail();
        }
    }

    /**
     * Audit 3-Way Match between PO, GRN, and Supplier Invoice with tolerance evaluation.
     */
    public static function auditGrnMatch(GoodsReceivedNote $grn, ?ThreeWayMatchConfig $config = null): array
    {
        if (!$config) {
            $config = ThreeWayMatchConfig::getForCompany($grn->created_by);
        }

        $hasInvoice = !empty($grn->purchase_invoice_id) && $grn->purchaseInvoice;
        $itemsAnalysis = [];
        $totalQtyVariance = 0;
        $totalPriceVariance = 0;
        $totalVarianceAmountTzs = 0;
        $exceedsTolerance = false;
        $toleranceReasons = [];

        // Check Quality Inspection requirement
        $qcPassed = $grn->inspection_status === 'inspected_passed';
        if ($config->require_qc_passed && !$qcPassed) {
            $exceedsTolerance = true;
            $toleranceReasons[] = "Quality inspection status is '{$grn->inspection_status}' (passed inspection required).";
        }

        foreach ($grn->items as $item) {
            $orderedQty = (float) $item->ordered_quantity;
            $receivedQty = (float) $item->received_quantity;
            $acceptedQty = (float) $item->accepted_quantity;
            $poPrice = (float) $item->unit_price;

            $billedQty = 0;
            $billedPrice = 0;
            $billedTotal = 0;

            if ($hasInvoice) {
                $invItem = $grn->purchaseInvoice->items
                    ->where('product_id', $item->product_id)
                    ->first();
                if ($invItem) {
                    $billedQty = (float) $invItem->quantity;
                    $billedPrice = (float) $invItem->unit_price;
                    $billedTotal = (float) $invItem->total_amount;
                }
            }

            // Variances between Delivered/Accepted and Billed
            $qtyDiff = $hasInvoice ? abs($acceptedQty - $billedQty) : 0;
            $priceDiff = $hasInvoice ? abs($poPrice - $billedPrice) : 0;
            $amountDiff = $hasInvoice ? abs(($acceptedQty * $poPrice) - ($billedQty * $billedPrice)) : 0;

            // Tolerance checks
            $qtyToleranceAllowed = $orderedQty * ((float) $config->qty_tolerance_percent / 100.0);
            $priceToleranceAllowed = $poPrice * ((float) $config->price_tolerance_percent / 100.0);

            $isQtyWithinTolerance = $qtyDiff <= ($qtyToleranceAllowed + 0.001);
            $isPriceWithinTolerance = $priceDiff <= ($priceToleranceAllowed + 0.001);

            if (!$isQtyWithinTolerance) {
                $exceedsTolerance = true;
                $toleranceReasons[] = "Line '{$item->product?->name}': Quantity variance of {$qtyDiff} exceeds allowed {$config->qty_tolerance_percent}% tolerance.";
            }

            if (!$isPriceWithinTolerance) {
                $exceedsTolerance = true;
                $toleranceReasons[] = "Line '{$item->product?->name}': Unit price variance of {$priceDiff} TZS exceeds allowed {$config->price_tolerance_percent}% tolerance.";
            }

            $totalQtyVariance += $qtyDiff;
            $totalPriceVariance += $priceDiff;
            $totalVarianceAmountTzs += $amountDiff;

            $itemsAnalysis[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product ? $item->product->name : 'Item',
                'sku' => $item->product ? $item->product->sku : 'N/A',
                'po_qty' => $orderedQty,
                'grn_qty' => $acceptedQty,
                'billed_qty' => $billedQty,
                'po_price' => $poPrice,
                'billed_price' => $billedPrice,
                'qty_variance' => $qtyDiff,
                'price_variance' => $priceDiff,
                'amount_variance' => $amountDiff,
                'is_matched' => $isQtyWithinTolerance && $isPriceWithinTolerance,
            ];
        }

        if ($totalVarianceAmountTzs > ((float)$config->max_variance_amount_tzs + 0.01) && (float)$config->max_variance_amount_tzs > 0) {
            $exceedsTolerance = true;
            $toleranceReasons[] = "Total variance amount of " . number_format($totalVarianceAmountTzs, 2) . " TZS exceeds maximum allowed tolerance of " . number_format($config->max_variance_amount_tzs, 2) . " TZS.";
        }

        // Determine Match Status
        if (!$hasInvoice) {
            $computedStatus = 'pending_invoice';
        } elseif ($exceedsTolerance) {
            $computedStatus = 'variance_hold';
        } else {
            $computedStatus = 'matched_cleared';
        }

        // Auto sync authorization record
        $auth = ThreeWayMatchAuthorization::firstOrCreate(
            ['grn_id' => $grn->id],
            [
                'purchase_invoice_id' => $grn->purchase_invoice_id,
                'authorization_status' => $computedStatus === 'matched_cleared' ? 'cleared' : 'pending',
                'qty_variance_detected' => $totalQtyVariance,
                'price_variance_detected' => $totalPriceVariance,
                'total_variance_amount_tzs' => $totalVarianceAmountTzs,
                'disbursement_cleared' => $computedStatus === 'matched_cleared',
                'cleared_at' => $computedStatus === 'matched_cleared' ? now() : null,
                'cleared_by' => $computedStatus === 'matched_cleared' ? $grn->created_by : null,
                'clearance_notes' => $computedStatus === 'matched_cleared' ? 'Auto-cleared: within tolerance thresholds.' : null,
                'created_by' => $grn->created_by,
            ]
        );

        if (!$auth->wasRecentlyCreated && $auth->authorization_status !== 'disputed') {
            $auth->update([
                'purchase_invoice_id' => $grn->purchase_invoice_id,
                'qty_variance_detected' => $totalQtyVariance,
                'price_variance_detected' => $totalPriceVariance,
                'total_variance_amount_tzs' => $totalVarianceAmountTzs,
                'authorization_status' => $computedStatus === 'matched_cleared' ? 'cleared' : 'pending',
                'disbursement_cleared' => $computedStatus === 'matched_cleared',
                'cleared_at' => $computedStatus === 'matched_cleared' ? now() : null,
            ]);
        }
        return [
            'grn' => $grn,
            'has_invoice' => $hasInvoice,
            'match_status' => $computedStatus,
            'qc_passed' => $qcPassed,
            'exceeds_tolerance' => $exceedsTolerance,
            'tolerance_reasons' => $toleranceReasons,
            'total_qty_variance' => $totalQtyVariance,
            'total_price_variance' => $totalPriceVariance,
            'total_variance_amount_tzs' => $totalVarianceAmountTzs,
            'authorization' => $auth,
            'items' => $itemsAnalysis,
        ];
    }

    /**
     * Authorize Accounts Payable disbursement release.
     */
    public static function authorizeDisbursement(int $authId, int $userId, string $notes): ThreeWayMatchAuthorization
    {
        return DB::transaction(function () use ($authId, $userId, $notes) {
            CompanyAccess::requirePermission('manage-account');
            $auth = ThreeWayMatchAuthorization::where('created_by', creatorId())->with(['grn.warehouse', 'grn.vendor', 'purchaseInvoice'])->lockForUpdate()->findOrFail($authId);
            $audit = self::auditGrnMatch($auth->grn);
            abort_unless($auth->grn->status === 'posted' && $audit['match_status'] === 'matched_cleared', 422, 'Resolve matching and quality variances before authorization.');
            $verificationHash = hash('sha256', "3WAY-AUTH-{$auth->id}-{$auth->grn_id}-{$userId}-" . time());

            $auth->update([
                'authorization_status' => 'cleared',
                'disbursement_cleared' => true,
                'cleared_by' => $userId,
                'cleared_at' => now(),
                'clearance_notes' => $notes,
                'verification_hash' => $verificationHash,
            ]);

            // Register Digital Verification Seal
            try {
                DocumentVerification::updateOrCreate(
                    [
                        'document_type' => 'three_way_match_clearance',
                        'document_id' => $auth->id,
                    ],
                    [
                        'document_number' => '3WAY-' . $auth->grn->grn_number,
                        'verification_hash' => $verificationHash,
                        'qr_payload' => url('/verify/document/' . $verificationHash),
                        'issuer_name' => 'Mutamark Enterprises Ltd - Internal Audit',
                        'party_name' => $auth->grn->vendor?->company_name ?: 'Vendor',
                        'total_amount' => $auth->grn->total_amount,
                        'currency' => 'TZS',
                        'issue_date' => now()->toDateString(),
                        'status' => 'cleared',
                        'signatory_name' => 'Internal Audit & Accounts Payable Lead',
                        'signatory_title' => 'Chief Financial Officer',
                        'verified_at' => now(),
                        'created_by' => $auth->created_by,
                    ]
                );
            } catch (\Exception $e) {
                // Ignore verification sync exceptions
            }

            return $auth->fresh(['grn.vendor', 'grn.warehouse', 'purchaseInvoice', 'clearedByUser']);
        });
    }

    /**
     * Flag discrepancy and place disbursement on dispute hold.
     */
    public static function flagDiscrepancy(int $authId, string $notes): ThreeWayMatchAuthorization
    {
        CompanyAccess::requirePermission('manage-account');
        $auth = ThreeWayMatchAuthorization::where('created_by', creatorId())->findOrFail($authId);
        $auth->update([
            'authorization_status' => 'disputed',
            'disbursement_cleared' => false,
            'clearance_notes' => $notes,
        ]);

        return $auth->fresh(['grn.vendor', 'grn.warehouse', 'purchaseInvoice']);
    }

    /**
     * Link an existing Supplier Bill / Invoice to a GRN.
     */
    public static function linkInvoiceToGrn(int $grnId, int $invoiceId, int $companyId): array
    {
        $grn = GoodsReceivedNote::where('created_by', $companyId)->findOrFail($grnId);
        $invoice = PurchaseInvoice::where('created_by', $companyId)->findOrFail($invoiceId);
        $vendor = \Workdo\Account\Models\Vendor::where('created_by', $companyId)->findOrFail($grn->vendor_id);
        abort_unless((int) $vendor->user_id === (int) $invoice->vendor_id && (int) $grn->warehouse_id === (int) $invoice->warehouse_id, 422, 'Supplier and warehouse must match the invoice.');

        $grn->update(['purchase_invoice_id' => $invoice->id]);

        $auth = ThreeWayMatchAuthorization::where('grn_id', $grn->id)->first();
        if ($auth) {
            $auth->update(['purchase_invoice_id' => $invoice->id]);
        }

        return self::auditGrnMatch($grn->fresh(['items.product', 'purchaseInvoice.items', 'vendor', 'warehouse']));
    }
}
