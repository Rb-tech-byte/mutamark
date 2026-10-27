<?php

namespace Modules\DoubleEntry\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'DoubleEntry';
        $menu = $event->menu;
        $menu->add([
            'title' => 'Journal Settings',
            'name' => 'journal-setting',
            'order' => 120,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'journal-sidenav',
            'module' => $module,
            'permission' => 'journalentry manage'
        ]);


    }
}
