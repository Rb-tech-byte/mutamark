import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';
import { Textarea } from '@/components/ui/textarea';
import { EditMobileServiceContractProps, EditMobileServiceContractFormData } from './types';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import axios from 'axios';

export default function EditMobileServiceContract({ mobileservicecontract, onSuccess }: EditMobileServiceContractProps) {
    const {  } = usePage<any>().props;

    const { t } = useTranslation();
    const { data, setData, put, processing, errors } = useForm<EditMobileServiceContractFormData>({
        customer_name: mobileservicecontract.customer_name ?? '',
        contract_type: mobileservicecontract.contract_type ?? '',
        start_date: mobileservicecontract.start_date || '',
        end_date: mobileservicecontract.end_date || '',
        contract_details: mobileservicecontract.contract_details ?? '',
    });



    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('mobile-service-management.mobile-service-contracts.update', mobileservicecontract.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Edit Service Contract')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="customer_name">{t('Customer Name')}</Label>
                    <Input
                        id="customer_name"
                        type="text"
                        value={data.customer_name}
                        onChange={(e) => setData('customer_name', e.target.value)}
                        placeholder={t('Enter Customer Name')}
                        required
                    />
                    <InputError message={errors.customer_name} />
                </div>
                
                <div>
                    <Label htmlFor="contract_type" required>{t('Contract Type')}</Label>
                    <Input
                        id="contract_type"
                        type="text"
                        value={data.contract_type}
                        onChange={(e) => setData('contract_type', e.target.value)}
                        placeholder={t('Enter Contract Type')}
                    />
                    <InputError message={errors.contract_type} />
                </div>
                
                <div>
                    <Label required>{t('Start Date')}</Label>
                    <DatePicker
                        value={data.start_date}
                        onChange={(date) => setData('start_date', date)}
                        placeholder={t('Select Start Date')}
                    />
                    <InputError message={errors.start_date} />
                </div>
                
                <div>
                    <Label required>{t('End Date')}</Label>
                    <DatePicker
                        value={data.end_date}
                        onChange={(date) => setData('end_date', date)}
                        placeholder={t('Select End Date')}
                    />
                    <InputError message={errors.end_date} />
                </div>
                
                <div>
                    <Label htmlFor="contract_details">{t('Contract Details')}</Label>
                    <Textarea
                        id="contract_details"
                        value={data.contract_details}
                        onChange={(e) => setData('contract_details', e.target.value)}
                        placeholder={t('Enter Contract Details')}
                        rows={3}
                    />
                    <InputError message={errors.contract_details} />
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