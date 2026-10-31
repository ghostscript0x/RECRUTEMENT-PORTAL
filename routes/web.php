<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\EmailLogController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\PositionController as AdminPositionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterviewRescheduleController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

/* ------------------------------------------------------------- Public site */

Route::get('/', HomeController::class)->name('home');

Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
Route::get('/positions/{position}', [PositionController::class, 'show'])->name('positions.show');

Route::get('/apply', fn () => redirect()->route('positions.index'))->name('apply.index');
Route::get('/apply/{position}', [ApplyController::class, 'start'])->name('apply.start');
Route::get('/apply/{position}/step/{step}', [ApplyController::class, 'showStep'])->whereNumber('step')->name('apply.step');
Route::post('/apply/{position}/step/{step}', [ApplyController::class, 'storeStep'])
    ->whereNumber('step')
    ->middleware('throttle:30,1');
Route::get('/apply/{position}/review', [ApplyController::class, 'review'])->name('apply.review');
Route::post('/apply/{position}', [ApplyController::class, 'submit'])->middleware('throttle:5,1')->name('apply.submit');
Route::get('/apply-complete', [ApplyController::class, 'success'])->name('apply.success');

Route::get('/track', [TrackingController::class, 'show'])->name('tracking.show');
Route::post('/track', [TrackingController::class, 'track'])->middleware('throttle:10,1')->name('tracking.track');
Route::get('/interview-reschedule/{token}', [InterviewRescheduleController::class, 'show'])
    ->where('token', '[a-f0-9]{64}')
    ->name('interview-reschedule.show');
Route::post('/interview-reschedule/{token}', [InterviewRescheduleController::class, 'store'])
    ->where('token', '[a-f0-9]{64}')
    ->middleware('throttle:5,10')
    ->name('interview-reschedule.store');

Route::get('/api/state/{stateId}/lgas', [TrackingController::class, 'apiStateLgas'])
    ->name('api.state.lgas');

/* ------------------------------------------------------------ Admin panel */

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
        Route::middleware('can:dashboard.view')->get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::middleware('can:settings.manage')->group(function () {
            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        });

        Route::middleware('can:applications.view')->group(function () {
            Route::get('applications/export', [AdminApplicationController::class, 'export'])->middleware('can:exports.run')->name('applications.export');
            Route::get('applications', [AdminApplicationController::class, 'index'])->name('applications.index');
            Route::get('applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
            Route::post('applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->middleware('can:applications.status')->name('applications.status');
            Route::put('applications/{application}/reviewer', [AdminApplicationController::class, 'assignReviewer'])->middleware('can:applications.assign')->name('applications.reviewer');
            Route::post('applications/{application}/notes', [AdminApplicationController::class, 'addNote'])->middleware('can:applications.notes')->name('applications.notes');
            Route::get('applications/{application}/documents/{document}', [DocumentController::class, 'show'])->name('applications.documents');
        });

        Route::middleware('can:interviews.manage')->prefix('interviews')->name('interviews.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InterviewController::class, 'index'])->name('index');
            Route::get('/{application}/edit', [\App\Http\Controllers\Admin\InterviewController::class, 'edit'])->name('edit');
            Route::post('/{application}', [\App\Http\Controllers\Admin\InterviewController::class, 'store'])->name('store');
        });

        Route::middleware('can:positions.manage')->prefix('positions')->name('positions.')->group(function () {
            Route::get('/', [AdminPositionController::class, 'index'])->name('index');
            Route::get('/create', [AdminPositionController::class, 'create'])->name('create');
            Route::post('/', [AdminPositionController::class, 'store'])->name('store');
            Route::get('/{position}/edit', [AdminPositionController::class, 'edit'])->name('edit');
            Route::put('/{position}', [AdminPositionController::class, 'update'])->name('update');
            Route::patch('/{position}/status', [AdminPositionController::class, 'updateStatus'])->name('status');
        });

        Route::middleware('can:templates.manage')->prefix('email-templates')->name('templates.')->group(function () {
            Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
            Route::get('/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('edit');
            Route::put('/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('update');
        });

        Route::middleware('can:templates.manage')->prefix('email-logs')->name('email-logs.')->group(function () {
            Route::get('/', [EmailLogController::class, 'index'])->name('index');
            Route::post('/{emailLog}/retry', [EmailLogController::class, 'retry'])->name('retry');
        });

        Route::middleware('can:users.manage')->prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('can:audit.view')->get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    });
});
