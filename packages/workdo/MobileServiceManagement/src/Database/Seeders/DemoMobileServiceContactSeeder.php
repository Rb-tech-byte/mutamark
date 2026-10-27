<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileServiceContact;
use Carbon\Carbon;

class DemoMobileServiceContactSeeder extends Seeder
{
    public function run($userId): void
    {
        if (MobileServiceContact::where('created_by', $userId)->exists()) {
            return; // Skip seeding if data already exists
        }
        if (!empty($userId)) {
            // 20 realistic contact records ordered oldest to newest (6 months)
            $contactRecords = [
                ['first_name' => 'David', 'last_name' => 'Thompson', 'email' => 'david.thompson@gmail.com', 'phone' => '1555012345', 'subject' => 'Screen Repair Inquiry', 'message' => 'Hello, I need to get my iPhone 12 screen repaired. It has multiple cracks and touch sensitivity issues. Can you provide a quote and estimated repair time?', 'date' => Carbon::now()->subDays(180)],
                ['first_name' => 'Sarah', 'last_name' => 'Mitchell', 'email' => 'sarah.mitchell@yahoo.com', 'phone' => '+442079460958', 'subject' => 'Battery Replacement Service', 'message' => 'My Samsung Galaxy S21 battery drains very quickly now. I would like to know about battery replacement options and pricing. Is same-day service available?', 'date' => Carbon::now()->subDays(171)],
                ['first_name' => 'Michael', 'last_name' => 'Rodriguez', 'email' => 'michael.rodriguez@hotmail.com', 'phone' => '+919876543210', 'subject' => 'Water Damage Recovery', 'message' => 'My phone fell in water yesterday and now it won\'t turn on. I have important photos and contacts that I need to recover. Can you help with data recovery?', 'date' => Carbon::now()->subDays(162)],
                ['first_name' => 'Jessica', 'last_name' => 'Chen', 'email' => 'jessica.chen@outlook.com', 'phone' => '+61298765432', 'subject' => 'Charging Port Issue', 'message' => 'The charging port on my Google Pixel is loose and sometimes doesn\'t charge properly. How much would it cost to fix this issue? Do you offer warranty on repairs?', 'date' => Carbon::now()->subDays(153)],
                ['first_name' => 'Robert', 'last_name' => 'Williams', 'email' => 'robert.williams@gmail.com', 'phone' => '+81312345678', 'subject' => 'Camera Repair Request', 'message' => 'The rear camera on my iPhone 13 Pro is not focusing properly and images are blurry. I need this fixed urgently for work. What are your available appointment slots?', 'date' => Carbon::now()->subDays(144)],
                ['first_name' => 'Amanda', 'last_name' => 'Garcia', 'email' => 'amanda.garcia@yahoo.com', 'phone' => '+493012345678', 'subject' => 'Software Issues', 'message' => 'My Android phone keeps freezing and apps crash frequently. I think it might be a software problem. Do you provide software troubleshooting and virus removal services?', 'date' => Carbon::now()->subDays(135)],
                ['first_name' => 'Christopher', 'last_name' => 'Lee', 'email' => 'christopher.lee@gmail.com', 'phone' => '+33142868365', 'subject' => 'Speaker Replacement', 'message' => 'The speaker on my OnePlus device is crackling and sound quality is very poor. I need to get it replaced. How long does speaker repair typically take?', 'date' => Carbon::now()->subDays(126)],
                ['first_name' => 'Emily', 'last_name' => 'Johnson', 'email' => 'emily.johnson@hotmail.com', 'phone' => '+390612345678', 'subject' => 'Home Button Repair', 'message' => 'The home button on my iPhone 8 is not responding consistently. Sometimes I have to press it multiple times. Can this be repaired or does it need replacement?', 'date' => Carbon::now()->subDays(117)],
                ['first_name' => 'Daniel', 'last_name' => 'Brown', 'email' => 'daniel.brown@outlook.com', 'phone' => '+971234567890', 'subject' => 'Tablet Screen Repair', 'message' => 'I dropped my iPad and the screen is completely shattered. The device still works but I can\'t use the touchscreen. What would be the cost for screen replacement?', 'date' => Carbon::now()->subDays(108)],
                ['first_name' => 'Lisa', 'last_name' => 'Davis', 'email' => 'lisa.davis@gmail.com', 'phone' => '+34911234567', 'subject' => 'Microphone Problems', 'message' => 'People can\'t hear me clearly during phone calls. The microphone seems to be malfunctioning. Is this something that can be repaired quickly? I use my phone for business calls.', 'date' => Carbon::now()->subDays(99)],
                ['first_name' => 'Matthew', 'last_name' => 'Wilson', 'email' => 'matthew.wilson@yahoo.com', 'phone' => '+442079460876', 'subject' => 'Data Recovery Service', 'message' => 'My phone storage got corrupted and I lost all my photos from the last two years. Is there any way to recover this data? These photos are very important to me.', 'date' => Carbon::now()->subDays(90)],
                ['first_name' => 'Ashley', 'last_name' => 'Martinez', 'email' => 'ashley.martinez@gmail.com', 'phone' => '+74951234567', 'subject' => 'Network Connectivity Issues', 'message' => 'My smartphone has trouble connecting to WiFi and cellular networks. The signal strength is always weak even in areas with good coverage. Can you diagnose this problem?', 'date' => Carbon::now()->subDays(81)],
                ['first_name' => 'Kevin', 'last_name' => 'Anderson', 'email' => 'kevin.anderson@hotmail.com', 'phone' => '+27112314567', 'subject' => 'Overheating Problem', 'message' => 'My phone gets extremely hot during normal use and the battery drains very fast. This started happening after a recent software update. What could be causing this issue?', 'date' => Carbon::now()->subDays(72)],
                ['first_name' => 'Rachel', 'last_name' => 'Taylor', 'email' => 'rachel.taylor@outlook.com', 'phone' => '+82212345678', 'subject' => 'Cracked Back Glass', 'message' => 'The back glass of my iPhone 12 Pro is cracked but the phone works fine. Is it necessary to repair this or can I continue using it? What are the risks?', 'date' => Carbon::now()->subDays(63)],
                ['first_name' => 'Brandon', 'last_name' => 'Thomas', 'email' => 'brandon.thomas@gmail.com', 'phone' => '+34911234567', 'subject' => 'Headphone Jack Repair', 'message' => 'The headphone jack on my device is not working properly. Audio only comes from one side when I plug in headphones. Can this be fixed without replacing the entire unit?', 'date' => Carbon::now()->subDays(54)],
                ['first_name' => 'Stephanie', 'last_name' => 'White', 'email' => 'stephanie.white@yahoo.com', 'phone' => '+1555100198', 'subject' => 'Warranty Inquiry', 'message' => 'I had my phone repaired at your shop three months ago and now I\'m experiencing similar issues. Is this covered under the repair warranty? I have the original receipt.', 'date' => Carbon::now()->subDays(45)],
                ['first_name' => 'Jonathan', 'last_name' => 'Harris', 'email' => 'jonathan.harris@gmail.com', 'phone' => '+442079460876', 'subject' => 'Bulk Repair Services', 'message' => 'I represent a small business and we have multiple devices that need repair. Do you offer bulk repair services or corporate discounts? We have about 15 smartphones and tablets.', 'date' => Carbon::now()->subDays(36)],
                ['first_name' => 'Nicole', 'last_name' => 'Clark', 'email' => 'nicole.clark@hotmail.com', 'phone' => '+919876543287', 'subject' => 'Appointment Scheduling', 'message' => 'I would like to schedule an appointment for next week to get my phone diagnosed. The device randomly shuts down and restarts. What are your available time slots?', 'date' => Carbon::now()->subDays(27)],
                ['first_name' => 'Tyler', 'last_name' => 'Lewis', 'email' => 'tyler.lewis@outlook.com', 'phone' => '+6119876498', 'subject' => 'Price Quote Request', 'message' => 'Can you provide a detailed quote for replacing the screen and battery on a Samsung Galaxy Note 20? I want to compare prices before making a decision. Thank you.', 'date' => Carbon::now()->subDays(18)],
                ['first_name' => 'Melissa', 'last_name' => 'Walker', 'email' => 'melissa.walker@gmail.com', 'phone' => '+81312345687', 'subject' => 'Express Repair Service', 'message' => 'I need my phone repaired urgently as I\'m traveling tomorrow. Do you offer same-day or express repair services? The issue is with the charging port and it\'s not charging at all.', 'date' => Carbon::now()->subDays(9)]
            ];

            foreach ($contactRecords as $record) {
                MobileServiceContact::create([
                    'first_name' => $record['first_name'],
                    'last_name' => $record['last_name'],
                    'email' => $record['email'],
                    'phone' => $record['phone'],
                    'subject' => $record['subject'],
                    'message' => $record['message'],
                    'created_by' => $userId,
                    'created_at' => $record['date'],
                    'updated_at' => $record['date'],
                ]);
            }
        }
    }
}