<?php

namespace Modules\Contract\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'Contract';
        $menu = $event->menu;
        $menu->add([
            'title' => 'Contract Settings',
            'name' => 'contract-setting',
            'order' => 310,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'contract-sidenav',
            'module' => $module,
            'permission' => 'contract manage'
        ]);
    }
}
