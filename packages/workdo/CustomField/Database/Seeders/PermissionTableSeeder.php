<?php

namespace Modules\CustomField\Database\Seeders;

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

        $permissions  = [
            'customfield manage',
            'customfield create',
            'customfield edit',
            'customfield delete',
        ];


        $company_role = Role::where('name','company')->first();
        foreach ($permissions as $key => $value)
        {
            $table = Permission::where('name',$value)->where('module','CustomField')->exists();
            if($table == false)
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'CustomField',
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
