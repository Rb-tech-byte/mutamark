<?php

namespace Modules\Appointment\Database\Seeders;

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
        Model::unguard();

        // $this->call("OthersTableSeeder");

        // email notification
        $notifications = [
            'Appointment Status', 'Appointment Send'
        ];
        $permissions = [
            'appointments manage', 'appointments manage'
        ];
        foreach ($notifications as $key => $n) {
            $ntfy = Notification::where('action', $n)->where('type', 'mail')->where('module', 'Appointment')->count();
            if ($ntfy == 0) {
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'Appointment';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}
