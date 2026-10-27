<?php

namespace Workdo\Fleet\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Fleet';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => 'Fleet Dashboard',
            'icon' => '',
            'name' => 'fleet-dashboard',
            'parent' => 'dashboard',
            'order' => 120,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'fleet.dashboard',
            'module' => $module,
            'permission' => 'fleet dashboard manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Fleet'),
            'icon' => 'car',
            'name' => 'fleet',
            'parent' => null,
            'order' => 675,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'fleet manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Driver'),
            'icon' => '',
            'name' => 'driver',
            'parent' => 'fleet',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'driver.index',
            'module' => $module,
            'permission' => 'driver manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Customer'),
            'icon' => '',
            'name' => 'customer',
            'parent' => 'fleet',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'fleet_customer.index',
            'module' => $module,
            'permission' => 'fleet customer manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Vehicle'),
            'icon' => '',
            'name' => 'vehicle',
            'parent' => 'fleet',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'vehicle.index',
            'module' => $module,
            'permission' => 'vehicle manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Log Book'),
            'icon' => '',
            'name' => 'logbook',
            'parent' => 'fleet',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'logbook.index',
            'module' => $module,
            'permission' => 'fleet logbook manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Booking'),
            'icon' => '',
            'name' => 'booking',
            'parent' => 'fleet',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'booking.index',
            'module' => $module,
            'permission' => 'booking manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Availability'),
            'icon' => '',
            'name' => 'availability',
            'parent' => 'fleet',
            'order' => 35,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'availability.index',
            'module' => $module,
            'permission' => 'fleetavailability manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Insurance'),
            'icon' => '',
            'name' => 'insurance',
            'parent' => 'fleet',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'insurance.index',
            'module' => $module,
            'permission' => 'insurance manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Maintenance'),
            'icon' => '',
            'name' => 'maintenance',
            'parent' => 'fleet',
            'order' => 45,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'maintenance.index',
            'module' => $module,
            'permission' => 'maintenance manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Fuel History'),
            'icon' => '',
            'name' => 'fuel-history',
            'parent' => 'fleet',
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'fuel.index',
            'module' => $module,
            'permission' => 'fuel manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Report'),
            'icon' => '',
            'name' => 'fleet-reports',
            'parent' => 'fleet',
            'order' => 55,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'fleet report manage'
        ]);

        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Maintenance Report'),
            'icon' => '',
            'name' => 'maintenance-report',
            'parent' => 'fleet-reports',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'maintenance_report.index',
            'module' => $module,
            'permission' => 'report maintenance manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('Fuel history Report'),
            'icon' => '',
            'name' => 'fuel-history-report',
            'parent' => 'fleet-reports',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'fuel_history_report.index',
            'module' => $module,
            'permission' => 'report fuelhistoryreport manage'
        ]);
        $menu->add([
            'category' => 'Vehicle',
            'title' => __('System Setup'),
            'icon' => '',
            'name' => 'system-setup',
            'parent' => 'fleet',
            'order' => 55,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'license.index',
            'module' => $module,
            'permission' => 'license manage'
        ]);
    }
}
