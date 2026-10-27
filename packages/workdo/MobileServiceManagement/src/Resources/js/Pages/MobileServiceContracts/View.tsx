import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { FileText } from 'lucide-react';
import { MobileServiceContract } from './types';
import { formatDate } from '@/utils/helpers';

interface ViewProps {
    mobileservicecontract: MobileServiceContract;
}

export default function View({ mobileservicecontract }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <FileText className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Service Contract Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>
            
            <div className="overflow-y-auto flex-1 p-4">
                <div className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-gray-700">{t('Customer Name')}</label>
                            <p className="text-sm bg-gray-50 p-3 rounded-md">{mobileservicecontract.customer_name || '-'}</p>
                        </div>
                        
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-gray-700">{t('Contract Type')}</label>
                            <p className="text-sm bg-gray-50 p-3 rounded-md">{mobileservicecontract.contract_type || '-'}</p>
                        </div>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-gray-700">{t('Start Date')}</label>
                            <p className="text-sm bg-gray-50 p-3 rounded-md">{mobileservicecontract.start_date ? formatDate(mobileservicecontract.start_date) : '-'}</p>
                        </div>
                        
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-gray-700">{t('End Date')}</label>
                            <p className="text-sm bg-gray-50 p-3 rounded-md">{mobileservicecontract.end_date ? formatDate(mobileservicecontract.end_date) : '-'}</p>
                        </div>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Contract Details')}</label>
                        <div className="text-sm bg-gray-50 p-3 rounded-md min-h-[100px]">
                            {mobileservicecontract.contract_details || t('No contract details provided')}
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    );
}