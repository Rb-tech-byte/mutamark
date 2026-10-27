<?php

namespace Modules\Appointment\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");

        Artisan::call('cache:clear');

        $permission = [
            'appointment manage',
            'appointment dashboard manage',
            'appointments manage',
            'appointments create',
            'appointments edit',
            'appointments delete',
            'appointments show',
            'appointments copy link',
            'question manage',
            'question create',
            'question edit',
            'question delete',
            'schedule manage',
            'schedule delete',
            'schedule show',
            'schedule action',
        ];

        $company_role = Role::where('name', 'company')->first();
        foreach ($permission as $key => $value) {
            $table = Permission::where('name', $value)->where('module', 'Appointment')->exists();
            if (!$table) {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'Appointment',
                        'created_by' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                );

                if (!$company_role->hasPermission($value)) {
                    $company_role->givePermission($permission);
                }
            }
        }
    }
}
