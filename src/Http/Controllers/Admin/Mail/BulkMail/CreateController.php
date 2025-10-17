<?php

namespace Jiny\Mail\Http\Controllers\Admin\Mail\BulkMail;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthUser;
use Jiny\Mail\Models\AuthMailTemplate;
use Illuminate\Support\Facades\DB;

/**
 * 전체 메일 발송 생성 컨트롤러
 *
 * 여러 사용자에게 일괄적으로 메일을 발송하는 기능을 제공합니다.
 */
class CreateController extends Controller
{
    /**
     * 전체 메일 발송 페이지
     */
    public function __invoke(Request $request)
    {
        // 활성화된 메일 템플릿 조회
        $mailTemplates = AuthMailTemplate::active()->get();

        // 사용자 통계
        $userStats = $this->getUserStats();

        // 사용자 그룹 옵션 (등급별, 타입별 등)
        $userGroups = $this->getUserGroups();

        return view('jiny-mail::admin.mail.bulk.create', [
            'mailTemplates' => $mailTemplates,
            'userStats' => $userStats,
            'userGroups' => $userGroups,
        ]);
    }

    /**
     * 사용자 통계 조회
     */
    protected function getUserStats()
    {
        // 기본 사용자 테이블 통계
        $baseUsers = DB::table('users')->count();

        // 활성 사용자 수
        $activeUsers = DB::table('users')
            ->whereNull('suspended_until')
            ->orWhere('suspended_until', '<', now())
            ->count();

        // 이메일 인증된 사용자 수
        $verifiedUsers = DB::table('users')
            ->whereNotNull('email_verified_at')
            ->count();

        return [
            'total' => $baseUsers,
            'active' => $activeUsers,
            'verified' => $verifiedUsers,
            'suspended' => $baseUsers - $activeUsers,
        ];
    }

    /**
     * 사용자 그룹 옵션 조회
     */
    protected function getUserGroups()
    {
        $groups = [];

        // 전체 사용자 그룹
        $totalUsers = DB::table('users')->whereNotNull('email')->count();
        $groups['all'] = $totalUsers;

        // 사용자 타입별 그룹
        $userTypes = DB::table('users')
            ->select('utype', DB::raw('count(*) as count'))
            ->whereNotNull('utype')
            ->whereNotNull('email')
            ->groupBy('utype')
            ->get();

        if ($userTypes->count() > 0) {
            foreach ($userTypes as $type) {
                $typeName = $this->getUserTypeName($type->utype);
                $groups['type'][] = [
                    'value' => $type->utype,
                    'label' => "{$typeName} ({$type->count}명)",
                    'count' => $type->count,
                ];
            }
        }

        // 사용자 등급별 그룹 (grade 컬럼이 있는 경우)
        if (DB::getSchemaBuilder()->hasColumn('users', 'grade')) {
            $userGrades = DB::table('users')
                ->select('grade', DB::raw('count(*) as count'))
                ->whereNotNull('grade')
                ->whereNotNull('email')
                ->groupBy('grade')
                ->get();

            if ($userGrades->count() > 0) {
                foreach ($userGrades as $grade) {
                    $groups['grade'][] = [
                        'value' => $grade->grade,
                        'label' => "등급 {$grade->grade} ({$grade->count}명)",
                        'count' => $grade->count,
                    ];
                }
            }
        }

        // 이메일 인증 상태별 그룹
        $verifiedCount = DB::table('users')->whereNotNull('email_verified_at')->whereNotNull('email')->count();
        $unverifiedCount = DB::table('users')->whereNull('email_verified_at')->whereNotNull('email')->count();

        $groups['verification'] = [
            [
                'value' => 'verified',
                'label' => "이메일 인증 완료 ({$verifiedCount}명)",
                'count' => $verifiedCount,
            ],
            [
                'value' => 'unverified',
                'label' => "이메일 미인증 ({$unverifiedCount}명)",
                'count' => $unverifiedCount,
            ],
        ];

        // 계정 상태별 그룹
        $activeCount = DB::table('users')
            ->whereNotNull('email')
            ->where(function($q) {
                $q->whereNull('suspended_until')
                  ->orWhere('suspended_until', '<', now());
            })
            ->count();

        $suspendedCount = DB::table('users')
            ->whereNotNull('email')
            ->where('suspended_until', '>', now())
            ->count();

        $groups['status'] = [
            [
                'value' => 'active',
                'label' => "활성 계정 ({$activeCount}명)",
                'count' => $activeCount,
            ],
            [
                'value' => 'suspended',
                'label' => "정지 계정 ({$suspendedCount}명)",
                'count' => $suspendedCount,
            ],
        ];

        return $groups;
    }

    /**
     * 사용자 타입명 반환
     */
    protected function getUserTypeName($utype)
    {
        $typeNames = [
            'USR' => '일반 사용자',
            'ADM' => '관리자',
            'EDI' => '편집자',
            'VIP' => 'VIP 사용자',
            'PRO' => '프로 사용자',
        ];

        return $typeNames[$utype] ?? $utype;
    }
}