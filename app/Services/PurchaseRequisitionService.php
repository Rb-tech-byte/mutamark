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
     * Convert Approved Purchase Requisition directly into a Purchase Order & Purchase Bill (PurchaseInvoice).
     */
    public static function convertToPurchaseInvoice(PurchaseRequisition $pr, int $userId, ?int $vendorUserId = null): array
    {
        CompanyAccess::check($pr);

        return DB::transaction(function () use ($pr, $userId, $vendorUserId) {
            $pr->loadMissing('items');

            $resolvedVendorUserId = $vendorUserId
                ?: $pr->preferred_vendor_id
                ?: User::where('created_by', creatorId())
                    ->where(function ($q) {
                        $q->where('type', 'vendor')
                            ->orWhereHas('roles', fn ($r) => $r->where('name', 'vendor'));
                    })
                    ->value('id');

            if (!$resolvedVendorUserId) {
                throw new \RuntimeException(__('Please select or create a vendor before generating a Purchase Bill.'));
            }

            $accountVendor = \Workdo\Account\Models\Vendor::where('user_id', $resolvedVendorUserId)->first()
                ?? \Workdo\Account\Models\Vendor::where('created_by', creatorId())->first();

            $order = null;
            $rfq = \App\Models\Rfq::where('created_by', creatorId())
                ->where('requisition_id', $pr->id)
                ->where('status', 'awarded')
                ->first();

            if ($rfq && $rfq->awarded_po_id) {
                $order = \App\Models\PurchaseOrder::where('created_by', creatorId())->find($rfq->awarded_po_id);
            }

            $subtotal = 0;
            $totalTax = 0;
            $poItemsData = [];
            $piItemsData = [];

            foreach ($pr->items as $item) {
                $qty = (float) $item->quantity;
                $unitPrice = (float) ($item->estimated_unit_price ?? 0);
                $lineNet = round($qty * $unitPrice, 2);
                $taxRate = 18.0;
                $lineTax = round($lineNet * ($taxRate / 100), 2);
                $lineGross = round($lineNet + $lineTax, 2);

                $subtotal += $lineNet;
                $totalTax += $lineTax;

                $poItemsData[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name ?? 'Procured Item',
                    'sku' => $item->sku,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineGross,
                    'unit' => $item->unit ?? 'Unit',
                ];

                $piItemsData[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total_amount' => $lineGross,
                ];
            }

            $grandTotal = round($subtotal + $totalTax, 2);

            if (!$order && $accountVendor) {
                $order = \App\Models\PurchaseOrder::create([
                    'po_number' => DocumentNumberService::next('PO'),
                    'po_date' => now()->toDateString(),
                    'expected_delivery_date' => $pr->required_by_date ?? now()->addDays(7)->toDateString(),
                    'vendor_id' => $accountVendor->id,
                    'warehouse_id' => $pr->warehouse_id,
                    'requisition_id' => $pr->id,
                    'subtotal' => $subtotal,
                    'tax_amount' => $totalTax,
                    'discount_amount' => 0,
                    'total_amount' => $grandTotal,
                    'currency' => 'TZS',
                    'status' => 'issued',
                    'delivery_terms' => 'Standard Delivery',
                    'payment_terms' => 'Net 30 Days',
                    'notes' => $pr->purpose ?? ('Generated from Requisition ' . $pr->requisition_number),
                    'creator_id' => $userId,
                    'created_by' => creatorId(),
                ]);

                foreach ($poItemsData as $data) {
                    $data['purchase_order_id'] = $order->id;
                    \App\Models\PurchaseOrderItem::create($data);
                }
            }

            $invoice = PurchaseInvoice::create([
                'invoice_number' => PurchaseInvoice::generateInvoiceNumber(),
                'purchase_order_id' => $order?->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => $pr->required_by_date ?? now()->addDays(30)->toDateString(),
                'vendor_id' => $resolvedVendorUserId,
                'warehouse_id' => $pr->warehouse_id,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => 0,
                'total_amount' => $grandTotal,
                'paid_amount' => 0,
                'debit_note_applied' => 0,
                'balance_amount' => $grandTotal,
                'status' => 'draft',
                'payment_terms' => 'Net 30 Days',
                'notes' => $pr->purpose ?? ('Generated from Requisition ' . $pr->requisition_number),
                'creator_id' => $userId,
                'created_by' => creatorId(),
            ]);

            foreach ($piItemsData as $piData) {
                if (!empty($piData['product_id'])) {
                    $piData['invoice_id'] = $invoice->id;
                    PurchaseInvoiceItem::create($piData);
                }
            }

            $pr->update([
                'status' => 'converted_to_po',
                'converted_po_id' => $order?->id,
                'converted_invoice_id' => $invoice->id,
            ]);

            return [
                'requisition' => $pr->fresh(),
                'purchase_order' => $order,
                'purchase_invoice' => $invoice,
            ];
        });
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
