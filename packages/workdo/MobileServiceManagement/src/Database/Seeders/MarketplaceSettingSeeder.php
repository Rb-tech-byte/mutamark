<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\LandingPage\Models\MarketplaceSetting;
use Illuminate\Support\Facades\File;

class MarketplaceSettingSeeder extends Seeder
{
    public function run()
    {
        // Get all available screenshots from marketplace directory
        $marketplaceDir = __DIR__ . '/../../marketplace';
        $screenshots = [];

        if (File::exists($marketplaceDir)) {
            $files = File::files($marketplaceDir);
            foreach ($files as $file) {
                if (in_array($file->getExtension(), ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                    $screenshots[] = '/packages/workdo/MobileServiceManagement/src/marketplace/' . $file->getFilename();
                }
            }
        }

        sort($screenshots);

        MarketplaceSetting::firstOrCreate(['module' => 'MobileServiceManagement'], [
            'module' => 'MobileServiceManagement',
            'title' => 'Mobile Service Management Solutions',
            'subtitle' => 'Complete mobile service lifecycle management with advanced tracking and automation capabilities',
            'config_sections' => [
                'sections' => [
                    'hero' => [
                        'variant' => 'hero1',
                        'title' => 'Mobile Service Management for Mutamark ERP',
                        'subtitle' => 'Transform your mobile service operations with intelligent automation and real-time monitoring. Deliver exceptional customer experiences while optimizing resource allocation and service delivery efficiency.',
                        'primary_button_text' => 'Install MobileServiceManagement Module',
                        'primary_button_link' => '#install',
                        'secondary_button_text' => 'Learn More',
                        'secondary_button_link' => '#learn',
                        'image' => '/packages/workdo/MobileServiceManagement/src/marketplace/hero.png'
                    ],
                    'modules' => [
                        'variant' => 'modules1',
                        'title' => 'Mobile Service Management Module',
                        'subtitle' => 'Revolutionize your service delivery with comprehensive mobile workforce management and customer engagement tools'
                    ],
                    'dedication' => [
                        'variant' => 'dedication1',
                        'title' => 'Advanced Mobile Service Management Features',
                        'description' => 'Our comprehensive mobile service management platform delivers end-to-end solutions for modern service organizations. Experience seamless integration of field operations, customer management, and performance analytics in one powerful system.',
                        'subSections' => [
                            [
                                'title' => 'Dashboard Analytics & Insights',
                                'description' => 'Monitor your mobile service business performance with a comprehensive dashboard that provides real-time insights into all operations. Track total service requests, pending approvals, completed repairs, and total income from service invoices.',
                                'keyPoints' => ['Real-time business performance monitoring with comprehensive statistics and analytics','Recent service requests overview displaying latest five entries with complete details','Income tracking and payment status distribution with visual chart representations','Contract status management showing active, expiring, and expired service agreements'],
                                'screenshot' => '/packages/workdo/MobileServiceManagement/src/marketplace/image1.png'
                            ],
                            [
                                'title' => 'Professional Website Setup',
                                'description' => 'Create a stunning online presence with our comprehensive website customization system that allows complete control over your service website appearance and functionality. Configure site branding elements, design homepage sections including hero banners and service tracking areas, and set up dedicated pages for company information and customer contact.',
                                'keyPoints' => ['Complete website customization with professional branding, logos, and design elements','Homepage section management including banners, about sections, and call-to-action areas','About and contact page setup with team member profiles and social media integration','Custom page creation with footer display options for enhanced customer engagement'],
                                'screenshot' => '/packages/workdo/MobileServiceManagement/src/marketplace/image2.png'
                            ],
                            [
                                'title' => 'Advanced Service Configuration',
                                'description' => 'Establish your service offerings with a flexible configuration system that manages service categories, device brands, and custom tracking statuses. Create unlimited service categories to organize your repair offerings, maintain a comprehensive database of supported device brands, and customize tracking statuses with unique icons and colors.',
                                'keyPoints' => ['Unlimited service categories creation for organized repair service offerings and management','Device brand management with comprehensive database of supported mobile device brands','Custom tracking statuses with unique icons, colors, and drag-and-drop ordering capabilities','Flexible workflow configuration matching your specific business processes and requirements'],
                                'screenshot' => '/packages/workdo/MobileServiceManagement/src/marketplace/image3.png'
                            ],
                            [
                                'title' => 'Service Request Management',
                                'description' => 'Streamline your service request processing with a sophisticated three-stage workflow system that handles everything from initial customer submissions to completion. The pending requests module manages incoming service requests requiring approval or rejection with email notifications.',
                                'keyPoints' => ['Pending request approval system for incoming customer service submissions with email notifications','Active service tracking with technician assignments, progress updates, and payment integration','Complete service history records for business analysis, customer reference, and reporting'],
                                'screenshot' => '/packages/workdo/MobileServiceManagement/src/marketplace/image4.png'
                            ],
                            [
                                'title' => 'Payment & Invoice System',
                                'description' => 'Manage service payments and invoices with an integrated financial system that handles everything from pricing to payment completion. Create detailed invoices with service charges, repair costs, tax calculations, and additional charges. Track payment statuses from pending to cleared with automatic service updates.',
                                'keyPoints' => ['Comprehensive invoice creation with service charges, repair costs, and additional fee management','Payment status tracking from pending to cleared with automatic service status updates','Financial reporting and payment history tracking for business accounting and analysis'],
                                'screenshot' => '/packages/workdo/MobileServiceManagement/src/marketplace/image5.png'
                            ]
                        ]
                    ],
                    'screenshots' => [
                        'variant' => 'screenshots1',
                        'title' => 'Mobile Service Management in Action',
                        'subtitle' => 'Discover how our comprehensive platform transforms service delivery and customer satisfaction',
                        'images' => $screenshots
                    ],
                    'why_choose' => [
                        'variant' => 'whychoose1',
                        'title' => 'Why Choose Mobile Service Management Module?',
                        'subtitle' => 'Transform your service operations with intelligent automation and enhanced customer experiences',
                        'benefits' => [
                            [
                                'title' => 'Real-Time Analytics',
                                'description' => 'Monitor business performance with comprehensive dashboard insights and tracking.',
                                'icon' => 'BarChart',
                                'color' => 'blue'
                            ],
                            [
                                'title' => 'Professional Website',
                                'description' => 'Create stunning online presence with complete customization and branding control.',
                                'icon' => 'Globe',
                                'color' => 'green'
                            ],
                            [
                                'title' => 'Service Workflow',
                                'description' => 'Streamline requests with three-stage workflow and automated payment processing.',
                                'icon' => 'Workflow',
                                'color' => 'purple'
                            ]
                        ]
                    ]
                ],
                'section_visibility' => [
                    'header' => true,
                    'hero' => true,
                    'modules' => true,
                    'dedication' => true,
                    'screenshots' => true,
                    'why_choose' => true,
                    'cta' => true,
                    'footer' => true
                ],
                'section_order' => ['header', 'hero', 'modules', 'dedication', 'screenshots', 'why_choose', 'cta', 'footer']
            ]
        ]);
    }
}
