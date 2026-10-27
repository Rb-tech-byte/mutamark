import React, { useState, useEffect } from 'react';
import { Link, usePage, Head, useForm } from '@inertiajs/react';
import Layout from '../../Components/Frontend/Layout';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { ChevronRight, ChevronLeft, Check, Star, MessageCircle, Send, Search, Hash, Circle, Edit } from 'lucide-react';
import { getImagePath } from '@/utils/helpers';

interface ServiceDetailsProps {
    service: {
        id: number;
        name: string;
        description: string;
        icon: string;
        sale_price: string;
        expected_duration: string;
        long_description: string;
        category_id: number;
        image?: string;

    };
    relatedServices: {
        id: number;
        name: string;
        description: string;
        icon: string;
        sale_price: string;
        image?: string;

    }[];
    reviews: {
        id: number;
        name: string;
        email: string;
        rating: number;
        review: string;
        created_at: string;
    }[];
}

const ServiceDetails = () => {
    const { t } = useTranslation();
    const { service, relatedServices, reviews = [] } = usePage<ServiceDetailsProps>().props;
    const [currentSlide, setCurrentSlide] = useState(0);
    const slidesToShow = 3;
    const maxSlides = Math.max(0, (relatedServices?.length || 0) - slidesToShow);
    const [selectedRating, setSelectedRating] = useState(0);
    const { data, setData, post, processing, reset } = useForm({
        name: '',
        email: '',
        rating: 0,
        review: ''
    });
    const pageProps = usePage().props as any
    const { mobileServiceSettings, mobileServices } = pageProps;
    const contactInfo = mobileServiceSettings?.contact_info || {};
    const { url } = usePage();
    const pathParts = url.split('/');
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;

    useEffect(() => {
        setData('rating', selectedRating);
    }, [selectedRating]);

    const nextSlide = () => {
        setCurrentSlide(prev => Math.min(prev + 1, maxSlides));
    };

    const prevSlide = () => {
        setCurrentSlide(prev => Math.max(prev - 1, 0));
    };

    const handleStarClick = (rating: number) => {
        setSelectedRating(rating);
    };

    const handleSubmitReview = async (e: React.FormEvent) => {
        e.preventDefault();

        if (selectedRating === 0) {
            return;
        }

        post(route('mobile-service.frontend.reviews.store', { userSlug, service: service?.id }), {
            onSuccess: () => {
                reset();
                setSelectedRating(0);
            },
            onError: (errors) => {
            }
        });
    };

    const renderStars = (rating: number, size = 'h-5 w-5') => {
        return (
            <div className="flex">
                {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                        key={star}
                        className={`${size} ${star <= rating ? 'text-yellow-400 fill-current' : 'text-gray-300'}`}
                    />
                ))}
            </div>
        );
    };

    return (
        <Layout>
            <Head title={`${service?.name || 'Service'} Details | Mobile Service Management`} />
            {/* Hero Section */}
            <section className="relative lg:py-16 py-10 overflow-hidden" style={{ backgroundColor: '#33312e' }}>
                <div className="absolute inset-0 opacity-10">
                    <div className="absolute w-64 h-64 rounded-full -top-20 -end-20" style={{ backgroundColor: '#c77d1f' }}></div>
                    <div className="absolute w-64 h-64 lg:w-80 lg:h-80 rounded-full bottom-0 -start-40" style={{ backgroundColor: '#c77d1f' }}></div>
                </div>

                <div className="md:container w-full mx-auto px-4 relative">
                    <div className="text-center max-w-4xl mx-auto">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold md:mb-6 mb-4 shadow-sm border border-gray-200" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Service Overview')}
                        </span>
                        <h2 className="text-2xl md:text-3xl lg:text-5xl font-bold mb-4 lg:mb-6 leading-tight text-white">
                            {t('Service')} <span style={{ color: '#c77d1f' }}>{t('Details')}</span>
                        </h2>
                        <ul className="flex flex-wrap justify-center items-center capitalize text-white md:text-base text-sm">
                            <li className="capitalize flex items-center">
                                <Link href={route('mobile-service.frontend.index', { userSlug })}>{t('Home')}</Link>
                            </li>
                            <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                            <li className="capitalize flex items-center">{t('Service Details')}</li>
                        </ul>
                    </div>
                </div>
            </section>

            {/* Service Details */}
            <section className="py-12 lg:py-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="grid lg:grid-cols-3 gap-8 lg:gap-12">
                        <div className="lg:col-span-2 space-y-5 md:space-y-10">
                            {/* Service Overview */}
                            <div>
                                <h2 className="text-xl md:text-3xl font-bold mb-4" style={{ color: '#33312e' }}>{service?.name}</h2>
                                <p className="text-gray-600 text-sm md:text-lg leading-relaxed mb-5 md:mb-10">
                                    {service?.description}
                                </p>

                                <div className="bg-white border border-gray-200 rounded-lg p-4 md:p-8 shadow-sm">
                                    <div className="flex items-center mb-3 md:mb-6">
                                        <div className="w-8 h-8 rounded-lg flex items-center justify-center me-4" style={{ backgroundColor: '#c77d1f' }}>
                                            <MessageCircle className="w-4 h-4 text-white" />
                                        </div>
                                        <h3 className="text-lg md:text-2xl font-bold" style={{ color: '#33312e' }}>{t('Description')}</h3>
                                    </div>

                                    <div className="p-4 bg-gray-50 rounded-lg">
                                        <div 
                                            className="text-gray-700 leading-relaxed"
                                            dangerouslySetInnerHTML={{ 
                                                __html: service?.long_description || service?.description || 'No description available.' 
                                            }}
                                        />
                                    </div>
                                </div>
                            </div>

                        </div>

                        {/* Sidebar */}
                        <div>
                            <div className="bg-gray-50 border border-gray-200 p-4 md:p-6 lg:p-8 rounded-lg sticky top-6">
                                <div className="text-center mb-6">
                                    <div className="text-4xl font-bold mb-2" style={{ color: '#33312e' }}>{formatCurrency(service?.sale_price)}</div>
                                    <div className="text-black">{t('Starting Price')}</div>
                                    <div className="text-sm text-black mt-1">{t('*Price varies by device model')}</div>
                                </div>

                                <div className="space-y-4 mb-6 text-sm">

                                    <div className="flex justify-between">
                                        <span className="text-gray-600">{t('Repair Time:')}</span>
                                        <span className="font-semibold" style={{ color: '#33312e' }}>{service?.expected_duration}</span>
                                    </div>

                                </div>

                                <div className="flex gap-3 flex-col md:flex-row lg:flex-col">
                                    <Link href={route('mobile-service.frontend.booking', { userSlug })} className="w-full inline-flex items-center justify-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-amber-900 hover:border-amber-900" style={{ backgroundColor: '#c77d1f', color: 'white', border: '1px solid #c77d1f' }}>
                                        {t('Book Appointment')}
                                    </Link>
                                    <a href={`tel:${contactInfo.contact_phone}`} className="w-full inline-flex items-center justify-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-orange-500 hover:text-white hover:border-orange-500" style={{ backgroundColor: 'transparent', color: '#33312e', border: '1px solid #33312e' }}>
                                        {t('Call:')} {contactInfo.contact_phone}
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Related Services */}
            <section className="pb-12 lg:pb-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="text-center mb-5 md:mb-10">
                        <span className="inline-flex items-center bg-white uppercase px-4 py-1.5 rounded-full text-xs font-semibold mb-4 shadow-sm border border-gray-200" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Other Services')}
                        </span>
                        <h2 className="text-xl md:text-3xl font-bold title-font" style={{ color: '#33312e' }}>{t('Similar')} {t('Services')}</h2>
                    </div>

                    <div className="relative">
                        <div className="overflow-hidden">
                            <div
                                className="flex transition-transform duration-300 ease-in-out"
                                style={{ transform: `translateX(-${currentSlide * (100 / slidesToShow)}%)` }}
                            >
                                {(relatedServices || []).map((relatedService, index) => (
                                    <div key={index} className="w-1/3 flex-shrink-0 px-2">
                                        <div className="h-full flex flex-col group bg-white shadow-md border border-gray-200 rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                            <div className="relative overflow-hidden">
                                                <img
                                                    src={relatedService.image ? getImagePath(relatedService.image) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/default.png')}
                                                    alt={relatedService.name}
                                                    className="w-full h-48 object-cover group-hover:scale-110 transition-transform duration-500"
                                                />
                                            </div>
                                            <div className="flex-1 flex flex-col h-full lg:p-6 p-4">
                                                <div className="flex-1">
                                                    <h3 className="text-xl mb-3 group-hover:text-[#c77d1f] transition-all duration-300">
                                                        <Link href={route('mobile-service.frontend.service-details', { userSlug, id: relatedService.id })}>{relatedService.name}</Link>
                                                    </h3>
                                                    <p className="text-gray-600 mb-4 line-clamp-2">{relatedService.description}</p>
                                                </div>
                                                <div className="flex justify-between items-center pt-4 border-t gap-3">
                                                    <span className="text-lg font-bold" style={{ color: '#c77d1f' }}>{t('From')} {formatCurrency(relatedService.sale_price || '0')}</span>
                                                    <Link href={route('mobile-service.frontend.service-details', { userSlug, id: relatedService.id })} className="text-[#c77d1f] hover:text-[#33312e] font-medium transition-all duration-300 flex items-center">
                                                        {t('View Details')}
                                                        <ChevronRight className="ms-1 w-4 h-4" />
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {(relatedServices?.length || 0) > slidesToShow && (
                            <>
                                <button
                                    onClick={prevSlide}
                                    disabled={currentSlide === 0}
                                    className="absolute left-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white rounded-md shadow-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:text-white"
                                    style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                >
                                    <ChevronLeft className="w-4 h-4 mx-2 flex items-center" />
                                </button>
                                <button
                                    onClick={nextSlide}
                                    disabled={currentSlide >= maxSlides}
                                    className="absolute right-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white rounded-md shadow-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:text-white"
                                    style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                >
                                    <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                                </button>
                            </>
                        )}
                    </div>
                </div>
            </section>

            {/* Customer Reviews */}
            <section className="py-12 lg:py-16 bg-gray-50">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {/* Write Review Form */}
                        <div className="bg-white p-4 md:p-6 rounded-lg border border-gray-200">
                            <div className="flex items-center mb-3 md:mb-6">
                                <Edit className="me-3 w-5 h-5" style={{ color: '#33312e' }} />
                                <h3 className="text-lg font-bold text-gray-800">{t('Write A Review')}</h3>
                            </div>

                            <form className="space-y-4" onSubmit={handleSubmitReview}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-black mb-2">{t('Your Name')}</label>
                                        <input
                                            type="text"
                                            placeholder="Enter your name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            required
                                            className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-black mb-2">{t('Email')}</label>
                                        <input
                                            type="email"
                                            placeholder="Enter your email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            required
                                            className="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-black mb-2">{t('Rating')}</label>
                                    <div className="flex cursor-pointer">
                                        {[1, 2, 3, 4, 5].map(star => (
                                            <Star
                                                key={star}
                                                onClick={() => handleStarClick(star)}
                                                className={`h-8 w-8 mr-1 ${star <= selectedRating ? 'text-yellow-400 fill-current' : 'text-gray-300 hover:text-yellow-400 fill-current'}`}
                                            />
                                        ))}
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-black mb-2">{t('Your Review')}</label>
                                    <textarea
                                        rows={2}
                                        placeholder="Write your review here..."
                                        value={data.review}
                                        onChange={(e) => setData('review', e.target.value)}
                                        required
                                        className="w-full border border-gray-300 rounded-lg px-4 py-3 resize-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    ></textarea>
                                </div>

                                <button type="submit" disabled={processing} className="w-full inline-flex items-center justify-center gap-2 px-6 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-amber-900 hover:border-amber-900 disabled:opacity-50" style={{ backgroundColor: '#c77d1f', color: 'white', border: '1px solid #c77d1f' }}>
                                    {processing ? 'Submitting...' : 'Submit Review'}
                                </button>
                            </form>
                        </div>

                        {/* Customer Reviews List */}
                        <div className="bg-white rounded-lg border border-gray-200">
                            <div className="flex items-center justify-between p-4 md:p-6 !pb-0 md:mb-5 mb-3">
                                <h3 className="text-lg font-bold text-gray-800">{t('Customer Reviews')}</h3>
                            </div>

                            <div className="lg:space-y-6 space-y-4 max-h-96 overflow-y-auto p-4 md:p-6 !pt-0">
                                {reviews.length > 0 ? (
                                    reviews.map((review) => {
                                        const initials = review.name.split(' ').map(n => n[0]).join('').toUpperCase();
                                        const reviewDate = formatDate(review.created_at, pageProps);

                                        return (
                                            <div key={review.id} className="border-b border-gray-100 md:pb-6 pb-4 last:border-b-0 last:pb-0">
                                                <div className="flex items-start space-x-4">
                                                    <div className="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style={{ backgroundColor: '#33312e' }}>
                                                        <span className="text-white font-semibold text-sm">{initials}</span>
                                                    </div>
                                                    <div className="flex-1">
                                                        <div className="flex items-center space-x-2 mb-2">
                                                            <h4 className="font-semibold text-gray-800">{review.name}</h4>
                                                            <div className="flex items-center">
                                                                {renderStars(review.rating, 'h-4 w-4')}
                                                            </div>
                                                        </div>
                                                        <p className="text-gray-600 text-sm mb-2 leading-relaxed">
                                                            {review.review}
                                                        </p>
                                                        <p className="text-xs text-gray-700">{t('Posted on')} {reviewDate}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="text-center py-8">
                                        <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                            <Star className="h-8 w-8 text-gray-400"/>
                                            
                                        </div>
                                        <h4 className="text-lg font-semibold text-gray-800 mb-2">{t('No Reviews Yet')}</h4>
                                        <p className="text-gray-600">{t('Be the first to review this service!')}</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </Layout>
    );
};

export default ServiceDetails;