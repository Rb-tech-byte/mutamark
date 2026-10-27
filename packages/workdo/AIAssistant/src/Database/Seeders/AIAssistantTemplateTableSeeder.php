<?php

namespace Workdo\AIAssistant\Database\Seeders;

use App\Facades\ModuleFacade as Module;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AIAssistantTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this_module = Module::find('AIAssistant');
        $this_module->enable();
        $modules = Module::all();
        if (module_is_active('AIAssistant')) {
            foreach ($modules as $key => $value) {
                if ($value->name != 'AIAssistant') {
                    $name = '\Workdo\\' . $value->name;
                    $path =   $value->getPath();
                    if (file_exists($path . '/src/Database/Seeders/AIAssistantTemplateListTableSeeder.php')) {
                        $this->call($name . '\Database\Seeders\AIAssistantTemplateListTableSeeder');
                    }
                }
            }
        }
    }
}
