<?php

namespace Modules\FileSharing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use App\Models\Role;
use App\Models\Permission;

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
        Artisan::call('cache:clear');

        $permission  = [
            'filesharing manage',
            'files manage',
            'files create',
            'files edit',
            'files delete',
            'files download',
            'downloads manage',
            'downloads show'
        ];

        $company_role = Role::where('name', 'company')->first();
        foreach ($permission as $key => $value) {
            $table = Permission::where('name', $value)->where('module', 'FileSharing')->exists();
            if (!$table) {
                $data = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'FileSharing',
                        'created_by' => 0,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
                if(!$company_role->hasPermission($value)){
                    $company_role->givePermission($data);
                }
            }
        }
        
    }
}
