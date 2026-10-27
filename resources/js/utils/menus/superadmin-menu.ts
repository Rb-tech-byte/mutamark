import { LayoutGrid, Users, Building2, Settings, Shield, Image, Package, CreditCard, FileText, Ticket, Mail, Bell, Headphones, Clock, ShieldCheck} from 'lucide-react';
import { NavItem } from '@/types';

export const getSuperAdminMenu = (t: (key: string) => string): NavItem[] => [
    { title: t('Database Backups'), href: route('backups.index'), icon: ShieldCheck, name: 'backups', order: 26 },
    {
        title: t('Dashboard'),
        href: route('dashboard'),
        icon: LayoutGrid,
        permission: 'manage-dashboard',
        order: 1,
    },
    {
        title: t('Users'),
        href: route('users.index'),
        icon: Users,
        permission: 'manage-users',
        order: 20,
    },
    {
        title: t('Verification Ledger'),
        href: route('document-verifications.index'),
        icon: ShieldCheck,
        order: 25,
    },
    {
        title: t('Helpdesk'),
        icon: Headphones,
        permission: 'manage-helpdesk-tickets',
        order: 2750,
        children: [
            {
                title: t('Today\'s Tickets'),
                href: route('helpdesk-tickets.today'),
                permission: 'manage-any-helpdesk-tickets',
            },
            {
                title: t('All Tickets'),
                href: route('helpdesk-tickets.index'),
                permission: 'manage-any-helpdesk-tickets',
            },
            {
                title: t('Categories'),
                href: route('helpdesk-categories.index'),
                permission: 'manage-helpdesk-categories',
            }
        ]
    },

    {
        title: t('Email Templates'),
        href: route('email-templates.index'),
        icon: Mail,
        permission: 'manage-email-templates',
        order: 2850,
    },
    {
        title: t('Notification Templates'),
        href: route('notification-templates.index'),
        icon: Bell,
        permission: 'manage-notification-templates',
        order: 2900,
    },
    {
        title: t('Media Library'),
        href: route('media-library'),
        icon: Image,
        permission: 'manage-media',
        order: 2950,
    },
    {
        title: t('Modules'),
        href: route('modules.index'),
        icon: Package,
        permission: 'manage-add-on',
        order: 3000,
    },
    {
        title: t('Settings'),
        href: route('settings.index'),
        icon: Settings,
        permission: 'manage-settings',
        order: 3050,
    },
];
