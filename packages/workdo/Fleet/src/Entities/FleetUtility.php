<?php

namespace Workdo\Fleet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Role;
use App\Models\Permission;

class FleetUtility extends Model
{
    use HasFactory;

    protected $fillable = [];

    public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $client_permissions = [

            'fleet manage',
            'booking manage',
            'booking create',
            'booking edit',
            'booking show',
            'payment booking manage',
            'payment booking delete',
            'fleetavailability manage',
            'fleetavailability show',
            'fleet dashboard manage',
        ];

        $staff_permissions = [

            'fleet manage',
            'vehicle manage',
            'maintenance manage',
            'maintenance create',
            'maintenance edit',
            'maintenance delete',
            'fleet dashboard manage',
        ];

        $driver_permission=[

            'fleet manage',
            'vehicle manage',
            'fuel manage',
            'fuel create',
            'fuel edit',
            'fuel delete',
            'fleet dashboard manage',
        ];


        if ($role_id == Null) {
            // client
            $roles_c = Role::where('name', 'client')->get();
            foreach ($roles_c as $role) {
                foreach ($client_permissions as $permission_c) {
                    $permission = Permission::where('name', $permission_c)->first();

                    if(!$role->hasPermission($permission_c))
                    {
                        $role->givePermission($permission);
                    }
                }
            }

            // staff
            $roles_v = Role::where('name', 'staff')->get();

            foreach ($roles_v as $role) {
                foreach ($staff_permissions as $permission_v) {
                    $permission = Permission::where('name', $permission_v)->first();
                    if(!$role->hasPermission($permission_v))
                    {
                        $role->givePermission($permission);
                    }
                }
            }
            // driver
            $roles_v = Role::where('name', 'driver')->get();

            foreach ($roles_v as $role) {
                foreach ($driver_permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)->first();
                    if(!$role->hasPermission($permission_v))
                    {
                        $role->givePermission($permission);
                    }
                }
            }
        } else {
            if ($rolename == 'client') {
                $roles_c = Role::where('name', 'client')->where('id', $role_id)->first();
                if ($roles_c) {
                    foreach ($client_permissions as $permission_c) {
                        $permission = Permission::where('name', $permission_c)->first();
                        if (!empty($permission)) {
                            try {
                                if(!$roles_c->hasPermission($permission_c))
                                {
                                    $roles_c->givePermission($permission);
                                }
                            } catch (\Throwable $e) {}
                        }
                    }
                }
            } elseif ($rolename == 'staff') {
                $roles_v = Role::where('name', 'staff')->where('id', $role_id)->first();
                if ($roles_v) {
                    foreach ($staff_permissions as $permission_v) {
                        $permission = Permission::where('name', $permission_v)->first();
                        if (!empty($permission)) {
                            try {
                                if(!$roles_v->hasPermission($permission_v))
                                {
                                    $roles_v->givePermission($permission);
                                }
                            } catch (\Throwable $e) {}
                        }
                    }
                }
            }
            elseif ($rolename == 'driver') {
                $roles_v = Role::where('name', 'driver')->where('id', $role_id)->first();
                if ($roles_v) {
                    foreach ($driver_permission as $permission_v) {
                        $permission = Permission::where('name', $permission_v)->first();
                        if (!empty($permission)) {
                            try {
                                if(!$roles_v->hasPermission($permission_v))
                                {
                                    $roles_v->givePermission($permission);
                                }
                            } catch (\Throwable $e) {}
                        }
                    }
                }
            }
        }
    }
}
