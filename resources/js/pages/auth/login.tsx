import { FormEventHandler, useEffect, useState } from "react";
import AuthLayout from "@/layouts/auth-layout";
import { Head, Link, useForm } from "@inertiajs/react";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import InputError from "@/components/ui/input-error";
import { Checkbox } from "@/components/ui/checkbox";
import { Badge } from "@/components/ui/badge";
import {
    ShieldCheck,
    Building2,
    Calculator,
    Boxes,
    TrendingUp,
    Users,
    ShoppingBag,
    Factory,
    Zap,
    CheckCircle2,
    KeyRound,
    Sparkles,
    ArrowRight
} from "lucide-react";

import { useTranslation } from 'react-i18next';
import { useFormFields } from '@/hooks/useFormFields';
import { usePageButtons } from '@/hooks/usePageButtons';

interface DemoAccount {
    category: 'management' | 'operations' | 'portals';
    name: string;
    role: string;
    title: string;
    email: string;
    password: string;
    badge: string;
    badgeColor: string;
    borderColor: string;
    scope: string;
    icon: any;
}

const DEMO_ACCOUNTS: DemoAccount[] = [
    {
        category: 'management',
        name: 'Super Administrator',
        role: 'Super Admin',
        title: 'System & Multi-Tenant Superadmin',
        email: 'superadmin@example.com',
        password: '1234',
        badge: 'Platform Root',
        badgeColor: 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 border-purple-200 dark:border-purple-800',
        borderColor: 'hover:border-purple-400 dark:hover:border-purple-500',
        scope: 'Multi-tenant administration, package subscriptions, domain setups & global system controls.',
        icon: ShieldCheck,
    },
    {
        category: 'management',
        name: 'Mutamark Enterprises Ltd',
        role: 'Company Admin',
        title: 'Executive Managing Director',
        email: 'company@example.com',
        password: '1234',
        badge: 'Enterprise Owner',
        badgeColor: 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        borderColor: 'hover:border-blue-400 dark:hover:border-blue-500',
        scope: 'Enterprise operations, master dashboards, commercial KPIs & full corporate governance.',
        icon: Building2,
    },
    {
        category: 'operations',
        name: 'Amina Salum Kibona',
        role: 'Finance & Tax Manager',
        title: 'Head of Finance & Tax Compliance',
        email: 'amina.kibona@mutamark.co.tz',
        password: '1234',
        badge: 'TRA Fiscal & GL',
        badgeColor: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
        borderColor: 'hover:border-emerald-400 dark:hover:border-emerald-500',
        scope: 'TRA EFDMS fiscalization, 3-way matching, chart of accounts, bank reconciliations & GL.',
        icon: Calculator,
    },
    {
        category: 'operations',
        name: 'Baraka Joseph Mwangi',
        role: 'Logistics & Warehouse Manager',
        title: 'Logistics & Supply Chain Lead',
        email: 'baraka.mwangi@mutamark.co.tz',
        password: '1234',
        badge: 'WMS & Supply Chain',
        badgeColor: 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        borderColor: 'hover:border-amber-400 dark:hover:border-amber-500',
        scope: 'Multi-warehouse transfers, bin tracking, goods receipt notes (GRN), delivery notes & serial numbers.',
        icon: Boxes,
    },
    {
        category: 'operations',
        name: 'Zawadi Emmanuel Mushi',
        role: 'Commercial Sales Executive',
        title: 'Commercial Sales & B2B CRM',
        email: 'zawadi.mushi@mutamark.co.tz',
        password: '1234',
        badge: 'Wholesale & B2B',
        badgeColor: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950/70 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
        borderColor: 'hover:border-cyan-400 dark:hover:border-cyan-500',
        scope: 'Wholesale quotations, sales orders, proforma invoicing, customer credit limits & dispatch follow-up.',
        icon: TrendingUp,
    },
    {
        category: 'operations',
        name: 'Juma Bakari Mwalimu',
        role: 'HR & Statutory Payroll',
        title: 'HR & Statutory Payroll Specialist',
        email: 'juma.mwalimu@mutamark.co.tz',
        password: '1234',
        badge: 'TZ Statutory HR',
        badgeColor: 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        borderColor: 'hover:border-rose-400 dark:hover:border-rose-500',
        scope: 'Tanzanian payroll compliance (PAYE, NSSF, WCF, SDL), biometric attendance & employee records.',
        icon: Users,
    },
    {
        category: 'portals',
        name: 'Safari Holdings Ltd',
        role: 'Client Portal',
        title: 'Corporate B2B Customer Account',
        email: 'finance@safariholdings.co.tz',
        password: '1234',
        badge: 'Client Portal',
        badgeColor: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/70 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
        borderColor: 'hover:border-indigo-400 dark:hover:border-indigo-500',
        scope: 'B2B customer self-service: place orders, view statements, download invoices & track deliveries.',
        icon: ShoppingBag,
    },
    {
        category: 'portals',
        name: 'Dar Industrial Hardware Supplies',
        role: 'Vendor Portal',
        title: 'Authorized Vendor & Supplier Account',
        email: 'sales@darindustrial.co.tz',
        password: '1234',
        badge: 'Supplier Portal',
        badgeColor: 'bg-teal-100 text-teal-800 dark:bg-teal-950/70 dark:text-teal-300 border-teal-200 dark:border-teal-800',
        borderColor: 'hover:border-teal-400 dark:hover:border-teal-500',
        scope: 'B2B vendor portal: view purchase orders, acknowledge orders, submit bills & monitor receipts.',
        icon: Factory,
    },
];

