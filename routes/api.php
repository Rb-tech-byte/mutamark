<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

Route::middleware(['api.json', \App\Http\Middleware\LogApiRequest::class])->group(function () {

    Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureAccountEnabled::class, 'throttle:60,1'])->group(function () {
        Route::prefix('v1')->group(function () {
            Route::post('customers', [\Workdo\Account\Http\Controllers\CustomerController::class,'store']);
            Route::put('customers/{customer}', [\Workdo\Account\Http\Controllers\CustomerController::class,'update']);
            Route::post('suppliers', [\Workdo\Account\Http\Controllers\VendorController::class,'store']);
            Route::put('suppliers/{vendor}', [\Workdo\Account\Http\Controllers\VendorController::class,'update']);
            Route::post('products', [\Workdo\ProductService\Http\Controllers\ProductServiceItemController::class,'store']);
            Route::put('products/{item}', [\Workdo\ProductService\Http\Controllers\ProductServiceItemController::class,'update']);
            Route::post('sales-invoices', [\App\Http\Controllers\SalesInvoiceController::class,'store']);
            Route::put('sales-invoices/{salesInvoice}', [\App\Http\Controllers\SalesInvoiceController::class,'update']);
            Route::post('purchase-invoices', [\App\Http\Controllers\PurchaseInvoiceController::class,'store']);
            Route::put('purchase-invoices/{purchaseInvoice}', [\App\Http\Controllers\PurchaseInvoiceController::class,'update']);
            Route::post('supplier/orders/{purchaseOrder}/accept', [\App\Http\Controllers\PortalController::class,'supplierAcceptOrder']);
            Route::post('supplier/orders/{purchaseOrder}/attachments', [\App\Http\Controllers\PortalController::class,'supplierUpload']);
            Route::patch('supplier/profile', [\App\Http\Controllers\PortalController::class,'supplierUpdateProfile']);
            Route::get('tax-summary', [\Workdo\Account\Http\Controllers\ReportsController::class, 'taxSummary']);
            Route::post('withholding/{id}/remit', [\Workdo\Account\Http\Controllers\ReportsController::class, 'remitWithholding']);
            Route::post('accounting/controls', [\Workdo\Account\Http\Controllers\ReportsController::class, 'accountingControls']);
            Route::post('sales-invoices/{salesInvoice}/reconcile', [\App\Http\Controllers\FinancialAuditTrailController::class, 'reconcileInvoice']);
            Route::post('purchase-orders/{purchaseOrder}/action', [\App\Http\Controllers\GoodsReceivedNoteController::class, 'purchaseOrderAction']);
            Route::get('sales-orders', [\App\Http\Controllers\SalesOrderController::class, 'index']);
            Route::post('sales-orders', [\App\Http\Controllers\SalesOrderController::class, 'store']);
            Route::post('sales-orders/{id}/reserve', [\App\Http\Controllers\SalesOrderController::class, 'toggleStockReservation']);
            Route::post('sales-orders/{id}/deliver', [\App\Http\Controllers\SalesOrderController::class, 'convertToDeliveryNote']);
            Route::post('sales-orders/{id}/invoice', [\App\Http\Controllers\SalesOrderController::class, 'convertToInvoice']);
            Route::post('sales-invoices/{salesInvoice}/post', [\App\Http\Controllers\SalesInvoiceController::class, 'post']);
            Route::post('purchase-invoices/{purchaseInvoice}/post', [\App\Http\Controllers\PurchaseInvoiceController::class, 'post']);
            Route::post('goods-received-notes/{id}/post', [\App\Http\Controllers\GoodsReceivedNoteController::class, 'post']);
            Route::post('customer-payments', [\Workdo\Account\Http\Controllers\CustomerPaymentController::class, 'store']);
            Route::patch('customer-payments/{customerPayment}/status', [\Workdo\Account\Http\Controllers\CustomerPaymentController::class, 'updateStatus']);
            Route::post('supplier-payments', [\Workdo\Account\Http\Controllers\VendorPaymentController::class, 'store']);
            Route::patch('supplier-payments/{vendorPayment}/status', [\Workdo\Account\Http\Controllers\VendorPaymentController::class, 'updateStatus']);
            Route::post('approvals/{id}/action', [\App\Http\Controllers\ApprovalWorkflowController::class, 'processAction']);
            Route::post('approval-delegations', [\App\Http\Controllers\ApprovalDelegationController::class, 'store']);
            Route::delete('approval-delegations/{delegation}', [\App\Http\Controllers\ApprovalDelegationController::class, 'revoke']);
            Route::get('stock-adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'index']);
            Route::post('stock-adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'store']);
            Route::get('stock-adjustments/scan', [\App\Http\Controllers\StockAdjustmentController::class, 'scan']);
            Route::post('stock-adjustments/{stockAdjustment}/post', [\App\Http\Controllers\StockAdjustmentController::class, 'postAdjustment']);
            Route::get('documents', [\App\Http\Controllers\DocumentController::class, 'index']);
            Route::post('documents', [\App\Http\Controllers\DocumentController::class, 'store']);
            Route::get('documents/{document}', [\App\Http\Controllers\DocumentController::class, 'show']);
            Route::post('documents/{document}/versions', [\App\Http\Controllers\DocumentController::class, 'version']);
            Route::get('documents/{document}/download/{version?}', [\App\Http\Controllers\DocumentController::class, 'download']);
            Route::delete('documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy']);
            Route::get('proformas', [\App\Http\Controllers\ProformaInvoiceController::class, 'index']);
            Route::post('proformas', [\App\Http\Controllers\ProformaInvoiceController::class, 'store']);
            Route::post('proformas/{proforma}/convert', [\App\Http\Controllers\ProformaInvoiceController::class, 'convert']);
            Route::post('proformas/{proforma}/cancel', [\App\Http\Controllers\ProformaInvoiceController::class, 'cancel']);
            Route::get('{resource}', [\App\Http\Controllers\Api\ErpResourceController::class, 'index']);
            Route::get('{resource}/{id}', [\App\Http\Controllers\Api\ErpResourceController::class, 'show'])->whereNumber('id');
        });
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::post('/refresh', [AuthApiController::class, 'refresh']);
        Route::post('/change-password', [AuthApiController::class, 'changePassword']);
        Route::post('/two-factor', [\App\Http\Controllers\ProfileController::class, 'twoFactor'])->middleware('throttle:5,1');
        Route::post('/edit-profile', [AuthApiController::class, 'editProfile']);
        Route::delete('/delete-account', [AuthApiController::class, 'deleteAccount']);

        // get staff user
        Route::get('/staff-user', [AuthApiController::class, 'getStaffUser']);
        // get client user
        Route::get('/client-user', [AuthApiController::class, 'getClientUser']);
        // get vendor user
        Route::get('/vendor-user', [AuthApiController::class, 'getVendorUser']);
    });
});
