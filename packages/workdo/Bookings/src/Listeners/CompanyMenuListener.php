<?php

namespace Workdo\Bookings\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Bookings';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => 'Bookings Dashboard',
            'icon' => '',
            'name' => 'bookings-dashboard',
            'parent' => 'dashboard',
            'order' => 175,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'bookings.dashboard',
            'module' => $module,
            'permission' => 'bookings dashboard manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => 'Bookings',
            'icon' => 'book',
            'name' => 'bookings',
            'parent' => null,
            'order' => 696,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'bookings manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => 'Items',
            'icon' => '',
            'name' => 'items',
            'parent' => 'bookings',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'booking-items.index',
            'module' => $module,
            'permission' => 'bookings items manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => 'Package',
            'icon' => '',
            'name' => 'package',
            'parent' => 'bookings',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'booking-package.index',
            'module' => $module,
            'permission' => 'bookings package manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => 'Customers',
            'icon' => '',
            'name' => 'customers',
            'parent' => 'bookings',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'booking-customers.index',
            'module' => $module,
            'permission' => 'bookingcustomers manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => 'Appointments',
            'icon' => '',
            'name' => 'appointments',
            'parent' => 'bookings',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'booking-appointments.index',
            'module' => $module,
            'permission' => 'bookings appointments manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => 'Staff',
            'icon' => '',
            'name' => 'staff',
            'parent' => 'bookings',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'booking-staff.index',
            'module' => $module,
            'permission' => 'booking staffs manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => __('System Setup'),
            'icon' => '',
            'name' => 'bookings system setup',
            'parent' => 'bookings',
            'order' => 35,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'bookings system-setup manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => __('Business Hours'),
            'icon' => '',
            'name' => 'business hours',
            'parent' => 'bookings system setup',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'bookings-business-hours.index',
            'module' => $module,
            'permission' => 'bookings business hours manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => __('Location'),
            'icon' => '',
            'name' => 'location',
            'parent' => 'bookings system setup',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'bookings-location.index',
            'module' => $module,
            'permission' => 'bookings location manage'
        ]);
        $menu->add([
            'category' => 'eCommerce',
            'title' => __('Extra Services'),
            'icon' => '',
            'name' => 'extra services',
            'parent' => 'bookings system setup',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'bookings-extra-services.index',
            'module' => $module,
            'permission' => 'bookings extra services manage'
        ]);


    }
}
