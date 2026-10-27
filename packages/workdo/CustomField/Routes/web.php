<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomField\Http\Controllers\CustomFieldController;




Route::group(['middleware' => 'PlanModuleCheck:CustomField'], function () {

    Route::middleware(['auth'])->group(function () {

        Route::prefix('customfield')->group(function() {
            Route::get('/', 'CustomFieldController@index');
        });

        Route::resource('custom-field', CustomFieldController::class);

        Route::get('custom-field/get_module/{module_name}',[CustomFieldController::class,'get_module_list']);

    });

});
