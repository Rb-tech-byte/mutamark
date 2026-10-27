<?php

namespace Workdo\Fleet\Listeners;

use App\Events\GivePermissionToRole;
use Workdo\Fleet\Entities\FleetUtility;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GiveRoleToPermission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(GivePermissionToRole $event)
    {
        $role_id = $event->role_id;
        $rolename = $event->rolename;
        $user_module = is_array($event->user_module) ? $event->user_module : (!empty($event->user_module) ? explode(',', $event->user_module) : []);
        if (!empty($user_module)) {
            if (in_array("Fleet", $user_module)) {
                FleetUtility::GivePermissionToRoles($role_id, $rolename);
            }
        }
    }
}
