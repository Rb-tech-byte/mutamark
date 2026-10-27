<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class SrsBackup extends Command
{
    protected $signature = 'srs:backup {--verify= : Verify an existing encrypted backup filename without restoring}';
    protected $description = 'Create or authenticate an encrypted full database backup';
    public function handle(DatabaseBackupService $backups): int
    {
        if ($name = $this->option('verify')) {
            $path = $backups->directory().'/'.basename($name);
            $this->line(json_encode($backups->verify($path)));
        } else {
            $this->info('Encrypted snapshot created and verified: '.basename($backups->create()));
        }
        return self::SUCCESS;
    }
}
