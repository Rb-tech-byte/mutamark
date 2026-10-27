<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SrsAudit extends Command
{
    protected $signature = 'srs:audit {--json : Emit a machine-readable read-only schema report}';
    protected $description = 'Check SRS schema foundations and basic integrity without modifying data';

    public function handle(): int
    {
        $required = [
            'users' => ['is_enable_login', 'created_by'], 'warehouses' => ['created_by'],
            'documents' => ['document_number', 'file_path', 'created_by', 'deleted_at'],
            'document_versions' => ['document_id', 'version_number', 'uploaded_by'],
            'document_sequences' => ['prefix', 'year', 'last_number'],
            'proforma_invoices' => ['customer_id', 'converted_to_invoice_id', 'created_by'],
            'proforma_invoice_items' => ['proforma_invoice_id', 'product_id', 'quantity'],
            'warehouse_stocks' => ['product_id', 'warehouse_id', 'quantity'],
            'approval_delegations' => ['delegator_id', 'delegate_id', 'start_date', 'end_date'],
            'withholding_taxes' => ['reference_type', 'reference_id', 'gross_amount', 'wht_amount'],
            'payment_gateways' => ['credentials', 'creator_id', 'webhook_secret'],
            'payment_transactions' => ['gateway_key', 'invoice_id', 'customer_payment_id'],
        ];
        $missingTables = []; $missingColumns = [];
        foreach ($required as $table => $columns) {
            if (!Schema::hasTable($table)) { $missingTables[] = $table; continue; }
            $missing = array_values(array_diff($columns, Schema::getColumnListing($table)));
            if ($missing) $missingColumns[$table] = $missing;
        }
        $integrity = [];
        if (Schema::hasTable('warehouse_stocks')) {
            $integrity['duplicate_stock_keys'] = DB::query()->fromSub(DB::table('warehouse_stocks')->select('product_id', 'warehouse_id')->groupBy('product_id', 'warehouse_id')->havingRaw('COUNT(*) > 1'), 'duplicates')->count();
            $integrity['negative_stock_rows'] = DB::table('warehouse_stocks')->where('quantity', '<', 0)->count();
        }
        if (Schema::hasTable('payment_transactions') && Schema::hasTable('sales_invoices')) {
            $integrity['orphan_payment_invoice_links'] = DB::table('payment_transactions as payments')->leftJoin('sales_invoices as invoices', 'invoices.id', '=', 'payments.invoice_id')->whereNotNull('payments.invoice_id')->whereNull('invoices.id')->count();
        }
        $migrator = app('migrator');
        $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
        $ran = Schema::hasTable('migrations') ? $migrator->getRepository()->getRan() : [];
        $report = [
            'read_only' => true, 'driver' => DB::connection()->getDriverName(),
            'missing_tables' => $missingTables, 'missing_columns' => $missingColumns,
            'integrity_counts' => $integrity, 'pending_migrations' => array_values(array_diff(array_keys($files), $ran)),
        ];
        if (DB::connection()->getDriverName() === 'mysql') {
            $report['non_transactional_tables'] = DB::select("SELECT table_name, engine FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' AND engine <> 'InnoDB'");
        }
        if ($this->option('json')) $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        else $this->line(json_encode($report, JSON_PRETTY_PRINT));
        return ($missingTables || $missingColumns || array_sum($integrity) > 0) ? self::FAILURE : self::SUCCESS;
    }
}
