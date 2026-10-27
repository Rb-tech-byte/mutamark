<?php

namespace Modules\CustomField\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\LandingPage\Entities\MarketplacePageSetting;

class MarketPlaceSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Custom Field';
        $data['product_main_description'] = '<p>Would you like to collect additional information about modules then you can use custome filed.This feature allows you to gather more specific information.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'GetAdditionalInformation';
        $data['dedicated_theme_description'] = '<p>Bind fields to specific module.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Custom Filed","dedicated_theme_section_description":"<p>All you need to do is select a field from the list and the appropriate type. There are available five field types: Text, Date, Email, Number, and Textarea you will be able to show the custom fields without any additional effort.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null},"2":{"title":"null","description":null},"3":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Use All Modules","dedicated_theme_section_description":" <p>Custom Filed use in HRM, Account, Lead, Assests, Contract, Performance, Pos, Retainer, Sales, Taskly all modules.You can using custom filed collect additional information about all modules as you like.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null},"2":{"title":null,"description":null},"3":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"CustomField"},{"screenshots":"","screenshots_heading":"CustomField"},{"screenshots":"","screenshots_heading":"CustomField"},{"screenshots":"","screenshots_heading":"CustomField"},{"screenshots":"","screenshots_heading":"CustomField"}]';
        $data['addon_heading'] = 'Why choose dedicated modulesfor Your Business?';
        $data['addon_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modulesfor Your Business?';
        $data['whychoose_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with DASH';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Accounting, HR, Payments, Leads, Communication, Management, and more, all in one place!</p>';
        $data['pricing_plan_demo_link'] = '#';
        $data['pricing_plan_demo_button_text'] = 'View Live Demo';
        $data['pricing_plan_text'] = '{"1":{"title":"Pay-as-you-go"},"2":{"title":"Unlimited installation"},"3":{"title":"Secure cloud storage"}}';
        $data['whychoose_sections_status'] = 'on';
        $data['dedicated_theme_section_status'] = 'on';

        foreach ($data as $key => $value) {
            if (!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', 'CustomField')->exists()) {
                MarketplacePageSetting::updateOrCreate(
                    [
                        'name' => $key,
                        'module' => 'CustomField'

                    ],
                    [
                        'value' => $value
                    ]
                );
            }
        }
    }
}
