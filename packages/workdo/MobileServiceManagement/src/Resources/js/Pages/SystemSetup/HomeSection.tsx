import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent } from "@/components/ui/card";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/ui/input-error';
import { Save } from "lucide-react";
import SystemSetupSidebar from './SystemSetupSidebar';

interface HomeSectionProps {
    settings: {
        service_tracking_title?: string;
        service_tracking_description?: string;
        expertise_title?: string;
        expertise_description?: string;
        cta_title?: string;
        cta_description?: string;
        testimonial_title?: string;
        testimonial_description?: string;
    };
}

export default function HomeSection() {
    const { t } = useTranslation();
    const { settings } = usePage<HomeSectionProps>().props;

    const { data, setData, post, processing, errors } = useForm({
        service_tracking_title: settings?.service_tracking_title || '',
        service_tracking_description: settings?.service_tracking_description || '',
        expertise_title: settings?.expertise_title || '',
        expertise_description: settings?.expertise_description || '',
        cta_title: settings?.cta_title || '',
        cta_description: settings?.cta_description || '',
        testimonial_title: settings?.testimonial_title || '',
        testimonial_description: settings?.testimonial_description || '',
    });

    useFlashMessages();

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.home-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('Home Section') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('Home Section')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="home-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-lg font-medium">{t('Home Section')}</h3>
                            </div>
                            <form onSubmit={handleSubmit} className="space-y-8">
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div className="border rounded-lg p-6 space-y-4">
                                        <h3 className="text-lg font-semibold">{t('Service Tracking Section')}</h3>
                                        <div className="space-y-4">
                                            <div>
                                                <Label htmlFor="service_tracking_title">{t('Title')}</Label>
                                                <Input
                                                    id="service_tracking_title"
                                                    value={data.service_tracking_title}
                                                    onChange={(e) => setData('service_tracking_title', e.target.value)}
                                                    placeholder={t('Enter service tracking title')}
                                                />
                                                <InputError message={errors.service_tracking_title} />
                                            </div>
                                            <div>
                                                <Label htmlFor="service_tracking_description">{t('Description')}</Label>
                                                <Textarea
                                                    id="service_tracking_description"
                                                    value={data.service_tracking_description}
                                                    onChange={(e) => setData('service_tracking_description', e.target.value)}
                                                    placeholder={t('Enter service tracking description')}
                                                    rows={3}
                                                />
                                                <InputError message={errors.service_tracking_description} />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="border rounded-lg p-6 space-y-4">
                                        <h3 className="text-lg font-semibold">{t('Our Expertise Section')}</h3>
                                        <div className="space-y-4">
                                            <div>
                                                <Label htmlFor="expertise_title">{t('Title')}</Label>
                                                <Input
                                                    id="expertise_title"
                                                    value={data.expertise_title}
                                                    onChange={(e) => setData('expertise_title', e.target.value)}
                                                    placeholder={t('Enter expertise title')}
                                                />
                                                <InputError message={errors.expertise_title} />
                                            </div>
                                            <div>
                                                <Label htmlFor="expertise_description">{t('Description')}</Label>
                                                <Textarea
                                                    id="expertise_description"
                                                    value={data.expertise_description}
                                                    onChange={(e) => setData('expertise_description', e.target.value)}
                                                    placeholder={t('Enter expertise description')}
                                                    rows={3}
                                                />
                                                <InputError message={errors.expertise_description} />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div className="border rounded-lg p-6 space-y-4">
                                        <h3 className="text-lg font-semibold">{t('CTA Section')}</h3>
                                        <div className="space-y-4">
                                            <div>
                                                <Label htmlFor="cta_title">{t('Title')}</Label>
                                                <Input
                                                    id="cta_title"
                                                    value={data.cta_title}
                                                    onChange={(e) => setData('cta_title', e.target.value)}
                                                    placeholder={t('Enter CTA title')}
                                                />
                                                <InputError message={errors.cta_title} />
                                            </div>
                                            <div>
                                                <Label htmlFor="cta_description">{t('Description')}</Label>
                                                <Textarea
                                                    id="cta_description"
                                                    value={data.cta_description}
                                                    onChange={(e) => setData('cta_description', e.target.value)}
                                                    placeholder={t('Enter CTA description')}
                                                    rows={3}
                                                />
                                                <InputError message={errors.cta_description} />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="border rounded-lg p-6 space-y-4">
                                        <h3 className="text-lg font-semibold">{t('Testimonial Section')}</h3>
                                        <div className="space-y-4">
                                            <div>
                                                <Label htmlFor="testimonial_title">{t('Title')}</Label>
                                                <Input
                                                    id="testimonial_title"
                                                    value={data.testimonial_title}
                                                    onChange={(e) => setData('testimonial_title', e.target.value)}
                                                    placeholder={t('Enter testimonial title')}
                                                />
                                                <InputError message={errors.testimonial_title} />
                                            </div>
                                            <div>
                                                <Label htmlFor="testimonial_description">{t('Description')}</Label>
                                                <Textarea
                                                    id="testimonial_description"
                                                    value={data.testimonial_description}
                                                    onChange={(e) => setData('testimonial_description', e.target.value)}
                                                    placeholder={t('Enter testimonial description')}
                                                    rows={3}
                                                />
                                                <InputError message={errors.testimonial_description} />
                                            </div>
                                        </div>
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