import React, { useState, useEffect } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { getImagePath } from '@/utils/helpers';
import Header from './Header';
import Footer from './Footer';

interface LayoutProps {
    title?: string;
    children: React.ReactNode;
    activeNav?: string;
}

const Layout = ({ title = 'Mobile Service Management', children, activeNav }: LayoutProps) => {
    const { mobileServiceSettings, flash } = usePage().props as any;
    const [toast, setToast] = useState<{ message: string, type: 'success' | 'error' } | null>(null);

    useEffect(() => {
        if (flash?.success) {
            setToast({ message: flash.success, type: 'success' });
            setTimeout(() => setToast(null), 3000);
        }
        if (flash?.error) {
            setToast({ message: flash.error, type: 'error' });
            setTimeout(() => setToast(null), 3000);
        }
    }, [flash]);

    return (
        <>
            <Head title={title}>
                <link
                    rel="icon"
                    href={mobileServiceSettings?.site_settings?.favicon ? getImagePath(mobileServiceSettings.site_settings.favicon) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/favicon.png')}
                />
            </Head>
            
            {/* Toast Notification */}
            {toast && (
                <div className={`fixed top-32 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium ${
                    toast.type === 'success' ? 'bg-[#c77d1f]' : 'bg-red-500'
                }`}>
                    {toast.message}
                </div>
            )}
            
            <Header activeNav={activeNav} />
            {children}
            <Footer />
        </>
    );
};

export default Layout;