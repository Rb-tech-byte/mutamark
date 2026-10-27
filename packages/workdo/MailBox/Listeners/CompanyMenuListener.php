<?php

namespace Modules\MailBox\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'MailBox';
        $menu = $event->menu;
        $menu->add([
            'category' => 'Communication',
            'title' =>__('EMail Box'),
            'icon' => 'mail',
            'name' => 'mailbox',
            'parent' => null,
            'order' => 1275,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'mailbox.index',
            'module' => $module,
            'permission' => 'Emailbox manage'
        ]);
    }
}
