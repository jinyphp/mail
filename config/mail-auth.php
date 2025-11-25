<?php

// 기본값(환경 변수 기반)
$defaults = [
    'mailer' => env('MAIL_MAILER', 'smtp'),
    'host' => env('MAIL_HOST', 'sandbox.smtp.mailtrap.io'),
    'port' => env('MAIL_PORT', 2525),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@jinyphp.com'),
    'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'JinyPHP')),
];

// JSON 설정 읽기 (동일 디렉터리의 mail.json)
$jsonPath = __DIR__.'/mail.json';
$json = [];
if (file_exists($jsonPath)) {
    try {
        $contents = file_get_contents($jsonPath);
        $decoded = json_decode($contents, true);
        if (is_array($decoded)) {
            $json = $decoded;
        }
    } catch (\Throwable $e) {
        // JSON 파싱 실패 시 조용히 기본값 사용
    }
}

// JSON이 있으면 JSON 값이 기본값을 덮어쓰도록 병합
$config = array_merge($defaults, $json);

// 타입 정규화
if (array_key_exists('port', $config) && !is_null($config['port'])) {
    $config['port'] = (int) $config['port'];
}
if (isset($config['encryption']) && $config['encryption'] === 'null') {
    $config['encryption'] = null;
}

return $config;