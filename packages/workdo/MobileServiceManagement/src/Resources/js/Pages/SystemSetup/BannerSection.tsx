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
import InputError from '@/components/ui/input-error';
import SystemSetupSidebar from "./SystemSetupSidebar";
import { getImagePath } from '@/utils/helpers';
import MediaPicker from '@/components/MediaPicker';
import { IconPicker } from '@/components/ui/icon-picker';
import { Save } from 'lucide-react';

interface BannerSectionProps {
    settings: {
        banner_title?: string;
        banner_subtitle?: string;
        image?: string;
        image_icon?: string;
        image_text?: string;
        description?: string;
    };
}

export default function BannerSection() {
    const { t } = useTranslation();
    const { settings } = usePage<BannerSectionProps>().props;

    const { data, setData, post, processing, errors } = useForm({
        banner_title: settings?.banner_title || '',
        banner_subtitle: settings?.banner_subtitle || '',
        image: settings?.image || '',
        image_icon: settings?.image_icon || '',
        image_text: settings?.image_text || '',
        description: settings?.description || '',
    });

    useFlashMessages();

    const handleMediaSelect = (name: string, url: string | string[]) => {
        const urlString = Array.isArray(url) ? url[0] || '' : url;
        const baseName = urlString ? urlString.split('/').pop() || urlString : '';
        setData(name as keyof typeof data, baseName);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.banner-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('Banner Section') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('Banner Section')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="banner-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">{t('Banner Section')}</h3>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit}>
                                <div className="space-y-6 mb-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <Label htmlFor="banner_title">{t('Banner Title')}</Label>
                                            <Input
                                                id="banner_title"
                                                value={data.banner_title}
                                                onChange={(e) => setData('banner_title', e.target.value)}
                                                placeholder={t('Enter Banner Title')}
                                                required
                                            />
                                            <InputError message={errors.banner_title} />
                                        </div>

                                        <div>
                                            <Label htmlFor="banner_subtitle">{t('Banner Subtitle')}</Label>
                                            <Input
                                                id="banner_subtitle"
                                                value={data.banner_subtitle}
                                                onChange={(e) => setData('banner_subtitle', e.target.value)}
                                                placeholder={t('Enter Banner Subtitle')}
                                            />
                                            <InputError message={errors.banner_subtitle} />
                                        </div>
                                    </div>

                                    <div className="space-y-6">
                                        <div className="grid grid-cols-12 gap-4">
                                            <div className="col-span-10">
                                                <Label>{t('Banner Image')}</Label>
                                                <MediaPicker
                                                    value={data.image || 'banner-image.jpg'}
                                                    onChange={(url) => handleMediaSelect('image', url)}
                                                    placeholder={t('Select banner image...')}
                                                    showPreview={false}
                                                />
                                                <InputError message={errors.image} />
                                            </div>
                                            <div className="col-span-2">
                                                <div className="flex items-center justify-center h-24 w-full">
                                                    <img
                                                        src={data.image ? getImagePath(data.image) : getImagePath('/packages/workdo/MobileServiceManagement/src/Resources/assets/images/banner-image.jpg')}
                                                        alt={t('Banner Image')}
                                                        className="max-h-full max-w-full object-contain"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <Label htmlFor="image_icon">{t('Image Icon')}</Label>
                                            <IconPicker
                                                value={data.image_icon}
                                                onChange={(value) => setData('image_icon', value)}
                                                placeholder={t('Select an icon')}
                                            />
                                            <InputError message={errors.image_icon} />
                                        </div>

                                        <div>
                                            <Label htmlFor="image_text">{t('Image Text')}</Label>
                                            <Input
                                                id="image_text"
                                                value={data.image_text}
                                                onChange={(e) => setData('image_text', e.target.value)}
                                                placeholder={t('Enter Image Text')}
                                            />
                                            <InputError message={errors.image_text} />
                                        </div>
                                    </div>

                                    <div>
                                        <Label htmlFor="description">{t('Description')}</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder={t('Enter Description')}
                                            rows={3}
                                        />
                                        <InputError message={errors.description} />
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