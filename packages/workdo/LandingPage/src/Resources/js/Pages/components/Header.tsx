import { Menu, X, LogIn, LayoutDashboard } from 'lucide-react';
import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { getAdminSetting, getImagePath } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';
import { LanguageSwitcher } from '@/components/language-switcher';

interface HeaderProps {
    settings?: any;
    customPages?: any[];
}

const HEADER_VARIANTS = {
    header1: {
        nav: 'bg-white border-b border-gray-200 sticky top-0 z-50',
        container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
        wrapper: 'flex justify-between items-center h-16 [direction:ltr]',
        logo: 'text-2xl font-bold',
        desktop: 'hidden md:flex items-center gap-2',
        mobile: 'md:hidden text-gray-600 p-2 transition-colors',
        mobileMenu: 'md:hidden bg-white border-t shadow-lg'
    },
    header2: {
        nav: 'bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm',
        container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
        wrapper: 'flex flex-col items-center py-6 gap-6',
        logo: 'text-3xl font-bold',
        desktop: 'flex items-center gap-2 bg-gray-50 px-6 py-3 rounded-full',
        mobile: 'md:hidden text-gray-600 p-2 transition-colors absolute top-4 end-4 hover:bg-gray-100 rounded-lg',
        mobileMenu: 'md:hidden bg-white border-t w-full shadow-lg'
    },
    header3: {
        nav: 'bg-white/95 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50',
        container: 'max-w-6xl mx-auto px-6 sm:px-8 lg:px-10',
        wrapper: 'flex justify-between items-center h-14 py-2 [direction:ltr]',
        logo: 'text-xl font-bold',
        desktop: 'hidden md:flex items-center gap-2',
        mobile: 'md:hidden text-gray-600 p-2 transition-colors hover:bg-gray-100 rounded-md',
        mobileMenu: 'md:hidden bg-white/95 backdrop-blur-md border-t'
    },
    header4: {
        nav: 'bg-black/20 backdrop-blur-md absolute top-0 left-0 right-0 z-50 border-b border-white/10',
        container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
        wrapper: 'flex justify-between items-center h-20 py-4 [direction:ltr]',
        logo: 'text-2xl font-bold text-white drop-shadow-lg',
        desktop: 'hidden md:flex items-center gap-2',
        mobile: 'md:hidden text-white p-2 transition-colors hover:bg-white/10 rounded-lg',
        mobileMenu: 'md:hidden bg-black/90 backdrop-blur-md border-t border-white/10'
    },
    header5: {
        nav: 'sticky top-0 z-50 shadow-xl',
        container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
        wrapper: 'flex justify-between items-center h-20 py-4 [direction:ltr]',
        logo: 'text-2xl font-bold text-white drop-shadow-lg',
        desktop: 'hidden md:flex items-center gap-2',
        mobile: 'md:hidden text-white p-2 transition-colors hover:bg-white/10 rounded-lg',
        mobileMenu: 'md:hidden border-t border-white/20'
    }
};

