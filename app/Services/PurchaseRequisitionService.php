<?php

namespace App\Services;

use App\Models\DocumentVerification;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseRequisitionService
{
    /**
     * Generate sequential Purchase Requisition number adhering to Appendix A (PR-YYYY-#####).
     */
    public static function generateRequisitionNumber(int $creatorId): string
    {
        $year = date('Y');
        $lastReq = PurchaseRequisition::where('created_by', $creatorId)
            ->where('requisition_number', 'like', "PR-{$year}-%")
            ->latest('id')
            ->first();

        $nextNumber = 1;
        if ($lastReq && preg_match('/PR-\d{4}-(\d+)/', $lastReq->requisition_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        return sprintf('PR-%s-%05d', $year, $nextNumber);
    }

    /**
     * Create a new Purchase Requisition (CUD: Create).
     */
    public static function createRequisition(array $data, int $creatorId, int $userId): PurchaseRequisition
    {
        return DB::transaction(function () use ($data, $creatorId, $userId) {
            $reqNumber = !empty($data['requisition_number'])
                ? $data['requisition_number']
                : self::generateRequisitionNumber($creatorId);

            $estimatedTotal = 0.00;
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = (float)($item['quantity_requested'] ?? $item['quantity'] ?? 1);
                    $unitCost = (float)($item['estimated_unit_cost'] ?? $item['estimated_unit_price'] ?? 0);
                    $estimatedTotal += ($qty * $unitCost);
                }
            }

            $requisition = PurchaseRequisition::create([
                'requisition_number' => $reqNumber,
                'requisition_date' => $data['requisition_date'] ?? now()->toDateString(),
                'required_by_date' => $data['required_by_date'] ?? null,
                'requester_id' => $data['requester_id'] ?? $userId,
                'department_name' => $data['department'] ?? $data['department_name'] ?? 'Operations',
                'warehouse_id' => $data['warehouse_id'],
                'preferred_vendor_id' => !empty($data['preferred_vendor_id']) ? $data['preferred_vendor_id'] : null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => $data['status'] ?? 'draft',
                'purpose' => $data['justification'] ?? $data['purpose'] ?? null,
                'budget_reference' => $data['budget_reference'] ?? ('BUD-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6))),
                'total_estimated_amount' => $estimatedTotal,
                'creator_id' => $userId,
                'created_by' => $creatorId,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = (float)($item['quantity_requested'] ?? $item['quantity'] ?? 1);
                    $unitCost = (float)($item['estimated_unit_cost'] ?? $item['estimated_unit_price'] ?? 0);
                    $lineTotal = $qty * $unitCost;

                    PurchaseRequisitionItem::create([
                        'requisition_id' => $requisition->id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'] ?? 'Requested Material',
                        'sku' => $item['sku'] ?? null,
                        'quantity' => $qty,
                        'estimated_unit_price' => $unitCost,
                        'estimated_total_price' => $lineTotal,
                        'unit' => $item['unit'] ?? 'units',
                        'description' => $item['specifications'] ?? $item['description'] ?? null,
                    ]);
                }
            }

            return $requisition->fresh(['items', 'requester', 'warehouse', 'preferredVendor']);
        });
    }

    /**
     * Update an existing Purchase Requisition (CUD: Update).
     */
    public static function updateRequisition(PurchaseRequisition $pr, array $data): PurchaseRequisition
    {
        return DB::transaction(function () use ($pr, $data) {
            $estimatedTotal = $pr->total_estimated_amount;

            if (isset($data['items']) && is_array($data['items'])) {
                $estimatedTotal = 0.00;
                $pr->items()->delete();
                foreach ($data['items'] as $item) {
                    $qty = (float)($item['quantity_requested'] ?? $item['quantity'] ?? 1);
                    $unitCost = (float)($item['estimated_unit_cost'] ?? $item['estimated_unit_price'] ?? 0);
                    $lineTotal = $qty * $unitCost;
                    $estimatedTotal += $lineTotal;

                    PurchaseRequisitionItem::create([
                        'requisition_id' => $pr->id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'] ?? 'Requested Material',
                        'sku' => $item['sku'] ?? null,
                        'quantity' => $qty,
                        'estimated_unit_price' => $unitCost,
                        'estimated_total_price' => $lineTotal,
                        'unit' => $item['unit'] ?? 'units',
                        'description' => $item['specifications'] ?? $item['description'] ?? null,
                    ]);
                }
            }

            $pr->update([
                'requisition_date' => $data['requisition_date'] ?? $pr->requisition_date,
                'required_by_date' => $data['required_by_date'] ?? $pr->required_by_date,
                'requester_id' => $data['requester_id'] ?? $pr->requester_id,
                'department_name' => $data['department'] ?? $data['department_name'] ?? $pr->department_name,
                'warehouse_id' => $data['warehouse_id'] ?? $pr->warehouse_id,
                'preferred_vendor_id' => array_key_exists('preferred_vendor_id', $data) ? $data['preferred_vendor_id'] : $pr->preferred_vendor_id,
                'priority' => $data['priority'] ?? $pr->priority,
                'purpose' => $data['justification'] ?? $data['purpose'] ?? $pr->purpose,
                'total_estimated_amount' => $estimatedTotal,
            ]);

            return $pr->fresh(['items', 'requester', 'warehouse', 'preferredVendor']);
        });
    }

    /**
     * Submit Requisition for Department / Management Approval.
     */
    public static function submitRequisition(PurchaseRequisition $pr): PurchaseRequisition
    {
        $pr->update(['status' => 'submitted']);
        return $pr->fresh(['items', 'requester', 'warehouse', 'preferredVendor']);
    }

    /**
     * Approve Purchase Requisition & Issue Cryptographic Verification Hash.
     */
    public static function approveRequisition(PurchaseRequisition $pr, int $userId): PurchaseRequisition
    {
        return DB::transaction(function () use ($pr, $userId) {
            $verificationHash = hash('sha256', "PR-{$pr->id}-{$pr->requisition_number}-{$pr->created_by}-" . time());

            $pr->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'approval_notes' => 'Approved by authorized officer.',
                'verification_hash' => $verificationHash,
            ]);

            try {
                DocumentVerification::updateOrCreate(
                    [
                        'document_type' => 'purchase_requisition',
                        'document_id' => $pr->id,
                    ],
                    [
                        'document_number' => $pr->requisition_number,
                        'verification_hash' => $verificationHash,
                        'qr_payload' => url('/verify/document/' . $verificationHash),
                        'issuer_name' => 'Mutamark Enterprises Ltd',
                        'party_name' => ($pr->department_name) . ' (' . ($pr->requester->name ?? 'Requester') . ')',
                        'total_amount' => $pr->total_estimated_amount,
                        'currency' => 'TZS',
                        'issue_date' => $pr->requisition_date,
                        'status' => 'approved',
                        'signatory_name' => 'Department Head / Supply Chain Director',
                        'signatory_title' => 'Authorized Procurement Officer',
                        'verified_at' => now(),
                        'created_by' => $pr->created_by,
                    ]
                );
            } catch (\Exception $e) {
                // Ignore verification sync exceptions
            }

            return $pr->fresh(['items', 'requester', 'warehouse', 'preferredVendor', 'approver']);
        });
    }

    /**
     * Reject Purchase Requisition with reason.
     */
    public static function rejectRequisition(PurchaseRequisition $pr, string $reason, int $userId): PurchaseRequisition
    {
        $pr->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'approval_notes' => $reason,
        ]);

        return $pr->fresh(['items', 'requester', 'warehouse', 'preferredVendor', 'approver']);
    }

    /**
     * Convert Approved Purchase Requisition directly into a Purchase Order / Bill (PurchaseInvoice).
     */
    public static function convertToPurchaseInvoice(PurchaseRequisition $pr, int $userId): array
    {
        CompanyAccess::check($pr);
        $rfq = \App\Models\Rfq::where('created_by', creatorId())->where('requisition_id', $pr->id)->where('status','awarded')->first();
        abort_unless($rfq, 422, 'Create and award an RFQ before issuing a purchase order.');
        $order = \App\Models\PurchaseOrder::where('created_by', creatorId())->findOrFail($rfq->awarded_po_id);
        $pr->update(['status' => 'converted_to_po', 'converted_po_id' => $order->id]);
        return ['requisition' => $pr->fresh(), 'purchase_order' => $order];
    }

    /**
     * Delete Purchase Requisition (CUD: Delete).
     */
    public static function deleteRequisition(PurchaseRequisition $pr): bool
    {
        return DB::transaction(function () use ($pr) {
            DocumentVerification::where('document_type', 'purchase_requisition')
                ->where('document_id', $pr->id)
                ->delete();

            return $pr->delete();
        });
    }
}
