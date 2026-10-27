<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        (new PermissionRoleSeeder())->run();
        (new DefultSetting())->run();
        (new PlanSeeder())->run();
        (new EmailTemplatesSeeder())->run();
        (new NotificationsTableSeeder())->run();

        $userId = User::where('email', 'company@example.com')->first()->id;
        User::CompanySetting($userId);

        // Core Mutamark Enterprise Module Seeders
        $mutamarkSeeders = [
            MutamarkAccountingSeeder::class,
            MutamarkInventorySeeder::class,
            MutamarkProcurementSeeder::class,
            MutamarkProcureToPaySeeder::class,
            MutamarkPosSeeder::class,
            MutamarkPayrollSeeder::class,
            MutamarkFixedAssetSeeder::class,
            MutamarkFleetSeeder::class,
            MutamarkProjectSeeder::class,
            MutamarkApprovalSeeder::class,
            MutamarkDeliveryNotesSeeder::class,
            MutamarkWmsSeeder::class,
            MutamarkStatutoryDeclarationsSeeder::class,
            MutamarkTraFiscalSeeder::class,
            MutamarkPaymentGatewaySeeder::class,
            MutamarkMultiCurrencyForexSeeder::class,
            MutamarkSampleDataSeeder::class,
            MutamarkNicheUsersSeeder::class,
        ];

        foreach ($mutamarkSeeders as $seeder) {
            try {
                if (class_exists($seeder)) {
                    $this->call($seeder);
                }
            } catch (\Throwable $e) {
                // Continue if optional package seeder encounters missing tables
            }
        }

        if(config('app.run_demo_seeder'))
        {
            // // Pass $userId to your custom seeder


            (new CouponSeeder())->run();
            (new DemoUserSeeder())->run();

            (new DemoStaffSeeder())->run($userId);
            (new DemoLoginHistorySeeder())->run($userId);
            (new DemoWarehouseSeeder())->run($userId);
            (new HelpdeskCategorySeeder())->run();
            (new HelpdeskTicketSeeder())->run($userId);
            (new HelpdeskReplySeeder())->run($userId);
            (new DemoOrderSeeder())->run($userId);
            (new DemoCouponDetailsSeeder())->run();
            (new DemoBankTransferSeeder())->run($userId);
            (new MessengerSeeder())->run();
            (new AIAgentChatSessionSeeder())->run($userId);
            (new AIAgentChatMessageSeeder())->run($userId);

             // temporary
            // (new PackageSeeder())->run($userId);

            // in this seeder product
            (new DemoTransferSeeder())->run($userId);
        }
    }
}
