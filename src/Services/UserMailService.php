<?php

namespace Jiny\Mail\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Jiny\Admin\Mail\EmailMailable;
use Illuminate\Support\SupportServiceProvider;
use Illuminate\Support\Str;

/**
 * UserMailService
 *
 * JSON 설정 기반 메일 발송 로직을 제공하는 서비스 클래스입니다.
 * - 설정 로드/적용
 * - HTML/텍스트/Blade 발송
 */
class UserMailService
{
    /**
     * mail.json 설정을 로드합니다.
     */
    public function loadConfig(): array
    {
        // __DIR__ 기준 상대 경로로 설정 파일을 지정합니다.
        // 현재 파일: jiny/mail/src/Services/UserMailService.php
        // 설정 파일: jiny/mail/config/mail.json → ../../config/mail.json
        $jsonConfigPath = __DIR__ . '/../../config/mail.json';

        try {
            if (file_exists($jsonConfigPath)) {
                $jsonString = File::get($jsonConfigPath);
                $config = json_decode($jsonString, true);
                if (is_array($config)) {
                    return $config;
                }
                \Log::warning('UserMailService@loadConfig: JSON 파싱 실패, 기본값으로 대체합니다.');
            } else {
                \Log::warning('UserMailService@loadConfig: 설정 파일이 없습니다. 기본값으로 대체합니다.', [
                    'path' => $jsonConfigPath
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('UserMailService@loadConfig: 설정 읽기 오류 - '.$e->getMessage(), [
                'path' => $jsonConfigPath,
                'exception' => $e
            ]);
        }

        // JSON이 없거나 실패한 경우의 기본값 (환경변수 기반)
        return [
            'mailer' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', 'localhost'),
            'port' => (int) env('MAIL_PORT', 25),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', null),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
            'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
        ];
    }

    /**
     * 로드된 설정을 런타임 메일 설정에 적용합니다.
     */
    public function applyConfig(array $config): void
    {
        config([
            'mail.default' => $config['mailer'] ?? config('mail.default'),
            'mail.mailers.smtp.host' => $config['host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $config['port'] ?? config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => $config['username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $config['password'] ?? config('mail.mailers.smtp.password'),
            'mail.mailers.smtp.encryption' => (isset($config['encryption']) && $config['encryption'] === 'null')
                ? null
                : ($config['encryption'] ?? config('mail.mailers.smtp.encryption')),
            'mail.from.address' => $config['from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $config['from_name'] ?? config('mail.from.name'),
        ]);

        if (($config['mailer'] ?? 'smtp') !== 'smtp') {
            switch ($config['mailer']) {
                case 'sendmail':
                    config(['mail.mailers.sendmail.path' => '/usr/sbin/sendmail -bs']);
                    break;
                case 'log':
                    config(['mail.mailers.log.channel' => env('MAIL_LOG_CHANNEL', 'mail')]);
                    break;
            }
        }
    }

    /**
     * HTML 본문으로 메일 발송
     */
    public function sendByHtml(string $toEmail, string $subject, string $htmlContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        $config = $this->loadConfig();
        if (is_array($overrideConfig) && !empty($overrideConfig)) {
            $config = array_merge($config, $overrideConfig);
        }
        $this->applyConfig($config);

        try {
            Mail::to($toEmail, $toName)->send(new EmailMailable(
                $subject,
                $htmlContent,
                $config['from_address'] ?? null,
                $config['from_name'] ?? null,
                $toEmail
            ));
            return ['success' => true];
        } catch (\Throwable $e) {
            \Log::error('UserMailService@sendByHtml 실패: '.$e->getMessage(), [
                'to' => $toEmail,
                'subject' => $subject,
                'exception' => $e
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 텍스트 본문으로 메일 발송
     */
    public function sendByText(string $toEmail, string $subject, string $textContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        $htmlContent = nl2br(e($textContent));
        return $this->sendByHtml($toEmail, $subject, $htmlContent, $toName, $overrideConfig);
    }

    /**
     * Blade 뷰 렌더링 후 메일 발송
     */
    public function sendByBlade(string $toEmail, string $subject, string $view, array $data = [], ?string $toName = null, ?array $overrideConfig = null): array
    {
        try {
            $html = view($view, $data)->render();
        } catch (\Throwable $e) {
            \Log::error('UserMailService@sendByBlade: 뷰 렌더링 실패 - '.$e->getMessage(), [
                'view' => $view,
                'data' => $data,
                'exception' => $e
            ]);
            return ['success' => false, 'message' => '뷰 렌더링 실패: '.$e->getMessage()];
        }

        return $this->sendByHtml($toEmail, $subject, $html, $toName, $overrideConfig);
    }

    /**
     * Markdown 본문으로 메일 발송
     *
     * - Laravel의 Str::markdown을 사용하여 HTML로 변환 후 발송합니다.
     */
    public function sendByMarkdown(string $toEmail, string $subject, string $markdownContent, ?string $toName = null, ?array $overrideConfig = null): array
    {
        try {
            $html = Str::markdown($markdownContent);
        } catch (\Throwable $e) {
            \Log::error('UserMailService@sendByMarkdown: 마크다운 변환 실패 - '.$e->getMessage(), [
                'exception' => $e
            ]);
            // 변환 실패 시 텍스트를 간단히 HTML로 감싸서 발송
            $html = '<pre style="white-space:pre-wrap">'.e($markdownContent).'</pre>';
        }

        return $this->sendByHtml($toEmail, $subject, $html, $toName, $overrideConfig);
    }
}


