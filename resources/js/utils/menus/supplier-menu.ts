import { LayoutGrid, ShoppingCart, FileText, Building2, HelpCircle } from 'lucide-react';
import { NavItem } from '@/types';

export const getSupplierMenu = (t: (key: string) => string): NavItem[] => [
    {
        title: t('Portal Overview'),
        href: route('portal.supplier'),
        icon: LayoutGrid,
        name: 'supplier-overview',
        order: 10,
    },
    {
        title: t('Purchase Orders & RFQs'),
        href: route('portal.supplier') + '#orders',
        icon: ShoppingCart,
        name: 'supplier-orders',
        order: 20,
    },
    {
        title: t('Bills & Delivery Notes'),
        href: route('portal.supplier') + '#bills',
        icon: FileText,
        name: 'supplier-bills',
        order: 30,
    },
    {
        title: t('Supplier Profile & Banking'),
        href: route('portal.supplier') + '#profile',
        icon: Building2,
        name: 'supplier-profile',
        order: 40,
    },
];

