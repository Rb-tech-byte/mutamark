<?php

namespace Modules\Recruitment\Database\Seeders;

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
        $data['product_main_heading'] = 'Recruitment';
        $data['product_main_description'] = '<p>The Dashboard of Recruitment module provides users with an immediate overview of their recruitment activities. It presents key metrics such as the total number of jobs published, expired listings, and the current count of job candidates. Additionally, users can easily view their interview schedule through an interactive calendar and stay updated on recently created job listings and job applications by date. This real-time monitoring tool empowers users to make informed decisions and effortlessly stay on top of their hiring processes.</p>';
        $data['product_main_demo_link'] = '#';
        $data['product_main_demo_button_text'] = 'View Live Demo';
        $data['dedicated_theme_heading'] = 'Recruit New Candidates and Grow Your Team';
        $data['dedicated_theme_description'] = '<p>Collect and manage applications from start to finish. Easily compare candidates and pick the best one for the job.</p>';
        $data['dedicated_theme_sections'] = '[{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Jobs Creation","dedicated_theme_section_description":"<p>Within the recruitment module, the Jobs Creation page offers a streamlined process for creating new job listings. Users can input essential details including job titles, descriptions, requirements, and any other pertinent information. This intuitive interface allows for the customization of each listing to attract the right candidates. By simplifying the task of posting job openings, users can quickly reach potential candidates while maintaining consistency and professionalism in their job postings.</p>"},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Candidate Management","dedicated_theme_section_description":"<p>Efficiently managing incoming job applications is facilitated through the Candidate Management page. Here, users can review, filter, and respond to applications seamlessly. This centralized hub provides a clear overview of each candidates journey, from onboarding application. Robust filtering and sorting options allow users to prioritize candidates based on qualifications and suitability for the role.</p>"},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Interview Scheduling","dedicated_theme_section_description":"<p>The Interview Scheduling page simplifies the coordination of interview schedules. Integrated calendar functionalities and automated notifications enable users to efficiently manage interview appointments for both hiring managers and candidates. By eliminating the hassle of back-and-forth communication, this feature saves time and enhances the overall candidate experience, ultimately leading to a smoother recruitment process.</p>"},{"dedicated_theme_section_image":"","dedicated_theme_section_heading":"Career Page","dedicated_theme_section_description":"<p>The Career Page is a gateway for potential candidates to learn more about the organization, culture, and available opportunities. Here, users can showcase company values, employee testimonials, career growth prospects, and upcoming events. With engaging multimedia content and informative resources, the Career Page effectively communicates the employer value proposition and stands out in a competitive job market, attracting top talent and fostering a strong employer brand.</p>"}]';
        $data['dedicated_theme_sections_heading'] = '';
        $data['screenshots'] = '[{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"},{"screenshots":"","screenshots_heading":"Recruitment"}]';
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
            if(!MarketplacePageSetting::where('name', '=', $key)->where('module', '=', 'Recruitment')->exists()){
                MarketplacePageSetting::updateOrCreate(
                [
                    'name' => $key,
                    'module' => 'Recruitment'

                ],
                [
                    'value' => $value
                ]);
            }
        }
    }
}
