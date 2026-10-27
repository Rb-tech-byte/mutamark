<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Route;
use Workdo\Bookings\Http\Controllers\BookingsItemsController;
use Workdo\ProductService\Http\Controllers\ProductServiceItemController;
use Workdo\Bookings\Http\Controllers\BookingsCustomersController;
use Workdo\Bookings\Http\Controllers\BookingsBusinessHoursController;
use Workdo\Bookings\Http\Controllers\BookingsAppointmentsController;
use Workdo\Bookings\Http\Controllers\BookingsStaffController;
use Workdo\Bookings\Http\Controllers\BookingsPackageController;
use Workdo\Bookings\Http\Controllers\BookingsDashboardController;
use Workdo\Bookings\Http\Controllers\BookingsExtraServicesController;
use Workdo\Bookings\Http\Controllers\BookingsFrontendAppointmentsController;
use Workdo\Bookings\Http\Controllers\BookingsLocationController;

Route::group(['middleware' => ['web', 'auth', 'verified','PlanModuleCheck:Bookings']], function () {
    Route::prefix('bookings')->group(function() {

        Route::get('bookings', [ProductServiceItemController::class,'create'])->name('bookings-item');
        Route::get('bookings-dashboard',[BookingsDashboardController::class,'index'])->name('bookings.dashboard');

        Route::resource('booking-items', BookingsItemsController::class);
        Route::resource('booking-customers', BookingsCustomersController::class);
        Route::resource('booking-appointments', BookingsAppointmentsController::class);
        Route::resource('booking-staff', BookingsStaffController::class);
        Route::resource('bookings-extra-services', BookingsExtraServicesController::class);
        Route::resource('booking-package', BookingsPackageController::class);
        Route::resource('bookings-location', BookingsLocationController::class);

        Route::post('/booking/staff/service/get/{id}', [BookingsStaffController::class,'relatedServiceStore'])->name('booking.staff.service.get');

        Route::get('bookings-appointment-kanban',[BookingsAppointmentsController::class,'kanban'])->name('booking-appointments.kanban');
        Route::Post('bookings-appointment/stage/order',[BookingsAppointmentsController::class,'stageOrder'])->name('bookings.appointments.stage.order');
        Route::get('bookings-appointment/calendar', [BookingsAppointmentsController::class, 'calendar'])->name('bookings.appointments.calendar');
        Route::any('/search-appointment', [BookingsAppointmentsController::class,'searchAppointment'])->name('search.appointment');
        Route::get('booking-package/{id}/duplicate', [BookingsPackageController::class, 'duplicate'])->name('booking-package.duplicate');
        Route::post('/package-item/price', [BookingsPackageController::class,'packageItemPrice'])->name('package.item.price');

        Route::resource('bookings-business-hours', BookingsBusinessHoursController::class);
        Route::get('bookings-appointment-status-change/{id}',[BookingsAppointmentsController::class,'bookingsappointmentStatusChange'])->name('bookings.appointment.status.change');
        Route::post('bookings-appointment-status-update/{id}',[BookingsAppointmentsController::class,'bookingsappointmentStatusUpdate'])->name('bookings.appointment.status.update');

        Route::get('item-description/{id}', [BookingsItemsController::class,'description'])->name('booking.item.description');
    });
});

Route::middleware(['web'])->group(function ()
{
    Route::prefix('bookings')->group(function() {
        Route::post('getUser',[BookingsCustomersController::class,'getUser'])->name('bookings.getuser');
        Route::post('/booking-package/service', [BookingsPackageController::class,'packageService'])->name('package.service');

        Route::get('/bookings-frontend-appointment/{slug}/{lang?}', [BookingsFrontendAppointmentsController::class,'index'])->name('bookings.frontend.appointment');
        Route::post('/bookings-frontend-appointment-store/{slug}/{lang?}', [BookingsFrontendAppointmentsController::class,'store'])->name('bookings.frontend.appointment.store');
        Route::any('/bookings-search-appointment/{id}/{slug}', [BookingsFrontendAppointmentsController::class,'bookingsSearchAppointment'])->name('bookings.search.appointment');
    });
});
