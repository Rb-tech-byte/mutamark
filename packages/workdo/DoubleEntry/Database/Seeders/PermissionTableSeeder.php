<?php

namespace Modules\DoubleEntry\Database\Seeders;

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
            'doubleentry manage',
            'report ledger',
            'report balance sheet',
            'report profit loss',
            'report trial balance',
            'journalentry manage',
            'journalentry create',
            'journalentry show',
            'journalentry edit',
            'journalentry delete',
            'report sales',
            'report receivables',
            'report payables',

        ];
        $company_role = Role::where('name','company')->first();
        foreach ($permission as $key => $value)
        {
            $table = Permission::where('name',$value)->where('module','DoubleEntry')->exists();
            if(!$table)
            {
                $permission = Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'DoubleEntry',
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
