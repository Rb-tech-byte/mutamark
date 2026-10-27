import { Palette, Building, SettingsIcon, Mail, DollarSign, CreditCard, Bot, Smartphone } from 'lucide-react';

export interface SettingMenuItem {
  order: number;
  title: string;
  href: string;
  icon: any;
  permission: string;
  component: string;
}

export const getCompanySettings = (t: (key: string) => string): SettingMenuItem[] => [
  {
    order: 10,
    title: t('Brand Settings'),
    href: '#brand-settings',
    icon: Palette,
    permission: 'manage-brand-settings',
    component: 'brand-settings'
  },
  {
    order: 20,
    title: t('System Settings'),
    href: '#system-settings',
    icon: SettingsIcon,
    permission: 'manage-system-settings',
    component: 'system-settings'
  },
  {
    order: 30,
    title: t('Company Settings'),
    href: '#company-settings',
    icon: Building,
    permission: 'manage-company-settings',
    component: 'company-settings'
  },
  {
    order: 35,
    title: t('AI Agent Settings'),
    href: '#ai-agent-settings',
    icon: Bot,
    permission: 'manage-ai-agent-settings',
    component: 'ai-agent-settings'
  },
  {
    order: 40,
    title: t('Currency Settings'),
    href: '#currency-settings',
    icon: DollarSign,
    permission: 'manage-currency-settings',
    component: 'currency-settings'
  },
  {
    order: 500,
    title: t('Email Settings'),
    href: '#email-settings',
    icon: Mail,
    permission: 'manage-email-settings',
    component: 'email-settings'
  },
  {
    order: 510,
    title: t('Email Notification Settings'),
    href: '#email-notification-settings',
    icon: Mail,
    permission: 'manage-email-notification-settings',
    component: 'email-notification-settings'
  },
  {
    order: 1010,
    title: t('PayPal Settings'),
    href: '#paypal-settings',
    icon: CreditCard,
    permission: 'manage-paypal-settings',
    component: 'paypal-settings'
  },
  {
    order: 1020,
    title: t('Stripe Settings'),
    href: '#stripe-settings',
    icon: CreditCard,
    permission: 'manage-stripe-settings',
    component: 'stripe-settings'
  },
  {
    order: 1030,
    title: t('Selcom Paytech Settings'),
    href: '#selcom-settings',
    icon: Smartphone,
    permission: 'manage-selcom-settings',
    component: 'selcom-settings'
  },
  {
    order: 1040,
    title: t('Pesapal Settings'),
    href: '#pesapal-settings',
    icon: CreditCard,
    permission: 'manage-pesapal-settings',
    component: 'pesapal-settings'
  },
  {
    order: 1050,
    title: t('Lipa Namba (Mobile Money) Settings'),
    href: '#lipa-namba-settings',
    icon: Smartphone,
    permission: 'manage-lipa-namba-settings',
    component: 'lipa-namba-settings'
  },
  {
    order: 1060,
    title: t('Bank Transfer Settings'),
    href: '#bank-transfer-settings',
    icon: CreditCard,
    permission: 'manage-bank-transfer-settings',
    component: 'bank-transfer-settings'
  }
];

