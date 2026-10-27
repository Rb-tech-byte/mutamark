<?php

namespace Modules\Inventory\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Inventory';
        $menu = $event->menu;
        $menu->add([
            'category' => 'Sales',
            'title' => __('Inventory'),
            'icon' => 'file-invoice',
            'name' => 'inventory',
            'parent' => null,
            'order' => 850,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'inventory.index',
            'module' => $module,
            'permission' => 'inventory manage'
        ]);
    }
}

