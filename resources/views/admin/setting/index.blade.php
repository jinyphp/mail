@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('content')
<div class="container-fluid">
    {{-- 페이지 헤더 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">🔐 {{ $title ?? 'Auth 메일 설정' }}</h1>
                    <p class="text-muted mb-0">{{ $subtitle ?? 'jiny-auth 전용 SMTP 메일 서버 설정을 관리합니다' }}</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="testMailBtn" class="btn btn-success btn-sm">
                        <i class="bi bi-envelope-check me-2"></i>테스트 메일 발송
                    </button>

                </div>
            </div>
        </div>
    </div>

    {{-- 설정 폼 --}}
    <div class="row">
        <div class="col-xl-8 col-lg-8 col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-envelope-gear me-2"></i>메일 설정
                    </h5>
                </div>
                <div class="card-body">
                    <form id="authMailSettingsForm">
                        @csrf

                        {{-- 메일 드라이버 선택 --}}
                        <div class="mb-3">
                            <label for="mailer" class="form-label fw-bold">
                                메일 드라이버 <span class="text-danger">*</span>
                            </label>
                            <select id="mailer" name="mailer" class="form-select">
                                <option value="smtp" {{ ($mailSettings['mailer'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ ($mailSettings['mailer'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun" {{ ($mailSettings['mailer'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ ($mailSettings['mailer'] ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                <option value="postmark" {{ ($mailSettings['mailer'] ?? '') == 'postmark' ? 'selected' : '' }}>Postmark</option>
                                <option value="log" {{ ($mailSettings['mailer'] ?? '') == 'log' ? 'selected' : '' }}>Log (테스트용)</option>
                            </select>
                        </div>

                        {{-- SMTP 설정 (SMTP 선택시에만 표시) --}}
                        <div id="smtpSettings">
                            <hr class="my-4">
                            <h6 class="fw-bold text-primary mb-3">SMTP 서버 설정</h6>

                            <div class="row">
                                {{-- SMTP 호스트 --}}
                                <div class="col-md-6 mb-3">
                                    <label for="host" class="form-label">
                                        SMTP 호스트 <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="host" name="host"
                                           value="{{ $mailSettings['host'] ?? '' }}"
                                           placeholder="smtp.gmail.com"
                                           class="form-control">
                                    <div class="form-text">예: smtp.gmail.com, smtp.naver.com</div>
                                </div>

                                {{-- SMTP 포트 --}}
                                <div class="col-md-6 mb-3">
                                    <label for="port" class="form-label">
                                        SMTP 포트 <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" id="port" name="port"
                                           value="{{ $mailSettings['port'] ?? '' }}"
                                           placeholder="587"
                                           class="form-control">
                                    <div class="form-text">TLS: 587, SSL: 465</div>
                                </div>

                                {{-- SMTP 사용자명 --}}
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        SMTP 사용자명
                                    </label>
                                    <input type="text" id="username" name="username"
                                           value="{{ $mailSettings['username'] ?? '' }}"
                                           placeholder="your-email@gmail.com"
                                           class="form-control">
                                </div>

                                {{-- SMTP 비밀번호 --}}
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        SMTP 비밀번호
                                    </label>
                                    <input type="password" id="password" name="password"
                                           value="{{ $mailSettings['password'] ?? '' }}"
                                           placeholder="••••••••"
                                           class="form-control">
                                    <div class="form-text">Gmail의 경우 앱 비밀번호를 사용하세요</div>
                                </div>

                                {{-- 암호화 방식 --}}
                                <div class="col-md-6 mb-3">
                                    <label for="encryption" class="form-label">
                                        암호화 방식
                                    </label>
                                    <select id="encryption" name="encryption" class="form-select">
                                        <option value="tls" {{ ($mailSettings['encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ ($mailSettings['encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="null" {{ ($mailSettings['encryption'] ?? '') == 'null' ? 'selected' : '' }}>없음</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 발신자 정보 --}}
                        <hr class="my-4">
                        <h6 class="fw-bold text-primary mb-3">발신자 정보</h6>

                        <div class="row">
                            {{-- 발신자 이메일 --}}
                            <div class="col-md-6 mb-3">
                                <label for="from_address" class="form-label">
                                    발신자 이메일 <span class="text-danger">*</span>
                                </label>
                                <input type="email" id="from_address" name="from_address"
                                       value="{{ $mailSettings['from_address'] ?? '' }}"
                                       placeholder="noreply@example.com"
                                       required
                                       class="form-control">
                                <div class="form-text">회원가입, 비밀번호 재설정 등 인증 이메일의 발신자 주소</div>
                            </div>

                            {{-- 발신자 이름 --}}
                            <div class="col-md-6 mb-3">
                                <label for="from_name" class="form-label">
                                    발신자 이름 <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="from_name" name="from_name"
                                       value="{{ $mailSettings['from_name'] ?? '' }}"
                                       placeholder="Jiny-Auth 시스템"
                                       required
                                       class="form-control">
                                <div class="form-text">사용자에게 표시될 발신자 이름</div>
                            </div>
                        </div>

                        {{-- 버튼 영역 --}}
                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>설정 저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 도움말 --}}
        <div class="col-xl-4 col-lg-4 col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>도움말
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <strong class="text-primary">📧 Gmail 사용 시</strong>
                            <p class="mb-1 text-muted">2단계 인증을 활성화하고 앱 비밀번호를 생성하여 사용하세요.</p>
                        </div>

                        <div class="mb-3">
                            <strong class="text-primary">🔌 포트 설정</strong>
                            <p class="mb-1 text-muted">TLS는 포트 587, SSL은 포트 465를 사용합니다.</p>
                        </div>

                        <div class="mb-3">
                            <strong class="text-primary">🧪 테스트</strong>
                            <p class="mb-1 text-muted">설정 후 반드시 테스트 메일로 동작을 확인하세요.</p>
                        </div>

                        <div class="mb-3">
                            <strong class="text-primary">📮 Mailtrap.io 소개</strong>
                            <p class="mb-2 text-muted">
                                <a href="https://mailtrap.io/" target="_blank" class="text-decoration-none fw-bold">Mailtrap</a>은 개발자를 위한 이메일 테스트 서비스입니다.
                            </p>
                            <ul class="small text-muted mb-0 ps-3">
                                <li class="mb-1">실제 이메일 발송 없이 안전한 테스트 환경 제공</li>
                                <li class="mb-1">발송된 메일의 HTML/텍스트 내용 미리보기</li>
                                <li class="mb-1">메일 헤더, 첨부파일, 스팸 점수 확인 가능</li>
                                <li class="mb-1">무료 플랜으로 월 100개 메일 테스트 지원</li>
                                <li class="mb-0">개발 단계에서 실수로 실제 사용자에게 메일이 가는 것을 방지</li>
                            </ul>
                        </div>

                        <div class="mb-0">
                            <strong class="text-primary">🔒 보안</strong>
                            <p class="mb-0 text-muted">이 설정은 회원가입 인증, 비밀번호 재설정 등에 사용됩니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 테스트 메일 모달 --}}
<div class="modal fade" id="testMailModal" tabindex="-1" aria-labelledby="testMailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="testMailModalLabel">
                    <i class="bi bi-envelope-check me-2"></i>테스트 메일 발송
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="testEmail" class="form-label fw-bold">
                        수신 이메일 주소 <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="testEmail"
                           placeholder="example@domain.com"
                           class="form-control">
                    <div class="form-text">
                        테스트 메일을 수신할 이메일 주소를 입력하세요.
                    </div>
                </div>

                {{-- 발송 중 표시 --}}
                <div id="sendingIndicator" class="d-none">
                    <div class="d-flex align-items-center justify-content-center py-3">
                        <div class="spinner-border spinner-border-sm text-success me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted">메일 발송 중...</span>
                    </div>
                </div>

                {{-- 결과 메시지 --}}
                <div id="resultMessage" class="d-none">
                    <div class="alert" role="alert">
                        <div class="d-flex">
                            <div class="flex-shrink-0" id="resultIcon"></div>
                            <div class="ms-3">
                                <span id="resultText"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>취소
                </button>
                <button type="button" id="sendTestMail" class="btn btn-success">
                    <i class="bi bi-send me-2"></i>메일 발송
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 메일 드라이버 변경시 SMTP 설정 표시/숨김
    const mailerSelect = document.getElementById('mailer');
    const smtpSettings = document.getElementById('smtpSettings');

    function toggleSmtpSettings() {
        if (mailerSelect.value === 'smtp') {
            smtpSettings.style.display = 'block';
        } else {
            smtpSettings.style.display = 'none';
        }
    }

    mailerSelect.addEventListener('change', toggleSmtpSettings);
    toggleSmtpSettings();

    // 설정 저장
    document.getElementById('authMailSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const button = this.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;

        // 버튼 비활성화
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>저장 중...';

        fetch('{{ route("admin.auth.mail.setting.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 성공 알림
                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
            } else {
                throw new Error(data.message || '알 수 없는 오류');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>설정 저장 실패: ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
        })
        .finally(() => {
            // 버튼 복원
            button.disabled = false;
            button.innerHTML = originalText;

            // 페이지 상단으로 스크롤
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // 테스트 메일 모달
    const testMailBtn = document.getElementById('testMailBtn');
    const testMailModal = new bootstrap.Modal(document.getElementById('testMailModal'));
    const sendTestMail = document.getElementById('sendTestMail');
    const sendingIndicator = document.getElementById('sendingIndicator');
    const resultMessage = document.getElementById('resultMessage');
    const resultIcon = document.getElementById('resultIcon');
    const resultText = document.getElementById('resultText');
    const testEmailInput = document.getElementById('testEmail');

    // 모달 열기
    testMailBtn.addEventListener('click', function() {
        testMailModal.show();
        testEmailInput.value = '';
        sendingIndicator.classList.add('d-none');
        resultMessage.classList.add('d-none');
        setTimeout(() => testEmailInput.focus(), 500);
    });

    // Enter 키로 메일 발송
    testEmailInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            sendTestMail.click();
        }
    });

    // 테스트 메일 발송
    sendTestMail.addEventListener('click', function() {
        const testEmail = testEmailInput.value.trim();

        if (!testEmail) {
            testEmailInput.focus();
            testEmailInput.classList.add('is-invalid');
            setTimeout(() => {
                testEmailInput.classList.remove('is-invalid');
            }, 2000);
            return;
        }

        // 이메일 형식 검증
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(testEmail)) {
            showResult(false, '올바른 이메일 주소를 입력하세요.');
            return;
        }

        // UI 상태 변경
        const originalText = sendTestMail.innerHTML;
        sendTestMail.disabled = true;
        sendTestMail.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>발송 중...';
        sendingIndicator.classList.remove('d-none');
        resultMessage.classList.add('d-none');

        fetch('{{ route("admin.auth.mail.setting.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                test_email: testEmail
            })
        })
        .then(response => response.json())
        .then(data => {
            sendingIndicator.classList.add('d-none');
            showResult(data.success, data.message);

            if (data.success) {
                setTimeout(() => {
                    testMailModal.hide();
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            sendingIndicator.classList.add('d-none');
            showResult(false, '테스트 메일 발송 중 오류가 발생했습니다.');
        })
        .finally(() => {
            sendTestMail.disabled = false;
            sendTestMail.innerHTML = originalText;
        });
    });

    // 결과 표시 함수
    function showResult(success, message) {
        resultMessage.classList.remove('d-none');
        const alertDiv = resultMessage.querySelector('.alert');

        if (success) {
            alertDiv.className = 'alert alert-success';
            resultIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
        } else {
            alertDiv.className = 'alert alert-danger';
            resultIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i>';
        }

        resultText.textContent = message;
    }
});
</script>
@endsection
