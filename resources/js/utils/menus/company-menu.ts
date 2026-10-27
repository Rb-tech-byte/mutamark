import {
    LayoutGrid,
    Users,
    CheckCircle2,
    UserCheck,
    Replace,
    Receipt,
    Store,
    Layers,
    ShoppingCart,
    ShoppingBag,
    Package,
    Truck,
    FolderKanban,
    Wrench,
    Cpu,
    Calculator,
    ShieldCheck,
    Briefcase,
    Bot,
    MessageCircle,
    FileText,
    Image,
    Headphones,
    Settings,
    Database,
    Globe,
    Smartphone
} from 'lucide-react';
import { NavItem } from '@/types';

export const getCompanyMenu = (t: (key: string) => string): NavItem[] => [
    {
        title: t('Proforma Invoices'), href: route('proformas.index'), icon: Receipt,
        permission: 'manage-sales-invoices', name: 'proformas', order: 54,
    },
    {
        title: t('Document Repository'), href: route('documents.index'), icon: FileText,
        permission: 'manage-media', name: 'documents', order: 172,
    },
    // ----------------------------------------------------
    // 1. Executive & Governance
    // ----------------------------------------------------
    {
        title: t('Dashboard'),
        icon: LayoutGrid,
        permission: 'manage-dashboard',
        name: 'dashboard',
        order: 10,
        children: [
            {
                title: t('Overview'),
                href: route('dashboard'),
                permission: 'manage-dashboard',
            },
        ],
    },
    {
        title: t('User Management'),
        icon: Users,
        permission: 'manage-users',
        name: 'users',
        order: 20,
        children: [
            {
                title: t('Roles'),
                href: route('roles.index'),
                permission: 'manage-roles',
            },
            {
                title: t('Users'),
                href: route('users.index'),
                permission: 'manage-users',
            },
        ],
    },
    {
        title: t('Approval Workflows'),
        href: route('approvals.index'),
        icon: CheckCircle2,
        permission: 'manage-account',
        name: 'approvals',
        order: 30,
    },

    // ----------------------------------------------------
    // 2. Commercial Sales & CRM (Lead-to-Cash)
    // ----------------------------------------------------
    {
        title: t('CRM'),
        icon: UserCheck,
        name: 'crm',
        module: 'Lead',
        order: 40,
        children: [
            {
                title: t('Leads'),
                href: route('lead.leads.index'),
                permission: 'manage-leads',
            },
            {
                title: t('Deals'),
                href: route('lead.deals.index'),
                permission: 'manage-deals',
            },
            {
                title: t('Pipelines & Stages'),
                href: route('lead.pipelines.index'),
                permission: 'manage-pipelines',
            },
            {
                title: t('Reports'),
                href: route('lead.reports.index'),
                permission: 'view-reports',
            },
        ],
    },
    {
        title: t('Proposal'),
        href: route('sales-proposals.index'),
        icon: Replace,
        permission: 'manage-sales-proposals',
        name: 'proposals',
        order: 50,
    },
    {
        title: t('Sales Orders'),
        href: route('sales-orders.index'),
        icon: ShoppingBag,
        name: 'sales-orders',
        order: 55,
    },

    {
        title: t('Sales Invoice'),
        icon: Receipt,
        permission: 'manage-sales-invoices',
        name: 'sales-invoices',
        order: 60,
        children: [
            {
                title: t('Sales Invoice'),
                href: route('sales-invoices.index'),
                permission: 'manage-sales-invoices',
            },
            {
                title: t('Sales Invoice Returns'),
                href: route('sales-returns.index'),
                permission: 'manage-sales-return-invoices',
            },
            {
                title: t('TRA Fiscal Receipts (VFD)'),
                href: route('tra-fiscal.index'),
                permission: 'manage-sales-invoices',
            },
            {
                title: t('Delivery Notes & Dispatch'),
                href: route('sales.delivery-notes.index'),
                permission: 'manage-sales-invoices',
            },
        ],
    },
    {
        title: t('Point of Sale (POS)'),
        icon: Store,
        permission: 'manage-pos',
        name: 'pos',
        module: 'Pos',
        order: 70,
        children: [
            {
                title: t('POS Register'),
                href: route('pos.create'),
                permission: 'manage-pos',
            },
            {
                title: t('POS Orders & Sales'),
                href: route('pos.orders'),
                permission: 'manage-pos-orders',
            },
            {
                title: t('Returns & Refunds'),
                href: route('pos.returns.index'),
                permission: 'manage-pos-returns',
            },
            {
                title: t('Print Barcode'),
                href: route('pos.barcode'),
                permission: 'manage-pos-barcodes',
            },
            {
                title: t('Billing Counters'),
                href: route('pos.billing-counters'),
                permission: 'manage-pos-billing-counters',
            },
            {
                title: t('Discounts & Promotions'),
                href: route('pos.discounts.index'),
                permission: 'manage-pos-discounts',
            },
            {
                title: t('Sales Reports'),
                href: route('pos.reports.sales'),
                permission: 'manage-pos-reports',
            },
        ],
    },

    // ----------------------------------------------------
    // 3. Supply Chain, Logistics & Warehousing (Procure-to-Pay)
    // ----------------------------------------------------
    {
        title: t('Product & Service'),
        icon: Layers,
        permission: 'manage-product-service-item',
        name: 'product-service',
        module: 'ProductService',
        order: 80,
        children: [
            {
                title: t('Items'),
                href: route('product-service.items.index'),
                permission: 'manage-product-service-item',
            },
            {
                title: t('Item Categories & Setup'),
                href: route('product-service.item-categories.index'),
                permission: 'manage-product-service-item',
            },
        ],
    },
    {
        title: t('Procurement'),
        icon: ShoppingCart,
        permission: 'manage-purchase-invoices',
        name: 'procurement',
        order: 90,
        children: [
            {
                title: t('Purchase Requisitions (PR)'),
                href: route('procurement.purchase-requisitions.index'),
                permission: 'manage-purchase-invoices',
            },
            {
                title: t('Request for Quotations (RFQ)'),
                href: route('procurement.rfqs.index'),
                permission: 'manage-purchase-invoices',
            },
            {
                title: t('Goods Received (GRN)'),
                href: route('goods-received-notes.index'),
                permission: 'manage-grn',
            },
            {
                title: t('3-Way Matching Audit'),
                href: route('procurement.three-way-match.index'),
                permission: 'manage-purchase-invoices',
            },
            {
                title: t('Purchase Invoices / Bills'),
                href: route('purchase-invoices.index'),
                permission: 'manage-purchase-invoices',
            },
            {
                title: t('Purchase Returns'),
                href: route('purchase-returns.index'),
                permission: 'manage-purchase-return-invoices',
            },
            {
                title: t('Vendors & Suppliers'),
                href: route('account.vendors.index'),
                permission: 'manage-vendors',
                module: 'Account',
            },
        ],
    },
    {
        title: t('Inventory & Stock'),
        icon: Package,
        permission: 'manage-stock',
        name: 'inventory',
        order: 100,
        children: [
            {
                title: t('Stock Levels'),
                href: route('product-service.stock.index'),
                permission: 'manage-stock',
            },
            {
                title: t('Serial & Batch Tracking'),
                href: route('inventory-serials.index'),
                permission: 'manage-inventory-serials',
            },
            {
                title: t('Warehouses'),
                href: route('warehouses.index'),
                permission: 'manage-warehouses',
            },
            {
                title: t('Stock Transfers'),
                href: route('transfers.index'),
                permission: 'manage-transfers',
            },
            {
                title: t('Stock Adjustments'),
                href: route('stock-adjustments.index'),
                permission: 'manage-stock',
            },
            {
                title: t('Products & Items'),
                href: route('product-service.items.index'),
                permission: 'manage-product-service-item',
            },
        ],
    },
    {
        title: t('Fleet & Transport'),
        href: route('fleet.index'),
        icon: Truck,
        permission: 'fleet manage',
        name: 'fleet',
        module: 'Fleet',
        order: 110,
    },

    // ----------------------------------------------------
    // 4. Operations, Servicing & Asset Lifecycle
    // ----------------------------------------------------
    {
        title: t('Projects & Costing'),
        icon: FolderKanban,
        permission: 'manage-project',
        name: 'project',
        module: 'Taskly',
        order: 120,
        children: [
            {
                title: t('Projects Overview'),
                href: route('project.index'),
                permission: 'manage-project',
            },
            {
                title: t('Tasks & Milestones'),
                href: route('project.tasks.index'),
                permission: 'manage-project-task',
            },
            {
                title: t('Timesheets & Labor Costing'),
                href: route('project.timesheets.index'),
                permission: 'manage-project',
            },
            {
                title: t('Bug & Issue Tracker'),
                href: route('project.bugs.index'),
                permission: 'manage-project-bug',
            },
            {
                title: t('Milestone Payments'),
                href: route('project-payments.index'),
                permission: 'manage-project-payments',
            },
            {
                title: t('Project Reports'),
                href: route('project.report.index'),
                permission: 'manage-project-report',
            },
            {
                title: t('System Setup'),
                href: route('project.task-stages.index'),
                permission: 'manage-task-stages',
            },
        ],
    },
    {
        title: t('Warranty & Servicing'),
        icon: Wrench,
        name: 'warranty-servicing',
        order: 130,
        children: [
            {
                title: t('Warranty Dashboard & Tickets'),
                href: route('warranty.index'),
            },
            {
                title: t('Device & Mobile Repairs'),
                href: route('mobile-service-management.pending-service-requests.index'),
                permission: 'manage-mobile-service-management',
                module: 'MobileServiceManagement',
            },
            {
                title: t('Device Brands & Setup'),
                href: route('mobile-service.device-brands.index'),
                permission: 'manage-mobile-device-brands',
                module: 'MobileServiceManagement',
            },
            {
                title: t('Service Policies'),
                href: route('mobile-service-management.mobile-service-policies.index'),
                permission: 'manage-mobile-service-policies',
                module: 'MobileServiceManagement',
            },
            {
                title: t('Service Contracts'),
                href: route('mobile-service-management.mobile-service-contracts.index'),
                permission: 'manage-mobile-service-contracts',
                module: 'MobileServiceManagement',
            },
        ],
    },
    {
        title: t('Fixed Assets & Servicing'),
        href: route('assets.index'),
        icon: Cpu,
        permission: 'manage-account',
        name: 'assets',
        order: 140,
    },

    // ----------------------------------------------------
    // 5. Financial Treasury & Fiscal Compliance (Record-to-Report)
    // ----------------------------------------------------
    {
        title: t('Accounting'),
        icon: Calculator,
        permission: 'manage-account',
        name: 'accounting',
        module: 'Account',
        order: 150,
        children: [
            {
                title: t('Bank Accounts'),
                href: route('account.bank-accounts.index'),
                permission: 'manage-bank-accounts',
            },
            {
                title: t('Bank Transfers'),
                href: route('account.bank-transfers.index'),
                permission: 'manage-bank-transfers',
            },
            {
                title: t('Bank Transactions'),
                href: route('account.bank-transactions.index'),
                permission: 'manage-bank-transactions',
            },
            {
                title: t('Chart Of Accounts'),
                href: route('account.chart-of-accounts.index'),
                permission: 'manage-chart-of-accounts',
            },
            {
                title: t('Customer Payments'),
                href: route('account.customer-payments.index'),
                permission: 'manage-customer-payments',
            },
            {
                title: t('Vendor Payments'),
                href: route('account.vendor-payments.index'),
                permission: 'manage-vendor-payments',
            },
            {
                title: t('Revenue'),
                href: route('account.revenues.index'),
                permission: 'manage-revenues',
            },
            {
                title: t('Expense'),
                href: route('account.expenses.index'),
                permission: 'manage-expenses',
            },
            {
                title: t('Debit Notes'),
                href: route('account.debit-notes.index'),
                permission: 'manage-debit-notes',
            },
            {
                title: t('Credit Notes'),
                href: route('account.credit-notes.index'),
                permission: 'manage-credit-notes',
            },
            {
                title: t('Financial & Tax Reports'),
                href: route('account.reports.index'),
                permission: 'manage-account-reports',
            },
            {
                title: t('Financial Audit & Controls'),
                href: route('accounting.audit-trail.index'),
                permission: 'manage-account',
            },
            {
                title: t('Multi-Currency & Forex'),
                href: route('accounting.multi-currency.index'),
                permission: 'manage-account',
            },
            {
                title: t('System Setup'),
                href: route('account.account-types.index'),
                permission: 'manage-account-types',
            },
        ],
    },
    {
        title: t('Verification Ledger'),
        href: route('document-verifications.index'),
        icon: ShieldCheck,
        name: 'document-verifications',
        order: 160,
    },

    // ----------------------------------------------------
    // 6. Human Capital Management (Hire-to-Retire)
    // ----------------------------------------------------
    {
        title: t('Human Resources & Payroll'),
        icon: Briefcase,
        permission: 'manage-payrolls',
        name: 'hrm',
        module: 'Hrm',
        order: 170,
        children: [
            {
                title: t('Employee Directory'),
                href: route('hrm.employees.index'),
                permission: 'manage-employees',
            },
            {
                title: t('Tanzania Statutory Reports (TRA / NSSF)'),
                href: route('hrm.reports.tanzania-statutory'),
                permission: 'manage-payrolls',
            },
            {
                title: t('Payrolls & Runs'),
                href: route('hrm.payrolls.index'),
                permission: 'manage-payrolls',
            },
            {
                title: t('Salary Setup'),
                href: route('hrm.set-salary.index'),
                permission: 'manage-set-salary',
            },
            {
                title: t('Attendance & Shifts'),
                href: route('hrm.attendances.index'),
                permission: 'manage-attendances',
            },
            {
                title: t('Leave Management'),
                href: route('hrm.leave-applications.calendar'),
                permission: 'manage-leave-applications',
            },
            {
                title: t('Holidays'),
                href: route('hrm.holidays.index'),
                permission: 'manage-holidays',
            },
            {
                title: t('Awards & Recognition'),
                href: route('hrm.awards.index'),
                permission: 'manage-awards',
            },
            {
                title: t('Promotions & Transfers'),
                href: route('hrm.promotions.index'),
                permission: 'manage-promotions',
            },
            {
                title: t('Company Policies'),
                href: route('hrm.company-policies.index'),
                permission: 'manage-company-policies',
            },
            {
                title: t('Announcements & Events'),
                href: route('hrm.announcements.index'),
                permission: 'manage-announcements',
            },
            {
                title: t('System Setup'),
                href: route('hrm.branches.index'),
                permission: 'manage-branches',
            },
        ],
    },

    // ----------------------------------------------------
    // 7. External Portals (SRS Modules 27 & 28)
    // ----------------------------------------------------
    {
        title: t('Self-Service Portals'),
        icon: Globe,
        name: 'portals',
        order: 180,
        children: [
            {
                title: t('Customer Portal'),
                href: route('portal.customer'),
            },
            {
                title: t('Supplier Portal'),
                href: route('portal.supplier'),
            },
        ],
    },

    // ----------------------------------------------------
    // 8. Enterprise AI, Productivity & Collaboration
    // ----------------------------------------------------
    {
        title: t('AI Agent'),
        href: route('ai-agent.chat.page'),
        icon: Bot,
        permission: 'manage-ai-agent',
        name: 'ai-agent',
        order: 190,
    },
    {
        title: t('Messenger'),
        href: route('messenger.index'),
        icon: MessageCircle,
        permission: 'manage-messenger',
        name: 'messenger',
        order: 200,
    },
    {
        title: t('Paperless Office & Templates'),
        href: route('paperless.templates.index'),
        icon: FileText,
        permission: 'manage-settings',
        name: 'paperless',
        order: 210,
    },
    {
        title: t('Media Library'),
        href: route('media-library'),
        icon: Image,
        permission: 'manage-media',
        name: 'media-library',
        order: 220,
    },
    {
        title: t('Helpdesk'),
        href: route('helpdesk-tickets.index'),
        icon: Headphones,
        permission: 'manage-helpdesk-tickets',
        name: 'helpdesk',
        order: 230,
    },

    // ----------------------------------------------------
    // 9. Administration & Disaster Recovery
    // ----------------------------------------------------
    {
        title: t('Settings'),
        href: route('settings.index'),
        icon: Settings,
        permission: 'manage-settings',
        name: 'settings',
        order: 250,
    },
];
