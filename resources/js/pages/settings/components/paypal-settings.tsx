import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { CreditCard, Save, Eye, EyeOff, Copy, Check, ExternalLink, ShieldCheck } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Switch } from '@/components/ui/switch';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';

interface PaypalSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function PaypalSettings({ userSettings = {}, auth }: PaypalSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [showSecret, setShowSecret] = useState(false);
  const [copiedWebhook, setCopiedWebhook] = useState(false);

  const canEdit = auth?.user?.permissions?.includes('edit-paypal-settings') ||
                  auth?.user?.permissions?.includes('edit-settings') ||
                  auth?.user?.permissions?.includes('manage-settings');

  const [settings, setSettings] = useState({
    paypal_enabled: userSettings?.paypal_enabled === 'on' ? 'on' : 'off',
    paypal_mode: userSettings?.paypal_mode || 'sandbox',
    paypal_client_id: userSettings?.paypal_client_id || '',
    paypal_secret_key: userSettings?.paypal_secret_key || '',
  });

  useEffect(() => {
    if (userSettings) {
      setSettings({
        paypal_enabled: userSettings?.paypal_enabled === 'on' ? 'on' : 'off',
        paypal_mode: userSettings?.paypal_mode || 'sandbox',
        paypal_client_id: userSettings?.paypal_client_id || '',
        paypal_secret_key: userSettings?.paypal_secret_key || '',
      });
    }
  }, [userSettings]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleModeChange = (value: string) => {
    setSettings(prev => ({ ...prev, paypal_mode: value }));
  };

  const handleSwitchChange = (checked: boolean) => {
    setSettings(prev => ({ ...prev, paypal_enabled: checked ? 'on' : 'off' }));
  };

  const webhookUrl = typeof window !== 'undefined'
    ? `${window.location.origin}/api/payment/webhook/paypal`
    : '/api/payment/webhook/paypal';

  const copyWebhookUrl = () => {
    navigator.clipboard.writeText(webhookUrl);
    setCopiedWebhook(true);
    toast.success(t('Webhook URL copied to clipboard'));
    setTimeout(() => setCopiedWebhook(false), 2000);
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('paypal.settings.update'), {
      settings: {
        ...settings,
        paypal_enabled: settings.paypal_enabled === 'on' ? 'on' : 'off'
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
          toast.success(t('PayPal settings saved successfully.'));
        }
      },
      onError: (errors) => {
        setIsLoading(false);
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save PayPal settings');
        toast.error(errorMessage);
      }
    });
  };

  const isEnabled = settings.paypal_enabled === 'on';

  return (
    <Card className="border-border shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between pb-4 border-b">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <CreditCard className="h-5 w-5 text-primary" />
            <CardTitle className="text-lg font-semibold">{t('PayPal Gateway Settings')}</CardTitle>
            <Badge variant={isEnabled ? 'default' : 'secondary'} className={isEnabled ? 'bg-emerald-600 hover:bg-emerald-700' : ''}>
              {isEnabled ? t('Active') : t('Disabled')}
            </Badge>
          </div>
          <CardDescription>
            {t('Accept international debit/credit cards and PayPal wallet payments from global customers.')}
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
            <Label htmlFor="paypal_enabled" className="text-base font-medium">
              {t('Enable PayPal Gateway')}
            </Label>
            <p className="text-xs text-muted-foreground">
              {t('Turn on to display PayPal as an automated payment option on checkout pages & sales invoices.')}
            </p>
          </div>
          <Switch
            id="paypal_enabled"
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
                <Label className="text-sm font-medium">{t('Environment Mode')}</Label>
                <RadioGroup
                  value={settings.paypal_mode}
                  onValueChange={handleModeChange}
                  disabled={!canEdit}
                  className="flex gap-6"
                >
                  <div className="flex items-center space-x-2 border rounded-lg p-3 hover:bg-muted/30 cursor-pointer">
                    <RadioGroupItem value="sandbox" id="paypal-sandbox" />
                    <Label htmlFor="paypal-sandbox" className="cursor-pointer font-medium">{t('Sandbox (Testing)')}</Label>
                  </div>
                  <div className="flex items-center space-x-2 border rounded-lg p-3 hover:bg-muted/30 cursor-pointer">
                    <RadioGroupItem value="live" id="paypal-live" />
                    <Label htmlFor="paypal-live" className="cursor-pointer font-medium text-emerald-600 dark:text-emerald-400">{t('Live (Production)')}</Label>
                  </div>
                </RadioGroup>
                <p className="text-xs text-muted-foreground">
                  {settings.paypal_mode === 'sandbox'
                    ? t('Use sandbox credentials to test without real money deductions.')
                    : t('Use live production credentials to accept real settlements.')}
                </p>
              </div>

              {/* Client ID */}
              <div className="space-y-2">
                <Label htmlFor="paypal_client_id">{t('PayPal Client ID')}</Label>
                <Input
                  id="paypal_client_id"
                  name="paypal_client_id"
                  value={settings.paypal_client_id}
                  onChange={handleInputChange}
                  placeholder="e.g. A21AAHz... or your PayPal App Client ID"
                  disabled={!canEdit}
                  className="font-mono text-sm"
                />
                <p className="text-xs text-muted-foreground">
                  {t('The public REST App client identifier generated in your PayPal Developer dashboard.')}
                </p>
              </div>

              {/* Secret Key */}
              <div className="space-y-2">
                <Label htmlFor="paypal_secret_key">{t('PayPal Secret Key')}</Label>
                <div className="relative">
                  <Input
                    id="paypal_secret_key"
                    name="paypal_secret_key"
                    type={showSecret ? 'text' : 'password'}
                    value={settings.paypal_secret_key}
                    onChange={handleInputChange}
                    placeholder="Enter PayPal secret key"
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
                  {t('Your confidential API secret key for server-to-server token generation and order captures.')}
                </p>
              </div>

              {/* Webhook & IPN Endpoint */}
              <div className="space-y-2 p-3 bg-muted/40 rounded-lg border">
                <Label className="text-xs font-medium text-muted-foreground">{t('PayPal Webhook / IPN Listener URL')}</Label>
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
                  {t('Register this URL in your PayPal Developer dashboard under Webhook Subscriptions for event: PAYMENT.CAPTURE.COMPLETED')}
                </p>
              </div>
            </div>

            {/* Guide Sidebar */}
            <div className="lg:col-span-1 border rounded-xl p-5 bg-blue-50/50 dark:bg-blue-950/20 space-y-4">
              <div className="flex items-center gap-2 text-blue-900 dark:text-blue-300 font-semibold text-sm">
                <ShieldCheck className="h-4 w-4" />
                <h4>{t('How to Connect PayPal')}</h4>
              </div>
              <ol className="space-y-2.5 text-xs text-blue-900/90 dark:text-blue-200/90 list-decimal list-inside">
                <li>
                  {t('Log in to ')}
                  <a href="https://developer.paypal.com" target="_blank" rel="noopener noreferrer" className="underline font-semibold inline-flex items-center gap-1">
                    PayPal Developer <ExternalLink className="h-3 w-3 inline" />
                  </a>
                </li>
                <li>{t('Navigate to Apps & Credentials and select your target account.')}</li>
                <li>{t('Click "Create App", name it "Mutamark ERP", and select Merchant.')}</li>
                <li>{t('Copy Client ID and Secret into the fields on the left.')}</li>
                <li>{t('Toggle mode to Sandbox for testing or Live for genuine payments.')}</li>
              </ol>
              <div className="text-[11px] text-blue-700/80 dark:text-blue-400 border-t border-blue-200 dark:border-blue-900/50 pt-3">
                {t('Supported currencies include USD, EUR, GBP, KES, etc. Payments convert automatically to base currency.')}
              </div>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
