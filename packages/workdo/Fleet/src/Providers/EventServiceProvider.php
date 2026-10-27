<?php

namespace Workdo\Fleet\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use Workdo\Fleet\Listeners\CompanyMenuListener;
use Workdo\Fleet\Listeners\CompanySettingListener;
use Workdo\Fleet\Listeners\CompanySettingMenuListener;
use App\Events\CreateInvoice;
use Workdo\Fleet\Listeners\CreateInvoiceLis;
use App\Events\DuplicateInvoice;
use Workdo\Fleet\Listeners\DuplicateInvoiceLis;
use App\Events\GivePermissionToRole;
use Workdo\Fleet\Listeners\GiveRoleToPermission;
use App\Events\UpdateInvoice;
use Workdo\Fleet\Listeners\UpdateInvoiceLis;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        CreateInvoice::class => [
            CreateInvoiceLis::class,
        ],
        DuplicateInvoice::class => [
            DuplicateInvoiceLis::class,
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],
        UpdateInvoice::class => [
            UpdateInvoiceLis::class,
        ],
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
