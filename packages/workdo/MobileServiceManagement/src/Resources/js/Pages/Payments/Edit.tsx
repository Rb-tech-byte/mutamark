import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';
import InputError from '@/components/ui/input-error';
import { useFormFields } from '@/hooks/useFormFields';
import { Repeater } from '@/components/ui/repeater';
import { useState, useEffect } from 'react';
import { formatCurrency } from '@/utils/helpers';

interface ServiceRequest {
    id: number;
    service_id: string;
    customer_name: string;
    email: string;
    mobile_no: string;
    mobile_service_id: number;
    mobile_service?: {
        id: number;
        name: string;
        sale_price: string;
        taxes?: Array<{
            id: number;
            tax_name: string;
            rate: string;
        }>;
    };
}

interface Payment {
    id: number;
    mobile_service_request_id: number;
    payment_date: string;
    price: string;
    payment_type: string;
    payment_status: string;
    description?: string;
}

interface EditProps {
    payment: Payment;
    serviceRequests: ServiceRequest[];
    onSuccess: () => void;
}

interface FormData {
    mobile_service_id: string;
    payment_date: string;
    price: string;
    tax_amount: string;
    repair_charge: string;
    extra_charges: Array<{text: string; price: string}>;
    description: string;
    bank_account_id: string;
}

