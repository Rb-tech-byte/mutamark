<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\AppointmentController;
use Modules\Appointment\Http\Controllers\AppointmentsController;
use Modules\Appointment\Http\Controllers\DashboardController;
use Modules\Appointment\Http\Controllers\PublicAppointmentController;
use Modules\Appointment\Http\Controllers\QuestionController;
use Modules\Appointment\Http\Controllers\ScheduleController;

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


Route::group(['middleware' => 'PlanModuleCheck:Appointment'], function () {
    Route::prefix('appointment')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->middleware(['auth']);
    });

    Route::get('dashboard/appointment', [DashboardController::class, 'index'])->name('appointment.dashboard')->middleware(['auth']);

    Route::resource('appointments', AppointmentsController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('appointments-calender', [AppointmentsController::class, 'calender'])->name('appointments.calender')->middleware(
        [
            'auth',
        ]
    );

    Route::get('appointment/{id}/scheduleshow', [AppointmentsController::class, 'scheduleshow'])->name('appointments.scheduleshow')->middleware(
        [
            'auth',
        ]
    );

    Route::resource('questions', QuestionController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('schedules', ScheduleController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::get('schedules/{id}/action', [ScheduleController::class, 'action'])->name('schedules.action')->middleware(
        [
            'auth',
        ]
    );
    Route::post('schedules/changeaction', [ScheduleController::class, 'changeaction'])->name('schedules.changeaction')->middleware(
        [
            'auth',
        ]
    );

    Route::get('callbacks/{id}/action', [ScheduleController::class, 'callbackaction'])->name('callback.callbackaction')->middleware(
        [
            'auth',
        ]
    );
    Route::post('callbacks/changeaction', [ScheduleController::class, 'callbackchangeaction'])->name('callback.callbackchangeaction')->middleware(
        [
            'auth',
        ]
    );

    Route::delete('callbacks/Delete/{id}', [ScheduleController::class, 'CallbackDestroy'])->name('callback.destroy')->middleware(
        [
            'auth',
        ]
    );
});

Route::get('{slug}/appointments/{id}', [PublicAppointmentController::class, 'create'])->name('appointments');
Route::post('{slug}/appointments-store/{id}', [PublicAppointmentController::class, 'store'])->name('appointments.store');
Route::get('{slug}/appointment-search', [PublicAppointmentController::class, 'search'])->name('get.appointment.search');
Route::post('{slug}/appointment/post/search', [PublicAppointmentController::class, 'appointmentSearch'])->name('appointment.search');
Route::get('{slug}/appointment/{id}', [PublicAppointmentController::class, 'index'])->name('appointment.view');
Route::post('{slug}/support-ticket/{id}', [PublicAppointmentController::class, 'CancelForm'])->name('appointment.cancel_form');
Route::post('{slug}/appointment-callback/{id}', [PublicAppointmentController::class, 'Callback'])->name('appointment.callback');
