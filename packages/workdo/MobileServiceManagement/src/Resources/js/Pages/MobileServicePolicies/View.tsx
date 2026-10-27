import { DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useTranslation } from 'react-i18next';
import { FileText } from 'lucide-react';
import { MobileServicePolicy } from './types';
import { formatTime } from '@/utils/helpers';

interface ViewProps {
    mobileservicepolicy: MobileServicePolicy;
}

export default function View({ mobileservicepolicy }: ViewProps) {
    const { t } = useTranslation();

    return (
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <DialogHeader className="pb-4 border-b">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg">
                        <FileText className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle className="text-xl font-semibold">{t('Service Policy Details')}</DialogTitle>
                    </div>
                </div>
            </DialogHeader>
            
            <div className="overflow-y-auto flex-1 p-4">
                <div className="space-y-6">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Policy Name')}</label>
                        <p className="text-sm bg-gray-50 p-3 rounded-md">{mobileservicepolicy.policy_name || '-'}</p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-gray-700">{t('Response Time')}</label>
                            <p className="text-sm bg-gray-50 p-3 rounded-md">{mobileservicepolicy.response_time ? formatTime(mobileservicepolicy.response_time) : '-'}</p>
                        </div>
                        
                        <div className="space-y-2">
                            <label className="text-sm font-medium text-gray-700">{t('Resolution Time')}</label>
                            <p className="text-sm bg-gray-50 p-3 rounded-md">{mobileservicepolicy.resolution_time ? formatTime(mobileservicepolicy.resolution_time) : '-'}</p>
                        </div>
                    </div>
                    
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700">{t('Description')}</label>
                        <div className="text-sm bg-gray-50 p-3 rounded-md min-h-[100px]">
                            {mobileservicepolicy.description || t('No description provided')}
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    );
}