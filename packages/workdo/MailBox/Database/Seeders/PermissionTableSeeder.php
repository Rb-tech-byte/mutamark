<?php

namespace Modules\MailBox\Database\Seeders;

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

        $Companypermission  = [
            'Emailbox manage',
            'Emailbox mail sent',
            'Emailbox mail reply',
            'Emailbox mail delete',
            'Emailbox mail move'
        ];
        $company_role = Role::where('name','company')->first();
        foreach ($Companypermission as $key => $value)
        {
            $table = Permission::where('name',$value)->where('module','MailBox')->exists();
            if(!$table)
            {
                Permission::create(
                    [
                        'name' => $value,
                        'guard_name' => 'web',
                        'module' => 'MailBox',
                        'created_by' => 0,
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
                
                $permission = Permission::where('name',$value)->first();
                if(!$company_role->hasPermission($value))
                {
                    $company_role->givePermission($permission);
                }
            }
        }
    }
}
