import { Head, useForm, usePage } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import InputError from "@/components/ui/input-error";
import { PhoneInputComponent } from "@/components/ui/phone-input";
import MediaPicker from '@/components/MediaPicker';

interface MobileService {
    id: number;
    name: string;
    expected_price?: number;
}

interface DeviceBrand {
    id: number;
    name: string;
}

interface EditServiceRequestFormData {
    firstName: string;
    phone: string;
    email: string;
    address: string;
    serviceType: string;
    deviceBrand: string;
    deviceModel: string;
    urgency: string;
    issueDescription: string;
    specialRequests: string;
    attachments: string[];
}

export default function Edit() {
    const { t } = useTranslation();
    const { mobileservicerequest, mobileservices, devicebrands } = usePage().props as {
        mobileservicerequest: any;
        mobileservices: MobileService[];
        devicebrands: DeviceBrand[];
    };

    const { data, setData, put, processing, errors } = useForm<EditServiceRequestFormData>({
        firstName: mobileservicerequest.customer_name || '',
        phone: mobileservicerequest.mobile_no || '',
        email: mobileservicerequest.email || '',
        address: mobileservicerequest.address || '',
        serviceType: mobileservicerequest.mobile_service_id?.toString() || '',
        deviceBrand: mobileservicerequest.device_brand?.toString() || '',
        deviceModel: mobileservicerequest.mobile_model || '',
        urgency: mobileservicerequest.priority || 'standard',
        issueDescription: mobileservicerequest.description || '',
        specialRequests: mobileservicerequest.special_requests || '',
        attachments: mobileservicerequest.attachment ? JSON.parse(mobileservicerequest.attachment) : []
    });

    useFlashMessages();

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('mobile-service-management.service-requests.update', mobileservicerequest.id));
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service Management'), url: route('mobile-service.index') },
                { label: t('Service Requests'), url: route('mobile-service-management.service-requests.index') },
                { label: t('Edit Service Request') }
            ]}
            pageTitle={t('Edit Service Request')}
        >
            <Head title={t('Edit Service Request')} />

            <Card>
                <CardContent>
                    <form onSubmit={submit} className="space-y-6 pt-5">
                        {/* Customer Details */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-medium">{t('Customer Details')}</h3>
                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div>
                                    <Label htmlFor="firstName" required>{t('Customer Name')}</Label>
                                    <Input
                                        id="firstName"
                                        value={data.firstName}
                                        onChange={(e) => setData('firstName', e.target.value)}
                                        placeholder={t('Enter Customer Name')}
                                        required
                                    />
                                    <InputError message={errors.firstName} />
                                </div>
                                <div>
                                    <PhoneInputComponent
                                        label={t('Mobile Number')}
                                        value={data.phone}
                                        onChange={(value) => setData('phone', value || '')}
                                        placeholder="+1234567890"
                                        error={errors.phone}
                                        required
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="email" required>{t('Email Address')}</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder={t('Enter Email Address')}
                                        required
                                    />
                                    <div className="text-xs text-muted-foreground mt-1">
                                        {t('Using this email you can track your service request')}
                                    </div>
                                    <InputError message={errors.email} />
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="address" required>{t('Address')}</Label>
                                <Textarea
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder={t('Enter Customer Address')}
                                    rows={3}
                                    required
                                />
                                <InputError message={errors.address} />
                            </div>
                        </div>

                        {/* Service Details */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-medium">{t('Service Details')}</h3>
                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="serviceType" required>{t('Service Type')}</Label>
                                    <Select value={data.serviceType} onValueChange={(value) => setData('serviceType', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Service Type')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {mobileservices?.map(service => (
                                                <SelectItem key={service.id} value={service.id.toString()}>{service.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.serviceType} />
                                </div>
                                <div>
                                    <Label htmlFor="deviceBrand" required>{t('Device Brand')}</Label>
                                    <Select value={data.deviceBrand} onValueChange={(value) => setData('deviceBrand', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Device Brand')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {devicebrands?.map(brand => (
                                                <SelectItem key={brand.id} value={brand.id.toString()}>{brand.name}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.deviceBrand} />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="deviceModel" required>{t('Device Model')}</Label>
                                    <Input
                                        id="deviceModel"
                                        value={data.deviceModel}
                                        onChange={(e) => setData('deviceModel', e.target.value)}
                                        placeholder={t('Enter Device Model')}
                                        required
                                    />
                                    <InputError message={errors.deviceModel} />
                                </div>
                                <div>
                                    <Label htmlFor="urgency" required>{t('Priority')}</Label>
                                    <Select value={data.urgency} onValueChange={(value) => setData('urgency', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Priority')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="standard">{t('Standard')}</SelectItem>
                                            <SelectItem value="priority">{t('Priority')}</SelectItem>
                                            <SelectItem value="emergency">{t('Emergency')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.urgency} />
                                </div>
                            </div>
                        </div>

                        {/* Issue Information */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-medium">{t('Issue Information')}</h3>
                            <div>
                                <Label htmlFor="issueDescription" required>{t('Issue Description')}</Label>
                                <Textarea
                                    id="issueDescription"
                                    value={data.issueDescription}
                                    onChange={(e) => setData('issueDescription', e.target.value)}
                                    placeholder={t('Describe the issue in detail')}
                                    rows={3}
                                    required
                                />
                                <InputError message={errors.issueDescription} />
                            </div>

                            <div>
                                <Label htmlFor="specialRequests">{t('Special Requests')}</Label>
                                <Textarea
                                    id="specialRequests"
                                    value={data.specialRequests}
                                    onChange={(e) => setData('specialRequests', e.target.value)}
                                    placeholder={t('Any special requests or additional information')}
                                    rows={3}
                                />
                                <InputError message={errors.specialRequests} />
                            </div>

                            <div>
                                <MediaPicker
                                    label={t('Attachments (Optional)')}
                                    value={data.attachments}
                                    onChange={(value) => setData('attachments', Array.isArray(value) ? value : [value].filter(Boolean))}
                                    multiple={true}
                                    placeholder={t('Upload images of the device issue')}
                                    showPreview={false}
                                />
                                <InputError message={errors.attachments} />
                            </div>
                        </div>

                        <div className="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-6 border-t">
                            <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                {t('Cancel')}
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? t('Updating...') : t('Update')}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AuthenticatedLayout>
    );
}