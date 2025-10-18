# Jiny Mail Package - TDD 테스트 가이드

## 테스트 개요

이 문서는 jiny/mail 패키지의 TDD 테스트 수행 방법을 설명합니다.

## 테스트 구조

```
tests/
├── Feature/                    # 기능 테스트
│   ├── RoutesTest.php         # 라우트 응답 테스트
│   ├── MailSendingTest.php    # 메일 발송 기능 테스트
│   ├── MailSettingsTest.php   # 메일 설정 기능 테스트
│   ├── MailTemplatesTest.php  # 메일 템플릿 기능 테스트
│   └── MailLogsTest.php       # 메일 로그 기능 테스트
├── Unit/                      # 단위 테스트
│   ├── ServiceProviderTest.php # ServiceProvider 테스트
│   └── AuthMailLogTest.php    # AuthMailLog 모델 테스트
├── Middleware/                # 테스트용 페이크 미들웨어
│   ├── FakeAdminMiddleware.php
│   └── FakeAuthMiddleware.php
└── TestCase.php               # 기본 테스트 케이스
```

## 테스트 요구사항 충족

### 1. 모든 라우트 경로 200 응답 확인 ✅
- `RoutesTest.php`에서 모든 GET 라우트가 200 응답을 반환하는지 테스트
- 동적 라우트(ID 필요)는 404 응답도 허용

### 2. 메일 발송 및 설정 기능 검사 ✅
- **메일 발송**: `MailSendingTest.php`
  - 테스트 메일 발송
  - 전체 메일 발송
  - 사용자별 메일 발송
  - 메일 재발송
  - 큐 기능
  - 유효성 검사
  - 로그 생성

- **메일 설정**: `MailSettingsTest.php`
  - 설정 업데이트
  - SMTP/다양한 드라이버 설정
  - 암호화 설정
  - 발신자 정보 설정
  - 백업/복원

- **메일 템플릿**: `MailTemplatesTest.php`
  - CRUD 작업
  - 템플릿 변수 교체
  - 미리보기
  - 검색/필터링

- **메일 로그**: `MailLogsTest.php`
  - 로그 조회/필터링
  - 읽음 추적
  - 통계 정보
  - 재발송 기능

### 3. Admin 미들웨어 페이크 처리 ✅
- `FakeAdminMiddleware.php`: jiny/admin 패키지의 AdminMiddleware를 페이크로 처리
- `FakeAuthMiddleware.php`: Laravel 기본 auth 미들웨어 페이크 처리
- `TestCase.php`에서 자동으로 페이크 미들웨어 등록

## 테스트 실행 방법

### 1. 의존성 설치
```bash
cd /Users/hojin8/projects/jinyphp/jinysite_recruit/vendor/jiny/mail
composer install
```

### 2. 전체 테스트 실행
```bash
vendor/bin/phpunit
```

### 3. 특정 테스트 그룹 실행
```bash
# Feature 테스트만 실행
vendor/bin/phpunit --testsuite=Feature

# Unit 테스트만 실행
vendor/bin/phpunit --testsuite=Unit

# 특정 테스트 파일 실행
vendor/bin/phpunit tests/Feature/RoutesTest.php

# 특정 테스트 메서드 실행
vendor/bin/phpunit --filter=all_get_routes_return_200_response
```

### 4. 코버리지 리포트 생성
```bash
vendor/bin/phpunit --coverage-html coverage
```

## 테스트 환경 설정

### 자동 설정 항목
- **데이터베이스**: 인메모리 SQLite 사용
- **메일**: Array 드라이버 사용 (실제 메일 발송 안함)
- **캐시**: Array 드라이버 사용
- **세션**: Array 드라이버 사용
- **큐**: Sync 연결 사용

### Admin 미들웨어 페이크
```php
// TestCase에서 자동으로 처리됨
protected function actingAsAdmin()
{
    // 관리자 권한으로 테스트 실행
}

protected function actingAsUser()
{
    // 일반 사용자 권한으로 테스트 실행
}
```

## 테스트 시나리오

### 라우트 테스트
- ✅ `/admin/mail/setting` - 메일 설정 페이지
- ✅ `/admin/mail/logs` - 메일 로그 페이지
- ✅ `/admin/mail/templates` - 메일 템플릿 목록
- ✅ `/admin/mail/templates/create` - 템플릿 생성
- ✅ `/admin/mail/bulk/create` - 전체 메일 발송
- ✅ `/admin/users/{id}/mail` - 사용자별 메일

### 기능 테스트
- ✅ 메일 발송 (테스트, 전체, 개별)
- ✅ 메일 설정 (SMTP, 암호화, 드라이버)
- ✅ 템플릿 관리 (CRUD, 변수 교체)
- ✅ 로그 관리 (필터링, 검색, 통계)
- ✅ 유효성 검사
- ✅ 권한 검사

## 예상 테스트 결과

```
PHPUnit 10.x.x

Feature Tests:
✅ RoutesTest (6 tests)
✅ MailSendingTest (9 tests)
✅ MailSettingsTest (8 tests)
✅ MailTemplatesTest (12 tests)
✅ MailLogsTest (11 tests)

Unit Tests:
✅ ServiceProviderTest (8 tests)
✅ AuthMailLogTest (10 tests)

Total: 64 tests, XX assertions
```

## 문제 해결

### 일반적인 문제
1. **라우트가 등록되지 않음**: ServiceProvider가 올바르게 로드되었는지 확인
2. **뷰를 찾을 수 없음**: 뷰 경로가 올바르게 등록되었는지 확인
3. **미들웨어 오류**: 페이크 미들웨어가 등록되었는지 확인
4. **데이터베이스 오류**: 마이그레이션이 실행되었는지 확인

### 디버깅
```bash
# 디버그 정보 출력
vendor/bin/phpunit --debug

# 실패한 테스트만 재실행
vendor/bin/phpunit --stop-on-failure

# 상세한 오류 메시지
vendor/bin/phpunit --verbose
```

## 지속적 통합 (CI)

### GitHub Actions 예시
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v2

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'

    - name: Install dependencies
      run: composer install

    - name: Run tests
      run: vendor/bin/phpunit
```

## 결론

이 테스트 슈트는 jiny/mail 패키지의 모든 주요 기능을 검증하며, admin 미들웨어 페이크 처리를 통해 독립적인 테스트 환경을 제공합니다. 모든 라우트가 200 응답을 반환하고, 메일 발송 및 관리 기능이 올바르게 작동하는지 확인할 수 있습니다.