export default function Login({
    status,
    canResetPassword,
    enableRegistration,
}: {
    status?: string;
    canResetPassword: boolean;
    enableRegistration?: boolean;
}) {
    const { t } = useTranslation();
    const [selectedCategory, setSelectedCategory] = useState<'all' | 'management' | 'operations' | 'portals'>('all');
    const [loadedAccount, setLoadedAccount] = useState<DemoAccount | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        two_factor_code: "",
        remember: false,
        recaptcha_token: null,
    });

    const formFields = useFormFields('getReCaptchFields', data, setData, errors, 'create', t);
    const loginButtons = usePageButtons('getLoginButtons', { t, isLoading: processing });

    useEffect(() => {
        return () => {
            reset("password");
        };
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route("login"));
    };

    const handleSelectDemo = (account: DemoAccount) => {
        setData((prev) => ({
            ...prev,
            email: account.email,
            password: account.password,
            two_factor_code: "",
        }));
        setLoadedAccount(account);
    };

    const filteredAccounts = selectedCategory === 'all'
        ? DEMO_ACCOUNTS
        : DEMO_ACCOUNTS.filter((acc) => acc.category === selectedCategory);

    return (
        <AuthLayout
            title={t('Mutamark Enterprise ERP Portal')}
            description={t('Sign in to access fiscal accounting, supply chain logistics, and business operations.')}
            maxWidth="max-w-6xl"
        >
            <Head title={t('Log in')} />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950/40 p-3 rounded-lg border border-green-200 dark:border-green-800">
                    {status}
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {/* Left Column: Login Form */}
                <div className="lg:col-span-5 order-2 lg:order-1 flex flex-col justify-between bg-slate-50/70 dark:bg-slate-900/60 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm">
                    <div className="space-y-4">
                        <div className="border-b border-slate-200 dark:border-slate-700/80 pb-3">
                            <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <KeyRound className="w-4 h-4 text-primary" />
                                {t('Account Credentials')}
                            </h2>
                            <p className="text-xs text-muted-foreground mt-0.5">
                                {t('Enter your assigned credentials or choose a demo profile.')}
                            </p>
                        </div>

                        {loadedAccount && (
                            <div className="bg-primary/10 border border-primary/30 rounded-xl p-3 flex items-center justify-between text-xs transition-all animate-in fade-in">
                                <div className="flex items-center gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-primary shrink-0" />
                                    <div>
                                        <span className="font-semibold text-slate-900 dark:text-slate-100">{loadedAccount.name}</span>
                                        <span className="text-muted-foreground block text-[11px]">{loadedAccount.role} ({loadedAccount.email})</span>
                                    </div>
                                </div>
                                <span className="bg-primary text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                    Ready
                                </span>
                            </div>
                        )}

                        <form onSubmit={submit} className="flex flex-col gap-4">
                            <div className="grid gap-1.5">
                                <Label htmlFor="two-factor-code" className="text-xs text-muted-foreground font-normal">
                                    {t('2FA / Recovery Code (if configured)')}
                                </Label>
                                <Input
                                    id="two-factor-code"
                                    autoComplete="one-time-code"
                                    value={data.two_factor_code}
                                    onChange={(event) => setData('two_factor_code', event.target.value)}
                                    maxLength={32}
                                    className="h-9 text-xs"
                                    placeholder="Optional 6-digit code"
                                />
                                <InputError message={errors.two_factor_code} />
                            </div>

                            <div className="grid gap-1.5">
                                <Label htmlFor="email" className="text-xs font-medium">
                                    {t('Corporate Email Address')}
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="name@mutamark.co.tz"
                                    className="h-10"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-1.5">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="text-xs font-medium">
                                        {t('Password')}
                                    </Label>
                                    {canResetPassword && (
                                        <Link
                                            href={route('password.request')}
                                            className="text-xs text-primary hover:underline"
                                            tabIndex={5}
                                        >
                                            {t('Forgot password?')}
                                        </Link>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder={t('Enter account password')}
                                    className="h-10"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-2 pt-1">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onCheckedChange={(checked) => setData('remember', !!checked)}
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember" className="text-xs font-normal text-muted-foreground cursor-pointer">
                                    {t('Remember this device')}
                                </Label>
                            </div>

                            {formFields.map((field) => (
                                <div key={field.id}>{field.component}</div>
                            ))}

                            <Button
                                type="submit"
                                className="mt-2 w-full h-11 text-sm font-semibold shadow-md gap-2"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing ? (
                                    t('Signing in...')
                                ) : (
                                    <>
                                        {t('Sign In to ERP Portal')}
                                        <ArrowRight className="w-4 h-4 ml-1" />
                                    </>
                                )}
                            </Button>

                            {loginButtons.length > 0 && (
                                <div className="space-y-2 pt-2">
                                    <div className="relative">
                                        <div className="absolute inset-0 flex items-center">
                                            <span className="w-full border-t border-slate-200 dark:border-slate-700" />
                                        </div>
                                        <div className="relative flex justify-center text-xs uppercase">
                                            <span className="bg-slate-50 dark:bg-slate-900 px-2 text-muted-foreground text-[10px]">
                                                {t('Or continue with')}
                                            </span>
                                        </div>
                                    </div>
                                    {loginButtons.map((button) => (
                                        <div key={button.id}>{button.component}</div>
                                    ))}
                                </div>
                            )}
                        </form>
                    </div>

                    {enableRegistration && (
                        <div className="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700/80 text-center text-xs text-muted-foreground">
                            {t("Don't have an enterprise account?")}{' '}
                            <Link href={route('register')} tabIndex={6} className="text-primary font-medium hover:underline">
                                {t('Request onboarding')}
                            </Link>
                        </div>
                    )}
                </div>

                {/* Right Column: Demo Evaluation Credentials Hub */}
                <div className="lg:col-span-7 order-1 lg:order-2 flex flex-col gap-4">
                    <div className="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border border-primary/20 rounded-2xl p-4 sm:p-5">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <div className="flex items-center gap-2">
                                    <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-primary text-white shadow-sm">
                                        <Zap className="h-4 w-4" />
                                    </span>
                                    <h3 className="font-semibold text-sm sm:text-base text-slate-900 dark:text-slate-100">
                                        {t('Instant Demo & Role Evaluation Hub')}
                                    </h3>
                                </div>
                                <p className="text-xs text-muted-foreground mt-1">
                                    {t('Click any enterprise role below to automatically populate testing credentials.')}
                                </p>
                            </div>
                            <span className="inline-flex items-center gap-1 text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 px-2.5 py-1 rounded-full shrink-0 border border-emerald-200 dark:border-emerald-800">
                                <Sparkles className="w-3 h-3" />
                                Demo Password: <code className="font-mono ml-0.5">1234</code>
                            </span>
                        </div>

                        {/* Category Filter Tabs */}
                        <div className="flex flex-wrap gap-1.5 mt-4 pt-3 border-t border-primary/10">
                            {[
                                { id: 'all', label: t('All Roles (8)') },
                                { id: 'management', label: t('Leadership & Superadmin (2)') },
                                { id: 'operations', label: t('Operations & Finance (4)') },
                                { id: 'portals', label: t('B2B Portals (2)') },
                            ].map((tab) => (
                                <button
                                    key={tab.id}
                                    type="button"
                                    onClick={() => setSelectedCategory(tab.id as any)}
                                    className={`px-3 py-1 text-xs font-medium rounded-lg transition-all ${
                                        selectedCategory === tab.id
                                            ? 'bg-primary text-white shadow-sm font-semibold'
                                            : 'bg-white/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700/60'
                                    }`}
                                >
                                    {tab.label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Role Cards Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[500px] overflow-y-auto pr-1">
                        {filteredAccounts.map((account) => {
                            const IconComponent = account.icon;
                            const isSelected = data.email === account.email;

                            return (
                                <div
                                    key={account.email}
                                    onClick={() => handleSelectDemo(account)}
                                    className={`group cursor-pointer p-3.5 rounded-xl border text-left transition-all duration-200 relative flex flex-col justify-between ${
                                        isSelected
                                            ? 'bg-primary/[0.07] border-primary dark:border-primary shadow-sm ring-1 ring-primary'
                                            : `bg-white dark:bg-slate-800/90 border-slate-200 dark:border-slate-700/70 hover:shadow-md ${account.borderColor}`
                                    }`}
                                >
                                    <div>
                                        <div className="flex items-start justify-between gap-2 mb-2">
                                            <div className="flex items-center gap-2">
                                                <div className={`p-1.5 rounded-lg ${isSelected ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-200 group-hover:bg-primary group-hover:text-white transition-colors'}`}>
                                                    <IconComponent className="w-4 h-4" />
                                                </div>
                                                <div>
                                                    <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight">
                                                        {account.name}
                                                    </h4>
                                                    <span className="text-[11px] font-medium text-muted-foreground block">
                                                        {account.role}
                                                    </span>
                                                </div>
                                            </div>
                                            <Badge variant="outline" className={`text-[10px] px-1.5 py-0 font-semibold border ${account.badgeColor}`}>
                                                {account.badge}
                                            </Badge>
                                        </div>

                                        <p className="text-[11px] text-muted-foreground line-clamp-2 mb-2 leading-relaxed">
                                            {account.scope}
                                        </p>
                                    </div>

                                    <div className="pt-2 border-t border-slate-100 dark:border-slate-700/50 flex items-center justify-between text-[11px]">
                                        <code className="text-[11px] text-slate-600 dark:text-slate-300 font-mono truncate max-w-[170px]" title={account.email}>
                                            {account.email}
                                        </code>
                                        <button
                                            type="button"
                                            className={`text-[11px] font-semibold px-2 py-0.5 rounded transition-all flex items-center gap-1 ${
                                                isSelected
                                                    ? 'bg-primary text-white'
                                                    : 'text-primary bg-primary/10 group-hover:bg-primary group-hover:text-white'
                                            }`}
                                        >
                                            {isSelected ? (
                                                <>
                                                    <CheckCircle2 className="w-3 h-3" />
                                                    {t('Loaded')}
                                                </>
                                            ) : (
                                                <>
                                                    <Zap className="w-3 h-3" />
                                                    {t('Fill')}
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    <div className="flex items-center justify-between text-[11px] text-muted-foreground px-2 pt-1">
                        <span>💡 Tip: Click any role card above to instantly populate login credentials.</span>
                        <span className="font-mono">Mutamark v2.5 Tanzania</span>
                    </div>
                </div>
            </div>
        </AuthLayout>
    );
}

