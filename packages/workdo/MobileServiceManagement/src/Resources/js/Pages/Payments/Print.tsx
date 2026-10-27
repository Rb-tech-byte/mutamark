import React, { useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import html2pdf from 'html2pdf.js';
import { formatDate, formatCurrency, getCompanySetting } from '@/utils/helpers';
import { useTranslation } from 'react-i18next';

interface Payment {
    id: number;
    payment_number: string;
    payment_date: string;
    payment_amount: string;
    service_price: string;
    repair_charge?: string;
    tax_amount?: string;
    payment_type?: string;
    payment_status: string;
    description?: string;
    extra_charges?: Array<{text: string; price: string}>;
    mobile_service_request: {
        id: number;
        service_id: string;
        customer_name: string;
        email?: string;
        mobile_no?: string;
    };
    bank_account?: {
        id: number;
        account_name: string;
        account_number: string;
        bank_name: string;
    };
}

interface PageProps {
    payment: Payment;
}

export default function Print() {
    const { t } = useTranslation();
    const { payment } = usePage<PageProps>().props;
    const [isDownloading, setIsDownloading] = useState(false);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('download') === 'pdf') {
            downloadPDF();
        }
    }, []);

    const downloadPDF = async () => {
        setIsDownloading(true);

        const printContent = document.querySelector('.payment-container');
        if (printContent) {
            const opt = {
                margin: 0.25,
                filename: `payment-receipt-${payment.payment_number}.pdf`,
                image: { type: 'jpeg' as const, quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' as const }
            };

            try {
                await html2pdf().set(opt).from(printContent as HTMLElement).save();
                setTimeout(() => window.close(), 1000);
            } catch (error) {
            }
        }

        setIsDownloading(false);
    };

    return (
        <div className="min-h-screen bg-white">
            <Head title={`Payment Receipt - ${payment.payment_number}`} />

            {isDownloading && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white p-6 rounded-lg shadow-lg">
                        <div className="flex items-center space-x-3">
                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <p className="text-lg font-semibold text-gray-700">{t('Generating PDF...')}</p>
                        </div>
                    </div>
                </div>
            )}

            <div className="payment-container bg-white max-w-4xl mx-auto p-12">
                <div className="flex justify-between items-start mb-12">
                    <div className="w-1/2">
                        <h1 className="text-2xl font-bold mb-4">{getCompanySetting('company_name') || 'YOUR COMPANY'}</h1>
                        <div className="text-sm space-y-1">
                            {getCompanySetting('company_address') && <p>{getCompanySetting('company_address')}</p>}
                            {getCompanySetting('company_telephone') && <p>{t('Phone')}: {getCompanySetting('company_telephone')}</p>}
                            {getCompanySetting('company_email') && <p>{t('Email')}: {getCompanySetting('company_email')}</p>}
                        </div>
                    </div>
                    <div className="text-right w-1/2">
                        <h2 className="text-2xl font-bold mb-2">{t('PAYMENT RECEIPT')}</h2>
                        <p className="text-lg font-semibold">#{payment.payment_number}</p>
                        <div className="text-sm mt-2 space-y-1">
                            <p>{t('Date')}: {formatDate(payment.payment_date)}</p>
                            <p>{t('Status')}: <span className="capitalize">{payment.payment_status}</span></p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 className="font-semibold text-lg mb-4">{t('Payment Details')}</h3>
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span className="text-gray-600">{t('Payment Date')}:</span>
                                <span>{formatDate(payment.payment_date)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">{t('Service ID')}:</span>
                                <span>{payment.mobile_service_request.service_id}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">{t('Status')}:</span>
                                <span className="capitalize">{payment.payment_status}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 className="font-semibold text-lg mb-4">{t('Customer Information')}</h3>
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span className="text-gray-600">{t('Name')}:</span>
                                <span>{payment.mobile_service_request.customer_name}</span>
                            </div>
                            {payment.mobile_service_request.email && (
                                <div className="flex justify-between">
                                    <span className="text-gray-600">{t('Email')}:</span>
                                    <span>{payment.mobile_service_request.email}</span>
                                </div>
                            )}
                            {payment.mobile_service_request.mobile_no && (
                                <div className="flex justify-between">
                                    <span className="text-gray-600">{t('Phone')}:</span>
                                    <span>{payment.mobile_service_request.mobile_no}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="border-t border-b py-6 mb-6">
                    <h3 className="font-semibold text-lg mb-4">{t('Amount Breakdown')}</h3>
                    <div className="space-y-3">
                        <div className="flex justify-between">
                            <span>{t('Service Price')}:</span>
                            <span>{formatCurrency(payment.service_price)}</span>
                        </div>
                        {payment.repair_charge && parseFloat(payment.repair_charge) > 0 && (
                            <div className="flex justify-between">
                                <span>{t('Repair Charge')}:</span>
                                <span>{formatCurrency(payment.repair_charge)}</span>
                            </div>
                        )}
                        {payment.tax_amount && parseFloat(payment.tax_amount) > 0 && (
                            <div className="flex justify-between">
                                <span>{t('Tax Amount')}:</span>
                                <span>{formatCurrency(payment.tax_amount)}</span>
                            </div>
                        )}
                        {payment.extra_charges && payment.extra_charges.length > 0 && (
                            payment.extra_charges.map((charge, index) => (
                                <div key={index} className="flex justify-between">
                                    <span>{charge.text}:</span>
                                    <span>{formatCurrency(charge.price)}</span>
                                </div>
                            ))
                        )}
                        <div className="border-t pt-3 flex justify-between font-bold text-lg">
                            <span>{t('Total Amount')}:</span>
                            <span className="text-blue-600">{formatCurrency(payment.payment_amount)}</span>
                        </div>
                    </div>
                </div>

                {payment.description && (
                    <div className="mb-6">
                        <h3 className="font-semibold text-lg mb-2">{t('Description')}</h3>
                        <p className="text-gray-700">{payment.description}</p>
                    </div>
                )}

                {payment.bank_account && (
                    <div className="mb-6">
                        <h3 className="font-semibold text-lg mb-4">{t('Bank Account Details')}</h3>
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span className="text-gray-600">{t('Account Name')}:</span>
                                <span>{payment.bank_account.account_name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">{t('Account Number')}:</span>
                                <span>{payment.bank_account.account_number}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">{t('Bank Name')}:</span>
                                <span>{payment.bank_account.bank_name}</span>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            <style>{`
                body {
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                    font-family: Arial, sans-serif;
                }

                @page {
                    margin: 0.25in;
                    size: A4;
                }

                .payment-container {
                    max-width: 100%;
                    margin: 0;
                    box-shadow: none;
                }

                @media print {
                    body {
                        background: white;
                    }

                    .payment-container {
                        box-shadow: none;
                    }
                }
            `}</style>
        </div>
    );
}