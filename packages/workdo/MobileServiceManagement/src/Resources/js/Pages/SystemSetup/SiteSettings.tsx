import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import SystemSetupSidebar from "./SystemSetupSidebar";
import { getImagePath } from '@/utils/helpers';
import MediaPicker from '@/components/MediaPicker';
import { Save } from 'lucide-react';

interface SiteSettingsProps {
    settings: {
        site_logo?: string;
        footer_logo?: string;
        favicon?: string;
        title?: string;
        subtitle?: string;
        footer_text?: string;
        footer_description?: string;
    };
}

export default function SiteSettings() {
    const { t } = useTranslation();
    const { settings } = usePage<SiteSettingsProps>().props;

    const defaultLogos = {
        site_logo: getImagePath('/packages/workdo/MobileServiceManagement/src/Resources/assets/images/logo.png'),
        footer_logo: getImagePath('/packages/workdo/MobileServiceManagement/src/Resources/assets/images/footer-logo.png'),
        favicon: getImagePath('/packages/workdo/MobileServiceManagement/src/Resources/assets/images/favicon.png')
    };

    const { data, setData, post, processing, errors } = useForm({
        site_logo: settings?.site_logo || '',
        footer_logo: settings?.footer_logo || '',
        favicon: settings?.favicon || '',
        title: settings?.title || '',
        subtitle: settings?.subtitle || '',
        footer_text: settings?.footer_text || '',
        footer_description: settings?.footer_description || '',
    });

    useFlashMessages();

    const handleMediaSelect = (name: string, url: string | string[]) => {
        const urlString = Array.isArray(url) ? url[0] || '' : url;
        const baseName = urlString ? urlString.split('/').pop() || urlString : '';
        setData(name as keyof typeof data, baseName);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.site-settings.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('Site Settings') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('Site Settings')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="site-settings" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">{t('Site Settings')}</h3>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                    <div className="space-y-3">
                                        <Label>{t('Site Logo')}</Label>
                                        <div className="flex flex-col gap-3">
                                            <div className="border rounded-md p-4 flex items-center justify-center bg-muted/30 h-32">
                                                <img
                                                    src={data.site_logo ? getImagePath(data.site_logo) : defaultLogos.site_logo}
                                                    alt={t('Site Logo')}
                                                    className="max-h-full max-w-full object-contain"
                                                />
                                            </div>
                                            <MediaPicker
                                                value={data.site_logo || 'header-logo.png'}
                                                onChange={(url) => handleMediaSelect('site_logo', url)}
                                                placeholder={t('Select site logo...')}
                                                showPreview={false}
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <Label>{t('Footer Logo')}</Label>
                                        <div className="flex flex-col gap-3">
                                            <div className="border rounded-md p-4 flex items-center justify-center bg-gray-800 h-32">
                                                <img
                                                    src={data.footer_logo ? getImagePath(data.footer_logo) : defaultLogos.footer_logo}
                                                    alt={t('Footer Logo')}
                                                    className="max-h-full max-w-full object-contain"
                                                />
                                            </div>
                                            <MediaPicker
                                                value={data.footer_logo || 'footer-logo.png'}
                                                onChange={(url) => handleMediaSelect('footer_logo', url)}
                                                placeholder={t('Select footer logo...')}
                                                showPreview={false}
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <Label>{t('Favicon')}</Label>
                                        <div className="flex flex-col gap-3">
                                            <div className="border rounded-md p-4 flex items-center justify-center bg-muted/30 h-32">
                                                <img
                                                    src={data.favicon ? getImagePath(data.favicon) : defaultLogos.favicon}
                                                    alt={t('Favicon')}
                                                    className="h-16 w-16 object-contain"
                                                />
                                            </div>
                                            <MediaPicker
                                                value={data.favicon || 'favicon.png'}
                                                onChange={(url) => handleMediaSelect('favicon', url)}
                                                placeholder={t('Select favicon...')}
                                                showPreview={false}
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-6 mb-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <Label htmlFor="title">{t('Title')}</Label>
                                            <Input
                                                id="title"
                                                value={data.title}
                                                onChange={(e) => setData('title', e.target.value)}
                                                placeholder={t('Enter Title')}
                                                required
                                            />
                                            {errors.title && (
                                                <p className="text-red-500 text-sm mt-1">{errors.title}</p>
                                            )}
                                        </div>

                                        <div>
                                            <Label htmlFor="subtitle">{t('Subtitle')}</Label>
                                            <Input
                                                id="subtitle"
                                                value={data.subtitle}
                                                onChange={(e) => setData('subtitle', e.target.value)}
                                                placeholder={t('Enter Subtitle')}
                                            />
                                            {errors.subtitle && (
                                                <p className="text-red-500 text-sm mt-1">{errors.subtitle}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div>
                                        <Label htmlFor="footer_text">{t('Footer Text')}</Label>
                                        <Input
                                            id="footer_text"
                                            value={data.footer_text}
                                            onChange={(e) => setData('footer_text', e.target.value)}
                                            placeholder={t('Enter Footer Text')}
                                        />
                                        {errors.footer_text && (
                                            <p className="text-red-500 text-sm mt-1">{errors.footer_text}</p>
                                        )}
                                    </div>

                                    <div>
                                        <Label htmlFor="footer_description">{t('Footer Description')}</Label>
                                        <Textarea
                                            id="footer_description"
                                            value={data.footer_description}
                                            onChange={(e) => setData('footer_description', e.target.value)}
                                            placeholder={t('Enter Footer Description')}
                                            rows={3}
                                        />
                                        {errors.footer_description && (
                                            <p className="text-red-500 text-sm mt-1">{errors.footer_description}</p>
                                        )}
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