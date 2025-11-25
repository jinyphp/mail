<?php

namespace Jiny\Mail\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * UserMail 파사드
 *
 * JSON 기반 메일 발송 서비스에 대한 파사드 접근을 제공합니다.
 *
 * @method static array loadConfig()
 * @method static void applyConfig(array $config)
 * @method static array sendByHtml(string $toEmail, string $subject, string $htmlContent, ?string $toName = null, ?array $overrideConfig = null)
 * @method static array sendByText(string $toEmail, string $subject, string $textContent, ?string $toName = null, ?array $overrideConfig = null)
 * @method static array sendByBlade(string $toEmail, string $subject, string $view, array $data = [], ?string $toName = null, ?array $overrideConfig = null)
 * @method static array sendByMarkdown(string $toEmail, string $subject, string $markdownContent, ?string $toName = null, ?array $overrideConfig = null)
 */
class UserMail extends Facade
{
    /**
     * 서비스 컨테이너 바인딩 키를 반환합니다.
     */
    protected static function getFacadeAccessor()
    {
        return 'user.mail';
    }
}


