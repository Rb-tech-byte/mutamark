<?php

namespace Workdo\Fleet\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Workdo\Fleet\Entities\FleetUtility;
use App\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $super_admin = User::where('type','super admin')->first();
        if(!empty($super_admin))
        {
            $companys = User::where('type','company')->get();
            if(count($companys) > 0)
            {
                foreach ($companys as $key => $company) {
                    $role = Role::where('name','driver')->where('created_by',$company->id)->where('guard_name','web')->exists();
                    if(!$role)
                    {
                        $role                   = new Role();
                        $role->name             = 'driver';
                        $role->guard_name       = 'web';
                        $role->module           = 'Fleet';
                        $role->created_by       = $company->id;
                        $role->save();
                    }
                }
            }
        }

        $companys = User::where('type','company')->get();
        if(count($companys) > 0)
        {
            foreach ($companys as $key => $company) {
                $role = Role::where('name','contractor')->where('created_by',$company->id)->where('guard_name','web')->exists();
                if(!$role)
                {
                    $role                   = new Role();
                    $role->name             = 'contractor';
                    $role->guard_name       = 'web';
                    $role->module           = 'Fleet';
                    $role->created_by       = $company->id;
                    $role->save();
                }
            }
        }

        if(!empty($super_admin))
        {
            $companys = User::where('type','company')->get();
            if(count($companys) > 0)
            {
                foreach ($companys as $key => $company) {
                    $role = Role::where('name','client')->where('created_by',$company->id)->where('guard_name','web')->exists();
                    if(!$role)
                    {
                        $role                   = new Role();
                        $role->name             = 'client';
                        $role->guard_name       = 'web';
                        $role->module           = 'Fleet';
                        $role->created_by       = $company->id;
                        $role->save();
                    }
                }
            }
        }
        FleetUtility::GivePermissionToRoles();
    }
}
