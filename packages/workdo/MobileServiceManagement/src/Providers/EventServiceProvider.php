<?php

namespace Workdo\MobileServiceManagement\Providers;

use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Workdo\MobileServiceManagement\Listeners\DataDefault;
use Workdo\MobileServiceManagement\Listeners\GiveRoleToPermission;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DefaultData::class => [
            DataDefault::class,
        ],
         GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],
    ];
}