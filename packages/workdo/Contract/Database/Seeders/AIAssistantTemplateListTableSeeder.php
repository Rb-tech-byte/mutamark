<?php

namespace Modules\Contract\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\AIAssistant\Entities\AssistantTemplate;

class AIAssistantTemplateListTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $defaultTemplate = [
            [
                'template_name'=>'subject',
                'template_module'=>'contract',
                'prompt'=> "generate contract subject for this contract description ##description##",
                'field_json'=>'{"field":[{"label":"Contract Description","placeholder":"e.g.","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=> 0,
            ],
            [
                'template_name'=>'notes',
                'template_module'=>'contract',
                'prompt'=> "generate contract description for this contract subject ##subject##",
                'field_json'=>'{"field":[{"label":"Contract Subject","placeholder":"e.g.","field_type":"textarea","field_name":"subject"}]}',
                'is_tone'=> 0,
            ],
        ];
        foreach($defaultTemplate as $temp)
        {
            $check = AssistantTemplate::where('template_module',$temp['template_module'])->where('module','Contract')->where('template_name',$temp['template_name'])->exists();
            if(!$check)
            {
                AssistantTemplate::create(
                    [
                        'template_name' => $temp['template_name'],
                        'template_module' => $temp['template_module'],
                        'module' => 'Contract',
                        'prompt' => $temp['prompt'],
                        'field_json' => $temp['field_json'],
                        'is_tone' => $temp['is_tone'],
                        "created_at" => date('Y-m-d H:i:s'),
                        "updated_at" => date('Y-m-d H:i:s')
                    ]
                );
            }
        }
    }
}
