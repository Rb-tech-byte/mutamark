<?php

namespace Modules\FileSharing\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'FileSharing';
        $menu = $event->menu;
        $menu->add([
            'category' => 'Productivity',
            'title' => __('File Sharing'),
            'icon' => 'file',
            'name' => 'filesharing',
            'parent' => null,
            'order' => 1200,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'filesharing manage'
        ]);
        $menu->add([
            'category' => 'Productivity',
            'title' => __('Files'),
            'icon' => '',
            'name' => 'Files',
            'parent' => 'filesharing',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'files.index',
            'module' => $module,
            'permission' => 'files manage'
        ]);
        $menu->add([
            'category' => 'Productivity',
            'title' => __('Download'),
            'icon' => '',
            'name' => 'download',
            'parent' => 'filesharing',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'download-detailes.index',
            'module' => $module,
            'permission' => 'downloads manage'
        ]);
    }
}
