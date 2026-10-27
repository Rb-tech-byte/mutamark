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
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/ui/input-error';

interface FaqsSectionProps {
    settings: {
        faqs?: string;
    };
}

export default function FaqsSection() {
    const { t } = useTranslation();
    const { settings } = usePage<FaqsSectionProps>().props;

    const parsedFaqs = settings?.faqs ? JSON.parse(settings.faqs) : [];

    const { data, setData, post, processing, errors } = useForm({
        faqs: parsedFaqs.length > 0 ? parsedFaqs : [{ question: '', answer: '' }],
    });

    useFlashMessages();

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.faqs-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('FAQ') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('FAQ')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="faqs-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">{t('FAQ')}</h3>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-8">
                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Frequently Asked Questions')}</h3>
                                    <div className="space-y-4">
                                    <Repeater
                                        fields={[
                                            {
                                                name: 'question',
                                                label: t('Question'),
                                                type: 'text',
                                                placeholder: t('Enter Question'),
                                                required: true,
                                                layout: { colSpan: 3 },
                                            },
                                            {
                                                name: 'answer',
                                                label: t('Answer'),
                                                type: 'textarea',
                                                placeholder: t('Enter Answer'),
                                                required: true,
                                                layout: { colSpan: 3 },
                                            },
                                        ]}
                                        value={data.faqs.map((faq, index) => ({
                                            id: `faq-${index}`,
                                            question: faq.question || '',
                                            answer: faq.answer || ''
                                        }))}
                                        onChange={(items) => {
                                            const faqs = items.map(({ id, ...item }) => item);
                                            setData('faqs', faqs);
                                        }}
                                        layout={{
                                            type: "grid",
                                            columns: 3,
                                        }}
                                        addButtonText={t('Add FAQ')}
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