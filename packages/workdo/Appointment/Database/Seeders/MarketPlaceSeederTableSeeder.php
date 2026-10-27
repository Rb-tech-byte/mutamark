<?php

namespace Modules\Appointment\Database\Seeders;

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

        // $this->call("OthersTableSeeder");

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Appointment';
        $data['product_main_description'] = '<p>Our user-friendly appointment scheduling system simplifies appointment management, allowing efficient organization, tracking, and optimization for business or personal use. It features a dashboard,  calendar button, color change, meeting types, appointment cancel form, appointment callback form, after complete appointment send feedback form, and copy link button for easy form submission. Users can create and update appointments ensuring seamless scheduling and efficient scheduling for their needs.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = '<h2>Get <b>Additional </b> Information for an appointment</h2>';
        $data['dedicated_theme_description'] = '<p>Appointment is a feature that allows you to gather extra details from individuals scheduling appointments. This information can be crucial for ensuring a smooth and tailored interaction.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"How does the appointment work?","dedicated_theme_section_description":"<p>In the appointment section, you can create an appointment. It has a calendar button that displays all appointments and changes color according to the appointment status. It has a copy link button, in which any user can fill out the appointment form.</p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Manage Question","dedicated_theme_section_description":"<p>You just need to select a field and the appropriate type from the list. There are four field types available: Text, Radio, Dropdown and Checkbox You will be able to show the question without any extra effort. And you can decide whether to make that question mandatory or not. And you can also decide whether you want to keep that question in the appointment section or not.</p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Manage Schedule","dedicated_theme_section_description":"<p>In the schedule section, the details of the filled appointment form are shown. In it, the schedule status action can be changed. It has the option to approve appointments, reject appointments, assign users, complete appointments, and send feedback forms after completion. Here, appointment approval or rejection will be communicated to the appointee through their email when they fill out the form, and a feedback form will also be sent.</p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Manage Appointment Callback","dedicated_theme_section_description":"<p>Instead of creating an appointment again, you can easily book an appointment through a callback only.</p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"HRM"},{"screenshots":"","screenshots_heading":"HRM"},{"screenshots":"","screenshots_heading":"HRM"},{"screenshots":"","screenshots_heading":"HRM"},{"screenshots":"","screenshots_heading":"HRM"},{"screenshots":"","screenshots_heading":"HRM"}]';
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
            if (!MarketplacePageSetting::where('name', $key)->where('module', 'Appointment')->exists()) {
                MarketplacePageSetting::updateOrCreate(
                    [
                        'name' => $key,
                        'module' => 'Appointment'
                    ],
                    [
                        'value' => $value
                    ]
                );
            }
        }
    }
}
