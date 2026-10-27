<?php

namespace App\Services;

use App\Models\ProformaInvoice;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItemTax;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProformaInvoiceService
{
    public static function create(array $data): ProformaInvoice
    {
        TransactionSafety::requireTransactionalTables(['proforma_invoices', 'proforma_invoice_items', 'document_sequences']);
        return DB::transaction(function () use ($data) {
            $items = $data['items'];
            unset($data['items']);
            $proforma = ProformaInvoice::create($data + [
                'proforma_number' => DocumentNumberService::next('PI'), 'status' => 'draft',
                'creator_id' => auth()->id(), 'created_by' => creatorId(),
            ]);
            $subtotal = $discount = $tax = 0;
            foreach ($items as $item) {
                $line = round($item['quantity'] * $item['unit_price'], 2);
                $lineDiscount = round($line * ($item['discount_percentage'] ?? 0) / 100, 2);
                $lineTax = round(($line - $lineDiscount) * ($item['tax_rate'] ?? 0) / 100, 2);
                $proforma->items()->create([
                    'product_id' => $item['product_id'], 'product_name' => $item['product_name'],
                    'sku' => $item['sku'] ?? null, 'unit' => $item['unit'] ?? null,
                    'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'],
                    'discount_amount' => $lineDiscount, 'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $lineTax, 'total_amount' => $line - $lineDiscount + $lineTax,
                ]);
                $subtotal += $line; $discount += $lineDiscount; $tax += $lineTax;
            }
            $proforma->update(['subtotal' => $subtotal, 'discount_amount' => $discount, 'tax_amount' => $tax, 'total_amount' => $subtotal - $discount + $tax]);
            return $proforma->load('items');
        });
    }

    public static function cancel(ProformaInvoice $proforma): ProformaInvoice
    {
        TransactionSafety::requireTransactionalTables(['proforma_invoices']);
        return DB::transaction(function () use ($proforma) {
            $locked = ProformaInvoice::where('created_by', creatorId())->lockForUpdate()->findOrFail($proforma->id);
            abort_unless(!$locked->converted_to_invoice_id && in_array($locked->status, ['draft', 'sent', 'accepted']), 409);
            $locked->update(['status' => 'cancelled']);
            return $locked;
        }, 3);
    }

    /** Conversion creates a draft invoice; existing posting controls and listeners still apply. */
    public static function convert(ProformaInvoice $proforma, array $data): SalesInvoice
    {
        TransactionSafety::requireTransactionalTables(['proforma_invoices', 'sales_invoices', 'sales_invoice_items', 'sales_invoice_item_taxes', 'document_sequences']);
        return DB::transaction(function () use ($proforma, $data) {
            $proforma = ProformaInvoice::where('created_by', creatorId())->lockForUpdate()->findOrFail($proforma->id);
            if ($proforma->converted_to_invoice_id) {
                return SalesInvoice::where('created_by', creatorId())->findOrFail($proforma->converted_to_invoice_id);
            }
            if (!in_array($proforma->status, ['draft', 'sent', 'accepted']) || ($proforma->expiry_date && $proforma->expiry_date->endOfDay()->isPast())) {
                throw ValidationException::withMessages(['proforma' => __('Only active, unexpired proformas can be converted.')]);
            }
            if (!$proforma->customer_id || $proforma->currency !== 'TZS') {
                throw ValidationException::withMessages(['proforma' => __('Conversion requires an existing customer and TZS currency.')]);
            }
            $invoice = SalesInvoice::create([
                'invoice_number' => DocumentNumberService::next('INV'),
                'customer_id' => $proforma->customer_id, 'warehouse_id' => $data['warehouse_id'],
                'invoice_date' => $data['invoice_date'], 'due_date' => $data['due_date'],
                'subtotal' => $proforma->subtotal, 'discount_amount' => $proforma->discount_amount,
                'tax_amount' => $proforma->tax_amount, 'total_amount' => $proforma->total_amount,
                'balance_amount' => $proforma->total_amount, 'paid_amount' => 0, 'status' => 'draft', 'type' => 'product',
                'payment_terms' => $proforma->payment_terms, 'notes' => $proforma->notes,
                'creator_id' => auth()->id(), 'created_by' => creatorId(),
            ]);
            foreach ($proforma->items as $item) {
                $lineTotal = (float) $item->quantity * (float) $item->unit_price;
                $invoiceItem = $invoice->items()->create([
                    'product_id' => $item->product_id, 'quantity' => $item->quantity, 'unit_price' => $item->unit_price,
                    'discount_percentage' => $lineTotal > 0 ? round((float) $item->discount_amount / $lineTotal * 100, 2) : 0,
                    'tax_percentage' => $item->tax_rate,
                ]);
                if ((float) $item->tax_rate > 0) {
                    SalesInvoiceItemTax::create(['item_id' => $invoiceItem->id, 'tax_name' => 'Tax', 'tax_rate' => $item->tax_rate]);
                }
            }
            // Invoice item saving computes amounts; use those exact amounts in its header.
            $invoice->update([
                'subtotal' => $invoice->items->sum(fn ($item) => $item->quantity * $item->unit_price),
                'discount_amount' => $invoice->items->sum('discount_amount'),
                'tax_amount' => $invoice->items->sum('tax_amount'),
                'total_amount' => $invoice->items->sum('total_amount'),
                'balance_amount' => $invoice->items->sum('total_amount'),
            ]);
            $proforma->update(['status' => 'converted', 'converted_to_invoice_id' => $invoice->id]);
            return $invoice;
        }, 3);
    }
}
