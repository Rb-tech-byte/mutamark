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
import { TimePicker } from '@/components/ui/time-picker';
import InputError from '@/components/ui/input-error';
import SystemSetupSidebar from "./SystemSetupSidebar";
import { Save } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { PhoneInputComponent } from '@/components/ui/phone-input';


interface ContactUsSectionProps {
    settings: {
        contact_title?: string;
        contact_phone?: string;
        contact_email?: string;
        contact_emergency?: string;
        location_title?: string;
        location_address?: string;
        repair_center_title?: string;
        repair_center_description?: string;
        repair_center_map_code?: string;
        hours_title?: string;
        service_hours?: string;
    };
}

export default function ContactUsSection() {
    const { t } = useTranslation();
    const { settings } = usePage<ContactUsSectionProps>().props;

    const parsedServiceHours = settings?.service_hours && typeof settings.service_hours === 'string' ? JSON.parse(settings.service_hours) : settings?.service_hours || {};

    const defaultHours = {
        monday: { status: 'open', opening_time: '', closing_time: '' },
        tuesday: { status: 'open', opening_time: '', closing_time: '' },
        wednesday: { status: 'open', opening_time: '', closing_time: '' },
        thursday: { status: 'open', opening_time: '', closing_time: '' },
        friday: { status: 'open', opening_time: '', closing_time: '' },
        saturday: { status: 'open', opening_time: '', closing_time: '' },
        sunday: { status: 'closed', opening_time: '', closing_time: '' }
    };

    const { data, setData, post, processing, errors } = useForm({
        contact_title: settings?.contact_title || '',
        contact_phone: settings?.contact_phone || '',
        contact_email: settings?.contact_email || '',
        contact_emergency: settings?.contact_emergency || '',
        location_title: settings?.location_title || '',
        location_address: settings?.location_address || '',
        repair_center_title: settings?.repair_center_title || '',
        repair_center_description: settings?.repair_center_description || '',
        repair_center_map_code: settings?.repair_center_map_code || '',
        hours_title: settings?.hours_title || '',
        service_hours: Object.keys(parsedServiceHours).length > 0 ? parsedServiceHours : defaultHours,
    });

    useFlashMessages();

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service.contact-us-section.store'));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service'), url: route('mobile-service.index') },
                { label: t('System Setup'), url: route('mobile-service.device-brands.index') },
                { label: t('Contact Us') }
            ]}
            pageTitle={t('System Setup')}
        >
            <Head title={t('Contact Us')} />

            <div className="flex flex-col md:flex-row gap-8">
                <div className="md:w-64 flex-shrink-0">
                    <SystemSetupSidebar activeItem="contact-us-section" />
                </div>

                <div className="flex-1">
                    <Card>
                        <CardHeader>
                            <h3 className="text-lg font-medium">{t('Contact Us')}</h3>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-8">
                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Contact Details')}</h3>
                                    <div className="space-y-4">
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <Label htmlFor="contact_title">{t('Contact Title')}</Label>
                                                <Input
                                                    id="contact_title"
                                                    value={data.contact_title}
                                                    onChange={(e) => setData('contact_title', e.target.value)}
                                                    placeholder={t('Enter contact title')}
                                                />
                                                <InputError message={errors.contact_title} />
                                            </div>
                                            <div>
                                                <Label htmlFor="contact_email">{t('Email')}</Label>
                                                <Input
                                                    id="contact_email"
                                                    type="email"
                                                    value={data.contact_email}
                                                    onChange={(e) => setData('contact_email', e.target.value)}
                                                    placeholder={t('Enter email address')}
                                                />
                                                <InputError message={errors.contact_email} />
                                            </div>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <PhoneInputComponent
                                                    label={t('Phone')}
                                                    value={data.contact_phone}
                                                    onChange={(value) => setData('contact_phone', value)}
                                                    placeholder={t('Enter phone number')}
                                                    error={errors.contact_phone}
                                                />
                                            </div>

                                            <div>
                                                <PhoneInputComponent
                                                    label={t('Emergency/Support')}
                                                    value={data.contact_emergency}
                                                    onChange={(value) => setData('contact_emergency', value)}
                                                    placeholder={t('Enter emergency contact')}
                                                    error={errors.contact_emergency}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Our Location')}</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <Label htmlFor="location_title">{t('Location Title')}</Label>
                                            <Input
                                                id="location_title"
                                                value={data.location_title}
                                                onChange={(e) => setData('location_title', e.target.value)}
                                                placeholder={t('Enter location title')}
                                            />
                                            <InputError message={errors.location_title} />
                                        </div>
                                        <div>
                                            <Label htmlFor="location_address">{t('Address')}</Label>
                                            <Textarea
                                                id="location_address"
                                                value={data.location_address}
                                                onChange={(e) => setData('location_address', e.target.value)}
                                                placeholder={t('Enter complete address')}
                                                rows={3}
                                            />
                                            <InputError message={errors.location_address} />
                                        </div>
                                    </div>
                                </div>

                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Our Repair Center')}</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <Label htmlFor="repair_center_title">{t('Title')}</Label>
                                            <Input
                                                id="repair_center_title"
                                                value={data.repair_center_title}
                                                onChange={(e) => setData('repair_center_title', e.target.value)}
                                                placeholder={t('Enter repair center title')}
                                            />
                                            <InputError message={errors.repair_center_title} />
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <Label htmlFor="repair_center_description">{t('Description')}</Label>
                                                <Textarea
                                                    id="repair_center_description"
                                                    value={data.repair_center_description}
                                                    onChange={(e) => setData('repair_center_description', e.target.value)}
                                                    placeholder={t('Enter repair center description')}
                                                    rows={3}
                                                />
                                                <InputError message={errors.repair_center_description} />
                                            </div>
                                            <div>
                                                <Label htmlFor="repair_center_map_code">{t('Google Maps Embed Code')}</Label>
                                                <Textarea
                                                    id="repair_center_map_code"
                                                    value={data.repair_center_map_code}
                                                    onChange={(e) => setData('repair_center_map_code', e.target.value)}
                                                    placeholder={t('Enter Google Maps embed code')}
                                                    rows={3}
                                                />
                                                <InputError message={errors.repair_center_map_code} />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="border rounded-lg p-6 space-y-4">
                                    <h3 className="text-lg font-semibold">{t('Service Hours')}</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <Label htmlFor="hours_title">{t('Hours Title')}</Label>
                                            <Input
                                                id="hours_title"
                                                value={data.hours_title}
                                                onChange={(e) => setData('hours_title', e.target.value)}
                                                placeholder={t('Enter hours title')}
                                            />
                                            <InputError message={errors.hours_title} />
                                        </div>
                                        <div className="space-y-3">
                                            {['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].map((day) => (
                                                <div key={day} className="grid grid-cols-4 gap-4 items-end">
                                                    <div>
                                                        <Label className="capitalize">{t(day)}</Label>
                                                        <Select
                                                            value={data.service_hours[day]?.status || 'open'}
                                                            onValueChange={(value) => {
                                                                setData('service_hours', {
                                                                    ...data.service_hours,
                                                                    [day]: {
                                                                        ...data.service_hours[day],
                                                                        status: value,
                                                                        opening_time: value === 'closed' ? '' : data.service_hours[day]?.opening_time ,
                                                                        closing_time: value === 'closed' ? '' : data.service_hours[day]?.closing_time 
                                                                    }
                                                                });
                                                            }}
                                                        >
                                                            <SelectTrigger>
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="open">{t('Open')}</SelectItem>
                                                                <SelectItem value="closed">{t('Closed')}</SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </div>
                                                    <div>
                                                        <Label>{t('Opening Time')}</Label>
                                                        <TimePicker
                                                            value={data.service_hours[day]?.opening_time || ''}
                                                            onChange={(value) => {
                                                                setData('service_hours', {
                                                                    ...data.service_hours,
                                                                    [day]: {
                                                                        ...data.service_hours[day],
                                                                        opening_time: value
                                                                    }
                                                                });
                                                            }}
                                                            disabled={data.service_hours[day]?.status === 'closed'}
                                                        />
                                                    </div>
                                                    <div>
                                                        <Label>{t('Closing Time')}</Label>
                                                        <TimePicker
                                                            value={data.service_hours[day]?.closing_time || ''}
                                                            onChange={(value) => {
                                                                setData('service_hours', {
                                                                    ...data.service_hours,
                                                                    [day]: {
                                                                        ...data.service_hours[day],
                                                                        closing_time: value
                                                                    }
                                                                });
                                                            }}
                                                            disabled={data.service_hours[day]?.status === 'closed'}
                                                        />
                                                    </div>
                                                </div>
                                            ))}
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