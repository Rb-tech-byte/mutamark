import React, { useEffect, useState } from 'react';
import { Link, usePage, useForm } from '@inertiajs/react';
import Layout from '../../Components/Frontend/Layout';
import { ChevronRight, Send, Circle, MapPin, Clock, Phone, Mail } from 'lucide-react';
import SocialLinks from '@/components/SocialLinks';
import { useTranslation } from 'react-i18next';
import { formatTime } from '@/utils/helpers';
import { route } from 'ziggy-js';

interface ContactInfo {
    contact_phone?: string;
    contact_email?: string;
    contact_emergency?: string;
    location_address?: string;
    service_hours?: any;
    repair_center_description?: string;
    repair_center_title?: string;
    repair_center_map_code?: string;
}

interface ContactProps {
    contactInfo: ContactInfo;
    socialLinks: any[];
    flash?: {
        success?: string;
        error?: string;
    };
}


const Contact = () => {
    const { contactInfo = {}, socialLinks = [], flash } = usePage<ContactProps>().props;
    const { t } = useTranslation();
    const { url } = usePage();
    
    const pathParts = url.split('/');
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;
    
    const [errors, setErrors] = useState<{[key: string]: string}>({});
    
    const { data, setData, post, processing, reset } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        repair_type: '',
        message: ''
    });
    
    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setData(name as keyof typeof data, value);
        
        // Clear error when user starts typing
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };
    
    const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        const newErrors = { ...errors };

        if (name === 'email' && value.trim()) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                newErrors.email = t('Please enter a valid email format (e.g., test@example.com)');
            }
        }

        if (name === 'phone' && value.trim()) {
            if (!/^\+\d{9,15}$/.test(value)) {
                newErrors.phone = t('Phone number must start with + followed by 9-15 digits (e.g., +945875565)');
            }
        }

        setErrors(newErrors);
    };
    
    const validateForm = () => {
        const newErrors: { [key: string]: string } = {};
        let firstErrorField = '';

        if (!data.first_name.trim()) {
            newErrors.first_name = t('First name is required');
            if (!firstErrorField) firstErrorField = 'first_name';
        }
        
        if (!data.last_name.trim()) {
            newErrors.last_name = t('Last name is required');
            if (!firstErrorField) firstErrorField = 'last_name';
        }

        if (!data.email.trim()) {
            newErrors.email = t('Email is required');
            if (!firstErrorField) firstErrorField = 'email';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
            newErrors.email = t('Please enter a valid email format (e.g., test@example.com)');
            if (!firstErrorField) firstErrorField = 'email';
        }

        if (data.phone.trim() && !/^\+\d{9,15}$/.test(data.phone)) {
            newErrors.phone = t('Phone number must start with + followed by 9-15 digits (e.g., +945875565)');
            if (!firstErrorField) firstErrorField = 'phone';
        }
        
        if (!data.repair_type) {
            newErrors.repair_type = t('Please select a repair type');
            if (!firstErrorField) firstErrorField = 'repair_type';
        }
        
        if (!data.message.trim()) {
            newErrors.message = t('Message is required');
            if (!firstErrorField) firstErrorField = 'message';
        }

        setErrors(newErrors);

        // Focus on first error field
        if (firstErrorField) {
            setTimeout(() => {
                const element = document.getElementById(firstErrorField);
                if (element) {
                    element.focus();
                }
            }, 100);
        }

        return Object.keys(newErrors).length === 0;
    };
    
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        const routeParams = userSlug ? { userSlug } : {};
        post(route('mobile-service.frontend.contact.store', routeParams), {
            onSuccess: () => reset()
        });
    };
    return (
        <Layout title={`${t('Contact Us')} | ${t('Mobile Service Management')}`} activeNav="contact">
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
                            {t('Contact Us')}
                        </span>
                        <h2 className="text-2xl md:text-3xl lg:text-5xl font-bold mb-4 lg:mb-6 leading-tight text-white">
                            {t('Contact')}<span style={{ color: '#c77d1f' }}> {t('Us')}</span>
                        </h2>
                        <ul className="flex flex-wrap justify-center items-center capitalize text-white md:text-base text-sm">
                            <li className="capitalize flex items-center">
                                <Link href={route('mobile-service.frontend.index', { userSlug })}>{t('Home')}</Link>
                            </li>
                            <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                            <li className="capitalize flex items-center">{t('Contact Us')}</li>
                        </ul>
                    </div>
                </div>
            </section>

            {/* Contact Section */}
            <section className="py-10 lg:py-16" style={{ backgroundColor: '#e6ded6' }}>
                <div className="md:container mx-auto px-4 w-full">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        {/* Contact Form */}
                        <div className="bg-white py-6 px-4 md:p-8 rounded-lg border border-gray-200">
                            <h3 className="text-xl md:text-2xl font-bold title-font mb-4 md:mb-6" style={{ color: '#33312e' }}>{t('Get in touch')}</h3>
                            <form onSubmit={handleSubmit} className="space-y-4" noValidate>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label className="block text-sm font-medium text-black mb-1">{t('First Name')}</label>
                                        <input 
                                            type="text" 
                                            id="first_name"
                                            name="first_name"
                                            value={data.first_name}
                                            onChange={handleInputChange}
                                            placeholder={t('First Name')} 
                                            className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 ${errors.first_name ? 'border-red-500' : 'border-gray-300'}`}
                                            required
                                        />
                                        {errors.first_name && <p className="text-red-500 text-sm mt-1">{errors.first_name}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-black mb-1">{t('Last Name')}</label>
                                        <input 
                                            type="text" 
                                            id="last_name"
                                            name="last_name"
                                            value={data.last_name}
                                            onChange={handleInputChange}
                                            placeholder={t('Last Name')} 
                                            className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 ${errors.last_name ? 'border-red-500' : 'border-gray-300'}`}
                                            required
                                        />
                                        {errors.last_name && <p className="text-red-500 text-sm mt-1">{errors.last_name}</p>}
                                    </div>
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label className="block text-sm font-medium text-black mb-1">{t('Email')}</label>
                                        <input 
                                            type="email" 
                                            id="email"
                                            name="email"
                                            value={data.email}
                                            onChange={handleInputChange}
                                            onBlur={handleBlur}
                                            placeholder={t('Email')} 
                                            className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 ${errors.email ? 'border-red-500' : 'border-gray-300'}`}
                                            required
                                        />
                                        {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-black mb-1">{t('Phone')}</label>
                                        <input 
                                            type="tel" 
                                            id="phone"
                                            name="phone"
                                            value={data.phone}
                                            onChange={handleInputChange}
                                            onBlur={handleBlur}
                                            placeholder={t('Enter phone number (e.g., +945875565)')} 
                                            className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 ${errors.phone ? 'border-red-500' : 'border-gray-300'}`}
                                        />
                                        {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone}</p>}
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-black mb-1">{t('Repair Type')}</label>
                                    <select 
                                        id="repair_type"
                                        name="repair_type"
                                        value={data.repair_type}
                                        onChange={handleInputChange}
                                        className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 ${errors.repair_type ? 'border-red-500' : 'border-gray-300'}`}
                                        required
                                    >
                                        <option value="">{t('Select repair type...')}</option>
                                        <option value="screen_replacement">{t('Screen Replacement')}</option>
                                        <option value="battery_replacement">{t('Battery Replacement')}</option>
                                        <option value="water_damage">{t('Water Damage')}</option>
                                        <option value="software_issue">{t('Software Issue')}</option>
                                        <option value="other">{t('Other')}</option>
                                    </select>
                                    {errors.repair_type && <p className="text-red-500 text-sm mt-1">{errors.repair_type}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-black mb-1">{t('Message')}</label>
                                    <textarea 
                                        id="message"
                                        name="message"
                                        value={data.message}
                                        onChange={handleInputChange}
                                        rows={5} 
                                        className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 ${errors.message ? 'border-red-500' : 'border-gray-300'}`}
                                        placeholder={t('Tell us about your device issue...')}
                                        required
                                    ></textarea>
                                    {errors.message && <p className="text-red-500 text-sm mt-1">{errors.message}</p>}
                                </div>
                                <button 
                                    type="submit" 
                                    disabled={processing}
                                    className="w-full inline-flex items-center justify-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-amber-900 hover:border-amber-900 disabled:opacity-50" 
                                    style={{ backgroundColor: '#c77d1f', color: 'white', border: '1px solid #c77d1f' }}
                                >
                                    <Send className="w-4 h-4 me-2" /> {processing ? t('Sending...') : t('Send Message')}
                                </button>
                            </form>
                        </div>

                        {/* Contact Info */}
                        <div className="space-y-6">
                            <div className="bg-white py-6 px-4 md:p-8 rounded-lg border border-gray-200">
                                <h3 className="text-xl md:text-2xl font-bold title-font mb-4 md:mb-6" style={{ color: '#33312e' }}>{t('Contact Information')}</h3>

                                <div className="space-y-6">
                                    {contactInfo.location_address && (
                                        <div className="flex items-start">
                                            <div className="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center text-white me-4 rounded-lg flex-shrink-0" style={{ backgroundColor: '#33312e' }}>
                                                <MapPin className="w-4 h-4 md:w-5 md:h-5" />
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-lg mb-1" style={{ color: '#33312e' }}>{t('Our Location')}</h4>
                                                <p className="text-gray-600">{contactInfo.location_address}</p>
                                            </div>
                                        </div>
                                    )}

                                    {contactInfo.service_hours && Object.keys(contactInfo.service_hours).length > 0 && (
                                        <div className="flex items-start">
                                            <div className="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center text-white me-4 rounded-lg flex-shrink-0" style={{ backgroundColor: '#33312e' }}>
                                                <Clock className="w-4 h-4 md:w-5 md:h-5" />
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-lg mb-1" style={{ color: '#33312e' }}>{t('Service Hours')}</h4>
                                                <p className="text-gray-600">
                                                    {Object.entries(contactInfo.service_hours)
                                                        .filter(([day, hours]: [string, any]) => hours && (hours.status === 'closed' || (hours.opening_time && hours.closing_time)))
                                                        .map(([day, hours]: [string, any]) => (
                                                            <span key={day} className="block">
                                                                {day.charAt(0).toUpperCase() + day.slice(1)}: {hours.status === 'closed' ? t('Closed') : `${formatTime(hours.opening_time)} - ${formatTime(hours.closing_time)}`}<br />
                                                            </span>
                                                        ))}
                                                </p>
                                            </div>
                                        </div>
                                    )}

                                    {contactInfo.contact_phone && (
                                        <div className="flex items-start">
                                            <div className="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center text-white me-4 rounded-lg flex-shrink-0" style={{ backgroundColor: '#33312e' }}>
                                                <Phone className="w-4 h-4 md:w-5 md:h-5" />
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-lg mb-1" style={{ color: '#33312e' }}>{t('Contact Info')}</h4>
                                                <p className="text-gray-600">
                                                    {t('Phone')}: <a href={`tel:${contactInfo.contact_phone}`} className="hover:text-orange-600 transition-colors">{contactInfo.contact_phone}</a><br />
                                                    {contactInfo.contact_email && (
                                                        <>{t('Email')}: <a href={contactInfo.contact_email} className="hover:text-orange-600 transition-colors">{contactInfo.contact_email.replace('mailto:', '')}</a><br /></>
                                                    )}
                                                    {contactInfo.contact_emergency && (
                                                        <>{t('Emergency')}: <a href={`tel:${contactInfo.contact_emergency}`} className="hover:text-orange-600 transition-colors">{contactInfo.contact_emergency}</a></>
                                                    )}
                                                </p>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {socialLinks.length > 0 && (
                                <div className="bg-white py-6 px-4 md:p-8 rounded-lg border border-gray-200">
                                    <h3 className="text-xl md:text-2xl font-bold title-font mb-4 md:mb-6" style={{ color: '#33312e' }}>{t('Follow Us')}</h3>
                                    <div className="flex gap-3">
                                        {socialLinks.map((social, index) => (
                                            <a
                                                key={index}
                                                href={social.url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="w-8 h-8 md:w-10 md:h-10 rounded-lg text-white flex items-center justify-center transition-colors hover:bg-orange-500"
                                                style={{ backgroundColor: '#33312e' }}
                                            >
                                                <SocialLinks icon={social.icon} className="w-4 h-4 text-white" />
                                            </a>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* Map Section */}
            {contactInfo.repair_center_map_code && (
                <section className="py-10 lg:py-16 bg-white">
                    <div className="md:container mx-auto px-4 w-full">
                        <div className="text-center mb-6 md:mb-10">
                            <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4" style={{ color: '#33312e' }}>
                                {contactInfo.repair_center_title}
                            </h2>
                            <p className="lg:text-lg text-gray-600 max-w-3xl mx-auto">
                                {contactInfo.repair_center_description }
                            </p>
                        </div>

                        <div className="rounded-2xl overflow-hidden shadow-xl" style={{ height: '400px' }}>
                            <div
                                dangerouslySetInnerHTML={{ __html: contactInfo.repair_center_map_code }}
                                style={{ width: '100%', height: '100%' }}
                            />
                        </div>
                    </div>
                </section>
            )}
        </Layout>
    );
};

export default Contact;