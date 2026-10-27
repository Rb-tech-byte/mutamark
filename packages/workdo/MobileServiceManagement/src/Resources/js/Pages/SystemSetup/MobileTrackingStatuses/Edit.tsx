import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Input } from '@/components/ui/input';
import { IconPicker } from '@/components/ui/icon-picker';

import { EditMobileTrackingStatusProps, MobileTrackingStatusFormData } from './types';

export default function Edit({ mobiletrackingstatus, onSuccess }: EditMobileTrackingStatusProps) {
    const { t } = useTranslation();
    const { data, setData, put, processing, errors } = useForm<MobileTrackingStatusFormData>({
        icon_name: mobiletrackingstatus.icon_name ?? '',
        status_color: mobiletrackingstatus.status_color ?? '#FF6B6B',
        status_name: mobiletrackingstatus.status_name ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('mobile-service.tracking-statuses.update', mobiletrackingstatus.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Edit Tracking status')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="status_name">{t('Name')}</Label>
                    <Input
                        id="status_name"
                        type="text"
                        value={data.status_name}
                        onChange={(e) => setData('status_name', e.target.value)}
                        placeholder={t('Enter Name')}
                        required
                    />
                    <InputError message={errors.status_name} />
                </div>
                <div>
                    <Label htmlFor="icon_name" required>{t('Icon')}</Label>
                    <IconPicker
                        value={data.icon_name}
                        onChange={(value) => setData('icon_name', value)}
                        placeholder={t('Select an icon')}
                    />
                    <InputError message={errors.icon_name} />
                </div>
                <div>
                    <Label htmlFor="status_color">{t('Color')}</Label>
                    <Input
                        id="status_color"
                        type="color"
                        value={data.status_color}
                        onChange={(e) => setData('status_color', e.target.value)}
                        className="mt-2 h-10 w-20"
                    />
                    <InputError message={errors.status_color} />
                </div>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={() => onSuccess()}>
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