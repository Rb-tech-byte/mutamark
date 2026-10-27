import { ArrowRight } from 'lucide-react';
import { getImagePath } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';

interface HeroProps {
    settings?: any;
}

const HERO_VARIANTS = {
    hero1: {
        section: 'bg-white py-20',
        container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
         title: 'text-4xl md:text-5xl font-bold text-slate-900 mb-6 leading-tight',
        subtitle: 'text-xl text-slate-700 mb-8 leading-relaxed',
        buttons: 'flex flex-col sm:flex-row gap-4',
        primaryBtn: 'text-white px-8 py-3 rounded-lg text-lg font-medium flex items-center transition-all duration-300 shadow-lg hover:shadow-xl',
        secondaryBtn: 'border-2 border-gray-300 text-slate-700 px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-50 transition-all duration-300',
        layout: 'right-split',
        showImage: true
    },
    hero2: {
        section: 'bg-white py-20',
        container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
        title: 'text-4xl md:text-5xl font-bold text-slate-900 mb-6',
        subtitle: 'text-xl text-slate-700 mb-8',
        buttons: 'flex flex-col sm:flex-row gap-4 justify-center',
        primaryBtn: 'text-white px-8 py-3 rounded-md text-lg font-medium flex items-center justify-center transition-colors',
        secondaryBtn: 'border border-gray-300 text-slate-700 px-8 py-3 rounded-md text-lg font-medium hover:bg-gray-50',
        layout: 'split',
        showImage: true
    },
    hero3: {
        section: 'relative bg-gray-900 py-32 md:py-40 overflow-hidden',
        container: 'relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white z-10',
        title: 'text-4xl md:text-6xl lg:text-7xl font-bold mb-8 leading-tight drop-shadow-2xl',
        subtitle: 'text-xl md:text-2xl mb-12 max-w-4xl mx-auto opacity-95 leading-relaxed',
        buttons: 'flex flex-col sm:flex-row gap-6 justify-center items-center',
        primaryBtn: 'text-white px-10 py-4 rounded-2xl text-lg font-semibold flex items-center justify-center transition-all duration-300 shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 backdrop-blur-sm',
        secondaryBtn: 'border-2 border-white/30 text-white px-10 py-4 rounded-2xl text-lg font-semibold hover:bg-white/10 hover:border-white/50 transition-all duration-300 backdrop-blur-sm',
        layout: 'background',
        showImage: false
    },
    hero4: {
        section: 'bg-white py-16 md:py-20 border-b border-gray-100',
        container: 'max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center',
        title: 'text-3xl md:text-4xl lg:text-5xl font-bold text-slate-900 mb-6 leading-tight tracking-tight',
        subtitle: 'text-lg md:text-xl text-slate-700 mb-8 max-w-2xl mx-auto leading-relaxed font-medium',
        buttons: 'flex flex-col sm:flex-row gap-3 justify-center items-center',
        primaryBtn: 'text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md',
        secondaryBtn: 'text-slate-700 px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-all duration-200 border border-gray-200 hover:border-gray-300',
        layout: 'minimal',
        showImage: false
    }
};

