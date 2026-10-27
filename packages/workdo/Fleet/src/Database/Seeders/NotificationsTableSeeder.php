<?php

namespace Workdo\Fleet\Database\Seeders;

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

        $notifications = [
            'New Vehicle', 'New Booking', 'New Booking Payment'
        ];
        $permissions = [
            'vehicle manage',
            'booking manage',
            'payment booking manage'

        ];
        foreach ($notifications as $key => $n) {
            $ntfy = Notification::where('action', $n)->where('type', 'mail')->where('module', 'Fleet')->count();
            if ($ntfy == 0) {
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'Fleet';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}
