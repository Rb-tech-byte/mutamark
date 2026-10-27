<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /** Shared, transactional sequence. Global scope preserves existing globally unique document columns. */
    public static function next(string $prefix): string
    {
        TransactionSafety::requireTransactionalTables(['document_sequences']);
        return DB::transaction(function () use ($prefix) {
            $key = ['company_id' => 0, 'prefix' => $prefix, 'year' => (int) date('Y')];
            DB::table('document_sequences')->insertOrIgnore($key + ['last_number' => 0]);
            $sequence = DB::table('document_sequences')->where($key)->lockForUpdate()->first();
            $sources = ['INV' => ['sales_invoices', 'invoice_number'], 'PI' => ['proforma_invoices', 'proforma_number'], 'DOC' => ['documents', 'document_number'], 'RV' => ['customer_payments', 'payment_number'], 'TXN' => ['payment_transactions', 'transaction_reference']];
            $sources['APR'] = ['approval_requests', 'request_number'];
            $sources['SO'] = ['sales_orders', 'order_number'];
            $sources['DN'] = ['delivery_notes', 'delivery_number'];
            $sources['PO'] = ['purchase_orders', 'po_number'];
            $sources['GRN'] = ['goods_received_notes', 'grn_number'];
            $sources['AUD'] = ['financial_audit_logs', 'audit_number'];
            $sources['WHT'] = ['withholding_taxes', 'wht_number'];
            $sources['PINV'] = ['purchase_invoices', 'invoice_number'];
            $sources['JV'] = ['journal_entries', 'journal_number'];
            $sources['RJV'] = ['journal_entries', 'journal_number'];
            $sources['PV'] = ['vendor_payments', 'payment_number'];
            $existing = 0;
            if ((int) $sequence->last_number === 0 && isset($sources[$prefix])) {
                [$table, $column] = $sources[$prefix];
                foreach (DB::table($table)->where($column, 'like', $prefix.'-'.$key['year'].'-%')->pluck($column) as $value) {
                    if (preg_match('/-(\d+)$/', $value, $match)) $existing = max($existing, (int) $match[1]);
                }
            }
            $number = max($sequence->last_number, $existing) + 1;
            DB::table('document_sequences')->where('id', $sequence->id)->update(['last_number' => $number]);
            return sprintf('%s-%s-%05d', $prefix, $key['year'], $number);
        }, 3);
    }
}
