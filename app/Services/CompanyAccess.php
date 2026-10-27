<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompanyAccess
{
    public static function requirePermission(string $permission): void
    {
        abort_unless(Auth::check() && Auth::user()->can($permission), 403);
    }

    public static function check(Model $record, string $column = 'created_by'): void
    {
        abort_unless(Auth::check() && (int) $record->{$column} === (int) creatorId(), 404);
    }
}
