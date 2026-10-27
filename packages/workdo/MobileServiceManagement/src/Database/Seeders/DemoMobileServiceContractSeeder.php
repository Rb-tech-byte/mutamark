<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileServiceContract;
use Carbon\Carbon;

class DemoMobileServiceContractSeeder extends Seeder
{
    public function run($userId): void
    {
        if (MobileServiceContract::where('created_by', $userId)->exists()) {
            return; // Skip seeding if data already exists
        }
        if (!empty($userId)) {
            // 20 realistic contract records ordered oldest to newest (6 months)
            $contractRecords = [
                ['customer_name' => 'TechCorp Solutions', 'contract_type' => 'Enterprise Mobile Support', 'contract_details' => 'Comprehensive mobile device support contract covering 50+ corporate smartphones and tablets. Includes priority repair services, device replacement, and 24/7 technical support for business operations.', 'start_date' => Carbon::now()->subDays(180), 'end_date' => Carbon::now()->addDays(185)],
                ['customer_name' => 'Digital Marketing Pro', 'contract_type' => 'Bulk Device Maintenance', 'contract_details' => 'Annual maintenance contract for marketing team mobile devices. Covers screen protection, battery optimization, software updates, and quarterly device health checkups for improved productivity.', 'start_date' => Carbon::now()->subDays(171), 'end_date' => Carbon::now()->addDays(194)],
                ['customer_name' => 'StartUp Innovations', 'contract_type' => 'Startup Device Package', 'contract_details' => 'Cost-effective mobile device support package for growing startup. Includes device setup, security configuration, app installation, and priority repair services for team productivity enhancement.', 'start_date' => Carbon::now()->subDays(162), 'end_date' => Carbon::now()->addDays(203)],
                ['customer_name' => 'E-Commerce Hub', 'contract_type' => 'Retail Support Contract', 'contract_details' => 'Specialized mobile support for e-commerce operations. Covers point-of-sale devices, inventory scanners, customer service tablets, and emergency replacement services for uninterrupted business operations.', 'start_date' => Carbon::now()->subDays(153), 'end_date' => Carbon::now()->addDays(212)],
                ['customer_name' => 'Cloud Systems Ltd', 'contract_type' => 'IT Infrastructure Support', 'contract_details' => 'Enterprise-grade mobile device management contract. Includes device provisioning, security compliance, remote troubleshooting, and comprehensive data backup solutions for IT infrastructure teams.', 'start_date' => Carbon::now()->subDays(144), 'end_date' => Carbon::now()->addDays(221)],
                ['customer_name' => 'Healthcare Partners', 'contract_type' => 'Medical Device Support', 'contract_details' => 'Specialized contract for healthcare mobile devices. Covers HIPAA-compliant repairs, sanitization protocols, priority service for critical medical applications, and 24/7 emergency support availability.', 'start_date' => Carbon::now()->subDays(135), 'end_date' => Carbon::now()->addDays(230)],
                ['customer_name' => 'Education First Academy', 'contract_type' => 'Educational Institution Plan', 'contract_details' => 'Comprehensive mobile support for educational institution. Covers student tablets, teacher devices, administrative smartphones, and bulk repair services with educational discounts and flexible scheduling.', 'start_date' => Carbon::now()->subDays(126), 'end_date' => Carbon::now()->addDays(239)],
                ['customer_name' => 'Construction Dynamics', 'contract_type' => 'Industrial Device Protection', 'contract_details' => 'Rugged device support contract for construction industry. Includes waterproof cases, drop protection, dust-resistant modifications, and field repair services for harsh working environment conditions.', 'start_date' => Carbon::now()->subDays(117), 'end_date' => Carbon::now()->addDays(248)],
                ['customer_name' => 'Financial Services Group', 'contract_type' => 'Banking Security Package', 'contract_details' => 'High-security mobile device contract for financial services. Covers encrypted device setup, security audits, compliance monitoring, and priority repair services for banking and financial applications.', 'start_date' => Carbon::now()->subDays(108), 'end_date' => Carbon::now()->addDays(257)],
                ['customer_name' => 'Logistics Express', 'contract_type' => 'Fleet Device Management', 'contract_details' => 'Mobile device support for logistics operations. Covers driver smartphones, GPS devices, delivery tablets, and real-time tracking systems with 24/7 support for continuous delivery operations.', 'start_date' => Carbon::now()->subDays(99), 'end_date' => Carbon::now()->addDays(266)],
                ['customer_name' => 'Restaurant Chain Plus', 'contract_type' => 'Hospitality Device Support', 'contract_details' => 'Restaurant industry mobile support contract. Covers POS tablets, kitchen display systems, server devices, and customer ordering tablets with food-safe cleaning protocols and quick replacement services.', 'start_date' => Carbon::now()->subDays(90), 'end_date' => Carbon::now()->addDays(275)],
                ['customer_name' => 'Legal Associates Firm', 'contract_type' => 'Professional Services Plan', 'contract_details' => 'Legal industry mobile device support. Covers attorney smartphones, paralegal tablets, court recording devices, and confidential data protection with priority repair services for legal professionals.', 'start_date' => Carbon::now()->subDays(81), 'end_date' => Carbon::now()->addDays(284)],
                ['customer_name' => 'Manufacturing Solutions', 'contract_type' => 'Industrial Operations Support', 'contract_details' => 'Manufacturing facility mobile device contract. Covers production line tablets, quality control devices, inventory scanners, and maintenance smartphones with industrial-grade protection and support services.', 'start_date' => Carbon::now()->subDays(72), 'end_date' => Carbon::now()->addDays(293)],
                ['customer_name' => 'Real Estate Professionals', 'contract_type' => 'Property Management Package', 'contract_details' => 'Real estate industry mobile support contract. Covers agent smartphones, property inspection tablets, virtual tour devices, and client presentation systems with flexible scheduling and priority service.', 'start_date' => Carbon::now()->subDays(63), 'end_date' => Carbon::now()->addDays(302)],
                ['customer_name' => 'Sports & Fitness Center', 'contract_type' => 'Fitness Facility Support', 'contract_details' => 'Fitness industry mobile device contract. Covers trainer tablets, member check-in systems, workout tracking devices, and payment processing tablets with moisture protection and quick turnaround repairs.', 'start_date' => Carbon::now()->subDays(54), 'end_date' => Carbon::now()->addDays(311)],
                ['customer_name' => 'Media Production House', 'contract_type' => 'Creative Industry Plan', 'contract_details' => 'Media production mobile support contract. Covers high-end smartphones for content creation, editing tablets, live streaming devices, and backup systems with priority service for production deadlines.', 'start_date' => Carbon::now()->subDays(45), 'end_date' => Carbon::now()->addDays(320)],
                ['customer_name' => 'Transportation Services', 'contract_type' => 'Fleet Communication Support', 'contract_details' => 'Transportation industry mobile device contract. Covers driver communication devices, dispatch tablets, GPS navigation systems, and emergency communication tools with 24/7 roadside support availability.', 'start_date' => Carbon::now()->subDays(36), 'end_date' => Carbon::now()->addDays(329)],
                ['customer_name' => 'Retail Fashion Boutique', 'contract_type' => 'Small Business Package', 'contract_details' => 'Small retail business mobile support contract. Covers POS systems, inventory management tablets, customer service devices, and social media marketing smartphones with affordable pricing and flexible terms.', 'start_date' => Carbon::now()->subDays(27), 'end_date' => Carbon::now()->addDays(338)],
                ['customer_name' => 'Consulting Group Elite', 'contract_type' => 'Professional Consulting Support', 'contract_details' => 'Business consulting mobile device contract. Covers consultant smartphones, presentation tablets, client meeting devices, and remote work solutions with priority support for client-facing activities.', 'start_date' => Carbon::now()->subDays(18), 'end_date' => Carbon::now()->addDays(347)],
                ['customer_name' => 'Event Management Co', 'contract_type' => 'Event Services Package', 'contract_details' => 'Event management mobile support contract. Covers event coordination tablets, registration systems, audio-visual control devices, and emergency communication tools with on-site support during events.', 'start_date' => Carbon::now()->subDays(9), 'end_date' => Carbon::now()->addDays(356)]
            ];

            foreach ($contractRecords as $record) {
                MobileServiceContract::create([
                    'customer_name' => $record['customer_name'],
                    'contract_type' => $record['contract_type'],
                    'start_date' => $record['start_date'],
                    'end_date' => $record['end_date'],
                    'contract_details' => $record['contract_details'],
                    'creator_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => $record['start_date'],
                    'updated_at' => $record['start_date'],
                ]);
            }
        }
    }
}