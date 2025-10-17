<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Mail Routes
|--------------------------------------------------------------------------
|
| 사용자 메일 관련 라우트
|
*/

Route::middleware(['web', 'auth'])->group(function () {

    // 사용자 메일함 (임시 비활성화 - MailController 미구현)
    // Route::prefix('mail')->name('mail.')->group(function () {
    //     Route::get('/', [\Jiny\Mail\Http\Controllers\Home\MailController::class, 'index'])->name('index');
    //     Route::get('/inbox', [\Jiny\Mail\Http\Controllers\Home\MailController::class, 'inbox'])->name('inbox');
    //     Route::get('/sent', [\Jiny\Mail\Http\Controllers\Home\MailController::class, 'sent'])->name('sent');
    //     Route::get('/compose', [\Jiny\Mail\Http\Controllers\Home\MailController::class, 'compose'])->name('compose');
    //     Route::post('/send', [\Jiny\Mail\Http\Controllers\Home\MailController::class, 'send'])->name('send');
    //     Route::get('/{id}', [\Jiny\Mail\Http\Controllers\Home\MailController::class, 'show'])->name('show');
    //     Route::delete('/{id}', [\Jiny\Mail\Http\Controllers\Home\MailController::class, 'delete'])->name('delete');
    // });

});