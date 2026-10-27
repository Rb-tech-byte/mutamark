<?php

namespace Modules\CustomField\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'CustomField';
        $menu = $event->menu;
        $menu->add([
            'category' => 'Settings',
            'title' => __('Custom Field'),
            'icon' => 'circle-plus',
            'name' => 'customfield',
            'parent' => null,
            'order' => 750,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'custom-field.index',
            'module' => $module,
            'permission' => 'customfield manage'
        ]);




    }
}
