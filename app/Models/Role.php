<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $guarded = [];

    /**
     * Compatibility alias for hasPermissionTo
     */
    public function hasPermission($permission): bool
    {
        try {
            return $this->hasPermissionTo($permission);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Resilient givePermissionTo that ignores already-attached duplicates
     */
    public function givePermissionTo(...$permissions)
    {
        try {
            return parent::givePermissionTo(...$permissions);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return $this;
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), '1062')) {
                return $this;
            }
            throw $e;
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '1062')) {
                return $this;
            }
            throw $e;
        }
    }

    /**
     * Compatibility alias for givePermissionTo
     */
    public function givePermission($permission)
    {
        try {
            return $this->givePermissionTo($permission);
        } catch (\Throwable $e) {
            return $this;
        }
    }
}
