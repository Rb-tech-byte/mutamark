<?php

namespace Modules\WhatsAppAPI\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'WhatsAppAPI';
        $menu = $event->menu;
        $menu->add([
            'title' => __('WhatsApp API Settings'),
            'name' => '',
            'order' => 560,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'whatsappapi-sidenav',
            'module' => $module,
            'permission' => 'whatsappapi manage'
        ]);
    }
}
