import { LayoutGrid, Ship, Receipt, Headphones, Replace, UserCheck } from 'lucide-react';
import { NavItem } from '@/types';

export const getCustomerMenu = (t: (key: string) => string): NavItem[] => [
    {
        title: t('Portal Overview'),
        href: route('portal.customer'),
        icon: LayoutGrid,
        name: 'customer-overview',
        order: 10,
    },
    {
        title: t('C&F Shipments & Tracking'),
        href: route('portal.customer') + '#cf-shipments',
        icon: Ship,
        name: 'customer-cf',
        order: 20,
    },
    {
        title: t('Invoices & Statements'),
        href: route('portal.customer') + '#invoices',
        icon: Receipt,
        name: 'customer-invoices',
        order: 30,
    },
    {
        title: t('Commercial Proposals'),
        href: route('portal.customer') + '#proposals',
        icon: Replace,
        name: 'customer-proposals',
        order: 40,
    },
    {
        title: t('Support & Helpdesk'),
        href: route('portal.customer') + '#tickets',
        icon: Headphones,
        name: 'customer-support',
        order: 50,
    },
];

