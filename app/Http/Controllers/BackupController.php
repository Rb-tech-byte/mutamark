<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class BackupController extends Controller
{
    protected function getBackupDir(): string
    {
        abort_unless(Auth::check() && Auth::user()->type === 'superadmin', 403);
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    /**
     * Display Database Backups & Disaster Recovery Dashboard (§3.34.1)
     */
    public function index()
    {
        $dir = $this->getBackupDir();
        $files = array_merge(glob($dir . '/*.sql.enc'), glob($dir . '/*.sql'));
        rsort($files);

        $backups = [];
        foreach ($files as $file) {
            $size = filesize($file);
            $backups[] = [
                'filename' => basename($file),
                'size_human' => $this->formatBytes($size),
                'size_bytes' => $size,
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                'age' => Carbon::createFromTimestamp(filemtime($file))->diffForHumans(),
            ];
        }

        $tablesCount = count(Schema::getTableListing());
        $dbName = DB::connection()->getDatabaseName();

        return Inertia::render('Backup/Index', [
            'backups' => $backups,
            'system_info' => [
                'database_name' => $dbName,
                'tables_count' => $tablesCount,
                'backup_directory' => 'storage/app/backups',
                'total_backups' => count($backups),
                'latest_backup' => count($backups) > 0 ? $backups[0]['created_at'] : null,
                'disk_free_space' => $this->formatBytes(disk_free_space($dir)),
            ],
        ]);
    }

    /**
     * Create Full Database Backup Snapshot
     */
    public function createBackup()
    {
        $this->getBackupDir();
        $path = app(\App\Services\DatabaseBackupService::class)->create();
        return back()->with('success', __('Encrypted backup created and integrity verified: ').basename($path));
    }

    /**
     * Download Backup File
     */
    public function downloadBackup($filename)
    {
        $safeName = basename($filename);
        $filepath = $this->getBackupDir() . '/' . $safeName;

        if (!file_exists($filepath)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        return response()->download($filepath, $safeName, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Restore Database from Backup Snapshot
     */
    public function restoreBackup(Request $request, $filename)
    {
        $safeName = basename($filename);
        $filepath = $this->getBackupDir() . '/' . $safeName;

        if (!file_exists($filepath)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        // Execute SQL script
        try {
            app(\App\Services\DatabaseBackupService::class)->restore($filepath);

            return redirect()->back()->with('success', "Database successfully restored from {$safeName}!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete Backup File
     */
    public function destroyBackup($filename)
    {
        $safeName = basename($filename);
        $filepath = $this->getBackupDir() . '/' . $safeName;

        if (file_exists($filepath)) {
            unlink($filepath);
            return redirect()->back()->with('success', "Backup {$safeName} removed.");
        }

        return redirect()->back()->with('error', 'Backup file not found.');
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
