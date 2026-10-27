import { useState } from 'react';
import { Head, usePage, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useTranslation } from 'react-i18next';
import { useFlashMessages } from '@/hooks/useFlashMessages';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Card, CardContent } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import InvoiceAging from './InvoiceAging';
import BillAging from './BillAging';
import TaxSummary from './TaxSummary';
import CustomerBalance from './CustomerBalance';
import VendorBalance from './VendorBalance';

interface ReportsIndexProps {
    [key: string]: any;
    accountingControls?: Record<string,string>;
    glAccounts?: { id: number; account_code: string; account_name: string }[];
    auth: {
        user?: {
            permissions?: string[];
        }
    }
    financialYear?: {
        year_start_date: string;
        year_end_date: string;
    }
}

export default function Index() {
    const { t } = useTranslation();
    const { auth, financialYear, accountingControls = {}, glAccounts = [] } = usePage<ReportsIndexProps>().props;
    const controls = useForm({ financial_year_start: financialYear?.year_start_date || '', financial_year_end: financialYear?.year_end_date || '', accounting_locked_until: accountingControls.accounting_locked_until || '', inventory_adjustment_account_id: accountingControls.inventory_adjustment_account_id || '', payroll_deductions_account_id: accountingControls.payroll_deductions_account_id || '', payroll_loans_receivable_account_id: accountingControls.payroll_loans_receivable_account_id || '', withholding_payable_account_id: accountingControls.withholding_payable_account_id || '' });
    const [activeTab, setActiveTab] = useState('invoice-aging');

    useFlashMessages();

    const tabs = [
        { id: 'invoice-aging', label: t('Invoice Aging'), permission: 'view-invoice-aging' },
        { id: 'bill-aging', label: t('Bill Aging'), permission: 'view-bill-aging' },
        { id: 'tax-summary', label: t('Tax Summary'), permission: 'view-tax-summary' },
        { id: 'customer-balance', label: t('Customer Balance'), permission: 'view-customer-balance' },
        { id: 'vendor-balance', label: t('Vendor Balance'), permission: 'view-vendor-balance' },
    ].filter(tab => auth.user?.permissions?.includes(tab.permission));

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Accounting'), url: route('account.index')},
                { label: t('Reports') }
            ]}
            pageTitle={t('Reports')}
        >
            <Head title={t('Reports')} />

            <Card className="shadow-sm">
                <CardContent className="p-6">
                    <Tabs value={activeTab} onValueChange={setActiveTab}>
                        <TabsList className="w-full justify-start overflow-x-auto overflow-y-hidden h-auto p-1">
                            {tabs.map(tab => (
                                <TabsTrigger key={tab.id} value={tab.id} className="whitespace-nowrap flex-shrink-0">
                                    {tab.label}
                                </TabsTrigger>
                            ))}
                        </TabsList>

                        <TabsContent value="invoice-aging" className="mt-4">
                            <InvoiceAging financialYear={financialYear} />
                        </TabsContent>

                        <TabsContent value="bill-aging" className="mt-4">
                            <BillAging financialYear={financialYear} />
                        </TabsContent>

                        <TabsContent value="tax-summary" className="mt-4">
                            <TaxSummary financialYear={financialYear} />
                        </TabsContent>

                        <TabsContent value="customer-balance" className="mt-4">
                            <CustomerBalance financialYear={financialYear} />
                        </TabsContent>

                        <TabsContent value="vendor-balance" className="mt-4">
                            <VendorBalance financialYear={financialYear} />
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
            {auth.user?.permissions?.includes('manage-account') && <Card className="mt-6"><CardContent className="p-6"><h2 className="font-semibold mb-4">{t('Accounting Controls')}</h2>
                <form className="grid gap-4 md:grid-cols-2" onSubmit={event => { event.preventDefault(); controls.post(route('accounting.controls')); }}>
                    {(['financial_year_start','financial_year_end','accounting_locked_until'] as const).map(key => <div key={key}><Label htmlFor={key}>{t(key.replaceAll('_',' '))}</Label><Input id={key} type="date" value={controls.data[key]} onChange={event => controls.setData(key,event.target.value)} /></div>)}
                    {(['inventory_adjustment_account_id','payroll_deductions_account_id','payroll_loans_receivable_account_id','withholding_payable_account_id'] as const).map(key => <div key={key}><Label>{t(key.replaceAll('_',' '))}</Label><Select value={controls.data[key] || 'none'} onValueChange={value => controls.setData(key,value === 'none' ? '' : value)}><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem value="none">{t('Select GL account')}</SelectItem>{glAccounts.map(account => <SelectItem key={account.id} value={String(account.id)}>{account.account_code} — {account.account_name}</SelectItem>)}</SelectContent></Select></div>)}
                    {Object.values(controls.errors).map((error,index) => <p key={index} role="alert" className="text-sm text-destructive">{error}</p>)}<div><Button disabled={controls.processing}>{t('Save accounting controls')}</Button></div>
                </form>
            </CardContent></Card>}
        </AuthenticatedLayout>
    );
}
