<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        Artisan::call('cache:clear');

        $permission = [
            ['name' => 'manage-mobile-service-management', 'module' => 'mobile-service-management', 'label' => 'Manage Mobile Service Management'],
            ['name' => 'manage-mobile-service-dashboard', 'module' => 'mobile-service-management', 'label' => 'Manage Mobile Service Dashboard'],

            // MobileDeviceBrand management
            ['name' => 'manage-mobile-device-brands', 'module' => 'mobile-device-brands', 'label' => 'Manage Mobile Device Brands'],
            ['name' => 'manage-any-mobile-device-brands', 'module' => 'mobile-device-brands', 'label' => 'Manage Any Mobile Device Brands'],
            ['name' => 'manage-own-mobile-device-brands', 'module' => 'mobile-device-brands', 'label' => 'Manage Own Mobile Device Brands'],
            ['name' => 'create-mobile-device-brands', 'module' => 'mobile-device-brands', 'label' => 'Create Mobile Device Brands'],
            ['name' => 'edit-mobile-device-brands', 'module' => 'mobile-device-brands', 'label' => 'Edit Mobile Device Brands'],
            ['name' => 'delete-mobile-device-brands', 'module' => 'mobile-device-brands', 'label' => 'Delete Mobile Device Brands'],

            // MobileTrackingStatus management
            ['name' => 'manage-mobile-tracking-statuses', 'module' => 'mobile-tracking-statuses', 'label' => 'Manage Mobile Tracking Statuses'],
            ['name' => 'manage-any-mobile-tracking-statuses', 'module' => 'mobile-tracking-statuses', 'label' => 'Manage Any Mobile Tracking Statuses'],
            ['name' => 'manage-own-mobile-tracking-statuses', 'module' => 'mobile-tracking-statuses', 'label' => 'Manage Own Mobile Tracking Statuses'],
            ['name' => 'create-mobile-tracking-statuses', 'module' => 'mobile-tracking-statuses', 'label' => 'Create Mobile Tracking Statuses'],
            ['name' => 'edit-mobile-tracking-statuses', 'module' => 'mobile-tracking-statuses', 'label' => 'Edit Mobile Tracking Statuses'],
            ['name' => 'delete-mobile-tracking-statuses', 'module' => 'mobile-tracking-statuses', 'label' => 'Delete Mobile Tracking Statuses'],


            // MobileServiceContract management
            ['name' => 'manage-mobile-service-contracts', 'module' => 'mobile-service-contracts', 'label' => 'Manage Service Contracts'],
            ['name' => 'manage-any-mobile-service-contracts', 'module' => 'mobile-service-contracts', 'label' => 'Manage All Mobile Service Contracts'],
            ['name' => 'manage-own-mobile-service-contracts', 'module' => 'mobile-service-contracts', 'label' => 'Manage Own Mobile Service Contracts'],
            ['name' => 'view-mobile-service-contracts', 'module' => 'mobile-service-contracts', 'label' => 'View Mobile Service Contracts'],
            ['name' => 'create-mobile-service-contracts', 'module' => 'mobile-service-contracts', 'label' => 'Create Service Contracts'],
            ['name' => 'edit-mobile-service-contracts', 'module' => 'mobile-service-contracts', 'label' => 'Edit Service Contracts'],
            ['name' => 'delete-mobile-service-contracts', 'module' => 'mobile-service-contracts', 'label' => 'Delete Service Contracts'],

            // MobileServicePolicy management
            ['name' => 'manage-mobile-service-policies', 'module' => 'mobile-service-policies', 'label' => 'Manage Service Policies'],
            ['name' => 'manage-any-mobile-service-policies', 'module' => 'mobile-service-policies', 'label' => 'Manage All Mobile Service Policies'],
            ['name' => 'manage-own-mobile-service-policies', 'module' => 'mobile-service-policies', 'label' => 'Manage Own Mobile Service Policies'],
            ['name' => 'view-mobile-service-policies', 'module' => 'mobile-service-policies', 'label' => 'View Mobile Service Policies'],
            ['name' => 'create-mobile-service-policies', 'module' => 'mobile-service-policies', 'label' => 'Create Mobile Service Policies'],
            ['name' => 'edit-mobile-service-policies', 'module' => 'mobile-service-policies', 'label' => 'Edit Mobile Service Policies'],
            ['name' => 'delete-mobile-service-policies', 'module' => 'mobile-service-policies', 'label' => 'Delete Mobile Service Policies'],

            // Site Settings management
            ['name' => 'manage-mobile-site-settings', 'module' => 'site-settings', 'label' => 'Manage Site Settings'],
            ['name' => 'edit-mobile-site-settings', 'module' => 'site-settings', 'label' => 'Edit Site Settings'],

            // Banner Section management
            ['name' => 'manage-mobile-banner-section', 'module' => 'banner-section', 'label' => 'Manage Banner Section'],
            ['name' => 'edit-mobile-banner-section', 'module' => 'banner-section', 'label' => 'Edit Banner Section'],

            // About Section management
            ['name' => 'manage-mobile-about--section', 'module' => 'about-section', 'label' => 'Manage About Section'],
            ['name' => 'edit-mobile-about--section', 'module' => 'about-section', 'label' => 'Edit About Section'],

            // About Title Section management
            ['name' => 'manage-mobile-about-title-section', 'module' => 'about-title-section', 'label' => 'Manage About Title Section'],
            ['name' => 'edit-mobile-about-title-section', 'module' => 'about-title-section', 'label' => 'Edit About Title Section'],

            // Contact Us Section management
            ['name' => 'manage-mobile-contact-us-section', 'module' => 'contact-us-section', 'label' => 'Manage Contact Us Section'],
            ['name' => 'edit-mobile-contact-us-section', 'module' => 'contact-us-section', 'label' => 'Edit Contact Us Section'],

            // Social Links Section management
            ['name' => 'manage-mobile-social-links-section', 'module' => 'social-links-section', 'label' => 'Manage Social Links Section'],
            ['name' => 'edit-mobile-social-links-section', 'module' => 'social-links-section', 'label' => 'Edit Social Links Section'],

            // FAQs Section management
            ['name' => 'manage-mobile-faqs-section', 'module' => 'faqs-section', 'label' => 'Manage FAQs Section'],
            ['name' => 'edit-mobile-faqs-section', 'module' => 'faqs-section', 'label' => 'Edit FAQs Section'],

            // Mobile Service Custom Pages management
            ['name' => 'manage-mobile-service-custom-pages', 'module' => 'mobile-service-custom-pages', 'label' => 'Manage Mobile Service Custom Pages'],
            ['name' => 'create-mobile-service-custom-pages', 'module' => 'mobile-service-custom-pages', 'label' => 'Create Mobile Service Custom Pages'],
            ['name' => 'edit-mobile-service-custom-pages', 'module' => 'mobile-service-custom-pages', 'label' => 'Edit Mobile Service Custom Pages'],
            ['name' => 'delete-mobile-service-custom-pages', 'module' => 'mobile-service-custom-pages', 'label' => 'Delete Mobile Service Custom Pages'],

            // Home Section management
            ['name' => 'manage-mobile-home-section', 'module' => 'home-section', 'label' => 'Manage Home Section'],
            ['name' => 'edit-mobile-home-section', 'module' => 'home-section', 'label' => 'Edit Home Section'],

            // Contacts management
            ['name' => 'manage-mobile-service-contacts', 'module' => 'mobile-service-contacts', 'label' => 'Manage Contacts'],
            ['name' => 'manage-any-mobile-service-contacts', 'module' => 'mobile-service-contacts', 'label' => 'Manage Any Contacts'],
            ['name' => 'manage-own-mobile-service-contacts', 'module' => 'mobile-service-contacts', 'label' => 'Manage Own Contacts'],
            ['name' => 'delete-mobile-service-contacts', 'module' => 'mobile-service-contacts', 'label' => 'Delete Contacts'],

            // Review management
            ['name' => 'manage-mobile-service-reviews', 'module' => 'mobile-service-reviews', 'label' => 'Manage Review'],
            ['name' => 'manage-any-mobile-service-reviews', 'module' => 'mobile-service-reviews', 'label' => 'Manage Any Review'],
            ['name' => 'manage-own-mobile-service-reviews', 'module' => 'mobile-service-reviews', 'label' => 'Manage Own Review'],
            ['name' => 'view-mobile-service-reviews', 'module' => 'mobile-service-reviews', 'label' => 'View Review'],
            ['name' => 'delete-mobile-service-reviews', 'module' => 'mobile-service-reviews', 'label' => 'Delete Review'],

            //  Pending Serivce request
            ['name' => 'manage-mobile-pending-requests', 'module' => 'mobile-pending-requests', 'label' => 'Manage Pending Serivce request'],
            ['name' => 'manage-any-mobile-pending-requests', 'module' => 'mobile-pending-requests', 'label' => 'Manage Any Pending Serivce request'],
            ['name' => 'manage-own-mobile-pending-requests', 'module' => 'mobile-pending-requests', 'label' => 'Manage Own Pending Serivce request'],
            ['name' => 'approve-mobile-pending-requests', 'module' => 'mobile-pending-requests', 'label' => 'Approve Pending Serivce request'],
            ['name' => 'reject-mobile-pending-requests', 'module' => 'mobile-pending-requests', 'label' => 'Reject Pending Serivce request'],
            ['name' => 'view-mobile-pending-requests', 'module' => 'mobile-pending-requests', 'label' => 'View Pending Serivce request'],
            ['name' => 'delete-mobile-pending-requests', 'module' => 'mobile-pending-requests', 'label' => 'Delete Pending Serivce request'],


            //  Serivce request
            ['name' => 'manage-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'Manage Serivce request'],
            ['name' => 'manage-any-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'Manage Any Serivce request'],
            ['name' => 'manage-own-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'Manage Own Serivce request'],
            ['name' => 'create-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'Create Serivce request'],
            ['name' => 'edit-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'Edit Serivce request'],
            ['name' => 'view-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'View Serivce request'],
            ['name' => 'delete-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'Delete Serivce request'],
            ['name' => 'tracking-mobile-service-requests', 'module' => 'mobile-service-requests', 'label' => 'Tracking Serivce request'],
            ['name' => 'end-mobile-service-repair', 'module' => 'mobile-service-requests', 'label' => 'End Repair Request'],
            ['name' => 'assign-mobile-service-technician', 'module' => 'mobile-service-requests', 'label' => 'Assign Technician Serivce request'],

            //  Serivce History
            ['name' => 'manage-mobile-service-history', 'module' => 'mobile-service-history', 'label' => 'Manage Serivce history'],
            ['name' => 'manage-any-mobile-service-history', 'module' => 'mobile-service-history', 'label' => 'Manage Any Serivce history'],
            ['name' => 'manage-own-mobile-service-history', 'module' => 'mobile-service-history', 'label' => 'Manage Own Serivce history'],
            ['name' => 'view-mobile-service-history', 'module' => 'mobile-service-history', 'label' => 'View Serivce history'],
            ['name' => 'delete-mobile-service-history', 'module' => 'mobile-service-history', 'label' => 'Delete Serivce history'],

            // Payment management
            ['name' => 'manage-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Manage Payments'],
            ['name' => 'manage-any-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Manage All Payments'],
            ['name' => 'manage-own-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Manage Own Payments'],
            ['name' => 'create-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Create Payments'],
            ['name' => 'view-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'View Payments'],
            ['name' => 'edit-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Edit Payments'],
            ['name' => 'delete-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Delete Payments'],
            ['name' => 'cleared-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Cleared Payments'],
            ['name' => 'cancelled-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Cancelled Payments'],
            ['name' => 'print-mobile-service-payments', 'module' => 'mobile-service-payments', 'label' => 'Print Payments'],

        ];

        $company_role = Role::where('name', 'company')->first();

        foreach ($permission as $perm) {
            $permission_obj = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'module' => $perm['module'],
                    'label' => $perm['label'],
                    'add_on' => 'MobileServiceManagement',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            if ($company_role && !$company_role->hasPermissionTo($permission_obj)) {
                $company_role->givePermissionTo($permission_obj);
            }
        }
    }
}
