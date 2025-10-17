@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '메일 템플릿 상세')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.dashboard') }}">대시보드</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.mail.templates.index') }}">메일 템플릿 관리</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $template->name }}</li>
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
                        <i class="bi bi-envelope-paper text-primary"></i>
                        {{ $template->name }}
                        @if($template->is_active)
                            <span class="badge bg-success ms-2">활성화</span>
                        @else
                            <span class="badge bg-secondary ms-2">비활성화</span>
                        @endif
                    </h1>
                    <p class="text-muted mb-0">메일 템플릿 상세 정보</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.auth.mail.templates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> 목록
                        </a>
                        <a href="{{ route('admin.auth.mail.templates.edit', $template->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> 수정
                        </a>
                        <button type="button" class="btn btn-danger"
                                onclick="deleteTemplate({{ $template->id }}, '{{ $template->name }}')">
                            <i class="bi bi-trash"></i> 삭제
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- 왼쪽: 템플릿 정보 --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> 템플릿 정보
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">템플릿 이름:</th>
                                    <td>{{ $template->name }}</td>
                                </tr>
                                <tr>
                                    <th>타입:</th>
                                    <td>
                                        <span class="badge bg-info">{{ $template->type_name }}</span>
                                        <small class="text-muted">({{ $template->type }})</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>상태:</th>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">활성화</span>
                                        @else
                                            <span class="badge bg-secondary">비활성화</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="120">등록자:</th>
                                    <td>
                                        {{ $template->admin_user_name }}
                                        @if($template->admin_user_id)
                                            <small class="text-muted">(ID: {{ $template->admin_user_id }})</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>등록일:</th>
                                    <td>{{ $template->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일:</th>
                                    <td>{{ $template->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($template->description)
                    <div class="mb-4">
                        <h6>템플릿 설명</h6>
                        <div class="p-3 bg-light rounded">
                            {{ $template->description }}
                        </div>
                    </div>
                    @endif

                    <div class="mb-4">
                        <h6>메일 제목</h6>
                        <div class="p-3 border rounded bg-white">
                            {{ $template->subject }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>메일 내용</h6>
                        <div class="p-3 border rounded bg-white" style="white-space: pre-wrap;">{{ $template->message }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 오른쪽: 미리보기 및 기타 정보 --}}
        <div class="col-lg-4">
            {{-- 메일 미리보기 --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-eye"></i> 메일 미리보기
                    </h6>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <div class="bg-white rounded p-3 shadow-sm">
                            <div class="border-bottom pb-2 mb-3">
                                <div class="fw-bold text-primary">{{ $template->subject }}</div>
                                <small class="text-muted">from: noreply@{{ config('app.url', 'example.com') }}</small>
                            </div>
                            <div style="font-size: 14px; line-height: 1.5; white-space: pre-wrap;">
                                {{ Str::limit($template->message, 300) }}
                            </div>
                            @if(strlen($template->message) > 300)
                                <div class="text-center mt-2">
                                    <small class="text-muted">... 내용이 더 있습니다</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 사용 가능한 변수 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-code"></i> 사용 가능한 변수
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <strong>사용자 정보:</strong>
                            <ul class="list-unstyled mt-1 ms-3">
                                <li><code>@{{ '{{' }}USER_NAME@{{ '}}' }}</code> - 사용자 이름</li>
                                <li><code>@{{ '{{' }}USER_EMAIL@{{ '}}' }}</code> - 사용자 이메일</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <strong>사이트 정보:</strong>
                            <ul class="list-unstyled mt-1 ms-3">
                                <li><code>@{{ '{{' }}SITE_NAME@{{ '}}' }}</code> - 사이트 이름</li>
                                <li><code>@{{ '{{' }}SITE_URL@{{ '}}' }}</code> - 사이트 URL</li>
                            </ul>
                        </div>
                        <div>
                            <strong>Laravel 설정:</strong>
                            <ul class="list-unstyled mt-1 ms-3">
                                <li><code>@{{ '{{' }} config("app.name") @{{ '}}' }}</code></li>
                                <li><code>@{{ '{{' }} config("app.url") @{{ '}}' }}</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 테스트 메일 발송 --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-send"></i> 테스트 메일 발송
                    </h6>
                </div>
                <div class="card-body">
                    <form id="test-mail-form">
                        <div class="mb-3">
                            <label for="test-email" class="form-label">테스트 이메일</label>
                            <input type="email" class="form-control" id="test-email" required
                                   placeholder="test@example.com">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-send"></i> 테스트 발송
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 삭제 확인 모달 --}}
<div class="modal fade" id="delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">템플릿 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="delete-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">삭제</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let deleteTemplateId = null;

function deleteTemplate(id, name) {
    deleteTemplateId = id;
    document.getElementById('delete-message').textContent = `"${name}" 템플릿을 삭제하시겠습니까?`;

    const modal = new bootstrap.Modal(document.getElementById('delete-modal'));
    modal.show();
}

document.getElementById('confirm-delete').addEventListener('click', function() {
    if (deleteTemplateId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('admin.auth.mail.templates.index') }}/${deleteTemplateId}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
});

// 테스트 메일 발송
document.getElementById('test-mail-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('test-email').value;

    if (email) {
        alert(`"${email}"로 테스트 메일 발송 기능은 추후 구현될 예정입니다.`);
    }
});
</script>
@endpush