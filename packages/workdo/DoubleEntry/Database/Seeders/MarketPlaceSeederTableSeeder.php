<?php

namespace Modules\DoubleEntry\Database\Seeders;

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
        $data['product_main_heading'] = 'Double Entry';
        $data['product_main_description'] = '<p>Double Entry accounting is a fundamental accounting method that records financial transactions by entering them into two separate accounts: a debit and a credit. Each transaction affects both sides of the accounting equation, ensuring that assets equal liabilities plus equity. This system helps maintain the accuracy and integrity of financial records, enabling businesses to track their financial health and comply with accounting standards.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Dual Accounting Principle';
        $data['dedicated_theme_description'] = '<p>Double Entry Accounting is crucial for maintaining accurate and balanced financial records, providing a complete picture of a company financial activities.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image": "","dedicated_theme_section_heading": "Maintain Balance And Accuracy in Accounting","dedicated_theme_section_description": "","dedicated_theme_section_cards": {"1": {"title": "What Is an Accounting Journal?","description": "Journal Account typically refers to an account used in the double entry accounting system to record individual financial transactions. In the double entry system, each financial transaction is recorded in a journal account with both a debit and a credit entry to ensure the accounting equation (assets = liabilities + equity) remains balanced."},"2": {"title": "How to Do Accounting Journal Entries?","description": "To perform accounting journal entries, identify the transaction, analyze its impact on accounts, and record it in a journal with debits and credits. Ensure entries balance and post them to the general ledger for accurate financial reporting."}}},{"dedicated_theme_section_image": "","dedicated_theme_section_heading": "Tools for keeping an accurate general ledger","dedicated_theme_section_description": "<p>A ledger account is a record of all transactions affecting a particular account within the general ledger. Individual transactions are identified within the ledger account with a date, transaction number, and description to make it easier for business owners and accountants to research the reason for the transaction.</p>","dedicated_theme_section_cards": {"1": {"title": null,"description": null}}},{"dedicated_theme_section_image": "","dedicated_theme_section_heading": "Balance Sheet","dedicated_theme_section_description": "<p>Balance sheets provide a snapshot of a company financial position by presenting its assets, liabilities, and equity at a specific point in time. Assets are what the company owns, liabilities are what it owes, and equity represents ownership. The equation, Assets = Liabilities + Equity, ensures the balance sheet fundamental principle: assets must equal the sum of liabilities and equity.</p>","dedicated_theme_section_cards": {"1": {"title": null,"description": null}}},{"dedicated_theme_section_image": "","dedicated_theme_section_heading": "Profit and Loss (P&L) Statement","dedicated_theme_section_description": "<p>Profit and Loss (P&L) statements, also known as income statements, provide a detailed financial overview of a company performance over a specific period. They start with total revenues generated from sales and then subtract all operating expenses, including cost of goods sold (COGS), operating expenses, taxes, and interest. The resulting net profit (or loss) represents the company bottom-line earnings after all costs are considered.</p>","dedicated_theme_section_cards": {"1": {"title": null,"description": null}}},{"dedicated_theme_section_image": "","dedicated_theme_section_heading": "Trial Balance","dedicated_theme_section_description": "<p>A Trial Balance is an accounting worksheet that lists all general ledger accounts and their balances. It serves as a fundamental check to ensure total debits equal total credits, verifying data accuracy across major accounting items like assets, liabilities, equity, revenues, expenses, gains, and losses.</p>","dedicated_theme_section_cards": {"1": {"title": null,"description": null}}}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":""},{"screenshots":"","screenshots_heading":""},{"screenshots":"","screenshots_heading":""},{"screenshots":"","screenshots_heading":""},{"screenshots":"","screenshots_heading":""}]';
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

        foreach($data as $key => $value){
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', 'DoubleEntry')->exists()){
                MarketplacePageSetting::updateOrCreate(
                    [
                        'name' => $key,
                        'module' => 'DoubleEntry'
                    ],
                    [
                        'value' => $value
                    ]);
            }
        }
    }
}
