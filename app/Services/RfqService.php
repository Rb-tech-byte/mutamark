<?php

namespace App\Services;

use App\Models\DocumentVerification;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\SupplierQuotation;
use App\Models\SupplierQuotationItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Workdo\Account\Models\Vendor;
use Workdo\ProductService\Models\ProductServiceItem;

class RfqService
{
    /**
     * Generate unique RFQ number following Appendix A standard (RFQ-YYYY-#####).
     */
    public static function generateRfqNumber(int $companyId): string
    {
        $year = date('Y');
        $prefix = "RFQ-{$year}-";

        $last = Rfq::where('created_by', $companyId)
            ->where('rfq_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last && preg_match('/RFQ-\d{4}-(\d+)/', $last->rfq_number, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        } else {
            $count = Rfq::where('created_by', $companyId)->count();
            $nextSeq = $count + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new Request for Quotation (RFQ) with line items.
     */
    public static function createRfq(array $data, int $userId, int $companyId): Rfq
    {
        return DB::transaction(function () use ($data, $userId, $companyId) {
            $rfqNumber = self::generateRfqNumber($companyId);
            $verificationHash = hash('sha256', "RFQ-{$rfqNumber}-{$companyId}-" . time());

            $rfq = Rfq::create([
                'rfq_number' => $rfqNumber,
                'title' => $data['title'],
                'requisition_id' => $data['requisition_id'] ?? null,
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'closing_date' => $data['closing_date'],
                'delivery_deadline' => $data['delivery_deadline'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'payment_terms' => $data['payment_terms'] ?? 'Net 30 Days',
                'delivery_terms' => $data['delivery_terms'] ?? 'DAP Dar es Salaam',
                'evaluation_notes' => $data['evaluation_notes'] ?? null,
                'verification_hash' => $verificationHash,
                'creator_id' => $userId,
                'created_by' => $companyId,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    RfqItem::create([
                        'rfq_id' => $rfq->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'] ?? 'Custom Procurement Item',
                        'sku' => $item['sku'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'unit' => $item['unit'] ?? 'units',
                        'target_unit_price' => $item['target_unit_price'] ?? 0,
                        'specifications' => $item['specifications'] ?? null,
                    ]);
                }
            }

            // If generated from an approved purchase requisition, update PR status to converted_to_rfq
            if (!empty($data['requisition_id'])) {
                $pr = PurchaseRequisition::find($data['requisition_id']);
                if ($pr) {
                    $pr->update(['status' => 'converted_to_rfq']);
                }
            }

            return $rfq->fresh(['items', 'requisition', 'warehouse']);
        });
    }

    /**
     * Update RFQ details.
     */
    public static function updateRfq(Rfq $rfq, array $data): Rfq
    {
        return DB::transaction(function () use ($rfq, $data) {
            $rfq->update([
                'title' => $data['title'] ?? $rfq->title,
                'closing_date' => $data['closing_date'] ?? $rfq->closing_date,
                'delivery_deadline' => $data['delivery_deadline'] ?? $rfq->delivery_deadline,
                'warehouse_id' => $data['warehouse_id'] ?? $rfq->warehouse_id,
                'payment_terms' => $data['payment_terms'] ?? $rfq->payment_terms,
                'delivery_terms' => $data['delivery_terms'] ?? $rfq->delivery_terms,
                'evaluation_notes' => $data['evaluation_notes'] ?? $rfq->evaluation_notes,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                $rfq->items()->delete();
                foreach ($data['items'] as $item) {
                    RfqItem::create([
                        'rfq_id' => $rfq->id,
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'] ?? 'Procurement Item',
                        'sku' => $item['sku'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'unit' => $item['unit'] ?? 'units',
                        'target_unit_price' => $item['target_unit_price'] ?? 0,
                        'specifications' => $item['specifications'] ?? null,
                    ]);
                }
            }

            return $rfq->fresh(['items', 'requisition', 'warehouse', 'quotations.items']);
        });
    }

    /**
     * Publish RFQ for supplier bidding.
     */
    public static function publishRfq(Rfq $rfq): Rfq
    {
        $rfq->update(['status' => 'published']);
        return $rfq->fresh(['items', 'requisition', 'warehouse']);
    }

    /**
     * Add a supplier quotation bid to an RFQ.
     */
    public static function addVendorBid(Rfq $rfq, array $data, int $userId, int $companyId): SupplierQuotation
    {
        return DB::transaction(function () use ($rfq, $data, $userId, $companyId) {
            $vendor = Vendor::find($data['vendor_id']);
            $vendorName = $vendor ? ($vendor->company_name ?? $vendor->name) : 'Supplier';

            $totalBid = 0;
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $totalBid += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
                }
            }

            $quotation = SupplierQuotation::create([
                'rfq_id' => $rfq->id,
                'vendor_id' => $data['vendor_id'],
                'vendor_name' => $vendorName,
                'quotation_reference' => $data['quotation_reference'] ?? ('QUO-' . strtoupper(substr(md5(uniqid()), 0, 8))),
                'submission_date' => $data['submission_date'] ?? now()->toDateString(),
                'valid_until' => $data['valid_until'] ?? now()->addDays(30)->toDateString(),
                'delivery_lead_time_days' => $data['delivery_lead_time_days'] ?? 7,
                'warranty_months' => $data['warranty_months'] ?? 12,
                'payment_terms' => $data['payment_terms'] ?? '30 Days Net',
                'total_bid_amount' => $totalBid,
                'status' => 'submitted',
                'notes' => $data['notes'] ?? null,
                'creator_id' => $userId,
                'created_by' => $companyId,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = $item['quantity'] ?? 1;
                    $price = $item['unit_price'] ?? 0;
                    SupplierQuotationItem::create([
                        'supplier_quotation_id' => $quotation->id,
                        'rfq_item_id' => $item['rfq_item_id'],
                        'unit_price' => $price,
                        'quantity' => $qty,
                        'total_price' => $qty * $price,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                }
            }

            // Automatically move RFQ into 'evaluating' status if it was published
            if ($rfq->status === 'published' || $rfq->status === 'draft') {
                $rfq->update(['status' => 'evaluating']);
            }

            return $quotation->fresh(['items', 'vendor']);
        });
    }

    /**
     * Update an existing supplier quotation bid.
     */
    public static function updateVendorBid(SupplierQuotation $quotation, array $data): SupplierQuotation
    {
        return DB::transaction(function () use ($quotation, $data) {
            $totalBid = 0;
            if (isset($data['items']) && is_array($data['items'])) {
                $quotation->items()->delete();
                foreach ($data['items'] as $item) {
                    $qty = $item['quantity'] ?? 1;
                    $price = $item['unit_price'] ?? 0;
                    $total = $qty * $price;
                    $totalBid += $total;

                    SupplierQuotationItem::create([
                        'supplier_quotation_id' => $quotation->id,
                        'rfq_item_id' => $item['rfq_item_id'],
                        'unit_price' => $price,
                        'quantity' => $qty,
                        'total_price' => $total,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                }
            } else {
                $totalBid = $quotation->total_bid_amount;
            }

            $quotation->update([
                'quotation_reference' => $data['quotation_reference'] ?? $quotation->quotation_reference,
                'delivery_lead_time_days' => $data['delivery_lead_time_days'] ?? $quotation->delivery_lead_time_days,
                'warranty_months' => $data['warranty_months'] ?? $quotation->warranty_months,
                'payment_terms' => $data['payment_terms'] ?? $quotation->payment_terms,
                'total_bid_amount' => $totalBid,
                'notes' => $data['notes'] ?? $quotation->notes,
            ]);

            return $quotation->fresh(['items', 'vendor']);
        });
    }

    /**
     * Delete supplier quotation.
     */
    public static function deleteVendorBid(SupplierQuotation $quotation): bool
    {
        return $quotation->delete();
    }

    /**
     * Award winning bid, reject competing bids, and auto-generate Purchase Bill / PO.
     */
    public static function awardBidAndGeneratePo(Rfq $rfq, int $quotationId, int $userId): array
    {
        CompanyAccess::check($rfq); CompanyAccess::requirePermission('manage-purchase-invoices');
        return DB::transaction(function () use ($rfq, $quotationId, $userId) {
            $rfq = Rfq::where('created_by', creatorId())->lockForUpdate()->findOrFail($rfq->id);
            if ($rfq->status === 'awarded') return ['rfq' => $rfq, 'purchase_order' => \App\Models\PurchaseOrder::where('created_by', creatorId())->findOrFail($rfq->awarded_po_id)];
            abort_unless(in_array($rfq->status, ['published','evaluating']), 409, 'Only active RFQs can be awarded.');
            $bid = SupplierQuotation::where('created_by', creatorId())->where('rfq_id', $rfq->id)->with('items.rfqItem')->findOrFail($quotationId);
            $items = $bid->items->map(function ($line) { abort_unless($line->rfqItem?->product_id, 422, 'Map every RFQ line to a product before award.'); return ['product_id' => $line->rfqItem->product_id, 'quantity' => $line->quantity, 'unit_price' => $line->unit_price]; })->all();
            $order = ThreeWayMatchService::createPurchaseOrder(['vendor_id' => $bid->vendor_id, 'warehouse_id' => $rfq->warehouse_id, 'requisition_id' => $rfq->requisition_id, 'rfq_id' => $rfq->id, 'payment_terms' => $bid->payment_terms, 'items' => $items]);
            $bid->update(['status' => 'awarded']); SupplierQuotation::where('rfq_id', $rfq->id)->where('id','!=',$bid->id)->update(['status' => 'rejected']);
            $rfq->update(['status' => 'awarded', 'awarded_vendor_id' => $bid->vendor_id, 'awarded_bid_id' => $bid->id, 'awarded_po_id' => $order->id]);
            return ['rfq' => $rfq->fresh(['awardedPo']), 'purchase_order' => $order];
        }, 3);
    }

    /**
     * Delete RFQ.
     */
    public static function deleteRfq(Rfq $rfq): bool
    {
        return DB::transaction(function () use ($rfq) {
            $rfq->quotations()->delete();
            $rfq->items()->delete();
            return $rfq->delete();
        });
    }
}
