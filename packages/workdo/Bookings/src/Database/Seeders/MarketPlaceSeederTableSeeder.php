<?php

namespace Workdo\Bookings\Database\Seeders;

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
        Model::unguard();
        $module = 'Bookings';

        $data['product_main_banner'] = '';
        $data['product_main_status'] = 'on';
        $data['product_main_heading'] = 'Bookings';
        $data['product_main_description'] = '<p>The Dash Saas Bookings Module makes managing your appointments simple and hassle-free. Our easy-to-use interface lets you set up and adjust booking slots quickly, ensuring your calendar is always up-to-date. Whether you are a solo business owner or managing a team, our scheduling tool adapts to your needs. From one-time meetings to recurring appointments, you’ll stay organized and in control with ease.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Simplify Your Schedule with Dash SaaS Booking Module';
        $data['dedicated_theme_description'] = '<p>Streamline your scheduling with Dash SaaS Booking. Integrated seamlessly into the Dash Saas platform, our intuitive module minimizes conflicts and enhances customer satisfaction. Simplify appointments effortlessly.</p>';

        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"uploads\/marketplace_image\/Bookings\/1692847416-dedicated_theme_section_image.png",
            "dedicated_theme_section_status":"on",
            "dedicated_theme_section_heading":"Great Customer Experience",
            "dedicated_theme_section_description":"<p>Providing a great customer experience is simple with the Dash Saas Bookings Module. Clients can book appointments anytime through an easy online portal. Automatic confirmations and reminders help reduce no-shows, keeping everyone informed and prepared. The seamless integration with your website and social media allows clients to book services without leaving their favorite platforms, making the process smooth and convenient.<\/p>",
            "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},
            {"dedicated_theme_section_image":"uploads\/marketplace_image\/Bookings\/1692847446-dedicated_theme_section_image.png","dedicated_theme_section_status":"on",
                "dedicated_theme_section_heading":"Easy Integration and Customization",
                "dedicated_theme_section_description":"<p>Our Bookings Module works well with the tools you already use. Easily integrate with popular calendar apps like Google Calendar and Outlook to keep all your schedules in sync. Customize booking forms and workflows to fit your business needs. Whether you need to collect detailed information upfront or offer different service options, our module allows you to create a booking experience that matches your brand.<\/p>",
                "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},
                {"dedicated_theme_section_image":"uploads\/marketplace_image\/Bookings\/1692847446-dedicated_theme_section_image.png","dedicated_theme_section_status":"on",
                    "dedicated_theme_section_heading":"Useful Analytics",
                    "dedicated_theme_section_description":"<p>Get valuable insights into your booking trends and business performance with the Dash Saas Bookings Module/s analytics. Track important metrics like appointment numbers, peak booking times, and customer preferences. Use this data to make better decisions about staffing, marketing, and services. Our simple reports and dashboards give you a clear view of how your booking system is doing, helping you improve efficiency and profitability. Plus, you can manage bookings from both the backend and frontend, giving you complete control and flexibility over your scheduling process.<\/p>",
                    "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}},
                    {"dedicated_theme_section_image":"uploads\/marketplace_image\/Bookings\/1692847446-dedicated_theme_section_image.png","dedicated_theme_section_status":"on",
                        "dedicated_theme_section_heading":"Online Payments",
                        "dedicated_theme_section_description":"<p>With the Dash Saas Bookings Module, customers can pay for their appointments online quickly and securely. We support major payment gateways like Stripe and PayPal, making it easy for clients to complete transactions. This feature not only enhances convenience but also ensures that payments are processed efficiently, reducing the risk of missed payments and improving your cash flow. Offering online payment options helps streamline your business operations and provides a seamless experience for your customers.<\/p>",
                        "dedicated_theme_section_cards":{"1":{"title":null,"description":null}}}]';


        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Bookings"},{"screenshots":"","screenshots_heading":"Bookings"},{"screenshots":"","screenshots_heading":"Bookings"},{"screenshots":"","screenshots_heading":"Bookings"},{"screenshots":"","screenshots_heading":"Bookings"}]';
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
