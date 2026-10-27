import { Link, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { useTranslation } from 'react-i18next';
import { getImagePath } from '@/utils/helpers';
import { useState, useEffect, useRef } from 'react';
import { ChevronDown, Menu } from 'lucide-react';
import languagesData from '@/../lang/language.json';

interface HeaderProps {
    activeNav?: string;
}

const Header = ({ activeNav }: HeaderProps) => {
    const { t, i18n } = useTranslation();
    const pageProps = usePage().props as any;
    const { mobileServiceSettings, auth } = pageProps;
    const { url } = usePage();
    const pathParts = url ? url.split('/') : [];
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;

    const [languageDropdownOpen, setLanguageDropdownOpen] = useState(false);
    const [mobileLanguageDropdownOpen, setMobileLanguageDropdownOpen] = useState(false);
    const [selectedLanguage, setSelectedLanguage] = useState('en');
    const dropdownRef = useRef<HTMLDivElement>(null);
    const mobileDropdownRef = useRef<HTMLDivElement>(null);

    const languages = languagesData.filter(lang => lang.enabled !== false);

    useEffect(() => {
        const savedLang = sessionStorage.getItem('selectedLanguage');
        const companyLang = auth?.lang || 'en';
        const finalLang = savedLang || companyLang;
        
        setSelectedLanguage(finalLang);
        i18n.changeLanguage(finalLang);

        document.documentElement.dir = 'ltr';
        document.documentElement.lang = finalLang;
    }, [auth?.lang]);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setLanguageDropdownOpen(false);
            }
            if (mobileDropdownRef.current && !mobileDropdownRef.current.contains(event.target as Node)) {
                setMobileLanguageDropdownOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleLanguageChange = (languageCode: string) => {
        setSelectedLanguage(languageCode);
        setLanguageDropdownOpen(false);
        setMobileLanguageDropdownOpen(false);
        i18n.changeLanguage(languageCode);
        
        sessionStorage.setItem('selectedLanguage', languageCode);

        document.documentElement.dir = 'ltr';
        document.documentElement.lang = languageCode;
    };

    const currentLang = languages.find(lang => lang.code === selectedLanguage) || languages[0];
    return (
        <header className="sticky top-0 z-50 w-full">
            <nav className="bg-white backdrop-filter backdrop-blur-lg w-full border-b border-gray-100">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="flex justify-between py-3 lg:py-4 items-center">
                        <div className="flex items-center">
                            <h1 className="flex-shrink-0 flex items-center gap-3 logo-col">
                                <Link href={route('mobile-service.frontend.index', { userSlug })}>
                                    <img 
                                        src={mobileServiceSettings?.site_settings?.site_logo ? getImagePath(mobileServiceSettings.site_settings.site_logo) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/logo.png')} 
                                        alt="logo-image" 
                                        className="size-full object-contain" 
                                    />
                                </Link>
                            </h1>
                        </div>

                        <div className="hidden md:flex items-center md:space-x-4 lg:space-x-8">
                            <Link href={route('mobile-service.frontend.services', { userSlug })} className={`relative transition-colors group ${activeNav === 'services' ? '' : ''}`} style={{color: activeNav === 'services' ? '#c77d1f' : '#1f2937'}}>
                                {t('Services')}
                            </Link>
                            <Link href={route('mobile-service.frontend.faq', { userSlug })} className={`relative transition-colors group ${activeNav === 'faq' ? '' : ''}`} style={{color: activeNav === 'faq' ? '#c77d1f' : '#1f2937'}}>
                                {t('FAQ')}
                            </Link>
                            <Link href={route('mobile-service.frontend.about', { userSlug })} className={`relative transition-colors group ${activeNav === 'about' ? '' : ''}`} style={{color: activeNav === 'about' ? '#c77d1f' : '#1f2937'}}>
                                {t('About')}
                            </Link>
                            <Link href={route('mobile-service.frontend.contact', { userSlug })} className={`relative transition-colors group ${activeNav === 'contact' ? '' : ''}`} style={{color: activeNav === 'contact' ? '#c77d1f' : '#1f2937'}}>
                                {t('Contact')}
                            </Link>
                        </div>

                        <div className="menu-end-col hidden md:flex items-center gap-4">
                            <div ref={dropdownRef} className="relative hidden md:inline-block text-start">
                                <button 
                                    onClick={() => setLanguageDropdownOpen(!languageDropdownOpen)}
                                    type="button" 
                                    className="inline-flex items-center justify-center gap-2 px-5 py-3 font-medium border rounded-lg transition-colors" 
                                    style={{borderColor: '#33312e', color: '#33312e'}}
                                >
                                    <span className="w-[65px]">{currentLang.name}</span>
                                    <ChevronDown className="w-4 h-4" />
                                </button>
                                {languageDropdownOpen && (
                                    <div className="absolute end-0 rtl:end-auto rtl:start-0 mt-2 py-2 w-48 bg-white border rounded-lg shadow-lg z-50">
                                        {languages.map((lang) => (
                                            <button
                                                key={lang.code}
                                                onClick={() => handleLanguageChange(lang.code)}
                                                className={`block w-full text-left rtl:text-right px-4 py-2 text-gray-900 hover:bg-gray-100 ${selectedLanguage === lang.code ? 'bg-gray-50' : ''}`}
                                                onMouseEnter={(e) => e.currentTarget.style.color = '#c77d1f'}
                                                onMouseLeave={(e) => e.currentTarget.style.color = '#1f2937'}
                                            >
                                                {lang.name}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                            <Link href={route('mobile-service.frontend.booking', { userSlug })} className="inline-flex items-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5" style={{backgroundColor: '#c77d1f', color: 'white', border: '1px solid #c77d1f'}}>
                                {t('Book Service')}
                            </Link>
                        </div>

                        <div className="md:hidden flex items-center">
                            <button id="mobileMenuBtn" className="text-gray-600 hover:text-primary focus:outline-none transition-colors">
                                <Menu className="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                </div>

                <div id="mobileMenu" className="md:hidden hidden">
                    <div className="px-4 py-4">
                        <div className="mobilemenu-btn-wrp flex items-center gap-4">
                            <div ref={mobileDropdownRef} className="relative flex-1 inline-block text-start text-sm">
                                <button 
                                    onClick={() => setMobileLanguageDropdownOpen(!mobileLanguageDropdownOpen)}
                                    type="button" 
                                    className="w-full inline-flex items-center justify-between gap-2 px-3 py-2 font-medium rounded-md transition-colors" 
                                    style={{border: '1px solid #33312e', color: '#33312e'}}
                                >
                                    <span>{currentLang.name}</span>
                                    <ChevronDown className="w-4 h-4" />
                                </button>
                                {mobileLanguageDropdownOpen && (
                                    <div className="w-full absolute end-0 rtl:end-auto rtl:start-0 mt-2 py-2 min-w-28 bg-white border rounded-lg shadow-lg z-50">
                                        {languages.map((lang) => (
                                            <button
                                                key={lang.code}
                                                onClick={() => handleLanguageChange(lang.code)}
                                                className={`block w-full text-left rtl:text-right px-4 py-2 text-gray-900 hover:bg-gray-100 ${selectedLanguage === lang.code ? 'bg-gray-50' : ''}`}
                                                onMouseEnter={(e) => e.currentTarget.style.color = '#c77d1f'}
                                                onMouseLeave={(e) => e.currentTarget.style.color = '#1f2937'}
                                            >
                                                {lang.name}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                            <Link href={route('mobile-service.frontend.booking', { userSlug })} className="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 font-medium rounded-lg transition-all" style={{backgroundColor: '#c77d1f', color: 'white', border: '1px solid #c77d1f'}}>
                                {t('Book Service')}
                            </Link>
                        </div>

                        <Link href={route('mobile-service.frontend.services', { userSlug })} className="block px-0 py-3 transition-all border-b border-gray-300" style={{color: '#33312e'}}>{t('Services')}</Link>
                        <Link href={route('mobile-service.frontend.faq', { userSlug })} className="block px-0 py-3 transition-all border-b border-gray-300" style={{color: '#33312e'}}>{t('FAQ')}</Link>
                        <Link href={route('mobile-service.frontend.about', { userSlug })} className="block px-0 py-3 transition-all border-b border-gray-300" style={{color: '#33312e'}}>{t('About')}</Link>
                        <Link href={route('mobile-service.frontend.contact', { userSlug })} className="block px-0 py-3 transition-all border-b border-gray-300" style={{color: '#33312e'}}>{t('Contact')}</Link>
                    </div>
                </div>
            </nav>
        </header>
    );
};

export default Header;