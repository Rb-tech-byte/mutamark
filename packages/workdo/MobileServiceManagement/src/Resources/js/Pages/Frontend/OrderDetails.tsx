import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import Layout from '../../Components/Frontend/Layout';
import { Phone, Wrench, Check, Star, MessageCircle, Mail, Search, Hash, Circle, CheckCircle } from 'lucide-react';
import SocialLinks from '@/components/SocialLinks';
import { formatDate, formatCurrency } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';

interface OrderDetailsProps {
    serviceRequest?: any;
    allTrackingStatuses?: any[];
    serviceCost?: number;
    totalPayments?: number;
    paymentStatus?: string;
}

const OrderDetails = ({ serviceRequest, allTrackingStatuses = [], serviceCost = 0, totalPayments = 0, paymentStatus='' }: OrderDetailsProps) => {
    const pageProps = usePage().props;
    const { t } = useTranslation();
    const { url } = usePage();
    const pathParts = url ? url.split('/') : [];
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;
   
    if (!serviceRequest) {
        return (
            <Layout title="Order Details">
                <section className="py-12 bg-gray-50">
                    <div className="container mx-auto px-4 text-center">
                        <h2 className="text-2xl font-bold mb-4">{t('Service Request Not Found')}</h2>
                        <p className="text-gray-600 mb-6">{t('Please check your service ID and email address.')}</p>
                        <Link href="/mobile-service" className="inline-flex items-center px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                            {t('Back to Home')}
                        </Link>
                    </div>
                </section>
            </Layout>
        );
    }

    const trackingSteps = allTrackingStatuses.map((status) => {
        const isActive = serviceRequest.tracking_status_log && serviceRequest.tracking_status_log.includes(status.id);
        return {
            step: status.status_name,
            date: isActive ? formatDate(serviceRequest.created_at, pageProps) : null,
            note: status.description || '',
            active: isActive,
            iconName: status.icon_name
        };
    });

    return (
        <Layout title="Order Details">
            <section className="relative" style={{ backgroundColor: '#33312e', padding: '4rem 0' }}>
                <div className="container mx-auto px-4">
                    <div className="text-center text-white">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold mb-6 border border-gray-200" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Order Details')}
                        </span>
                        <h2 className="text-4xl font-bold mb-4">
                            {t('Service Request')} <span style={{ color: '#c77d1f' }}>#{serviceRequest.service_id}</span>
                        </h2>
                        <nav className="flex justify-center items-center text-sm">
                            <Link href={route('mobile-service.frontend.index', { userSlug })}>{t('Home')}</Link>
                            <span className="mx-2">›</span>
                            <span>{t('Order Details')}</span>
                        </nav>
                    </div>
                </div>
            </section>

            <section className="py-12 bg-gray-50">
                <div className="container mx-auto px-4">
                    <div className="flex flex-wrap items-center justify-between bg-white p-4 mb-6 rounded-lg border">
                        <div>
                            <h2 className="text-2xl font-bold mb-1">{t('Service')} #{serviceRequest.service_id}</h2>
                            <p className="text-gray-600 text-sm">{formatDate(serviceRequest.created_at, pageProps)}</p>
                        </div>
                        {serviceRequest.tracking_status && (
                            <span className="border rounded-lg px-4 py-2 text-sm font-medium" style={{ borderColor: '#c77d1f', color: '#c77d1f' }}>
                                <SocialLinks 
                                    icon={serviceRequest.tracking_status.icon_name} 
                                    className="w-4 h-4 me-1 inline" 
                                    style={{ color: '#c77d1f' }}
                                />
                                {serviceRequest.tracking_status.status_name}
                            </span>
                        )}
                    </div>

                    <div className="grid lg:grid-cols-4 gap-6">
                        {/* Tracking */}
                        <div className="lg:col-span-1">
                            <div className="bg-white p-6 rounded-lg border">
                                <h2 className="text-lg font-bold mb-4 border-b-2 pb-2" style={{ color: '#33312e', borderColor: '#c77d1f' }}>{t('Tracking')}</h2>
                                <div className="space-y-4">
                                    {trackingSteps.map((item, index) => (
                                        <div key={index} className={`rounded-lg border p-4 ${item.active ? 'active' : ''}`}>
                                            <div className="text-gray-900 font-semibold text-sm" style={{ color: item.active ? '#c77d1f' : '#6b7280' }}>
                                                {item.active ? (
                                                    <Check className="w-4 h-4 me-2 inline" style={{ color: '#c77d1f' }} />
                                                ) : item.iconName ? (
                                                    <SocialLinks 
                                                        icon={item.iconName} 
                                                        className="w-4 h-4 me-2 inline" 
                                                        style={{ color: '#6b7280' }} 
                                                    />
                                                ) : (
                                                    <Check className="w-4 h-4 me-2 inline" style={{ color: '#6b7280' }} />
                                                )}
                                                {item.step}
                                            </div>
                                            {item.date && (
                                                <div className="text-xs text-black mt-1">{item.date}</div>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Main Details */}
                        <div className="lg:col-span-2">
                            {/* Device Card */}
                            <div className="bg-white p-6 mb-6 border rounded-lg">
                                <div className="flex items-start gap-4">
                                    <div className="w-16 h-16 rounded-lg text-white flex items-center justify-center text-2xl" style={{ backgroundColor: '#c77d1f' }}>
                                        <Wrench className="w-8 h-8" />

                                    </div>
                                    <div className="flex-1">
                                        <h3 className="text-xl font-bold mb-2" style={{ color: '#33312e' }}>{serviceRequest.mobile_model}</h3>
                                        <div className="grid grid-cols-2 gap-2 text-sm">
                                            <div><span className="text-gray-600">{t('Brand')}:</span> <span className="font-medium">{ serviceRequest.device_brand?.name || ''}</span></div>
                                            <div><span className="text-gray-600">{t('Service')}:</span> <span className="font-medium">{serviceRequest.mobile_service?.name || serviceRequest.service?.name || ''}</span></div>
                                            <div><span className="text-gray-600">{t('Priority')}:</span> <span className="font-medium">{t(serviceRequest.priority)}</span></div>
                                            <div><span className="text-gray-600">{t('Issue')}:</span> <span className="font-medium text-red-600">{serviceRequest.description}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Notes */}
                            <div className="bg-white p-6 border rounded-lg">
                                <h3 className="text-lg font-bold mb-3" style={{ color: '#33312e' }}>{t('Special Instructions')}</h3>
                                <div className="bg-gray-50 p-3 border-l-4 rounded-lg" style={{ borderColor: '#33312e' }}>
                                    <p className="text-gray-700">{serviceRequest.special_requests || t('No special instructions provided.')}</p>
                                </div>
                            </div>
                        </div>

                        {/* Customer & Payment */}
                        <div className="lg:col-span-1">
                            {/* Customer Info */}
                            <div className="bg-white mb-6 p-6 border rounded-lg">
                                <h3 className="text-lg font-bold mb-4 border-b-2 pb-2" style={{ color: '#33312e', borderColor: '#c77d1f' }}>{t('Customer')}</h3>
                                <div className="space-y-3">
                                    <div>
                                        <div className="font-bold text-gray-800">{serviceRequest.customer_name}</div>
                                        <div className="text-sm text-gray-600">{serviceRequest.address}</div>
                                    </div>
                                    <div className="pt-2 border-t border-gray-200">
                                        <div className="text-sm text-gray-600 mb-3">
                                            <Phone className="w-4 h-4 me-2 fill-current inline" style={{ color: '#c77d1f' }} />{serviceRequest.mobile_no}
                                        </div>
                                        <div className="text-sm text-gray-600">
                                            <Mail className="w-4 h-4 me-2 inline" style={{ color: '#c77d1f' }} />{serviceRequest.email}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Cost Summary */}
                            <div className="bg-white p-6 border rounded-lg">
                                <h3 className="text-lg font-bold mb-4 border-b-2 pb-2" style={{ color: '#33312e', borderColor: '#c77d1f' }}>{t('Cost')}</h3>
                                <div className="space-y-2">
                                    <div className="flex justify-between text-sm">
                                        <span className="text-gray-600">{t('Service Cost')}</span>
                                        <span className="font-medium">{formatCurrency(serviceCost || 0, pageProps)}</span>
                                    </div>
                                    <div className="border-t border-gray-200 pt-2 mt-3">
                                        <div className="flex justify-between">
                                            <span className="font-bold text-gray-800">{t('Total')}</span>
                                            <span className="font-bold text-lg" style={{ color: '#c77d1f' }}>{formatCurrency(totalPayments || 0, pageProps)}</span>
                                        </div>
                                    </div>
                                </div>
                                <div className="mt-4 pt-4 border-t border-gray-200">
                                    <div className="text-center">
                                        <div className="text-sm text-gray-600 mb-1">{t('Payment Status')}</div>
                                        <div className="font-bold" style={{ color: '#33312e' }}>
                                            <CheckCircle className="w-4 h-4 me-1 inline" />{paymentStatus || t('Pending')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </Layout>
    );
};

export default OrderDetails;