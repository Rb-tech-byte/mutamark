import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { toast } from 'sonner';
import { CreditCard, Save, Building2, Landmark, CheckCircle2, Info } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';

interface BankTransferSettingsProps {
    userSettings?: Record<string, string>;
    auth?: any;
}

export default function BankTransferSettings({ userSettings = {}, auth }: BankTransferSettingsProps) {
    const { t } = useTranslation();
    const [isLoading, setIsLoading] = useState(false);
    const canEdit = auth?.user?.permissions?.includes('edit-bank-transfer-settings') ||
                    auth?.user?.permissions?.includes('edit-settings') ||
                    auth?.user?.permissions?.includes('manage-settings');

    const [bankSettings, setBankSettings] = useState({
        bankTransferEnabled: userSettings?.bankTransferEnabled === 'on',
        bank_name: userSettings?.bank_name || '',
        bank_account_name: userSettings?.bank_account_name || userSettings?.company_name || '',
        bank_account_number: userSettings?.bank_account_number || '',
        bank_branch: userSettings?.bank_branch || '',
        bank_swift_code: userSettings?.bank_swift_code || '',
        bank_currency: userSettings?.bank_currency || 'TZS',
        instructions: userSettings?.instructions || ''
    });

    useEffect(() => {
        setBankSettings({
            bankTransferEnabled: userSettings?.bankTransferEnabled === 'on',
            bank_name: userSettings?.bank_name || '',
            bank_account_name: userSettings?.bank_account_name || userSettings?.company_name || '',
            bank_account_number: userSettings?.bank_account_number || '',
            bank_branch: userSettings?.bank_branch || '',
            bank_swift_code: userSettings?.bank_swift_code || '',
            bank_currency: userSettings?.bank_currency || 'TZS',
            instructions: userSettings?.instructions || ''
        });
    }, [userSettings]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setBankSettings(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleSwitchChange = (checked: boolean) => {
        setBankSettings(prev => ({
            ...prev,
            bankTransferEnabled: checked
        }));
    };

    const saveBankSettings = () => {
        setIsLoading(true);

        router.post(route('settings.bank-transfer.update'), {
            settings: {
                ...bankSettings,
                bankTransferEnabled: bankSettings.bankTransferEnabled ? 'on' : 'off'
            }
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                setIsLoading(false);
                const successMessage = (page.props.flash as any)?.success;
                const errorMessage = (page.props.flash as any)?.error;

                if (successMessage) {
                    toast.success(successMessage);
                    router.reload({ only: ['globalSettings'] });
                } else if (errorMessage) {
                    toast.error(errorMessage);
                } else {
                    toast.success(t('Bank transfer settings updated successfully.'));
                }
            },
            onError: (errors) => {
                setIsLoading(false);
                const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save bank transfer settings');
                toast.error(errorMessage);
            }
        });
    };

    const isEnabled = bankSettings.bankTransferEnabled;

    return (
        <Card className="border-border shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between pb-4 border-b">
                <div className="space-y-1">
                    <div className="flex items-center gap-2">
                        <Landmark className="h-5 w-5 text-blue-600" />
                        <CardTitle className="text-lg font-semibold">{t('Bank Wire Transfer Settings (Offline)')}</CardTitle>
                        <Badge variant={isEnabled ? 'default' : 'secondary'} className={isEnabled ? 'bg-blue-600 hover:bg-blue-700 text-white' : ''}>
                            {isEnabled ? t('Active') : t('Disabled')}
                        </Badge>
                    </div>
                    <CardDescription>
                        {t('Configure your corporate bank account details and wire transfer guidelines for direct B2B payments.')}
                    </CardDescription>
                </div>
                {canEdit && (
                    <Button onClick={saveBankSettings} disabled={isLoading} size="sm" className="gap-2">
                        <Save className="h-4 w-4" />
                        {isLoading ? t('Saving...') : t('Save Changes')}
                    </Button>
                )}
            </CardHeader>

            <CardContent className="pt-6 space-y-6">
                {/* Enable/Disable Bank Transfer */}
                <div className="flex items-center justify-between p-4 bg-muted/40 rounded-xl border border-border/80">
                    <div className="space-y-0.5">
                        <Label htmlFor="bankTransferEnabled" className="text-base font-medium">
                            {t('Enable Bank Wire Transfer')}
                        </Label>
                        <p className="text-xs text-muted-foreground">
                            {t('Allow customers to choose direct bank deposit / wire transfer and upload proof of payment deposit slips.')}
                        </p>
                    </div>
                    <Switch
                        id="bankTransferEnabled"
                        checked={isEnabled}
                        onCheckedChange={handleSwitchChange}
                        disabled={!canEdit}
                    />
                </div>

                {isEnabled && (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-2">
                        {/* Form Fields */}
                        <div className="lg:col-span-2 space-y-5">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Bank Name */}
                                <div className="space-y-2">
                                    <Label htmlFor="bank_name">{t('Bank Name')}</Label>
                                    <Input
                                        id="bank_name"
                                        name="bank_name"
                                        value={bankSettings.bank_name}
                                        onChange={handleInputChange}
                                        placeholder={t('e.g. CRDB Bank Plc / NMB Bank')}
                                        disabled={!canEdit}
                                    />
                                </div>

                                {/* Branch */}
                                <div className="space-y-2">
                                    <Label htmlFor="bank_branch">{t('Branch Name')}</Label>
                                    <Input
                                        id="bank_branch"
                                        name="bank_branch"
                                        value={bankSettings.bank_branch}
                                        onChange={handleInputChange}
                                        placeholder={t('e.g. Clock Tower Branch, Arusha')}
                                        disabled={!canEdit}
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Account Name */}
                                <div className="space-y-2">
                                    <Label htmlFor="bank_account_name">{t('Account Holder Name')}</Label>
                                    <Input
                                        id="bank_account_name"
                                        name="bank_account_name"
                                        value={bankSettings.bank_account_name}
                                        onChange={handleInputChange}
                                        placeholder={t('e.g. MUTAMARK ENTERPRISES LIMITED')}
                                        disabled={!canEdit}
                                    />
                                </div>

                                {/* Account Number */}
                                <div className="space-y-2">
                                    <Label htmlFor="bank_account_number">{t('Account Number')}</Label>
                                    <Input
                                        id="bank_account_number"
                                        name="bank_account_number"
                                        value={bankSettings.bank_account_number}
                                        onChange={handleInputChange}
                                        placeholder={t('e.g. 01501234567890')}
                                        disabled={!canEdit}
                                        className="font-mono"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* SWIFT / BIC */}
                                <div className="space-y-2">
                                    <Label htmlFor="bank_swift_code">{t('SWIFT / BIC Code (Optional)')}</Label>
                                    <Input
                                        id="bank_swift_code"
                                        name="bank_swift_code"
                                        value={bankSettings.bank_swift_code}
                                        onChange={handleInputChange}
                                        placeholder={t('e.g. CORUTZTZ')}
                                        disabled={!canEdit}
                                        className="font-mono uppercase"
                                    />
                                </div>

                                {/* Currency */}
                                <div className="space-y-2">
                                    <Label htmlFor="bank_currency">{t('Settlement Currency')}</Label>
                                    <Input
                                        id="bank_currency"
                                        name="bank_currency"
                                        value={bankSettings.bank_currency}
                                        onChange={handleInputChange}
                                        placeholder={t('e.g. TZS or USD')}
                                        disabled={!canEdit}
                                        className="uppercase"
                                    />
                                </div>
                            </div>

                            {/* Additional Instructions */}
                            <div className="space-y-2">
                                <Label htmlFor="instructions">{t('Payment Instructions & Notes')}</Label>
                                <Textarea
                                    id="instructions"
                                    name="instructions"
                                    value={bankSettings.instructions}
                                    onChange={handleInputChange}
                                    placeholder={t('Include invoice number as payment reference. Email payment slip to accounts@company.com')}
                                    rows={4}
                                    disabled={!canEdit}
                                />
                                <p className="text-xs text-muted-foreground">
                                    {t('Custom wire guidelines printed on quotations, proformas, invoices, and checkout pages.')}
                                </p>
                            </div>
                        </div>

                        {/* Live Customer Preview */}
                        <div className="lg:col-span-1 border rounded-xl p-5 bg-blue-50/40 dark:bg-blue-950/20 space-y-4">
                            <div className="flex items-center gap-2 font-semibold text-sm text-blue-950 dark:text-blue-200">
                                <Building2 className="h-4 w-4 text-blue-600" />
                                <h4>{t('Customer Bank Slip Preview')}</h4>
                            </div>
                            <div className="p-4 bg-background rounded-xl border shadow-sm space-y-2.5 text-xs">
                                <div className="font-semibold text-sm border-b pb-2 text-foreground">
                                    {bankSettings.bank_name || t('Your Bank Name')}
                                </div>
                                <div>
                                    <span className="text-muted-foreground">{t('Account Name:')} </span>
                                    <span className="font-medium text-foreground">{bankSettings.bank_account_name || t('Company Name')}</span>
                                </div>
                                <div>
                                    <span className="text-muted-foreground">{t('Account No:')} </span>
                                    <span className="font-mono font-bold text-foreground">{bankSettings.bank_account_number || '----------------'}</span>
                                </div>
                                {bankSettings.bank_branch && (
                                    <div>
                                        <span className="text-muted-foreground">{t('Branch:')} </span>
                                        <span className="font-medium text-foreground">{bankSettings.bank_branch}</span>
                                    </div>
                                )}
                                {bankSettings.bank_swift_code && (
                                    <div>
                                        <span className="text-muted-foreground">{t('SWIFT:')} </span>
                                        <span className="font-mono text-foreground">{bankSettings.bank_swift_code}</span>
                                    </div>
                                )}
                                {bankSettings.instructions && (
                                    <div className="mt-2 pt-2 border-t text-[11px] text-muted-foreground italic">
                                        "{bankSettings.instructions}"
                                    </div>
                                )}
                            </div>
                            <div className="text-[11px] text-blue-800/80 dark:text-blue-300 flex items-start gap-2">
                                <Info className="h-4 w-4 shrink-0 mt-0.5" />
                                <span>{t('Once received, bank wire payments can be matched against Sales Invoices or reconciliation statements.')}</span>
                            </div>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
