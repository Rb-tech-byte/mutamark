import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Wrench, CheckCircle, Clock, DollarSign, Copy, QrCode } from 'lucide-react';
import { LineChart } from '@/components/charts/LineChart';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { useState, useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import QRCode from 'qrcode';

interface StaffDashboardProps {
    message: string;
    bookingUrl?: string;
    stats?: {
        assignedRequests: number;
        completedRequests: number;
        pendingRequests: number;
        totalEarnings: number;
    };
    recentRequests?: Array<{
        id: number;
        service_id: string;
        customer_name: string;
        mobile_model: string;
        device_brand: string;
        service_name: string;
        priority: string;
        status: string;
        created_at: string;
    }>;
    performanceChart?: Array<{
        month: string;
        completed: number;
    }>;
    staffInfo?: {
        name: string;
        email: string;
        role: string;
    };
}

export default function StaffDashboard({
    message,
    bookingUrl,
    stats,
    recentRequests,
    performanceChart,
    staffInfo
}: StaffDashboardProps) {
    const { t } = useTranslation();
    const [qrCodeUrl, setQrCodeUrl] = useState('');

    const copyToClipboard = async () => {
        if (bookingUrl) {
            await navigator.clipboard.writeText(bookingUrl);
            toast.success(t('Link copied to clipboard!'));
        }
    };

    useMemo(() => {
        if (bookingUrl) {
            QRCode.toDataURL(bookingUrl)
                .then(setQrCodeUrl)
                .catch(console.error);
        }
    }, [bookingUrl]);

    return (
        <AuthenticatedLayout
            breadcrumbs={[{ label: t('Mobile Service Management') }, { label: t('Staff Dashboard') }]}
            pageTitle={t('Staff Dashboard')}
        >
            <Head title={t('Staff Dashboard')} />

            {/* Welcome Banner & Stats */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {/* Welcome Banner */}
                <div>
                    <div className="bg-gradient-to-r from-primary/90 to-primary/70 rounded-lg p-6 text-white relative overflow-hidden" style={{ minHeight: '200px' }}>
                        {/* Mobile Service Icons */}
                        <div className="absolute inset-0 opacity-25">
                            <svg className="w-full h-full" viewBox="0 0 400 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stopColor="currentColor" stopOpacity="0.3" />
                                        <stop offset="100%" stopColor="currentColor" stopOpacity="0.1" />
                                    </linearGradient>
                                </defs>

                                {/* Phone Icon */}
                                <g transform="translate(60, 40)">
                                    <rect x="8" y="5" width="14" height="20" rx="2" fill="url(#grad1)">
                                        <animate attributeName="opacity" values="0.8;1;0.8" dur="3s" repeatCount="indefinite" />
                                    </rect>
                                    <rect x="10" y="8" width="10" height="12" fill="currentColor" opacity="0.4" />
                                    <circle cx="15" cy="22" r="1" fill="currentColor" opacity="0.6" />
                                </g>

                                {/* Repair Tools */}
                                <g transform="translate(280, 30)">
                                    <circle cx="15" cy="15" r="15" fill="url(#grad1)">
                                        <animate attributeName="r" values="15;18;15" dur="2s" repeatCount="indefinite" />
                                    </circle>
                                    <path d="M10 10 L20 20 M20 10 L10 20" stroke="currentColor" strokeWidth="2" opacity="0.6" />
                                    <circle cx="8" cy="8" r="2" fill="currentColor" opacity="0.5" />
                                    <circle cx="22" cy="22" r="2" fill="currentColor" opacity="0.5" />
                                </g>

                                {/* Circuit Board */}
                                <g transform="translate(150, 120)">
                                    <rect x="5" y="5" width="20" height="20" rx="2" fill="url(#grad1)">
                                        <animate attributeName="opacity" values="0.8;1;0.8" dur="3s" repeatCount="indefinite" />
                                    </rect>
                                    <circle cx="10" cy="10" r="2" fill="currentColor" opacity="0.6" />
                                    <circle cx="20" cy="10" r="2" fill="currentColor" opacity="0.6" />
                                    <circle cx="15" cy="20" r="2" fill="currentColor" opacity="0.6" />
                                    <path d="M10 10 L20 10 M15 10 L15 20" stroke="currentColor" strokeWidth="1" opacity="0.4" />
                                </g>

                                {/* Battery */}
                                <g transform="translate(320, 120)">
                                    <rect x="8" y="12" width="14" height="8" rx="1" fill="url(#grad1)">
                                        <animateTransform attributeName="transform" type="rotate" values="0 15 16;2 15 16;0 15 16;-2 15 16;0 15 16" dur="4s" repeatCount="indefinite" />
                                    </rect>
                                    <rect x="22" y="14" width="2" height="4" fill="currentColor" opacity="0.6" />
                                    <rect x="10" y="14" width="8" height="4" fill="currentColor" opacity="0.3">
                                        <animate attributeName="width" values="8;4;8" dur="2s" repeatCount="indefinite" />
                                    </rect>
                                </g>
                            </svg>
                        </div>
                        <div className="flex items-center justify-between relative z-10 h-full">
                            <div className="flex-1 pr-6">
                                <h2 className="text-3xl font-bold mb-3">{t('Mobile Service Center')}</h2>
                                <p className="text-white/80 mb-3 text-lg">{staffInfo?.role}</p>
                                <p className="text-white/70 mb-4 text-sm">{t('Track your service assignments and performance efficiently.')}</p>
                                {bookingUrl && (
                                    <div className="flex items-center gap-3">
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            onClick={copyToClipboard}
                                            className="bg-white/20 hover:bg-white/30 text-white border-white/30"
                                        >
                                            <Copy className="h-4 w-4 mr-2" />
                                            {t('Copy Link')}
                                        </Button>
                                    </div>
                                )}
                            </div>
                            {bookingUrl && (
                                <div className="bg-white p-6 rounded-lg shadow-lg">
                                    {qrCodeUrl ? (
                                        <img src={qrCodeUrl} alt="QR Code" className="w-36 h-36" />
                                    ) : (
                                        <div className="w-36 h-36 bg-gray-100 rounded flex items-center justify-center">
                                            <QrCode className="h-16 w-16 text-gray-400" />
                                        </div>
                                    )}
                                    <p className="text-sm text-gray-600 mt-3 text-center font-medium">{t('Scan for Service')}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Stats Cards */}
                {stats && (
                    <div className="grid grid-cols-2 gap-4">
                        <Card className="bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-xs font-medium text-blue-700">{t('Assigned Requests')}</CardTitle>
                                <Wrench className="h-6 w-6 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-700">{stats.assignedRequests}</div>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-xs font-medium text-green-700">{t('Completed')}</CardTitle>
                                <CheckCircle className="h-6 w-6 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-700">{stats.completedRequests}</div>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-r from-yellow-50 to-yellow-100 border-yellow-200">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-xs font-medium text-yellow-700">{t('Pending')}</CardTitle>
                                <Clock className="h-6 w-6 text-yellow-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-yellow-700">{stats.pendingRequests}</div>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-xs font-medium text-purple-700">{t('Total Earnings')}</CardTitle>
                                <DollarSign className="h-6 w-6 text-purple-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-purple-700">{formatCurrency(stats.totalEarnings)}</div>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>

            {/* Recent Requests */}
            <div className="mb-6">
                {/* Recent Requests */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <Wrench className="h-5 w-5 text-blue-600" />
                            {t('My Recent Requests')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentRequests && recentRequests.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {t('Service ID')}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {t('Customer')}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {t('Device')}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {t('Priority')}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {t('Status')}
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {t('Date')}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {recentRequests.map((request) => (
                                            <tr key={request.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-700 cursor-pointer"
                                                    onClick={() => router.get(route('mobile-service-management.service-requests.show', request.id))}>
                                                    {request.service_id}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {request.customer_name}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {request.mobile_model}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${request.priority === 'high' ? 'bg-red-100 text-red-800' :
                                                            request.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                                                                'bg-green-100 text-green-800'
                                                        }`}>
                                                        {request.priority}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${request.status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                                        }`}>
                                                        {request.status}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {formatDate(request.created_at)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center py-12 text-gray-500">
                                <Wrench className="h-12 w-12 mx-auto mb-3 opacity-30" />
                                <p className="text-sm font-medium">{t('No assigned requests')}</p>
                                <p className="text-xs">{t('Your assigned service requests will appear here')}</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}