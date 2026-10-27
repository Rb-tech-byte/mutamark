<?php

namespace Workdo\AIAssistant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Workdo\AIAssistant\Database\Seeders\AIAssistantTemplateTableSeeder;

class AIAssistantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(AIAssistantTemplateTableSeeder::class);
        if (module_is_active('LandingPage')) {
            $this->call(MarketPlaceSeederTableSeeder::class);
        };
    }
}
