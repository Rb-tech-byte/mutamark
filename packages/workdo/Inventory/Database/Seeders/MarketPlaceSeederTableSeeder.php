<?php

namespace Modules\Inventory\Database\Seeders;

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
        $module = 'Inventory';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Inventory';
        $data['product_main_description'] = '<p>An Inventory management module, often found in business software systems like Enterprise Resource Planning (ERP) or Inventory Management Software, typically includes a set of features and functionalities designed to handle various aspects of inventory control and optimization.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Inventory Management';
        $data['dedicated_theme_description'] = '<p>An inventory management module is a software component that tracks, controls, and optimizes inventory levels, helping businesses efficiently manage stock, reduce costs, and meet customer demand.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Why use Inventory?","dedicated_theme_section_description":"<p>Inventory management helps maintain the right amount of inventory to meet customer demand while reducing excess stock and carrying costs. Prevents product shortages and stockouts, ensuring products are available when customers need them. Inventory is effectively managed to reduce storage, handling and obsolescence costs.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"How does the Inventory Management work?","dedicated_theme_section_description":"<p>Inventory Management in the context of converting retainers and proposals to invoices or adjusting quantities ensures that your inventory records accurately reflect the status of products and services, helping you prevent overbilling, stockouts, and discrepancies in your financial records. It is a critical component of efficient business operations and financial management.<\/p>","dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"uploads\/marketplace_image\/ActivityLog\/1692847682-screenshots.png","screenshots_heading":"Activity Log"},{"screenshots":"uploads\/marketplace_image\/ActivityLog\/1692847702-screenshots.png","screenshots_heading":"Activity Log"},{"screenshots":"uploads\/marketplace_image\/ActivityLog\/1692847714-screenshots.png","screenshots_heading":"Activity Log"},{"screenshots":"uploads\/marketplace_image\/ActivityLog\/1692847728-screenshots.png","screenshots_heading":"Activity Log"},{"screenshots":"uploads\/marketplace_image\/ActivityLog\/1692847740-screenshots.png","screenshots_heading":"Activity Log"}]';
        $data['addon_heading'] = 'Why choose dedicated modules for Your Business?';
        $data['addon_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['addon_section_status'] = 'on';
        $data['whychoose_heading'] = 'Why choose dedicated modules for Your Business?';
        $data['whychoose_description'] = '<p>With Dash, you can conveniently manage all your business functions from a single location.</p>';
        $data['pricing_plan_heading'] = 'Empower Your Workforce with DASH';
        $data['pricing_plan_description'] = '<p>Access over Premium Add-ons for Accounting, HR, Payments, Leads, Communication, Management, and more, all in one place!</p>';
        $data['pricing_plan_demo_link'] = '#';
        $data['pricing_plan_demo_button_text'] = 'View Live Demo';
        $data['pricing_plan_text'] = '{"1":{"title":"Pay-as-you-go"},"2":{"title":"Unlimited installation"},"3":{"title":"Secure cloud storage"}}';
        $data['whychoose_sections_status'] = 'on';
        $data['dedicated_theme_section_status'] = 'on';

        foreach($data as $key => $value){
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', $module)->exists()){
                MarketplacePageSetting::updateOrCreate(
                [
                    'name' => $key,
                    'module' => $module

                ],
                [
                    'value' => $value
                ]);
            }
        }
    }
}
