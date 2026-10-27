import { Head, usePage, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from '@/components/ui/badge';
import { Download } from "lucide-react";
import { formatDate, formatCurrency } from '@/utils/helpers';

interface Payment {
    id: number;
    payment_number: string;
    payment_date: string;
    payment_amount: string;
    service_price: string;
    repair_charge?: string;
    tax_amount?: string;
    payment_type?: string;
    payment_status: string;
    description?: string;
    extra_charges?: Array<{text: string; price: string}>;
    mobile_service_request: {
        id: number;
        service_id: string;
        customer_name: string;
        email?: string;
        mobile_no?: string;
        description?: string;
    };
    bank_account?: {
        id: number;
        account_name: string;
        account_number: string;
        bank_name: string;
    };
    created_at: string;
    updated_at: string;
}

interface PageProps {
    payment: Payment;
    auth: any;
}

export default function View() {
    const { t } = useTranslation();
    const { payment, auth } = usePage<PageProps>().props;

    const getStatusBadge = (status: string) => {
        const statusMap = {
            'cleared': { label: t('Cleared'), class: '!bg-green-100 !text-green-800' },
            'pending': { label: t('Pending'), class: '!bg-yellow-100 !text-yellow-800' },
            'cancelled': { label: t('Cancelled'), class: '!bg-red-100 !text-red-800' },
        };
        const statusInfo = statusMap[status as keyof typeof statusMap] || { label: status, class: '!bg-gray-100 !text-gray-800' };
        return <Badge className={statusInfo.class}>{statusInfo.label}</Badge>;
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Mobile Service Management'), url: route('mobile-service.index') },
                { label: t('Payments'), url: route('mobile-service-management.payments.index') },
                { label: t('Payment Detail') }
            ]}
            pageTitle={`${t('Payment')} #${payment.payment_number}`}
        >
            <Head title={`${t('Payment')} #${payment.payment_number}`} />

            <div className="space-y-6">
                {/* Payment Information */}
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Payment Information')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Payment Number')}</label>
                                <p className="mt-1 text-sm font-mono bg-blue-50 px-2 py-1 rounded">{payment.payment_number}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Payment Date')}</label>
                                <p className="mt-1 text-sm">{formatDate(payment.payment_date)}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Payment Type')}</label>
                                <p className="mt-1 text-sm capitalize">{payment.payment_type || 'Cash'}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Status')}</label>
                                <div className="mt-1">{getStatusBadge(payment.payment_status)}</div>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Service ID')}</label>
                                <p className="mt-1 text-sm font-mono">{payment.mobile_service_request.service_id}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">{t('Total Amount')}</label>
                                <p className="mt-1 text-sm font-semibold text-blue-600">{formatCurrency(payment.payment_amount)}</p>
                            </div>
                        </div>

                        <div className="mt-6 p-3 bg-blue-50 rounded">
                            <div className="flex justify-between items-center">
                                <div>
                                    {auth.user?.permissions?.includes('print-mobile-service-payments') && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => {
                                                const printUrl = route('mobile-service-management.payments.print', payment.id) + '?download=pdf';
                                                window.open(printUrl, '_blank');
                                            }}
                                        >
                                            <Download className="h-4 w-4 mr-2" />
                                            {t('Download PDF')}
                                        </Button>
                                    )}
                                </div>
                                <div className="text-right">
                                    <div className="text-lg font-bold text-blue-600">{formatCurrency(payment.payment_amount)}</div>
                                    <div className="text-sm text-gray-500">{t('Total Amount')}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Customer Information */}
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Customer Information')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label className="text-sm font-medium text-gray-500">{t('Customer Name')}</label>
                                    <p className="mt-1 text-sm">{payment.mobile_service_request.customer_name}</p>
                                </div>
                                {payment.mobile_service_request.email && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">{t('Email')}</label>
                                        <p className="mt-1 text-sm">{payment.mobile_service_request.email}</p>
                                    </div>
                                )}
                            {payment.mobile_service_request.mobile_no && (
                                <div>
                                    <label className="text-sm font-medium text-gray-500">{t('Mobile Number')}</label>
                                    <p className="mt-1 text-sm">{payment.mobile_service_request.mobile_no}</p>
                                </div>
                            )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Amount Breakdown */}
                <Card>
                    <CardHeader>
                        <CardTitle>{t('Amount Breakdown')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            <div className="flex justify-between items-center">
                                <span className="text-sm text-gray-600">{t('Service Price')}</span>
                                <span className="text-sm font-medium">{formatCurrency(payment.service_price)}</span>
                            </div>
                            {payment.repair_charge && parseFloat(payment.repair_charge) > 0 && (
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">{t('Repair Charge')}</span>
                                    <span className="text-sm font-medium">{formatCurrency(payment.repair_charge)}</span>
                                </div>
                            )}
                            {payment.tax_amount && parseFloat(payment.tax_amount) > 0 && (
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">{t('Tax Amount')}</span>
                                    <span className="text-sm font-medium">{formatCurrency(payment.tax_amount)}</span>
                                </div>
                            )}
                            {payment.extra_charges && payment.extra_charges.length > 0 && (
                                payment.extra_charges.map((charge, index) => (
                                    <div key={index} className="flex justify-between items-center">
                                        <span className="text-sm text-gray-600">{charge.text}</span>
                                        <span className="text-sm font-medium">{formatCurrency(charge.price)}</span>
                                    </div>
                                ))
                            )}
                            <div className="border-t pt-3 flex justify-between items-center">
                                <span className="font-semibold">{t('Total Amount')}</span>
                                <span className="font-bold text-blue-600">{formatCurrency(payment.payment_amount)}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Description */}
                {payment.description && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Description')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm leading-relaxed">{payment.description}</p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
