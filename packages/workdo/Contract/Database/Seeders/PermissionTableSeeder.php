<?php

namespace Modules\Contract\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

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
            'contract manage',
            'contract create',
            'contract edit',
            'contract delete',
            'contract show',
            'comment create',
            'comment delete',
            'contract note create',
            'contract note delete',
            'contracttype manage',
            'contracttype create',
            'contracttype edit',
            'contracttype delete',
            'renewcontract create',
            'renewcontract delete',
        ];

        $company_role = Role::where('name','company')->first();
        foreach ($permission as $key => $value)
        {
            $table = Permission::where('name',$value)->where('module','Contract')->exists();
            if(!$table)
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'Contract',
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
