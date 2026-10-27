<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\User;

class EmailTemplateTableSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('type','company')->first();

        $emailTemplate = [
            'New Mobile Service Request',
            'Service Request Accept',
            'Service Request Reject',
        ];
        
        $defaultTemplate = [
            'New Mobile Service Request' => [
                'subject' => 'New Mobile Service Request',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "Tracking ID": "tracking_id",
                    "Tracking URL": "tracking_url"
                  }',
                'lang' => [
                    'en' => '<p>Hello,&nbsp;<br />Welcome to {app_name}</p>
                    <p><strong>Tracking Id </strong>: {tracking_id}<br /><strong>Tracking URL</strong> : {tracking_url}</p>
                    <p>{app_url}</p>
                    <p>Thanks,<br />{app_name}</p>',
                ],
            ],
            'Service Request Accept' => [
                'subject' => 'Service Request Accept',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "Tracking ID": "tracking_id",
                    "Tracking URL": "tracking_url"
                  }',
                'lang' => [
                    'en' => '<p>Hello ,&nbsp;<br />Welcome to {app_name}</p>
                    <p>Your Mobile Service Request Has Been Accepted. Here Is Tracking ID :  <strong> {tracking_id} </strong></p><br/>
                    <p>You Can Track Your Service From Here : {tracking_url} </p>
                    <p>{app_url}</p>
                    <p>Thanks,<br />{app_name}</p>',
                ],
            ],
            'Service Request Reject' => [
                'subject' => 'Service Request Reject',
                'variables' => '{
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name": "company_name",
                    "Service Title": "service_type"
                  }',
                'lang' => [
                    'en' => '<p>Hello ,&nbsp;<br />Welcome to {app_name}</p>
                    <p><strong>This Service Request</strong>: {tracking_id}<br /></p><strong>Has Been Rejected</strong><br/>
                    <p>{app_url}</p>
                    <p>Thanks,<br />{app_name}</p>',
                ]
            ]
        ];
        
        foreach($emailTemplate as $eTemp)
        {
            $table = EmailTemplate::where('name',$eTemp)->where('module_name','MobileServiceManagement')->exists();
            
            if(!$table)
            {
                $emailtemplate = EmailTemplate::create([
                    'name' => $eTemp,
                    'from' => !empty(env('APP_NAME')) ? env('APP_NAME') : 'Mutamark ERP',
                    'module_name' => 'MobileServiceManagement',
                    'created_by' => $admin->id,
                    'creator_id' => $admin->id,
                ]);
                
                foreach($defaultTemplate[$eTemp]['lang'] as $lang => $content)
                {
                    EmailTemplateLang::create([
                        'parent_id' => $emailtemplate->id,
                        'lang' => $lang,
                        'subject' => $defaultTemplate[$eTemp]['subject'],
                        'variables' => $defaultTemplate[$eTemp]['variables'],
                        'content' => $content,
                    ]);
                }
            }
        }
    }
}