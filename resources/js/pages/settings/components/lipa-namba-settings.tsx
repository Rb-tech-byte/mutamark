import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'sonner';
import { Smartphone, Save, PhoneCall, QrCode, CheckCircle2, Building, Info, ShieldCheck } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Switch } from '@/components/ui/switch';

interface LipaNambaSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function LipaNambaSettings({ userSettings = {}, auth }: LipaNambaSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const canEdit = auth?.user?.permissions?.includes('edit-lipa-namba-settings') ||
                  auth?.user?.permissions?.includes('edit-settings') ||
                  auth?.user?.permissions?.includes('manage-settings');

  const [settings, setSettings] = useState({
    lipa_namba_enabled: userSettings?.lipa_namba_enabled === 'on' ? 'on' : 'off',
    lipa_namba_till_number: userSettings?.lipa_namba_till_number || '',
    lipa_namba_merchant_name: userSettings?.lipa_namba_merchant_name || userSettings?.company_name || 'MUTAMARK ENTERPRISES LTD',
    lipa_namba_instructions: userSettings?.lipa_namba_instructions || 'Lipa kwa Simu / Lipa Namba mtandao wowote (Vodacom M-Pesa, Airtel Money, Tigo Pesa, Halopesa au Benki).',
  });

  useEffect(() => {
    if (userSettings) {
      setSettings({
        lipa_namba_enabled: userSettings?.lipa_namba_enabled === 'on' ? 'on' : 'off',
        lipa_namba_till_number: userSettings?.lipa_namba_till_number || '',
        lipa_namba_merchant_name: userSettings?.lipa_namba_merchant_name || userSettings?.company_name || 'MUTAMARK ENTERPRISES LTD',
        lipa_namba_instructions: userSettings?.lipa_namba_instructions || 'Lipa kwa Simu / Lipa Namba mtandao wowote (Vodacom M-Pesa, Airtel Money, Tigo Pesa, Halopesa au Benki).',
      });
    }
  }, [userSettings]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleSwitchChange = (checked: boolean) => {
    setSettings(prev => ({ ...prev, lipa_namba_enabled: checked ? 'on' : 'off' }));
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('settings.lipa-namba.update'), {
      settings: {
        ...settings,
        lipa_namba_enabled: settings.lipa_namba_enabled === 'on' ? 'on' : 'off'
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
          toast.success(t('Lipa Namba settings saved successfully.'));
        }
      },
      onError: (errors) => {
        setIsLoading(false);
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save Lipa Namba settings');
        toast.error(errorMessage);
      }
    });
  };

  const isEnabled = settings.lipa_namba_enabled === 'on';
  const displayTill = settings.lipa_namba_till_number || '______';
  const displayMerchant = settings.lipa_namba_merchant_name || 'Company Merchant Name';

  return (
    <Card className="border-border shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between pb-4 border-b">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <Smartphone className="h-5 w-5 text-emerald-600" />
            <CardTitle className="text-lg font-semibold">{t('Lipa Namba & Mobile Money Till Settings')}</CardTitle>
            <Badge variant={isEnabled ? 'default' : 'secondary'} className={isEnabled ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : ''}>
              {isEnabled ? t('Active') : t('Disabled')}
            </Badge>
          </div>
          <CardDescription>
            {t('Configure your company\'s official Tanzania Mobile Money Till / Paybill number (M-Pesa, Airtel Money, Tigo Pesa, Halopesa).')}
          </CardDescription>
        </div>
        {canEdit && (
          <Button onClick={saveSettings} disabled={isLoading} size="sm" className="gap-2">
            <Save className="h-4 w-4" />
            {isLoading ? t('Saving...') : t('Save Changes')}
          </Button>
        )}
      </CardHeader>

      <CardContent className="pt-6 space-y-6">
        {/* Toggle Switch */}
        <div className="flex items-center justify-between p-4 bg-muted/40 rounded-xl border border-border/80">
          <div className="space-y-0.5">
            <Label htmlFor="lipa_namba_enabled" className="text-base font-medium">
              {t('Enable Lipa Namba Payment Method')}
            </Label>
            <p className="text-xs text-muted-foreground">
              {t('Allow customers to pay directly via mobile money till number and enter transaction SMS references.')}
            </p>
          </div>
          <Switch
            id="lipa_namba_enabled"
            checked={isEnabled}
            onCheckedChange={handleSwitchChange}
            disabled={!canEdit}
          />
        </div>

        {isEnabled && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-2">
            {/* Form Fields */}
            <div className="lg:col-span-2 space-y-5">
              {/* Till Number */}
              <div className="space-y-2">
                <Label htmlFor="lipa_namba_till_number" className="flex items-center gap-2">
                  <Smartphone className="h-4 w-4 text-emerald-600" />
                  {t('Company Merchant Till / Paybill Number')}
                </Label>
                <Input
                  id="lipa_namba_till_number"
                  name="lipa_namba_till_number"
                  value={settings.lipa_namba_till_number}
                  onChange={handleInputChange}
                  placeholder={t('Enter company till number (e.g. 556677 or 1234567)')}
                  disabled={!canEdit}
                  className="font-mono text-base font-semibold"
                />
                <p className="text-xs text-muted-foreground">
                  {t('Your business\'s dedicated merchant till number registered with Vodacom, Airtel, or Tigo.')}
                </p>
              </div>

              {/* Merchant Name */}
              <div className="space-y-2">
                <Label htmlFor="lipa_namba_merchant_name" className="flex items-center gap-2">
                  <Building className="h-4 w-4 text-primary" />
                  {t('Registered Merchant Name (Display on Payer Phone)')}
                </Label>
                <Input
                  id="lipa_namba_merchant_name"
                  name="lipa_namba_merchant_name"
                  value={settings.lipa_namba_merchant_name}
                  onChange={handleInputChange}
                  placeholder={t('e.g. MUTAMARK ENTERPRISES LTD')}
                  disabled={!canEdit}
                  className="font-medium"
                />
                <p className="text-xs text-muted-foreground">
                  {t('The exact business name shown on the payer\'s screen when verifying the till number before entering PIN.')}
                </p>
              </div>

              {/* Instructions */}
              <div className="space-y-2">
                <Label htmlFor="lipa_namba_instructions">{t('Custom Payment Instructions & Guidance')}</Label>
                <Textarea
                  id="lipa_namba_instructions"
                  name="lipa_namba_instructions"
                  value={settings.lipa_namba_instructions}
                  onChange={handleInputChange}
                  placeholder={t('Instructions displayed to customer after selecting Lipa Namba...')}
                  rows={4}
                  disabled={!canEdit}
                />
                <p className="text-xs text-muted-foreground">
                  {t('Guidance shown on checkout pages and invoice printouts for making mobile payments.')}
                </p>
              </div>

              {/* Supported Networks Info */}
              <div className="p-4 bg-muted/30 rounded-xl border space-y-3">
                <h4 className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                  {t('Supported Networks on this Till')}
                </h4>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                  {[
                    { name: 'Vodacom M-Pesa', code: '*150*00#', color: 'border-red-500/30 text-red-700 dark:text-red-400 bg-red-50/50 dark:bg-red-950/20' },
                    { name: 'Tigo Pesa', code: '*150*01#', color: 'border-blue-500/30 text-blue-700 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-950/20' },
                    { name: 'Airtel Money', code: '*150*60#', color: 'border-rose-500/30 text-rose-700 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-950/20' },
                    { name: 'Halopesa', code: '*150*88#', color: 'border-amber-500/30 text-amber-700 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20' },
                    { name: 'CRDB SimBanking', code: '*150*03#', color: 'border-emerald-500/30 text-emerald-700 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-950/20' },
                    { name: 'NMB Mkononi', code: '*150*66#', color: 'border-sky-500/30 text-sky-700 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-950/20' },
                  ].map((net, i) => (
                    <div key={i} className={`p-2.5 rounded-lg border text-xs flex flex-col justify-between ${net.color}`}>
                      <span className="font-semibold">{net.name}</span>
                      <span className="font-mono text-[11px] opacity-80">{net.code}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Live Customer Preview */}
            <div className="lg:col-span-1 border rounded-xl p-5 bg-gradient-to-b from-muted/30 to-muted/10 space-y-4">
              <div className="flex items-center gap-2 font-semibold text-sm">
                <PhoneCall className="h-4 w-4 text-emerald-600" />
                <h4>{t('Live Customer Preview')}</h4>
              </div>
              <p className="text-xs text-muted-foreground">
                {t('This is what customers see when selecting Lipa Namba on invoice checkout:')}
              </p>

              {/* Mock Invoice Box */}
              <div className="p-4 bg-background rounded-xl border shadow-sm space-y-3">
                <div className="flex items-center justify-between border-b pb-2">
                  <span className="text-xs font-semibold text-muted-foreground">{t('Lipa kwa Simu / Till')}</span>
                  <Badge variant="outline" className="text-emerald-600 border-emerald-600 font-mono text-[10px]">
                    {t('Direct Till')}
                  </Badge>
                </div>
                <div>
                  <div className="text-[11px] text-muted-foreground">{t('Till Number')}</div>
                  <div className="text-xl font-bold font-mono tracking-wider text-primary">
                    {displayTill}
                  </div>
                </div>
                <div>
                  <div className="text-[11px] text-muted-foreground">{t('Recipient / Business Name')}</div>
                  <div className="text-xs font-semibold uppercase text-foreground">
                    {displayMerchant}
                  </div>
                </div>
                <div className="pt-2 border-t text-[11px] text-muted-foreground space-y-1">
                  <div className="font-medium text-foreground">{t('Quick Dial Codes:')}</div>
                  <div>• Vodacom: <span className="font-mono font-bold text-foreground">*150*00#</span> &gt; Lipa Namba</div>
                  <div>• Tigo: <span className="font-mono font-bold text-foreground">*150*01#</span> &gt; Lipa kwa Simu</div>
                  <div>• Airtel: <span className="font-mono font-bold text-foreground">*150*60#</span> &gt; Lipa Bili</div>
                </div>
              </div>

              <div className="text-[11px] text-muted-foreground flex items-start gap-2 pt-2">
                <Info className="h-4 w-4 text-primary shrink-0 mt-0.5" />
                <span>{t('Once saved, this Till number will instantly reflect on customer invoice payment links, printed receipts, and POS cashier consoles.')}</span>
              </div>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
