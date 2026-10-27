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
use Modules\DoubleEntry\Http\Controllers\JournalEntryController;
use Modules\DoubleEntry\Http\Controllers\ReportController;




Route::group(['middleware' => 'PlanModuleCheck:DoubleEntry'], function ()
{

    Route::prefix('doubleentry')->group(function() {
        Route::get('/', 'DoubleEntryController@index');
    });


    Route::resource('journal-entry', JournalEntryController::class)->middleware(['auth']);
    Route::post('journal-entry/account/destroy', [JournalEntryController::class, 'accountDestroy'])->name('journal.account.destroy')->middleware(['auth']);
    Route::delete('journal-entry/journal/destroy/{item_id}', [JournalEntryController::class, 'journalDestroy'])->name('journal.destroy')->middleware(['auth']);


    Route::get('report/ledger/{account?}', [ReportController::class, 'ledgerReport'])->name('report.ledger');
    Route::get('report/balance-sheet/{view?}/{collapseview?}', [ReportController::class, 'balanceSheet'])->name('report.balance.sheet');
    Route::get('report/profit-loss/{view?}/{collapseView?}', [ReportController::class, 'profitLoss'])->name('report.profit.loss');
    Route::get('report/trial-balance/{view?}', [ReportController::class, 'trialBalance'])->name('report.trial.balance');


    Route::get('report/sales', [ReportController::class, 'salesReport'])->name('report.sales');
    Route::post('print/sales-report', [ReportController::class, 'salesReportPrint'])->name('sales.report.print');


    Route::get('report/receivables', [ReportController::class, 'ReceivablesReport'])->name('report.receivables');
    Route::post('print/receivables', [ReportController::class, 'ReceivablesPrint'])->name('receivables.print');


    Route::get('report/payables', [ReportController::class, 'PayablesReport'])->name('report.payables');
    Route::post('print/payables', [ReportController::class, 'PayablesPrint'])->name('payables.print');

    Route::post('journal-entry/setting/store', [JournalEntryController::class, 'setting'])->name('journal-entry.setting.store')->middleware(['auth']);










});
