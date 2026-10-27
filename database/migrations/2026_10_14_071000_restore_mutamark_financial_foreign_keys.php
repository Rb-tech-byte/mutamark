<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function relationships(): array
    {
        return [
            ['customers', 'user_id', 'users'], ['vendors', 'user_id', 'users'],
            ['sales_invoices', 'customer_id', 'users'], ['sales_invoices', 'warehouse_id', 'warehouses'], ['sales_invoices', 'sales_order_id', 'sales_orders'],
            ['sales_invoice_items', 'invoice_id', 'sales_invoices'], ['sales_invoice_items', 'product_id', 'product_service_items'],
            ['purchase_invoices', 'vendor_id', 'users'], ['purchase_invoices', 'warehouse_id', 'warehouses'],
            ['purchase_invoice_items', 'invoice_id', 'purchase_invoices'], ['purchase_invoice_items', 'product_id', 'product_service_items'],
            ['warehouse_stocks', 'product_id', 'product_service_items'], ['warehouse_stocks', 'warehouse_id', 'warehouses'],
            ['sales_orders', 'customer_id', 'users'], ['sales_orders', 'warehouse_id', 'warehouses'], ['sales_order_items', 'sales_order_id', 'sales_orders'],
            ['delivery_notes', 'invoice_id', 'sales_invoices'], ['delivery_notes', 'sales_order_id', 'sales_orders'], ['delivery_notes', 'customer_id', 'users'],
            ['delivery_note_items', 'delivery_note_id', 'delivery_notes'], ['goods_received_notes', 'purchase_invoice_id', 'purchase_invoices'],
            ['grn_items', 'grn_id', 'goods_received_notes'], ['document_versions', 'document_id', 'documents'],
            ['customer_payment_allocations', 'payment_id', 'customer_payments'], ['customer_payment_allocations', 'invoice_id', 'sales_invoices'],
            ['vendor_payment_allocations', 'payment_id', 'vendor_payments'], ['vendor_payment_allocations', 'invoice_id', 'purchase_invoices'],
            ['journal_entry_items', 'journal_entry_id', 'journal_entries'], ['journal_entry_items', 'account_id', 'chart_of_accounts'],
        ];
    }

    public function up(): void
    {
        $pending = [];
        $violations = [];
        // Check every relationship before applying any DDL; never discard legacy rows.
        foreach ($this->relationships() as [$table, $column, $parent]) {
            if (!Schema::hasTable($table) || !Schema::hasTable($parent) || !Schema::hasColumn($table, $column)) continue;
            if (collect(Schema::getForeignKeys($table))->contains(fn ($key) => $key['columns'] === [$column])) continue;
            $orphans = DB::table($table.' as child')->leftJoin($parent.' as parent', 'parent.id', '=', 'child.'.$column)->whereNotNull('child.'.$column)->whereNull('parent.id')->count();
            if ($orphans) $violations[] = ['table' => $table, 'column' => $column, 'parent' => $parent, 'orphans' => $orphans];
            $pending[] = [$table, $column, $parent];
        }
        file_put_contents(base_path('scratch/mutamark-fk-preflight.json'), json_encode($violations, JSON_PRETTY_PRINT));
        if ($violations) throw new RuntimeException('Reconcile orphan rows listed in scratch/mutamark-fk-preflight.json before adding foreign keys. No rows changed.');
        foreach ($pending as [$table, $column, $parent]) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->foreign($column, 'mutamark_'.substr(hash('sha256', $table.'.'.$column), 0, 20))->references('id')->on($parent)->restrictOnDelete());
        }
    }

    public function down(): void
    {
        // Financial referential integrity is deliberately retained on application rollback.
    }
};
