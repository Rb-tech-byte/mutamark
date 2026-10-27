<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileServiceSetup;
use App\Models\User;

class DemoMobileServiceSetupSeeder extends Seeder
{
    public function run($userId): void
    {
         if (MobileServiceSetup::where('created_by', $userId)->exists()) {
            return; // Skip seeding if data already exists
        }
        if (!empty($userId)) {
            $users = User::where('created_by', $userId)->limit(5)->pluck('id')->toArray();
            $userIds = array_pad($users, 5, $userId);
            
            $setupData = [
                // Site Settings
                'site_settings' => json_encode([
                    'site_logo' => '',
                    'footer_logo' => '',
                    'favicon' => '',
                    'title' => 'Mobile Service Management',
                    'subtitle' => 'Professional Mobile Device Repair Services',
                    'footer_text' => 'MobileRepair. All rights reserved.',
                    'footer_description' => 'Professional device repairs with certified parts and guaranteed satisfaction.'
                ]),

                // Banner Section
                'banner_section' => json_encode([
                    'banner_title' => 'Precision Repairs Exceptional Care',
                    'banner_subtitle' => 'Premium Repair Services',
                    'image' => '',
                    'image_icon' => 'Smartphone',
                    'image_text' => 'Professional Repair',
                    'description' => 'Trusted device restoration with certified parts and service guarantees that put you first.'
                ]),

                // Home Section
                'home_section' => json_encode([
                    'service_tracking_title' => 'Track Your Repair Status',
                    'service_tracking_description' => 'Enter your tracking details to get real-time updates on your device repair',
                    'expertise_title' => 'Comprehensive Device Solutions',
                    'expertise_description' => 'Precision repairs with premium components and guaranteed results.',
                    'cta_title' => 'Need Expert Device Repair?',
                    'cta_description' => 'Schedule your repair today with our certified technicians. Same-day service available for most repairs.',
                    'testimonial_title' => 'Trusted by Thousands of Customers',
                    'testimonial_description' => 'Don\'t just take our word for it - hear what our customers say about their experiences.'
                ]),

                // About Section
                'about_section' => json_encode([
                    'about_title' => 'Trusted Device Experts Since 2012',
                    'about_description' => 'Founded with a commitment to quality repairs and exceptional customer service, Mobile Repair Nexus has grown to become a trusted leader in device restoration. Our team combines technical expertise with genuine care for your devices.',
                    'about_image' => '',
                    'about_image_text' => 'Every repair receives our full attention to detail and care',
                    'about_facilities' => [
                        ['title' => 'Certified technicians with specialized training for all major brands'],
                        ['title' => 'Premium replacement parts backed by 1-year warranty'],
                        ['title' => 'Eco-friendly repair processes that reduce electronic waste'],
                    ]
                ]),

                // Contact Us Section
                'contact_section' => json_encode([
                    'contact_title' => 'Get In Touch',
                    'contact_phone' => '+8885551234',
                    'contact_email' => 'help@mobilerepairnexus.com',
                    'contact_emergency' => '+8885555678',
                    'location_title' => 'Find Our Repair Center',
                    'location_address' => '123 Tech Street, Digital City, CA 90210, United States',
                    'repair_center_title' => 'State-of-the-Art Repair Facility',
                    'repair_center_description' => 'Visit our conveniently located service center with free parking and fast walk-in service.',
                    'repair_center_map_code' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.215384526689!2d-73.9878449242676!3d40.74844053922403!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a9b3117469%3A0xd134e199a405a163!2sEmpire%20State%20Building!5e0!3m2!1sen!2sus!4v1710275666652!5m2!1sen!2sus" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" reerrerpolicy="no-referrer-when-downgrade"></iframe>',
                    'hours_title' => 'Service Hours',
                    'service_hours' => json_encode([
                        'monday' => ['status' => 'open', 'opening_time' => '09:00', 'closing_time' => '18:00'],
                        'tuesday' => ['status' => 'open', 'opening_time' => '09:00', 'closing_time' => '18:00'],
                        'wednesday' => ['status' => 'open', 'opening_time' => '09:00', 'closing_time' => '18:00'],
                        'thursday' => ['status' => 'open', 'opening_time' => '09:00', 'closing_time' => '18:00'],
                        'friday' => ['status' => 'open', 'opening_time' => '09:00', 'closing_time' => '18:00'],
                        'saturday' => ['status' => 'open', 'opening_time' => '10:00', 'closing_time' => '16:00'],
                        'sunday' => ['status' => 'closed', 'opening_time' => null, 'closing_time' => null]
                    ])
                ]),

                // About Title Section
                'about_title_section' => json_encode([
                    'story_title' => 'More Than Just Device Repairs',
                    'story_description' => 'FouFounded in 2018, MobileFix began with a simple mission: to provide reliable, professional mobile device repair services that customers can trust. What started as a small repair shop has grown into a comprehensive mobile service management company.</br></br>Our team of certified technicians brings years of experience and a passion for excellence to every repair. We stay current with the latest device technologies and repair methodologies to ensure your device receives the best possible care.',
                    'story_image' => '',
                    'about_cta_title' => 'Ready to Experience Professional Service?',
                    'about_cta_description' => 'Join thousands of satisfied customers who trust MobileFix with their mobile device repairs.',
                    'mission_title' => 'Why We Do What We Do',
                    'mission_description' => 'We\'re driven by core values that guide everything we do.',
                    'missions' => [
                        ['icon' => 'Wrench', 'title' => 'Quality Service', 'description' => 'We ensure every repair meets the highest quality standards.'],
                        ['icon' => 'Heart', 'title' => 'Customer Satisfaction', 'description' => 'Your satisfaction is our top priority in every interaction.'],
                        ['icon' => 'DollarSign', 'title' => 'Fair Pricing', 'description' => 'Transparent and competitive pricing for all repair services.']
                    ],
                    'team_title' => 'Our Mobile Service Experts',
                    'team_description' => 'Meet the skilled professionals who keep your mobile services running smoothly.',
                    'team_members' => [
                        ['user_id' => (string)$userIds[0], 'experience' => '12+ Yrs Exp', 'position' => 'Lead Technician', 'description' => 'Expert in smartphone and tablet repairs with 8+ years experience'],
                        ['user_id' => (string)$userIds[1], 'experience' => '10+ Yrs Exp', 'position' => 'Repair Specialist', 'description' => 'Specialized in water damage recovery and component-level repairs'],
                        ['user_id' => (string)$userIds[2], 'experience' => 'CS Lead', 'position' => 'Senior Technician', 'description' => 'Certified in all major mobile brands with expertise in motherboard repairs'],
                        ['user_id' => (string)$userIds[3], 'experience' => '5G Certified ', 'position' => 'Software & 5G Specialist', 'description' => 'Focuses on mobile software optimization, 5G configurations and enterprise mobile management solutions.']
                    ]
                ]),

                // Social Links
                'social_links' => json_encode([
                    ['url' => 'https://facebook.com/techfixrepair', 'icon' => 'Facebook'],
                    ['url' => 'https://instagram.com/techfixrepair', 'icon' => 'Instagram'],
                    ['url' => 'https://twitter.com/techfixrepair', 'icon' => 'Twitter'],
                    ['url' => 'https://wa.me/15555551234', 'icon' => 'svg:whatsapp']
                ]),

                // FAQs Section
                'faqs' => json_encode([
                    [
                        'question' => 'How long does a typical screen repair take?',
                        'answer' => 'Most screen repairs are completed within 1-2 hours. However, the exact time depends on your device model and the availability of parts. We\'ll provide you with an accurate time estimate when you bring in your device.'
                    ],
                    [
                        'question' => 'Do you offer warranty on repairs?',
                        'answer' => 'Yes! We provide a 6-month warranty on screen repairs and 1-year warranty on battery replacements. Our warranty covers defects in parts and workmanship, giving you peace of mind with every repair.'
                    ],
                    [
                        'question' => 'What types of devices do you repair?',
                        'answer' => 'We repair all major smartphone brands including iPhone, Samsung, Google Pixel, OnePlus, and more. We also service tablets, smartwatches, and other mobile devices. Contact us if you\'re unsure about your specific device.'
                    ],
                    [
                        'question' => 'Can you repair water-damaged devices?',
                        'answer' => 'We offer professional data recovery services for water-damaged devices. While we can\'t guarantee 100% recovery, our success rate is high. We use specialized equipment and techniques to maximize the chances of retrieving your important data.'
                    ],
                    [
                        'question' => 'Do you use original parts for repairs?',
                        'answer' => 'We use high-quality OEM (Original Equipment Manufacturer) parts whenever possible. For some repairs, we may use premium aftermarket parts that meet or exceed original specifications. We\'ll always inform you about the parts being used.'
                    ],
                    
                ])
            ];

            foreach ($setupData as $key => $value) {
                MobileServiceSetup::updateOrCreate(
                    ['key' => $key, 'created_by' => $userId],
                    [
                        'value' => $value,
                        'creator_id' => $userId,
                        'created_by' => $userId
                    ]
                );
            }
        }
    }
}