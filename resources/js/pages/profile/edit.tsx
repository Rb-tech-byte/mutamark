import AuthenticatedLayout from "@/layouts/authenticated-layout";
import UpdatePasswordForm from "@/pages/profile/partials/update-password-form";
import UpdateProfileInformationForm from "@/pages/profile/partials/update-profile-information-form";
import { Head, usePage, useForm } from "@inertiajs/react";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { useTranslation } from 'react-i18next';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useFlashMessages } from '@/hooks/useFlashMessages';
import { useFormFields } from '@/hooks/useFormFields';
import { PageProps } from '@/types';

export default function Edit({
    mustVerifyEmail,
    status,
    twoFactorEnabled = false, twoFactorSetup, recoveryCodes = [],
}: { mustVerifyEmail: boolean; status?: string; twoFactorEnabled?: boolean; twoFactorSetup?: string; recoveryCodes?: string[] }) {
    const { t } = useTranslation();
    const { auth } = usePage<PageProps>().props;
    useFlashMessages();
    const security = useForm({ action: 'setup', password: '', code: '' });

    const formFields = useFormFields('getProfileFields', {}, () => { }, {}, 'edit', t);
    const integrationFields = useFormFields('getJobsearchFields', {}, () => { }, {}, 'edit', t, 'JobSearch');

    return (
        <AuthenticatedLayout
            breadcrumbs={[{label: t('Profile')}]}
            pageTitle={t('Profile Settings')}
        >
            <Head title={t('Profile')} />

            <Card className="shadow-sm">
                <CardContent className="p-6">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <Card className="shadow-sm min-h-[500px]">
                            <CardHeader className="border-b bg-gray-50/50">
                                <CardTitle className="text-base">{t('Profile Information')}</CardTitle>
                                <p className="text-sm text-gray-600 mt-1">{t('Details about your personal information')}</p>
                            </CardHeader>
                            <CardContent className="p-6">
                                <UpdateProfileInformationForm
                                    mustVerifyEmail={mustVerifyEmail}
                                    status={status}
                                    className=""
                                />
                            </CardContent>
                        </Card>

                        <Card className="shadow-sm min-h-[500px]">
                            <CardHeader className="border-b bg-gray-50/50">
                                <CardTitle className="text-base">{t('Change Password')}</CardTitle>
                                <p className="text-sm text-gray-600 mt-1">{t('Details about your account password change')}</p>
                            </CardHeader>
                            <CardContent className="p-6">
                                <UpdatePasswordForm className="" />
                            </CardContent>
                        </Card>
                    </div>

                    {formFields.length > 0 && (
                        <div className="mt-6">
                            {formFields.map((field) => (
                                <div key={field.id}>
                                    {field.component}
                                </div>
                            ))}
                        </div>
                    )}

                    {integrationFields.length > 0 && auth.user?.permissions?.includes('job-seeker-information') && (auth.user as any)?.type !== 'company' && (
                        <div className="mt-6">
                            <Card className="shadow-sm">
                                <CardHeader className="border-b bg-gray-50/50">
                                    <CardTitle className="text-base">{t('Job Seeker Information')}</CardTitle>
                                    <p className="text-sm text-gray-600 mt-1">{t('Complete your job seeker profile with this details')}</p>
                                </CardHeader>
                                <CardContent className="p-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {integrationFields.map((field) => (
                                            <div key={field.id}>
                                                {field.component}
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    )}
                </CardContent>
            </Card>
            <Card className="mt-6"><CardHeader><CardTitle>{t('Two-factor authentication')}</CardTitle></CardHeader><CardContent className="space-y-4">
                <p>{twoFactorEnabled ? t('Authenticator enabled') : t('Add an authenticator app to protect your account.')}</p>
                {twoFactorSetup && <p className="break-all">{t('Enter this setup key in your authenticator app')}: <code>{twoFactorSetup}</code></p>}
                {recoveryCodes.length > 0 && <div><p>{t('Save these recovery codes securely. Each code works once.')}</p><pre className="whitespace-pre-wrap">{recoveryCodes.join('\n')}</pre></div>}
                <form className="space-y-3" onSubmit={event => { event.preventDefault(); security.transform(data => ({ ...data, action: twoFactorEnabled ? 'disable' : twoFactorSetup ? 'confirm' : 'setup' })); security.post(route('profile.two-factor'), { onSuccess: () => security.reset('password', 'code') }); }}>
                    <Label htmlFor="security-password">{t('Current password')}</Label><Input id="security-password" type="password" required autoComplete="current-password" value={security.data.password} onChange={event => security.setData('password', event.target.value)} />
                    {(twoFactorSetup || twoFactorEnabled) && <><Label htmlFor="security-code">{t('Authenticator or recovery code')}</Label><Input id="security-code" required autoComplete="one-time-code" value={security.data.code} onChange={event => security.setData('code', event.target.value)} /></>}
                    {Object.values(security.errors).map((error, index) => <p key={index} role="alert" className="text-destructive text-sm">{error}</p>)}
                    <Button disabled={security.processing}>{twoFactorEnabled ? t('Disable authenticator') : twoFactorSetup ? t('Confirm authenticator') : t('Set up authenticator')}</Button>
                </form>
            </CardContent></Card>
        </AuthenticatedLayout>
    );
}
