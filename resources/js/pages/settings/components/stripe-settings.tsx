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

interface StripeSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function StripeSettings({ userSettings = {}, auth }: StripeSettingsProps) {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [showSecret, setShowSecret] = useState(false);
  const [showWebhookSecret, setShowWebhookSecret] = useState(false);
  const [copiedWebhook, setCopiedWebhook] = useState(false);

  const canEdit = auth?.user?.permissions?.includes('edit-stripe-settings') ||
                  auth?.user?.permissions?.includes('edit-settings') ||
                  auth?.user?.permissions?.includes('manage-settings');

  const [settings, setSettings] = useState({
    stripe_enabled: userSettings?.stripe_enabled === 'on' ? 'on' : 'off',
    stripe_key: userSettings?.stripe_key || '',
    stripe_secret: userSettings?.stripe_secret || '',
    stripe_webhook_secret: userSettings?.stripe_webhook_secret || '',
  });

  useEffect(() => {
    if (userSettings) {
      setSettings({
        stripe_enabled: userSettings?.stripe_enabled === 'on' ? 'on' : 'off',
        stripe_key: userSettings?.stripe_key || '',
        stripe_secret: userSettings?.stripe_secret || '',
        stripe_webhook_secret: userSettings?.stripe_webhook_secret || '',
      });
    }
  }, [userSettings]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setSettings(prev => ({ ...prev, [name]: value }));
  };

  const handleSwitchChange = (checked: boolean) => {
    setSettings(prev => ({ ...prev, stripe_enabled: checked ? 'on' : 'off' }));
  };

  const webhookUrl = typeof window !== 'undefined'
    ? `${window.location.origin}/api/payment/webhook/stripe`
    : '/api/payment/webhook/stripe';

  const copyWebhookUrl = () => {
    navigator.clipboard.writeText(webhookUrl);
    setCopiedWebhook(true);
    toast.success(t('Stripe Webhook URL copied to clipboard'));
    setTimeout(() => setCopiedWebhook(false), 2000);
  };

