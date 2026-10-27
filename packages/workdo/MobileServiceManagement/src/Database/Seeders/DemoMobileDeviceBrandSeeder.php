<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileDeviceBrand;
use Carbon\Carbon;

class DemoMobileDeviceBrandSeeder extends Seeder
{
    public function run($userId): void
    { 
        if (MobileDeviceBrand::where('created_by', $userId)->exists()) {
            return; // Skip seeding if data already exists
        }
        if (!empty($userId)) {
            // 20 realistic device brand records ordered oldest to newest (6 months)
            $brandRecords = [
                ['name' => 'Apple', 'date' => Carbon::now()->subDays(180)],
                ['name' => 'Samsung', 'date' => Carbon::now()->subDays(171)],
                ['name' => 'Google', 'date' => Carbon::now()->subDays(162)],
                ['name' => 'OnePlus', 'date' => Carbon::now()->subDays(153)],
                ['name' => 'Xiaomi', 'date' => Carbon::now()->subDays(144)],
                ['name' => 'Huawei', 'date' => Carbon::now()->subDays(135)],
                ['name' => 'Oppo', 'date' => Carbon::now()->subDays(126)],
                ['name' => 'Vivo', 'date' => Carbon::now()->subDays(117)],
                ['name' => 'Realme', 'date' => Carbon::now()->subDays(108)],
                ['name' => 'Sony', 'date' => Carbon::now()->subDays(99)],
                ['name' => 'LG', 'date' => Carbon::now()->subDays(90)],
                ['name' => 'Motorola', 'date' => Carbon::now()->subDays(81)],
                ['name' => 'Nokia', 'date' => Carbon::now()->subDays(72)],
                ['name' => 'Honor', 'date' => Carbon::now()->subDays(63)],
                ['name' => 'Nothing', 'date' => Carbon::now()->subDays(54)],
                ['name' => 'Asus', 'date' => Carbon::now()->subDays(45)],
                ['name' => 'Poco', 'date' => Carbon::now()->subDays(36)],
                ['name' => 'Infinix', 'date' => Carbon::now()->subDays(27)],
                ['name' => 'Tecno', 'date' => Carbon::now()->subDays(18)],
                ['name' => 'Fairphone', 'date' => Carbon::now()->subDays(9)]
            ];

            foreach ($brandRecords as $record) {
                MobileDeviceBrand::create([
                    'name' => $record['name'],
                    'creator_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => $record['date'],
                    'updated_at' => $record['date'],
                ]);
            }
        }
    }
}
