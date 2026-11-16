import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { formatCurrency } from '@/utils/helpers';
import PosReportDocument from '../../Components/PosReportDocument';

interface ProductsReportProps {
    productData: Array<{
        name: string;
        sku: string;
        total_quantity: number;
        total_revenue: number;
        total_orders: number;
    }>;
}

export default function ProductsReport({ productData = [] }: ProductsReportProps) {
    const { t } = useTranslation();
    const reportDate = new Date().toLocaleDateString('en-GB');
    const totalQuantity = productData.reduce((sum, p) => sum + Number(p.total_quantity || 0), 0);
    const totalRevenue = productData.reduce((sum, p) => sum + Number(p.total_revenue || 0), 0);
    const totalOrders = productData.reduce((sum, p) => sum + Number(p.total_orders || 0), 0);

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('POS'), url: route('pos.index') },
                { label: t('Product Report') },
            ]}
            pageTitle={t('Product Report')}
        >
            <Head title={t('Product Report')} />

            <PosReportDocument
                documentTitle={t('POS PRODUCT SALES STATEMENT')}
                documentNumber="RPT-POS-PRD-001"
                reportDate={reportDate}
                leftBoxTitle={t('Report Scope')}
                leftBoxFields={[
                    { label: t('Report Type'), value: t('Product Performance Ledger') },
                    { label: t('Total Products Listed'), value: productData.length },
                    { label: t('Total Quantity Sold'), value: totalQuantity },
                ]}
                rightBoxTitle={t('Financial Summary')}
                rightBoxFields={[
                    { label: t('Total Orders'), value: totalOrders },
                    { label: t('Total Revenue'), value: formatCurrency(totalRevenue) },
                    { label: t('Status'), value: t('Official Document') },
                ]}
                columns={[
                    { key: 'index', label: '#', width: '6%', align: 'center', render: (_item, idx) => idx + 1 },
                    { key: 'name', label: t('Product Name'), width: '34%' },
                    { key: 'sku', label: t('SKU'), width: '18%' },
                    { key: 'total_quantity', label: t('Quantity Sold'), width: '14%', align: 'right' },
                    { key: 'total_orders', label: t('Orders'), width: '10%', align: 'right' },
                    { key: 'total_revenue', label: t('Total Revenue'), width: '18%', align: 'right', render: (item) => formatCurrency(item.total_revenue || 0) },
                ]}
                items={productData}
                totals={[
                    { label: t('Total Quantity Sold'), value: String(totalQuantity) },
                    { label: t('Total Revenue'), value: formatCurrency(totalRevenue), isGrandTotal: true },
                ]}
                csvFilename="pos-product-sales-statement.csv"
                pdfFilename="pos-product-sales-statement.pdf"
                csvHeaders={[t('Product Name'), t('SKU'), t('Quantity Sold'), t('Orders'), t('Total Revenue')]}
                csvRows={productData.map((p) => [
                    p.name,
                    p.sku,
                    p.total_quantity,
                    p.total_orders,
                    Number(p.total_revenue || 0).toFixed(2),
                ])}
            />
        </AuthenticatedLayout>
    );
}
