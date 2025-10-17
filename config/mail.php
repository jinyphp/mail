<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jiny Mail Configuration
    |--------------------------------------------------------------------------
    |
    | Jiny Mail 시스템의 기본 설정입니다.
    |
    */

    'defaults' => [
        'mailer' => env('JINY_MAIL_MAILER', 'smtp'),
        'host' => env('JINY_MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('JINY_MAIL_PORT', 587),
        'username' => env('JINY_MAIL_USERNAME', ''),
        'password' => env('JINY_MAIL_PASSWORD', ''),
        'encryption' => env('JINY_MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('JINY_MAIL_FROM_ADDRESS', 'hello@example.com'),
        'from_name' => env('JINY_MAIL_FROM_NAME', 'Jiny Mail'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail Templates
    |--------------------------------------------------------------------------
    |
    | 메일 템플릿 관련 설정입니다.
    |
    */

    'templates' => [
        'default_layout' => 'jiny-mail::layouts.default',
        'cache_enabled' => true,
        'cache_ttl' => 3600, // 1시간
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail Logs
    |--------------------------------------------------------------------------
    |
    | 메일 로그 관련 설정입니다.
    |
    */

    'logs' => [
        'enabled' => true,
        'retention_days' => 30, // 30일 후 자동 삭제
        'log_content' => true,
        'log_errors' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Bulk Mail
    |--------------------------------------------------------------------------
    |
    | 대량 메일 발송 관련 설정입니다.
    |
    */

    'bulk' => [
        'max_recipients' => 1000,
        'batch_size' => 50,
        'delay_between_batches' => 5, // 초
        'retry_attempts' => 3,
    ],
];