import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { Smartphone, Save, Eye, EyeOff, Copy, Check, ExternalLink, ShieldCheck, Zap } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Switch } from '@/components/ui/switch';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';

interface SelcomSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function SelcomSettings({ userSettings = {}, auth }: SelcomSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [showSecret, setShowSecret] = useState(false);
  const [copiedWebhook, setCopiedWebhook] = useState(false);

  const canEdit = auth?.user?.permissions?.includes('edit-selcom-settings') ||
                  auth?.user?.permissions?.includes('edit-settings') ||
                  auth?.user?.permissions?.includes('manage-settings');

  const [settings, setSettings] = useState({
    selcom_enabled: userSettings?.selcom_enabled === 'on' ? 'on' : 'off',
    selcom_mode: userSettings?.selcom_mode || 'sandbox',
    selcom_vendor_id: userSettings?.selcom_vendor_id || '',
    selcom_api_key: userSettings?.selcom_api_key || '',
    selcom_api_secret: userSettings?.selcom_api_secret || '',
    selcom_till_id: userSettings?.selcom_till_id || '',
  });

  useEffect(() => {
    if (userSettings) {
      setSettings({
        selcom_enabled: userSettings?.selcom_enabled === 'on' ? 'on' : 'off',
        selcom_mode: userSettings?.selcom_mode || 'sandbox',
        selcom_vendor_id: userSettings?.selcom_vendor_id || '',
        selcom_api_key: userSettings?.selcom_api_key || '',
        selcom_api_secret: userSettings?.selcom_api_secret || '',
        selcom_till_id: userSettings?.selcom_till_id || '',
      });
    }
  }, [userSettings]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleModeChange = (value: string) => {
    setSettings(prev => ({ ...prev, selcom_mode: value }));
  };

  const handleSwitchChange = (checked: boolean) => {
    setSettings(prev => ({ ...prev, selcom_enabled: checked ? 'on' : 'off' }));
  };

  const webhookUrl = typeof window !== 'undefined'
    ? `${window.location.origin}/api/payment/webhook/selcom`
    : '/api/payment/webhook/selcom';

  const copyWebhookUrl = () => {
    navigator.clipboard.writeText(webhookUrl);
    setCopiedWebhook(true);
    toast.success(t('Selcom Webhook URL copied to clipboard'));
    setTimeout(() => setCopiedWebhook(false), 2000);
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('settings.selcom.update'), {
      settings: {
        ...settings,
        selcom_enabled: settings.selcom_enabled === 'on' ? 'on' : 'off'
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
          toast.success(t('Selcom Paytech settings saved successfully.'));
        }
      },
      onError: (errors) => {
        setIsLoading(false);
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save Selcom settings');
        toast.error(errorMessage);
      }
    });
  };

  const isEnabled = settings.selcom_enabled === 'on';

  return (
    <Card className="border-border shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between pb-4 border-b">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <Smartphone className="h-5 w-5 text-amber-500" />
            <CardTitle className="text-lg font-semibold">{t('Selcom Paytech Tanzania Gateway')}</CardTitle>
            <Badge variant={isEnabled ? 'default' : 'secondary'} className={isEnabled ? 'bg-amber-600 hover:bg-amber-700 text-white' : ''}>
              {isEnabled ? t('Active') : t('Disabled')}
            </Badge>
          </div>
          <CardDescription>
            {t('Tanzania national payment aggregator for automated push-USSD prompts on M-Pesa, Airtel Money, Tigo Pesa, Halopesa & Selcom Cards.')}
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
            <Label htmlFor="selcom_enabled" className="text-base font-medium">
              {t('Enable Selcom Paytech Gateway')}
            </Label>
            <p className="text-xs text-muted-foreground">
              {t('Enables instant USSD push triggers directly to customer mobile phones during online checkout.')}
            </p>
          </div>
          <Switch
            id="selcom_enabled"
            checked={isEnabled}
            onCheckedChange={handleSwitchChange}
            disabled={!canEdit}
          />
        </div>

        {isEnabled && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-2">
            {/* Form Fields */}
            <div className="lg:col-span-2 space-y-5">
              {/* Environment Mode */}
              <div className="space-y-2">
                <Label className="text-sm font-medium">{t('Selcom Environment Mode')}</Label>
                <RadioGroup
                  value={settings.selcom_mode}
                  onValueChange={handleModeChange}
                  disabled={!canEdit}
                  className="flex gap-6"
                >
                  <div className="flex items-center space-x-2 border rounded-lg p-3 hover:bg-muted/30 cursor-pointer">
                    <RadioGroupItem value="sandbox" id="selcom-sandbox" />
                    <Label htmlFor="selcom-sandbox" className="cursor-pointer font-medium">{t('Sandbox (Selcom Testbed)')}</Label>
                  </div>
                  <div className="flex items-center space-x-2 border rounded-lg p-3 hover:bg-muted/30 cursor-pointer">
                    <RadioGroupItem value="live" id="selcom-live" />
                    <Label htmlFor="selcom-live" className="cursor-pointer font-medium text-amber-600 dark:text-amber-400">{t('Live (Production Switch)')}</Label>
                  </div>
                </RadioGroup>
                <p className="text-xs text-muted-foreground">
                  {settings.selcom_mode === 'sandbox'
                    ? t('Use test credentials from Selcom developer sandbox.')
                    : t('Use live production credentials issued by Selcom Paytech for genuine collections.')}
                </p>
              </div>

              {/* Vendor ID */}
              <div className="space-y-2">
                <Label htmlFor="selcom_vendor_id">{t('Selcom Vendor / Merchant ID')}</Label>
                <Input
                  id="selcom_vendor_id"
                  name="selcom_vendor_id"
                  value={settings.selcom_vendor_id}
                  onChange={handleInputChange}
                  placeholder="e.g. VEND00123"
                  disabled={!canEdit}
                  className="font-mono text-sm"
                />
                <p className="text-xs text-muted-foreground">
                  {t('Your registered Vendor ID assigned by Selcom Paytech.')}
                </p>
              </div>

              {/* API Key */}
              <div className="space-y-2">
                <Label htmlFor="selcom_api_key">{t('Selcom API Key')}</Label>
                <Input
                  id="selcom_api_key"
                  name="selcom_api_key"
                  value={settings.selcom_api_key}
                  onChange={handleInputChange}
                  placeholder="Enter Selcom API Key"
                  disabled={!canEdit}
                  className="font-mono text-sm"
                />
              </div>

              {/* API Secret */}
              <div className="space-y-2">
                <Label htmlFor="selcom_api_secret">{t('Selcom API Secret')}</Label>
                <div className="relative">
                  <Input
                    id="selcom_api_secret"
                    name="selcom_api_secret"
                    type={showSecret ? 'text' : 'password'}
                    value={settings.selcom_api_secret}
                    onChange={handleInputChange}
                    placeholder="Enter Selcom API Secret"
                    disabled={!canEdit}
                    className="font-mono text-sm pr-10"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="absolute right-0 top-0 h-full px-3 text-muted-foreground hover:text-foreground"
                    onClick={() => setShowSecret(!showSecret)}
                  >
                    {showSecret ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                  </Button>
                </div>
                <p className="text-xs text-muted-foreground">
                  {t('Used to compute HMAC-SHA256 signature hashes for API requests.')}
                </p>
              </div>

              {/* Till / Terminal ID */}
              <div className="space-y-2">
                <Label htmlFor="selcom_till_id">{t('Selcom Till ID / POS Terminal (Optional)')}</Label>
                <Input
                  id="selcom_till_id"
                  name="selcom_till_id"
                  value={settings.selcom_till_id}
                  onChange={handleInputChange}
                  placeholder="e.g. 1002003"
                  disabled={!canEdit}
                  className="font-mono text-sm"
                />
                <p className="text-xs text-muted-foreground">
                  {t('Optional POS terminal or sub-till routing ID for multi-branch accounting.')}
                </p>
              </div>

              {/* Webhook & Callback Endpoint */}
              <div className="space-y-2 p-3 bg-muted/40 rounded-lg border">
                <Label className="text-xs font-medium text-muted-foreground">{t('Selcom Callback & Webhook URL')}</Label>
                <div className="flex items-center gap-2">
                  <Input
                    readOnly
                    value={webhookUrl}
                    className="font-mono text-xs bg-background"
                  />
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={copyWebhookUrl}
                    className="shrink-0"
                  >
                    {copiedWebhook ? <Check className="h-4 w-4 text-emerald-500" /> : <Copy className="h-4 w-4" />}
                  </Button>
                </div>
                <p className="text-[11px] text-muted-foreground">
                  {t('Provide this URL to Selcom support or portal so payment completion IPNs update invoices in real time.')}
                </p>
              </div>
            </div>

            {/* Guide Sidebar */}
            <div className="lg:col-span-1 border rounded-xl p-5 bg-amber-50/50 dark:bg-amber-950/20 space-y-4">
              <div className="flex items-center gap-2 text-amber-900 dark:text-amber-300 font-semibold text-sm">
                <Zap className="h-4 w-4 text-amber-600" />
                <h4>{t('About Selcom Paytech')}</h4>
              </div>
              <p className="text-xs text-amber-900/90 dark:text-amber-200/90 leading-relaxed">
                {t('Selcom is Tanzania’s largest digital payment switch, providing direct integration for:')}
              </p>
              <ul className="text-xs text-amber-900/90 dark:text-amber-200/90 space-y-1 list-disc list-inside">
                <li><strong className="text-emerald-700 dark:text-emerald-300">Vodacom M-Pesa</strong> Push USSD</li>
                <li><strong className="text-blue-700 dark:text-blue-300">Tigo Pesa</strong> Push USSD</li>
                <li><strong className="text-red-700 dark:text-red-300">Airtel Money</strong> Push USSD</li>
                <li><strong className="text-orange-700 dark:text-orange-300">Halopesa</strong> Push USSD</li>
                <li><strong className="text-purple-700 dark:text-purple-300">Visa / Mastercard</strong> (TZS & USD)</li>
              </ul>
              <div className="text-[11px] text-amber-800/80 dark:text-amber-400 border-t border-amber-200 dark:border-amber-900/50 pt-3">
                {t('Contact Selcom (selcom.net) to onboard your Tanzanian business and obtain API credentials.')}
              </div>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
