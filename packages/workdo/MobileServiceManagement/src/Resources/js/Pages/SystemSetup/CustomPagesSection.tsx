import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import SystemSetupSidebar from "./SystemSetupSidebar";
import { Save } from 'lucide-react';


interface CustomPagesSectionProps {
    customPages: Array<{
        id: number;
        name: string;
        title: string;
        description?: string;
        contents: string;
        enable_page_footer: string;
    }>;
}

export default function CustomPagesSection() {
    const { t } = useTranslation();
    const { customPages } = usePage<CustomPagesSectionProps>().props;

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        title: '',
        description: '',
        contents: '',
        enable_page_footer: false,
    });

    useFlashMessages();

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.custom-pages-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('Custom Pages Section') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('Custom Pages Section')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="custom-pages-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">{t('Custom Pages Section')}</h3>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="space-y-4">
                                    <div>
                                        <label htmlFor="name" className="block text-sm font-medium">{t('Name')}</label>
                                        <input
                                            id="name"
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder={t('Enter page name')}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="title" className="block text-sm font-medium">{t('Title')}</label>
                                        <input
                                            id="title"
                                            type="text"
                                            value={data.title}
                                            onChange={(e) => setData('title', e.target.value)}
                                            placeholder={t('Enter page title')}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="description" className="block text-sm font-medium">{t('Description')}</label>
                                        <textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder={t('Enter page description')}
                                            rows={3}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="contents" className="block text-sm font-medium">{t('Contents')}</label>
                                        <textarea
                                            id="contents"
                                            value={data.contents}
                                            onChange={(e) => setData('contents', e.target.value)}
                                            placeholder={t('Enter page contents')}
                                            rows={6}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            required
                                        />
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <input
                                            id="enable_page_footer"
                                            type="checkbox"
                                            checked={data.enable_page_footer}
                                            onChange={(e) => setData('enable_page_footer', e.target.checked)}
                                        />
                                        <label htmlFor="enable_page_footer">{t('Enable Page Footer')}</label>
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