  const saveSettings = () => {
    setIsLoading(true);

    router.post(route('stripe.settings.update'), {
      settings: {
        ...settings,
        stripe_enabled: settings.stripe_enabled === 'on' ? 'on' : 'off'
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
          toast.success(t('Stripe settings saved successfully.'));
        }
      },
      onError: (errors) => {
        setIsLoading(false);
        const errorMessage = errors.error || Object.values(errors).join(', ') || t('Failed to save Stripe settings');
        toast.error(errorMessage);
      }
    });
  };

  const isEnabled = settings.stripe_enabled === 'on';

  return (
    <Card className="border-border shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between pb-4 border-b">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <CreditCard className="h-5 w-5 text-indigo-500" />
            <CardTitle className="text-lg font-semibold">{t('Stripe Gateway Settings')}</CardTitle>
            <Badge variant={isEnabled ? 'default' : 'secondary'} className={isEnabled ? 'bg-indigo-600 hover:bg-indigo-700' : ''}>
              {isEnabled ? t('Active') : t('Disabled')}
            </Badge>
          </div>
          <CardDescription>
            {t('Accept global credit cards (Visa, Mastercard, Amex), Apple Pay, and Google Pay with PCI-compliant tokenization.')}
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
            <Label htmlFor="stripe_enabled" className="text-base font-medium">
              {t('Enable Stripe Gateway')}
            </Label>
            <p className="text-xs text-muted-foreground">
              {t('Turn on to enable direct debit/credit card processing via Stripe Elements on invoice payment pages.')}
            </p>
          </div>
          <Switch
            id="stripe_enabled"
            checked={isEnabled}
            onCheckedChange={handleSwitchChange}
            disabled={!canEdit}
          />
        </div>

        {isEnabled && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-2">
            {/* Form Fields */}
            <div className="lg:col-span-2 space-y-5">
              {/* Stripe Publishable Key */}
              <div className="space-y-2">
                <Label htmlFor="stripe_key">{t('Stripe Publishable Key')}</Label>
                <Input
                  id="stripe_key"
                  name="stripe_key"
                  value={settings.stripe_key}
                  onChange={handleInputChange}
                  placeholder="pk_test_... or pk_live_..."
                  disabled={!canEdit}
                  className="font-mono text-sm"
                />
                <p className="text-xs text-muted-foreground">
                  {t('Public API key used for client-side card field tokenization and Stripe Elements.')}
                </p>
              </div>

              {/* Stripe Secret Key */}
              <div className="space-y-2">
                <Label htmlFor="stripe_secret">{t('Stripe Secret Key')}</Label>
                <div className="relative">
                  <Input
                    id="stripe_secret"
                    name="stripe_secret"
                    type={showSecret ? 'text' : 'password'}
                    value={settings.stripe_secret}
                    onChange={handleInputChange}
                    placeholder="sk_test_... or sk_live_..."
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
                  {t('Secret API key used on the server to create PaymentIntents and capture customer settlements.')}
                </p>
              </div>

              {/* Stripe Webhook Secret */}
              <div className="space-y-2">
                <Label htmlFor="stripe_webhook_secret">{t('Stripe Webhook Signing Secret (Optional)')}</Label>
                <div className="relative">
                  <Input
                    id="stripe_webhook_secret"
                    name="stripe_webhook_secret"
                    type={showWebhookSecret ? 'text' : 'password'}
                    value={settings.stripe_webhook_secret}
                    onChange={handleInputChange}
                    placeholder="whsec_..."
                    disabled={!canEdit}
                    className="font-mono text-sm pr-10"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="absolute right-0 top-0 h-full px-3 text-muted-foreground hover:text-foreground"
                    onClick={() => setShowWebhookSecret(!showWebhookSecret)}
                  >
                    {showWebhookSecret ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                  </Button>
                </div>
                <p className="text-xs text-muted-foreground">
                  {t('Signing secret used to cryptographically verify asynchronous webhook events from Stripe.')}
                </p>
              </div>

              {/* Webhook Endpoint */}
              <div className="space-y-2 p-3 bg-muted/40 rounded-lg border">
                <Label className="text-xs font-medium text-muted-foreground">{t('Stripe Webhook Endpoint URL')}</Label>
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
                  {t('Add this URL to Stripe Dashboard > Developers > Webhooks for events: payment_intent.succeeded and charge.refunded.')}
                </p>
              </div>
            </div>

            {/* Guide Sidebar */}
            <div className="lg:col-span-1 border rounded-xl p-5 bg-indigo-50/50 dark:bg-indigo-950/20 space-y-4">
              <div className="flex items-center gap-2 text-indigo-900 dark:text-indigo-300 font-semibold text-sm">
                <ShieldCheck className="h-4 w-4" />
                <h4>{t('How to Connect Stripe')}</h4>
              </div>
              <ol className="space-y-2.5 text-xs text-indigo-900/90 dark:text-indigo-200/90 list-decimal list-inside">
                <li>
                  {t('Log in to ')}
                  <a href="https://dashboard.stripe.com" target="_blank" rel="noopener noreferrer" className="underline font-semibold inline-flex items-center gap-1">
                    Stripe Dashboard <ExternalLink className="h-3 w-3 inline" />
                  </a>
                </li>
                <li>{t('Navigate to Developers > API keys.')}</li>
                <li>{t('Copy the "Publishable key" (pk_...) to the field on the left.')}</li>
                <li>{t('Reveal and copy the "Secret key" (sk_...) into the secret field.')}</li>
                <li>{t('For local/dev testing, use testmode keys starting with pk_test_ and sk_test_.')}</li>
              </ol>
              <div className="text-[11px] text-indigo-700/80 dark:text-indigo-400 border-t border-indigo-200 dark:border-indigo-900/50 pt-3">
                {t('Funds will be automatically credited to your Stripe balance and paid out according to your scheduled payout settings.')}
              </div>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
