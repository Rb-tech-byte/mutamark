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
import { Repeater } from '@/components/ui/repeater';
import { Save } from 'lucide-react';

interface AboutSectionProps {
    settings: {
        about_title?: string;
        about_description?: string;
        about_image?: string;
        about_image_text?: string;
        about_facilities?: string;
    };
}

export default function AboutSection() {
    const { t } = useTranslation();
    const { settings } = usePage<AboutSectionProps>().props;

    const parsedFacilities = settings?.about_facilities || [];

    const { data, setData, post, processing, errors } = useForm({
        about_title: settings?.about_title || '',
        about_description: settings?.about_description || '',
        about_image: settings?.about_image || '',
        about_image_text: settings?.about_image_text || '',
        about_facilities: parsedFacilities.length > 0 ? parsedFacilities : [{ title: '' }],
    });

    useFlashMessages();

    const handleMediaSelect = (name: string, url: string | string[]) => {
        const urlString = Array.isArray(url) ? url[0] || '' : url;
        const baseName = urlString ? urlString.split('/').pop() || urlString : '';
        setData(name as keyof typeof data, baseName);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.about-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('About Section') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('About Section')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="about-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">{t('About Section')}</h3>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit}>
                                <div className="space-y-6 mb-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <Label htmlFor="about_title">{t('Title')}</Label>
                                            <Input
                                                id="about_title"
                                                value={data.about_title}
                                                onChange={(e) => setData('about_title', e.target.value)}
                                                placeholder={t('Enter About Title')}
                                                required
                                            />
                                            <InputError message={errors.about_title} />
                                        </div>

                                        <div>
                                            <Label htmlFor="about_image_text">{t('Image Text')}</Label>
                                            <Input
                                                id="about_image_text"
                                                value={data.about_image_text}
                                                onChange={(e) => setData('about_image_text', e.target.value)}
                                                placeholder={t('Enter Image Text')}
                                            />
                                            <InputError message={errors.about_image_text} />
                                        </div>
                                    </div>

                                    <div>
                                        <Label htmlFor="about_description">{t('Description')}</Label>
                                        <Textarea
                                            id="about_description"
                                            value={data.about_description}
                                            onChange={(e) => setData('about_description', e.target.value)}
                                            placeholder={t('Enter About Description')}
                                            rows={3}
                                        />
                                        <InputError message={errors.about_description} />
                                    </div>

                                    <div className="space-y-6">
                                        <div className="grid grid-cols-12 gap-4">
                                            <div className="col-span-10">
                                                <Label>{t('About Image')}</Label>
                                                <MediaPicker
                                                    value={data.about_image || 'about-page-img.png'}
                                                    onChange={(url) => handleMediaSelect('about_image', url)}
                                                    placeholder={t('Select about image...')}
                                                    showPreview={false}
                                                />
                                                <InputError message={errors.about_image} />
                                            </div>
                                            <div className="col-span-2">
                                                <div className="flex items-center justify-center h-24 w-full">
                                                    <img
                                                        src={data.about_image ? getImagePath(data.about_image) : getImagePath('/packages/workdo/MobileServiceManagement/src/Resources/assets/images/about-page-img.png')}
                                                        alt={t('About Image')}
                                                        className="max-h-full max-w-full object-contain"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <Label className="text-lg font-medium mb-4 block">{t('Facility & Features')}</Label>
                                        <Repeater
                                            fields={[
                                                { name: 'title', label: t('Title'), type: 'text', placeholder: t('Enter facility title'), required: true, layout: { colSpan: 6 }}
                                            ]}
                                            value={data.about_facilities.map((facility, index) => ({
                                                id: `facility-${index}`,
                                                title: facility.title || ''
                                            }))}
                                            onChange={(items) => {
                                                const facilities = items.map(({ id, ...item }) => item);
                                                setData('about_facilities', facilities);
                                            }}
                                            addButtonText={t('Add Facility')}
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