<?php

namespace Jiny\Mail\Http\Controllers\Admin\Mail\MailLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Mail\Models\AuthMailLog;

/**
 * MailLogs IndexController
 *
 * 메일 로그 관리 및 조회 기능을 제공합니다.
 */
class IndexController extends Controller
{
    /**
     * 메일 로그 목록 페이지
     */
    public function __invoke(Request $request)
    {
        // 필터링 조건
        $filters = [
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ];

        // 메일 로그 조회 (Eloquent 모델 사용)
        $query = AuthMailLog::query()
            ->orderBy('created_at', 'desc');

        // 필터 적용
        if ($filters['type']) {
            $query->where('type', $filters['type']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if ($filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('recipient_email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('subject', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('recipient_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 20);
        $mailLogs = $query->paginate($perPage);

        // 통계 정보 (Eloquent 모델 사용)
        $stats = [
            'total' => AuthMailLog::count(),
            'sent' => AuthMailLog::where('status', 'sent')->count(),
            'failed' => AuthMailLog::where('status', 'failed')->count(),
            'read' => AuthMailLog::where('status', 'read')->count(),
            'today' => AuthMailLog::whereDate('created_at', today())->count(),
        ];

        return view('jiny-mail::admin.mail.logs.index', [
            'mailLogs' => $mailLogs,
            'filters' => $filters,
            'stats' => $stats,
            'title' => '메일 로그',
            'subtitle' => '발송된 메일 이력과 읽음 상태를 관리합니다',
        ]);
    }
}