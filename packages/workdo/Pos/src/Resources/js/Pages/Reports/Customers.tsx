import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { formatDate, formatCurrency } from '@/utils/helpers';
import PosReportDocument from '../../Components/PosReportDocument';

interface CustomersReportProps {
    customerData: Array<{
        customer_id: number | null;
        total_orders: number;
        total_spent: number;
        avg_order_value: number;
        last_order_date: string;
        customer?: { name: string };
    }>;
}

export default function CustomersReport({ customerData = [] }: CustomersReportProps) {
    const { t } = useTranslation();
    const reportDate = new Date().toLocaleDateString('en-GB');
    const totalRevenue = customerData.reduce((sum, c) => sum + Number(c.total_spent || 0), 0);
    const totalOrders = customerData.reduce((sum, c) => sum + Number(c.total_orders || 0), 0);
    const overallAvg = customerData.length > 0
        ? customerData.reduce((sum, c) => sum + Number(c.avg_order_value || 0), 0) / customerData.length
        : 0;

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('POS'), url: route('pos.index') },
                { label: t('Customer Report') },
            ]}
            pageTitle={t('Customer Report')}
        >
            <Head title={t('Customer Report')} />

            <PosReportDocument
                documentTitle={t('POS CUSTOMER SALES STATEMENT')}
                documentNumber="RPT-POS-CST-001"
                reportDate={reportDate}
                leftBoxTitle={t('Report Scope')}
                leftBoxFields={[
                    { label: t('Report Type'), value: t('Customer Ledger Statement') },
                    { label: t('Total Customers'), value: customerData.length },
                    { label: t('Total Orders'), value: totalOrders },
                ]}
                rightBoxTitle={t('Financial Summary')}
                rightBoxFields={[
                    { label: t('Average Order Value'), value: formatCurrency(overallAvg) },
                    { label: t('Total Customer Revenue'), value: formatCurrency(totalRevenue) },
                    { label: t('Status'), value: t('Official Document') },
                ]}
                columns={[
                    { key: 'index', label: '#', width: '6%', align: 'center', render: (_item, idx) => idx + 1 },
                    { key: 'customer', label: t('Customer Name'), width: '32%', render: (item) => item.customer?.name || t('Walk-in Customer') },
                    { key: 'total_orders', label: t('Total Orders'), width: '14%', align: 'right' },
                    { key: 'avg_order_value', label: t('Avg Order Value'), width: '18%', align: 'right', render: (item) => formatCurrency(item.avg_order_value || 0) },
                    { key: 'last_order_date', label: t('Last Order Date'), width: '14%', render: (item) => formatDate(item.last_order_date) },
                    { key: 'total_spent', label: t('Total Spent'), width: '16%', align: 'right', render: (item) => formatCurrency(item.total_spent || 0) },
                ]}
                items={customerData}
                totals={[
                    { label: t('Total Orders'), value: String(totalOrders) },
                    { label: t('Grand Total Revenue'), value: formatCurrency(totalRevenue), isGrandTotal: true },
                ]}
                csvFilename="pos-customer-sales-statement.csv"
                pdfFilename="pos-customer-sales-statement.pdf"
                csvHeaders={[t('Customer Name'), t('Total Orders'), t('Avg Order Value'), t('Last Order Date'), t('Total Spent')]}
                csvRows={customerData.map((c) => [
                    c.customer?.name || t('Walk-in Customer'),
                    c.total_orders,
                    Number(c.avg_order_value || 0).toFixed(2),
                    formatDate(c.last_order_date),
                    Number(c.total_spent || 0).toFixed(2),
                ])}
            />
        </AuthenticatedLayout>
    );
}
