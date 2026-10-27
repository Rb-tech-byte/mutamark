<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SrsUpgradeStorage extends Command
{
    protected $signature = 'srs:upgrade-storage {--apply : Rebuild tables after maintenance approval} {--confirm-database= : Exact database name required for apply} {--maintenance-approved : Confirm permission to pause HTTP and database writes}';
    protected $description = 'Plan an InnoDB conversion; changes require explicit maintenance flags';

    public function handle(DatabaseBackupService $backups): int
    {
        if (DB::connection()->getDriverName() !== 'mysql') throw new \RuntimeException('Storage upgrade supports MySQL/MariaDB only.');
        $database = DB::connection()->getDatabaseName();
        $tables = DB::select("SELECT table_name AS name, engine AS engine, table_rows AS estimated_rows, data_length + index_length AS bytes FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' AND engine <> 'InnoDB' ORDER BY table_name");
        $plan = [
            'database' => $database, 'read_only_plan' => !$this->option('apply'),
            'table_count' => count($tables), 'tables' => $tables,
            'estimated_source_bytes' => array_sum(array_map(fn ($table) => (int) $table->bytes, $tables)),
            'workspace_free_bytes' => disk_free_space(base_path()),
            'operations' => [
                'Pause the application and ensure external writers/queue workers are paused.',
                'Acquire a global read lock, stream an encrypted backup, release the lock and authenticate the backup.',
                'Convert each named table to InnoDB and compare exact before/after row counts.',
                'Apply only the two reviewed additive SRS migrations.',
                'Check all table engines and migration success before reopening the application.',
            ],
            'limits' => ['Conversion does not reconstruct foreign keys omitted by MyISAM.', 'A failed conversion leaves maintenance enabled for recovery; previously converted tables remain converted.', 'Backup authentication is not a substitute for an isolated restore rehearsal.'],
        ];
        File::ensureDirectoryExists(base_path('scratch'));
        File::put(base_path('scratch/srs-storage-upgrade-plan.json'), json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        if (!$this->option('apply')) {
            $this->info('Read-only plan saved to scratch/srs-storage-upgrade-plan.json. Tables requiring conversion: '.count($tables));
            return self::SUCCESS;
        }
        if ($this->option('confirm-database') !== $database || !$this->option('maintenance-approved')) {
            $this->error('Apply requires the exact database name and explicit maintenance approval. No data changed.');
            return self::FAILURE;
        }
        if (app()->isDownForMaintenance()) {
            $this->error('The application is already in maintenance. Resolve its existing maintenance owner before this operation.');
            return self::FAILURE;
        }
        $history = ['started_at' => now()->toIso8601String(), 'database' => $database, 'completed_tables' => []];
        Artisan::call('down', ['--retry' => 60]);
        try {
            $path = $backups->create(true);
            $history['encrypted_backup'] = basename($path);
            foreach ($tables as $table) {
                $before = DB::table($table->name)->count();
                $identifier = '`'.str_replace('`', '``', $table->name).'`';
                DB::statement('ALTER TABLE '.$identifier.' ENGINE=InnoDB');
                $after = DB::table($table->name)->count();
                if ($before !== $after) throw new \RuntimeException('Row count changed for '.$table->name.'. Application remains in maintenance.');
                $history['completed_tables'][] = ['name' => $table->name, 'rows' => $after];
                File::put(base_path('scratch/srs-storage-upgrade-history.json'), json_encode($history, JSON_PRETTY_PRINT));
            }
            $exit = Artisan::call('migrate', ['--path' => [
                'database/migrations/2026_10_14_060000_add_document_sequences_and_retention.php',
                'database/migrations/2026_10_14_061000_enforce_inventory_stock_uniqueness.php',
            ], '--force' => true]);
            if ($exit !== 0) throw new \RuntimeException('Additive migrations failed; review migration diagnostics before reopening.');
            $remaining = DB::selectOne("SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' AND engine <> 'InnoDB'")->count;
            if ($remaining) throw new \RuntimeException('Non-transactional tables remain.');
            $history['completed_at'] = now()->toIso8601String();
            File::put(base_path('scratch/srs-storage-upgrade-history.json'), json_encode($history, JSON_PRETTY_PRINT));
            Artisan::call('up');
            $this->info('InnoDB conversion and additive migrations completed; row counts preserved.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $history['error'] = $e->getMessage();
            File::put(base_path('scratch/srs-storage-upgrade-history.json'), json_encode($history, JSON_PRETTY_PRINT));
            $this->error('Upgrade stopped and maintenance remains enabled: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
