import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { useTranslation } from 'react-i18next';
import { useEffect } from 'react';
import { Smartphone, Download, CheckCircle2 } from 'lucide-react';

interface AppsProps {
    data: any;
    getSectionData: (key: string) => any;
    updateSectionData: (key: string, updates: any) => void;
    updateSectionVisibility: (key: string, visible: boolean) => void;
}

export default function Apps({ data, getSectionData, updateSectionData, updateSectionVisibility }: AppsProps) {
    const { t } = useTranslation();
    const sectionData = getSectionData('apps') || {};
    const isVisible = data.config_sections?.section_visibility?.apps !== false;

    useEffect(() => {
        const defaultSettings = {
            title: 'Mutamark Mobile & Barcode Scanner Suite',
            subtitle: 'Built for warehouse dock workers, distribution drivers, and store cashiers. Seamlessly connect rugged barcode scanners (Zebra, Honeywell, Sunmi) and smartphones directly to Mutamark ERP.',
            badge_text: 'Enterprise Mobile Companion v2.4',
            play_store_url: 'https://play.google.com/store/apps/details?id=com.mutamark.erp',
            app_store_url: 'https://apps.apple.com/app/mutamark-erp/id123456789',
            apk_download_url: '/packages/workdo/LandingPage/src/marketplace/image5.png',
            image: '/packages/workdo/LandingPage/src/marketplace/image5.png',
        };

        const currentData = getSectionData('apps');
        const hasAllKeys = Object.keys(defaultSettings).every(key => key in currentData);

        if (!hasAllKeys) {
            updateSectionData('apps', { ...defaultSettings, ...currentData });
        }
    }, []);

    return (
        <div className="space-y-6">
            <Card>
                <CardHeader>
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-emerald-100 rounded-lg">
                                <Smartphone className="h-5 w-5 text-emerald-600" />
                            </div>
                            <div>
                                <CardTitle className="text-base">{t('Mobile & Scanner Apps Settings')}</CardTitle>
                                <p className="text-sm text-gray-500">
                                    {t('Manage app store download links, rugged barcode scanner APKs, and mobile companion branding')}
                                </p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <Label htmlFor="apps-visibility" className="text-sm">
                                {isVisible ? t('Visible') : t('Hidden')}
                            </Label>
                            <Switch
                                id="apps-visibility"
                                checked={isVisible}
                                onCheckedChange={(checked) => updateSectionVisibility('apps', checked)}
                            />
                        </div>
                    </div>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="apps-title">{t('Section Title')}</Label>
                            <Input
                                id="apps-title"
                                value={sectionData.title || ''}
                                onChange={(e) => updateSectionData('apps', { title: e.target.value })}
                                placeholder={t('Mutamark Mobile & Barcode Scanner Suite')}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="apps-badge">{t('Version / Badge Label')}</Label>
                            <Input
                                id="apps-badge"
                                value={sectionData.badge_text || ''}
                                onChange={(e) => updateSectionData('apps', { badge_text: e.target.value })}
                                placeholder={t('Enterprise Mobile Companion v2.4')}
                            />
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="apps-subtitle">{t('Section Subtitle')}</Label>
                        <Textarea
                            id="apps-subtitle"
                            rows={3}
                            value={sectionData.subtitle || ''}
                            onChange={(e) => updateSectionData('apps', { subtitle: e.target.value })}
                            placeholder={t('Describe the mobile app capabilities for warehouse intake, POS, and audits...')}
                        />
                    </div>

                    <div className="border-t pt-4 space-y-4">
                        <h4 className="text-sm font-semibold text-gray-900 flex items-center gap-2">
                            <Download className="h-4 w-4 text-emerald-600" />
                            {t('Store & Direct Download URLs')}
                        </h4>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="apps-play-store">{t('Google Play Store URL')}</Label>
                                <Input
                                    id="apps-play-store"
                                    value={sectionData.play_store_url || ''}
                                    onChange={(e) => updateSectionData('apps', { play_store_url: e.target.value })}
                                    placeholder="https://play.google.com/..."
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="apps-app-store">{t('Apple App Store URL')}</Label>
                                <Input
                                    id="apps-app-store"
                                    value={sectionData.app_store_url || ''}
                                    onChange={(e) => updateSectionData('apps', { app_store_url: e.target.value })}
                                    placeholder="https://apps.apple.com/..."
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="apps-apk-url">{t('Direct Enterprise APK (Zebra / Sunmi)')}</Label>
                                <Input
                                    id="apps-apk-url"
                                    value={sectionData.apk_download_url || ''}
                                    onChange={(e) => updateSectionData('apps', { apk_download_url: e.target.value })}
                                    placeholder="/downloads/mutamark-scanner.apk"
                                />
                            </div>
                        </div>
                    </div>

                    <div className="border-t pt-4 space-y-2">
                        <Label htmlFor="apps-image">{t('Mockup Screen Image Path')}</Label>
                        <Input
                            id="apps-image"
                            value={sectionData.image || ''}
                            onChange={(e) => updateSectionData('apps', { image: e.target.value })}
                            placeholder="/packages/workdo/LandingPage/src/marketplace/image5.png"
                        />
                        <p className="text-xs text-gray-500">
                            {t('Path to high-resolution mobile terminal mockup. Default: /packages/workdo/LandingPage/src/marketplace/image5.png')}
                        </p>
                    </div>

                    <div className="rounded-lg bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3">
                        <CheckCircle2 className="h-5 w-5 text-emerald-600 shrink-0 mt-0.5" />
                        <div className="text-xs text-emerald-800">
                            <p className="font-semibold">{t('Hardware Driver Notice')}</p>
                            <p className="mt-0.5">
                                {t('The direct enterprise APK includes built-in EMDK drivers for Zebra 2D SE4710 imagers, Honeywell Mobility Edge, and Sunmi thermal ESC/POS receipt print engines.')}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
