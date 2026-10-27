<?php

namespace Workdo\Fleet\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'Fleet';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Fleet Settings'),
            'name' => 'fleet',
            'order' => 370,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'fleet',
            'module' => $module,
            'permission' => 'fleet manage'
        ]);
    }
}
