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
use Modules\WhatsAppAPI\Http\Controllers\WhatsAppAPIController;

Route::group(['middleware' => 'PlanModuleCheck:WhatsAppAPI'], function ()
{

    Route::post('whatsappapi/setting/store', [WhatsAppAPIController::class, 'setting'])
        ->name('whatsappapi.setting.store');

    Route::post('test-settings-massage', [WhatsAppAPIController::class, 'testMassage'])
        ->name('test.setting.massage')
        ->middleware(['auth']);

    Route::post('send-settings-massage', [WhatsAppAPIController::class, 'sendTestMassage'])
        ->name('send.test.massage')
        ->middleware(['auth']);

});