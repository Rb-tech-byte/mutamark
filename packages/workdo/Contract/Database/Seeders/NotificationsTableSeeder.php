<?php

namespace Modules\Contract\Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notifications = [
            'Contract'
        ];
        $permissions = [
            'contract manage',

        ];
        foreach($notifications as $key=>$n){
            $ntfy = Notification::where('action',$n)->where('type','mail')->where('module','Contract')->count();
            if($ntfy == 0){
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'Contract';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}
