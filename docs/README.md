## Jiny Mail - JSON 기반 메일 설정과 UserMail 파사드/헬퍼 사용 안내

이 문서는 `jiny/mail/config/mail.json` 설정 파일과 메일 발송 헬퍼 함수의 사용 방법을 설명합니다. 관리자 UI의 설정 저장/테스트, 벌크 발송, 일반 코드에서의 간편 발송 모두 동일한 JSON 설정을 사용합니다.

### 설정 파일
- 위치: `jiny/mail/config/mail.json`  
- 헬퍼는 `__DIR__ . '/../config/mail.json'` 상대 경로로 설정 파일을 찾습니다.

예시:

```json
{
  "mailer": "smtp",
  "host": "sandbox.smtp.mailtrap.io",
  "port": 2525,
  "username": "YOUR_USERNAME",
  "password": "YOUR_PASSWORD",
  "encryption": "tls",
  "from_address": "hello@example.com",
  "from_name": "Example"
}
```

주의:
- `encryption`에 "null" 문자열 또는 미설정이면 실제 `null`로 적용됩니다.
- `mailer`가 `smtp`가 아닌 경우(`sendmail`, `log`) 일부 추가 옵션이 런타임에 자동 설정됩니다.

### Composer 오토로드
`composer.json`의 `autoload.files`에 `jiny/mail/Helpers/helper.php`가 등록되어 있습니다. 변경 후에는 아래를 한 번 실행하세요.

```bash
composer dump-autoload -o
```

### 헬퍼 함수 개요
`jiny/mail/Helpers/helper.php` 에서 제공하는 함수:

- `mail_load_config(): array`  
  JSON 설정을 읽어 배열로 반환합니다. 파일이 없거나 파싱 실패 시 환경변수 기반 기본값을 반환합니다.

- `mail_apply_config(array $config): void`  
  읽어온 설정을 `config('mail.*')`에 주입하여, 이후 `Mail` 파사드가 해당 설정으로 발송되도록 합니다.

- `mail_sendByHtml(string $toEmail, string $subject, string $htmlContent, ?string $toName = null, ?array $overrideConfig = null): array`  
  HTML 본문 그대로 발송합니다.

- `mail_sendByText(string $toEmail, string $subject, string $textContent, ?string $toName = null, ?array $overrideConfig = null): array`  
  텍스트를 HTML로 안전 변환하여 발송합니다.

- `mail_sendByBlade(string $toEmail, string $subject, string $view, array $data = [], ?string $toName = null, ?array $overrideConfig = null): array`  
  Blade 뷰를 렌더링하여 발송합니다.

반환값(공통):  
`['success' => true]` 또는 `['success' => false, 'message' => '에러메시지']`

참고:
- 위 헬퍼 함수들은 내부적으로 `UserMail` 파사드를 호출하는 얇은 래퍼입니다. 신규 코드는 파사드 직접 사용을 권장합니다.

### 파사드(Facade) 사용 (UserMail)

서비스 클래스와 파사드를 제공합니다. 파사드 이름은 `UserMail` 입니다.

- 서비스: `Jiny\Mail\Services\UserMailService`
- 파사드: `Jiny\Mail\Facades\UserMail`
- 프로바이더: `Jiny\Mail\Providers\UserMailServiceProvider`

애플리케이션에 등록(`config/app.php`) 후 사용하세요:

```php
// config/app.php
'providers' => [
    // ...
    Jiny\Mail\Providers\UserMailServiceProvider::class,
],

'aliases' => [
    // ...
    'UserMail' => Jiny\Mail\Facades.UserMail::class,
],
```

파사드 사용 예시:

```php
use UserMail;

// HTML
UserMail::sendByHtml('user@example.com', '제목', '<b>본문</b>', '홍길동');

// Text
UserMail::sendByText('user@example.com', '제목', "첫 줄\n둘째 줄", '홍길동');

// Blade
UserMail::sendByBlade('user@example.com', '제목', 'emails.notice', ['userName' => '홍길동']);
```

### 빠른 사용 예시

HTML 본문 발송

```php
$result = mail_sendByHtml(
    'user@example.com',
    '테스트 메일',
    '<b>안녕하세요</b> Jiny Mail 테스트입니다.',
    '홍길동'
);
if (!$result['success']) {
    \Log::error('메일 발송 실패: '.$result['message']);
}
```

텍스트 본문 발송

```php
$result = mail_sendByText(
    'user@example.com',
    '텍스트 메일',
    "첫 줄\n두 번째 줄",
    '홍길동'
);
```

Blade 뷰 발송

```php
$result = mail_sendByBlade(
    'user@example.com',
    '알림 메일',
    'emails.notice', // resources/views/emails/notice.blade.php
    ['userName' => '홍길동', 'message' => '공지사항입니다.'],
    '홍길동'
);
```

샘플 Blade (`resources/views/emails/notice.blade.php`)

```php
<div style="font-family: Arial, sans-serif;">
    <h2>{{ $userName }}님께</h2>
    <p>{!! nl2br(e($message)) !!}</p>
</div>
```

### 관리자 설정 화면과 테스트
- `AuthMailSetting` 컨트롤러는 JSON만 사용합니다.
- 관리자에서 설정 저장 시 `mail.json`이 갱신됩니다.
- 테스트 메일도 `UserMail` 파사드를 통해 JSON 설정으로 발송됩니다.

### 벌크 메일 발송과 연동
- `BulkMail/SendController`는 `mail.json`을 로드하여 런타임 설정을 적용한 후, 개별 수신자에게 순차 발송합니다.
- 추가 설정 없이 `mail.json`만 수정해도 벌크 발송에 바로 반영됩니다.

### 에러 처리와 로깅
- 헬퍼는 실패 시 `['success' => false, 'message' => '...']`를 반환하고, 예외를 로깅합니다.
- 벌크 발송 컨트롤러는 성공/실패 건수, 일부 오류 메시지를 로그로 남깁니다.

### 보안/운영 주의
- `mail.json`에는 자격증명이 포함될 수 있으니 접근 권한을 관리하세요.
- 운영 환경에서는 비밀 값 관리(예: Vault, 환경변수)를 고려하세요.
- 빈/손상된 JSON은 환경변수 기본값으로 대체됩니다.


