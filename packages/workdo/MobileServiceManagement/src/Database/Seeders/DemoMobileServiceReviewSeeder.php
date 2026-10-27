<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileServiceReview;
use Workdo\ProductService\Models\ProductServiceItem;
use Carbon\Carbon;

class DemoMobileServiceReviewSeeder extends Seeder
{
    public function run($userId): void
    {
        if (MobileServiceReview::where('created_by', $userId)->exists()) {
            return; // Skip seeding if data already exists
        }
        if (!empty($userId)) {
            // Get existing mobile services to link reviews
            $services = ProductServiceItem::where('created_by', $userId)
                ->where('type', 'service')
                ->get();
            
            if ($services->isEmpty()) {
                return; // No services available to link reviews
            }

            // 20 realistic review records ordered oldest to newest (6 months)
            $reviewRecords = [
                ['name' => 'Michael Chen', 'email' => 'michael.chen@gmail.com', 'rating' => '5', 'review' => 'Excellent phone repair service! My iPhone screen was replaced perfectly and the technician explained the entire process. Professional work and fair pricing. Highly recommended!', 'date' => Carbon::now()->subDays(180)],
                ['name' => 'Emma Rodriguez', 'email' => 'emma.rodriguez@yahoo.com', 'rating' => '4', 'review' => 'Great experience with tablet repair. The staff was knowledgeable and kept me updated throughout the process. Quick turnaround time and quality work. Will return for future needs.', 'date' => Carbon::now()->subDays(171)],
                ['name' => 'James Patterson', 'email' => 'james.patterson@hotmail.com', 'rating' => '5', 'review' => 'Outstanding service for my Samsung Galaxy repair! Water damage recovery was successful and all my data was saved. The technician was skilled and professional. Excellent customer service!', 'date' => Carbon::now()->subDays(162)],
                ['name' => 'Sophie Williams', 'email' => 'sophie.williams@outlook.com', 'rating' => '3', 'review' => 'The repair was completed but took longer than expected. Service quality was decent but communication could be improved. Final result was satisfactory for the price paid.', 'date' => Carbon::now()->subDays(153)],
                ['name' => 'Daniel Martinez', 'email' => 'daniel.martinez@gmail.com', 'rating' => '5', 'review' => 'Amazing battery replacement service! My phone now lasts all day again. The technician was efficient and provided warranty on the new battery. Great value for money!', 'date' => Carbon::now()->subDays(144)],
                ['name' => 'Isabella Thompson', 'email' => 'isabella.thompson@yahoo.com', 'rating' => '4', 'review' => 'Professional charging port repair service. The issue was diagnosed quickly and fixed properly. Clean workspace and friendly staff. Reasonable pricing for quality work. Recommended!', 'date' => Carbon::now()->subDays(135)],
                ['name' => 'Alexander Kim', 'email' => 'alexander.kim@gmail.com', 'rating' => '5', 'review' => 'Exceptional camera repair service for my smartphone! The focus issue was resolved completely and image quality is now perfect. Knowledgeable technician and excellent customer care.', 'date' => Carbon::now()->subDays(126)],
                ['name' => 'Olivia Garcia', 'email' => 'olivia.garcia@hotmail.com', 'rating' => '2', 'review' => 'Unfortunately, the repair did not meet expectations. The issue returned after few days and had to bring it back. Service was slow and communication was poor.', 'date' => Carbon::now()->subDays(117)],
                ['name' => 'Ryan Johnson', 'email' => 'ryan.johnson@outlook.com', 'rating' => '4', 'review' => 'Good speaker repair service. Sound quality is now clear and loud. The technician tested everything thoroughly before returning the device. Fair pricing and professional service.', 'date' => Carbon::now()->subDays(108)],
                ['name' => 'Ava Wilson', 'email' => 'ava.wilson@gmail.com', 'rating' => '5', 'review' => 'Perfect screen replacement service! The new display looks original and touch sensitivity is excellent. Quick service and competitive pricing. Will definitely recommend to friends and family.', 'date' => Carbon::now()->subDays(99)],
                ['name' => 'Noah Brown', 'email' => 'noah.brown@yahoo.com', 'rating' => '4', 'review' => 'Satisfied with the motherboard repair service. Complex issue was resolved efficiently and device performance is now stable. Professional diagnosis and skilled repair work. Good experience overall.', 'date' => Carbon::now()->subDays(90)],
                ['name' => 'Mia Davis', 'email' => 'mia.davis@gmail.com', 'rating' => '5', 'review' => 'Excellent software troubleshooting service! All bugs were fixed and phone performance improved significantly. The technician was patient and explained all the solutions clearly. Outstanding service!', 'date' => Carbon::now()->subDays(81)],
                ['name' => 'Ethan Miller', 'email' => 'ethan.miller@hotmail.com', 'rating' => '3', 'review' => 'Average service experience. The repair was completed successfully but waiting time was longer than promised. Staff was helpful but facility could be more organized. Results were acceptable.', 'date' => Carbon::now()->subDays(72)],
                ['name' => 'Charlotte Anderson', 'email' => 'charlotte.anderson@outlook.com', 'rating' => '5', 'review' => 'Incredible data recovery service! Thought all my photos were lost but they recovered everything from my damaged phone. Professional team and reasonable pricing. Truly grateful for their expertise!', 'date' => Carbon::now()->subDays(63)],
                ['name' => 'Lucas Taylor', 'email' => 'lucas.taylor@gmail.com', 'rating' => '4', 'review' => 'Great microphone repair service. Voice calls are now crystal clear and recording quality is excellent. Quick diagnosis and efficient repair process. Satisfied with the professional service provided.', 'date' => Carbon::now()->subDays(54)],
                ['name' => 'Amelia Thomas', 'email' => 'amelia.thomas@yahoo.com', 'rating' => '5', 'review' => 'Outstanding comprehensive device checkup service! Multiple issues were identified and fixed in single visit. Transparent pricing and detailed explanation of all repairs. Excellent customer service experience!', 'date' => Carbon::now()->subDays(45)],
                ['name' => 'Benjamin White', 'email' => 'benjamin.white@gmail.com', 'rating' => '4', 'review' => 'Excellent home button repair service. Functionality is now perfect and response is immediate. Clean work environment and professional staff. Reasonable pricing for quality repair work. Recommended!', 'date' => Carbon::now()->subDays(36)],
                ['name' => 'Harper Jackson', 'email' => 'harper.jackson@hotmail.com', 'rating' => '5', 'review' => 'Perfect virus removal and security setup service! Phone is now running smoothly and all malware was eliminated. The technician provided helpful security tips. Professional and thorough service!', 'date' => Carbon::now()->subDays(27)],
                ['name' => 'Mason Harris', 'email' => 'mason.harris@outlook.com', 'rating' => '4', 'review' => 'Very satisfied with the network connectivity repair. WiFi and cellular issues were resolved completely. Good diagnostic skills and efficient repair process. Fair pricing and professional service delivery.', 'date' => Carbon::now()->subDays(18)],
                ['name' => 'Evelyn Martin', 'email' => 'evelyn.martin@gmail.com', 'rating' => '5', 'review' => 'Exceptional device optimization service! Phone performance improved dramatically and battery life extended significantly. Knowledgeable technician and excellent customer care. Worth every penny spent on service!', 'date' => Carbon::now()->subDays(9)]
            ];

            foreach ($reviewRecords as $record) {
                // Select random service for review
                $selectedService = $services->random();

                MobileServiceReview::create([
                    'name' => $record['name'],
                    'email' => $record['email'],
                    'mobile_service_id' => $selectedService->id,
                    'rating' => $record['rating'],
                    'review' => $record['review'],
                    'created_by' => $userId,
                    'created_at' => $record['date'],
                    'updated_at' => $record['date'],
                ]);
            }
        }
    }
}