import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { 
    ShieldAlert, 
    ArrowLeft, 
    LayoutDashboard, 
    Lock, 
    LogOut, 
    UserX, 
    HelpCircle,
    Building2,
    Shield
} from 'lucide-react';
import { BrandProvider } from '@/contexts/brand-context';
import ApplicationLogo from '@/components/application-logo';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { PageProps } from '@/types';

interface ErrorProps {
    status?: number;
    title?: string;
    message?: string;
}

export default function ForbiddenError({
    status = 403,
    title,
    message,
}: ErrorProps) {
    const { t } = useTranslation();
    const { auth } = usePage<PageProps>().props as any;

    const displayTitle = title || t('Access Restricted / Unauthorized Action');
    const displayMessage = message || t('You do not have the required permissions to access this enterprise module or perform this action.');
    const userRoles = Array.isArray(auth?.user?.roles) ? auth.user.roles.join(', ') : 'Staff';

    return (
        <BrandProvider>
            <Head title={`${status} - ${displayTitle}`} />

            <div className="min-h-screen bg-slate-50 dark:bg-slate-950 text-foreground flex flex-col justify-between relative overflow-hidden">
                {/* Ambient Decorative Background */}
                <div 
                    className="absolute inset-0 opacity-20 dark:opacity-10 pointer-events-none" 
                    style={{
                        backgroundImage: 'radial-gradient(circle, #94a3b8 1.5px, transparent 1px)',
                        backgroundSize: '28px 28px',
                    }} 
                />
                <div className="absolute top-1/4 -left-20 w-80 h-80 bg-rose-500/10 rounded-full blur-3xl pointer-events-none" />
                <div className="absolute bottom-1/4 -right-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none" />

                {/* Minimal Header */}
                <header className="border-b bg-white/80 dark:bg-slate-900/80 backdrop-blur sticky top-0 z-50">
                    <div className="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                        <Link href="/dashboard" className="flex items-center gap-3">
                            <ApplicationLogo className="w-8 h-8 fill-current text-primary" />
                            <div>
                                <span className="font-bold text-base tracking-tight block leading-none">Mutamark ERP</span>
                                <span className="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">
                                    Enterprise Security Engine
                                </span>
                            </div>
                        </Link>

                        <div className="flex items-center gap-2">
                            <Button variant="outline" size="sm" asChild>
                                <Link href="/dashboard" className="gap-2 text-xs">
                                    <LayoutDashboard className="w-3.5 h-3.5" />
                                    <span>{t('Dashboard')}</span>
                                </Link>
                            </Button>
                        </div>
                    </div>
                </header>

                {/* Main Content Card */}
                <main className="max-w-2xl mx-auto px-4 py-12 flex-1 w-full flex items-center justify-center relative z-10">
                    <div className="w-full bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xl p-8 sm:p-10 text-center space-y-6">
                        
                        {/* Status Icon */}
                        <div className="mx-auto w-20 h-20 rounded-2xl bg-gradient-to-br from-rose-100 to-rose-50 dark:from-rose-950/60 dark:to-rose-900/30 border border-rose-200 dark:border-rose-800/60 flex items-center justify-center shadow-lg shadow-rose-500/10">
                            <ShieldAlert className="w-10 h-10 text-rose-600 dark:text-rose-400" />
                        </div>

                        {/* Title & Badge */}
                        <div className="space-y-2">
                            <div className="flex items-center justify-center gap-2">
                                <Badge variant="destructive" className="font-mono text-xs font-bold uppercase tracking-wider px-2.5 py-0.5">
                                    HTTP {status}
                                </Badge>
                                <Badge variant="outline" className="border-slate-200 dark:border-slate-700 text-xs text-muted-foreground">
                                    Role-Based Access Control
                                </Badge>
                            </div>

                            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                                {displayTitle}
                            </h1>

                            <p className="text-sm text-muted-foreground max-w-lg mx-auto leading-relaxed">
                                {displayMessage}
                            </p>
                        </div>

                        {/* User Identity & Audit Callout */}
                        {auth?.user && (
                            <div className="bg-slate-50 dark:bg-slate-950/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 text-xs text-left space-y-2">
                                <div className="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                                    <span className="text-muted-foreground font-medium flex items-center gap-1.5">
                                        <Lock className="w-3.5 h-3.5 text-primary" />
                                        Current Session
                                    </span>
                                    <span className="font-mono text-[11px] text-muted-foreground">
                                        UID #{auth.user.id}
                                    </span>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                    <div>
                                        <span className="text-muted-foreground block">Authenticated User:</span>
                                        <strong className="text-foreground">{auth.user.name}</strong>
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground block">Assigned Role(s):</span>
                                        <span className="font-mono text-primary font-semibold">{userRoles}</span>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Explanatory Help */}
                        <p className="text-xs text-muted-foreground leading-relaxed">
                            💡 If your operational duties require access to this section, please contact your Mutamark System Administrator or Department Lead to grant the corresponding permission profile.
                        </p>

                        {/* Action Buttons */}
                        <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                            <Button 
                                onClick={() => window.history.back()} 
                                variant="outline" 
                                className="w-full sm:w-auto gap-2 h-10 px-5 text-xs font-semibold"
                            >
                                <ArrowLeft className="w-4 h-4" />
                                {t('Previous Screen')}
                            </Button>

                            <Button 
                                asChild 
                                className="w-full sm:w-auto gap-2 h-10 px-6 text-xs font-semibold shadow-sm"
                            >
                                <Link href="/dashboard">
                                    <LayoutDashboard className="w-4 h-4" />
                                    {t('Return to Dashboard')}
                                </Link>
                            </Button>

                            <Button 
                                asChild 
                                variant="ghost" 
                                className="w-full sm:w-auto gap-2 h-10 px-4 text-xs text-muted-foreground hover:text-foreground"
                            >
                                <Link href="/login">
                                    <LogOut className="w-4 h-4" />
                                    {t('Switch Profile')}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </main>

                {/* Footer */}
                <footer className="border-t bg-white/60 dark:bg-slate-900/60 py-4 text-center text-xs text-muted-foreground">
                    <div className="max-w-4xl mx-auto px-4 space-y-1">
                        <p>Mutamark Enterprises Ltd • Enterprise Security Architecture</p>
                        <p className="text-[11px] opacity-75">Powered by Wolinet Technology Ltd (+255 747 003 357)</p>
                    </div>
                </footer>
            </div>
        </BrandProvider>
    );
}
