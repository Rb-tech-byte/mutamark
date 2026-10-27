<?php

use Illuminate\Support\Facades\Route;
use Modules\Hrm\Http\Controllers\EmployeeController;
use Modules\Recruitment\Entities\JobCandidate;
use Modules\Recruitment\Http\Controllers\CustomQuestionController;
use Modules\Recruitment\Http\Controllers\DashboardController;
use Modules\Recruitment\Http\Controllers\InterviewScheduleController;
use Modules\Recruitment\Http\Controllers\JobApplicationController;
use Modules\Recruitment\Http\Controllers\JobAwardController;
use Modules\Recruitment\Http\Controllers\JobCandidateController;
use Modules\Recruitment\Http\Controllers\JobCategoryController;
use Modules\Recruitment\Http\Controllers\JobController;
use Modules\Recruitment\Http\Controllers\JobExperienceCandidateController;
use Modules\Recruitment\Http\Controllers\JobExperienceController;
use Modules\Recruitment\Http\Controllers\JobProjectController;
use Modules\Recruitment\Http\Controllers\JobQualificationController;
use Modules\Recruitment\Http\Controllers\JobSkillController;
use Modules\Recruitment\Http\Controllers\JobStageController;
use Modules\Recruitment\Http\Controllers\PayslipTypeController;

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

//------------------------------------  Recurtment --------------------------------


Route::group(['middleware' => 'PlanModuleCheck:Recruitment'], function () {
    Route::get('dashboard/recruitment', [DashboardController::class, 'index'])->name('recruitment.dashboard')->middleware(['auth']);
    Route::resource('job-category', JobCategoryController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-stage', JobStageController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-stage/order', [JobStageController::class, 'order'])->name('job.stage.order')->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job', JobController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-grid', [JobController::class, 'grid'])->name('job.grid')->middleware(
        [
            'auth'
        ]
    );

    Route::get('candidates-job-applications', [JobApplicationController::class, 'archived'])->name('job.application.archived')->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-candidates', JobCandidateController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-project', JobProjectController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-qualification', JobQualificationController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-award', JobAwardController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-experience-candidate', JobExperienceCandidateController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-skill', JobSkillController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-experience', JobExperienceController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::resource('job-application', JobApplicationController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-application-list', [JobApplicationController::class, 'list'])->name('job.list')->middleware(
        [
            'auth'
        ]
    );

    Route::post('job-application/order', [JobApplicationController::class, 'order'])->name('job.application.order')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-application/{id}/rating', [JobApplicationController::class, 'rating'])->name('job.application.rating')->middleware(
        [
            'auth',
        ]
    );
    Route::delete('job-application/{id}/archive', [JobApplicationController::class, 'archive'])->name('job.application.archive')->middleware(
        [
            'auth',
        ]
    );

    Route::post('job-application/{id}/skill/store', [JobApplicationController::class, 'addSkill'])->name('job.application.skill.store')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-application/{id}/note/store', [JobApplicationController::class, 'addNote'])->name('job.application.note.store')->middleware(
        [
            'auth',
        ]
    );
    Route::delete('job-application/{id}/note/destroy', [JobApplicationController::class, 'destroyNote'])->name('job.application.note.destroy')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-application/getByJob', [JobApplicationController::class, 'getByJob'])->name('get.job.application')->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-onboard-grid', [JobApplicationController::class, 'grid'])->name('job.on.board.grid')->middleware(
        [
            'auth'
        ]
    );

    Route::get('job-onboard', [JobApplicationController::class, 'jobOnBoard'])->name('job.on.board')->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-onboard/create/{id}', [JobApplicationController::class, 'jobBoardCreate'])->name('job.on.board.create')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-onboard/store/{id}', [JobApplicationController::class, 'jobBoardStore'])->name('job.on.board.store')->middleware(
        [
            'auth',
        ]
    );

    Route::get('job-onboard/edit/{id}', [JobApplicationController::class, 'jobBoardEdit'])->name('job.on.board.edit')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-onboard/update/{id}', [JobApplicationController::class, 'jobBoardUpdate'])->name('job.on.board.update')->middleware(
        [
            'auth',
        ]
    );
    Route::delete('job-onboard/delete/{id}', [JobApplicationController::class, 'jobBoardDelete'])->name('job.on.board.delete')->middleware(
        [
            'auth',
        ]
    );
    Route::get('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvert'])->name('job.on.board.converts')->middleware(
        [
            'auth',
        ]
    );
    Route::post('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvertData'])->name('job.on.board.convert')->middleware(
        [
            'auth',
        ]
    );


    Route::post('job-application/stage/change', [JobApplicationController::class, 'stageChange'])->name('job.application.stage.change')->middleware(
        [
            'auth',
        ]
    );

    Route::resource('custom-question', CustomQuestionController::class)->middleware(
        [
            'auth',
        ]
    );


    Route::resource('interview-schedule', InterviewScheduleController::class)->middleware(
        [
            'auth',
        ]
    );
    Route::get('interview-schedule/create/{id?}', [InterviewScheduleController::class, 'create'])->name('interview-schedule.create')->middleware(
        [
            'auth',
        ]
    );

    //payslip type
    Route::resource('paysliptype', PayslipTypeController::class)->middleware(
        [
            'auth',
        ]
    );

    Route::post('employee/getdepartmentes', [EmployeeController::class, 'getDepartment'])->name('employee.getdepartments')->middleware(
        [
            'auth',
        ]
    );
    Route::post('employee/getdesignationes', [EmployeeController::class, 'getdDesignation'])->name('employee.getdesignations')->middleware(
        [
            'auth',
        ]
    );
    
    //offer Letter
    Route::post('setting/offerlatter/{lang?}', [JobApplicationController::class, 'offerletterupdate'])->name('offerlatter.update');
    Route::get('job-onboard/pdf/{id}', [JobApplicationController::class, 'offerletterPdf'])->name('offerlatter.download.pdf');
    Route::get('job-onboard/doc/{id}', [JobApplicationController::class, 'offerletterDoc'])->name('offerlatter.download.doc');

    // // job template settig in account
    Route::get('/job/preview/{template}/{color}', [JobCandidateController::class,'previewJob'])->name('job.preview');
    Route::post('/recruitment/setting/store', [JobCandidateController::class,'saveJobTemplateSettings'])->name('job.template.setting');
});

Route::get('career/{slug?}/{lang?}', [JobController::class, 'career'])->name('career');

// resume show
Route::get('resume/pdf/{id}', [JobCandidateController::class,'DownloadResume'])->name('resume.pdf');

Route::get('job/requirement/{code}/{lang}', [JobController::class, 'jobRequirement'])->name('job.requirement');
Route::get('job/apply/{code}/{lang}', [JobController::class, 'jobApply'])->name('job.apply');
Route::get('terms_and_condition/{code}/{lang}', [JobController::class, 'TermsAndCondition'])->name('job.terms.and.conditions');
Route::post('job/apply/data/{code}', [JobController::class, 'jobApplyData'])->name('job.apply.data');

//------------------------------------ End Recurtment --------------------------------
