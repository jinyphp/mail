@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '전체 메일 발송')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.auth.dashboard') }}">대시보드</a></li>
            <li class="breadcrumb-item active" aria-current="page">전체 메일 발송</li>
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
                            <i class="bi bi-send text-primary"></i>
                            전체 메일 발송
                        </h1>
                        <p class="text-muted mb-0">여러 사용자에게 일괄적으로 메일을 발송합니다</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.mail.templates.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-gear"></i> 템플릿 관리
                        </a>
                        <a href="{{ route('admin.mail.logs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list"></i> 메일 로그
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 알림 메시지 --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            {{-- 왼쪽: 메일 작성 폼 --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-envelope-plus"></i> 메일 작성
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.mail.bulk.send') }}" method="POST" id="bulk-mail-form">
                            @csrf

                            {{-- 템플릿 선택 --}}
                            @if ($mailTemplates->count() > 0)
                                <div class="mb-4">
                                    <label for="template-select" class="form-label">메일 템플릿 선택 (선택사항)</label>
                                    <select class="form-select" id="template-select" onchange="onTemplateSelect(this)">
                                        <option value="">-- 템플릿을 선택해주세요 --</option>
                                        @foreach ($mailTemplates as $template)
                                            <option value="{{ $template->id }}"
                                                data-template="{{ json_encode([
                                                    'id' => $template->id,
                                                    'name' => $template->name,
                                                    'subject' => $template->subject,
                                                    'message' => $template->message,
                                                    'type_name' => $template->type_name,
                                                ]) }}">
                                                {{ $template->name }} ({{ $template->type_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle"></i>
                                        템플릿을 선택하면 제목과 내용이 자동으로 입력됩니다.
                                    </div>
                                </div>
                            @endif

                            {{-- 제목 --}}
                            <div class="mb-3">
                                <label for="subject" class="form-label">
                                    메일 제목 <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                    id="subject" name="subject" value="{{ old('subject') }}" placeholder="메일 제목을 입력하세요"
                                    required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- 내용 --}}
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    메일 내용 <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="8"
                                    placeholder="메일 내용을 입력하세요" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    사용 가능한 변수: @{{ '{{' }}USER_NAME@{{ ' }}' }},
                                    @{{ '{{' }}USER_EMAIL@{{ ' }}' }},
                                    @{{ '{{' }}SITE_NAME@{{ ' }}' }},
                                    @{{ '{{' }}SITE_URL@{{ ' }}' }}
                                </div>
                            </div>

                            {{-- 수신자 선택 방식 --}}
                            <div class="mb-4">
                                <label class="form-label">수신자 선택 방식 <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="recipient_type"
                                                id="recipient_manual" value="manual"
                                                {{ old('recipient_type', 'manual') === 'manual' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="recipient_manual">
                                                <strong>직접 입력</strong>
                                            </label>
                                            <div class="form-text">이메일 주소를 직접 입력합니다</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="recipient_type"
                                                id="recipient_group" value="group"
                                                {{ old('recipient_type') === 'group' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="recipient_group">
                                                <strong>그룹 선택</strong>
                                            </label>
                                            <div class="form-text">사용자 그룹별로 선택합니다</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 직접 입력 방식 --}}
                            <div id="manual-recipients" class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">
                                        수신자 이메일 <span class="text-danger">*</span>
                                    </label>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="openEmailModal()">
                                        <i class="bi bi-plus-circle"></i> 이메일 추가
                                    </button>
                                </div>

                                {{-- 이메일 목록 표시 --}}
                                <div id="email-list" class="border rounded p-3 mb-3"
                                    style="min-height: 120px; max-height: 200px; overflow-y: auto;">
                                    <div id="empty-state" class="text-center text-muted py-3">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mb-2">등록된 이메일이 없습니다</p>
                                        <small>위의 "이메일 추가" 버튼을 클릭하여 이메일을 추가하세요</small>
                                    </div>
                                </div>

                                {{-- 숨겨진 textarea (폼 제출용) --}}
                                <textarea class="form-control @error('recipients') is-invalid @enderror d-none" id="recipients" name="recipients">{{ old('recipients') }}</textarea>
                                @error('recipients')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i>
                                    이메일 주소의 유효성을 자동으로 검사합니다. 총 <span id="email-count">0</span>개의 이메일이 등록되었습니다.
                                </div>
                            </div>

                            {{-- 그룹 선택 방식 --}}
                            <div id="group-recipients" class="mb-4" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="group_type" class="form-label">그룹 유형</label>
                                        <select class="form-select" id="group_type" name="group_type"
                                            onchange="updateGroupOptions()">
                                            <option value="">-- 그룹 유형 선택 --</option>
                                            <option value="all" {{ old('group_type') === 'all' ? 'selected' : '' }}>전체
                                                사용자</option>
                                            @if (isset($userGroups['type']) && count($userGroups['type']) > 0)
                                                <option value="type"
                                                    {{ old('group_type') === 'type' ? 'selected' : '' }}>사용자 타입별</option>
                                            @endif
                                            @if (isset($userGroups['grade']) && count($userGroups['grade']) > 0)
                                                <option value="grade"
                                                    {{ old('group_type') === 'grade' ? 'selected' : '' }}>사용자 등급별</option>
                                            @endif
                                            <option value="verification"
                                                {{ old('group_type') === 'verification' ? 'selected' : '' }}>이메일 인증 상태별
                                            </option>
                                            <option value="status" {{ old('group_type') === 'status' ? 'selected' : '' }}>
                                                계정 상태별</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="group_value" class="form-label">세부 옵션</label>
                                        <select class="form-select" id="group_value" name="group_value">
                                            <option value="">-- 세부 옵션 선택 --</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="group-preview" class="mt-3"></div>
                            </div>

                            {{-- 테스트 발송 --}}
                            <div class="mb-3">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-2">
                                            <i class="bi bi-flask"></i> 테스트 발송
                                        </h6>
                                        <p class="small text-muted mb-2">
                                            실제 발송 전에 관리자 이메일로 테스트 메일을 먼저 발송해보세요.
                                        </p>
                                        <div class="input-group input-group-sm">
                                            <input type="email" class="form-control" id="test-email"
                                                placeholder="테스트 수신 이메일 ({{ auth()->user()->email ?? 'admin@example.com' }})"
                                                value="{{ auth()->user()->email ?? '' }}">
                                            <button type="button" class="btn btn-outline-info"
                                                onclick="sendTestEmail()">
                                                <i class="bi bi-send"></i> 테스트 발송
                                            </button>
                                        </div>
                                        <div id="test-result" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- 발송 버튼 --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="send-btn">
                                    <i class="bi bi-send"></i> 메일 발송
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 오른쪽: 통계 및 도움말 --}}
            <div class="col-lg-4">
                {{-- 사용자 통계 --}}
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-bar-chart"></i> 사용자 통계
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="h4 mb-1 text-primary">{{ number_format($userStats['total']) }}</div>
                                    <div class="small text-muted">전체 사용자</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="h4 mb-1 text-success">{{ number_format($userStats['active']) }}</div>
                                    <div class="small text-muted">활성 사용자</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <div class="h4 mb-1 text-info">{{ number_format($userStats['verified']) }}</div>
                                    <div class="small text-muted">인증 완료</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <div class="h4 mb-1 text-warning">{{ number_format($userStats['suspended']) }}</div>
                                    <div class="small text-muted">정지 계정</div>
                                </div>
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
                                    <li><code>@{{ '{{' }}USER_NAME@{{ ' }}' }}</code> - 사용자 이름</li>
                                    <li><code>@{{ '{{' }}USER_EMAIL@{{ ' }}' }}</code> - 사용자 이메일
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <strong>사이트 정보:</strong>
                                <ul class="list-unstyled mt-1 ms-3">
                                    <li><code>@{{ '{{' }}SITE_NAME@{{ ' }}' }}</code> - 사이트 이름
                                    </li>
                                    <li><code>@{{ '{{' }}SITE_URL@{{ ' }}' }}</code> - 사이트 URL
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 주의사항 --}}
                <div class="card mt-3">
                    <div class="card-header bg-warning-subtle">
                        <h6 class="card-title mb-0 text-warning-emphasis">
                            <i class="bi bi-exclamation-triangle"></i> 주의사항
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <i class="bi bi-dot"></i>
                                많은 사용자에게 발송 시 시간이 오래 걸릴 수 있습니다
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-dot"></i>
                                메일 내용을 신중하게 확인 후 발송하세요
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-dot"></i>
                                스팸으로 분류될 수 있으니 적절한 내용으로 작성하세요
                            </li>
                            <li>
                                <i class="bi bi-dot"></i>
                                발송 후 메일 로그에서 결과를 확인할 수 있습니다
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 이메일 추가 모달 --}}
    <div class="modal fade" id="email-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-envelope-plus"></i> 이메일 주소 추가
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- 왼쪽: 이메일 입력 --}}
                        <div class="col-md-6">
                            <h6 class="mb-3">이메일 입력</h6>

                            {{-- 단일 이메일 입력 --}}
                            <div class="mb-3">
                                <label for="single-email" class="form-label">이메일 주소</label>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="single-email"
                                        placeholder="user@example.com">
                                    <button type="button" class="btn btn-outline-primary" onclick="addSingleEmail()">
                                        <i class="bi bi-plus"></i> 추가
                                    </button>
                                </div>
                                <div class="form-text">올바른 이메일 형식을 입력하세요</div>
                            </div>

                            {{-- 일괄 입력 --}}
                            <div class="mb-3">
                                <label for="bulk-emails" class="form-label">일괄 입력</label>
                                <textarea class="form-control" id="bulk-emails" rows="5"
                                    placeholder="user1@example.com&#10;user2@example.com&#10;user3@example.com"></textarea>
                                <div class="form-text">한 줄에 하나씩 입력하거나 쉼표(,) 또는 세미콜론(;)으로 구분</div>
                                <button type="button" class="btn btn-outline-success btn-sm mt-2"
                                    onclick="addBulkEmails()">
                                    <i class="bi bi-plus-circle"></i> 일괄 추가
                                </button>
                            </div>

                            {{-- 데이터베이스에서 검색 --}}
                            <div class="mb-3">
                                <label for="user-search" class="form-label">사용자 검색</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="user-search"
                                        placeholder="이름 또는 이메일로 검색...">
                                    <button type="button" class="btn btn-outline-info" onclick="searchUsers()">
                                        <i class="bi bi-search"></i> 검색
                                    </button>
                                </div>
                                <div id="search-results" class="mt-2" style="max-height: 150px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>

                        {{-- 오른쪽: 추가된 이메일 목록 --}}
                        <div class="col-md-6">
                            <h6 class="mb-3">
                                추가된 이메일 목록
                                <span class="badge bg-primary ms-2" id="modal-email-count">0</span>
                            </h6>

                            <div id="modal-email-list" class="border rounded p-3"
                                style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                                <div id="modal-empty-state" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2"></i>
                                    <p class="mb-0">추가된 이메일이 없습니다</p>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearAllEmails()">
                                    <i class="bi bi-trash"></i> 전체 삭제
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm"
                                    onclick="removeInvalidEmails()">
                                    <i class="bi bi-exclamation-triangle"></i> 유효하지 않은 이메일 제거
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-primary" onclick="applyEmails()">
                        <i class="bi bi-check-circle"></i> 적용
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 숨겨진 데이터 (JavaScript용) --}}
    <script type="text/javascript">
        const userGroups = @json($userGroups);
        let modalEmailList = []; // 모달에서 관리하는 이메일 목록
    </script>
