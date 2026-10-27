<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseBackupService
{
    private const MAGIC = "MUTAMARK-ENCRYPTED-BACKUP-1\n";

    public function directory(): string
    {
        $directory = storage_path('app/backups');
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) throw new \RuntimeException('Unable to create backup directory.');
        return $directory;
    }

    public function create(bool $lockWrites = false): string
    {
        if (DB::connection()->getDriverName() !== 'mysql') throw new \RuntimeException('Full database backup currently supports MySQL.');
        $filename = 'mutamark_backup_'.date('Y-m-d_H-i-s').'_'.Str::random(8).'.sql.enc';
        $path = $this->directory().'/'.$filename;
        $this->encrypt($path, $this->sqlChunks($lockWrites));
        $this->verify($path);
        if ($disk = config('backup.offsite_disk')) {
            $stream = fopen($path, 'rb');
            try {
                if (!Storage::disk($disk)->put('mutamark/backups/'.$filename, $stream)) throw new \RuntimeException('Off-site backup upload failed; local encrypted snapshot is retained.');
            } finally { fclose($stream); }
        }
        return $path;
    }

    /** Authenticated streaming encryption; plaintext is never written to a backup file. */
    public function encrypt(string $path, iterable $chunks): void
    {
        if (!function_exists('sodium_crypto_secretstream_xchacha20poly1305_init_push')) throw new \RuntimeException('Sodium secretstream support is required for encrypted backups.');
        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($this->key());
        $file = fopen($path, 'xb');
        if (!$file) throw new \RuntimeException('Unable to open backup output.');
        try {
            $this->write($file, self::MAGIC.$header);
            foreach ($chunks as $chunk) {
                foreach (str_split($chunk, 65536) as $part) {
                    $encrypted = sodium_crypto_secretstream_xchacha20poly1305_push($state, $part);
                    $this->write($file, pack('N', strlen($encrypted)).$encrypted);
                }
            }
            $final = sodium_crypto_secretstream_xchacha20poly1305_push($state, '', '', SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL);
            $this->write($file, pack('N', strlen($final)).$final);
        } catch (\Throwable $e) {
            fclose($file); unlink($path); throw $e;
        }
        fclose($file);
    }

    public function verify(string $path): array
    {
        $bytes = 0;
        foreach ($this->decrypt($path) as $chunk) $bytes += strlen($chunk);
        return ['verified' => true, 'plaintext_bytes' => $bytes, 'sha256' => hash_file('sha256', $path)];
    }

    public function restore(string $path): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') throw new \RuntimeException('Restore currently supports MySQL.');
        if (filesize($path) > config('backup.max_restore_bytes')) throw new \RuntimeException('Snapshot exceeds the web restore limit. Use the documented administrator recovery procedure.');
        $this->verify($path); // Authenticate the whole file before executing any SQL.
        $sql = implode('', iterator_to_array($this->decrypt($path), false));
        try { DB::unprepared($sql); }
        finally { DB::unprepared('SET FOREIGN_KEY_CHECKS=1'); }
    }

    private function sqlChunks(bool $lockWrites = false): \Generator
    {
        $pdo = DB::connection()->getPdo();
        if ($pdo->inTransaction()) throw new \RuntimeException('Backups must run outside a business transaction.');
        $nonTransactional = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE' AND engine <> 'InnoDB'")->fetchColumn();
        if ($nonTransactional && !$lockWrites) throw new \RuntimeException('A consistent online backup requires InnoDB tables. Review non-transactional tables before proceeding.');
        if ($lockWrites) $pdo->exec('FLUSH TABLES WITH READ LOCK');
        else { $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'); $pdo->beginTransaction(); }
        try {
            yield "-- Mutamark ERP authenticated full backup\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
            $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $identifier = '`'.str_replace('`', '``', $table).'`';
                $definition = $pdo->query('SHOW CREATE TABLE '.$identifier)->fetch(\PDO::FETCH_NUM)[1];
                yield 'DROP TABLE IF EXISTS '.$identifier.";\n".$definition.";\n";
                $count = (int) $pdo->query('SELECT COUNT(*) FROM '.$identifier)->fetchColumn();
                for ($offset = 0; $offset < $count; $offset += 200) {
                    $rows = $pdo->query('SELECT * FROM '.$identifier.' LIMIT 200 OFFSET '.$offset)->fetchAll(\PDO::FETCH_ASSOC);
                    if (!$rows) continue;
                    $columns = implode(',', array_map(fn ($column) => '`'.str_replace('`', '``', $column).'`', array_keys($rows[0])));
                    $values = array_map(fn ($row) => '('.implode(',', array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote($value), array_values($row))).')', $rows);
                    yield 'INSERT INTO '.$identifier.' ('.$columns.') VALUES '.implode(',', $values).";\n";
                }
            }
            yield "SET FOREIGN_KEY_CHECKS=1;\n";
        } finally {
            if ($lockWrites) $pdo->exec('UNLOCK TABLES');
            elseif ($pdo->inTransaction()) $pdo->rollBack();
        }
    }

    private function decrypt(string $path): \Generator
    {
        $file = fopen($path, 'rb');
        if (!$file) throw new \RuntimeException('Backup not found.');
        try {
            if (fread($file, strlen(self::MAGIC)) !== self::MAGIC) throw new \RuntimeException('Use an authenticated encrypted backup; legacy SQL snapshots require a separate reviewed recovery procedure.');
            $header = fread($file, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES);
            $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $this->key());
            while (true) {
                $lengthBytes = fread($file, 4);
                if (strlen($lengthBytes) !== 4) throw new \RuntimeException('Truncated backup.');
                $length = unpack('N', $lengthBytes)[1];
                if ($length < 17 || $length > 65553) throw new \RuntimeException('Invalid encrypted backup frame.');
                $ciphertext = fread($file, $length);
                if (strlen($ciphertext) !== $length || !($result = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $ciphertext))) throw new \RuntimeException('Backup authentication failed.');
                [$plaintext, $tag] = $result;
                if ($tag === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL) {
                    if (fread($file, 1) !== '') throw new \RuntimeException('Unexpected data after backup end.');
                    return;
                }
                yield $plaintext;
            }
        } finally { fclose($file); }
    }

    private function key(): string
    {
        $key = (string) config('app.key');
        if ($key === '') throw new \RuntimeException('An application key is required for backup encryption.');
        return hash('sha256', 'mutamark-backup-v1|'.$key, true);
    }

    private function write($file, string $data): void
    {
        while ($data !== '') {
            $written = fwrite($file, $data);
            if (!$written) throw new \RuntimeException('Unable to write backup; check disk space.');
            $data = substr($data, $written);
        }
    }
}
