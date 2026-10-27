import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import SystemSetupSidebar from "./SystemSetupSidebar";
import { Save } from 'lucide-react';
import { Repeater } from '@/components/ui/repeater';

interface SocialLinksSectionProps {
    settings: {
        social_links?: string;
    };
}

export default function SocialLinksSection() {
    const { t } = useTranslation();
    const { settings } = usePage<SocialLinksSectionProps>().props;

    const parsedSocialLinks = settings?.social_links ? JSON.parse(settings.social_links) : [];

    const { data, setData, post, processing, errors } = useForm({
        social_links: parsedSocialLinks.length > 0 ? parsedSocialLinks : [{ icon: '', url: '' }],
    });

    useFlashMessages();

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.social-links-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('Social Links') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('Social Links')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="social-links-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">{t('Social Links')}</h3>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-8">
                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Social Links')}</h3>
                                    <div className="space-y-4">
                                        <Repeater
                                            fields={[
                                                { name: 'icon', label: t('Social Media Icon'), type: 'icon', placeholder: t('Select an icon'), required: true },
                                                { name: 'url', label: t('URL'), type: 'text', placeholder: t('Enter social media URL'), required: true }
                                            ]}
                                            value={data.social_links.map((link, index) => ({
                                                id: `link-${index}`,
                                                icon: link.icon || '',
                                                url: link.url || ''
                                            }))}
                                            onChange={(items) => {
                                                const socialLinks = items.map(({ id, ...item }) => item);
                                                setData('social_links', socialLinks);
                                            }}
                                            addButtonText={t('Add Social Link')}
                                            deleteTooltipText={t('Remove')}
                                            minItems={1}
                                            showDefault={true}
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end">
                                    <Button type="submit" disabled={processing}>
                                        <Save className="h-4 w-4 mr-2" />
                                        {processing ? t('Saving...') : t('Save Changes')}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}