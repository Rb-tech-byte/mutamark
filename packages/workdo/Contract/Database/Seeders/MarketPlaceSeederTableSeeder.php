<?php

namespace Modules\Contract\Database\Seeders;

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
        $data['product_main_heading'] = 'Contract';
        $data['product_main_description'] = '<p>The new contract management module is designed to help borrowers better manage contracts and thereby improve overall contract and project execution. Cover the end-to-end contract implementation cycle from contract signing to contract completion and consolidates contract management records.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = null;
        $data['dedicated_theme_description'] = null;
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"What is contract management?","dedicated_theme_section_description":"<p>Contract management is the process of digitally maintaining agreements made with customers, vendors, partners, or employees. This encompasses creating, managing, sharing and archiving business contracts, and should allow in-house legal and its business users to automate management processes while extracting business intelligence from those contracts.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Contract Terminations","dedicated_theme_section_description":"<p>Create legally binding contracts in seconds and keep all your contracts organized in one place. Also get real-time updates on the status of your contracts, and automatically record contract changes.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"What does the contract module help with?","dedicated_theme_section_description":"<p>Contract Module is very helpful for proper documentation of the contracts done with clients showing the type of contract, the value, and time duration. The predefined settings can be done/ updated through Setup.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Contract"},{"screenshots":"","screenshots_heading":"Contract"},{"screenshots":"","screenshots_heading":"Contract"},{"screenshots":"","screenshots_heading":"Contract"},{"screenshots":"","screenshots_heading":"Contract"}]';
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
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', 'Contract')->exists()){
                MarketplacePageSetting::updateOrCreate(
                [
                    'name' => $key,
                    'module' => 'Contract'

                ],
                [
                    'value' => $value
                ]);
            }
        }
    }
}
