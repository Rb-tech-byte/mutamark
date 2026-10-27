<?php

namespace Modules\WhatsAppAPI\Database\Seeders;

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
        $data['product_main_heading'] = 'WhatsApp API';
        $data['product_main_description'] = '<p>Workdo-Dash seamlessly integrates the WhatsApp API. This integration allows users to send WhatsApp messages triggered by specific events within the application. In the settings section, users have the ability to customize and manage event notifications by toggling them on or off as per their preferences. This feature enhances user engagement and facilitates efficient communication through WhatsApp messaging within the Workdo-Dash platform.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2>More<b> Effective</b> With Whatsapp Notification</h2>';
        $data['dedicated_theme_description'] = '<p>Whatsapp channels are a wonderful tool, especially if you have a digital venture.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"End-to-End Encryption","dedicated_theme_section_description":"<p>Whatsapp is known for its end-to-end encryption, to the point that many specialists recommend it when talking about sensitive topics.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Settings Page for WhatsApp API","dedicated_theme_section_description":"<p>Enhance your Workdo-Dash experience with our customizable WhatsApp event notifications feature. Tailor your alerts by toggling events on and off, empowering users to stay connected and informed through seamless WhatsApp integration. Easily configure credentials for the WhatsApp API and manage event preferences within the settings section, ensuring a personalized and efficient communication workflow within your application.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Whatsapp"},{"screenshots":"","screenshots_heading":"Whatsapp"},{"screenshots":"","screenshots_heading":"Whatsapp"},{"screenshots":"","screenshots_heading":"Whatsapp"},{"screenshots":"","screenshots_heading":"Whatsapp"}]';
        $data['addon_heading'] = '<h2>Why choose dedicated modules<b> for Your Business?</b></h2>';
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
            if (!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', 'WhatsAppAPI')->exists()) {
                MarketplacePageSetting::updateOrCreate(
                    [
                        'name' => $key,
                        'module' => 'WhatsAppAPI'

                    ],
                    [
                        'value' => $value
                    ]
                );
            }
        }
    }
}
