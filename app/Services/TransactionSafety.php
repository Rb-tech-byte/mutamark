<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionSafety
{
    public static function requireTransactionalTables(array $tables): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') return;
        $rows = DB::table('information_schema.tables')->where('table_schema', DB::connection()->getDatabaseName())
            ->whereIn('table_name', $tables)->get(['table_name as name', 'engine as engine']);
        if ($rows->count() !== count(array_unique($tables)) || $rows->contains(fn ($row) => strcasecmp($row->engine ?? '', 'InnoDB') !== 0)) {
            throw ValidationException::withMessages(['database' => __('This workflow requires the reviewed InnoDB storage upgrade and pending migrations before it can write safely.')]);
        }
    }
}
