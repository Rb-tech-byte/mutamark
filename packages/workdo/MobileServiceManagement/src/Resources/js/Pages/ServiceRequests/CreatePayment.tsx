import { DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/ui/input-error';
import { DatePicker } from '@/components/ui/date-picker';

interface CreatePaymentProps {
    onSuccess: () => void;
    serviceRequest: {
        id: number;
        service_id: string;
        customer_name: string;
        price?: number;
    };
}

export default function CreatePayment({ onSuccess, serviceRequest }: CreatePaymentProps) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        mobile_service_request_id: serviceRequest.id.toString(),
        payment_date: '',
        price: serviceRequest.price?.toString() || '',
        payment_type: '1',
        payment_status: '1',
        description: '',
        receipt: null as File | null,
        tracking_status_id: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service-management.payments.store'), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Create Payment')}</DialogTitle>
                <DialogDescription>
                    {t('Create a new payment record for service request')} #{serviceRequest.service_id}
                </DialogDescription>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label>{t('Service ID')}</Label>
                    <Input
                        value={serviceRequest.service_id}
                        disabled
                        placeholder={t('Service ID')}
                    />
                </div>

                <div>
                    <Label>{t('Customer Name')}</Label>
                    <Input
                        value={serviceRequest.customer_name}
                        disabled
                        placeholder={t('Customer Name')}
                    />
                </div>

                <div>
                    <Label>{t('Date')} <span className="text-red-500">*</span></Label>
                    <DatePicker
                        value={data.payment_date}
                        onChange={(date) => setData('payment_date', date)}
                        placeholder={t('Select Date')}
                        required
                    />
                    <InputError message={errors.payment_date} />
                </div>

                <div>
                    <Label>{t('Amount')}</Label>
                    <Input
                        type="number"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value)}
                        placeholder={t('Amount')}
                    />
                    <InputError message={errors.price} />
                </div>

                <div>
                    <Label>{t('Description')}</Label>
                    <Textarea
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder={t('Enter Description')}
                    />
                    <InputError message={errors.description} />
                </div>

                <div>
                    <Label>{t('Payment Receipt')}</Label>
                    <Input
                        type="file"
                        onChange={(e) => setData('receipt', e.target.files?.[0] || null)}
                        accept="image/*,.pdf"
                    />
                    <InputError message={errors.receipt} />
                </div>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Creating...') : t('Create')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}