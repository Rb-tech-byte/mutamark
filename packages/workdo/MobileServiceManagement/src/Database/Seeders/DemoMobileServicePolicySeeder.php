<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileServicePolicy;
use Carbon\Carbon;

class DemoMobileServicePolicySeeder extends Seeder
{
    public function run($userId): void
    {
        if (MobileServicePolicy::where('created_by', $userId)->exists()) {
            return; // Skip seeding if data already exists
        }
        if (!empty($userId)) {
            // 20 realistic service policy records ordered oldest to newest (6 months)
            $policyRecords = [
                ['policy_name' => 'Standard Warranty', 'description' => 'Basic warranty coverage for manufacturing defects and hardware failures within the first year of purchase. Includes free diagnostics and repair services.', 'response_time' => '24:00', 'resolution_time' => '72:00', 'date' => Carbon::now()->subDays(180)],
                ['policy_name' => 'Extended Warranty', 'description' => 'Extended warranty coverage for up to 2 years including accidental damage protection and priority customer support with dedicated service representatives.', 'response_time' => '12:00', 'resolution_time' => '48:00', 'date' => Carbon::now()->subDays(171)],
                ['policy_name' => 'Premium Support', 'description' => 'Priority support with dedicated technician and same-day service for critical issues. Includes 24/7 customer support and emergency repair services.', 'response_time' => '2:00', 'resolution_time' => '24:00', 'date' => Carbon::now()->subDays(162)],
                ['policy_name' => 'Screen Protection Plan', 'description' => 'Comprehensive screen damage coverage including cracks, scratches, and display issues. Covers unlimited screen repairs with premium quality replacement parts.', 'response_time' => '6:00', 'resolution_time' => '12:00', 'date' => Carbon::now()->subDays(153)],
                ['policy_name' => 'Water Damage Coverage', 'description' => 'Protection against liquid damage and moisture-related hardware failures. Includes data recovery services and component replacement with certified technicians.', 'response_time' => '48:00', 'resolution_time' => '96:00', 'date' => Carbon::now()->subDays(144)],
                ['policy_name' => 'Battery Replacement Policy', 'description' => 'Comprehensive battery health monitoring and replacement service. Covers battery degradation issues and provides high-capacity replacement batteries with extended warranty.', 'response_time' => '8:00', 'resolution_time' => '24:00', 'date' => Carbon::now()->subDays(135)],
                ['policy_name' => 'Enterprise Support Package', 'description' => 'Business-focused support package for corporate clients. Includes bulk device management, priority repairs, and dedicated account management for seamless operations.', 'response_time' => '4:00', 'resolution_time' => '12:00', 'date' => Carbon::now()->subDays(126)],
                ['policy_name' => 'Student Discount Plan', 'description' => 'Special pricing and support for students and educational institutions. Includes discounted repair services and flexible payment options for budget-conscious customers.', 'response_time' => '24:00', 'resolution_time' => '48:00', 'date' => Carbon::now()->subDays(117)],
                ['policy_name' => 'Senior Citizen Support', 'description' => 'Dedicated support program for senior citizens with simplified processes and patient customer service. Includes home pickup and delivery services.', 'response_time' => '12:00', 'resolution_time' => '36:00', 'date' => Carbon::now()->subDays(108)],
                ['policy_name' => 'Data Recovery Service', 'description' => 'Professional data recovery service for damaged or corrupted devices. Includes secure data extraction and transfer to new devices with privacy protection.', 'response_time' => '24:00', 'resolution_time' => '120:00', 'date' => Carbon::now()->subDays(99)],
                ['policy_name' => 'Express Repair Service', 'description' => 'Fast-track repair service for urgent cases with same-day completion guarantee. Includes priority queue processing and expedited parts procurement for quick turnaround.', 'response_time' => '1:00', 'resolution_time' => '8:00', 'date' => Carbon::now()->subDays(90)],
                ['policy_name' => 'Camera Module Protection', 'description' => 'Specialized coverage for camera-related issues including lens damage, focus problems, and image quality degradation. Covers professional-grade camera repairs and calibration.', 'response_time' => '12:00', 'resolution_time' => '48:00', 'date' => Carbon::now()->subDays(81)],
                ['policy_name' => 'Software Support Package', 'description' => 'Comprehensive software troubleshooting and optimization service. Includes virus removal, performance tuning, and operating system updates with technical guidance.', 'response_time' => '6:00', 'resolution_time' => '24:00', 'date' => Carbon::now()->subDays(72)],
                ['policy_name' => 'Charging Port Repair Plan', 'description' => 'Specialized service for charging port issues including loose connections, corrosion damage, and compatibility problems. Includes cable and adapter replacement services.', 'response_time' => '8:00', 'resolution_time' => '24:00', 'date' => Carbon::now()->subDays(63)],
                ['policy_name' => 'Audio System Coverage', 'description' => 'Complete audio system repair service covering speakers, microphones, and headphone jacks. Includes sound quality testing and acoustic calibration services.', 'response_time' => '12:00', 'resolution_time' => '36:00', 'date' => Carbon::now()->subDays(54)],
                ['policy_name' => 'Network Connectivity Support', 'description' => 'Specialized service for network-related issues including WiFi, Bluetooth, and cellular connectivity problems. Includes antenna repair and signal optimization services.', 'response_time' => '6:00', 'resolution_time' => '48:00', 'date' => Carbon::now()->subDays(45)],
                ['policy_name' => 'Vintage Device Restoration', 'description' => 'Specialized service for older and discontinued mobile devices. Includes rare parts sourcing, compatibility upgrades, and preservation services for collector devices.', 'response_time' => '72:00', 'resolution_time' => '168:00', 'date' => Carbon::now()->subDays(36)],
                ['policy_name' => 'Gaming Performance Package', 'description' => 'Performance optimization service for mobile gaming enthusiasts. Includes thermal management, processor tuning, and graphics enhancement for optimal gaming experience.', 'response_time' => '12:00', 'resolution_time' => '48:00', 'date' => Carbon::now()->subDays(27)],
                ['policy_name' => 'Environmental Protection Plan', 'description' => 'Eco-friendly repair service using sustainable practices and recycled components. Includes proper disposal of damaged parts and environmental impact reduction initiatives.', 'response_time' => '24:00', 'resolution_time' => '72:00', 'date' => Carbon::now()->subDays(18)],
                ['policy_name' => 'Unlimited Repair Subscription', 'description' => 'Monthly subscription service providing unlimited repairs and maintenance. Includes regular device health checkups, preventive maintenance, and priority customer support access.', 'response_time' => '4:00', 'resolution_time' => '24:00', 'date' => Carbon::now()->subDays(9)]
            ];

            foreach ($policyRecords as $record) {
                MobileServicePolicy::create([
                    'policy_name' => $record['policy_name'],
                    'description' => $record['description'],
                    'response_time' => $record['response_time'],
                    'resolution_time' => $record['resolution_time'],
                    'creator_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => $record['date'],
                    'updated_at' => $record['date'],
                ]);
            }
        }
    }
}