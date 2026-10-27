import { Head, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Smartphone, Users, FileText, Star, Copy, QrCode } from 'lucide-react';
import { PieChart as PieChartComponent } from '@/components/charts/PieChart';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { useState, useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import QRCode from 'qrcode';

interface CompanyDashboardProps {
    message: string;
    bookingUrl?: string;
    stats?: {
        totalRequests: number;
        pendingRequests: number;
        completedRequests: number;
        totalIncome: number;
    };
    recentServices?: Array<{
        id: number;
        service_id: string;
        customer_name: string;
        mobile_model: string;
        device_brand: string;
        service_name: string;
        description: string;
        priority: string;
        is_approve: number;
        created_at: string;
    }>;
    chartData?: Array<{
        status: string;
        count: number;
    }>;
    recentPayments?: Array<{
        id: number;
        payment_number: string;
        customer_name: string;
        device_name: string;
        payment_amount: number;
        payment_status: string;
        created_at: string;
    }>;
}

export default function CompanyDashboard({ 
    message, 
    bookingUrl, 
    stats, 
    recentServices, 
    chartData, 
    recentPayments 
}: CompanyDashboardProps) {
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
            breadcrumbs={[{ label: t('Mobile Service Management') }, { label: t('Dashboard') }]}
            pageTitle={t('Mobile Service Dashboard')}
        >
            <Head title={t('Mobile Service Dashboard')} />

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
                                <p className="text-white/80 mb-3 text-lg">{t('Manage your mobile repair services and customer contracts efficiently.')}</p>
                                <p className="text-white/70 mb-4 text-sm">{t('Track repairs, manage inventory, and grow your mobile service business.')}</p>
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
                                <CardTitle className="text-xs font-medium text-blue-700">{t('Total Requests')}</CardTitle>
                                <Smartphone className="h-6 w-6 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-700">{stats.totalRequests}</div>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-r from-green-50 to-green-100 border-green-200 cursor-pointer" onClick={() => router.visit(route('mobile-service-management.pending-service-requests.index'))}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-xs font-medium text-green-700">{t('Pending Requests')}</CardTitle>
                                <Users className="h-6 w-6 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-700">{stats.pendingRequests}</div>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200 cursor-pointer" onClick={() => router.visit(route('mobile-service-management.service-history.index'))}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-xs font-medium text-purple-700">{t('Total Completed')}</CardTitle>
                                <FileText className="h-6 w-6 text-purple-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-purple-700">{stats.completedRequests}</div>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-r from-orange-50 to-orange-100 border-orange-200 cursor-pointer" onClick={() => router.visit(route('mobile-service-management.payments.index'))}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-xs font-medium text-orange-700">{t('Total Income')}</CardTitle>
                                <Star className="h-6 w-6 text-orange-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-orange-700">{formatCurrency(stats.totalIncome)}</div>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>

            {/* Charts & Payments */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {/* Chart */}
                {chartData && (
                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <Users className="h-5 w-5 text-green-600" />
                                {t('Payment Status')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {chartData && chartData.length > 0 ? (
                                <PieChartComponent
                                    data={chartData.map(item => ({
                                        ...item,
                                        name: item.status,
                                        value: item.count,
                                        color: item.status === 'Paid' ? '#10b981' : '#f59e0b'
                                    }))}
                                    dataKey="value"
                                    nameKey="name"
                                    showLabels={true}
                                    showLegend={true}
                                    showTooltip={true}
                                    height={300}
                                />
                            ) : (
                                <div className="h-[300px] flex items-center justify-center text-gray-500">
                                    <p className="text-sm">{t('No payment data available')}</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Payment History */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <Star className="h-5 w-5 text-orange-600" />
                            {t('Recent Payments')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentPayments && recentPayments.length > 0 ? (
                            <div className="space-y-3 max-h-80 overflow-y-auto">
                                {recentPayments.map((payment) => (
                                    <div key={payment.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div className="flex-1">
                                            <h4 className="font-medium text-sm text-gray-900">{payment.payment_number}</h4>
                                            <div className="flex items-center gap-4 mt-1">
                                                <p className="text-xs text-gray-600">{payment.customer_name} - {payment.device_name}</p>
                                                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                    payment.payment_status === 'cleared' ? 'bg-green-100 text-green-800' :
                                                    payment.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                }`}>
                                                    {payment.payment_status === 'cleared' ? t('Cleared') :
                                                     payment.payment_status === 'pending' ? t('Pending') : t('Cancelled')}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm font-semibold text-gray-600">{formatCurrency(payment.payment_amount)}</p>
                                            <p className="text-xs text-gray-500 mt-1">{formatDate(payment.created_at)}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12 text-gray-500">
                                <Star className="h-12 w-12 mx-auto mb-3 opacity-30" />
                                <p className="text-sm font-medium">{t('No recent payments')}</p>
                                <p className="text-xs">{t('Payment history will appear here')}</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Recent Approved Requests */}
            {recentServices && recentServices.length > 0 && (
                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle className="text-lg">{t('Recent Approved Requests')}</CardTitle>
                    </CardHeader>
                    <CardContent>
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
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {recentServices.map((service) => (
                                        <tr key={service.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-700 cursor-pointer" onClick={() => router.get(route('mobile-service-management.service-requests.show', service.id))}>
                                                {service.service_id}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {service.customer_name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div className="font-medium">{service.mobile_model}</div>
                                                <div className="text-xs text-gray-500">{service.device_brand}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                    service.priority === 'emergency' ? 'bg-red-100 text-red-800' :
                                                    service.priority === 'priority' ? 'bg-purple-100 text-purple-800' :
                                                    service.priority=== 'standard' ? 'bg-gray-100 text-gray-800':
                                                    'bg-gray-100 text-gray-800'
                                                }`}>
                                                    {service.priority}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                    service.is_approve 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-yellow-100 text-yellow-800'
                                                }`}>
                                                    {service.is_approve ? t('Approved') : t('Pending')}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            )}


        </AuthenticatedLayout>
    );
}