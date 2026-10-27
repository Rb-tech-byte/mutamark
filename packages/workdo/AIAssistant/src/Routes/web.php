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
use Workdo\AIAssistant\Http\Controllers\AIAssistantController;

Route::group(['middleware' =>  ['auth', 'web', 'verified','PlanModuleCheck:AIAssistant']], function () {
    Route::prefix('aiassistant')->group(function () {
        Route::get('/generate-ai/{template_module}/{module}', [AIAssistantController::class, 'create'])->name('aiassistant.generate');
        Route::post('/generate/keywords/{id}', [AIAssistantController::class, 'GetKeywords'])->name('aiassistant.generate.keywords');
        Route::any('/generate/response', [AIAssistantController::class, 'AiGenerate'])->name('aiassistant.generate.response');
        Route::any('/generate/{template_module}/{module}/{id}', [AIAssistantController::class, 'vcard_create_business'])->name('aiassistant.generate_business');
        Route::any('/generate_service/{template_module}/{module}/{id}', [AIAssistantController::class, 'vcard_create_service'])->name('aiassistant.generate_vcard_service');
        Route::any('/generate_testimonial/{template_module}/{module}/{id}', [AIAssistantController::class, 'vcard_create_testimonial'])->name('aiassistant.generate_vcard_testimonial');
        Route::any('/cmms-generate/{template_module}/{module}', [AIAssistantController::class, 'cmms_create'])->name('cmms_aiassistant.generate');
    });
});
