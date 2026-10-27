<?php

namespace Workdo\Fleet\Database\Seeders;

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
        $module = 'Fleet';

        $permissions  = [
           'fleet manage',
            'driver manage',
            'driver create',
            'driver show',
            'driver edit',
            'driver delete',
            'license manage',
            'license create',
            'license edit',
            'license delete',
            'vehicletype manage',
            'vehicletype create',
            'vehicletype edit',
            'vehicletype delete',
            'fueltype manage',
            'fueltype create',
            'fueltype edit',
            'fueltype delete',
            'recuerring manage',
            'recuerring create',
            'recuerring edit',
            'recuerring delete',
            'maintenanceType manage',
            'maintenanceType create',
            'maintenanceType edit',
            'maintenanceType delete',
            'fleet customer manage',
            'fleet customer create',
            'fleet customer edit',
            'fleet customer delete',
            'vehicle manage',
            'vehicle create',
            'vehicle show',
            'vehicle edit',
            'vehicle delete',
            'insurance manage',
            'insurance create',
            'insurance edit',
            'insurance show',
            'insurance delete',
            'fuel manage',
            'fuel create',
            'fuel edit',
            'fuel delete',
            'booking manage',
            'booking create',
            'booking edit',
            'booking show',
            'booking delete',
            'payment booking manage',
            'payment booking delete',
            'maintenance manage',
            'maintenance create',
            'maintenance edit',
            'maintenance delete',
            'fleet dashboard manage',
            'fleetavailability manage',
            'fleetavailability show',
            'fleet report manage',
            'report maintenance manage',
            'report fuelhistoryreport manage',
            'fleet logbook manage',
            'fleet logbook create',
            'fleet logbook edit',
            'fleet logbook show',
            'fleet logbook delete',
            'fleet insurance booking create',
            'fleet insurance booking edit',
            'fleet insurance booking delete',
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
