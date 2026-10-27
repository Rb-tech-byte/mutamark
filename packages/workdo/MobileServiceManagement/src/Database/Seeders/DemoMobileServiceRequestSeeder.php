<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use Workdo\MobileServiceManagement\Models\MobileServiceAssignTechnician;
use Workdo\MobileServiceManagement\Models\MobileServiceLiveTracking;
use Workdo\MobileServiceManagement\Models\MobileDeviceBrand;
use Workdo\MobileServiceManagement\Models\MobileTrackingStatus;
use Workdo\ProductService\Models\ProductServiceItem;
use App\Models\User;
use Carbon\Carbon;

class DemoMobileServiceRequestSeeder extends Seeder
{
    public function run($userId): void
    {
        $hasRequests = MobileServiceRequest::where('created_by', $userId)->exists();
        $hasTracking = MobileServiceLiveTracking::where('created_by', $userId)->exists();
        $hasTechnicians = MobileServiceAssignTechnician::where('created_by', $userId)->exists();

        if ($hasRequests && $hasTracking && $hasTechnicians) {
            return; // All three tables contain user data → skip seeding
        }
        
        if (!empty($userId)) {
            // Get required data
            $services = ProductServiceItem::where('created_by', $userId)->where('type', 'service')->get();
            $deviceBrands = MobileDeviceBrand::where('created_by', $userId)->get();
            $trackingStatuses = MobileTrackingStatus::where('created_by', $userId)->orderBy('order')->get();
            $technicians = User::where('created_by', $userId)->emp()->get();
            
            if ($services->isEmpty() || $deviceBrands->isEmpty()) {
                return; // Required data not available
            }

            if ($trackingStatuses->isEmpty()) {
                return; // No tracking statuses available
            }

            // 20 realistic service request records
            $requestRecords = [
                ['customer_name' => 'John Smith', 'email' => 'john.smith@gmail.com', 'mobile_no' => '1555012345', 'address' => '123 Main St, New York, NY 10001', 'priority' => 'standard', 'mobile_model' => 'iPhone 14 Pro', 'description' => 'Screen cracked after dropping the phone', 'special_requests' => 'Please backup data before repair', 'attachment' => '["mobile-service1.png"]', 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 1, 'date' => Carbon::now()->subDays(180)],
                ['customer_name' => 'Sarah Johnson', 'email' => 'sarah.johnson@yahoo.com', 'mobile_no' => '+442079460958', 'address' => '456 Oak Ave, London, UK', 'priority' => 'priority', 'mobile_model' => 'Samsung Galaxy S23', 'description' => 'Battery draining very fast', 'special_requests' => 'Need same day service if possible', 'attachment' => '["mobile-service2.png"]', 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 1, 'date' => Carbon::now()->subDays(171)],
                ['customer_name' => 'Mike Wilson', 'email' => 'mike.wilson@hotmail.com', 'mobile_no' => '+919876543210', 'address' => '789 Pine Rd, Mumbai, India', 'priority' => 'emergency', 'mobile_model' => 'Google Pixel 7', 'description' => 'Phone fell in water, not turning on', 'special_requests' => 'Data recovery is priority, phone replacement acceptable', 'attachment' => '["mobile-service3.png", "mobile-service4.png"]', 'is_approve' => 1, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(162)],
                ['customer_name' => 'Lisa Chen', 'email' => 'lisa.chen@outlook.com', 'mobile_no' => '+61298765432', 'address' => '321 Elm St, Sydney, Australia', 'priority' => 'standard', 'mobile_model' => 'OnePlus 11', 'description' => 'Charging port loose, not charging properly', 'special_requests' => 'Please use original parts only', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 0, 'date' => Carbon::now()->subDays(153)],
                ['customer_name' => 'David Brown', 'email' => 'david.brown@gmail.com', 'mobile_no' => '+81312345678', 'address' => '654 Maple Dr, Tokyo, Japan', 'priority' => 'priority', 'mobile_model' => 'iPhone 13', 'description' => 'Camera not focusing, blurry images', 'special_requests' => 'Professional photographer, need perfect camera quality', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 1, 'date' => Carbon::now()->subDays(144)],
                ['customer_name' => 'Emma Davis', 'email' => 'emma.davis@yahoo.com', 'mobile_no' => '+493012345678', 'address' => '987 Cedar Ln, Berlin, Germany', 'priority' => 'standard', 'mobile_model' => 'Xiaomi 13 Pro', 'description' => 'Apps crashing frequently, phone freezing', 'special_requests' => 'Keep all apps and settings intact', 'attachment' => null, 'is_approve' => 0, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(135)],
                ['customer_name' => 'Chris Lee', 'email' => 'chris.lee@gmail.com', 'mobile_no' => '+33142868365', 'address' => '147 Birch St, Paris, France', 'priority' => 'standard', 'mobile_model' => 'Samsung Galaxy Note 20', 'description' => 'Speaker crackling, poor sound quality', 'special_requests' => 'Test speaker quality thoroughly', 'attachment' => '["mobile-service5.png"]', 'is_approve' => 1, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(126)],
                ['customer_name' => 'Anna Garcia', 'email' => 'anna.garcia@hotmail.com', 'mobile_no' => '+390612345678', 'address' => '258 Willow Ave, Rome, Italy', 'priority' => 'priority', 'mobile_model' => 'iPhone 12', 'description' => 'Home button not responding consistently', 'special_requests' => 'Urgent repair needed for work', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 1, 'date' => Carbon::now()->subDays(117)],
                ['customer_name' => 'Tom Martinez', 'email' => 'tom.martinez@outlook.com', 'mobile_no' => '+971234567890', 'address' => '369 Spruce Rd, São Paulo, Brazil', 'priority' => 'emergency', 'mobile_model' => 'Huawei P50 Pro', 'description' => 'Screen completely shattered, touchscreen not working', 'special_requests' => 'Need screen protector after repair', 'attachment' => '["mobile-service6.png", "mobile-service7.png"]', 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 0, 'date' => Carbon::now()->subDays(108)],
                ['customer_name' => 'Kate Wilson', 'email' => 'kate.wilson@gmail.com', 'mobile_no' => '+34911234567', 'address' => '741 Ash Dr, Dubai, UAE', 'priority' => 'standard', 'mobile_model' => 'Oppo Find X5', 'description' => 'Microphone not working during calls', 'special_requests' => 'Test all call functions before return', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(99)],
                ['customer_name' => 'Alex Taylor', 'email' => 'alex.taylor@yahoo.com', 'mobile_no' => '+442079460876', 'address' => '852 Poplar St, Beijing, China', 'priority' => 'priority', 'mobile_model' => 'Vivo X90 Pro', 'description' => 'Phone storage corrupted, data recovery needed', 'special_requests' => 'Data recovery is most important', 'attachment' => null, 'is_approve' => 2, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(90)],
                ['customer_name' => 'Rachel White', 'email' => 'rachel.white@gmail.com', 'mobile_no' => '+74951234567', 'address' => '963 Hickory Ave, Moscow, Russia', 'priority' => 'standard', 'mobile_model' => 'Realme GT 3', 'description' => 'WiFi and cellular connectivity issues', 'special_requests' => 'Check network settings after repair', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 1, 'date' => Carbon::now()->subDays(81)],
                ['customer_name' => 'Steve Clark', 'email' => 'steve.clark@hotmail.com', 'mobile_no' => '+27112314567', 'address' => '159 Walnut Rd, Cape Town, South Africa', 'priority' => 'emergency', 'mobile_model' => 'Sony Xperia 1 IV', 'description' => 'Phone overheating, battery draining fast', 'special_requests' => 'Check for hardware damage due to overheating', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(72)],
                ['customer_name' => 'Nina Rodriguez', 'email' => 'nina.rodriguez@outlook.com', 'mobile_no' => '+82212345678', 'address' => '357 Cherry St, Seoul, South Korea', 'priority' => 'standard', 'mobile_model' => 'iPhone 14', 'description' => 'Back glass cracked but phone works fine', 'special_requests' => 'Only cosmetic repair needed', 'attachment' => null, 'is_approve' => 0, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(63)],
                ['customer_name' => 'Paul Anderson', 'email' => 'paul.anderson@gmail.com', 'mobile_no' => '+34911234567', 'address' => '468 Peach Ave, Madrid, Spain', 'priority' => 'priority', 'mobile_model' => 'Nothing Phone 2', 'description' => 'Headphone jack not working properly', 'special_requests' => 'Test with different headphones', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 0, 'date' => Carbon::now()->subDays(54)],
                ['customer_name' => 'Grace Kim', 'email' => 'grace.kim@yahoo.com', 'mobile_no' => '+1555100198', 'address' => '579 Plum Dr, Los Angeles, CA', 'priority' => 'standard', 'mobile_model' => 'Samsung Galaxy A54', 'description' => 'Previous repair issue returned, warranty claim', 'special_requests' => 'This is warranty repair, no charge expected', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(45)],
                ['customer_name' => 'Mark Thompson', 'email' => 'mark.thompson@gmail.com', 'mobile_no' => '+442079460876', 'address' => '680 Orange St, Manchester, UK', 'priority' => 'standard', 'mobile_model' => 'Motorola Edge 40', 'description' => 'Multiple devices need repair for business', 'special_requests' => 'Bulk repair discount requested', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 1, 'date' => Carbon::now()->subDays(36)],
                ['customer_name' => 'Sophie Miller', 'email' => 'sophie.miller@hotmail.com', 'mobile_no' => '+919876543287', 'address' => '791 Grape Ave, Delhi, India', 'priority' => 'priority', 'mobile_model' => 'iPhone 15 Pro', 'description' => 'Device randomly shuts down and restarts', 'special_requests' => 'Run full diagnostic before repair', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(27)],
                ['customer_name' => 'Ryan Harris', 'email' => 'ryan.harris@outlook.com', 'mobile_no' => '+6119876498', 'address' => '802 Lemon Rd, Melbourne, Australia', 'priority' => 'standard', 'mobile_model' => 'Google Pixel 8', 'description' => 'Need quote for screen and battery replacement', 'special_requests' => 'Please provide detailed cost breakdown', 'attachment' => null, 'is_approve' => 0, 'is_repair_end' => 0, 'payment_status' => 0, 'date' => Carbon::now()->subDays(18)],
                ['customer_name' => 'Amy Lewis', 'email' => 'amy.lewis@gmail.com', 'mobile_no' => '+81312345687', 'address' => '913 Lime St, Osaka, Japan', 'priority' => 'emergency', 'mobile_model' => 'OnePlus 12', 'description' => 'Urgent repair needed, traveling tomorrow', 'special_requests' => 'Express service needed, willing to pay extra', 'attachment' => null, 'is_approve' => 1, 'is_repair_end' => 1, 'payment_status' => 1, 'date' => Carbon::now()->subDays(9)]
            ];

            foreach ($requestRecords as $index => $record) {
                // Create service request
                $serviceRequest = MobileServiceRequest::create([
                    'service_id' => time() + $index,
                    'customer_name' => $record['customer_name'],
                    'email' => $record['email'],
                    'mobile_no' => $record['mobile_no'],
                    'address' => $record['address'],
                    'priority' => $record['priority'],
                    'mobile_model' => $record['mobile_model'],
                    'description' => $record['description'],
                    'special_requests' => $record['special_requests'] ?? null,
                    'attachment' => $record['attachment'] ?? null,
                    'mobile_service_id' => $services[$index % $services->count()]->id,
                    'device_brand' => $deviceBrands[$index % $deviceBrands->count()]->id,
                    'tracking_status' => $trackingStatuses->first()->id,
                    'tracking_status_log' => [$trackingStatuses->first()->id],
                    'is_approve' => $record['is_approve'],
                    'is_technician_asign' => $record['is_approve'] == 1 ? 1 : 0,
                    'is_repair_end' => $record['is_repair_end'],
                    'payment_status' => 0, // Set all to unpaid initially, payment seeder will update
                    'creator_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => $record['date'],
                    'updated_at' => $record['date'],
                ]);

                // Assign technician if approved
                if ($record['is_approve'] == 1 && !$technicians->isEmpty()) {
                    MobileServiceAssignTechnician::create([
                        'mobile_service_id' => $serviceRequest->id,
                        'technician_id' => $technicians[$index % $technicians->count()]->id,
                        'completion_date' => $record['date']->addDays(rand(3, 7)),
                        'creator_id' => $userId,
                        'created_by' => $userId,
                        'created_at' => $record['date'],
                        'updated_at' => $record['date'],
                    ]);
                }

                // Create tracking entries for approved requests
                if ($record['is_approve'] == 1) {
                    $statusesToComplete = $record['is_repair_end'] == 1 ? $trackingStatuses->count() : (($index % 3) + 1);
                    
                    for ($i = 0; $i < $statusesToComplete; $i++) {
                        MobileServiceLiveTracking::create([
                            'service_id' => $serviceRequest->service_id,
                            'tracking_status_id' => $trackingStatuses[$i]->id,
                            'date' => $record['date']->addHours($i * 6),
                            'creator_id' => $userId,
                            'created_by' => $userId,
                            'created_at' => $record['date']->addHours($i * 6),
                            'updated_at' => $record['date']->addHours($i * 6),
                        ]);
                    }

                    // Update tracking status and log
                    $completedStatuses = $trackingStatuses->take($statusesToComplete)->pluck('id')->toArray();
                    $serviceRequest->update([
                        'tracking_status' => end($completedStatuses),
                        'tracking_status_log' => $completedStatuses,
                    ]);
                }
            }
        }
    }
}