import React, { useState } from 'react';
import { Link, usePage, router, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { useTranslation } from 'react-i18next';
import { getImagePath, formatDate,formatCurrency } from '@/utils/helpers';
import SocialLinks from '@/components/SocialLinks';
import Layout from '../../Components/Frontend/Layout';
import { Phone, Wrench, Check, Star, ChevronRight, ChevronLeft, Mail, Hash, Circle, Smartphone, Heart, Settings, ArrowRight, Calendar } from 'lucide-react';
import { useFormFields } from '@/hooks/useFormFields';

interface Testimonial {
    id: number;
    name: string;
    review: string;
    rating: number;
    created_at: string;
}

interface IndexProps {
    testimonials: Testimonial[];
    reviewStats: {
        totalReviews: number;
        averageRating: number;
        fiveStarReviews: number;
        totalServices: number;
        totalDeviceRepaired: number;
        satisfactionRate: number;
    };
}

const Index = () => {
    const { t } = useTranslation();
    const pageProps = usePage<IndexProps>().props;
    const { mobileServiceSettings, mobileServices, testimonials = [], reviewStats } = pageProps;
    const [currentTestimonial, setCurrentTestimonial] = useState(0);
    const [currentService, setCurrentService] = useState(0);

    const { data: integrationData, setData: setIntegrationData, errors: integrationErrors } = useForm({});
    const integrationFields = useFormFields('getIntegrationFields', integrationData, setIntegrationData, integrationErrors, 'create', t, 'MobileServiceManagement');

    const [trackingData, setTrackingData] = useState({
        serviceId: '',
        email: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showToast, setShowToast] = useState(false);
    const [toastMessage, setToastMessage] = useState('');

    const { totalReviews, averageRating, fiveStarReviews, totalServices, totalDeviceRepaired, satisfactionRate } = reviewStats || { totalReviews: 0, averageRating: 0, fiveStarReviews: 0, totalServices: 0, totalDeviceRepaired: 0, satisfactionRate: 0 };

    const { url } = usePage();
    const pathParts = url.split('/');
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;

    // Get banner settings
    const bannerSettings = mobileServiceSettings?.banner_settings || {};
    const contactInfo = mobileServiceSettings?.contact_info || {};
    const homeSection = mobileServiceSettings?.home_section || {};



    const maxSlides = Math.max(0, testimonials.length - 3);
    const isLastSlide = currentTestimonial >= maxSlides;

    const maxServiceSlides = Math.max(0, mobileServices.length - 3);
    const isLastServiceSlide = currentService >= maxServiceSlides;

    const nextTestimonial = () => {
        if (!isLastSlide) {
            setCurrentTestimonial((prev) => prev + 1);
        }
    };

    const prevTestimonial = () => {
        if (currentTestimonial > 0) {
            setCurrentTestimonial((prev) => prev - 1);
        }
    };

    const nextService = () => {
        if (!isLastServiceSlide) {
            setCurrentService((prev) => prev + 1);
        }
    };

    const prevService = () => {
        if (currentService > 0) {
            setCurrentService((prev) => prev - 1);
        }
    };

    const handleTrackingSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (!trackingData.serviceId.trim()) {
            setToastMessage('Please enter a service ID');
            setShowToast(true);
            setTimeout(() => setShowToast(false), 4000);
            return;
        }

        if (!trackingData.email.trim()) {
            setToastMessage('Please enter your email address');
            setShowToast(true);
            setTimeout(() => setShowToast(false), 4000);
            return;
        }

        setIsSubmitting(true);

        router.post(route('mobile-service.frontend.track-service', userSlug), {
            serviceId: trackingData.serviceId,
            email: trackingData.email
        }, {
            onError: (errors) => {
                const errorMessage = errors.serviceId || errors.email || 'Service request not found';
                setToastMessage(errorMessage);
                setShowToast(true);
                setTimeout(() => setShowToast(false), 4000);
                setIsSubmitting(false);
            },
            onFinish: () => {
                setIsSubmitting(false);
            }
        });
    };

    return (
        <Layout title="Mobile Service Management | Mutamark ERP" activeNav="home">
            {showToast && (
                <div className="fixed top-4 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg" style={{ backgroundColor: '#c77d1f', color: 'white' }}>
                    <span>{toastMessage}</span>
                </div>
            )}
            {/* Hero Section */}
            <section className="pt-10 pb-16 md:py-24 lg:py-28 relative overflow-hidden" style={{ backgroundColor: '#e6ded6' }}>
                <div className="round-blob opacity-10 -z-10 -top-40 -end-40 rotate-animation" style={{ backgroundColor: '#33312e' }}></div>
                <div className="round-blob opacity-10 -z-10 bottom-0 -start-40 rotate-animation" style={{ backgroundColor: '#c77d1f', animationDelay: '3s' }}></div>

                <div className="md:container mx-auto px-4 w-full">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-12 lg:gap-16 items-center">
                        <div className="text-center md:text-start">
                            {bannerSettings.banner_subtitle && (
                                <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold mb-4 md:mb-6 shadow-sm border border-gray-200" style={{ color: '#33312e' }}>
                                    <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                                    {bannerSettings.banner_subtitle}
                                </span>
                            )}
                            {bannerSettings.banner_title && (
                                <h2 className="text-3xl sm:text-4xl md:text-4xl lg:text-5xl xl:text-6xl font-bold title-font mb-4 lg:mb-6 leading-tight" style={{ color: '#33312e' }}>
                                    {bannerSettings.banner_title}
                                </h2>
                            )}
                            {bannerSettings.description && (
                                <p className="lg:text-lg text-gray-700 mb-4 lg:mb-8 max-w-xl mx-auto lg:mx-0">
                                    {bannerSettings.description}
                                </p>
                            )}
                            <div className="flex flex-wrap gap-3 sm:gap-4 justify-center md:justify-start">
                                <Link href={route('mobile-service.frontend.booking', { userSlug })} className="inline-flex items-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5" style={{ backgroundColor: '#c77d1f', color: 'white', border: '1px solid #c77d1f' }}>
                                    <Wrench className="w-4 h-4 me-2" />
                                    <span>{t('Book Repair')}</span>
                                </Link>
                                {contactInfo.contact_phone && (
                                    <a href={`tel:${contactInfo.contact_phone}`} className="inline-flex items-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5" style={{ backgroundColor: 'transparent', color: '#33312e', border: '1px solid #33312e' }}>
                                        <Phone className="w-4 h-4 me-2" />
                                        <span>{contactInfo.contact_phone}</span>
                                    </a>
                                )}
                            </div>

                            <div className="mt-6 md:mt-8 lg:mt-12 flex flex-wrap justify-center md:justify-start gap-3 lg:gap-4">
                                <div className="flex items-center space-x-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                                    <div className="w-3 h-3 rounded-lg flex items-center justify-center" style={{ backgroundColor: '#c77d1f' }}>
                                        <Star className="w-2 h-2 text-white" />
                                    </div>
                                    <span className="text-sm font-medium">{averageRating} ({totalReviews}+ {t('reviews')})</span>
                                </div>
                                <div className="flex items-center space-x-2 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                                    <div className="w-3 h-3 rounded-lg flex items-center justify-center" style={{ backgroundColor: '#c77d1f' }}>
                                        <Check className="w-2 h-2 text-white" />
                                    </div>
                                    <a href="#expertise-section" className="text-sm font-medium hover:text-primary transition-colors">{t('Our Expertise')}</a>
                                </div>
                            </div>
                        </div>

                        <div className="relative flex justify-center">
                            <div className="relative w-full">
                                <div className="absolute inset-0 rounded-2xl -rotate-6 shadow-xl hidden md:block" style={{ backgroundColor: '#33312e' }}></div>
                                    <img
                                        src={bannerSettings.image ? getImagePath(bannerSettings.image) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/banner-image.jpg')}
                                        alt="Technician repairing phone"
                                        className="relative rounded-lg shadow-xl w-full h-auto border-4 border-white"
                                    />
                            </div>
                            {(bannerSettings.image_icon || bannerSettings.image_text) && (
                                <div className="absolute -bottom-6 start-0 text-white px-5 py-2 rounded-lg shadow-lg flex items-center space-x-2" style={{ backgroundColor: '#33312e' }}>
                                    {bannerSettings.image_icon && (
                                        <SocialLinks
                                            icon={bannerSettings.image_icon}
                                            className="w-4 h-4"
                                            style={{ color: '#c77d1f' }}
                                        />
                                    )}
                                    {bannerSettings.image_text && (
                                        <span className="font-medium">{bannerSettings.image_text}</span>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* Order Tracking Section */}
            <section className="py-10 lg:py-16 overflow-hidden" style={{ backgroundColor: '#33312e' }}>
                <div className="md:container mx-auto px-4 w-full">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                        <div className="max-w-2xl mx-auto lg:mx-0">
                            <div className="text-center lg:text-start mb-8">
                                <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase font-semibold mb-4 shadow-sm border border-gray-200" style={{ color: '#33312e' }}>
                                    <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                                    {t('Service Tracking')}
                                </span>
                                <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-white title-font mb-4">{homeSection?.service_tracking_title}</h2>
                                <p className="text-gray-300">{homeSection?.service_tracking_description}</p>
                            </div>

                            <div className="bg-white rounded-lg shadow-lg p-4 md:p-8">
                                <form onSubmit={handleTrackingSubmit} className="space-y-6">
                                    <div className="grid grid-cols-1 gap-3 md:gap-6">
                                        <div>
                                            <label className="block text-sm font-semibold mb-2" style={{ color: '#33312e' }}>{t('Service ID')}</label>
                                            <div className="relative">
                                                <input
                                                    type="text"
                                                    placeholder="Enter service ID"
                                                    className="w-full px-4 py-3 pe-[35px] border border-gray-300 rounded-lg transition-colors"
                                                    value={trackingData.serviceId}
                                                    onChange={(e) => setTrackingData(prev => ({ ...prev, serviceId: e.target.value }))}
                                                    required
                                                />
                                                <Hash className="w-4 h-4 absolute end-3 top-1/2 transform -translate-y-1/2 text-gray-800" />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-semibold mb-2" style={{ color: '#33312e' }}>{t('Email Address')}</label>
                                            <div className="relative">
                                                <input
                                                    type="email"
                                                    placeholder="Enter email address"
                                                    className="w-full px-4 py-3 pe-[35px] border border-gray-300 rounded-lg transition-colors"
                                                    value={trackingData.email}
                                                    onChange={(e) => setTrackingData(prev => ({ ...prev, email: e.target.value }))}
                                                    required
                                                />
                                                <Mail className="w-4 h-4 absolute end-3 top-1/2 transform -translate-y-1/2 text-gray-800" />
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={isSubmitting}
                                        className="w-full inline-flex items-center justify-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 disabled:opacity-50"
                                        style={{ backgroundColor: '#c77d1f', color: 'white', border: '1px solid #c77d1f' }}
                                    >
                                        {isSubmitting ? t('Tracking...') : t('Track My Order')}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 lg:gap-8">
                            <div className="text-center p-4 lg:p-6 bg-white bg-opacity-10 rounded-lg backdrop-blur-sm border border-white border-opacity-20">
                                <div className="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center" style={{ backgroundColor: '#c77d1f' }}>
                                    <Smartphone className="w-6 h-6 text-white" />
                                </div>
                                <div className="text-3xl lg:text-4xl font-bold mb-2 text-white">
                                    <span className="counter" data-target={totalDeviceRepaired}>{totalDeviceRepaired}</span>+
                                </div>
                                <p className="text-sm lg:text-base text-white font-medium">{t('Devices Repaired')}</p>
                            </div>
                            <div className="text-center p-4 lg:p-6 bg-white bg-opacity-10 rounded-lg backdrop-blur-sm border border-white border-opacity-20">
                                <div className="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center" style={{ backgroundColor: '#c77d1f' }}>
                                    <Heart className="w-6 h-6 text-white" />
                                </div>
                                <div className="text-3xl lg:text-4xl font-bold mb-2 text-white">
                                    <span className="counter" data-target={satisfactionRate}>{satisfactionRate}</span>%
                                </div>
                                <p className="text-sm lg:text-base text-white font-medium">{t('Satisfaction Rate')}</p>
                            </div>
                            <div className="text-center p-4 lg:p-6 bg-white bg-opacity-10 rounded-lg backdrop-blur-sm border border-white border-opacity-20">
                                <div className="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center" style={{ backgroundColor: '#c77d1f' }}>
                                    <Star className="w-6 h-6 text-white" />
                                </div>
                                <div className="text-3xl lg:text-4xl font-bold mb-2 text-white">
                                    <span className="counter" data-target={fiveStarReviews}>{fiveStarReviews}</span>+
                                </div>
                                <p className="text-sm lg:text-base text-white font-medium">{t('5-Star Reviews')}</p>
                            </div>
                            <div className="text-center p-4 lg:p-6 bg-white bg-opacity-10 rounded-lg backdrop-blur-sm border border-white border-opacity-20">
                                <div className="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center" style={{ backgroundColor: '#c77d1f' }}>
                                    <Settings className="w-6 h-6 text-white" />
                                </div>
                                <div className="text-3xl lg:text-4xl font-bold mb-2 text-white">
                                    <span className="counter" data-target={totalServices}>{totalServices}</span>+
                                </div>
                                <p className="text-sm lg:text-base text-white font-medium">{t('Total Services')}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Services Section */}
            <section id="expertise-section" className="py-12 lg:py-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="text-center mb-6 md:mb-12">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold mb-4 shadow-sm border border-gray-200 mx-auto" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Our Expertise')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4" style={{ color: '#33312e' }}>
                            {homeSection?.expertise_title}
                        </h2>
                        <p className="lg:text-lg text-gray-600 max-w-3xl mx-auto">
                            {homeSection?.expertise_description}
                        </p>
                    </div>

                    {mobileServices && mobileServices.length > 0 ? (
                        <>
                            <div className="relative">
                                <div className="overflow-hidden">
                                    <div
                                        className="flex transition-transform duration-500 ease-in-out"
                                        style={{ transform: `translateX(-${currentService * (100 / Math.min(mobileServices.length, 3))}%)` }}
                                    >
                                        {mobileServices.map((service, index) => (
                                            <div key={index} className={`flex-shrink-0 px-2 ${mobileServices.length >= 3 ? 'w-1/3' : mobileServices.length === 2 ? 'w-1/2' : 'w-full'}`}>
                                                <div className="h-full flex flex-col group bg-white shadow-md border border-gray-200 rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                                    <div className="relative overflow-hidden">
                                                        <img
                                                            src={service.image ? getImagePath(service.image) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/default.png')}
                                                            alt={service.name}
                                                            className="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-500"
                                                        />
                                                    </div>
                                                    <div className="flex-1 flex flex-col h-full lg:p-6 p-4">
                                                        <div className="flex-1">
                                                            <h3 className="text-xl mb-3 group-hover:text-[#c77d1f] transition-all duration-300">
                                                                <Link href={route('mobile-service.frontend.service-details', { userSlug, id: service.id })}>{service.name}</Link>
                                                            </h3>
                                                            <p className="text-gray-600 mb-4 line-clamp-2">{service.description}</p>
                                                        </div>
                                                        <div className="flex justify-between items-center pt-4 border-t gap-3">
                                                            <span className="text-lg font-bold" style={{ color: '#c77d1f' }}>{t('From')} {formatCurrency(service.sale_price || '0')}</span>
                                                            <Link href={route('mobile-service.frontend.service-details', { userSlug, id: service.id })} className="text-[#c77d1f] hover:text-[#33312e] font-medium transition-all duration-300 flex items-center">
                                                                {t('View Details')}
                                                                <ArrowRight className="ms-1 w-4 h-4" />
                                                            </Link>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                {mobileServices.length > 3 && (
                                    <>
                                        <button
                                            onClick={prevService}
                                            disabled={currentService === 0}
                                            className={`absolute left-4 top-1/2 transform -translate-y-1/2 w-9 h-9 bg-white text-base rounded-md shadow-lg transition-colors flex items-center justify-center ${currentService === 0
                                                ? 'opacity-50 cursor-not-allowed'
                                                : 'hover:text-white'
                                                }`}
                                            style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                        >
                                            <ChevronLeft className="w-4 h-4 mx-2 flex items-center" />
                                        </button>
                                        <button
                                            onClick={nextService}
                                            disabled={isLastServiceSlide}
                                            className={`absolute right-4 top-1/2 transform -translate-y-1/2 w-9 h-9 bg-white text-base rounded-md shadow-lg transition-colors flex items-center justify-center ${isLastServiceSlide
                                                ? 'opacity-50 cursor-not-allowed'
                                                : 'hover:text-white'
                                                }`}
                                            style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                        >
                                            <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                                        </button>
                                    </>
                                )}
                            </div>

                            <div className="mt-6 md:mt-10 text-center">
                                <Link href={route('mobile-service.frontend.services', { userSlug })} className="inline-flex items-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-orange-500 hover:border-orange-500" style={{ backgroundColor: '#33312e', color: 'white', border: '1px solid #33312e' }}>
                                    {t('View All Services')}
                                    <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                </Link>
                            </div>
                        </>
                    ) : (
                        <div className="text-center py-12">
                            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                <Wrench className="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">{t('No Services Available')}</h3>
                            <p className="text-gray-600">{t('Our services will appear here when added.')}</p>
                        </div>
                    )}
                </div>
            </section>

            {/* About Section */}
            <section className="py-12 lg:py-16" style={{ backgroundColor: '#e6ded6' }}>
                <div className="md:container mx-auto px-4 w-full">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
                        <div className="relative">
                            <div className="relative rounded-2xl overflow-hidden shadow-xl">
                                <img src={mobileServiceSettings?.about_section?.about_image ? getImagePath(mobileServiceSettings.about_section.about_image) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/about-section-img.png')} alt="Technician working" className="w-full h-auto" />
                            </div>
                            <div className="absolute -bottom-6 -end-6 text-white px-6 py-4 rounded-lg shadow-lg max-w-xs hidden md:block" style={{ backgroundColor: '#c77d1f' }}>
                                <p className="font-medium text-sm">"{mobileServiceSettings?.about_section?.about_image_text}"</p>
                            </div>
                        </div>

                        <div>
                            <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold mb-4 shadow-sm border border-gray-200" style={{ color: '#33312e' }}>
                                <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                                {t('Who We Are')}
                            </span>
                            <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4" style={{ color: '#33312e' }}>{mobileServiceSettings?.about_section?.about_title}</h2>
                            <p className="text-gray-700 mb-4 md:mb-6">
                                {mobileServiceSettings?.about_section?.about_description}
                            </p>

                            <div className="space-y-4 mb-6 md:mb-8">
                                {(Array.isArray(mobileServiceSettings?.about_section?.about_facilities) ? mobileServiceSettings.about_section.about_facilities : []).map((facility, index) => (
                                    <div key={index} className="flex items-start gap-4">
                                        <div className="flex-shrink-0">
                                            <div className="w-5 h-5 rounded flex items-center justify-center mt-1" style={{ backgroundColor: '#c77d1f' }}>
                                                <Check className="w-3 h-3 text-white" />
                                            </div>
                                        </div>
                                        <div>
                                            <p className="text-gray-700 font-medium">{facility.title}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <Link href={route('mobile-service.frontend.about', { userSlug })} className="inline-flex items-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-orange-500 hover:border-orange-500" style={{ backgroundColor: '#33312e', color: 'white', border: '1px solid #33312e' }}>
                                {t('Our Repair Process')}
                                <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* Testimonials */}
            <section className="py-10 lg:py-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="text-center mb-6 md:mb-10">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold mb-4 shadow-sm border border-gray-200 mx-auto" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Client Testimonials')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4" style={{ color: '#33312e' }}>{homeSection?.testimonial_title}</h2>
                        <p className="lg:text-lg text-gray-600 max-w-3xl mx-auto">
                            {homeSection?.testimonial_description}
                        </p>
                    </div>

                    <div className="relative">
                        <div className="overflow-hidden">
                            <div
                                className="flex transition-transform duration-500 ease-in-out"
                                style={{ transform: `translateX(-${currentTestimonial * (100 / Math.min(testimonials.length, 3))}%)` }}
                            >
                                {testimonials.length > 0 ? testimonials.map((testimonial, index) => {
                                    const initials = testimonial.name.split(' ').map(n => n[0]).join('').toUpperCase();
                                    const reviewDate = formatDate(testimonial.created_at, pageProps);

                                    return (
                                        <div key={testimonial.id} className={`flex-shrink-0 px-2 ${testimonials.length >= 3 ? 'w-1/3' : testimonials.length === 2 ? 'w-1/2' : 'w-full'}`}>
                                            <div className="testimonial-card h-full flex flex-col bg-white rounded-lg p-4 md:p-6 border border-gray-200 transition-shadow">
                                                <div className="testimonial-card-top flex-1">
                                                    <div className="flex mb-4 space-x-1" style={{ color: '#c77d1f' }}>
                                                        {[1, 2, 3, 4, 5].map((star) => (
                                                            <Star key={star} className={`w-4 h-4 ${star <= testimonial.rating ? 'text-yellow-400' : 'text-gray-300'}`} />
                                                        ))}
                                                    </div>
                                                    <p className="text-gray-600 mb-4 relative line-clamp-4">
                                                        "{testimonial.review}"
                                                    </p>
                                                </div>
                                                <div className="flex items-center">
                                                    <div className="w-10 h-10 md:w-12 md:h-12 rounded-full border-2 me-4 flex items-center justify-center" style={{ borderColor: '#33312e', backgroundColor: '#33312e' }}>
                                                        <span className="text-white font-semibold text-sm">{initials}</span>
                                                    </div>
                                                    <div>
                                                        <h4 className="font-bold" style={{ color: '#33312e' }}>{testimonial.name}</h4>
                                                        <p className="text-gray-500 text-sm">{reviewDate}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                }) : (
                                    <div className="w-full text-center py-8">
                                        <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                            <Star className="w-8 h-8 text-gray-400" />
                                        </div>
                                        <h4 className="text-lg font-semibold text-gray-800 mb-2">{t('No Reviews Yet')}</h4>
                                        <p className="text-gray-600">{t('Be the first to review our services!')}</p>
                                    </div>
                                )}
                            </div>
                        </div>
                        {testimonials.length > 3 && testimonials.length > 0 && (
                            <>
                                <button
                                    onClick={prevTestimonial}
                                    disabled={currentTestimonial === 0}
                                    className={`absolute left-4 top-1/2 transform -translate-y-1/2 w-9 h-9 bg-white text-base rounded-md shadow-lg transition-colors flex items-center justify-center ${currentTestimonial === 0
                                        ? 'opacity-50 cursor-not-allowed'
                                        : 'hover:text-white'
                                        }`}
                                    style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                >
                                    <ChevronLeft className="w-4 h-4 mx-2 flex items-center" />
                                </button>
                                <button
                                    onClick={nextTestimonial}
                                    disabled={isLastSlide}
                                    className={`absolute right-4 top-1/2 transform -translate-y-1/2 w-9 h-9 bg-white text-base rounded-md shadow-lg transition-colors flex items-center justify-center ${isLastSlide
                                        ? 'opacity-50 cursor-not-allowed'
                                        : 'hover:text-white'
                                        }`}
                                    style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                >
                                    <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                                </button>
                            </>
                        )}
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="pb-10 lg:pb-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="text-white rounded-2xl p-4 md:p-8 lg:p-10 text-center relative overflow-hidden" style={{ backgroundColor: '#c77d1f' }}>
                        <div className="absolute -top-10 -end-10 w-32 h-32 rounded-full bg-white bg-opacity-10"></div>
                        <div className="absolute -bottom-10 -start-10 w-40 h-40 rounded-full bg-white bg-opacity-10"></div>

                        <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4">{homeSection?.cta_title}</h2>
                        <p className="lg:text-lg mb-6 lg:mb-8 max-w-3xl mx-auto">
                            {homeSection?.cta_description}
                        </p>
                        <div className="flex gap-4 justify-center">
                            <Link href={route('mobile-service.frontend.booking', { userSlug })} className="inline-flex items-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-amber-900 hover:text-white hover:border-amber-900" style={{ backgroundColor: 'white', color: '#33312e', border: '1px solid white' }}>
                                <Calendar className="w-4 h-4" />
                                <span>{t('Book Appointment')}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* Integration Widgets (Tawk.to, WhatsApp, etc.) */}
            {integrationFields.map((field) => (
                <div key={field.id}>
                    {field.component}
                </div>
            ))}

        </Layout>
    );
};

export default Index;