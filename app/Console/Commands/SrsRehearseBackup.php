<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SrsRehearseBackup extends Command
{
    protected $signature = 'srs:rehearse-backup {filename : Encrypted backup filename, without directories}';
    protected $description = 'Restore an encrypted snapshot into a newly created temporary database and report table counts';

    public function handle(DatabaseBackupService $backups): int
    {
        $name = (string) $this->argument('filename');
        if ($name !== basename($name) || !str_ends_with($name, '.sql.enc')) throw new \RuntimeException('Supply an encrypted backup filename only.');
        $path = $backups->directory().'/'.$name;
        $authentication = $backups->verify($path);
        $original = DB::getDefaultConnection();
        $source = DB::connection($original);
        if ($source->getDriverName() !== 'mysql') throw new \RuntimeException('The rehearsal requires MySQL.');
        $temporary = 'mutamark_restore_'.bin2hex(random_bytes(10));
        $created = false;
        $started = microtime(true);
        $report = ['backup' => $name, 'authentication' => $authentication, 'temporary_database' => $temporary, 'business_database' => $source->getDatabaseName()];
        try {
            // Never accept a caller-supplied database name or reuse an existing database.
            $source->statement('CREATE DATABASE `'.$temporary.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $created = true;
            config(['database.connections.srs_rehearsal' => array_merge($source->getConfig(), ['database' => $temporary])]);
            DB::setDefaultConnection('srs_rehearsal');
            if (DB::connection()->getDatabaseName() !== $temporary || $temporary === $source->getDatabaseName()) throw new \RuntimeException('Restore target isolation check failed.');
            $backups->restore($path);
            $tables = DB::select("SELECT table_name AS name, engine AS engine FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' ORDER BY table_name");
            $report['tables'] = array_map(fn ($table) => ['name' => $table->name, 'engine' => $table->engine, 'rows' => DB::table($table->name)->count()], $tables);
            $report['restored'] = true;
            $report['restore_seconds'] = round(microtime(true) - $started, 3);
        } catch (\Throwable $e) {
            $report['error'] = $e->getMessage();
            throw $e;
        } finally {
            DB::disconnect('srs_rehearsal');
            DB::setDefaultConnection($original);
            if ($created) {
                // Only remove the randomly named schema this invocation successfully created.
                $source->statement('DROP DATABASE `'.$temporary.'`');
                $report['temporary_database_removed'] = true;
            }
            File::ensureDirectoryExists(base_path('scratch'));
            File::put(base_path('scratch/srs-restore-rehearsal.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        $this->info('Isolated restore succeeded; '.count($report['tables']).' tables read successfully. Temporary database removed.');
        return self::SUCCESS;
    }
}
