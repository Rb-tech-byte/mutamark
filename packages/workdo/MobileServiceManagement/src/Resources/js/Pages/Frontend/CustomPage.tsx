import React from 'react';
import { usePage,Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { route } from 'ziggy-js';
import Layout from '../../Components/Frontend/Layout';
import { ChevronRight, Circle } from 'lucide-react';

interface PageData {
    name: string;
    title: string;
    contents: string;
}

interface CustomPageProps {
    customPage: PageData;
}

const CustomPage = () => {
    const { t } = useTranslation();
    const { customPage } = usePage().props as CustomPageProps;
    const page = customPage;
    const { url } = usePage();
    const pathParts = url ? url.split('/') : [];
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;
    
    if (!page) {
        return <div>{t('Page not found')}</div>;
    }

    return (
        <Layout title={page.name} activeNav="">
            {/* Page Header */}
            <section className="relative lg:py-16 py-10 overflow-hidden" style={{ backgroundColor: '#33312e' }}>
                <div className="absolute inset-0 opacity-10">
                    <div className="absolute w-64 h-64 rounded-full -top-20 -end-20" style={{ backgroundColor: '#c77d1f' }}></div>
                    <div className="absolute w-64 h-64 lg:w-80 lg:h-80 rounded-full bottom-0 -start-40" style={{ backgroundColor: '#c77d1f' }}></div>
                </div>

                <div className="md:container w-full mx-auto px-4 relative">
                    <div className="text-center max-w-4xl mx-auto">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold md:mb-6 mb-4 shadow-sm border border-gray-200" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {page.name}
                        </span>
                        <h2 className="text-2xl md:text-3xl lg:text-5xl font-bold mb-4 lg:mb-6 leading-tight text-white">
                            {page.title}
                        </h2>
                        <ul className="flex flex-wrap justify-center items-center capitalize text-white md:text-base text-sm">
                            <li className="capitalize flex items-center">
                                 <Link href={route('mobile-service.frontend.index', { userSlug })}>{t('Home')}</Link>
                            </li>
                            <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                            <li className="capitalize flex items-center">{page.name}</li>
                        </ul>
                    </div>
                </div>
            </section>

            {/* Content Section */}
            <section className="py-10 lg:py-16 bg-gray-50">
                <div className="max-w-4xl mx-auto px-4 lg:px-6">
                    <div className="bg-white rounded-lg border border-gray-200 p-4 lg:p-8 not-tailwind">
                        <div dangerouslySetInnerHTML={{ __html: page.contents?.replace(/&lt;br&gt;/g, '<br>').replace(/&lt;\/br&gt;/g, '') }} />
                    </div>
                </div>
            </section>
        </Layout>
    );
};

export default CustomPage;