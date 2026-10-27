import React, { useState } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import SocialLinks from '@/components/SocialLinks';
import { formatCurrency,getImagePath } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { ChevronRight, Wrench, Check, Star, MessageCircle, Send, Search, Hash, Circle, ChevronLeft } from 'lucide-react';

interface PaginatedServices {
    data: {
        id: number;
        name: string;
        description: string;
        sale_price: string;
        icon?: string;
        includes?: string[];
        image?: string;
    }[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface ServicesProps {
    categories: {
        id: number;
        name: string;
        slug: string;
    }[];
    services: PaginatedServices | {
        id: number;
        name: string;
        description: string;
        sale_price: string;
        icon?: string;
        includes?: string[];
        image?: string;
    }[];
    search?: string;
};
import Layout from '../../Components/Frontend/Layout';

const Services = () => {
    const { t } = useTranslation();
    const { categories, services, search } = usePage<ServicesProps>().props;
    const { url } = usePage();
    const pathParts = url.split('/');
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;
    
    const [searchTerm, setSearchTerm] = useState(search || '');
    
    const handleSearch = () => {
        router.visit(route('mobile-service.frontend.services', { userSlug }), {
            method: 'get',
            data: { search: searchTerm },
            preserveState: true
        });
    };

    return (
        <Layout title="Services | Mobile Service Management" activeNav="services">
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
                            {t('Our Services')}
                        </span>
                        <h2 className="text-2xl md:text-3xl lg:text-5xl font-bold mb-4 lg:mb-6 leading-tight text-white">
                            {t('Our')} <span style={{ color: '#c77d1f' }}>{t('Mobile Services')}</span>
                        </h2>
                        <ul className="flex flex-wrap justify-center items-center capitalize text-white md:text-base text-sm">
                            <li className="capitalize flex items-center">
                                <Link href={route('mobile-service.frontend.index', { userSlug })}>{t('Home')}</Link>
                            </li>
                            <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                            <li className="capitalize flex items-center">{t('Services')}</li>
                        </ul>
                    </div>
                </div>
            </section>

            {/* Services Grid */}
            <section className="py-10 lg:py-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    {/* Search Section */}
                    <div className="bg-gray-50 rounded-lg shadow-md border border-gray-100 lg:p-8 md:p-6 p-4 lg:mb-12 mb-8">
                        <div className="max-w-2xl mx-auto">
                            <div className="flex gap-3 flex-wrap">
                                <input
                                    type="text"
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    placeholder={t('Search for services...')}
                                    className="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#c77d1f] focus:border-transparent transition-all"
                                    onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                />
                                <button
                                    onClick={handleSearch}
                                    className="bg-[#c77d1f] hover:bg-[#33312e] text-white px-8 py-3 rounded-lg transition-colors font-medium flex items-center justify-center"
                                >
                                    <Search className="w-4 h-4 me-2" />
                                    {t('Search')}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {/* Services Grid */}
                    {services && (Array.isArray(services) ? services.length > 0 : services.data && services.data.length > 0) ? (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                                {(Array.isArray(services) ? services : services.data).map((service) => (
                                    <div key={service.id} className="h-full flex flex-col group bg-white shadow-md border border-gray-200 rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                        <div className="relative overflow-hidden">
                                            <img
                                                src={service.image ? getImagePath(service.image) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/default.png')}
                                                alt={service.name}
                                                loading="lazy"
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
                                            <div className="flex items-center pt-4 border-t">
                                                <span className="text-sm font-bold flex-1" style={{ color: '#c77d1f' }}>{t('From')} {formatCurrency(service.sale_price || '0')}</span>
                                                <div className="flex items-center gap-1">
                                                    <Link 
                                                        href={route('mobile-service.frontend.booking', { userSlug }) + `?service=${service.id}`} 
                                                        className="bg-[#c77d1f] hover:bg-white hover:text-[#c77d1f] text-white px-3 py-1.5 rounded text-sm font-medium transition-all duration-300 border border-[#c77d1f]"
                                                    >
                                                        {t('Book Service')}
                                                    </Link>
                                                    <Link 
                                                        href={route('mobile-service.frontend.service-details', { userSlug, id: service.id })} 
                                                        className="border border-[#c77d1f] text-[#c77d1f] hover:bg-[#c77d1f] hover:text-white px-3 py-1.5 rounded text-sm font-medium transition-all duration-300 flex items-center"
                                                    >
                                                        {t('View Details')}
                                                        <ChevronRight className="ms-1 w-3 h-3" />
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            
                            {/* Pagination */}
                            {!Array.isArray(services) && services.last_page > 1 && (
                                <div className="flex items-center justify-between mt-8 px-2 py-4">
                                    <div className="text-sm text-gray-600">
                                        {t('Showing')} {services.from} {t('to')} {services.to} {t('of')} {services.total} {t('results')}
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <button
                                            onClick={() => router.visit(route('mobile-service.frontend.services', { userSlug }), { method: 'get', data: { page: services.current_page - 1, search: searchTerm }, preserveState: true })}
                                            disabled={services.current_page === 1}
                                            className="flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <ChevronLeft className="w-4 h-4 mr-1" />
                                            {t('Previous')}
                                        </button>

                                        {Array.from({ length: services.last_page }, (_, i) => i + 1).map((page) => {
                                            const isActive = page === services.current_page;
                                            return (
                                                <button
                                                    key={page}
                                                    onClick={() => router.visit(route('mobile-service.frontend.services', { userSlug }), { method: 'get', data: { page, search: searchTerm }, preserveState: true })}
                                                    className={`px-3 py-2 text-sm font-medium rounded-lg ${
                                                        isActive
                                                            ? 'text-white bg-[#c77d1f] border border-[#c77d1f]'
                                                            : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700'
                                                    }`}
                                                >
                                                    {page}
                                                </button>
                                            );
                                        })}

                                        <button
                                            onClick={() => router.visit(route('mobile-service.frontend.services', { userSlug }), { method: 'get', data: { page: services.current_page + 1, search: searchTerm }, preserveState: true })}
                                            disabled={services.current_page === services.last_page}
                                            className="flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {t('Next')}
                                            <ChevronRight className="w-4 h-4 ml-1" />
                                        </button>
                                    </div>
                                </div>
                            )}
                        </>
                    ) : (
                        <div className="text-center py-12">
                            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                <Wrench className="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">{t('No Services Available')}</h3>
                            <p className="text-gray-600">{t('Services will appear here when added.')}</p>
                        </div>
                    )}
                </div>
            </section>
        </Layout>
    );
};

export default Services;