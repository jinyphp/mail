<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Mail Routes
|--------------------------------------------------------------------------
|
| 관리자 메일 관리 라우트 (Single Action Controllers)
|
*/

Route::prefix('admin')->middleware(['web', 'admin'])->group(function () {

    // 메일 설정 관리 (Mail)
    Route::prefix('mail/setting')->name('admin.mail.setting.')->group(function () {
        Route::get('/', \Jiny\Mail\Http\Controllers\Admin\Mail\MailSetting\AuthMailSetting::class)->name('index');
        Route::post('/update', [\Jiny\Mail\Http\Controllers\Admin\Mail\MailSetting\AuthMailSetting::class, 'update'])->name('update');
        Route::post('/test', [\Jiny\Mail\Http\Controllers\Admin\Mail\MailSetting\AuthMailSetting::class, 'test'])->name('test');
    });

    // 메일 로그 관리 (Mail Logs)
    Route::prefix('mail/logs')->name('admin.mail.logs.')->group(function () {
        Route::get('/', \Jiny\Mail\Http\Controllers\Admin\Mail\MailLogs\IndexController::class)->name('index');
        Route::get('/{id}/content', \Jiny\Mail\Http\Controllers\Admin\Mail\MailLogs\ContentController::class)->name('content');
        Route::get('/{id}/error', \Jiny\Mail\Http\Controllers\Admin\Mail\MailLogs\ErrorController::class)->name('error');
        Route::post('/{id}/resend', \Jiny\Mail\Http\Controllers\Admin\Mail\MailLogs\ResendController::class)->name('resend');
    });

    // 메일 템플릿 관리 (Mail Templates)
    Route::prefix('mail/templates')->name('admin.mail.templates.')->group(function () {
        Route::get('/', \Jiny\Mail\Http\Controllers\Admin\Mail\Template\IndexController::class)->name('index');
        Route::get('/create', \Jiny\Mail\Http\Controllers\Admin\Mail\Template\CreateController::class)->name('create');
        Route::post('/', \Jiny\Mail\Http\Controllers\Admin\Mail\Template\StoreController::class)->name('store');
        Route::get('/{id}', \Jiny\Mail\Http\Controllers\Admin\Mail\Template\ShowController::class)->name('show');
        Route::get('/{id}/edit', \Jiny\Mail\Http\Controllers\Admin\Mail\Template\EditController::class)->name('edit');
        Route::put('/{id}', \Jiny\Mail\Http\Controllers\Admin\Mail\Template\UpdateController::class)->name('update');
        Route::delete('/{id}', \Jiny\Mail\Http\Controllers\Admin\Mail\Template\DeleteController::class)->name('delete');
    });

    // 전체 메일 발송 (Bulk Mail)
    Route::prefix('mail/bulk')->name('admin.mail.bulk.')->group(function () {
        Route::get('/create', \Jiny\Mail\Http\Controllers\Admin\Mail\BulkMail\CreateController::class)->name('create');
        Route::post('/send', \Jiny\Mail\Http\Controllers\Admin\Mail\BulkMail\SendController::class)->name('send');
    });

    // 사용자별 메일 관리 (User Mail)
    Route::prefix('users/{id}/mail')->name('admin.users.mail.')->group(function () {
        Route::get('/', [\Jiny\Mail\Http\Controllers\Admin\Users\UserMailController::class, 'index'])->name('index');
        Route::post('/send', [\Jiny\Mail\Http\Controllers\Admin\Users\UserMailController::class, 'send'])->name('send');
    });

});