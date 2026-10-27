import React from 'react';
import { useTranslation } from 'react-i18next';
import { getImagePath } from '@/utils/helpers';
import { 
    Barcode, 
    CheckCircle2, 
    Printer, 
    ShieldCheck, 
    Zap, 
    Smartphone, 
    Download, 
    Warehouse, 
    Server, 
    ArrowRight 
} from 'lucide-react';

interface MobileAppProps {
    settings?: any;
}

export default function MobileApp({ settings }: MobileAppProps) {
    const { t } = useTranslation();
    const sectionData = settings?.config_sections?.sections?.apps || {};
    const colors = settings?.config_sections?.colors || { primary: '#10b981', secondary: '#059669', accent: '#f59e0b' };

    const title = sectionData.title || 'Mutamark Mobile & Barcode Scanner Suite';
    const subtitle = sectionData.subtitle || 'Built for warehouse dock workers, distribution drivers, and store cashiers. Seamlessly connect rugged barcode scanners (Zebra, Honeywell, Sunmi) and smartphones directly to Mutamark ERP.';
    const badgeText = sectionData.badge_text || 'Enterprise Mobile Companion v2.4';
    const playStoreUrl = sectionData.play_store_url || '#';
    const appStoreUrl = sectionData.app_store_url || '#';
    const apkDownloadUrl = sectionData.apk_download_url || '#';
    const mobileMockupImage = sectionData.image || '/packages/workdo/LandingPage/src/marketplace/image5.png';

    const features = [
        {
            icon: Barcode,
            title: 'High-Speed 1D/2D Barcode Receiving',
            description: 'Scan supplier cartons, pallets, and serial tags in under 50ms. Eliminates manual keystrokes and entry errors at dockside intake.'
        },
        {
            icon: CheckCircle2,
            title: 'Dockside 3-Way Match Verification',
            description: 'Intake workers compare physical deliveries directly against approved Purchase Orders with automated line variance alerts.'
        },
        {
            icon: Warehouse,
            title: 'Real-Time Bin & Shelf Auditing',
            description: 'Perform cyclic stock counts, bin transfers, and inter-branch warehouse dispatches on the move without paper clipboards.'
        },
        {
            icon: Printer,
            title: 'TRA Fiscal ESC/POS Printing',
            description: 'Pair with mobile thermal Bluetooth printers to generate official TRA fiscal receipts with scannable cryptographic QR codes.'
        },
        {
            icon: Zap,
            title: 'Offline-First Logistics Engine',
            description: 'Uninterrupted receiving and stock counting in metal-shielded warehouse yards; scans cache locally and auto-sync when back online.'
        },
        {
            icon: ShieldCheck,
            title: 'Cryptographic Document Ledger',
            description: 'Instant scan verification of Mutamark delivery notes, gate passes, and tax invoices using SHA-256 cryptographic ledgers.'
        }
    ];

    const compatibleDevices = [
        'Zebra Technologies (TC21, TC26, TC52, TC57)',
        'Honeywell Industrial (ScanPal EDA51/52, CT40)',
        'Sunmi Enterprise POS (V2, V2 Pro, P2 Smart Terminals)',
        'Apple iOS 16+ (iPhone & iPad Enterprise MDM)',
        'Android 11+ Enterprise OS'
    ];

    return (
        <section id="apps" className="py-24 bg-slate-900 text-white relative overflow-hidden">
            {/* Background Texture & Glow */}
            <div className="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:32px_32px] opacity-10 pointer-events-none" />
            <div className="absolute -top-40 -right-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />
            <div className="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-700/10 rounded-full blur-3xl pointer-events-none" />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                {/* Header Badge & Title */}
                <div className="text-center max-w-3xl mx-auto mb-16">
                    <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold tracking-wide uppercase mb-4">
                        <Smartphone className="w-3.5 h-3.5" />
                        <span>{badgeText}</span>
                    </div>
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white mb-6">
                        {title}
                    </h2>
                    <p className="text-base sm:text-lg text-slate-300 leading-relaxed">
                        {subtitle}
                    </p>
                </div>

                {/* Main Content Grid: Mockup Image & Features / Downloads */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center mb-16">
                    {/* Left: Device Mockup with Enterprise Badge Overlay */}
                    <div className="lg:col-span-6 relative">
                        <div className="relative mx-auto max-w-md lg:max-w-none rounded-2xl overflow-hidden border border-slate-700/80 bg-slate-800/60 shadow-2xl backdrop-blur-sm group">
                            <img 
                                src={getImagePath(mobileMockupImage)} 
                                alt="Mutamark Mobile Barcode Scanner Companion" 
                                className="w-full h-auto object-cover transform transition-transform duration-500 group-hover:scale-[1.02]"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent pointer-events-none" />
                            
                            {/* Live Scanner Operational Pill */}
                            <div className="absolute bottom-4 left-4 right-4 bg-slate-900/90 border border-slate-700/80 rounded-xl p-3 backdrop-blur-md flex items-center justify-between text-xs">
                                <div className="flex items-center gap-2.5">
                                    <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" />
                                    <div>
                                        <p className="font-semibold text-white">Hardware Barcode Imager Ready</p>
                                        <p className="text-slate-400 text-[11px]">Synced with Mutamark Central Hub</p>
                                    </div>
                                </div>
                                <div className="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-300 font-mono text-[11px] font-medium border border-emerald-500/30">
                                    TRA EFD OK
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right: Key Capabilities & Official Download Badges */}
                    <div className="lg:col-span-6 space-y-8">
                        <div>
                            <h3 className="text-xl sm:text-2xl font-bold text-white mb-3">
                                High-Precision Logistics in the Palm of Your Hand
                            </h3>
                            <p className="text-slate-300 text-sm leading-relaxed mb-6">
                                Eliminate manual clipboard counts, paper delivery notes, and inventory blind spots. Mutamark Mobile Companion turns any Android scanner terminal or iOS mobile device into an enterprise workstation.
                            </p>
                        </div>

                        {/* Feature Cards Grid */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {features.map((item, idx) => {
                                const Icon = item.icon;
                                return (
                                    <div key={idx} className="p-4 rounded-xl bg-slate-800/50 border border-slate-700/60 hover:border-emerald-500/50 transition-colors">
                                        <div className="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-2.5">
                                            <Icon className="w-4 h-4" />
                                        </div>
                                        <h4 className="text-sm font-semibold text-white mb-1">{item.title}</h4>
                                        <p className="text-xs text-slate-400 leading-normal">{item.description}</p>
                                    </div>
                                );
                            })}
                        </div>

                        {/* Official Corporate App Download Badges */}
                        <div className="pt-4 border-t border-slate-800 space-y-3">
                            <p className="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Download Mobile Companion & Terminal Drivers:
                            </p>
                            <div className="flex flex-wrap items-center gap-3">
                                {/* Google Play Store Badge */}
                                <a 
                                    href={playStoreUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 transition-all text-white shadow-sm hover:shadow-md"
                                >
                                    <svg className="w-6 h-6 fill-current text-white shrink-0" viewBox="0 0 512 512">
                                        <path d="M325.3 234.3L104.6 13l280.8 161.2-60.1 60.1zM47 0C34 6.8 25.3 19.2 25.3 35.3v441.3c0 16.1 8.7 28.5 21.7 35.3l256.6-256L47 0zm425.2 225.6l-58.9-34.1-65.7 64.5 65.7 64.5 60.1-34.1c18-14.3 18-46.5-1.2-60.8zM104.6 499l280.8-161.2-60.1-60.1L104.6 499z" />
                                    </svg>
                                    <div className="text-left">
                                        <div className="text-[10px] uppercase tracking-wider text-slate-400 font-medium leading-none">GET IT ON</div>
                                        <div className="text-sm font-semibold leading-tight text-white">Google Play</div>
                                    </div>
                                </a>

                                {/* Apple App Store Badge */}
                                <a 
                                    href={appStoreUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 transition-all text-white shadow-sm hover:shadow-md"
                                >
                                    <svg className="w-6 h-6 fill-current text-white shrink-0" viewBox="0 0 384 512">
                                        <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-88.9-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 52.3-14.9 69.5-34.3z" />
                                    </svg>
                                    <div className="text-left">
                                        <div className="text-[10px] uppercase tracking-wider text-slate-400 font-medium leading-none">Download on the</div>
                                        <div className="text-sm font-semibold leading-tight text-white">App Store</div>
                                    </div>
                                </a>

                                {/* Direct Enterprise APK Badge for Rugged Scanners */}
                                <a 
                                    href={apkDownloadUrl}
                                    download
                                    className="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 border border-emerald-500 hover:border-emerald-400 transition-all text-white shadow-sm hover:shadow-md"
                                >
                                    <Download className="w-6 h-6 text-white shrink-0" />
                                    <div className="text-left">
                                        <div className="text-[10px] uppercase tracking-wider text-emerald-100 font-semibold leading-none">Direct Enterprise APK</div>
                                        <div className="text-sm font-bold leading-tight text-white">Zebra & Sunmi POS</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Hardware Ecosystem Compatibility Strip */}
                <div className="p-6 rounded-2xl bg-slate-800/40 border border-slate-700/60 backdrop-blur-sm">
                    <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <div className="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <Server className="w-5 h-5" />
                            </div>
                            <div>
                                <h4 className="text-sm font-semibold text-white">Certified Terminal Compatibility</h4>
                                <p className="text-xs text-slate-400">Tested and validated by Wolinet Technology Ltd hardware lab</p>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {compatibleDevices.map((device, idx) => (
                                <span 
                                    key={idx}
                                    className="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-700/50 text-slate-300 border border-slate-600/50"
                                >
                                    {device}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
