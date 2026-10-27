import React, { useState } from 'react';
import { Link, usePage, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { route } from 'ziggy-js';
import { formatCurrency } from '@/utils/helpers';
import Layout from '../../Components/Frontend/Layout';
import { ChevronRight, ArrowRight, ArrowLeft } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface MobileService {
    id: number;
    name: string;
}

interface DeviceBrand {
    id: number;
    name: string;
}

interface BookingProps {
    mobileServices: MobileService[];
    deviceBrands: DeviceBrand[];
    mobileServiceCustomPages?: any[];
}

const Booking = () => {
    const { t } = useTranslation();
    const { mobileServices = [], deviceBrands = [], mobileServiceCustomPages = [] } = usePage<BookingProps>().props;
    
    const termsPage = mobileServiceCustomPages.find(page => 
        page.type === 'terms' || 
        page.name?.toLowerCase().includes('terms') || 
        page.name?.toLowerCase().includes('condition')
    );
    const privacyPage = mobileServiceCustomPages.find(page => 
        page.type === 'privacy' || 
        page.name?.toLowerCase().includes('privacy')
    );
    const { url } = usePage();
    const [currentStep, setCurrentStep] = useState(1);
    const [errors, setErrors] = useState<{[key: string]: string}>({});
    
    const pathParts = url.split('/');
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;
    
    // Get service ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const preSelectedService = urlParams.get('service') || '';
    
    const { data, setData, post, processing, reset } = useForm({
        serviceType: preSelectedService,
        deviceBrand: '',
        deviceModel: '',
        urgency: 'standard',
        issueDescription: '',
        attachments: [],
        firstName: '',
        email: '',
        phone: '',
        address: '',
        specialRequests: '',
        termsAgreed: false
    });

    const nextStep = () => {
        const newErrors: {[key: string]: string} = {};
        if (currentStep === 1) {
            
            if (!data.serviceType) newErrors.serviceType = 'Please select a service.';
            if (!data.deviceBrand) newErrors.deviceBrand = 'Please select a device brand.';
            if (!data.deviceModel) newErrors.deviceModel = 'Please enter device model.';
            if (!data.issueDescription) newErrors.issueDescription = 'Please describe the issue.';
            
            setErrors(newErrors);
            
            if (Object.keys(newErrors).length === 0) {
                setCurrentStep(currentStep + 1);
            }
        } else if (currentStep === 2) {

            if (!data.firstName) newErrors.firstName = 'Please enter your full name.';
            if (!data.email) {
                newErrors.email = 'Please enter your email address.';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
                newErrors.email = t('Please enter a valid email address');
            }
            if (!data.phone) {
                newErrors.phone = 'Please enter your phone number.';
            } else if (!/^\+\d{9,15}$/.test(data.phone)) {
                newErrors.phone = t('Phone number must start with + followed by 9-15 digits (e.g., +945875565)');
            }
            if (!data.address) newErrors.address = 'Please enter your address.';
            
            setErrors(newErrors);
            
            if (Object.keys(newErrors).length === 0) {
                setCurrentStep(currentStep + 1);
            }
            
        } else if (currentStep < 3) {
            setCurrentStep(currentStep + 1);
        }
    };

    const prevStep = () => {
        if (currentStep > 1) setCurrentStep(currentStep - 1);
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const { name, value, type } = e.target;
        let processedValue = type === 'checkbox' ? (e.target as HTMLInputElement).checked : value;
        
        setData(name as keyof typeof data, processedValue);
        
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

        if (name === 'phone' && value.trim()) {
            if (!/^\+\d{9,15}$/.test(value)) {
                newErrors.phone = t('Phone number must start with + followed by 9-15 digits (e.g., +945875565)');
            }
        }

        if (name === 'email' && value.trim()) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                newErrors.email = t('Please enter a valid email address');
            }
        }

        setErrors(newErrors);
    };
    
    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files || []);
        setData('attachments', [...data.attachments, ...files]);
    };

    const removeFile = (index: number) => {
        const updatedFiles = data.attachments.filter((_, i) => i !== index);
        setData('attachments', updatedFiles);
    };

    const submitForm = () => {
        const routeParams = userSlug ? { userSlug } : {};
        post(route('mobile-service.frontend.booking.store', routeParams), {
            onSuccess: () => {
                reset();
                setCurrentStep(1);
            }
        });
    };

    return (
        <Layout title={t('Book Service')}>
            <section className="relative" style={{backgroundColor: '#33312e', padding: '4rem 0'}}>
                <div className="container mx-auto px-4">
                    <div className="text-center text-white">
                        <h2 className="text-4xl font-bold mb-4">
                            {t('Schedule Your')} <span style={{color: '#c77d1f'}}>{t('Repair Today')}</span>
                        </h2>
                        <nav className="flex justify-center items-center text-sm">
                            <Link href={route('mobile-service.frontend.index', { userSlug: usePage().url.split('/')[1] })}>{t('Home')}</Link>
                            <ChevronRight className="w-4 h-4 mx-2" />
                            <span>{t('Booking')}</span>
                        </nav>
                    </div>
                </div>
            </section>

            <section className="py-16 bg-white">
                <div className="container mx-auto px-4">
                    <div className="max-w-4xl mx-auto bg-white rounded-lg shadow-lg">
                        <div className="p-6" style={{backgroundColor: '#e6ded6'}}>
                            <div className="flex justify-between items-center">
                                {[1, 2, 3].map((step) => (
                                    <div key={step} className="flex flex-col items-center">
                                        <div className={`w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold ${
                                            currentStep >= step ? 'bg-orange-500' : 'bg-gray-400'
                                        }`} style={currentStep >= step ? {backgroundColor: '#c77d1f'} : {}}>
                                            {step}
                                        </div>
                                        <span className="text-xs mt-1">
                                            {step === 1 ? t('Service') : step === 2 ? t('Details') : t('Confirm')}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="p-8">
                            {currentStep === 1 && (
                                <div>
                                    <h3 className="text-2xl font-bold mb-6" style={{color: '#33312e'}}>{t('Tell Us About Your Device')}</h3>
                                    <div className="space-y-6">
                                        <div>
                                            <Label className="block font-medium mb-2" required>{t('Service Type')}</Label>
                                            <Select value={data.serviceType} onValueChange={(value) => setData('serviceType', value)} required>
                                                <SelectTrigger className="w-full p-3 border rounded-lg">
                                                    <SelectValue placeholder={t('Select a service...')} />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {mobileServices.map((service) => (
                                                        <SelectItem key={service.id} value={service.id.toString()}>{service.name}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.serviceType && <p className="text-red-500 text-sm mt-1">{errors.serviceType}</p>}
                                        </div>
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <Label className="block font-medium mb-2" required>{t('Device Brand')}</Label>
                                                <Select value={data.deviceBrand} onValueChange={(value) => setData('deviceBrand', value)} required>
                                                    <SelectTrigger className="w-full p-3 border rounded-lg">
                                                        <SelectValue placeholder={t('Select brand...')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {deviceBrands.map((brand) => (
                                                            <SelectItem key={brand.id} value={brand.id.toString()}>{brand.name}</SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                                {errors.deviceBrand && <p className="text-red-500 text-sm mt-1">{errors.deviceBrand}</p>}
                                            </div>
                                            <div>
                                                <Label className="block font-medium mb-2" required>{t('Device Model')}</Label>
                                                <Input name="deviceModel" type="text" placeholder="e.g., iPhone 14 Pro"
                                                    value={data.deviceModel} onChange={handleInputChange}
                                                    className="w-full p-3 border rounded-lg" required />
                                                {errors.deviceModel && <p className="text-red-500 text-sm mt-1">{errors.deviceModel}</p>}
                                            </div>
                                        </div>
                                        <div>
                                            <Label className="block font-medium mb-3">{t('Urgency')}</Label>
                                            <div className="grid grid-cols-3 gap-4">
                                                {[
                                                    {value: 'standard', label: 'Standard', desc: '2-3 days'},
                                                    { value: 'priority', label: 'Priority', desc: `Next day (+${formatCurrency('26')})` },
                                                    { value: 'emergency', label: 'emergency', desc: `Same day (+${formatCurrency('50')})` },
                                                ].map((option) => (
                                                    <label key={option.value} className="border rounded-lg p-4 cursor-pointer">
                                                        <input type="radio" name="urgency" value={option.value}
                                                            checked={data.urgency === option.value} onChange={handleInputChange}
                                                            className="mr-3" />
                                                        <div>
                                                            <h4 className="font-semibold">{option.label}</h4>
                                                            <p className="text-sm text-gray-600">{option.desc}</p>
                                                        </div>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                        <div>
                                            <Label className="block font-medium mb-2" required>{t('Issue Description')}</Label>
                                            <Textarea name="issueDescription" value={data.issueDescription} onChange={handleInputChange}
                                                className="w-full p-3 border rounded-lg" rows={3} 
                                                placeholder="What's wrong with your device?" required />
                                            {errors.issueDescription && <p className="text-red-500 text-sm mt-1">{errors.issueDescription}</p>}
                                        </div>
                                        <div>
                                            <Label className="block font-medium mb-2">{t('Attachments (Optional)')}</Label>
                                            <Input type="file" onChange={handleFileChange}
                                                className="w-full p-3 border rounded-lg" 
                                                accept="image/*,.pdf,.doc,.docx"
                                                multiple
                                                placeholder="Upload photos or documents" />
                                            <p className="text-sm text-gray-500 mt-1">{t('Upload multiple photos of the issue or relevant documents')}</p>
                                            {data.attachments.length > 0 && (
                                                <div className="mt-3 space-y-2">
                                                    {data.attachments.map((file, index) => (
                                                        <div key={index} className="flex items-center justify-between bg-gray-50 p-2 rounded">
                                                            <span className="text-sm truncate">{file.name}</span>
                                                            <button type="button" onClick={() => removeFile(index)} 
                                                                className="text-red-500 hover:text-red-700 text-sm ml-2">
                                                                {t('Remove')}
                                                            </button>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex justify-end mt-6">
                                        <button onClick={nextStep} className="inline-flex items-center gap-2 px-6 py-3 text-white rounded-lg"
                                            style={{backgroundColor: '#c77d1f'}}>
                                            {t('Continue')}
                                            <ArrowRight className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            )}

                            {currentStep === 2 && (
                                <div>
                                    <h3 className="text-2xl font-bold mb-6" style={{color: '#33312e'}}>{t('Contact Information')}</h3>
                                    <div className="space-y-6">
                                        <div>
                                            <Label className="block font-medium mb-2" required>{t('Full Name')}</Label>
                                            <Input name="firstName" type="text" value={data.firstName} onChange={handleInputChange}
                                                placeholder="Enter your full name"
                                                className="w-full p-3 border rounded-lg" />
                                            {errors.firstName && <p className="text-red-500 text-sm mt-1">{errors.firstName}</p>}
                                        </div>
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <Label className="block font-medium mb-2" required>{t('Email')}</Label>
                                                <Input name="email" type="email" value={data.email} onChange={handleInputChange}
                                                    onBlur={handleBlur}
                                                    placeholder="Enter your email address"
                                                    className={`w-full p-3 border rounded-lg ${errors.email ? 'border-red-500' : 'border-gray-300'}`} required />
                                                {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                                            </div>
                                            <div>
                                                <Label className="block font-medium mb-2" required>{t('Phone')}</Label>
                                                <Input name="phone" type="tel" value={data.phone} onChange={handleInputChange}
                                                    onBlur={handleBlur}
                                                    placeholder={t('Enter phone number (e.g., +945875565)')}
                                                    className={`w-full p-3 border rounded-lg ${errors.phone ? 'border-red-500' : 'border-gray-300'}`} required />
                                                {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone}</p>}
                                            </div>
                                        </div>
                                        <div>
                                            <Label className="block font-medium mb-2" required>{t('Address')}</Label>
                                            <Textarea name="address" value={data.address} onChange={handleInputChange}
                                                placeholder="Enter your address"
                                                className="w-full p-3 border rounded-lg" rows={3} required />
                                            {errors.address && <p className="text-red-500 text-sm mt-1">{errors.address}</p>}
                                        </div>
                                    </div>
                                    <div className="flex justify-between mt-6">
                                        <button onClick={prevStep} className="inline-flex items-center gap-2 px-6 py-3 border rounded-lg"
                                            style={{borderColor: '#33312e', color: '#33312e'}}>
                                            <ArrowLeft className="w-4 h-4" />
                                            {t('Back')}
                                        </button>
                                        <button onClick={nextStep} className="inline-flex items-center gap-2 px-6 py-3 text-white rounded-lg"
                                            style={{backgroundColor: '#c77d1f'}}>
                                            {t('Continue')}
                                            <ArrowRight className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            )}

                            {currentStep === 3 && (
                                <div>
                                    <h3 className="text-2xl font-bold mb-6" style={{color: '#33312e'}}>{t('Review Your Booking')}</h3>
                                    <div className="grid grid-cols-3 gap-6">
                                        <div className="col-span-2 space-y-6">
                                            <div className="bg-gray-50 p-6 rounded-lg h-fit">
                                                <h4 className="font-bold mb-4">{t('Service Summary')}</h4>
                                                <div className="space-y-3">
                                                    <div>
                                                        <div className="text-gray-600">{t('Service')}:</div>
                                                        <div className="font-bold">{mobileServices.find(s => s.id.toString() === data.serviceType)?.name || data.serviceType}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-gray-600">{t('Device')}:</div>
                                                        <div className="font-bold">{deviceBrands.find(b => b.id.toString() === data.deviceBrand)?.name || data.deviceBrand} {data.deviceModel}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-gray-600">{t('Urgency')}:</div>
                                                        <div className="font-bold">{data.urgency}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-gray-600">{t('Issue')}:</div>
                                                        <div className="font-bold">{data.issueDescription}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="bg-gray-50 p-6 rounded-lg h-fit">
                                                <h4 className="font-bold mb-4">{t('Contact Info')}</h4>
                                                <div className="space-y-3">
                                                    <div>
                                                        <div className="text-gray-600">{t('Name')}:</div>
                                                        <div className="font-bold">{data.firstName}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-gray-600">{t('Email')}:</div>
                                                        <div className="font-bold">{data.email}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-gray-600">{t('Phone')}:</div>
                                                        <div className="font-bold">{data.phone}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-gray-600">{t('Address')}:</div>
                                                        <div className="font-bold">{data.address}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="space-y-6">
                                            <div className="bg-gray-50 p-6 rounded-lg h-fit">
                                                <h4 className="font-bold mb-4">{t('Pricing')}</h4>
                                                <div className="space-y-2">
                                                    <div className="flex justify-between">
                                                        <span>{t('Base Fee')}</span>
                                                        <span>{formatCurrency(99)}</span>
                                                    </div>
                                                    {data.urgency === 'priority' && (
                                                        <div className="flex justify-between">
                                                            <span>{t('Priority')}</span>
                                                            <span>{formatCurrency(25)}</span>
                                                        </div>
                                                    )}
                                                    {data.urgency === 'emergency' && (
                                                        <div className="flex justify-between">
                                                            <span>{t('Emergency')}</span>
                                                            <span>{formatCurrency(50)}</span>
                                                        </div>
                                                    )}
                                                    <hr />
                                                    <div className="flex justify-between font-bold">
                                                        <span>{t('Total')}</span>
                                                        <span style={{color: '#c77d1f'}}>
                                                            {formatCurrency(99 + (data.urgency === 'priority' ? 25 : data.urgency === 'emergency' ? 50 : 0))}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="bg-gray-50 p-6 rounded-lg h-fit">
                                                <h4 className="font-bold mb-4">{t('Special Requests')}</h4>
                                                <Textarea name="specialRequests" value={data.specialRequests} onChange={handleInputChange}
                                                    placeholder="Special requests..." className="w-full p-3 border rounded-lg" rows={3} />
                                                <label className="flex items-start mt-4">
                                                    <input type="checkbox" name="termsAgreed" checked={data.termsAgreed} onChange={handleInputChange}
                                                        className="mt-1 mr-3" required />
                                                    <span className="text-sm text-gray-600 cursor-pointer">
                                                        {t('I agree to the')} {' '}
                                                        {termsPage ? (
                                                            <Link href={route('mobile-service.frontend.custom-page', { userSlug, slug: termsPage.slug })} className="hover:underline" style={{ color: '#c77d1f' }}>{t('Terms and Conditions')}</Link>
                                                        ) : (
                                                            <span style={{ color: '#c77d1f' }}>{t('Terms and Conditions')}</span>
                                                        )}
                                                        {' & '}
                                                        {privacyPage ? (
                                                            <Link href={route('mobile-service.frontend.custom-page', { userSlug, slug: privacyPage.slug })} className="hover:underline" style={{ color: '#c77d1f' }}>{t('Privacy Policy')}</Link>
                                                        ) : (
                                                            <span style={{ color: '#c77d1f' }}>{t('Privacy Policy')}</span>
                                                        )}
                                                        . {t('I understand that this is an estimate and final pricing may vary.')}
                                                    </span>
                                                </label>
                                                <button onClick={submitForm} disabled={processing || !data.termsAgreed} className="w-full mt-4 py-3 text-white rounded-lg disabled:opacity-50"
                                                    style={{backgroundColor: '#c77d1f'}}>
                                                    {processing ? 'Submitting...' : 'Confirm & Book Repair'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex justify-start mt-6">
                                        <button onClick={prevStep} className="inline-flex items-center gap-2 px-6 py-3 border rounded-lg"
                                            style={{borderColor: '#33312e', color: '#33312e'}}>
                                            <ArrowLeft className="w-4 h-4" />
                                            {t('Back')}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </Layout>
    );
};

export default Booking;