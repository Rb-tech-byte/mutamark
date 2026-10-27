import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { CreditCard, Save, Eye, EyeOff, Copy, Check, ExternalLink, ShieldCheck, Globe } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Switch } from '@/components/ui/switch';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';

interface PesapalSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function PesapalSettings({ userSettings = {}, auth }: PesapalSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [showSecret, setShowSecret] = useState(false);
  const [copiedWebhook, setCopiedWebhook] = useState(false);

  const canEdit = auth?.user?.permissions?.includes('edit-pesapal-settings') ||
                  auth?.user?.permissions?.includes('edit-settings') ||
                  auth?.user?.permissions?.includes('manage-settings');

  const [settings, setSettings] = useState({
    pesapal_enabled: userSettings?.pesapal_enabled === 'on' ? 'on' : 'off',
    pesapal_mode: userSettings?.pesapal_mode || 'sandbox',
    pesapal_consumer_key: userSettings?.pesapal_consumer_key || '',
    pesapal_consumer_secret: userSettings?.pesapal_consumer_secret || '',
    pesapal_ipn_id: userSettings?.pesapal_ipn_id || '',
  });

  useEffect(() => {
    if (userSettings) {
      setSettings({
        pesapal_enabled: userSettings?.pesapal_enabled === 'on' ? 'on' : 'off',
        pesapal_mode: userSettings?.pesapal_mode || 'sandbox',
        pesapal_consumer_key: userSettings?.pesapal_consumer_key || '',
        pesapal_consumer_secret: userSettings?.pesapal_consumer_secret || '',
        pesapal_ipn_id: userSettings?.pesapal_ipn_id || '',
      });
    }
  }, [userSettings]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleModeChange = (value: string) => {
    setSettings(prev => ({ ...prev, pesapal_mode: value }));
  };

  const handleSwitchChange = (checked: boolean) => {
    setSettings(prev => ({ ...prev, pesapal_enabled: checked ? 'on' : 'off' }));
  };

  const webhookUrl = typeof window !== 'undefined'
    ? `${window.location.origin}/api/payment/webhook/pesapal`
    : '/api/payment/webhook/pesapal';

  const copyWebhookUrl = () => {
    navigator.clipboard.writeText(webhookUrl);
    setCopiedWebhook(true);
    toast.success(t('Pesapal IPN URL copied to clipboard'));
    setTimeout(() => setCopiedWebhook(false), 2000);
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('settings.pesapal.update'), {
      settings: {
        ...settings,
        pesapal_enabled: settings.pesapal_enabled === 'on' ? 'on' : 'off'
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
          toast.success(t('Pesapal settings saved successfully.'));
        }
      },
      onError: (errors) => {
        setIsLoading(false);
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save Pesapal settings');
        toast.error(errorMessage);
      }
    });
  };

  const isEnabled = settings.pesapal_enabled === 'on';

  return (
    <Card className="border-border shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between pb-4 border-b">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <Globe className="h-5 w-5 text-teal-600" />
            <CardTitle className="text-lg font-semibold">{t('Pesapal v3 Gateway Settings')}</CardTitle>
            <Badge variant={isEnabled ? 'default' : 'secondary'} className={isEnabled ? 'bg-teal-600 hover:bg-teal-700 text-white' : ''}>
              {isEnabled ? t('Active') : t('Disabled')}
            </Badge>
          </div>
          <CardDescription>
            {t('East African multi-currency payment gateway for mobile money (TZS, KES, UGX) and international cards.')}
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
            <Label htmlFor="pesapal_enabled" className="text-base font-medium">
              {t('Enable Pesapal Gateway')}
            </Label>
            <p className="text-xs text-muted-foreground">
              {t('Turn on to offer Pesapal v3 hosted iframe & redirect payment methods on invoices.')}
            </p>
          </div>
          <Switch
            id="pesapal_enabled"
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
                <Label className="text-sm font-medium">{t('Pesapal Environment Mode')}</Label>
                <RadioGroup
                  value={settings.pesapal_mode}
                  onValueChange={handleModeChange}
                  disabled={!canEdit}
                  className="flex gap-6"
                >
                  <div className="flex items-center space-x-2 border rounded-lg p-3 hover:bg-muted/30 cursor-pointer">
                    <RadioGroupItem value="sandbox" id="pesapal-sandbox" />
                    <Label htmlFor="pesapal-sandbox" className="cursor-pointer font-medium">{t('Demo / Sandbox (cybqa.pesapal.com)')}</Label>
                  </div>
                  <div className="flex items-center space-x-2 border rounded-lg p-3 hover:bg-muted/30 cursor-pointer">
                    <RadioGroupItem value="live" id="pesapal-live" />
                    <Label htmlFor="pesapal-live" className="cursor-pointer font-medium text-teal-600 dark:text-teal-400">{t('Live (pay.pesapal.com)')}</Label>
                  </div>
                </RadioGroup>
              </div>

              {/* Consumer Key */}
              <div className="space-y-2">
                <Label htmlFor="pesapal_consumer_key">{t('Pesapal Consumer Key')}</Label>
                <Input
                  id="pesapal_consumer_key"
                  name="pesapal_consumer_key"
                  value={settings.pesapal_consumer_key}
                  onChange={handleInputChange}
                  placeholder="Enter Pesapal Consumer Key"
                  disabled={!canEdit}
                  className="font-mono text-sm"
                />
                <p className="text-xs text-muted-foreground">
                  {t('The consumer key generated under API Keys in your Pesapal Merchant Dashboard.')}
                </p>
              </div>

              {/* Consumer Secret */}
              <div className="space-y-2">
                <Label htmlFor="pesapal_consumer_secret">{t('Pesapal Consumer Secret')}</Label>
                <div className="relative">
                  <Input
                    id="pesapal_consumer_secret"
                    name="pesapal_consumer_secret"
                    type={showSecret ? 'text' : 'password'}
                    value={settings.pesapal_consumer_secret}
                    onChange={handleInputChange}
                    placeholder="Enter Pesapal Consumer Secret"
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
                  {t('Confidential consumer secret used to generate OAuth 2.0 bearer access tokens.')}
                </p>
              </div>

              {/* IPN ID */}
              <div className="space-y-2">
                <Label htmlFor="pesapal_ipn_id">{t('Registered IPN Notification ID')}</Label>
                <Input
                  id="pesapal_ipn_id"
                  name="pesapal_ipn_id"
                  value={settings.pesapal_ipn_id}
                  onChange={handleInputChange}
                  placeholder="e.g. 52445b2b-0e13-4bf0-ba88-..."
                  disabled={!canEdit}
                  className="font-mono text-sm"
                />
                <p className="text-xs text-muted-foreground">
                  {t('The UUID returned by Pesapal when registering your Instant Payment Notification (IPN) webhook URL.')}
                </p>
              </div>

              {/* IPN URL */}
              <div className="space-y-2 p-3 bg-muted/40 rounded-lg border">
                <Label className="text-xs font-medium text-muted-foreground">{t('Pesapal IPN Webhook URL to Register')}</Label>
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
                  {t('Register this URL via Pesapal API or dashboard with IPN type "GET" or "POST".')}
                </p>
              </div>
            </div>

            {/* Guide Sidebar */}
            <div className="lg:col-span-1 border rounded-xl p-5 bg-teal-50/50 dark:bg-teal-950/20 space-y-4">
              <div className="flex items-center gap-2 text-teal-900 dark:text-teal-300 font-semibold text-sm">
                <ShieldCheck className="h-4 w-4" />
                <h4>{t('Connecting Pesapal v3')}</h4>
              </div>
              <ol className="space-y-2.5 text-xs text-teal-900/90 dark:text-teal-200/90 list-decimal list-inside">
                <li>
                  {t('Register merchant account at ')}
                  <a href="https://pesapal.com" target="_blank" rel="noopener noreferrer" className="underline font-semibold inline-flex items-center gap-1">
                    Pesapal.com <ExternalLink className="h-3 w-3 inline" />
                  </a>
                </li>
                <li>{t('Go to Settings > API Credentials in Pesapal Dashboard.')}</li>
                <li>{t('Copy Consumer Key and Secret to the fields on the left.')}</li>
                <li>{t('Submit IPN Registration with the Webhook URL provided.')}</li>
                <li>{t('Paste the returned Notification ID into the IPN ID field.')}</li>
              </ol>
              <div className="text-[11px] text-teal-800/80 dark:text-teal-400 border-t border-teal-200 dark:border-teal-900/50 pt-3">
                {t('Pesapal supports transactions across Tanzania, Kenya, Uganda, Rwanda, and international cards.')}
              </div>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