@endsection

@push('scripts')
    <script>
        // 수신자 선택 방식 변경
        document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const manualDiv = document.getElementById('manual-recipients');
                const groupDiv = document.getElementById('group-recipients');

                if (this.value === 'manual') {
                    manualDiv.style.display = 'block';
                    groupDiv.style.display = 'none';
                    document.getElementById('recipients').required = true;
                    document.getElementById('group_type').required = false;
                    document.getElementById('group_value').required = false;
                } else {
                    manualDiv.style.display = 'none';
                    groupDiv.style.display = 'block';
                    document.getElementById('recipients').required = false;
                    document.getElementById('group_type').required = true;
                    document.getElementById('group_value').required = true;
                }
            });
        });

        // 템플릿 선택 시 처리
        function onTemplateSelect(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];

            if (!selectedOption.value) {
                return;
            }

            try {
                const templateData = JSON.parse(selectedOption.dataset.template);

                document.getElementById('subject').value = templateData.subject;
                document.getElementById('message').value = templateData.message;

                // 성공 알림
                showNotification('템플릿 적용', `"${templateData.name}" 템플릿이 적용되었습니다.`, 'success');
            } catch (error) {
                console.error('템플릿 데이터 파싱 오류:', error);
                showNotification('오류', '템플릿 데이터를 읽는 중 오류가 발생했습니다.', 'error');
            }
        }

        // 그룹 옵션 업데이트
        function updateGroupOptions() {
            const groupType = document.getElementById('group_type').value;
            const groupValueSelect = document.getElementById('group_value');
            const previewDiv = document.getElementById('group-preview');

            // 기존 옵션 제거
            groupValueSelect.innerHTML = '<option value="">-- 세부 옵션 선택 --</option>';
            previewDiv.innerHTML = '';

            if (!groupType) {
                groupValueSelect.required = false;
                return;
            }

            if (groupType === 'all') {
                // 전체 사용자 선택 시 group_value 불필요
                groupValueSelect.required = false;
                groupValueSelect.disabled = true;
                previewDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                전체 사용자 ${userGroups.all || 0}명에게 발송됩니다.
            </div>
        `;
                return;
            }

            // 다른 옵션 선택 시 group_value 필수
            groupValueSelect.required = true;
            groupValueSelect.disabled = false;

            const options = userGroups[groupType] || [];

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.label;
                optionElement.dataset.count = option.count;
                groupValueSelect.appendChild(optionElement);
            });

            // 그룹 값 변경 시 미리보기 업데이트 (이벤트 리스너 중복 방지)
            groupValueSelect.removeEventListener('change', updateGroupPreview);
            groupValueSelect.addEventListener('change', updateGroupPreview);
        }

        // 그룹 미리보기 업데이트 함수 분리
        function updateGroupPreview() {
            const previewDiv = document.getElementById('group-preview');
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                const count = selectedOption.dataset.count || 0;
                previewDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                "${selectedOption.textContent}" 그룹에서 ${count}명에게 발송됩니다.
            </div>
        `;
            } else {
                previewDiv.innerHTML = '';
            }
        }

        // 알림 표시
        function showNotification(title, message, type = 'info') {
            // 간단한 알림 (실제로는 Toast나 모달 사용 권장)
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'error' ? 'alert-danger' : 'alert-info';

            const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

            // 기존 알림 제거
            const existingAlert = document.querySelector(
                '.alert:not(.alert-success):not(.alert-warning):not(.alert-danger)');
            if (existingAlert) {
                existingAlert.remove();
            }

            // 새 알림 추가
            const container = document.querySelector('.container-fluid');
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = alertHtml;
            container.insertBefore(tempDiv.firstElementChild, container.firstElementChild.nextSibling);
        }

        // 폼 제출 시 확인
        document.getElementById('bulk-mail-form').addEventListener('submit', function(e) {
            const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
            let recipientCount = 0;
            let confirmMessage = '';

            if (recipientType === 'manual') {
                const emailString = document.getElementById('recipients').value.trim();
                if (!emailString) {
                    e.preventDefault();
                    alert('이메일 주소를 입력해주세요.');
                    return;
                }
                const emails = emailString.split(';').filter(email => email.trim());
                recipientCount = emails.length;
                confirmMessage = `${recipientCount}개의 이메일 주소로 메일을 발송하시겠습니까?`;
            } else {
                const groupType = document.getElementById('group_type').value;
                if (!groupType) {
                    e.preventDefault();
                    alert('그룹 유형을 선택해주세요.');
                    return;
                }

                if (groupType === 'all') {
                    recipientCount = userGroups.all || 0;
                    confirmMessage = `전체 사용자 ${recipientCount}명에게 메일을 발송하시겠습니까?`;
                } else {
                    const groupValueSelect = document.getElementById('group_value');
                    const selectedOption = groupValueSelect.options[groupValueSelect.selectedIndex];
                    if (!selectedOption || !selectedOption.value) {
                        e.preventDefault();
                        alert('세부 옵션을 선택해주세요.');
                        return;
                    }
                    recipientCount = selectedOption.dataset.count || 0;
                    confirmMessage = `"${selectedOption.textContent}" 그룹에서 ${recipientCount}명에게 메일을 발송하시겠습니까?`;
                }
            }

            if (recipientCount === 0) {
                e.preventDefault();
                alert('발송할 수신자가 없습니다.');
                return;
            }

            if (!confirm(confirmMessage + '\n\n발송 후에는 취소할 수 없습니다.')) {
                e.preventDefault();
                return;
            }

            // 발송 버튼 비활성화
            const sendBtn = document.getElementById('send-btn');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>발송 중...';
        });

        // 테스트 메일 발송
        async function sendTestEmail() {
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            const testEmail = document.getElementById('test-email').value.trim();
            const resultDiv = document.getElementById('test-result');

            // 유효성 검사
            if (!testEmail) {
                resultDiv.innerHTML =
                    '<div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> 테스트 수신 이메일을 입력해주세요.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                return;
            }

            if (!isValidEmail(testEmail)) {
                resultDiv.innerHTML =
                    '<div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> 올바른 이메일 형식이 아닙니다.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                return;
            }

            if (!subject) {
                resultDiv.innerHTML =
                    '<div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> 메일 제목을 입력해주세요.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                document.getElementById('subject').focus();
                return;
            }

            if (!message) {
                resultDiv.innerHTML =
                    '<div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> 메일 내용을 입력해주세요.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                document.getElementById('message').focus();
                return;
            }

            // 로딩 표시
            resultDiv.innerHTML =
                '<div class="alert alert-info"><span class="spinner-border spinner-border-sm me-2"></span>테스트 메일 발송 중...</div>';

            try {
                const formData = new FormData();
                formData.append('subject', subject);
                formData.append('message', message);
                formData.append('test_email', testEmail);

                const response = await fetch('{{ route('admin.mail.bulk.test') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = `<div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle"></i> ${data.message || '테스트 발송 실패'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
                }
            } catch (error) {
                console.error('Test send error:', error);
                resultDiv.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle"></i> 테스트 발송 중 오류가 발생했습니다: ${error.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
            }
        }

        // 이메일 모달 열기
        function openEmailModal() {
            // 현재 이메일 목록을 모달에 복사
            const currentEmails = document.getElementById('recipients').value;
            if (currentEmails) {
                modalEmailList = currentEmails.split(';').map(email => email.trim()).filter(email => email);
            } else {
                modalEmailList = [];
            }

            updateModalEmailList();
            const modal = new bootstrap.Modal(document.getElementById('email-modal'));
            modal.show();
        }

        // 단일 이메일 추가
        function addSingleEmail() {
            const emailInput = document.getElementById('single-email');
            const email = emailInput.value.trim();

            if (!email) {
                alert('이메일 주소를 입력해주세요.');
                return;
            }

            if (!isValidEmail(email)) {
                alert('올바른 이메일 형식이 아닙니다.');
                emailInput.focus();
                return;
            }

            if (modalEmailList.includes(email)) {
                alert('이미 추가된 이메일입니다.');
                return;
            }

            modalEmailList.push(email);
            emailInput.value = '';
            updateModalEmailList();
            showEmailNotification('success', '이메일이 추가되었습니다.');
        }

        // 일괄 이메일 추가
        function addBulkEmails() {
            const bulkInput = document.getElementById('bulk-emails');
            const text = bulkInput.value.trim();

            if (!text) {
                alert('이메일 주소를 입력해주세요.');
                return;
            }

            // 다양한 구분자로 분리
            const emails = text.split(/[,;\n\r]/).map(email => email.trim()).filter(email => email);

            let addedCount = 0;
            let invalidCount = 0;
            let duplicateCount = 0;

            emails.forEach(email => {
                if (!isValidEmail(email)) {
                    invalidCount++;
                    return;
                }

                if (modalEmailList.includes(email)) {
                    duplicateCount++;
                    return;
                }

                modalEmailList.push(email);
                addedCount++;
            });

            bulkInput.value = '';
            updateModalEmailList();

            let message = `${addedCount}개의 이메일이 추가되었습니다.`;
            if (invalidCount > 0) message += ` (잘못된 형식: ${invalidCount}개)`;
            if (duplicateCount > 0) message += ` (중복: ${duplicateCount}개)`;

            showEmailNotification('info', message);
        }

        // 사용자 검색
        function searchUsers() {
            const searchInput = document.getElementById('user-search');
            const query = searchInput.value.trim();

            if (!query) {
                alert('검색어를 입력해주세요.');
                return;
            }

            if (query.length < 2) {
                alert('검색어는 2글자 이상 입력해주세요.');
                return;
            }

            // AJAX 요청 (실제 구현에서는 백엔드 API 필요)
            // 여기서는 시뮬레이션
            const resultsDiv = document.getElementById('search-results');
            resultsDiv.innerHTML =
                '<div class="text-center"><div class="spinner-border spinner-border-sm"></div> 검색 중...</div>';

            // 시뮬레이션 데이터 (실제로는 fetch API 사용)
            setTimeout(() => {
                const mockResults = [{
                        name: '김철수',
                        email: 'kim@example.com'
                    },
                    {
                        name: '이영희',
                        email: 'lee@example.com'
                    },
                    {
                        name: '박민수',
                        email: 'park@example.com'
                    }
                ].filter(user =>
                    user.name.includes(query) || user.email.includes(query)
                );

                if (mockResults.length === 0) {
                    resultsDiv.innerHTML = '<div class="text-muted text-center py-2">검색 결과가 없습니다.</div>';
                    return;
                }

                let html = '';
                mockResults.forEach(user => {
                    const isAdded = modalEmailList.includes(user.email);
                    html += `
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <div class="fw-semibold">${user.name}</div>
                        <small class="text-muted">${user.email}</small>
                    </div>
                    <button class="btn btn-outline-primary btn-sm ${isAdded ? 'disabled' : ''}"
                            onclick="addUserEmail('${user.email}')" ${isAdded ? 'disabled' : ''}>
                        ${isAdded ? '추가됨' : '추가'}
                    </button>
                </div>
            `;
                });

                resultsDiv.innerHTML = html;
            }, 500);
        }

        // 사용자 이메일 추가
        function addUserEmail(email) {
            if (modalEmailList.includes(email)) {
                return;
            }

            modalEmailList.push(email);
            updateModalEmailList();

            // 검색 결과 업데이트
            searchUsers();

            showEmailNotification('success', '사용자가 추가되었습니다.');
        }

        // 이메일 유효성 검사
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // 모달 이메일 목록 업데이트
        function updateModalEmailList() {
            const listDiv = document.getElementById('modal-email-list');
            const countSpan = document.getElementById('modal-email-count');
            const emptyState = document.getElementById('modal-empty-state');

            countSpan.textContent = modalEmailList.length;

            if (modalEmailList.length === 0) {
                emptyState.style.display = 'block';
                listDiv.innerHTML = '';
                listDiv.appendChild(emptyState);
                return;
            }

            emptyState.style.display = 'none';

            let html = '';
            modalEmailList.forEach((email, index) => {
                const isValid = isValidEmail(email);
                const badgeClass = isValid ? 'bg-success' : 'bg-danger';
                const badgeText = isValid ? '유효' : '잘못됨';

                html += `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div class="flex-grow-1">
                    <span class="${isValid ? '' : 'text-danger'}">${email}</span>
                    <span class="badge ${badgeClass} ms-2">${badgeText}</span>
                </div>
                <button class="btn btn-outline-danger btn-sm" onclick="removeEmailFromModal(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
            });

            listDiv.innerHTML = html;
        }

        // 모달에서 이메일 제거
        function removeEmailFromModal(index) {
            modalEmailList.splice(index, 1);
            updateModalEmailList();
            showEmailNotification('info', '이메일이 제거되었습니다.');
        }

        // 모든 이메일 삭제
        function clearAllEmails() {
            if (modalEmailList.length === 0) {
                return;
            }

            if (confirm('모든 이메일을 삭제하시겠습니까?')) {
                modalEmailList = [];
                updateModalEmailList();
                showEmailNotification('warning', '모든 이메일이 삭제되었습니다.');
            }
        }

        // 유효하지 않은 이메일 제거
        function removeInvalidEmails() {
            const invalidCount = modalEmailList.filter(email => !isValidEmail(email)).length;

            if (invalidCount === 0) {
                showEmailNotification('info', '유효하지 않은 이메일이 없습니다.');
                return;
            }

            if (confirm(`${invalidCount}개의 유효하지 않은 이메일을 제거하시겠습니까?`)) {
                modalEmailList = modalEmailList.filter(email => isValidEmail(email));
                updateModalEmailList();
                showEmailNotification('success', `${invalidCount}개의 유효하지 않은 이메일이 제거되었습니다.`);
            }
        }

        // 이메일 목록 적용
        function applyEmails() {
            // 메인 페이지 업데이트
            document.getElementById('recipients').value = modalEmailList.join(';');
            updateMainEmailList();

            // 모달 닫기
            const modal = bootstrap.Modal.getInstance(document.getElementById('email-modal'));
            modal.hide();

            showNotification('성공', `${modalEmailList.length}개의 이메일이 적용되었습니다.`, 'success');
        }

        // 메인 페이지 이메일 목록 업데이트
        function updateMainEmailList() {
            const emailListDiv = document.getElementById('email-list');
            const emptyState = document.getElementById('empty-state');
            const countSpan = document.getElementById('email-count');
            const recipientInput = document.getElementById('recipients');

            const emails = recipientInput.value ? recipientInput.value.split(';').map(e => e.trim()).filter(e => e) : [];
            countSpan.textContent = emails.length;

            if (emails.length === 0) {
                emailListDiv.innerHTML = '';
                emailListDiv.appendChild(emptyState);
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            let html = '';
            emails.forEach((email, index) => {
                const isValid = isValidEmail(email);
                const badgeClass = isValid ? 'bg-success' : 'bg-danger';
                const badgeText = isValid ? '유효' : '잘못됨';

                html += `
            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <div class="flex-grow-1">
                    <span class="${isValid ? '' : 'text-danger'}">${email}</span>
                    <span class="badge ${badgeClass} ms-2">${badgeText}</span>
                </div>
                <button class="btn btn-outline-danger btn-sm" onclick="removeMainEmail(${index})">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
            });

            emailListDiv.innerHTML = html;
        }

        // 메인에서 이메일 제거
        function removeMainEmail(index) {
            const recipientInput = document.getElementById('recipients');
            const emails = recipientInput.value.split(';').map(e => e.trim()).filter(e => e);
            emails.splice(index, 1);
            recipientInput.value = emails.join(';');
            updateMainEmailList();
        }

        // 이메일 관련 알림
        function showEmailNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'warning' ? 'alert-warning' :
                type === 'info' ? 'alert-info' : 'alert-primary';

            const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show mt-2" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

            const modalBody = document.querySelector('#email-modal .modal-body');
            const existingAlert = modalBody.querySelector('.alert');
            if (existingAlert) existingAlert.remove();

            modalBody.insertAdjacentHTML('afterbegin', alertHtml);

            // 3초 후 자동 제거
            setTimeout(() => {
                const alert = modalBody.querySelector('.alert');
                if (alert) alert.remove();
            }, 3000);
        }

        // 페이지 로드 시 초기 상태 설정
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="recipient_type"]:checked');
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change'));
            }

            // 초기 이메일 목록 표시
            updateMainEmailList();

            // Enter 키로 이메일 추가
            document.getElementById('single-email').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addSingleEmail();
                }
            });

            // 검색 Enter 키
            document.getElementById('user-search').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchUsers();
                }
            });
        });
    </script>
@endpush
