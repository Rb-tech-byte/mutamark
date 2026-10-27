import { Package, Smartphone, FileText, MessageCircle, Star, CreditCard } from 'lucide-react';

declare global {
    function route(name: string): string;
}

export const mobileservicemanagementCompanyMenu = (t: (key: string) => string) => [
    {
        title: t('Mobile Service Dashboard'),
        href: route('mobile-service.index'),
        permission: 'manage-mobile-service-dashboard',
        parent: 'dashboard',
        order: 235,
    },
    {
        title: t('Mobile Service'),
        icon: Smartphone,
        permission: 'manage-mobile-service-management',
        href: '',
        name: 'mobile-service',
        order: 130,

        children: [
            {
                title: t('Pending Requests'),
                href: route('mobile-service-management.pending-service-requests.index'),
                permission: 'manage-mobile-pending-requests',
            },
            {
                title: t('Service Requests'),
                href: route('mobile-service-management.service-requests.index'),
                permission: 'manage-mobile-service-requests',
            },
            {
                title: t('Service History'),
                href: route('mobile-service-management.service-history.index'),
                permission: 'manage-mobile-service-history',
            },
            
            {
                title: t('Payments'),
                href: route('mobile-service-management.payments.index'),
                permission: 'manage-mobile-service-payments',
            },
            {
                title: t('Service Policies'),
                href: route('mobile-service-management.mobile-service-policies.index'),
                permission: 'manage-mobile-service-policies',
            },
            {
                title: t('Service Contracts'),
                href: route('mobile-service-management.mobile-service-contracts.index'),
                permission: 'manage-mobile-service-contracts',
            },
            {
                title: t('Contacts'),
                href: route('mobile-service-management.mobile-service-contacts.index'),
                permission: 'manage-mobile-service-contacts',
            },
            {
                title: t('Reviews'),
                href: route('mobile-service-management.mobile-service-reviews.index'),
                permission: 'manage-mobile-service-reviews',
            },
            {
                title: t('System Setup'),
                href: route('mobile-service.device-brands.index'),
                permission: 'manage-mobile-device-brands',
            },
        ]
    }
];