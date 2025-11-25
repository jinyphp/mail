<?php

namespace Jiny\Mail\Providers;

use Illuminate\Support\ServiceProvider;
use Jiny\Mail\Services\UserMailService;

/**
 * UserMailServiceProvider
 *
 * JSON 기반 메일 발송 서비스를 컨테이너에 바인딩합니다.
 * - 파사드(UserMail)가 참조하는 'user.mail' 키에 싱글턴으로 등록합니다.
 */
class UserMailServiceProvider extends ServiceProvider
{
    /**
     * 서비스 바인딩 등록
     */
    public function register(): void
    {
        $this->app->singleton('user.mail', function () {
            return new UserMailService();
        });
    }

    /**
     * 부트스트랩 처리 (현재는 별도 작업 없음)
     */
    public function boot(): void
    {
        //
    }
}


