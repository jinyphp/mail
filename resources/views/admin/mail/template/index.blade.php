@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '메일 템플릿 관리')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.auth.dashboard') }}">대시보드</a></li>
        <li class="breadcrumb-item active" aria-current="page">메일 템플릿 관리</li>
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
                        메일 템플릿 관리
                    </h1>
                    <p class="text-muted mb-0">메일 발송에 사용할 템플릿을 관리합니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.mail.templates.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> 새 템플릿 추가
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 통계 카드 --}}
    <div class="row mb-4">
        @foreach($typeOptions as $type => $typeName)
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="mb-2">{{ $typeStats[$type] ?? 0 }}</h4>
                    <p class="text-muted mb-0">{{ $typeName }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 검색 및 필터 --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.mail.templates.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="search" class="form-label">검색</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       value="{{ $search }}" placeholder="템플릿명, 제목, 타입 검색">
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">타입</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">전체 타입</option>
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="is_active" class="form-label">상태</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="">전체 상태</option>
                                    <option value="1" {{ $selectedActive === '1' ? 'selected' : '' }}>활성화</option>
                                    <option value="0" {{ $selectedActive === '0' ? 'selected' : '' }}>비활성화</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-search"></i> 검색
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 템플릿 목록 --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-list"></i> 템플릿 목록
                        <span class="badge bg-secondary ms-2">{{ $templates->total() }}개</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>템플릿 정보</th>
                                    <th>타입</th>
                                    <th>제목</th>
                                    <th>상태</th>
                                    <th>등록자</th>
                                    <th>등록일</th>
                                    <th>액션</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $template->name }}</div>
                                        @if($template->description)
                                        <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $template->type_name }}</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;">
                                            {{ $template->subject }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">활성화</span>
                                        @else
                                            <span class="badge bg-secondary">비활성화</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $template->admin_user_name }}
                                        @if($template->admin_user_id)
                                            <small class="text-muted">(ID: {{ $template->admin_user_id }})</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $template->created_at->format('Y-m-d H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.auth.mail.templates.show', $template->id) }}"
                                               class="btn btn-outline-info" title="상세보기">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.auth.mail.templates.edit', $template->id) }}"
                                               class="btn btn-outline-warning" title="수정">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="deleteTemplate({{ $template->id }}, '{{ $template->name }}')"
                                                    title="삭제">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 페이지네이션 --}}
                    <div class="d-flex justify-content-center p-4">
                        {{ $templates->withQueryString()->links() }}
                    </div>
                    @else
                    <div class="text-center py-5 px-4">
                        <div class="text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">등록된 템플릿이 없습니다.</p>
                            <a href="{{ route('admin.auth.mail.templates.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> 첫 번째 템플릿 추가하기
                            </a>
                        </div>
                    </div>
                    @endif
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
</script>
@endpush