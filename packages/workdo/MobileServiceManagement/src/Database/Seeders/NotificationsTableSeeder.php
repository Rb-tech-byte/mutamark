<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $notifications = [
            'New Mobile Service Request',
            'Service Request Accept',
            'Service Request Reject',
        ];

        $permissions = [
            'create-mobile-service-requests',
            'approve-mobile-pending-requests',
            'reject-mobile-pending-requests',
        ];

        foreach ($notifications as $key => $n) {
            $ntfy = Notification::where('action', $n)->where('type', 'mail')->where('module', 'MobileServiceManagement')->count();
            if ($ntfy == 0) {
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'MobileServiceManagement';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}