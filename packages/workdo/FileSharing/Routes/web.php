<?php

use Illuminate\Support\Facades\Route;
use Modules\FileSharing\Http\Controllers\FilesController;
use Modules\FileSharing\Http\Controllers\FileSharingController;
use Modules\FileSharing\Http\Controllers\DownloadController;
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

Route::group(['middleware' => 'PlanModuleCheck:FileSharing'], function ()
{
    Route::prefix('filesharing')->group(function() {
        Route::get('/', [FileSharingController::class,'index']);
    });

    Route::resource('files', FilesController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::resource('download-detailes', DownloadController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('file/grid', [FilesController::class, 'grid'])->name('file.grid');

});
Route::post('/download/{file}', [FilesController::class,'download'])->name('file.download');

Route::post('file/password/check/{id}/{lang?}', [FilesController::class,'PasswordCheck'])->name('file.password.check');

Route::get('file/shared/link/{id}/{lang?}', [FilesController::class,'FileSharedLink'])->name('file.shared.link');
