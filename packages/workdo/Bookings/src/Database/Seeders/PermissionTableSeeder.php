<?php

namespace Workdo\Bookings\Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
     public function run()
    {
        Model::unguard();
        Artisan::call('cache:clear');
        $module = 'Bookings';

        $permissions  = [
            'bookings manage',
            'bookings items manage',
            'bookingcustomers manage',
            'bookingcustomers create',
            'bookingcustomers edit',
            'bookingcustomers delete',
            'booking staffs manage',
            'booking staffs create',
            'booking staffs edit',
            'booking staffs delete',
            'bookings appointments manage',
            'bookings appointments create',
            'bookings appointments show',
            'bookings appointments edit',
            'bookings appointments delete',
            'bookings system-setup manage',
            'bookings business hours manage',
            'bookings extra services manage',
            'bookings extra services create',
            'bookings extra services edit',
            'bookings extra services delete',
            'bookings package manage',
            'bookings package create',
            'bookings package edit',
            'bookings package delete',
            'bookings package duplicate',
            'bookings dashboard manage',
            'bookings location manage',
            'bookings description show',
        ];

        $company_role = Role::where('name','company')->first();
        foreach ($permissions as $key => $value)
        {
            $check = Permission::where('name',$value)->where('module',$module)->exists();
            if($check == false)
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => $module,
                        'created_by' => 0,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
                if(!$company_role->hasPermission($value))
                {
                    $company_role->givePermission($permission);
                }
            }
        }
    }
}
