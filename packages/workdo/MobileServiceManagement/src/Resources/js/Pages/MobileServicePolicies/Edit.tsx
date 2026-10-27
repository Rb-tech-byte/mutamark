import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { EditMobileServicePolicyProps, EditMobileServicePolicyFormData } from './types';
import { usePage } from '@inertiajs/react';
import { TimePicker } from '@/components/ui/time-picker';

export default function EditMobileServicePolicy({ mobileservicepolicy, onSuccess }: EditMobileServicePolicyProps) {
    const { } = usePage<any>().props;

    const { t } = useTranslation();
    const { data, setData, put, processing, errors } = useForm<EditMobileServicePolicyFormData>({
        policy_name: mobileservicepolicy.policy_name ?? '',
        description: mobileservicepolicy.description ?? '',
        response_time: mobileservicepolicy.response_time ? mobileservicepolicy.response_time.substring(0, 5) : '',
        resolution_time: mobileservicepolicy.resolution_time ? mobileservicepolicy.resolution_time.substring(0, 5) : '',
    });



    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('mobile-service-management.mobile-service-policies.update', mobileservicepolicy.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Edit Service Policy')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="policy_name">{t('Policy Name')}</Label>
                    <Input
                        id="policy_name"
                        type="text"
                        value={data.policy_name}
                        onChange={(e) => setData('policy_name', e.target.value)}
                        placeholder={t('Enter Policy Name')}
                        required
                    />
                    <InputError message={errors.policy_name} />
                </div>



                <div>
                    <Label htmlFor="response_time" required>{t('Response Time')}</Label>
                    <TimePicker
                        id="response_time"
                        value={data.response_time}
                        onChange={(value) => setData('response_time', value)}
                        placeholder={t('Response Time')}
                        required
                    />
                    <InputError message={errors.response_time} />
                </div>

                <div>
                    <Label htmlFor="resolution_time" required>{t('Resolution Time')}</Label>
                    <TimePicker
                        id="resolution_time"
                        value={data.resolution_time}
                        onChange={(value) => setData('resolution_time', value)}
                        placeholder={t('Resolution Time')}
                        required
                    />
                    <InputError message={errors.resolution_time} />
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

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Updating...') : t('Update')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}