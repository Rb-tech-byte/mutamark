import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatDate, formatCurrency } from '@/utils/helpers';
import PosReportDocument from '../../Components/PosReportDocument';

interface SalesReportProps {
    salesData: {
        data: Array<{
            id: number;
            sale_number: string;
            total: number;
            created_at: string;
            customer?: { name: string };
            warehouse?: { name: string };
        }>;
    };
    dailySales?: Array<{ date: string; sales: number; count: number }>;
    monthlySales?: Array<{ month: string; sales: number; count: number }>;
    warehouseSales?: Array<{ name: string; sales: number; count: number }>;
    discountData?: Array<{
        product_name: string;
        sku: string;
        total_discount_given: number;
        total_revenue: number;
    }>;
    monthlyDiscounts?: Array<{ month: string; discount_amount: number }>;
    returnData?: Array<{
        product_name: string;
        sku: string;
        total_returns: number;
        total_return_amount: number;
    }>;
    monthlyReturns?: Array<{ month: string; return_amount: number; return_count: number }>;
    returnStats?: {
        total_returns: number;
        total_return_amount: number;
    };
}

export default function SalesReport({
    salesData,
    dailySales = [],
    monthlySales = [],
    warehouseSales = [],
    discountData = [],
    returnData = [],
    returnStats,
}: SalesReportProps) {
    const { t } = useTranslation();
    const [activeTab, setActiveTab] = useState('transactions');
    const reportDate = new Date().toLocaleDateString('en-GB');

    const salesRows = salesData?.data || [];
    const totalSalesAmount = salesRows.reduce((sum, s) => sum + Number(s.total || 0), 0);
    const totalDailyAmount = dailySales.reduce((sum, d) => sum + Number(d.sales || 0), 0);
    const totalDailyOrders = dailySales.reduce((sum, d) => sum + Number(d.count || 0), 0);
    const totalMonthlyAmount = monthlySales.reduce((sum, m) => sum + Number(m.sales || 0), 0);
    const totalMonthlyOrders = monthlySales.reduce((sum, m) => sum + Number(m.count || 0), 0);
    const totalWarehouseAmount = warehouseSales.reduce((sum, w) => sum + Number(w.sales || 0), 0);
    const totalWarehouseOrders = warehouseSales.reduce((sum, w) => sum + Number(w.count || 0), 0);
    const totalDiscountGiven = discountData.reduce((sum, d) => sum + Number(d.total_discount_given || 0), 0);
    const totalDiscountRevenue = discountData.reduce((sum, d) => sum + Number(d.total_revenue || 0), 0);
    const totalReturnAmount = Number(returnStats?.total_return_amount || returnData.reduce((sum, r) => sum + Number(r.total_return_amount || 0), 0));
    const totalReturnCount = Number(returnStats?.total_returns || returnData.reduce((sum, r) => sum + Number(r.total_returns || 0), 0));

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('POS'), url: route('pos.index') },
                { label: t('Sales Report') },
            ]}
            pageTitle={t('Sales Report')}
        >
            <Head title={t('Sales Report')} />

            <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                <TabsList className="print:hidden flex flex-wrap h-auto gap-1">
                    <TabsTrigger value="transactions">{t('Sales Register')}</TabsTrigger>
                    <TabsTrigger value="daily">{t('Daily Sales Statement')}</TabsTrigger>
                    <TabsTrigger value="monthly">{t('Monthly Sales Statement')}</TabsTrigger>
                    <TabsTrigger value="warehouse">{t('Warehouse Sales Statement')}</TabsTrigger>
                    <TabsTrigger value="discounts">{t('Discount Register')}</TabsTrigger>
                    <TabsTrigger value="returns">{t('Returns Register')}</TabsTrigger>
                </TabsList>

                <TabsContent value="transactions">
                    <PosReportDocument
                        documentTitle={t('POS SALES REGISTER')}
                        documentNumber="RPT-POS-SLS-001"
                        reportDate={reportDate}
                        leftBoxTitle={t('Report Scope')}
                        leftBoxFields={[
                            { label: t('Report Type'), value: t('Itemized POS Sales Register') },
                            { label: t('Total Transactions'), value: salesRows.length },
                        ]}
                        rightBoxTitle={t('Financial Summary')}
                        rightBoxFields={[
                            { label: t('Total Sales Value'), value: formatCurrency(totalSalesAmount) },
                            { label: t('Status'), value: t('Official Document') },
                        ]}
                        columns={[
                            { key: 'index', label: '#', width: '6%', align: 'center', render: (_item, idx) => idx + 1 },
                            { key: 'sale_number', label: t('Sale Number'), width: '18%' },
                            { key: 'created_at', label: t('Date'), width: '16%', render: (item) => formatDate(item.created_at) },
                            { key: 'customer', label: t('Customer'), width: '24%', render: (item) => item.customer?.name || t('Walk-in Customer') },
                            { key: 'warehouse', label: t('Warehouse'), width: '18%', render: (item) => item.warehouse?.name || '-' },
                            { key: 'total', label: t('Amount'), width: '18%', align: 'right', render: (item) => formatCurrency(item.total || 0) },
                        ]}
                        items={salesRows}
                        totals={[
                            { label: t('Total Transactions'), value: String(salesRows.length) },
                            { label: t('Grand Total Sales'), value: formatCurrency(totalSalesAmount), isGrandTotal: true },
                        ]}
                        csvFilename="pos-sales-register.csv"
                        pdfFilename="pos-sales-register.pdf"
                        csvHeaders={[t('Sale Number'), t('Date'), t('Customer'), t('Warehouse'), t('Amount')]}
                        csvRows={salesRows.map((s) => [
                            s.sale_number,
                            formatDate(s.created_at),
                            s.customer?.name || t('Walk-in Customer'),
                            s.warehouse?.name || '-',
                            Number(s.total || 0).toFixed(2),
                        ])}
                    />
                </TabsContent>

                <TabsContent value="daily">
                    <PosReportDocument
                        documentTitle={t('DAILY SALES STATEMENT (LAST 7 DAYS)')}
                        documentNumber="RPT-POS-DAY-002"
                        reportDate={reportDate}
                        leftBoxTitle={t('Report Scope')}
                        leftBoxFields={[
                            { label: t('Period'), value: t('Last 7 Days') },
                            { label: t('Total Orders'), value: totalDailyOrders },
                        ]}
                        rightBoxTitle={t('Financial Summary')}
                        rightBoxFields={[
                            { label: t('Total Revenue'), value: formatCurrency(totalDailyAmount) },
                            { label: t('Status'), value: t('Official Document') },
                        ]}
                        columns={[
                            { key: 'index', label: '#', width: '10%', align: 'center', render: (_item, idx) => idx + 1 },
                            { key: 'date', label: t('Date'), width: '35%' },
                            { key: 'count', label: t('Order Count'), width: '25%', align: 'right' },
                            { key: 'sales', label: t('Sales Amount'), width: '30%', align: 'right', render: (item) => formatCurrency(item.sales || 0) },
                        ]}
                        items={dailySales}
                        totals={[
                            { label: t('Total Orders'), value: String(totalDailyOrders) },
                            { label: t('Total 7-Day Sales'), value: formatCurrency(totalDailyAmount), isGrandTotal: true },
                        ]}
                        csvFilename="pos-daily-sales-statement.csv"
                        pdfFilename="pos-daily-sales-statement.pdf"
                    />
                </TabsContent>

                <TabsContent value="monthly">
                    <PosReportDocument
                        documentTitle={t('MONTHLY SALES STATEMENT (LAST 6 MONTHS)')}
                        documentNumber="RPT-POS-MTH-003"
                        reportDate={reportDate}
                        leftBoxTitle={t('Report Scope')}
                        leftBoxFields={[
                            { label: t('Period'), value: t('Last 6 Months') },
                            { label: t('Total Orders'), value: totalMonthlyOrders },
                        ]}
                        rightBoxTitle={t('Financial Summary')}
                        rightBoxFields={[
                            { label: t('Total Revenue'), value: formatCurrency(totalMonthlyAmount) },
                            { label: t('Status'), value: t('Official Document') },
                        ]}
                        columns={[
                            { key: 'index', label: '#', width: '10%', align: 'center', render: (_item, idx) => idx + 1 },
                            { key: 'month', label: t('Month'), width: '35%' },
                            { key: 'count', label: t('Order Count'), width: '25%', align: 'right' },
                            { key: 'sales', label: t('Sales Amount'), width: '30%', align: 'right', render: (item) => formatCurrency(item.sales || 0) },
                        ]}
                        items={monthlySales}
                        totals={[
                            { label: t('Total Orders'), value: String(totalMonthlyOrders) },
                            { label: t('Total 6-Month Sales'), value: formatCurrency(totalMonthlyAmount), isGrandTotal: true },
                        ]}
                        csvFilename="pos-monthly-sales-statement.csv"
                        pdfFilename="pos-monthly-sales-statement.pdf"
                    />
                </TabsContent>

                <TabsContent value="warehouse">
                    <PosReportDocument
                        documentTitle={t('WAREHOUSE SALES STATEMENT')}
                        documentNumber="RPT-POS-WHS-004"
                        reportDate={reportDate}
                        leftBoxTitle={t('Report Scope')}
                        leftBoxFields={[
                            { label: t('Warehouses Covered'), value: warehouseSales.length },
                            { label: t('Total Orders'), value: totalWarehouseOrders },
                        ]}
                        rightBoxTitle={t('Financial Summary')}
                        rightBoxFields={[
                            { label: t('Total Sales'), value: formatCurrency(totalWarehouseAmount) },
                            { label: t('Status'), value: t('Official Document') },
                        ]}
                        columns={[
                            { key: 'index', label: '#', width: '10%', align: 'center', render: (_item, idx) => idx + 1 },
                            { key: 'name', label: t('Warehouse Name'), width: '40%' },
                            { key: 'count', label: t('Order Count'), width: '20%', align: 'right' },
                            { key: 'sales', label: t('Sales Amount'), width: '30%', align: 'right', render: (item) => formatCurrency(item.sales || 0) },
                        ]}
                        items={warehouseSales}
                        totals={[
                            { label: t('Total Orders'), value: String(totalWarehouseOrders) },
                            { label: t('Total Warehouse Sales'), value: formatCurrency(totalWarehouseAmount), isGrandTotal: true },
                        ]}
                        csvFilename="pos-warehouse-sales-statement.csv"
                        pdfFilename="pos-warehouse-sales-statement.pdf"
                    />
                </TabsContent>

                <TabsContent value="discounts">
                    <PosReportDocument
                        documentTitle={t('POS DISCOUNT REGISTER')}
                        documentNumber="RPT-POS-DSC-005"
                        reportDate={reportDate}
                        leftBoxTitle={t('Report Scope')}
                        leftBoxFields={[
                            { label: t('Discounted Products'), value: discountData.length },
                            { label: t('Report Type'), value: t('Discount Audit Ledger') },
                        ]}
                        rightBoxTitle={t('Financial Summary')}
                        rightBoxFields={[
                            { label: t('Total Discount Given'), value: formatCurrency(totalDiscountGiven) },
                            { label: t('Revenue After Discount'), value: formatCurrency(totalDiscountRevenue) },
                        ]}
                        columns={[
                            { key: 'index', label: '#', width: '8%', align: 'center', render: (_item, idx) => idx + 1 },
                            { key: 'product_name', label: t('Product Name'), width: '34%' },
                            { key: 'sku', label: t('SKU'), width: '18%' },
                            { key: 'total_discount_given', label: t('Discount Given'), width: '20%', align: 'right', render: (item) => formatCurrency(item.total_discount_given || 0) },
                            { key: 'total_revenue', label: t('Net Revenue'), width: '20%', align: 'right', render: (item) => formatCurrency(item.total_revenue || 0) },
                        ]}
                        items={discountData}
                        totals={[
                            { label: t('Total Discount Given'), value: formatCurrency(totalDiscountGiven) },
                            { label: t('Total Net Revenue'), value: formatCurrency(totalDiscountRevenue), isGrandTotal: true },
                        ]}
                        csvFilename="pos-discount-register.csv"
                        pdfFilename="pos-discount-register.pdf"
                    />
                </TabsContent>

                <TabsContent value="returns">
                    <PosReportDocument
                        documentTitle={t('POS RETURNS REGISTER')}
                        documentNumber="RPT-POS-RET-006"
                        reportDate={reportDate}
                        leftBoxTitle={t('Report Scope')}
                        leftBoxFields={[
                            { label: t('Returned Products'), value: returnData.length },
                            { label: t('Total Return Count'), value: totalReturnCount },
                        ]}
                        rightBoxTitle={t('Financial Summary')}
                        rightBoxFields={[
                            { label: t('Total Return Value'), value: formatCurrency(totalReturnAmount) },
                            { label: t('Status'), value: t('Official Document') },
                        ]}
                        columns={[
                            { key: 'index', label: '#', width: '8%', align: 'center', render: (_item, idx) => idx + 1 },
                            { key: 'product_name', label: t('Product Name'), width: '36%' },
                            { key: 'sku', label: t('SKU'), width: '20%' },
                            { key: 'total_returns', label: t('Return Count'), width: '16%', align: 'right' },
                            { key: 'total_return_amount', label: t('Return Amount'), width: '20%', align: 'right', render: (item) => formatCurrency(item.total_return_amount || 0) },
                        ]}
                        items={returnData}
                        totals={[
                            { label: t('Total Returns'), value: String(totalReturnCount) },
                            { label: t('Total Return Value'), value: formatCurrency(totalReturnAmount), isGrandTotal: true },
                        ]}
                        csvFilename="pos-returns-register.csv"
                        pdfFilename="pos-returns-register.pdf"
                    />
                </TabsContent>
            </Tabs>
        </AuthenticatedLayout>
    );
}
