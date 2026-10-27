<?php

namespace Workdo\MobileServiceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class MobileServiceUtility extends Model
{
    use HasFactory;

    public static function defaultdata($company_id)
    {
        $tracking_statuses = [
            [
                'status_name'  => 'Received',
                'icon_name'    => 'Package',
                'status_color' => '#3498db'
            ],
            [
                'status_name'  => 'Diagnosed',
                'icon_name'    => 'Search',
                'status_color' => '#f1c40f'
            ],
            [
                'status_name'  => 'In Progress',
                'icon_name'    => 'Wrench',
                'status_color' => '#e67e22'
            ],
            [
                'status_name'  => 'Awaiting Parts',
                'icon_name'    => 'Clock',
                'status_color' => '#9b59b6'
            ],
            [
                'status_name'  => 'Testing',
                'icon_name'    => 'TestTube',
                'status_color' => '#2d487cff'
            ],
            [
                'status_name'  => 'Completed',
                'icon_name'    => 'ThumbsUp',
                'status_color' => '#2ecc71'
            ]
        ];

        foreach ($tracking_statuses as $status) {
            MobileTrackingStatus::firstOrCreate(
                ['status_name' => $status['status_name'], 'created_by' => $company_id],
                [
                    'icon_name'    => $status['icon_name'],
                    'status_name'  => $status['status_name'],
                    'status_color' => $status['status_color'],
                    'creator_id'   => $company_id,
                    'created_by'   => $company_id,
                ]
            );
        }
        $customPages = [
            [
                'name'               => 'Privacy Policy',
                'title'              => 'Privacy Policy',
                'slug'               => 'privacy-policy',
                'enable_page_footer' => 'on',
                'contents'           => '<p><strong>Last updated:</strong> January 2025</p><p></br></p><p>This Privacy Policy describes how our Mobile Service Management Addon collects, uses, and protects your personal information when you use our mobile device repair and service management platform.</p><p></br></p><h3><strong>1. Information We Collect</strong></h3><ul><li>Personal details (name, email, phone number)</li><li>Mobile device information (brand, model, IMEI)</li><li>Service request details and repair history</li><li>Payment and billing information</li><li>Location data for service delivery</li></ul><p></br></p><h3><strong>2. How We Use Your Information</strong></h3><ul><li>Process mobile device repair requests</li><li>Schedule and manage service appointments</li><li>Track repair status and send notifications</li><li>Process payments and generate invoices</li><li>Provide customer support</li></ul><p></br></p><h3><strong>3. Data Security</strong></h3><p>We implement industry-standard security measures to protect your mobile service data, including encrypted data transmission and secure payment processing.</p><p></br></p><h3><strong>4. Your Rights</strong></h3><ul><li>Access your service history and personal data</li><li>Request corrections to your information</li><li>Delete your account and associated data</li><li>Export your service records</li></ul><p></br></p><h3><strong>5. Contact Us</strong></h3><p>For privacy-related questions about our Mobile Service Management Addon, contact us through your service dashboard or email support.</p>',
                'is_editable' => false,
                'creator_id'         => $company_id,
                'created_by'         => $company_id,
            ],
            [
                'name'               => 'Terms & Conditions',
                'title'              => 'Terms & Conditions',
                'slug'               => 'terms-conditions',
                'enable_page_footer' => 'on',
                'contents'           => '<p><strong>Last updated:</strong> January 2025</p><p></br></p><p>By using our Mobile Service Management Addon, you agree to the following terms and conditions.</p><p></br></p><h3><strong>1. Service Agreement</strong></h3><ul><li>Our addon provides mobile device repair management services</li><li>Service requests are processed based on availability</li><li>Repair timelines are estimates and may vary</li><li>We reserve the right to refuse service for damaged beyond repair devices</li></ul><p></br></p><h3><strong>2. Payment Terms</strong></h3><ul><li>Payment is required before service completion</li><li>Prices are subject to change based on repair complexity</li><li>Refunds are available per our refund policy</li><li>Additional charges may apply for premium parts</li></ul><p></br></p><h3><strong>3. Warranty & Liability</strong></h3><ul><li>90-day warranty on repairs and replacement parts</li><li>We are not liable for data loss during repairs</li><li>Customers must backup data before service</li><li>Warranty void if device is tampered with after repair</li></ul><p></br></p><h3><strong>4. User Responsibilities</strong></h3><ul><li>Provide accurate device and contact information</li><li>Remove personal data and accounts before service</li><li>Pay service fees promptly</li><li>Follow pickup and delivery instructions</li></ul><p></br></p><h3><strong>5. Modifications</strong></h3><p>We reserve the right to update these terms to improve our Mobile Service Management Addon. Users will be notified of significant changes through the platform.</p>',
                'is_editable' => false,
                'creator_id'         => $company_id,
                'created_by'         => $company_id,
            ]
        ];

        foreach ($customPages as $pageData) {
            MobileServiceCustomPage::firstOrCreate(
                ['slug' => $pageData['slug'], 'created_by' => $company_id],
                $pageData
            );
        }
    }
    public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $staff_permission = [
            'manage-mobile-service-management',
            'manage-mobile-service-dashboard',
            'manage-mobile-service-requests',
            'manage-own-mobile-service-requests',
            'view-mobile-service-requests',
            'end-mobile-service-repair',
            'manage-mobile-service-history',
            'manage-own-mobile-service-history',
            'view-mobile-service-history',

        ];

        if ($rolename == 'staff') {
            $roles_v = Role::where('name', 'staff')->where('id', $role_id)->first();
            if ($roles_v) {
                foreach ($staff_permission as $permission_v) {
                    $permission = Permission::where('name', $permission_v)->first();
                    if (!empty($permission)) {
                        try {
                            if (!$roles_v->hasPermissionTo($permission_v)) {
                                $roles_v->givePermissionTo($permission);
                            }
                        } catch (\Throwable $e) {}
                    }
                }
            }
        }
    }
}
