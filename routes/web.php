<?php



use App\Http\Controllers\MediaController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\BankTransferPaymentController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\CouponController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\HelpdeskCategoryController;
use App\Http\Controllers\HelpdeskTicketController;
use App\Http\Controllers\HelpdeskReplyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\TraFiscalController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SalesProposalController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\AIAgentChatPageController;
use App\Http\Controllers\AIAgentChatController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\InventorySerialController;
use App\Http\Controllers\GoodsReceivedNoteController;
use App\Http\Controllers\TanzaniaPayrollReportController;
use App\Http\Controllers\FinancialAuditTrailController;
use App\Http\Controllers\FleetManagementController;
use App\Http\Controllers\ProjectTimesheetController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\WarrantyServicingController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\ApprovalWorkflowController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\MultiCurrencyForexController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\ThreeWayMatchController;
use App\Http\Controllers\DocumentTemplateController;
use Inertia\Inertia;


Route::middleware(['auth', 'verified', 'PlanModuleCheck'])->group(function () {
    Route::resource('documents', \App\Http\Controllers\DocumentController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::get('proformas', [\App\Http\Controllers\ProformaInvoiceController::class, 'index'])->name('proformas.index');
    Route::get('proformas/{proforma}/print', [\App\Http\Controllers\ProformaInvoiceController::class, 'print'])->name('proformas.print');
    Route::post('proformas', [\App\Http\Controllers\ProformaInvoiceController::class, 'store'])->name('proformas.store');
    Route::post('proformas/{proforma}/convert', [\App\Http\Controllers\ProformaInvoiceController::class, 'convert'])->name('proformas.convert');
    Route::post('proformas/{proforma}/cancel', [\App\Http\Controllers\ProformaInvoiceController::class, 'cancel'])->name('proformas.cancel');
    Route::post('documents/{document}/versions', [\App\Http\Controllers\DocumentController::class, 'version'])->name('documents.versions.store');
    Route::get('documents/{document}/download/{version?}', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    // Route::get('/dashboard', function () {
    //     return Inertia::render('dashboard');
    // })->name('dashboard');

    Route::get('dashboard', [HomeController::class, 'Dashboard'])->name('dashboard');

    // Profile management routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('procurement/purchase-orders/{purchaseOrder}/action', [GoodsReceivedNoteController::class, 'purchaseOrderAction'])->name('purchase-orders.action');
    Route::post('/profile/two-factor', [ProfileController::class, 'twoFactor'])->middleware('throttle:5,1')->name('profile.two-factor');
    Route::post('portal/supplier/orders/{purchaseOrder}/accept', [PortalController::class,'supplierAcceptOrder'])->name('portal.supplier.accept-order');
    Route::post('portal/supplier/orders/{purchaseOrder}/attachments', [PortalController::class,'supplierUpload'])->name('portal.supplier.upload');
    Route::patch('portal/supplier/profile', [PortalController::class,'supplierUpdateProfile'])->name('portal.supplier.update-profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource management routes
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('users/leave-impersonation', [UserController::class, 'leaveImpersonation'])->name('users.leave-impersonation');
    Route::get('users/login/history', [UserController::class, 'loginHistory'])->name('users.login-history');
    Route::get('users/{user}/admin-hub', [UserController::class, 'adminHub'])->name('users.admin-hub');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('transfers', TransferController::class)->except(['edit', 'update']);
    Route::post('transfers/{transfer}/dispatch', [TransferController::class, 'dispatchTransfer'])->name('transfers.dispatch');
    Route::post('transfers/{transfer}/receive', [TransferController::class, 'receiveTransfer'])->name('transfers.receive');
    Route::get('transfers/{transfer}/waybill', [TransferController::class, 'waybill'])->name('transfers.waybill');
    Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'store']);
    Route::get('stock-adjustments/scan', [StockAdjustmentController::class, 'scan'])->name('stock-adjustments.scan');
    Route::post('stock-adjustments/{stockAdjustment}/post', [StockAdjustmentController::class, 'postAdjustment'])->name('stock-adjustments.post');
    Route::resource('inventory-serials', InventorySerialController::class);
    Route::get('/api/v1/inventory/serials/{serialNumber}', [InventorySerialController::class, 'apiShow'])->name('api.inventory-serials.show');

    // TRA EFDMS / VFD Real-time Fiscal Compliance Engine & Daily Z-Reports (SRS Module 27)
    Route::get('tra-fiscal', [TraFiscalController::class, 'index'])->name('tra-fiscal.index');
    Route::post('tra-fiscal/invoices/{salesInvoice}/fiscalize', [TraFiscalController::class, 'fiscalizeInvoice'])->name('tra-fiscal.fiscalize');
    Route::get('tra-fiscal/receipts/{traFiscalReceipt}', [TraFiscalController::class, 'showReceipt'])->name('tra-fiscal.receipt');
    Route::post('tra-fiscal/z-reports/close', [TraFiscalController::class, 'closeZReport'])->name('tra-fiscal.z-report.close');
    Route::get('tra-fiscal/z-reports/{traZReport}', [TraFiscalController::class, 'showZReport'])->name('tra-fiscal.z-report.show');

    // Purchase Requisitions & Internal Material Requests (SRS Module 8: Procure-to-Pay)
    Route::get('procurement/purchase-requisitions', [PurchaseRequisitionController::class, 'index'])->name('procurement.purchase-requisitions.index');
    Route::post('procurement/purchase-requisitions', [PurchaseRequisitionController::class, 'store'])->name('procurement.purchase-requisitions.store');
    Route::put('procurement/purchase-requisitions/{purchaseRequisition}', [PurchaseRequisitionController::class, 'update'])->name('procurement.purchase-requisitions.update');
    Route::delete('procurement/purchase-requisitions/{purchaseRequisition}', [PurchaseRequisitionController::class, 'destroy'])->name('procurement.purchase-requisitions.destroy');
    Route::post('procurement/purchase-requisitions/{purchaseRequisition}/submit', [PurchaseRequisitionController::class, 'submit'])->name('procurement.purchase-requisitions.submit');
    Route::post('procurement/purchase-requisitions/{purchaseRequisition}/approve', [PurchaseRequisitionController::class, 'approve'])->name('procurement.purchase-requisitions.approve');
    Route::post('procurement/purchase-requisitions/{purchaseRequisition}/reject', [PurchaseRequisitionController::class, 'reject'])->name('procurement.purchase-requisitions.reject');
    Route::post('procurement/purchase-requisitions/{purchaseRequisition}/convert-to-invoice', [PurchaseRequisitionController::class, 'convertToInvoice'])->name('procurement.purchase-requisitions.convert-to-invoice');

    // Request for Quotations (RFQ) & Comparative Evaluation (SRS Module 8: Section 3.8.3)
    Route::get('procurement/rfqs', [RfqController::class, 'index'])->name('procurement.rfqs.index');
    Route::post('procurement/rfqs', [RfqController::class, 'store'])->name('procurement.rfqs.store');
    Route::put('procurement/rfqs/{rfq}', [RfqController::class, 'update'])->name('procurement.rfqs.update');
    Route::delete('procurement/rfqs/{rfq}', [RfqController::class, 'destroy'])->name('procurement.rfqs.destroy');
    Route::post('procurement/rfqs/{rfq}/publish', [RfqController::class, 'publish'])->name('procurement.rfqs.publish');
    Route::post('procurement/rfqs/{rfq}/bids', [RfqController::class, 'storeBid'])->name('procurement.rfqs.bids.store');
    Route::put('procurement/rfqs/bids/{quotation}', [RfqController::class, 'updateBid'])->name('procurement.rfqs.bids.update');
    Route::delete('procurement/rfqs/bids/{quotation}', [RfqController::class, 'destroyBid'])->name('procurement.rfqs.bids.destroy');
    Route::post('procurement/rfqs/{rfq}/award', [RfqController::class, 'award'])->name('procurement.rfqs.award');

    // Goods Received Notes (GRN) & 3-Way Matching
    Route::resource('goods-received-notes', GoodsReceivedNoteController::class);
    Route::post('goods-received-notes/{id}/post', [GoodsReceivedNoteController::class, 'post'])->name('goods-received-notes.post');
    Route::post('goods-received-notes/{id}/inspection', [GoodsReceivedNoteController::class, 'updateInspection'])->name('goods-received-notes.inspection');
    Route::get('/api/v1/procurement/three-way-match/{grnId}', [GoodsReceivedNoteController::class, 'apiMatchCheck'])->name('api.procurement.match');

    // Three-Way Matching Audit & AP Reconciliation (SRS Module 8 - Section 3.8.6)
    Route::get('procurement/three-way-match', [ThreeWayMatchController::class, 'index'])->name('procurement.three-way-match.index');
    Route::put('procurement/three-way-match/config', [ThreeWayMatchController::class, 'updateConfig'])->name('procurement.three-way-match.config');
    Route::post('procurement/three-way-match/{id}/authorize', [ThreeWayMatchController::class, 'authorizeDisbursement'])->name('procurement.three-way-match.authorize');
    Route::post('procurement/three-way-match/{id}/dispute', [ThreeWayMatchController::class, 'flagDispute'])->name('procurement.three-way-match.dispute');
    Route::post('procurement/three-way-match/link', [ThreeWayMatchController::class, 'linkInvoice'])->name('procurement.three-way-match.link');

    // Tanzania Statutory Payroll & Remittance Engine (TRA PAYE, NSSF, NHIF, WCF, SDL)
    Route::get('hrm/reports/tanzania-statutory', [TanzaniaPayrollReportController::class, 'index'])->name('hrm.reports.tanzania-statutory');
    Route::get('hrm/reports/tanzania-statutory/export', [TanzaniaPayrollReportController::class, 'exportCsv'])->name('hrm.reports.tanzania-statutory.export');
    Route::post('hrm/reports/tanzania-statutory/declarations', [TanzaniaPayrollReportController::class, 'storeDeclaration'])->name('hrm.reports.tanzania-statutory.declarations.store');
    Route::put('hrm/reports/tanzania-statutory/declarations/{declaration}', [TanzaniaPayrollReportController::class, 'updateDeclaration'])->name('hrm.reports.tanzania-statutory.declarations.update');
    Route::delete('hrm/reports/tanzania-statutory/declarations/{declaration}', [TanzaniaPayrollReportController::class, 'destroyDeclaration'])->name('hrm.reports.tanzania-statutory.declarations.destroy');
    Route::post('hrm/reports/tanzania-statutory/employees/{employee}', [TanzaniaPayrollReportController::class, 'updateEmployeeStatutory'])->name('hrm.reports.tanzania-statutory.employees.update');

    // Financial Controls, Document Immutability & Audit Trail Engine (Appendix B & SRS 3.16)
    Route::get('accounting/audit-trail', [FinancialAuditTrailController::class, 'index'])->name('accounting.audit-trail.index');
    Route::get('accounting/audit-trail/export', [FinancialAuditTrailController::class, 'exportCsv'])->name('accounting.audit-trail.export');
    Route::post('accounting/audit-trail/reverse-journal', [FinancialAuditTrailController::class, 'reverseJournal'])->name('accounting.audit-trail.reverse-journal');
    Route::post('accounting/controls', [\Workdo\Account\Http\Controllers\ReportsController::class, 'accountingControls'])->name('accounting.controls');
    Route::post('accounting/withholding/{id}/remit', [\Workdo\Account\Http\Controllers\ReportsController::class, 'remitWithholding'])->name('accounting.withholding.remit');
    Route::post('accounting/audit-trail/invoices/{salesInvoice}/reconcile', [FinancialAuditTrailController::class, 'reconcileInvoice'])->name('accounting.audit-trail.reconcile-invoice');
    Route::get('/api/v1/compliance/verify-audit-chain', [FinancialAuditTrailController::class, 'apiVerifyChain'])->name('api.compliance.verify-audit-chain');

    // Multi-Currency Exchange Rates & Real-Time Unrealized Forex Engine (SRS Modules 8 & 28)
    Route::get('accounting/multi-currency', [MultiCurrencyForexController::class, 'index'])->name('accounting.multi-currency.index');
    Route::post('accounting/multi-currency/currencies', [MultiCurrencyForexController::class, 'storeCurrency'])->name('accounting.multi-currency.currencies.store');
    Route::put('accounting/multi-currency/currencies/{currency}', [MultiCurrencyForexController::class, 'updateCurrency'])->name('accounting.multi-currency.currencies.update');
    Route::delete('accounting/multi-currency/currencies/{currency}', [MultiCurrencyForexController::class, 'destroyCurrency'])->name('accounting.multi-currency.currencies.destroy');
    Route::post('accounting/multi-currency/sync-rates', [MultiCurrencyForexController::class, 'syncRates'])->name('accounting.multi-currency.sync-rates');
    Route::post('accounting/multi-currency/revaluations', [MultiCurrencyForexController::class, 'runRevaluation'])->name('accounting.multi-currency.revaluations.run');
    Route::post('accounting/multi-currency/revaluations/{revaluation}/reverse', [MultiCurrencyForexController::class, 'reverseRevaluation'])->name('accounting.multi-currency.revaluations.reverse');
    Route::delete('accounting/multi-currency/revaluations/{revaluation}', [MultiCurrencyForexController::class, 'destroyRevaluation'])->name('accounting.multi-currency.revaluations.destroy');

    Route::resource('roles', RoleController::class);

    // purchase invoices
    Route::resource('purchase-invoices', PurchaseInvoiceController::class);
    Route::post('purchase-invoices/{purchaseInvoice}/post', [PurchaseInvoiceController::class, 'post'])->name('purchase-invoices.post');
    Route::get('purchase-invoices/{purchaseInvoice}/print', [PurchaseInvoiceController::class, 'print'])->name('purchase-invoices.print');

    // sales invoices
    Route::resource('sales-invoices', SalesInvoiceController::class);
    Route::post('sales-invoices/{salesInvoice}/post', [SalesInvoiceController::class, 'post'])->name('sales-invoices.post');
    Route::get('sales-invoices/{salesInvoice}/print', [SalesInvoiceController::class, 'print'])->name('sales-invoices.print');
    Route::get('sales-invoices/warehouse/products', [SalesInvoiceController::class, 'getWarehouseProducts'])->name('sales-invoices.warehouse.products');
    Route::get('sales-invoices/services/list', [SalesInvoiceController::class, 'getServices'])->name('sales-invoices.services');

    // Delivery Notes & Dispatch Fulfillment Engine (SRS Module 9: Quote-to-Cash Workflow)
    Route::get('delivery-notes', [DeliveryNoteController::class, 'index'])->name('sales.delivery-notes.index');
    Route::post('delivery-notes', [DeliveryNoteController::class, 'store'])->name('sales.delivery-notes.store');
    Route::put('delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'update'])->name('sales.delivery-notes.update');
    Route::delete('delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'destroy'])->name('sales.delivery-notes.destroy');
    Route::post('delivery-notes/{deliveryNote}/dispatch', [DeliveryNoteController::class, 'dispatchNote'])->name('sales.delivery-notes.dispatch');
    Route::post('delivery-notes/{deliveryNote}/confirm-delivery', [DeliveryNoteController::class, 'confirmDelivery'])->name('sales.delivery-notes.confirm-delivery');
    Route::post('delivery-notes/{deliveryNote}/cancel', [DeliveryNoteController::class, 'cancelNote'])->name('sales.delivery-notes.cancel');

    // document verifications ledger
    Route::get('document-verifications', [DocumentVerificationController::class, 'index'])->name('document-verifications.index');

    // purchase returns
    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
    Route::get('purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
    Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
    Route::get('purchase-returns/{return}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
    Route::delete('purchase-returns/{return}', [PurchaseReturnController::class, 'destroy'])->name('purchase-returns.destroy');
    Route::post('purchase-returns/{return}/approve', [PurchaseReturnController::class, 'approve'])->name('purchase-returns.approve');
    Route::post('purchase-returns/{return}/complete', [PurchaseReturnController::class, 'complete'])->name('purchase-returns.complete');

    // sales returns
    Route::get('sales-returns', [SalesReturnController::class, 'index'])->name('sales-returns.index');
    Route::get('sales-returns/create', [SalesReturnController::class, 'create'])->name('sales-returns.create');
    Route::post('sales-returns', [SalesReturnController::class, 'store'])->name('sales-returns.store');
    Route::get('sales-returns/{salesReturn}', [SalesReturnController::class, 'show'])->name('sales-returns.show');
    Route::delete('sales-returns/{salesReturn}', [SalesReturnController::class, 'destroy'])->name('sales-returns.destroy');
    Route::post('sales-returns/{salesReturn}/approve', [SalesReturnController::class, 'approve'])->name('sales-returns.approve');
    Route::post('sales-returns/{salesReturn}/complete', [SalesReturnController::class, 'complete'])->name('sales-returns.complete');

    // Helpdesk Routes
    Route::resource('helpdesk-categories', HelpdeskCategoryController::class);
    Route::get('helpdesk-ticket/today', [HelpdeskTicketController::class, 'today'])->name('helpdesk-tickets.today');
    Route::resource('helpdesk-tickets', HelpdeskTicketController::class);

    // Helpdesk Replies (AJAX endpoints)
    Route::post('helpdesk-tickets/{ticket}/replies', [HelpdeskReplyController::class, 'store'])->name('helpdesk-replies.store');
    Route::delete('helpdesk-replies/{reply}', [HelpdeskReplyController::class, 'destroy'])->name('helpdesk-replies.destroy');

    // Module management routes
    Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('add-ons', fn() => redirect()->route('modules.index'))->name('add-ons.index');
    Route::get('add-on/upload', [ModuleController::class, 'upload'])->name('add-on.upload');
    Route::post('add-ons/install', [ModuleController::class, 'install'])->name('add-ons.install');
    Route::post('add-on/{name}/enable', [ModuleController::class, 'enable'])->name('add-on.enable');
    Route::get('user/active-modules', [ModuleController::class, 'getUserActiveModules'])->name('user.active-modules');
    Route::delete('user/active-modules/{moduleId}', [ModuleController::class, 'removeUserActiveModule'])->name('user.active-modules.remove');

    // Settings management routes
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings/brand', [SettingController::class, 'updateBrandSettings'])->name('settings.brand.update');
    Route::post('settings/company', [SettingController::class, 'updateCompanySettings'])->name('settings.company.update');
    Route::post('settings/system', [SettingController::class, 'updateSystemSettings'])->name('settings.system.update');
    Route::post('settings/currency', [SettingController::class, 'updateCurrencySettings'])->name('settings.currency.update');
    Route::post('settings/cache/clear', [SettingController::class, 'clearCache'])->name('settings.cache.clear');
    Route::post('settings/optimize', [SettingController::class, 'optimizeSite'])->name('settings.optimize');
    Route::post('settings/cookie', [SettingController::class, 'updateCookieSettings'])->name('settings.cookie.update');
    Route::post('settings/seo', [SettingController::class, 'updateSeoSettings'])->name('settings.seo.update');
    Route::post('settings/storage', [SettingController::class, 'updateStorageSettings'])->name('settings.storage.update');
    Route::get('settings/cookie/download', [SettingController::class, 'downloadCookieData'])->name('settings.cookie.download');
    Route::post('settings/email', [SettingController::class, 'updateEmailSettings'])->name('settings.email.update');
    Route::post('settings/email/test', [SettingController::class, 'testEmail'])->name('settings.email.test');
    Route::post('settings/pusher', [SettingController::class, 'updatePusherSettings'])->name('settings.pusher.update');
    Route::post('settings/bank-transfer', [SettingController::class, 'updateBankTransferSettings'])->name('settings.bank-transfer.update');
    Route::post('settings/paypal', [SettingController::class, 'updatePaypalSettings'])->name('paypal.settings.update');
    Route::post('settings/stripe', [SettingController::class, 'updateStripeSettings'])->name('stripe.settings.update');
    Route::post('settings/selcom', [SettingController::class, 'updateSelcomSettings'])->name('settings.selcom.update');
    Route::post('settings/pesapal', [SettingController::class, 'updatePesapalSettings'])->name('settings.pesapal.update');
    Route::post('settings/lipa-namba', [SettingController::class, 'updateLipaNambaSettings'])->name('settings.lipa-namba.update');
    Route::post('settings/ai-agent', [SettingController::class, 'updateAIAgentSettings'])->name('settings.ai-agent.update');
    Route::get('settings/ai-agent/providers', [SettingController::class, 'getAIAgentProviders'])->name('settings.ai-agent.providers');
    Route::post('email-notification-settings-save', [SettingController::class, 'mailNotificationStore'])->name('email.notification.setting.store');

    // Bank Transfer Payment routes
    Route::post('bank-transfer', [BankTransferPaymentController::class, 'store'])->name('payment.bank-transfer.store');
    Route::get('bank-transfer', [BankTransferPaymentController::class, 'index'])->name('bank-transfer.index');
    Route::post('bank-transfer/update/{id}', [BankTransferPaymentController::class, 'update'])->name('bank-transfer.update');
    Route::post('bank-transfer/{payment}/reject', [BankTransferPaymentController::class, 'reject'])->name('bank-transfer.reject');
    Route::delete('bank-transfer/{payment}', [BankTransferPaymentController::class, 'destroy'])->name('bank-transfer.destroy');

    // Language management routes
    Route::get('/languages/manage', [TranslationController::class, 'manage'])->name('languages.manage');
    Route::post('/languages/{locale}/update', [TranslationController::class, 'updateTranslations'])->name('languages.update');
    Route::get('/languages/{locale}/package/{packageName}', [TranslationController::class, 'getPackageTranslations'])->name('languages.package.translations');
    Route::post('/languages/{locale}/package/{packageName}/update', [TranslationController::class, 'updatePackageTranslations'])->name('languages.package.update');
    Route::post('/languages/create', [TranslationController::class, 'createLanguage'])->name('languages.create');
    Route::delete('/languages/{languageCode}', [TranslationController::class, 'deleteLanguage'])->name('languages.delete');
    Route::patch('/languages/{languageCode}/toggle', [TranslationController::class, 'toggleLanguageStatus'])->name('languages.toggle');
    Route::post('/languages/change', [TranslationController::class, 'changeLanguage'])->name('languages.change');

    // Email templates routes
    Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::get('email-templates/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
    Route::get('email-templates/{emailTemplate}/language/{lang}', [EmailTemplateController::class, 'getLanguageContent'])->name('email-templates.language-content');
    Route::put('email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::put('email-templates/{emailTemplate}/update-meta', [EmailTemplateController::class, 'updateMeta'])->name('email-templates.update-meta');

    // Notification template routes
    Route::get('notification-templates', [NotificationTemplateController::class, 'index'])->name('notification-templates.index');
    Route::get('notification-templates/{notificationTemplate}/edit', [NotificationTemplateController::class, 'edit'])->name('notification-templates.edit');
    Route::get('notification-templates/{notificationTemplate}/language/{lang}', [NotificationTemplateController::class, 'getLanguageContent'])->name('notification-templates.language-content');
    Route::put('notification-templates/{notificationTemplate}', [NotificationTemplateController::class, 'update'])->name('notification-templates.update');

     // Proposal Routes
    Route::resource('sales-proposals', SalesProposalController::class);
    Route::get('sales-proposals/{salesProposal}/print', [SalesProposalController::class, 'print'])->name('sales-proposals.print');
    Route::post('sales-proposals/{salesProposal}/sent', [SalesProposalController::class, 'sent'])->name('sales-proposals.sent');
    Route::post('sales-proposals/{salesProposal}/accept', [SalesProposalController::class, 'accept'])->name('sales-proposals.accept');
    Route::post('sales-proposals/{salesProposal}/reject', [SalesProposalController::class, 'reject'])->name('sales-proposals.reject');
    Route::post('sales-proposals/{salesProposal}/convert-to-invoice', [SalesProposalController::class, 'convertToInvoice'])->name('sales-proposals.convert-to-invoice');
    Route::get('sales-proposals/warehouse/products', [SalesProposalController::class, 'getWarehouseProducts'])->name('sales-proposals.warehouse.products');
    Route::get('sales-proposals/services/list', [SalesProposalController::class, 'getServices'])->name('sales-proposals.services');

    // Messenger routes
    Route::get('messenger', [MessengerController::class, 'index'])->name('messenger.index');
    Route::post('messenger/send', [MessengerController::class, 'send'])->name('messenger.send');
    Route::get('messenger/contacts', [MessengerController::class, 'getContacts'])->name('messenger.contacts');
    Route::get('messenger/messages/{userId}', [MessengerController::class, 'getMessages'])->name('messenger.messages');
    Route::post('messenger/toggle-favorite', [MessengerController::class, 'toggleFavorite'])->name('messenger.toggle-favorite');
    Route::get('messenger/favorites', [MessengerController::class, 'getFavorites'])->name('messenger.favorites');
    Route::put('messenger/messages/{messageId}/edit', [MessengerController::class, 'editMessage'])->name('messenger.edit-message');
    Route::delete('messenger/messages/{messageId}', [MessengerController::class, 'deleteMessage'])->name('messenger.delete-message');
    Route::post('/messenger/set-offline', [MessengerController::class, 'setOffline'])->name('messenger.set-offline');
    Route::post('/messenger/update-presence', [MessengerController::class, 'updatePresence'])->name('messenger.update-presence');
    Route::get('/messenger/online-users', [MessengerController::class, 'getOnlineUsers'])->name('messenger.online-users');
    Route::post('/messenger/toggle-pin', [MessengerController::class, 'togglePin'])->name('messenger.toggle-pin');
    Route::get('/messenger/pinned', [MessengerController::class, 'getPinned'])->name('messenger.pinned');
    Route::get('/messenger/check-new-messages', [MessengerController::class, 'checkNewMessages'])->name('messenger.check-new-messages');

    // AI Agent routes
    Route::prefix('ai-agent')->name('ai-agent.')->group(function () {
        Route::get('/chat-page', [AIAgentChatPageController::class, 'index'])->name('chat.page');
        Route::post('/chat', [AIAgentChatController::class, 'chat'])->name('chat');
        Route::get('/sessions', [AIAgentChatPageController::class, 'getSessions'])->name('sessions.index');
        Route::post('/sessions', [AIAgentChatPageController::class, 'createSession'])->name('sessions.store');
        Route::delete('/sessions/{session}', [AIAgentChatPageController::class, 'destroySession'])->name('sessions.destroy');
        Route::get('/sessions/{session}/messages', [AIAgentChatPageController::class, 'getMessages'])->name('sessions.messages');
    });

    // Media Library API routes
    Route::get('media-library', [MediaController::class, 'page'])->name('media-library');
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media/batch', [MediaController::class, 'batchStore'])->name('media.batch');
    Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::post('media/directories', [MediaController::class, 'createDirectory'])->name('media.directories.create');
    Route::put('media/directories/{id}', [MediaController::class, 'updateDirectory'])->name('media.directories.update');
    Route::delete('media/directories/{id}', [MediaController::class, 'destroyDirectory'])->name('media.directories.destroy');
    Route::patch('media/{id}/directory', [MediaController::class, 'updateMediaDirectory'])->name('media.directory.update');

    // Fleet & Transport Dispatch Management
    Route::get('fleet', [FleetManagementController::class, 'index'])->name('fleet.index');
    Route::post('fleet/vehicles', [FleetManagementController::class, 'storeVehicle'])->name('fleet.vehicles.store');
    Route::post('fleet/drivers', [FleetManagementController::class, 'storeDriver'])->name('fleet.drivers.store');
    Route::post('fleet/bookings', [FleetManagementController::class, 'storeBooking'])->name('fleet.bookings.store');
    Route::patch('fleet/bookings/{id}/status', [FleetManagementController::class, 'updateBookingStatus'])->name('fleet.bookings.status');
    Route::post('fleet/fuels', [FleetManagementController::class, 'storeFuel'])->name('fleet.fuels.store');
    Route::post('fleet/maintenances', [FleetManagementController::class, 'storeMaintenance'])->name('fleet.maintenances.store');

    // Project Timesheets & Costing
    Route::get('project/timesheets', [ProjectTimesheetController::class, 'index'])->name('project.timesheets.index');
    Route::post('project/timesheets', [ProjectTimesheetController::class, 'store'])->name('project.timesheets.store');
    Route::patch('project/timesheets/{id}/status', [ProjectTimesheetController::class, 'updateStatus'])->name('project.timesheets.status');
    Route::delete('project/timesheets/{id}', [ProjectTimesheetController::class, 'destroy'])->name('project.timesheets.destroy');

    // Fixed Asset Management & Equipment Servicing (SRS Modules 20 & 21)
        // Warranty & Servicing Management (SRS Module 21)
        // Self-Service Portals (SRS Modules 27 & 28)
        // Database Backups & Disaster Recovery (SRS Module 34)
        // Sales Orders & Stock Allocation (SRS Â§3.9.1, Â§3.9.4)
    Route::get('sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index');
    Route::post('sales-orders', [SalesOrderController::class, 'store'])->name('sales-orders.store');
    Route::post('sales-orders/convert-proposal/{proposalId}', [SalesOrderController::class, 'convertProposalToOrder'])->name('sales-orders.convert-proposal');
    Route::post('sales-orders/{id}/toggle-stock', [SalesOrderController::class, 'toggleStockReservation'])->name('sales-orders.toggle-stock');
    Route::post('sales-orders/{id}/convert-delivery', [SalesOrderController::class, 'convertToDeliveryNote'])->name('sales-orders.convert-delivery');
    Route::post('sales-orders/{id}/convert-invoice', [SalesOrderController::class, 'convertToInvoice'])->name('sales-orders.convert-invoice');
    Route::delete('sales-orders/{id}', [SalesOrderController::class, 'destroy'])->name('sales-orders.destroy');
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [BackupController::class, 'createBackup'])->name('backups.create');
    Route::get('backups/{filename}/download', [BackupController::class, 'downloadBackup'])->name('backups.download');
    Route::post('backups/{filename}/restore', [BackupController::class, 'restoreBackup'])->name('backups.restore');
    Route::delete('backups/{filename}', [BackupController::class, 'destroyBackup'])->name('backups.destroy');
    Route::get('portal/customer', [PortalController::class, 'customerPortal'])->name('portal.customer');
    Route::post('portal/customer/proposals/{id}/accept', [PortalController::class, 'customerAcceptProposal'])->name('portal.customer.accept-proposal');
    Route::post('portal/customer/proposals/{id}/reject', [PortalController::class, 'customerRejectProposal'])->name('portal.customer.reject-proposal');
    Route::post('portal/customer/invoices/{id}/payment', [PortalController::class, 'customerSubmitPayment'])->name('portal.customer.payment');
    Route::post('portal/customer/tickets', [PortalController::class, 'customerSubmitTicket'])->name('portal.customer.ticket');
    Route::get('portal/supplier', [PortalController::class, 'supplierPortal'])->name('portal.supplier');
    Route::post('portal/supplier/rfqs/{id}/bid', [PortalController::class, 'supplierSubmitBid'])->name('portal.supplier.bid');
    Route::get('warranty-servicing', [WarrantyServicingController::class, 'index'])->name('warranty.index');
    Route::post('warranty-servicing/warranties', [WarrantyServicingController::class, 'storeWarranty'])->name('warranty.warranties.store');
    Route::post('warranty-servicing/tickets', [WarrantyServicingController::class, 'storeTicket'])->name('warranty.tickets.store');
    Route::put('warranty-servicing/tickets/{id}', [WarrantyServicingController::class, 'updateTicket'])->name('warranty.tickets.update');
    Route::get('warranty-servicing/lookup', [WarrantyServicingController::class, 'lookup'])->name('warranty.lookup');
    Route::get('assets', [FixedAssetController::class, 'index'])->name('assets.index');
    Route::post('assets', [FixedAssetController::class, 'storeAsset'])->name('assets.store');
    Route::post('assets/depreciation', [FixedAssetController::class, 'runDepreciation'])->name('assets.depreciation.run');
    Route::post('assets/transfers', [FixedAssetController::class, 'storeTransfer'])->name('assets.transfers.store');
    Route::post('assets/disposals', [FixedAssetController::class, 'storeDisposal'])->name('assets.disposals.store');
    Route::post('assets/warranties', [FixedAssetController::class, 'storeWarranty'])->name('assets.warranties.store');
    Route::post('assets/service-tickets', [FixedAssetController::class, 'storeServiceTicket'])->name('assets.service-tickets.store');
    Route::patch('assets/service-tickets/{id}/status', [FixedAssetController::class, 'updateTicketStatus'])->name('assets.service-tickets.status');

    // Multi-Tier Approval Workflow Engine (SRS Module 24)
    Route::get('approvals', [ApprovalWorkflowController::class, 'index'])->name('approvals.index');
    Route::post('approvals/delegations', [\App\Http\Controllers\ApprovalDelegationController::class, 'store'])->name('approvals.delegations.store');
    Route::post('approvals/delegations/{delegation}/revoke', [\App\Http\Controllers\ApprovalDelegationController::class, 'revoke'])->name('approvals.delegations.revoke');
    Route::post('approvals/policies', [ApprovalWorkflowController::class, 'storePolicy'])->name('approvals.policies.store');
    Route::post('approvals/policies/{id}/toggle', [ApprovalWorkflowController::class, 'togglePolicy'])->name('approvals.policies.toggle');
    Route::post('approvals/submit', [ApprovalWorkflowController::class, 'submitDocument'])->name('approvals.submit');
    Route::post('approvals/{id}/action', [ApprovalWorkflowController::class, 'processAction'])->name('approvals.action');

    // Unified Payment Gateway & Lipa Namba Integration Engine (SRS Module 33 & Sections 8.3 & 19)
    Route::get('payment-gateways', [PaymentGatewayController::class, 'index'])->name('payment-gateways.index');
    Route::post('payment-gateways', [PaymentGatewayController::class, 'storeGateway'])->name('payment-gateways.store');
    Route::put('payment-gateways/{id}', [PaymentGatewayController::class, 'updateGateway'])->name('payment-gateways.update');
    Route::delete('payment-gateways/{id}', [PaymentGatewayController::class, 'destroyGateway'])->name('payment-gateways.destroy');
    Route::post('payment-gateways/{id}/toggle', [PaymentGatewayController::class, 'toggleGateway'])->name('payment-gateways.toggle');
    Route::post('payment-gateways/generate-link', [PaymentGatewayController::class, 'generatePaymentLink'])->name('payment-gateways.generate-link');
    Route::post('payment-gateways/manual-settle', [PaymentGatewayController::class, 'manualSettlement'])->name('payment-gateways.manual-settle');
    Route::put('payment-gateways/transactions/{id}/reconcile', [PaymentGatewayController::class, 'reconcileTransaction'])->name('payment-gateways.reconcile');
    Route::post('payment-gateways/transactions/{id}/void', [PaymentGatewayController::class, 'voidTransaction'])->name('payment-gateways.void');

    // Paperless Office Dynamic Document Template Builder (SRS Module 25)
    Route::get('paperless/templates', [DocumentTemplateController::class, 'index'])->name('paperless.templates.index');
    Route::post('paperless/templates', [DocumentTemplateController::class, 'store'])->name('paperless.templates.store');
    Route::put('paperless/templates/{template}', [DocumentTemplateController::class, 'update'])->name('paperless.templates.update');
    Route::delete('paperless/templates/{template}', [DocumentTemplateController::class, 'destroy'])->name('paperless.templates.destroy');
    Route::post('paperless/templates/{template}/set-default', [DocumentTemplateController::class, 'setDefault'])->name('paperless.templates.set-default');
});

Route::get('/storage-link', function () {
    Artisan::call('storage:unlink');
    Artisan::call('storage:link');
    return 'done';
});

Route::get('/translations/{locale}', [TranslationController::class, 'getTranslations'])->name('languages.translations');
Route::post('/cookie-consent-log', [SettingController::class, 'logCookieConsent'])->name('cookie.consent.log');

//for Instagramchat & Facebookchat
Route::any('/meta/callback', [MetaController::class, 'handleWebhook'])->name('meta.callback');

// Document Verification Routes (Public Engine)
Route::get('/verify', [DocumentVerificationController::class, 'publicVerifyPortal'])->name('document.verify.portal');
Route::get('/verify/document/{hash?}', [DocumentVerificationController::class, 'verify'])->name('document.verify');
Route::get('/api/v1/verify/{hash}', [DocumentVerificationController::class, 'apiVerify'])->name('api.document.verify');

// Public Customer Invoice Checkout & Payment Portal (SRS Module 33)
Route::get('/pay/checkout/{token}', [PaymentGatewayController::class, 'publicCheckout'])->name('payment.checkout');
Route::post('/pay/checkout/{token}', [PaymentGatewayController::class, 'processPublicPayment'])->name('payment.checkout.process');
Route::get('/pay/checkout/{token}/success', [PaymentGatewayController::class, 'checkoutSuccess'])->name('payment.checkout.success');
Route::post('/api/v1/payments/webhook/{gateway}', [PaymentGatewayController::class, 'webhook'])->name('payment.webhook');

require __DIR__.'/installer.php';
require __DIR__.'/updater.php';
require __DIR__.'/auth.php';
