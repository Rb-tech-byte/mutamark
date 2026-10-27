<?php

namespace Workdo\Fleet\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Workdo\LandingPage\Entities\MarketplacePageSetting;


class MarketPlaceSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Fleet';
        $data['product_main_description'] = '<p>Your all-in-one solution for vehicle booking, order tracking, payment history, fuel management,Maintenance Manage, and Insurance plans</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2>Complete Vehicle Management System</h2>';
        $data['dedicated_theme_description'] = '<p>Fleet gives users deep insight into their maintenance needs with detailed Vehicle Bookings, Vehicles,Maintenance,insurance , dashboards</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_status":"on","dedicated_theme_section_heading":"Fleet makes it easy to manage Vehicles","dedicated_theme_section_description":"<p>In vehicle management, you can keep a list of how many vehicles your company has or how many new ones have arrived, and you can also create where the driver is to be assigned to that vehicle.&nbsp;<\/p>","dedicated_theme_section_cards":{"1":{"title":"Esy to manage vehicles types","description":"In it you can add vehicle types according to your management system"},"2":{"title":"null","description":"null"}}},{"dedicated_theme_section_image":"","dedicated_theme_section_status":"on","dedicated_theme_section_heading":"Most effective way to manage your Booking system","dedicated_theme_section_description":"<p>A booking system you can mange the booking coming to your company and can also manage its payment and you can also see how much amount has been paid in the booking payment.<\/p>","dedicated_theme_section_cards":{"1":{"title":"Manage Booking Payment","description":"Its payment and you can also see how much amount has been paid in booking payment and also manage how much amount is pending."},"2":{"title":"null","description":"null"}}},{"dedicated_theme_section_image":"","dedicated_theme_section_status":"on","dedicated_theme_section_heading":"Manage Maintenance","dedicated_theme_section_description":"<p>In Fleet Management, you are also given a maintenance management system of your vehicle in which you can manage the maintenance of the entire vehicle and where you can add employees with cost, priority, total cost etc.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Fleet"},{"screenshots":"","screenshots_heading":"Fleet"},{"screenshots":"","screenshots_heading":"Fleet"},{"screenshots":"","screenshots_heading":"Fleet"},{"screenshots":"","screenshots_heading":"Fleet"}]';
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

        foreach($data as $key => $value){
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', 'Fleet')->exists()){
                MarketplacePageSetting::updateOrCreate(
                [
                    'name' => $key,
                    'module' => 'Fleet'

                ],
                [
                    'value' => $value
                ]);
            }
        }
    }
}
