<?php

namespace Jiny\Mail\Http\Controllers\Admin\Mail\Template;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthMailTemplate;

/**
 * 메일 템플릿 목록 컨트롤러
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = AuthMailTemplate::query();

        // 검색 기능
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // 타입 필터
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // 활성화 상태 필터
        if ($request->has('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(20);

        // 타입별 통계
        $typeStats = AuthMailTemplate::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        return view('jiny-mail::admin.mail.template.index', [
            'templates' => $templates,
            'typeStats' => $typeStats,
            'typeOptions' => AuthMailTemplate::getTypeOptions(),
            'search' => $search,
            'selectedType' => $type,
            'selectedActive' => $request->get('is_active'),
        ]);
    }
}