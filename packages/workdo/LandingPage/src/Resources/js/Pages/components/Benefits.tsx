import { useState } from 'react';
import { ChevronDown, CheckCircle, ArrowRight } from 'lucide-react';

interface BenefitsProps {
    settings?: any;
}

const BENEFITS_VARIANTS = {
    benefits1: {
        section: 'bg-white py-20',
        container: 'max-w-4xl mx-auto px-4 sm:px-6 lg:px-8',
        title: 'text-3xl md:text-4xl font-bold text-slate-900 mb-12 text-center',
        layout: 'accordion'
    },
    benefits2: {
        section: 'bg-gray-50 py-20',
        container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
        title: 'text-3xl md:text-4xl font-bold text-slate-900 mb-12 text-center',
        layout: 'cards'
    },
    benefits3: {
        section: 'bg-white py-20',
        container: 'max-w-5xl mx-auto px-4 sm:px-6 lg:px-8',
        title: 'text-3xl md:text-4xl font-bold text-slate-900 mb-12 text-center',
        layout: 'list'
    },
    benefits4: {
        section: 'bg-gray-900 py-20',
        container: 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8',
        title: 'text-3xl md:text-4xl font-bold text-white mb-12 text-center',
        layout: 'timeline'
    },
    benefits5: {
        section: 'bg-white py-20',
        container: 'max-w-6xl mx-auto px-4 sm:px-6 lg:px-8',
        title: 'text-3xl md:text-4xl font-bold text-slate-900 mb-12 text-center',
        layout: 'tabs'
    }
};

export default function Benefits({ settings }: BenefitsProps) {
    const sectionData = settings?.config_sections?.sections?.benefits || {};
    const variant = sectionData.variant || 'benefits1';
    const config = BENEFITS_VARIANTS[variant as keyof typeof BENEFITS_VARIANTS] || BENEFITS_VARIANTS.benefits1;
    
    const title = sectionData.title || 'Engineered for Mutamark Operational Excellence';
    const colors = settings?.config_sections?.colors || { primary: '#10b981', secondary: '#059669', accent: '#f59e0b' };
    const [openAccordion, setOpenAccordion] = useState(0);
    const [activeTab, setActiveTab] = useState(0);
    
    const defaultBenefits = [
        { 
            title: 'Multi-Warehouse & WMS Logistics', 
            description: 'Real-time stock visibility across central hub and regional branch warehouses with serial numbers, batch tracking, stock transfers, and automated inventory adjustments.' 
        },
        { 
            title: 'Integrated Procurement & 3-Way Match', 
            description: 'Seamless workflow from Purchase Requisitions (PR) and RFQs to PO generation and tolerance-based 3-way matching against Goods Received Notes (GRN).' 
        },
        { 
            title: 'TRA EFD / VFD Fiscalization', 
            description: 'Direct real-time integration with Tanzania Revenue Authority for instant fiscal receipt generation, QR code verification, and automated daily Z-Reports.' 
        },
        { 
            title: 'Tanzanian Statutory Payroll', 
            description: 'Automated salary and deduction calculations for NSSF (10%/10%), WCF (0.5%), NHIF (3%/3%), and graduated monthly PAYE tax bands with bank and mobile money disbursements.' 
        },
        { 
            title: 'Multi-Channel Payment Gateways', 
            description: 'Instant customer payment processing via Lipa Namba (M-Pesa / Tigo Pesa), Selcom, Pesapal, and verified bank transfers with automated cashier standee QR codes.' 
        },
        { 
            title: 'Enterprise Audit & Security Ledger', 
            description: 'Cryptographically hashed document verification ledgers, role-based access control, approval workflows, and comprehensive financial audit trails.' 
        }
    ];
    
    const benefits = sectionData.benefits?.length > 0 ? sectionData.benefits : defaultBenefits;

    const renderAccordion = () => (
        <div className="space-y-4">
            {benefits.map((benefit: any, index: number) => (
                <div key={index} className="border border-gray-200 rounded-lg overflow-hidden">
                    <button
                        onClick={() => setOpenAccordion(openAccordion === index ? -1 : index)}
                        className="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors"
                    >
                        <span className="font-semibold text-slate-900">{benefit.title}</span>
                        <ChevronDown className={`h-5 w-5 text-gray-500 transition-transform ${openAccordion === index ? 'rotate-180' : ''}`} />
                    </button>
                    {openAccordion === index && (
                        <div className="px-6 pb-4 text-slate-700">
                            {benefit.description}
                        </div>
                    )}
                </div>
            ))}
        </div>
    );

    const renderCards = () => (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {benefits.map((benefit: any, index: number) => (
                <div key={index} className="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                    <div className="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style={{ backgroundColor: colors.primary }}>
                        <CheckCircle className="h-6 w-6 text-white" />
                    </div>
                    <h3 className="text-xl font-semibold text-slate-900 mb-3">{benefit.title}</h3>
                    <p className="text-slate-700">{benefit.description}</p>
                </div>
            ))}
        </div>
    );

    const renderList = () => (
        <div className="space-y-6">
            {benefits.map((benefit: any, index: number) => (
                <div key={index} className="flex items-start gap-4 p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div className="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center" style={{ backgroundColor: colors.primary }}>
                        <span className="text-white font-bold text-sm">{index + 1}</span>
                    </div>
                    <div>
                        <h3 className="text-xl font-semibold text-slate-900 mb-2">{benefit.title}</h3>
                        <p className="text-slate-700">{benefit.description}</p>
                    </div>
                </div>
            ))}
        </div>
    );

    const renderTimeline = () => (
        <div className="relative">
            <div className="absolute start-8 top-0 bottom-0 w-0.5 bg-gray-600"></div>
            <div className="space-y-8">
                {benefits.map((benefit: any, index: number) => (
                    <div key={index} className="relative flex items-start gap-6">
                        <div className="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center" style={{ backgroundColor: colors.primary }}>
                            <CheckCircle className="h-8 w-8 text-white" />
                        </div>
                        <div className="bg-gray-800 p-6 rounded-lg flex-1">
                            <h3 className="text-xl font-semibold text-white mb-3">{benefit.title}</h3>
                            <p className="text-gray-300">{benefit.description}</p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );

    const renderTabs = () => (
        <div>
            <div className="flex flex-wrap justify-center mb-8 border-b">
                {benefits.map((benefit: any, index: number) => (
                    <button
                        key={index}
                        onClick={() => setActiveTab(index)}
                        className={`px-6 py-3 font-medium text-sm border-b-2 transition-colors ${
                            activeTab === index
                                ? 'text-white rounded-t-lg'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        }`}
                        style={activeTab === index ? { backgroundColor: colors.primary, borderColor: colors.primary } : {}}
                    >
                        {benefit.title}
                    </button>
                ))}
            </div>
            <div className="text-center">
                <div className="bg-gray-50 p-8 rounded-lg">
                    <h3 className="text-2xl font-semibold text-slate-900 mb-4">{benefits[activeTab]?.title}</h3>
                    <p className="text-lg text-slate-700">{benefits[activeTab]?.description}</p>
                </div>
            </div>
        </div>
    );

    const renderContent = () => {
        switch (config.layout) {
            case 'cards':
                return renderCards();
            case 'list':
                return renderList();
            case 'timeline':
                return renderTimeline();
            case 'tabs':
                return renderTabs();
            default:
                return renderAccordion();
        }
    };

    return (
        <section className={config.section}>
            <div className={config.container}>
                <h2 className={config.title}>{title}</h2>
                {renderContent()}
            </div>
        </section>
    );
}