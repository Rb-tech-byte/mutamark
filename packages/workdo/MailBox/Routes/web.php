<?php
use Illuminate\Support\Facades\Route;
use Modules\MailBox\Http\Controllers\MailBoxController;
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



Route::group(['middleware' => 'PlanModuleCheck:MailBox'], function () {
    Route::prefix('mailbox')->group(function () {
        Route::post('/setting/store', [MailBoxController::class, 'setting'])->name('mailbox.setting.store')->middleware(['auth']);
    });
    Route::post('mailbox/configuration/store', [MailBoxController::class, 'configuration_store'])->name('mailbox.configuration.store')->middleware(['auth']);
    Route::any('/mailbox/index/{type?}', [MailBoxController::class, 'index'])->name('mailbox.index')->middleware(['auth']);
    Route::any('/mailbox/create/{id?}/{type?}', [MailBoxController::class, 'create'])->name('mailbox.create')->middleware(['auth']);
    Route::any('/mailbox/store', [MailBoxController::class, 'store'])->name('mailbox.store')->middleware(['auth']);
    Route::any('/mail/view/{folder}/{id}', [MailBoxController::class, 'show'])->name('mailbox.show')->middleware(['auth']);
    Route::post('/mailbox/action', [MailBoxController::class, 'action'])->name('mailbox.action')->middleware(['auth']);
    Route::any('/mailbox/delete/{folder}/{id}', [MailBoxController::class, 'destroy'])->name('mailbox.destroy')->middleware(['auth']);
    Route::get('/mailbox/configuration', [MailBoxController::class, 'configuration'])->name('mailbox.configuration')->middleware(['auth']);
    Route::post('/mailbox/reply/sent', [MailBoxController::class, 'reply_send'])->name('mailbox.reply.sent')->middleware(['auth']);
    Route::get('/mailbox/mail/reply/{id}/{type}', [MailBoxController::class, 'reply'])->name('mailbox.mail.reply')->middleware(['auth']);
    Route::post('/mailbox/mail/move', [MailBoxController::class, 'move_mail'])->name('mailbox.mail.move')->middleware(['auth']);
});