export default function Header({ settings, customPages = [] }: HeaderProps) {
    const sectionData = settings?.config_sections?.sections?.header || {};
    const { t } = useTranslation();
    const variant = sectionData.variant || 'header1';
    const config = HEADER_VARIANTS[variant as keyof typeof HEADER_VARIANTS] || HEADER_VARIANTS.header1;

    const companyName = sectionData.company_name || settings?.company_name || 'Mutamark Enterprises Ltd';
    const isAuthenticated = !!settings?.is_authenticated;
    const colors = settings?.config_sections?.colors || { primary: '#10b981', secondary: '#059669', accent: '#f59e0b' };
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const logoKey = 'logo_dark';
    const logoPath = getAdminSetting(logoKey);
    const logoUrl = logoPath ? getImagePath(logoPath) : null;

    // Standardized default navigation items to ensure menu is always loaded and not mixing
    const defaultNavItems = [
        { text: t('Home'), href: '/' },
        { text: t('Capabilities'), href: '#features' },
        { text: t('Modules'), href: '#modules' },
        { text: t('Operational Screens'), href: '#gallery' },
        { text: t('Mobile Apps'), href: '#apps' },
        { text: t('Verify Document'), href: '/verify' },
    ];

    // Read navigation items from settings or fallback to defaults
    const configuredItems = Array.isArray(sectionData.navigation_items) && sectionData.navigation_items.length > 0
        ? sectionData.navigation_items
        : defaultNavItems;

    // Deduplicate and normalize navigation items
    const seenHrefs = new Set<string>();
    const navigationItems = configuredItems.filter((item: any) => {
        if (!item || !item.text) return false;
        const normalized = (item.href || '').trim();
        if (seenHrefs.has(normalized)) return false;
        seenHrefs.add(normalized);
        return true;
    });

    // Smart link resolver to prevent mixing/breaking URLs across domains or subpages
    const resolveHref = (rawHref: string): string => {
        if (!rawHref) return route('landing.page');

        // Custom CMS page pattern
        if (rawHref.startsWith('/page/')) {
            const slug = rawHref.replace('/page/', '');
            try {
                return route('custom-page.show', slug);
            } catch (e) {
                return `/page/${slug}`;
            }
        }

        // Anchor navigation
        if (rawHref.startsWith('#')) {
            if (typeof window !== 'undefined' && window.location.pathname !== '/' && !window.location.pathname.endsWith('/mutadash/')) {
                return `${route('landing.page')}${rawHref}`;
            }
            return rawHref;
        }

        // Local dev host cleanups
        if (rawHref === '/' || rawHref.includes('localhost/mutadash')) {
            return route('landing.page');
        }

        return rawHref;
    };

    const renderNavItems = (isMobile = false) => {
        const isTransparentOrGradient = variant === 'header4' || variant === 'header5';
        const textColor = isTransparentOrGradient ? 'text-white' : 'text-slate-700';
        const hoverBg = variant === 'header2'
            ? 'hover:bg-white hover:shadow-sm'
            : variant === 'header3'
            ? 'hover:bg-gray-50'
            : isTransparentOrGradient
            ? 'hover:bg-white/10'
            : 'hover:bg-gray-50';

        return navigationItems.map((item: any) => {
            const targetHref = resolveHref(item.href);
            const isExternal = item.target === '_blank';

            if (isExternal) {
                return (
                    <a
                        key={item.text}
                        href={targetHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        onClick={() => isMobile && setMobileMenuOpen(false)}
                        className={isMobile
                            ? `block px-4 py-3 text-base font-medium ${textColor} ${hoverBg} rounded-lg transition-all`
                            : `${textColor} px-3.5 py-2 text-sm font-medium ${hoverBg} rounded-lg transition-all duration-200`
                        }
                    >
                        {item.text}
                    </a>
                );
            }

            return (
                <Link
                    key={item.text}
                    href={targetHref}
                    onClick={() => isMobile && setMobileMenuOpen(false)}
                    className={isMobile
                        ? `block px-4 py-3 text-base font-medium ${textColor} ${hoverBg} rounded-lg transition-all`
                        : `${textColor} px-3.5 py-2 text-sm font-medium ${hoverBg} rounded-lg transition-all duration-200`
                    }
                >
                    {item.text}
                </Link>
            );
        });
    };

    // Single unified CTA: Eliminates duplicate "Sign In" + "ERP Portal Login" buttons
    const renderCTAButtons = (isMobile = false) => {
        if (isAuthenticated) {
            return (
                <button
                    onClick={() => {
                        if (isMobile) setMobileMenuOpen(false);
                        router.visit(route('dashboard'));
                    }}
                    className={`inline-flex items-center justify-center gap-2 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-[1.02] active:scale-[0.98] ${
                        isMobile ? 'px-4 py-2.5 text-sm w-full' :
                        variant === 'header3' ? 'px-3.5 py-1.5 text-xs' : 'px-4 py-2 text-sm'
                    }`}
                    style={{ backgroundColor: colors.primary }}
                    onMouseEnter={(e) => e.currentTarget.style.backgroundColor = colors.secondary}
                    onMouseLeave={(e) => e.currentTarget.style.backgroundColor = colors.primary}
                >
                    <LayoutDashboard className="w-4 h-4" />
                    <span>{t('Dashboard')}</span>
                </button>
            );
        }

        return (
            <button
                onClick={() => {
                    if (isMobile) setMobileMenuOpen(false);
                    router.visit(route('login'));
                }}
                className={`inline-flex items-center justify-center gap-2 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md hover:scale-[1.02] active:scale-[0.98] ${
                    isMobile ? 'px-4 py-2.5 text-sm w-full' :
                    variant === 'header3' ? 'px-3.5 py-1.5 text-xs' : 'px-4 py-2 text-sm'
                }`}
                style={{ backgroundColor: colors.primary }}
                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = colors.secondary}
                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = colors.primary}
                title={t('Access ERP Portal')}
            >
                <LogIn className="w-4 h-4" />
                <span>{t('ERP Portal Login')}</span>
            </button>
        );
    };

    const getGradientStyle = () => {
        if (variant === 'header5') {
            return {
                background: `linear-gradient(to right, ${colors.primary}, ${colors.secondary}, ${colors.accent})`
            };
        }
        return {};
    };

    const getMobileMenuStyle = () => {
        if (variant === 'header5') {
            return {
                background: `linear-gradient(to right, ${colors.primary}, ${colors.secondary})`
            };
        }
        return {};
    };

    return (
        <nav className={`${config.nav} relative`} style={getGradientStyle()}>
            <div className={config.container}>
                <div className={config.wrapper}>
                    <Link href={route('landing.page')} className={config.logo} style={{ color: colors.primary }}>
                        {logoUrl ? (
                            <img src={logoUrl} alt={companyName} className="w-auto max-w-28 h-10 object-contain" />
                        ) : (
                            companyName
                        )}
                    </Link>

                    <div className={config.desktop}>
                        {renderNavItems()}
                        <div className="w-px h-5 bg-gray-200 mx-2" />
                        {renderCTAButtons()}
                        <div className="w-px h-5 bg-gray-200 mx-2" />
                        <LanguageSwitcher />
                    </div>

                    <button
                        className={config.mobile}
                        aria-label="Toggle Navigation Menu"
                        onMouseEnter={(e) => e.currentTarget.style.color = colors.primary}
                        onMouseLeave={(e) => e.currentTarget.style.color = ''}
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                    >
                        {mobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                    </button>
                </div>
            </div>

            {mobileMenuOpen && (
                <div className={config.mobileMenu} style={getMobileMenuStyle()}>
                    <div className="px-3 pt-3 pb-4 space-y-1">
                        {renderNavItems(true)}
                        <div className="pt-2 border-t mt-2 space-y-2">
                            {renderCTAButtons(true)}
                            <div className="pt-2 flex justify-center">
                                <LanguageSwitcher />
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </nav>
    );
}