export default function Edit({ payment, serviceRequests, onSuccess }: EditProps) {
    const { t } = useTranslation();

    const { data, setData, put, processing, errors } = useForm<FormData>({
        mobile_service_id: payment.mobile_service_id?.toString() || '',
        payment_date: payment.payment_date,
        price: payment.service_price || '',
        tax_amount: payment.tax_amount?.toString() || '0',
        repair_charge: payment.repair_charge?.toString() || '0',
        extra_charges: payment.extra_charges || [],
        description: payment.notes || '',
        bank_account_id: payment.bank_account_id?.toString() || '',
    });

    const [selectedRequest, setSelectedRequest] = useState<ServiceRequest | null>(null);

    // Set initial selected request on component mount
    useEffect(() => {
        if (data.mobile_service_id && serviceRequests.length > 0) {
            const request = serviceRequests.find(r => r.id.toString() === data.mobile_service_id);
            setSelectedRequest(request || null);
        }
    }, [data.mobile_service_id, serviceRequests]);

    const handleServiceRequestChange = (value: string) => {
        setData('mobile_service_id', value);
        const request = serviceRequests.find(r => r.id.toString() === value);
        setSelectedRequest(request || null);
        if (request?.mobile_service?.sale_price) {
            setData('price', request.mobile_service.sale_price);
            
            // Calculate tax amount
            if (request.mobile_service.taxes) {
                const taxAmount = request.mobile_service.taxes.reduce((sum, tax) => {
                    return sum + (parseFloat(request.mobile_service.sale_price) * parseFloat(tax.rate)) / 100;
                }, 0);
                setData('tax_amount', taxAmount.toString());
            }
        }
    };

    // Bank Account Field
    const bankAccountField = useFormFields('bankAccountField', data, setData, errors, 'edit');

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('mobile-service-management.payments.update', payment.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent className="max-w-4xl">
            <DialogHeader>
                <DialogTitle>{t('Edit Payment')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <Label htmlFor="mobile_service_id" required>{t('Service Request')}</Label>
                        <Select value={data.mobile_service_id} onValueChange={handleServiceRequestChange}>
                            <SelectTrigger>
                                <SelectValue placeholder={t('Select Service Request')} />
                            </SelectTrigger>
                            <SelectContent>
                                {serviceRequests?.map((request) => (
                                    <SelectItem key={request.id} value={request.id.toString()}>
                                        {request.service_id} - {request.customer_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.mobile_service_id} />
                    </div>

                    <div>
                        <Label htmlFor="payment_date" required>{t('Payment Date')}</Label>
                        <DatePicker
                            id="payment_date"
                            value={data.payment_date}
                            onChange={(value) => {
                                const formattedDate = value instanceof Date ? value.toISOString().split('T')[0] : value;
                                setData('payment_date', formattedDate);
                            }}
                            placeholder={t('Select payment date')}
                            required
                        />
                        <InputError message={errors.payment_date} />
                    </div>
                </div>

                {bankAccountField.map((field) => (
                    <div key={field.id}>{field.component}</div>
                ))}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <Label htmlFor="price" required>{t('Service Amount')}</Label>
                        <Input
                            id="price"
                            type="number"
                            step="0.01"
                            value={data.price}
                            placeholder={t('Service amount')}
                            readOnly
                            className="bg-gray-100"
                        />
                        <InputError message={errors.price} />
                    </div>

                    <div>
                        <Label htmlFor="repair_charge">{t('Repair Charge')}</Label>
                        <Input
                            id="repair_charge"
                            type="number"
                            step="0.01"
                            value={data.repair_charge}
                            onChange={(e) => setData('repair_charge', e.target.value)}
                            placeholder={t('Enter repair charge')}
                        />
                        <InputError message={errors.repair_charge} />
                    </div>
                </div>

                {selectedRequest && (
                    <div className="bg-gray-50 p-4 rounded-lg">
                        <h3 className="font-medium mb-3">{t('Service Request Details')}</h3>
                        <div className="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span className="font-medium">{t('Customer')}:</span> {selectedRequest.customer_name}
                            </div>
                            <div>
                                <span className="font-medium">{t('Email')}:</span> {selectedRequest.email}
                            </div>
                            <div>
                                <span className="font-medium">{t('Phone')}:</span> {selectedRequest.mobile_no}
                            </div>
                            <div>
                                <span className="font-medium">{t('Service')}:</span> {selectedRequest.mobile_service?.name}
                            </div>
                        </div>
                    </div>
                )}

                <div>
                    <Label>{t('Extra Charges')}</Label>
                    <Repeater
                        value={data.extra_charges}
                        onChange={(items) => setData('extra_charges', items)}
                        fields={[
                            {
                                name: 'text',
                                label: t('Description'),
                                type: 'text',
                                placeholder: t('Enter description'),
                                required: false
                            },
                            {
                                name: 'price',
                                label: t('Amount'),
                                type: 'number',
                                placeholder: t('Enter amount'),
                                step: 0.01,
                                required: false
                            }
                        ]}
                        addButtonText={t('Add Extra Charge')}
                    />
                </div>

                {(data.price || data.repair_charge !== '0' || data.extra_charges.length > 0) && (
                    <div className="bg-blue-50 p-4 rounded-lg">
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span>{t('Service Amount')}:</span>
                                <span>{formatCurrency(parseFloat(data.price || '0'))}</span>
                            </div>
                            {selectedRequest?.mobile_service?.taxes && selectedRequest.mobile_service.taxes.length > 0 && (
                                <div className="flex justify-between">
                                    <span>{t('Total Tax')}:</span>
                                    <span>{formatCurrency((() => {
                                        const serviceAmount = parseFloat(data.price || '0');
                                        return selectedRequest.mobile_service.taxes.reduce((sum, tax) => {
                                            return sum + (serviceAmount * parseFloat(tax.rate)) / 100;
                                        }, 0);
                                    })())}</span>
                                </div>
                            )}
                            <div className="flex justify-between">
                                <span>{t('Repair Charge')}:</span>
                                <span>{formatCurrency(parseFloat(data.repair_charge || '0'))}</span>
                            </div>
                            {data.extra_charges.map((charge, index) => (
                                <div key={index} className="flex justify-between text-sm">
                                    <span>{charge.text}:</span>
                                    <span>{formatCurrency(parseFloat(charge.price || '0'))}</span>
                                </div>
                            ))}
                            <hr className="my-2" />
                            <div className="flex justify-between items-center font-bold">
                                <span>{t('Total Amount')}:</span>
                                <span className="text-lg text-blue-600">
                                    {formatCurrency((() => {
                                        const serviceAmount = parseFloat(data.price || '0');
                                        const repairCharge = parseFloat(data.repair_charge || '0');
                                        const extraCharges = data.extra_charges.reduce((sum, charge) => sum + parseFloat(charge.price || '0'), 0);
                                        const taxAmount = selectedRequest?.mobile_service?.taxes?.reduce((sum, tax) => {
                                            return sum + (serviceAmount * parseFloat(tax.rate)) / 100;
                                        }, 0) || 0;
                                        return serviceAmount + repairCharge + extraCharges + taxAmount;
                                    })())}
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                <div>
                    <Label htmlFor="description">{t('Description')}</Label>
                    <Textarea
                        id="description"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        rows={3}
                        placeholder={t('Enter description')}
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