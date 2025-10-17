@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', $title ?? '메일 로그')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/auth">대시보드</a></li>
        <li class="breadcrumb-item"><a href="#">메일</a></li>
        <li class="breadcrumb-item active" aria-current="page">메일 로그</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- 페이지 헤더 -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-1">{{ $title ?? '메일 로그' }}</h1>
            <p class="text-muted mb-0">{{ $subtitle ?? '발송된 메일 이력과 읽음 상태를 관리합니다' }}</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fe fe-filter me-1"></i>고급 필터
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                    <i class="fe fe-refresh-cw me-1"></i>새로고침
                </button>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="mb-2">
                        <i class="fe fe-mail text-muted" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format($stats['total']) }}</h4>
                    <small class="text-muted">전체 메일</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="mb-2">
                        <i class="fe fe-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-1 text-success">{{ number_format($stats['sent']) }}</h4>
                    <small class="text-muted">발송 완료</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="mb-2">
                        <i class="fe fe-x-circle text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-1 text-danger">{{ number_format($stats['failed']) }}</h4>
                    <small class="text-muted">발송 실패</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="mb-2">
                        <i class="fe fe-eye text-info" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-1 text-info">{{ number_format($stats['read']) }}</h4>
                    <small class="text-muted">읽음 확인</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="mb-2">
                        <i class="fe fe-calendar text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-1 text-warning">{{ number_format($stats['today']) }}</h4>
                    <small class="text-muted">오늘 발송</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center py-3">
                    <div class="mb-2">
                        <i class="fe fe-percent text-secondary" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mb-1 text-secondary">
                        @if($stats['total'] > 0)
                            {{ round(($stats['sent'] / $stats['total']) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </h4>
                    <small class="text-muted">성공률</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 메인 콘텐츠 -->
    <div class="row">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <!-- 빠른 검색 -->
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('admin.auth.mail.logs.index') }}" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="이메일, 제목, 수신자명 검색..." value="{{ $filters['search'] ?? '' }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fe fe-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.auth.mail.logs.index', ['status' => 'sent']) }}"
                                   class="btn {{ request('status') === 'sent' ? 'btn-success' : 'btn-outline-success' }}">
                                    성공
                                </a>
                                <a href="{{ route('admin.auth.mail.logs.index', ['status' => 'failed']) }}"
                                   class="btn {{ request('status') === 'failed' ? 'btn-danger' : 'btn-outline-danger' }}">
                                    실패
                                </a>
                                <a href="{{ route('admin.auth.mail.logs.index', ['type' => 'test']) }}"
                                   class="btn {{ request('type') === 'test' ? 'btn-info' : 'btn-outline-info' }}">
                                    테스트
                                </a>
                                <a href="{{ route('admin.auth.mail.logs.index') }}"
                                   class="btn {{ !request()->hasAny(['status', 'type', 'search', 'date_from', 'date_to']) ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    전체
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 활성 필터 표시 -->
                    @if(array_filter($filters))
                    <div class="mt-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="small text-muted me-2">활성 필터:</span>
                            @if($filters['type'])
                                <span class="badge bg-light text-dark border">
                                    타입: {{ $filters['type'] }}
                                    <a href="{{ route('admin.auth.mail.logs.index', array_diff_key(request()->query(), ['type' => ''])) }}" class="text-decoration-none ms-1">×</a>
                                </span>
                            @endif
                            @if($filters['status'])
                                <span class="badge bg-light text-dark border">
                                    상태: {{ $filters['status'] }}
                                    <a href="{{ route('admin.auth.mail.logs.index', array_diff_key(request()->query(), ['status' => ''])) }}" class="text-decoration-none ms-1">×</a>
                                </span>
                            @endif
                            @if($filters['date_from'])
                                <span class="badge bg-light text-dark border">
                                    시작일: {{ $filters['date_from'] }}
                                    <a href="{{ route('admin.auth.mail.logs.index', array_diff_key(request()->query(), ['date_from' => ''])) }}" class="text-decoration-none ms-1">×</a>
                                </span>
                            @endif
                            @if($filters['date_to'])
                                <span class="badge bg-light text-dark border">
                                    종료일: {{ $filters['date_to'] }}
                                    <a href="{{ route('admin.auth.mail.logs.index', array_diff_key(request()->query(), ['date_to' => ''])) }}" class="text-decoration-none ms-1">×</a>
                                </span>
                            @endif
                            @if($filters['search'])
                                <span class="badge bg-light text-dark border">
                                    검색: {{ $filters['search'] }}
                                    <a href="{{ route('admin.auth.mail.logs.index', array_diff_key(request()->query(), ['search' => ''])) }}" class="text-decoration-none ms-1">×</a>
                                </span>
                            @endif
                            <a href="{{ route('admin.auth.mail.logs.index') }}" class="badge bg-danger text-decoration-none">
                                <i class="fe fe-x me-1"></i>모든 필터 초기화
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-body p-0">

                    <!-- 테이블 -->
                    @if($mailLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-3 py-3" style="width: 140px;">
                                        <i class="fe fe-clock me-1"></i>발송일시
                                    </th>
                                    <th class="border-0 px-3 py-3" style="width: 100px;">
                                        <i class="fe fe-tag me-1"></i>타입
                                    </th>
                                    <th class="border-0 px-3 py-3">
                                        <i class="fe fe-user me-1"></i>수신자 / 제목
                                    </th>
                                    <th class="border-0 px-3 py-3" style="width: 110px;">
                                        <i class="fe fe-activity me-1"></i>상태
                                    </th>
                                    <th class="border-0 px-3 py-3" style="width: 120px;">
                                        <i class="fe fe-eye me-1"></i>읽음
                                    </th>
                                    <th class="border-0 px-3 py-3" style="width: 80px;">
                                        <i class="fe fe-repeat me-1"></i>시도
                                    </th>
                                    <th class="border-0 px-3 py-3" style="width: 60px;">작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mailLogs as $log)
                                <tr class="border-bottom">
                                    <td class="px-3 py-3">
                                        @php
                                            $createdAt = $log->created_at instanceof \Carbon\Carbon ? $log->created_at : \Carbon\Carbon::parse($log->created_at);
                                        @endphp
                                        <div class="text-nowrap">
                                            <div class="fw-medium">{{ $createdAt->format('m-d H:i') }}</div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        @php
                                            $typeColors = [
                                                'verification' => 'primary',
                                                'password_reset' => 'warning',
                                                'welcome' => 'success',
                                                'notification' => 'info',
                                                'test' => 'secondary'
                                            ];
                                            $typeNames = [
                                                'verification' => '인증',
                                                'password_reset' => '재설정',
                                                'welcome' => '환영',
                                                'notification' => '알림',
                                                'test' => '테스트'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$log->type] ?? 'light' }} text-{{ $typeColors[$log->type] === 'light' ? 'dark' : 'white' }}">
                                            {{ $typeNames[$log->type] ?? $log->type }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fe fe-user me-1 text-muted" style="font-size: 0.8rem;"></i>
                                                <span class="text-truncate fw-medium" style="max-width: 250px;" title="{{ $log->recipient_email }}">
                                                    {{ $log->recipient_email }}
                                                </span>
                                                @if($log->recipient_name)
                                                    <small class="text-muted ms-1">({{ $log->recipient_name }})</small>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fe fe-file-text me-1 text-muted" style="font-size: 0.8rem;"></i>
                                                <span class="text-truncate small text-muted" style="max-width: 300px;" title="{{ $log->subject }}">
                                                    {{ $log->subject }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        @php
                                            $statusData = [
                                                'pending' => ['color' => 'warning', 'icon' => 'clock', 'name' => '대기'],
                                                'sent' => ['color' => 'success', 'icon' => 'check-circle', 'name' => '완료'],
                                                'failed' => ['color' => 'danger', 'icon' => 'x-circle', 'name' => '실패'],
                                                'read' => ['color' => 'info', 'icon' => 'eye', 'name' => '읽음'],
                                                'bounced' => ['color' => 'secondary', 'icon' => 'arrow-left', 'name' => '반송']
                                            ];
                                            $status = $statusData[$log->status] ?? ['color' => 'secondary', 'icon' => 'help-circle', 'name' => $log->status];
                                        @endphp
                                        <span class="badge bg-{{ $status['color'] }} d-inline-flex align-items-center">
                                            <i class="fe fe-{{ $status['icon'] }} me-1" style="font-size: 0.75rem;"></i>
                                            {{ $status['name'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        @if($log->read_at)
                                            <div class="d-flex flex-column">
                                                @php
                                                    $readAt = $log->read_at instanceof \Carbon\Carbon ? $log->read_at : \Carbon\Carbon::parse($log->read_at);
                                                @endphp
                                                <span class="text-success small fw-medium">
                                                    <i class="fe fe-check me-1"></i>{{ $readAt->format('m-d H:i') }}
                                                </span>
                                                @if($log->read_count > 1)
                                                    <small class="text-muted">({{ $log->read_count }}회)</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fe fe-minus"></i> 미확인
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        @if($log->attempts > 1)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fe fe-refresh-cw me-1"></i>{{ $log->attempts }}회
                                            </span>
                                        @else
                                            <span class="text-muted small">1회</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-ghost-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fe fe-more-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="#" onclick="viewContent('{{ $log->id }}')">
                                                        <i class="fe fe-eye me-2 text-primary"></i>내용 보기
                                                    </a>
                                                </li>
                                                @if($log->error_message)
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="#" onclick="viewError('{{ $log->id }}')">
                                                        <i class="fe fe-alert-circle me-2 text-danger"></i>오류 보기
                                                    </a>
                                                </li>
                                                @endif
                                                @if($log->status === 'failed')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center" href="#" onclick="resendMail('{{ $log->id }}')">
                                                        <i class="fe fe-send me-2 text-success"></i>재발송
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <!-- 빈 상태 -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fe fe-mail text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                        </div>
                        <h5 class="text-muted mb-2">메일 로그가 없습니다</h5>
                        <p class="text-muted mb-4">
                            @if(array_filter($filters))
                                현재 필터 조건에 맞는 메일 로그가 없습니다.<br>
                                <a href="{{ route('admin.auth.mail.logs.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fe fe-refresh-cw me-1"></i>모든 로그 보기
                                </a>
                            @else
                                아직 발송된 메일이 없습니다.<br>
                                <a href="{{ route('admin.auth.mail.setting.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fe fe-settings me-1"></i>메일 설정하기
                                </a>
                            @endif
                        </p>
                    </div>
                    @endif

                    <!-- 페이지네이션 -->
                    @if($mailLogs->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top bg-light">
                        <div>
                            <small class="text-muted">
                                {{ $mailLogs->firstItem() ?? 0 }} - {{ $mailLogs->lastItem() ?? 0 }} / {{ number_format($mailLogs->total()) }}개 항목
                            </small>
                        </div>
                        <div>
                            {{ $mailLogs->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <!-- 도움말 카드 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 d-flex align-items-center">
                        <i class="fe fe-help-circle me-2"></i>도움말
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-dark mb-3">
                            <i class="fe fe-activity me-2 text-primary"></i>메일 상태 안내
                        </h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center small">
                                <span class="badge bg-warning d-inline-flex align-items-center me-2">
                                    <i class="fe fe-clock me-1" style="font-size: 0.75rem;"></i>대기
                                </span>
                                메일 발송 대기 중
                            </div>
                            <div class="d-flex align-items-center small">
                                <span class="badge bg-success d-inline-flex align-items-center me-2">
                                    <i class="fe fe-check-circle me-1" style="font-size: 0.75rem;"></i>완료
                                </span>
                                메일이 성공적으로 발송됨
                            </div>
                            <div class="d-flex align-items-center small">
                                <span class="badge bg-danger d-inline-flex align-items-center me-2">
                                    <i class="fe fe-x-circle me-1" style="font-size: 0.75rem;"></i>실패
                                </span>
                                메일 발송 실패
                            </div>
                            <div class="d-flex align-items-center small">
                                <span class="badge bg-info d-inline-flex align-items-center me-2">
                                    <i class="fe fe-eye me-1" style="font-size: 0.75rem;"></i>읽음
                                </span>
                                수신자가 메일을 확인함
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark mb-3">
                            <i class="fe fe-tag me-2 text-primary"></i>메일 타입 안내
                        </h6>
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">인증</span>
                                이메일 인증
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning text-dark me-2">재설정</span>
                                비밀번호 재설정
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">환영</span>
                                가입 환영
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2">알림</span>
                                일반 알림
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-secondary me-2">테스트</span>
                                설정 테스트
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-dark mb-3">
                            <i class="fe fe-settings me-2 text-primary"></i>주요 기능
                        </h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <i class="fe fe-search text-muted me-2"></i>실시간 검색 및 필터링
                            </li>
                            <li class="mb-2">
                                <i class="fe fe-eye text-muted me-2"></i>메일 내용 미리보기
                            </li>
                            <li class="mb-2">
                                <i class="fe fe-alert-circle text-muted me-2"></i>상세 오류 분석
                            </li>
                            <li class="mb-2">
                                <i class="fe fe-send text-muted me-2"></i>실패 메일 재발송
                            </li>
                            <li>
                                <i class="fe fe-trending-up text-muted me-2"></i>발송 통계 및 성공률 추적
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 빠른 액션 카드 -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0 d-flex align-items-center">
                        <i class="fe fe-zap me-2 text-warning"></i>빠른 액션
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.auth.mail.logs.index', ['status' => 'failed']) }}"
                           class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center">
                            <i class="fe fe-x-circle me-2"></i>실패한 메일
                        </a>
                        <a href="{{ route('admin.auth.mail.logs.index', ['status' => 'read']) }}"
                           class="btn btn-outline-info btn-sm d-flex align-items-center justify-content-center">
                            <i class="fe fe-eye me-2"></i>읽은 메일
                        </a>
                        <a href="{{ route('admin.auth.mail.logs.index', ['type' => 'test']) }}"
                           class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center">
                            <i class="fe fe-settings me-2"></i>테스트 메일
                        </a>
                        <a href="{{ route('admin.auth.mail.logs.index', ['date_from' => date('Y-m-d')]) }}"
                           class="btn btn-outline-warning btn-sm d-flex align-items-center justify-content-center">
                            <i class="fe fe-calendar me-2"></i>오늘 발송
                        </a>
                        <hr class="my-2">
                        <a href="{{ route('admin.auth.mail.setting.index') }}"
                           class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center">
                            <i class="fe fe-mail me-2"></i>메일 설정
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 필터 모달 -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="{{ route('admin.auth.mail.logs.index') }}">
                <div class="modal-header">
                    <h5 class="modal-title">메일 로그 필터</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">메일 타입</label>
                            <select name="type" class="form-select">
                                <option value="">전체</option>
                                <option value="verification" {{ $filters['type'] === 'verification' ? 'selected' : '' }}>이메일 인증</option>
                                <option value="password_reset" {{ $filters['type'] === 'password_reset' ? 'selected' : '' }}>비밀번호 재설정</option>
                                <option value="welcome" {{ $filters['type'] === 'welcome' ? 'selected' : '' }}>가입 환영</option>
                                <option value="notification" {{ $filters['type'] === 'notification' ? 'selected' : '' }}>알림</option>
                                <option value="test" {{ $filters['type'] === 'test' ? 'selected' : '' }}>테스트</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">상태</label>
                            <select name="status" class="form-select">
                                <option value="">전체</option>
                                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>발송 대기</option>
                                <option value="sent" {{ $filters['status'] === 'sent' ? 'selected' : '' }}>발송 완료</option>
                                <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>발송 실패</option>
                                <option value="read" {{ $filters['status'] === 'read' ? 'selected' : '' }}>읽음 확인</option>
                                <option value="bounced" {{ $filters['status'] === 'bounced' ? 'selected' : '' }}>반송</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">시작일</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">종료일</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">검색</label>
                        <input type="text" name="search" class="form-control" placeholder="이메일 주소, 제목, 수신자명 검색" value="{{ $filters['search'] }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">표시 개수</label>
                        <select name="per_page" class="form-select">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20개</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50개</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100개</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <a href="{{ route('admin.auth.mail.logs.index') }}" class="btn btn-outline-danger">초기화</a>
                    <button type="submit" class="btn btn-primary">적용</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 메일 내용 보기 모달 -->
<div class="modal fade" id="contentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">메일 내용</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="mailContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<!-- 오류 보기 모달 -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">오류 정보</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="errorContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function viewContent(logId) {
    // AJAX로 메일 내용을 가져와서 모달에 표시
    fetch(`/admin/auth/mail/logs/${logId}/content`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('mailContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('contentModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('메일 내용을 불러오는데 실패했습니다.');
        });
}

function viewError(logId) {
    // AJAX로 오류 정보를 가져와서 모달에 표시
    fetch(`/admin/auth/mail/logs/${logId}/error`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('errorContent').innerHTML = `
                    <div class="alert alert-danger">
                        <h6>오류 메시지</h6>
                        <pre class="mb-0">${data.error}</pre>
                    </div>
                    <div class="mt-3">
                        <h6>발생 시간</h6>
                        <p>${data.created_at}</p>
                    </div>
                    <div class="mt-3">
                        <h6>시도 횟수</h6>
                        <p>${data.attempts}회</p>
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('errorModal')).show();
            } else {
                alert('오류 정보를 불러오는데 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류 정보를 불러오는데 실패했습니다.');
        });
}

function resendMail(logId) {
    if (confirm('이 메일을 다시 발송하시겠습니까?')) {
        fetch(`/admin/auth/mail/logs/${logId}/resend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('메일이 다시 발송되었습니다.');
                location.reload();
            } else {
                alert('메일 재발송에 실패했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('메일 재발송에 실패했습니다.');
        });
    }
}
</script>
@endpush
