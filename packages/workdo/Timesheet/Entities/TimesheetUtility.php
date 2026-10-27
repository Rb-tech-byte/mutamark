<?php

namespace Modules\Timesheet\Entities;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimesheetUtility extends Model
{
    public static function GivePermissionToRoles($role_id = null,$rolename = null)
    {
        $staff_permissions=[
            'timesheet manage',
            'timesheet create',
            'timesheet edit',
            'timesheet delete',
        ];

        if($role_id == Null)
        {
            $roles_v = Role::where('name','staff')->get();

            foreach($roles_v as $role)
            {
                foreach($staff_permissions as $permission_v){
                    $permission = Permission::where('name',$permission_v)->first();
                    if(!empty($permission))
                    {
                        try {
                            if(!$role->hasPermission($permission_v))
                            {
                                $role->givePermission($permission);
                            }
                        } catch (\Throwable $e) {}
                    }
                }
            }
        }
        else
        {
            if($rolename == 'staff')
            {
                $roles_v = Role::where('name','staff')->where('id',$role_id)->first();
                if($roles_v)
                {
                    foreach($staff_permissions as $permission_v){
                        $permission = Permission::where('name',$permission_v)->first();
                        if(!empty($permission))
                        {
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
