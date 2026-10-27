<?php

namespace Modules\Appointment\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Appointment';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('Appointment Dashboard'),
            'icon' => '',
            'name' => 'appointment-dashboard',
            'parent' => 'dashboard',
            'order' => 150,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'appointment.dashboard',
            'module' => $module,
            'permission' => 'appointment dashboard manage'
        ]);
        $menu->add([
            'category' => 'Productivity',
            'title' => __('Appointment'),
            'icon' => 'calendar-time',
            'name' => 'appointment',
            'parent' => null,
            'order' => 1000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'appointment manage'
        ]);
        $menu->add([
            'category' => 'Productivity',
            'title' => __('Appointments'),
            'icon' => '',
            'name' => 'appointments',
            'parent' => 'appointment',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'appointments.index',
            'module' => $module,
            'permission' => 'appointments manage'
        ]);
        $menu->add([
            'category' => 'Productivity',
            'title' => __('Questions'),
            'icon' => '',
            'name' => 'questions',
            'parent' => 'appointment',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'questions.index',
            'module' => $module,
            'permission' => 'question manage'
        ]);
        $menu->add([
            'category' => 'Productivity',
            'title' => __('Schedule'),
            'icon' => '',
            'name' => 'schedule',
            'parent' => 'appointment',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'schedules.index',
            'module' => $module,
            'permission' => 'schedule manage'
        ]);
    }
}
