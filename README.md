# Jiny Mail Package

Jiny 프레임워크를 위한 통합 메일 시스템 패키지입니다.

## 기능

- 메일 설정 관리
- 메일 템플릿 관리
- 메일 로그 관리
- 대량 메일 발송
- 사용자별 메일 관리

## 설치

```bash
composer require jiny/mail
```

## 사용법

### 서비스 프로바이더 등록

Laravel의 `config/app.php`에 서비스 프로바이더를 등록합니다:

```php
'providers' => [
    // ...
    Jiny\Mail\JinyMailServiceProvider::class,
],
```

### 마이그레이션 실행

```bash
php artisan migrate
```

### 설정 파일 발행

```bash
php artisan vendor:publish --tag=jiny-mail-config
```

### 뷰 파일 발행

```bash
php artisan vendor:publish --tag=jiny-mail-views
```

## 라이센스

MIT 라이센스