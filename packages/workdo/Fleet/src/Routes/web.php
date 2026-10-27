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
use Workdo\Fleet\Http\Controllers\FleetController;
use Workdo\Fleet\Http\Controllers\DriverController;
use Workdo\Fleet\Http\Controllers\LicenseController;
use Workdo\Fleet\Http\Controllers\VehicleTypeController;
use Workdo\Fleet\Http\Controllers\FuelTypeController;
use Workdo\Fleet\Http\Controllers\RecurringController;
use Workdo\Fleet\Http\Controllers\MaintenanceTypeController;
use Workdo\Fleet\Http\Controllers\MaintenanceReportController;
use Workdo\Fleet\Http\Controllers\FuelHistroyReportController;
use Workdo\Fleet\Http\Controllers\CustomerController;
use Workdo\Fleet\Http\Controllers\VehicleController;
use Workdo\Fleet\Http\Controllers\InsuranceController;
use Workdo\Fleet\Http\Controllers\FuelController;
use Workdo\Fleet\Http\Controllers\BookingController;
use Workdo\Fleet\Http\Controllers\MaintenanceController;
use Workdo\Fleet\Http\Controllers\ChargepayableController;
use Workdo\Fleet\Http\Controllers\AvailabilityController;
use Workdo\Fleet\Http\Controllers\LogbookController;
use Workdo\Fleet\Http\Controllers\InsuranceBookingController;

Route::group(['middleware' => ['web', 'auth', 'verified','PlanModuleCheck:Fleet']], function () {
    Route::get('dashboard/fleet',[FleetController::class,'index'])->name('fleet.dashboard');

    Route::resource('driver', DriverController::class);
    Route::resource('driver-permits', LicenseController::class)->names('license');
    Route::resource('vehicleType', VehicleTypeController::class);
    Route::resource('fuelType', FuelTypeController::class);
    Route::resource('recuerring', RecurringController::class);
    Route::resource('maintenanceType', MaintenanceTypeController::class);
    Route::resource('fleet_customer', CustomerController::class);
    Route::resource('vehicle', VehicleController::class);
    Route::resource('insurance', InsuranceController::class);
    Route::resource('logbook', LogbookController::class);
    // Route::resource('insurance-booking', 'InsuranceBookingController');
    Route::resource('fuel', FuelController::class);
    Route::resource('booking', BookingController::class);
    Route::resource('maintenance_report', MaintenanceReportController::class);
    Route::resource('fuel_history_report', FuelHistroyReportController::class);

    Route::get('driver-grid', [DriverController::class,'grid'])->name('driver.grid');
    Route::post('driver-attechment/{id}', [DriverController::class, 'driverAttechment'])->name('driver.file.upload');
    Route::delete('driver-attechment/destroy/{id}', [DriverController::class, 'driverAttechmentDestroy'])->name('driver.attachment.destroy');
    Route::get('Addpayment/{id}', [BookingController::class,'Addpayment'])->name('Addpayment.create');
    Route::post('Addpayment/store/{id}', [BookingController::class,'PaymentStore'])->name('Addpayment.store');
    Route::DELETE('payment/destory/{id}/', [BookingController::class,'PaymentDestory'])->name('payment.delete');
    Route::post('fleet-settings-save', [BookingController::class, 'FleetSettingsStore'])->name('fleet.setting.store');
    Route::post('/logbook/item/rate', [LogbookController::class,'Itemrate'])->name('item.rate');


    Route::post('driver/getUser',[DriverController::class,'getUser'])->name('driver.getuser');
    Route::post('customer/getUser',[CustomerController::class,'getUser'])->name('customer.getuser');

    // Route::post('vehicle/get-item', [VehicleTypeController::class, 'GetItem'])->name('vehicle.get.item');
    Route::post('vehicle-attechment/{id}', [VehicleController::class, 'vehicleAttechment'])->name('vehicle.file.upload');
    Route::delete('vehicle-attechment/destroy/{id}', [VehicleController::class, 'vehicleAttechmentDestroy'])->name('vehicle.attachment.destroy');
    Route::any('fleet-sale', [VehicleTypeController::class, 'items'])->name('fleet.sale.items');

    Route::post('driver-status/{id}/{status}', [DriverController::class, 'driverstatus'])->name('driver.status');

    Route::resource('maintenance', MaintenanceController::class);
    Route::resource('availability', AvailabilityController::class);

    Route::get('insurance-booking/create/{id}', [InsuranceBookingController::class,'create'])->name('insurance-booking.create');
    Route::post('insurance-booking/store/{id}', [InsuranceBookingController::class,'store'])->name('insurance-booking.store');
    Route::get('insurance-booking/edit/{id}', [InsuranceBookingController::class,'edit'])->name('insurance-booking.edit');
    Route::post('insurance-booking/update/{id}', [InsuranceBookingController::class,'update'])->name('insurance-booking.update');
    Route::delete('insurance-booking/destroy/{id}', [InsuranceBookingController::class,'Destroy'])->name('insurance-booking.destroy');
    // Route::get('allowances/create/{eid}', [AllowanceController::class, 'allowanceCreate'])->name('allowances.create');
});