export default function Hero({ settings }: HeroProps) {
    const { t } = useTranslation();
    const sectionData = settings?.config_sections?.sections?.hero || {};
    const variant = sectionData.variant || 'hero1';
    const config = HERO_VARIANTS[variant as keyof typeof HERO_VARIANTS] || HERO_VARIANTS.hero1;

    const title = sectionData.title || 'Mutamark Integrated Enterprise Resource Planning';
    const subtitle = sectionData.subtitle || 'Custom-engineered enterprise suite for Mutamark Enterprises Ltd by Wolinet Technology Ltd. Centralizing Multi-Warehouse WMS, 3-Way Match Procurement, TRA EFD/VFD Real-Time Fiscalization, Tanzanian Statutory Payroll, and Multi-Channel Payment Reconciliation.';
    const primaryButtonText = sectionData.primary_button_text || 'ERP Portal Login';
    const primaryButtonLink = sectionData.primary_button_link || route('login');
    const secondaryButtonText = sectionData.secondary_button_text || 'Download Mobile Scanner';
    const secondaryButtonLink = sectionData.secondary_button_link || '#apps';
    const highlightText = sectionData.highlight_text || 'Enterprise Resource Planning';
    const heroImage = sectionData.image || '/packages/workdo/LandingPage/src/marketplace/hero.png';
    const colors = settings?.config_sections?.colors || { primary: '#10b981', secondary: '#059669', accent: '#f59e0b' };

    const renderTitle = () => {
        if (highlightText && title?.includes(highlightText)) {
            const titleParts = title.split(highlightText);
            return (
                <>
                    {titleParts[0]}
                    <span style={{ color: colors.primary }}>{highlightText}</span>
                    {titleParts[1]}
                </>
            );
        }
        return title;
    };

    const renderButtons = () => (
        <div className="space-y-4">
            <div className={config.buttons}>
                <button
                    className={config.primaryBtn}
                    style={{ backgroundColor: colors.primary }}
                    onMouseEnter={(e) => e.currentTarget.style.backgroundColor = colors.secondary}
                    onMouseLeave={(e) => e.currentTarget.style.backgroundColor = colors.primary}
                    onClick={() => window.location.href = primaryButtonLink}
                >
                    {primaryButtonText}
                    {config.layout !== 'minimal' && <ArrowRight className="ms-3 h-5 w-5" />}
                </button>
                <button
                    className={config.secondaryBtn}
                    onClick={() => {
                        const target = document.querySelector(secondaryButtonLink);
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth' });
                        } else {
                            window.location.href = secondaryButtonLink;
                        }
                    }}
                >
                    {secondaryButtonText}
                </button>
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500 pt-1">
                <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Tanzania Revenue Authority EFD/VFD Ready
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Multi-Branch WMS & Barcode Engine
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Zebra & Honeywell Terminal Companion
                </span>
            </div>
        </div>
    );

    const renderBackgroundImage = () => {
        if (config.layout !== 'background') return null;
        return (
            <>
                {heroImage && (
                    <div className="absolute inset-0">
                        <img src={getImagePath(heroImage)} alt="Hero Background" className="w-full h-full object-cover" />
                        <div className="absolute inset-0 bg-gradient-to-br from-black/60 via-black/40 to-black/60"></div>
                    </div>
                )}
                {!heroImage && (
                    <div className="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-black"></div>
                )}
                <div className="absolute inset-0">
                    <div className="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                    <div className="absolute bottom-20 right-20 w-48 h-48 bg-white/5 rounded-full blur-3xl animate-pulse delay-1000"></div>
                    <div className="absolute top-1/2 left-1/3 w-24 h-24 bg-white/10 rounded-full blur-2xl animate-pulse delay-500"></div>
                </div>
            </>
        );
    };

    const renderImage = () => {
        if (!config.showImage || config.layout === 'background') return null;

        return (
            <div className="relative rounded-2xl overflow-hidden shadow-2xl border border-slate-200/80 bg-slate-900 group">
                <img 
                    src={getImagePath(heroImage)} 
                    alt="Mutamark Integrated ERP Dashboard" 
                    className="w-full h-auto object-cover transform transition-transform duration-700 group-hover:scale-[1.01]" 
                />
                <div className="absolute inset-0 ring-1 ring-inset ring-black/10 rounded-2xl pointer-events-none" />
                <div className="absolute top-3 right-3 bg-slate-900/80 backdrop-blur-md px-3 py-1 rounded-full border border-slate-700/80 text-[11px] font-semibold text-emerald-400">
                    Mutamark Live Hub
                </div>
            </div>
        );
    };

    const renderContent = () => (
        <div className={config.layout === 'split' || config.layout === 'right-split' ? '' : 'w-full text-center'}>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold mb-4">
                <span>Wolinet Enterprise Architecture</span>
                <span className="text-slate-300">•</span>
                <span>Single-Tenant Dedicated Deployment</span>
            </div>
            <h1 className={config.title}>
                {renderTitle()}
            </h1>
            <p className={config.subtitle}>
                {subtitle}
            </p>
            {renderButtons()}
        </div>
    );

    return (
        <section className={config.section}>
            {renderBackgroundImage()}
            <div className={config.container}>
                {config.layout === 'split' || config.layout === 'right-split' ? (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        {config.layout === 'right-split' ? (
                            <>
                                {renderImage()}
                                {renderContent()}
                            </>
                        ) : (
                            <>
                                {renderContent()}
                                {renderImage()}
                            </>
                        )}
                    </div>
                ) : config.layout === 'background' ? (
                    <div className="relative">
                        {renderContent()}
                        <div className="absolute inset-0 pointer-events-none">
                            <div className="absolute top-1/4 left-1/4 w-2 h-2 bg-white rounded-full animate-ping"></div>
                            <div className="absolute top-3/4 right-1/4 w-1 h-1 bg-white/70 rounded-full animate-ping delay-700"></div>
                            <div className="absolute top-1/2 right-1/3 w-1.5 h-1.5 bg-white/50 rounded-full animate-ping delay-1000"></div>
                        </div>
                    </div>
                ) : (
                    <>
                        {renderContent()}
                        {renderImage()}
                    </>
                )}
            </div>
        </section>
    );
}
