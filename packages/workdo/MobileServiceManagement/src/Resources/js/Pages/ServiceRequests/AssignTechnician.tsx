import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useForm, usePage } from "@inertiajs/react";
import { useTranslation } from 'react-i18next';
import { Button } from "@/components/ui/button";
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { DatePicker } from '@/components/ui/date-picker';

interface RequestData {
    id: number;
    service_id: string;
}

interface AssignTechnicianProps {
    mobileservicerequest: RequestData;
    onSuccess: () => void;
}

interface PageProps {
    allUsers?: Array<{ id: number; name: string; }>;
}

export default function AssignTechnician({ mobileservicerequest, onSuccess }: AssignTechnicianProps) {
    const { t } = useTranslation();
    const { allUsers = [] } = usePage<PageProps>().props;
    
    const { data, setData, post, processing, errors } = useForm({
        technician_id: '',
        service_request_id: mobileservicerequest.id.toString(),
        estimated_completion_date: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('mobile-service-management.service-requests.assign-technician', mobileservicerequest.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{t('Assign Technician')}</DialogTitle>
            </DialogHeader>
            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="service_id">{t('Service ID')}</Label>
                    <Input
                        id="service_id"
                        value={mobileservicerequest.service_id}
                        disabled
                    />
                </div>
                
                <div>
                    <Label htmlFor="technician_id" required>{t('Select Technician')}</Label>
                    <Select value={data.technician_id} onValueChange={(value) => setData('technician_id', value)}>
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select Technician')} />
                        </SelectTrigger>
                        <SelectContent>
                            {allUsers.map((user) => (
                                <SelectItem key={user.id} value={user.id.toString()}>
                                    {user.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.technician_id} />
                </div>
                
                <div>
                    <Label required>{t('Estimated Completion Date')} </Label>
                    <DatePicker
                        value={data.estimated_completion_date}
                        onChange={(date) => setData('estimated_completion_date', date)}
                        placeholder={t('Select Date')}
                    />
                    <InputError message={errors.estimated_completion_date} />
                </div>
                
                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Assigning...') : t('Assign Technician')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}