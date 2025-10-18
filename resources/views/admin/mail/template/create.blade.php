@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '메일 템플릿 추가')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.dashboard') }}">대시보드</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.mail.templates.index') }}">메일 템플릿 관리</a></li>
        <li class="breadcrumb-item active" aria-current="page">새 템플릿 추가</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    {{-- 페이지 헤딩 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-plus-circle text-primary"></i>
                        새 메일 템플릿 추가
                    </h1>
                    <p class="text-muted mb-0">새로운 메일 템플릿을 등록합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.mail.templates.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로 돌아가기
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-pencil"></i> 템플릿 정보
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mail.templates.store') }}" method="POST" id="template-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">템플릿 이름 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="예: 신규 회원 환영 메시지">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">템플릿 타입 <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">타입 선택</option>
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">메일 제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                   id="subject" name="subject" value="{{ old('subject') }}" required
                                   placeholder="예: {{ config('app.name') }}에 가입을 환영합니다!">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">메일 내용 <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message" name="message" rows="12" required
                                      placeholder="메일 내용을 입력하세요...">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">템플릿 설명</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="이 템플릿의 용도나 설명을 입력하세요...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    템플릿 활성화
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.mail.templates.index') }}" class="btn btn-secondary me-md-2">
                                취소
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="bi bi-check-circle"></i> 템플릿 등록
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 도움말 사이드바 --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-question-circle"></i> 도움말
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>사용 가능한 변수</h6>
                        <p class="text-muted small">메일 내용에서 다음 변수들을 사용할 수 있습니다:</p>
                        <ul class="list-unstyled small">
                            <li><code>@{{ '{{' }}USER_NAME@{{ '}}' }}</code> - 사용자 이름</li>
                            <li><code>@{{ '{{' }}USER_EMAIL@{{ '}}' }}</code> - 사용자 이메일</li>
                            <li><code>@{{ '{{' }}SITE_NAME@{{ '}}' }}</code> - 사이트 이름</li>
                            <li><code>@{{ '{{' }}SITE_URL@{{ '}}' }}</code> - 사이트 URL</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6>Laravel 설정 변수</h6>
                        <p class="text-muted small">Laravel 설정값도 사용 가능합니다:</p>
                        <ul class="list-unstyled small">
                            <li><code>@{{ '{{' }} config("app.name") @{{ '}}' }}</code> - 앱 이름</li>
                            <li><code>@{{ '{{' }} config("app.url") @{{ '}}' }}</code> - 앱 URL</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6>템플릿 타입 안내</h6>
                        <ul class="list-unstyled small">
                            <li><strong>환영:</strong> 신규 회원가입 시</li>
                            <li><strong>공지:</strong> 일반 공지사항</li>
                            <li><strong>알림:</strong> 중요한 알림 사항</li>
                            <li><strong>인증:</strong> 이메일 인증</li>
                            <li><strong>비밀번호 재설정:</strong> 비밀번호 변경</li>
                            <li><strong>계정 업데이트:</strong> 계정 정보 변경</li>
                            <li><strong>시스템:</strong> 시스템 메시지</li>
                            <li><strong>사용자 정의:</strong> 기타 용도</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- 미리보기 카드 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-eye"></i> 미리보기
                    </h6>
                </div>
                <div class="card-body">
                    <div class="border p-3 bg-light rounded">
                        <div class="fw-bold mb-2" id="preview-subject">제목이 여기에 표시됩니다</div>
                        <div class="small text-muted" id="preview-message">내용이 여기에 표시됩니다</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    const previewSubject = document.getElementById('preview-subject');
    const previewMessage = document.getElementById('preview-message');

    function updatePreview() {
        const subject = subjectInput.value || '제목이 여기에 표시됩니다';
        const message = messageInput.value || '내용이 여기에 표시됩니다';

        previewSubject.textContent = subject;
        previewMessage.textContent = message.substring(0, 200) + (message.length > 200 ? '...' : '');
    }

    subjectInput.addEventListener('input', updatePreview);
    messageInput.addEventListener('input', updatePreview);

    // 폼 제출 시 로딩 상태
    document.getElementById('template-form').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>등록 중...';
    });
});
</script>
@endpush
