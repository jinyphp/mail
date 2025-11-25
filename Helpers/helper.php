<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Jiny\Admin\Mail\EmailMailable;
use Jiny\Mail\Facades\UserMail;

/**
 * jiny-mail: mail.json 설정을 로드합니다.
 *
 * - 기본적으로 jiny/mail/config/mail.json 파일을 읽어 배열로 반환합니다.
 * - 파일이 없거나 파싱이 실패하면 환경변수 기반의 안전한 기본값을 반환합니다.
 */
if (!function_exists('mail_load_config')) {
    function mail_load_config(): array
    {
        // 파사드(UserMail)를 통해 서비스에서 일원화된 설정 로드
        return UserMail::loadConfig();
    }
}

/**
 * jiny-mail: 로드된 메일 설정을 런타임 설정(config)으로 적용합니다.
 *
 * - SMTP 정보를 `config('mail.*')`에 주입하여 이후 Mail 파사드가 해당 설정으로 발송되도록 합니다.
 * - encryption 값이 문자열 'null'이거나 미설정이면 실제 null로 매핑합니다.
 * - mailer가 smtp가 아닌 경우 필요한 추가 설정을 보완합니다.
 */
if (!function_exists('mail_apply_config')) {
    function mail_apply_config(array $config): void
    {
        // 파사드(UserMail)를 통해 런타임 설정을 적용
        UserMail::applyConfig($config);
    }
}

/**
 * jiny-mail: HTML 본문으로 이메일을 발송합니다.
 *
 * 사용 예) jiny_mail_send('user@example.com', '제목', '<b>본문</b>', '홍길동');
 *
 * 반환:
 * - ['success' => true] 성공
 * - ['success' => false, 'message' => '에러메시지'] 실패
 *
 * 매개변수:
 * - $toEmail: 수신자 이메일
 * - $subject: 메일 제목
 * - $htmlContent: HTML 본문
 * - $toName: 수신자명(선택)
 * - $overrideConfig: mail.json 설정을 일시적으로 덮어쓸 배열(선택)
 */
if (!function_exists('mail_send')) {
    function mail_send(string $toEmail, string $subject, string $htmlContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        // 파사드(UserMail)를 통해 발송
        return UserMail::sendByHtml($toEmail, $subject, $htmlContent, $toName, $overrideConfig);
    }
}

/**
 * jiny-mail: 텍스트 본문으로 이메일을 발송합니다.
 *
 * - 순수 텍스트를 HTML로 안전하게 변환(nl2br + e)하여 발송합니다.
 */
if (!function_exists('mail_send_text')) {
    function mail_send_text(string $toEmail, string $subject, string $textContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        $htmlContent = nl2br(e($textContent));
        return mail_send($toEmail, $subject, $htmlContent, $toName, $overrideConfig);
    }
}

/**
 * HTML 본문을 그대로 사용하여 메일을 발송합니다.
 *
 * - 내부적으로 mail_send()를 호출합니다.
 */
if (!function_exists('mail_sendByHtml')) {
    function mail_sendByHtml(string $toEmail, string $subject, string $htmlContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        return UserMail::sendByHtml($toEmail, $subject, $htmlContent, $toName, $overrideConfig);
    }
}

/**
 * 텍스트 본문을 사용하여 메일을 발송합니다.
 *
 * - 개행을 유지하여 HTML로 변환한 뒤 발송합니다.
 * - 내부적으로 mail_send_text()를 호출합니다.
 */
if (!function_exists('mail_sendByText')) {
    function mail_sendByText(string $toEmail, string $subject, string $textContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        return UserMail::sendByText($toEmail, $subject, $textContent, $toName, $overrideConfig);
    }
}

/**
 * Blade 뷰를 렌더링하여 메일을 발송합니다.
 *
 * 매개변수:
 * - $view: Blade 뷰 이름 (예: 'emails.notice')
 * - $data: 뷰 렌더링에 주입할 데이터
 *
 * 동작:
 * - view($view, $data)->render()로 HTML을 생성한 뒤 mail_sendByHtml()로 발송합니다.
 */
if (!function_exists('mail_sendByBlade')) {
    function mail_sendByBlade(string $toEmail, string $subject, string $view, array $data = [], ?string $toName = null, ?array $overrideConfig = null): array
    {
        return UserMail::sendByBlade($toEmail, $subject, $view, $data, $toName, $overrideConfig);
    }
}

/**
 * Markdown 본문을 사용하여 메일을 발송합니다.
 *
 * - 내부적으로 UserMail::sendByMarkdown()을 호출합니다.
 */
if (!function_exists('mail_sendByMarkdown')) {
    function mail_sendByMarkdown(string $toEmail, string $subject, string $markdownContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        return UserMail::sendByMarkdown($toEmail, $subject, $markdownContent, $toName, $overrideConfig);
    }
}
