{{-- 메일 --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navMail"
        aria-expanded="false" aria-controls="navMail">
        <i class="nav-icon fe fe-mail me-2"></i>
        메일
    </a>
    <div id="navMail" class="collapse" data-bs-parent="#sideNavbar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.mail.setting.index') }}">
                    <i class="fe fe-settings me-2"></i>메일 설정
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.mail.templates.index') }}">
                    <i class="fe fe-file-text me-2"></i>메일 템플릿
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.mail.bulk.create') }}">
                    <i class="fe fe-send me-2"></i>전체 메일 발송
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.mail.logs.index') }}">
                    <i class="fe fe-list me-2"></i>메일 로그
                </a>
            </li>
        </ul>
    </div>
</li>
