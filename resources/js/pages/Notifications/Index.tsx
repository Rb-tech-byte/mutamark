import React from 'react';
import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Bell, CheckCheck, RefreshCw, Mail, ExternalLink } from 'lucide-react';
import { formatDate } from '@/utils/helpers';

interface NotificationItem {
    id: number;
    type: string;
    title: string;
    message: string;
    severity: 'info' | 'warning' | 'critical' | 'success';
    link?: string | null;
    read_at?: string | null;
    created_at: string;
}

export default function NotificationsIndex({
    notifications = [],
    unreadCount = 0,
}: {
    notifications: NotificationItem[];
    unreadCount: number;
}) {
    const { t } = useTranslation();

    const severityVariant = (s: string) => {
        if (s === 'critical') return 'destructive';
        if (s === 'warning') return 'default';
        return 'secondary';
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Notifications & Automation') }]}
            pageTitle={t('Notifications & Automation Center')}
        >
            <Head title={t('Notifications & Automation')} />

            <div className="space-y-6">
                <Card>
                    <CardHeader className="flex flex-row flex-wrap items-center justify-between gap-3">
                        <CardTitle className="flex items-center gap-2 text-base">
                            <Bell className="h-5 w-5 text-indigo-600" />
                            <span>{t('System & Automation Alerts')}</span>
                            {unreadCount > 0 && <Badge variant="destructive">{unreadCount} {t('Unread')}</Badge>}
                        </CardTitle>
                        <div className="flex flex-wrap gap-2">
                            <Button size="sm" variant="outline" onClick={() => router.post(route('notifications.scan'), { send_email: false })}>
                                <RefreshCw className="h-4 w-4 mr-1.5" /> {t('Run Automation Scan')}
                            </Button>
                            <Button size="sm" variant="outline" onClick={() => router.post(route('notifications.scan'), { send_email: true })}>
                                <Mail className="h-4 w-4 mr-1.5" /> {t('Scan & Dispatch Emails')}
                            </Button>
                            <Button size="sm" onClick={() => router.post(route('notifications.read-all'))} disabled={unreadCount === 0}>
                                <CheckCheck className="h-4 w-4 mr-1.5" /> {t('Mark All Read')}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        {notifications.length === 0 ? (
                            <div className="py-10 text-center text-muted-foreground text-sm">
                                {t('No alerts yet. Click "Run Automation Scan" to check low stock, due invoices/bills, pending approvals, stock transfers, and cash register closing.')}
                            </div>
                        ) : notifications.map((n) => (
                            <div
                                key={n.id}
                                className={`p-4 rounded-lg border flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${!n.read_at ? 'bg-indigo-50/40 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-800' : 'bg-card'}`}
                            >
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2 flex-wrap">
                                        <Badge variant={severityVariant(n.severity)}>{n.type.replace(/_/g, ' ').toUpperCase()}</Badge>
                                        <span className="font-semibold text-sm">{n.title}</span>
                                        {!n.read_at && <span className="h-2 w-2 rounded-full bg-indigo-600 inline-block" />}
                                    </div>
                                    <p className="text-sm text-muted-foreground">{n.message}</p>
                                    <p className="text-xs text-muted-foreground">{formatDate(n.created_at)}</p>
                                </div>
                                <div className="flex items-center gap-2 shrink-0">
                                    {n.link && (
                                        <Button size="sm" variant="outline" onClick={() => router.visit(n.link!)}>
                                            <ExternalLink className="h-3.5 w-3.5 mr-1" /> {t('Open')}
                                        </Button>
                                    )}
                                    {!n.read_at && (
                                        <Button size="sm" variant="ghost" onClick={() => router.post(route('notifications.read', n.id))}>
                                            {t('Mark Read')}
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}

