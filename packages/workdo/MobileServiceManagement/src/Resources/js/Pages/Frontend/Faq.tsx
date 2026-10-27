import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { route } from 'ziggy-js';
import Layout from '../../Components/Frontend/Layout';
import { Phone, Wrench, Check, Star, MessageCircle, Send, Search, Hash, Circle, Plus, Minus, ChevronRight } from 'lucide-react';

interface FaqItem {
    question: string;
    answer: string;
}

interface FaqProps {
    faqs: FaqItem[];
}

const Faq = () => {
    const { t } = useTranslation();
    const { faqs = [] } = usePage<FaqProps>().props;
    const [openFaq, setOpenFaq] = useState<number | null>(null);
    const { url } = usePage();
    const pathParts = url ? url.split('/') : [];
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;
   
    const toggleFaq = (index: number) => {
        setOpenFaq(openFaq === index ? null : index);
    };

    return (
        <Layout title={`${t('FAQ')} | ${t('Mobile Service Management')}`} activeNav="faq">
            {/* Hero Section */}
            <section className="relative lg:py-16 py-10 overflow-hidden" style={{backgroundColor: '#33312e'}}>
                <div className="absolute inset-0 opacity-10">
                    <div className="absolute w-64 h-64 rounded-full -top-20 -end-20" style={{backgroundColor: '#c77d1f'}}></div>
                    <div className="absolute w-64 h-64 lg:w-80 lg:h-80 rounded-full bottom-0 -start-40" style={{backgroundColor: '#c77d1f'}}></div>
                </div>

                <div className="md:container w-full mx-auto px-4 relative">
                    <div className="text-center max-w-4xl mx-auto">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold md:mb-6 mb-4 shadow-sm border border-gray-200" style={{color: '#33312e'}}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{color: '#c77d1f'}} />
                            {t('FAQ')}
                        </span>
                        <h2 className="text-2xl md:text-3xl lg:text-5xl font-bold text-white mb-4 lg:mb-6 leading-tight">
                            {t('Frequently Asked')} <span style={{color: '#c77d1f'}}>{t('Questions')}</span>
                        </h2>
                        <ul className="flex flex-wrap justify-center items-center capitalize text-white md:text-base text-sm">
                            <li className="capitalize flex items-center">
                                <Link href={route('mobile-service.frontend.index', { userSlug })}>{t('Home')}</Link>
                            </li>
                            <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                            <li className="capitalize flex items-center">{t('FAQ')}</li>
                        </ul>
                    </div>
                </div>
            </section>

            {/* FAQ Section */}
            <section className="py-10 lg:py-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="max-w-3xl mx-auto space-y-4">
                        {faqs.length > 0 ? faqs.map((faq, index) => (
                            <div key={index} className="faq-item bg-white rounded-lg overflow-hidden border border-gray-200">
                                <div 
                                    className="faq-question py-4 px-4 md:px-6 cursor-pointer flex justify-between items-center hover:bg-gray-50 transition-colors"
                                    onClick={() => toggleFaq(index)}
                                >
                                    <h3 className="font-medium md:text-lg" style={{color: '#33312e'}}>{faq.question}</h3>
                                    {openFaq === index ? (
                                        <Minus className="w-5 h-5 ms-2 transition-transform duration-300" style={{color: '#c77d1f'}} />
                                    ) : (
                                        <Plus className="w-5 h-5 ms-2 transition-transform duration-300" style={{color: '#c77d1f'}} />
                                    )}
                                </div>
                                {openFaq === index && (
                                    <div className="faq-answer py-4 px-4 md:px-6 border-t-2 border-gray-100 animate-fade-in">
                                        <p className="text-gray-600">{faq.answer}</p>
                                    </div>
                                )}
                            </div>
                        )) : (
                            <div className="text-center py-12">
                                <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                    <MessageCircle className="w-8 h-8 text-gray-400" />
                                </div>
                                <h3 className="text-lg font-semibold text-gray-800 mb-2">{t('No FAQs Available')}</h3>
                                <p className="text-gray-600">{t('Frequently asked questions will appear here when added.')}</p>
                            </div>
                        )}
                    </div>
                </div>
            </section>

            <style jsx>{`
                .animate-fade-in {
                    animation: fadeIn 0.3s ease-in-out;
                }
                
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `}</style>
        </Layout>
    );
};

export default Faq;