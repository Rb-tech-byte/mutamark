import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { getImagePath } from '@/utils/helpers';
import SocialLinks from '@/components/SocialLinks';
import { Phone, MapPin, Mail, ChevronUp } from 'lucide-react';
import { useTranslation } from 'react-i18next';

const Footer = () => {
    const { t } = useTranslation();
    const { mobileServiceSettings, mobileServiceCustomPages } = usePage().props as any;
    const { url } = usePage();
    const pathParts = url.split('/') ;
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;
    
    
    return (
        <>
            <footer className="text-white pt-10 pb-4 md:pt-16 md:pb-8 site-footer" style={{backgroundColor: '#33312e'}}>
                <div className="md:container mx-auto px-4 w-full">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-10 border-b border-gray-100 pb-5">
                        <div className="social-col">
                            <div className="footer-logo-col mb-4">
                                <Link href={route('mobile-service.frontend.index', { userSlug })} tabIndex={0}>
                                    <img src={mobileServiceSettings?.site_settings?.footer_logo ? getImagePath(mobileServiceSettings.site_settings.footer_logo) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/footer-logo.png')} alt="footer-logo" />
                                </Link>
                            </div>
                            <p className="text-white mb-4 md:mb-8">
                                {mobileServiceSettings?.site_settings?.footer_description}
                            </p>
                            {mobileServiceSettings?.social_links && mobileServiceSettings.social_links.length > 0 && (
                                <div className="flex gap-3">
                                    <SocialLinks 
                                        socialLinks={mobileServiceSettings.social_links.map((link: any) => ({
                                            platform: link.icon,
                                            icon: link.icon,
                                            url: link.url,
                                            enabled: true
                                        }))}
                                        variant="light"
                                        size="sm"
                                        style={{backgroundColor: '#c77d1f'}}
                                    />
                                </div>
                            )}
                        </div>

                        {mobileServiceCustomPages && mobileServiceCustomPages.length > 0 && (
                            <div>
                                <h4 className="text-lg lg:text-xl font-semibold mb-4">{t('Quick Links')}</h4>
                                <ul className="space-y-2">
                                    {mobileServiceCustomPages.map((page: any) => (
                                        <li key={page.id}>
                                            <Link href={route('mobile-service.frontend.custom-page', { userSlug, slug: page.slug })} className="text-white transition-all hover:ps-2">
                                                {page.title}
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {(mobileServiceSettings?.contact_info?.location_address || mobileServiceSettings?.contact_info?.contact_phone || mobileServiceSettings?.contact_info?.contact_email) && (
                            <div>
                                <h4 className="text-lg lg:text-xl font-semibold mb-4">{mobileServiceSettings?.contact_info?.contact_title || 'Contact Us'}</h4>
                                <ul className="space-y-3 text-sm text-white">
                                    {mobileServiceSettings?.contact_info?.location_address && (
                                        <li className="flex items-start text-sm lg:text-base">
                                            <MapPin size={16} className="mt-1 me-3" style={{color: '#c77d1f'}} />
                                            <p dangerouslySetInnerHTML={{__html: mobileServiceSettings.contact_info.location_address.replace(/\n/g, '<br />')}}></p>
                                        </li>
                                    )}
                                    {mobileServiceSettings?.contact_info?.contact_phone && (
                                        <li className="flex items-center text-sm lg:text-base">
                                            <Phone size={16} className="me-3" style={{color: '#c77d1f'}} />
                                            <a href={`tel:${mobileServiceSettings.contact_info.contact_phone}`}>{mobileServiceSettings.contact_info.contact_phone}</a>
                                        </li>
                                    )}
                                    {mobileServiceSettings?.contact_info?.contact_email && (
                                        <li className="flex items-center text-sm lg:text-base">
                                            <Mail size={16} className="me-3" style={{color: '#c77d1f'}} />
                                            <a href={`mailto:${mobileServiceSettings.contact_info.contact_email}`} className="break-all">{mobileServiceSettings.contact_info.contact_email}</a>
                                        </li>
                                    )}
                                </ul>
                            </div>
                        )}
                    </div>

                    <div className="flex flex-col md:flex-row justify-center items-center pt-5">
                        <p className="text-white text-sm">
                          &copy; {new Date().getFullYear()} {mobileServiceSettings?.site_settings?.footer_text}
                        </p>
                    </div>
                </div>
            </footer>

            <button id="back-to-top" className="fixed z-10 bottom-6 end-4 border border-white md:bottom-8 md:end-8 text-white w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl hover:bg-opacity-90 transition-all duration-300 transform hover:scale-110 hidden" style={{backgroundColor: '#c77d1f'}}>
                <ChevronUp size={16} />
            </button>
        </>
    );
};

export default Footer;