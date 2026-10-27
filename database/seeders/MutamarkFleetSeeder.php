<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Booking;
use Workdo\Fleet\Entities\Fuel;
use Workdo\Fleet\Entities\Maintenance;
use Workdo\Fleet\Entities\VehicleType;
use Workdo\Fleet\Entities\FuelType;
use Workdo\Fleet\Entities\MaintenanceType;

class MutamarkFleetSeeder extends Seeder
{
    public function run(): void
    {
        $companyUser = User::where('email', 'company@example.com')->first() ?? User::find(2);
        if (!$companyUser) {
            $this->command->error("Company user not found.");
            return;
        }

        $creatorId = $companyUser->id;
        $workspace = function_exists('getActiveWorkSpace') ? (getActiveWorkSpace() ?: 1) : 1;

        // 1. Vehicle Types
        $vTypes = [
            'Light Commercial Pickup (4x4)',
            'Heavy Regional Freight Truck (10 Ton)',
            'Utility Service Van',
            'Heavy Articulated Tipper / Hauler'
        ];
        $typeMap = [];
        foreach ($vTypes as $vt) {
            $obj = VehicleType::firstOrCreate(
                ['name' => $vt, 'created_by' => $creatorId],
                ['workspace' => $workspace]
            );
            $typeMap[$vt] = $obj->id;
        }

        // 2. Fuel Types
        $fTypes = [
            'Automotive Gas Oil (Diesel)',
            'Motor Super (Petrol)'
        ];
        $fuelMap = [];
        foreach ($fTypes as $ft) {
            $obj = FuelType::firstOrCreate(
                ['name' => $ft, 'created_by' => $creatorId],
                ['workspace' => $workspace]
            );
            $fuelMap[$ft] = $obj->id;
        }

        // 3. Maintenance Types
        $mTypes = [
            'Scheduled Routine Service',
            'Brakes & Suspension Overhaul',
            'Heavy Transmission & Drivetrain',
        ];
        $maintTypeMap = [];
        foreach ($mTypes as $mt) {
            $obj = MaintenanceType::firstOrCreate(
                ['name' => $mt, 'created_by' => $creatorId],
                ['workspace' => $workspace]
            );
            $maintTypeMap[$mt] = $obj->id;
        }

        // 3b. Driver Permit Types
        $licC = \Workdo\Fleet\Entities\License::firstOrCreate(
            ['name' => 'Class C Commercial Driver Permit', 'created_by' => $creatorId],
            ['workspace' => $workspace]
        );
        $licE = \Workdo\Fleet\Entities\License::firstOrCreate(
            ['name' => 'Class E Heavy Articulated Permit', 'created_by' => $creatorId],
            ['workspace' => $workspace]
        );

        // 4. Drivers
        $driversData = [
            [
                'name' => 'Juma Said Mwinyi',
                'phone' => '+255 754 112 334',
                'email' => 'juma.mwinyi@mutamark.co.tz',
                'lincese_number' => 'TZ-DL-994120',
                'lincese_type' => $licE->id,
                'expiry_date' => now()->addYears(2)->toDateString(),
                'join_date' => now()->subYears(2)->toDateString(),
                'address' => 'Temeke, Dar es Salaam',
                'driver_status' => 'Active',
            ],
            [
                'name' => 'Baraka Emmanuel Mtweve',
                'phone' => '+255 784 556 778',
                'email' => 'baraka.mtweve@mutamark.co.tz',
                'lincese_number' => 'TZ-DL-882194',
                'lincese_type' => $licC->id,
                'expiry_date' => now()->addYears(1)->toDateString(),
                'join_date' => now()->subYear()->toDateString(),
                'address' => 'Kinondoni, Dar es Salaam',
                'driver_status' => 'Active',
            ],
            [
                'name' => 'Hamisi Rajabu Kileo',
                'phone' => '+255 715 889 001',
                'email' => 'hamisi.kileo@mutamark.co.tz',
                'lincese_number' => 'TZ-DL-773412',
                'lincese_type' => $licE->id,
                'expiry_date' => now()->addYears(3)->toDateString(),
                'join_date' => now()->subMonths(18)->toDateString(),
                'address' => 'Ilala, Dar es Salaam',
                'driver_status' => 'Active',
            ],
            [
                'name' => 'Daudi Jackson Mushi',
                'phone' => '+255 768 443 210',
                'email' => 'daudi.mushi@mutamark.co.tz',
                'lincese_number' => 'TZ-DL-661209',
                'lincese_type' => $licC->id,
                'expiry_date' => now()->addYears(2)->toDateString(),
                'join_date' => now()->subMonths(6)->toDateString(),
                'address' => 'Ubungo, Dar es Salaam',
                'driver_status' => 'Active',
            ],
        ];

        $driverModels = [];
        foreach ($driversData as $dd) {
            $d = Driver::updateOrCreate(
                ['lincese_number' => $dd['lincese_number'], 'created_by' => $creatorId],
                array_merge($dd, ['workspace' => $workspace, 'user_id' => $creatorId])
            );
            $driverModels[] = $d;
        }

        // 5. Vehicles
        $vehiclesData = [
            [
                'name' => 'Toyota Hilux 4x4 Double Cabin (Field Engineering)',
                'lincense_plate' => 'T 482 DXK',
                'vehicle_type' => $typeMap['Light Commercial Pickup (4x4)'] ?? 1,
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'driver_name' => $driverModels[0]->id,
                'vehical_id_num' => 'MR0HA3CD8K104921',
                'model_year' => '2023',
                'seat_capacity' => 5,
                'status' => 'Available',
            ],
            [
                'name' => 'Isuzu Forward FVR 34 (10-Ton Regional Cargo Truck)',
                'lincense_plate' => 'T 823 EAB',
                'vehicle_type' => $typeMap['Heavy Regional Freight Truck (10 Ton)'] ?? 2,
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'driver_name' => $driverModels[1]->id,
                'vehical_id_num' => 'FVR34-778219401',
                'model_year' => '2022',
                'seat_capacity' => 3,
                'status' => 'On Trip',
            ],
            [
                'name' => 'Toyota Land Cruiser HZJ79 (Remote Site Support)',
                'lincense_plate' => 'T 194 CYZ',
                'vehicle_type' => $typeMap['Light Commercial Pickup (4x4)'] ?? 1,
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'driver_name' => $driverModels[2]->id,
                'vehical_id_num' => 'HZJ79-0091841',
                'model_year' => '2024',
                'seat_capacity' => 3,
                'status' => 'Available',
            ],
            [
                'name' => 'Scania G460 Heavy Hauler (Bulk Equipment Mover)',
                'lincense_plate' => 'T 650 DSB',
                'vehicle_type' => $typeMap['Heavy Articulated Tipper / Hauler'] ?? 4,
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'driver_name' => $driverModels[3]->id,
                'vehical_id_num' => 'YS2G4X200054921',
                'model_year' => '2021',
                'seat_capacity' => 2,
                'status' => 'Maintenance',
            ],
        ];

        $vehicleModels = [];
        foreach ($vehiclesData as $vd) {
            $v = Vehicle::updateOrCreate(
                ['lincense_plate' => $vd['lincense_plate'], 'created_by' => $creatorId],
                array_merge($vd, ['workspace' => $workspace])
            );
            $vehicleModels[] = $v;
        }

        // 6. Bookings / Trip Dispatches
        Booking::where('created_by', $creatorId)->delete();

        $bookingsData = [
            [
                'customer_name' => 'Tanzania Telecommunications Corp (TTCL)',
                'vehicle_name' => $vehicleModels[1]->id,
                'driver_name' => $driverModels[1]->id,
                'trip_type' => 'Heavy Regional Freight',
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'start_address' => 'Dar es Salaam Central Warehouse',
                'end_address' => 'Dodoma Government City Hub',
                'total_price' => 1450000.00,
                'status' => 'On Trip',
                'notes' => 'Transport of 40km armored optical fiber reels and server rack units.',
            ],
            [
                'customer_name' => 'Vodacom Tanzania PLC',
                'vehicle_name' => $vehicleModels[0]->id,
                'driver_name' => $driverModels[0]->id,
                'trip_type' => 'Site Engineering Dispatch',
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
                'start_address' => 'Dar es Salaam Workshop',
                'end_address' => 'Morogoro Microwave Repeater Station',
                'total_price' => 750000.00,
                'status' => 'Approved',
                'notes' => 'Field engineers technical equipment and testing analyzers.',
            ],
            [
                'customer_name' => 'National Social Security Fund (NSSF)',
                'vehicle_name' => $vehicleModels[2]->id,
                'driver_name' => $driverModels[2]->id,
                'trip_type' => 'Branch Network Equipment Delivery',
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->subDays(4)->toDateString(),
                'start_address' => 'Dar es Salaam HQ',
                'end_address' => 'Tanga Regional Office',
                'total_price' => 950000.00,
                'status' => 'Completed',
                'notes' => 'Successful delivery of 12x Cisco Core Switches.',
            ],
            [
                'customer_name' => 'Internal Depot Logistics',
                'vehicle_name' => $vehicleModels[3]->id,
                'driver_name' => $driverModels[3]->id,
                'trip_type' => 'Inter-Warehouse Stock Transfer',
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->subDays(8)->toDateString(),
                'start_address' => 'Dar es Salaam Port Depot',
                'end_address' => 'Arusha Regional Distribution Hub',
                'total_price' => 2200000.00,
                'status' => 'Completed',
                'notes' => 'Consolidated pallet delivery of network cabinets and UPS units.',
            ],
        ];

        foreach ($bookingsData as $bd) {
            Booking::create(array_merge($bd, [
                'workspace' => $workspace,
                'created_by' => $creatorId,
            ]));
        }

        // 7. Fuel Consumption Logs
        Fuel::where('created_by', $creatorId)->delete();

        $fuelsData = [
            [
                'vehicle_name' => $vehicleModels[0]->id,
                'driver_name' => $driverModels[0]->id,
                'fill_date' => now()->subDays(2)->toDateString(),
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'quantity' => 75,
                'cost' => 3120.00,
                'total_cost' => 75 * 3120.00, // 234,000 TZS
                'odometer_reading' => 54100,
                'notes' => 'Puma Energy - Mbagala Station',
            ],
            [
                'vehicle_name' => $vehicleModels[1]->id,
                'driver_name' => $driverModels[1]->id,
                'fill_date' => now()->subDay()->toDateString(),
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'quantity' => 260,
                'cost' => 3120.00,
                'total_cost' => 260 * 3120.00, // 811,200 TZS
                'odometer_reading' => 89350,
                'notes' => 'TotalEnergies - Ubungo Depot',
            ],
            [
                'vehicle_name' => $vehicleModels[2]->id,
                'driver_name' => $driverModels[2]->id,
                'fill_date' => now()->subDays(5)->toDateString(),
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'quantity' => 110,
                'cost' => 3120.00,
                'total_cost' => 110 * 3120.00, // 343,200 TZS
                'odometer_reading' => 32400,
                'notes' => 'Oryx Energies - Tazara',
            ],
            [
                'vehicle_name' => $vehicleModels[3]->id,
                'driver_name' => $driverModels[3]->id,
                'fill_date' => now()->subDays(9)->toDateString(),
                'fuel_type' => $fuelMap['Automotive Gas Oil (Diesel)'] ?? 1,
                'quantity' => 380,
                'cost' => 3120.00,
                'total_cost' => 380 * 3120.00, // 1,185,600 TZS
                'odometer_reading' => 195200,
                'notes' => 'Mount Meru - Morogoro Highway',
            ],
        ];

        foreach ($fuelsData as $fd) {
            Fuel::create(array_merge($fd, [
                'workspace' => $workspace,
                'created_by' => $creatorId,
            ]));
        }

        // 8. Vehicle Maintenance Logs
        Maintenance::where('created_by', $creatorId)->delete();

        $maintenancesData = [
            [
                'vehicle_name' => $vehicleModels[3]->id,
                'service_type' => 'Heavy Drivetrain & Clutch Plate Replacement',
                'service_name' => 'Scania Tanzania Authorized Workshop',
                'maintenance_date' => now()->subDays(2)->toDateString(),
                'priority' => 'High',
                'charge' => 1850000.00,
                'total_cost' => 1850000.00,
                'notes' => 'Full replacement of clutch assembly and pneumatic brake lines.',
            ],
            [
                'vehicle_name' => $vehicleModels[0]->id,
                'service_type' => 'Routine 50,000 KM Engine & Filter Service',
                'service_name' => 'Toyota Tanzania (Dar Central)',
                'maintenance_date' => now()->subDays(15)->toDateString(),
                'priority' => 'Normal',
                'charge' => 420000.00,
                'total_cost' => 420000.00,
                'notes' => 'Oil filter, fuel filter, genuine synthetic engine oil.',
            ],
            [
                'vehicle_name' => $vehicleModels[1]->id,
                'service_type' => 'Suspension Bushings & Steering Alignment',
                'service_name' => 'Isuzu Diamond Motors Service Center',
                'maintenance_date' => now()->subDays(20)->toDateString(),
                'priority' => 'Normal',
                'charge' => 680000.00,
                'total_cost' => 680000.00,
                'notes' => 'Pre-trip safety inspection and wheel balancing.',
            ],
        ];

        foreach ($maintenancesData as $md) {
            Maintenance::create(array_merge($md, [
                'workspace' => $workspace,
                'created_by' => $creatorId,
            ]));
        }

        $this->command->info("Mutamark Fleet Management & Dispatch data seeded successfully with realistic Tanzanian vehicles, drivers, fuel logs, and maintenance in TZS!");
    }
}
