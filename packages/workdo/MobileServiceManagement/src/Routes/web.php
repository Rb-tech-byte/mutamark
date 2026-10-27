<?php

use Workdo\MobileServiceManagement\Http\Controllers\MobileServicePolicyController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceContractController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileTrackingStatusController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileDeviceBrandController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceCategoryController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceSetupController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceContactController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceReviewController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceRequestController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServicePendingRequestController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServicePaymentController;
use Illuminate\Support\Facades\Route;
use Workdo\MobileServiceManagement\Http\Controllers\DashboardController;
use Workdo\MobileServiceManagement\Http\Controllers\FrontendController;
use Workdo\MobileServiceManagement\Http\Controllers\MobileServiceCustomPageController;
use Workdo\MobileServiceManagement\Http\Controllers\ServiceHistoryController;
use Workdo\MobileServiceManagement\Http\Middleware\MobileServiceSharedDataMiddleware;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:MobileServiceManagement'])->group(function () {
    Route::get('/mobile-service-management', [DashboardController::class, 'index'])->name('mobile-service.index');

    Route::prefix('mobile-service/device-brands')->name('mobile-service.device-brands.')->group(function () {
        Route::get('/', [MobileDeviceBrandController::class, 'index'])->name('index');
        Route::post('/', [MobileDeviceBrandController::class, 'store'])->name('store');
        Route::put('/{mobiledevicebrand}', [MobileDeviceBrandController::class, 'update'])->name('update');
        Route::delete('/{mobiledevicebrand}', [MobileDeviceBrandController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('mobile-service/tracking-statuses')->name('mobile-service.tracking-statuses.')->group(function () {
        Route::get('/', [MobileTrackingStatusController::class, 'index'])->name('index');
        Route::post('/', [MobileTrackingStatusController::class, 'store'])->name('store');
        Route::post('/update-order', [MobileTrackingStatusController::class, 'updateOrder'])->name('update-order');
        Route::put('/{mobiletrackingstatus}', [MobileTrackingStatusController::class, 'update'])->name('update');
        Route::delete('/{mobiletrackingstatus}', [MobileTrackingStatusController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('mobile-service/service-contracts')->name('mobile-service-management.mobile-service-contracts.')->group(function () {
        Route::get('/', [MobileServiceContractController::class, 'index'])->name('index');
        Route::post('/', [MobileServiceContractController::class, 'store'])->name('store');
        Route::put('/{mobileservicecontract}', [MobileServiceContractController::class, 'update'])->name('update');
        Route::delete('/{mobileservicecontract}', [MobileServiceContractController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mobile-service/service-policies')->name('mobile-service-management.mobile-service-policies.')->group(function () {
        Route::get('/', [MobileServicePolicyController::class, 'index'])->name('index');
        Route::post('/', [MobileServicePolicyController::class, 'store'])->name('store');
        Route::put('/{mobileservicepolicy}', [MobileServicePolicyController::class, 'update'])->name('update');
        Route::delete('/{mobileservicepolicy}', [MobileServicePolicyController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mobile-service/site-settings')->name('mobile-service.site-settings.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'siteSettingsIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'siteSettingsStore'])->name('store');
    });

    Route::prefix('mobile-service/banner-section')->name('mobile-service.banner-section.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'bannerSectionIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'bannerSectionStore'])->name('store');
    });
    Route::prefix('mobile-service/about-section')->name('mobile-service.about-section.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'aboutSectionIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'aboutSectionStore'])->name('store');
    });

    Route::prefix('mobile-service/about-title-section')->name('mobile-service.about-title-section.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'aboutTitleSectionIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'aboutTitleSectionStore'])->name('store');
    });

    Route::prefix('mobile-service/contact-us-section')->name('mobile-service.contact-us-section.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'contactUsSectionIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'contactUsSectionStore'])->name('store');
    });

    Route::prefix('mobile-service/social-links-section')->name('mobile-service.social-links-section.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'socialLinksSectionIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'socialLinksSectionStore'])->name('store');
    });

    Route::prefix('mobile-service/faqs-section')->name('mobile-service.faqs-section.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'faqsSectionIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'faqsSectionStore'])->name('store');
    });

    Route::prefix('mobile-service/custom-pages')->name('mobile-service.custom-pages.')->group(function () {
        Route::get('/', [MobileServiceCustomPageController::class, 'index'])->name('index');
        Route::post('/', [MobileServiceCustomPageController::class, 'store'])->name('store');
        Route::get('/{customPage}/edit', [MobileServiceCustomPageController::class, 'edit'])->name('edit');
        Route::put('/{customPage}', [MobileServiceCustomPageController::class, 'update'])->name('update');
        Route::delete('/{customPage}', [MobileServiceCustomPageController::class, 'destroy'])->name('destroy');
    });

     Route::prefix('mobile-service/home-section')->name('mobile-service.home-section.')->group(function () {
        Route::get('/', [MobileServiceSetupController::class, 'homeSectionIndex'])->name('index');
        Route::post('/', [MobileServiceSetupController::class, 'homeSectionStore'])->name('store');
    });

    Route::prefix('mobile-service-management/mobile-service-contacts')->name('mobile-service-management.mobile-service-contacts.')->group(function () {
        Route::get('/', [MobileServiceContactController::class, 'index'])->name('index');
        Route::delete('/{mobileservicecontact}', [MobileServiceContactController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mobile-service-management/mobile-service-reviews')->name('mobile-service-management.mobile-service-reviews.')->group(function () {
        Route::get('/', [MobileServiceReviewController::class, 'index'])->name('index');
        Route::delete('/{mobileservicereview}', [MobileServiceReviewController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mobile-service-management/pending-service-requests')->name('mobile-service-management.pending-service-requests.')->group(function () {
        Route::get('/', [MobileServicePendingRequestController::class, 'index'])->name('index');
        Route::get('/{pendingservicerequest}', [MobileServicePendingRequestController::class, 'show'])->name('show');
        Route::post('/{pendingservicerequest}/approve', [MobileServicePendingRequestController::class, 'approve'])->name('approve');
        Route::post('/{pendingservicerequest}/reject', [MobileServicePendingRequestController::class, 'reject'])->name('reject');
        Route::delete('/{pendingservicerequest}', [MobileServicePendingRequestController::class, 'destroy'])->name('destroy');
        Route::post('/{pendingservicerequest}/delete-attachment', [MobileServicePendingRequestController::class, 'deleteAttachment'])->name('delete-attachment');
    });

    Route::prefix('mobile-service-management/service-requests')->name('mobile-service-management.service-requests.')->group(function () {
        Route::get('/', [MobileServiceRequestController::class, 'index'])->name('index');
        Route::get('/create', [MobileServiceRequestController::class, 'create'])->name('create');
        Route::post('/', [MobileServiceRequestController::class, 'store'])->name('store');
        Route::get('/{mobileservicerequest}/edit', [MobileServiceRequestController::class, 'edit'])->name('edit');
        Route::put('/{mobileservicerequest}', [MobileServiceRequestController::class, 'update'])->name('update');
        Route::get('/{mobileservicerequest}', [MobileServiceRequestController::class, 'show'])->name('show');
        Route::post('/{mobileservicerequest}/update-tracking', [MobileServiceRequestController::class, 'updateTrackingStatus'])->name('update-tracking');
        Route::post('/{mobileservicerequest}/assign-technician', [MobileServiceRequestController::class, 'assignTechnician'])->name('assign-technician');
        Route::post('/{mobileservicerequest}/end-repair', [MobileServiceRequestController::class, 'endRepair'])->name('end-repair');
        Route::post('/{mobileservicerequest}/delete-attachment', [MobileServiceRequestController::class, 'deleteAttachment'])->name('delete-attachment');
        Route::delete('/{mobileservicerequest}', [MobileServiceRequestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mobile-service-management/payments')->name('mobile-service-management.payments.')->group(function () {
        Route::get('/', [MobileServicePaymentController::class, 'index'])->name('index');
        Route::post('/', [MobileServicePaymentController::class, 'store'])->name('store');
        Route::get('/{payment}', [MobileServicePaymentController::class, 'show'])->name('show');
        Route::get('/{payment}/print', [MobileServicePaymentController::class, 'print'])->name('print');
        Route::put('/{payment}', [MobileServicePaymentController::class, 'update'])->name('update');
        Route::patch('/{payment}/update-status', [MobileServicePaymentController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{payment}', [MobileServicePaymentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mobile-service-management/service-history')->name('mobile-service-management.service-history.')->group(function () {
        Route::get('/', [ServiceHistoryController::class, 'index'])->name('index');
        Route::get('/{mobileServiceRequest}', [ServiceHistoryController::class, 'show'])->name('show');
        Route::delete('/{mobileServiceRequest}', [ServiceHistoryController::class, 'destroy'])->name('destroy');
    });
});

// Frontend Routes (Public) - Multi-tenant support
Route::middleware(['web', MobileServiceSharedDataMiddleware::class])->prefix('{userSlug?}/mobile-service')->name('mobile-service.frontend.')->group(function () {
    Route::get('/', [FrontendController::class, 'index'])->name('index');
    Route::get('/services', [FrontendController::class, 'services'])->name('services');
    Route::get('/bookings', [FrontendController::class, 'booking'])->name('booking');
    Route::get('/about', [FrontendController::class, 'about'])->name('about');
    Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
    Route::post('/contact', [FrontendController::class, 'contactStore'])->name('contact.store');
    Route::post('/booking', [FrontendController::class, 'submitBooking'])->name('booking.store');
    Route::get('/faq', [FrontendController::class, 'faq'])->name('faq');
    Route::get('/service-details/{id?}', [FrontendController::class, 'serviceDetails'])->name('service-details');
    Route::get('/order-details/{id?}', [FrontendController::class, 'showTracking'])->name('order-details');
    Route::post('/track-service', [FrontendController::class, 'trackService'])->name('track-service');
    Route::post('/service/{service}/reviews', [FrontendController::class, 'storeReview'])->name('reviews.store');
    Route::get('/{slug}', [FrontendController::class, 'customPage'])->name('custom-page');
});
