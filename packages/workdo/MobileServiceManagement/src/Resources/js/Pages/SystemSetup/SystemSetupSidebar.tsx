import { router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { cn } from '@/lib/utils';
import { MapPin, Truck, Package, Phone, HelpCircle, MessageSquare, Share2, Users, Zap, Tag, Building2, Settings, Image, Home, Info, Type, FileText, Layout } from "lucide-react";


interface SidebarItem {
    key: string;
    label: string;
    icon: React.ComponentType<{ className?: string }>;
    route: string;
    permission: string;
}

interface SystemSetupSidebarProps {
    activeItem?: string;
    onSectionChange?: (section: string) => void;
}

export default function SystemSetupSidebar({ activeItem, onSectionChange }: SystemSetupSidebarProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props as any;
    const currentRoute = route().current();

    const sidebarItems: SidebarItem[] = [
        {
            key: 'mobile-device-brands',
            label: t('Device Brands'),
            icon: Building2,
            route: 'mobile-service.device-brands.index',
            permission: 'manage-mobile-device-brands'
        },
        {
            key: 'mobile-tracking-statuses',
            label: t('Tracking statuses'),
            icon: Tag,
            route: 'mobile-service.tracking-statuses.index',
            permission: 'manage-mobile-tracking-statuses'
        },
        {
            key: 'site-settings',
            label: t('Site Settings'),
            icon: Settings,
            route: 'mobile-service.site-settings.index',
            permission: 'manage-mobile-site-settings'
        },
        {
            key: 'banner-section',
            label: t('Banner Section'),
            icon: Image,
            route: 'mobile-service.banner-section.index',
            permission: 'manage-mobile-banner-section'
        },
        {
            key: 'home-section',
            label: t('Home Section'),
            icon: Home,
            route: 'mobile-service.home-section.index',
            permission: 'manage-mobile-home-section'
        },
        {
            key: 'about-section',
            label: t('About Section'),
            icon: Info,
            route: 'mobile-service.about-section.index',
            permission: 'manage-mobile-about--section'
        },
        {
            key: 'about-title-section',
            label: t('About Title Section'),
            icon: Type,
            route: 'mobile-service.about-title-section.index',
            permission: 'manage-mobile-about-title-section'
        },
        {
            key: 'contact-us-section',
            label: t('Contact Us'),
            icon: Phone,
            route: 'mobile-service.contact-us-section.index',
            permission: 'manage-mobile-contact-us-section'
        },
        {
            key: 'social-links-section',
            label: t('Social Links'),
            icon: Share2,
            route: 'mobile-service.social-links-section.index',
            permission: 'manage-mobile-social-links-section'
        },
        {
            key: 'faqs-section',
            label: t('FAQs'),
            icon: HelpCircle,
            route: 'mobile-service.faqs-section.index',
            permission: 'manage-mobile-faqs-section'
        },
        {
            key: 'custom-pages',
            label: t('Custom Pages'),
            icon: FileText,
            route: 'mobile-service.custom-pages.index',
            permission: 'manage-mobile-service-custom-pages'
        },
        
    ];

    const filteredItems = sidebarItems.filter(item =>
        auth.user?.permissions?.includes(item.permission)
    );

    return (
        <div className="sticky top-4">
            <ScrollArea className="h-[calc(100vh-8rem)]">
                <div className="pr-4 space-y-1">
                    {filteredItems.map((item) => {
                        const Icon = item.icon;
                        const isActive = activeItem === item.key || currentRoute === item.route;

                        return (
                            <Button
                                key={item.key}
                                variant="ghost"
                                className={cn('w-full justify-start', {
                                    'bg-muted font-medium': isActive,
                                })}
                                onClick={() => {
                                    router.get(route(item.route));
                                    onSectionChange?.(item.key);
                                }}
                            >
                                <Icon className="h-4 w-4 mr-2" />
                                {item.label}
                            </Button>
                        );
                    })}
                </div>
            </ScrollArea>
        </div>
    );
}