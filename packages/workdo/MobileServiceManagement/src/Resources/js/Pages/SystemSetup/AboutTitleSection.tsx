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
import { Save } from 'lucide-react';
import { Repeater } from '@/components/ui/repeater';

interface AboutTitleSectionProps {
    settings: any;
    users: Array<{ id: number; name: string }>;
}

export default function AboutTitleSection() {
    const { t } = useTranslation();
    const { settings, users } = usePage<AboutTitleSectionProps>().props;

    const { data, setData, post, processing, errors } = useForm({
        story_title: settings?.story_title || '',
        story_description: settings?.story_description || '',
        story_image: settings?.story_image || '',
        about_cta_title: settings?.about_cta_title || '',
        about_cta_description: settings?.about_cta_description || '',
        mission_title: settings?.mission_title || '',
        mission_description: settings?.mission_description || '',
        missions: settings?.missions?.length ? settings.missions : [{ icon: '', title: '', description: '' }],
        team_title: settings?.team_title || '',
        team_description: settings?.team_description || '',
        team_members: settings?.team_members?.length
            ? settings.team_members
            : [{ user_id: '', experience: '', position: '', description: '' }],
    });

    useFlashMessages();

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.about-title-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('About Title Section') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('About Title Section')} />

            <div className="flex flex-col md:flex-row gap-6">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="about-title-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">
                                {t('About Title Section')}
                            </h3>
                        </CardHeader>

                        {/* 🔧 FIXED PADDING */}
                        <CardContent className="pb-0">
                            {/* 🔧 FIXED SPACING */}
                            <form onSubmit={handleSubmit} className="space-y-6">

                                {/* OUR STORY */}
                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Our Story')}</h3>

                                    <div>
                                        <Label>{t('Title')}</Label>
                                        <Input
                                            value={data.story_title}
                                            onChange={e => setData('story_title', e.target.value)}
                                        />
                                        <InputError message={errors.story_title} />
                                    </div>

                                    <div>
                                        <Label>{t('Description')}</Label>
                                        <Textarea
                                            rows={3}
                                            value={data.story_description}
                                            onChange={e => setData('story_description', e.target.value)}
                                        />
                                    </div>
                                </div>

                                {/* CTA */}
                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('CTA Section')}</h3>
                                    <div>
                                        <Label>{t('Title')}</Label>
                                        <Input
                                            placeholder={t('Title')}
                                            value={data.about_cta_title}
                                            onChange={e => setData('about_cta_title', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('Description')}</Label>
                                        <Textarea
                                            rows={3}
                                            placeholder={t('Description')}
                                            value={data.about_cta_description}
                                            onChange={e => setData('about_cta_description', e.target.value)}
                                        />
                                    </div>
                                </div>

                                {/* MISSION */}
                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Our Mission')}</h3>
                                    <div>
                                        <Label>{t('Title')}</Label>
                                        <Input
                                            placeholder={t('Title')}
                                            value={data.mission_title}
                                            onChange={e => setData('mission_title', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('Description')}</Label>
                                        <Textarea
                                            rows={3}
                                            placeholder={t('Description')}
                                            value={data.mission_description}
                                            onChange={e => setData('mission_description', e.target.value)}
                                        />
                                    </div>

                                </div>

                                {/* TEAM */}
                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Our Team')}</h3>
                                    <div>
                                        <Label>{t('Title')}</Label>
                                        <Input
                                            placeholder={t('Title')}
                                            value={data.team_title}
                                            onChange={e => setData('team_title', e.target.value)}
                                        />
                                    </div>
                                    <div>
                                        <Label>{t('Description')}</Label>
                                        <Textarea
                                            rows={3}
                                            placeholder={t('Description')}
                                            value={data.team_description}
                                            onChange={e => setData('team_description', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end pt-2 pb-2">
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