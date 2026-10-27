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
use Modules\Inventory\Http\Controllers\InventoryController;


Route::group(['middleware' => 'PlanModuleCheck:Inventory'], function ()
{

Route::ANY('inventory/{feild_id}/{type}', [InventoryController::class,'show'])->name('inventory.view')->middleware([
    'auth'
]);


Route::resource('inventory', InventoryController::class)->middleware(['auth']);
});
