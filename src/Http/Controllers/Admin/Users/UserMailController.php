<?php

namespace Jiny\Mail\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthUser;
use Jiny\Mail\Models\ShardTable;
use Jiny\Mail\Models\AuthMailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Jiny\Auth\Services\ShardingService;
use Jiny\Admin\Mail\EmailMailable;

/**
 * 사용자별 메일 발송 컨트롤러
 *
 * 특정 사용자에게 메일을 발송하고 발송 기록을 관리합니다.
 */
class UserMailController extends Controller
{
    protected $shardingService;
    protected $config;
    protected $configPath;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
        $this->configPath = dirname(__DIR__, 5) . '/config/setting.json';
        $this->config = $this->loadSettings();
    }

    /**
     * JSON 설정 파일에서 설정 읽기
     */
    private function loadSettings()
    {
        if (file_exists($this->configPath)) {
            try {
                $jsonContent = file_get_contents($this->configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }

                \Log::error('JSON 파싱 오류: ' . json_last_error_msg());
            } catch (\Exception $e) {
                \Log::error('설정 파일 읽기 오류: ' . $e->getMessage());
            }
        }

        return [];
    }

    /**
     * 사용자별 메일 발송 페이지
     */
    public function index(Request $request, $id)
    {
        $shardId = $request->get('shard_id');
        $user = null;
        $userTable = 'users';

        if ($shardId) {
            // 샤드 테이블에서 사용자 조회
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $userTable = $shardTable->getShardTableName($shardId);
            $user = DB::table($userTable)->where('id', $id)->first();
        } else {
            // 일반 테이블에서 사용자 조회
            $user = AuthUser::find($id);
        }

        if (!$user) {
            return back()->with('error', '사용자를 찾을 수 없습니다.');
        }

        // 발송된 메일 히스토리 조회
        $mailLogs = $this->getMailLogs($user->id, $user->uuid ?? null, $shardId);

        // 메일 발송 통계
        $mailStats = $this->getMailStats($user->id, $user->uuid ?? null, $shardId);

        // 활성화된 메일 템플릿 조회
        $mailTemplates = AuthMailTemplate::active()->get();

        return view('jiny-mail::admin.auth-users.mail', [
            'user' => $user,
            'shardId' => $shardId,
            'userTable' => $userTable,
            'mailLogs' => $mailLogs,
            'mailStats' => $mailStats,
            'config' => $this->config,
            'mailTemplates' => $mailTemplates,
        ]);
    }

    /**
     * 메일 발송 처리
     */
    public function send(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $shardId = $request->get('shard_id');
        $subject = $request->input('subject');
        $message = $request->input('message');

        // 현재 로그인한 관리자 정보
        $adminUser = auth()->user();
        $adminUserId = $adminUser->id ?? null;
        $adminUserName = $adminUser->name ?? 'System';

        $user = null;
        if ($shardId) {
            // 샤드 테이블에서 사용자 조회
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $tableName = $shardTable->getShardTableName($shardId);
            $user = DB::table($tableName)->where('id', $id)->first();
        } else {
            // 일반 테이블에서 사용자 조회
            $user = AuthUser::find($id);
        }

        if (!$user) {
            return back()->with('error', '사용자를 찾을 수 없습니다.');
        }

        try {
            // 저장된 메일 설정 로드 및 적용
            $authMailConfig = $this->loadAuthMailConfig();
            $this->applyAuthMailConfig($authMailConfig);

            // 템플릿 변수 치환
            $processedSubject = $this->replaceTemplateVariables($subject, $user);
            $processedMessage = $this->replaceTemplateVariables($message, $user);

            // HTML 메일 내용 생성
            $htmlContent = $this->generateEmailHtml($user, $processedMessage, $adminUser, $processedSubject);

            // 발신자 정보 설정 (저장된 설정 우선 사용)
            $fromAddress = $authMailConfig['from_address'] ?? config('mail.from.address', 'noreply@' . config('app.url'));
            $fromName = $authMailConfig['from_name'] ?? $adminUser->name ?? config('mail.from.name', config('app.name'));

            // 메일 발송 전 최종 설정 로깅
            \Log::info('메일 발송 시작', [
                'to_email' => $user->email,
                'to_name' => $user->name,
                'from_address' => $fromAddress,
                'from_name' => $fromName,
                'subject' => $subject,
                'config_host' => config('mail.mailers.smtp.host'),
                'config_port' => config('mail.mailers.smtp.port'),
                'config_username' => config('mail.mailers.smtp.username'),
            ]);

            // EmailMailable을 사용하여 메일 발송
            Mail::to($user->email, $user->name)->send(new EmailMailable(
                $processedSubject,
                $htmlContent,
                $fromAddress,
                $fromName,
                $user->email
            ));

            \Log::info('메일 발송 성공', [
                'to_email' => $user->email,
                'subject' => $processedSubject,
            ]);

            // 메일 발송 로그 기록
            $this->recordMailLog([
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'shard_id' => $shardId,
                'email' => $user->email,
                'name' => $user->name,
                'subject' => $processedSubject,
                'message' => $processedMessage,
                'admin_user_id' => $adminUserId,
                'admin_user_name' => $adminUserName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'sent_at' => now(),
                'status' => 'sent',
            ]);

            // AJAX 요청인 경우 JSON 응답
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '메일이 성공적으로 발송되었습니다.',
                    'mail' => [
                        'to' => $user->email,
                        'subject' => $processedSubject,
                        'sent_at' => now()->toDateTimeString(),
                    ]
                ]);
            }

            return back()->with('success', '메일이 성공적으로 발송되었습니다.');

        } catch (\Exception $e) {
            \Log::error('메일 발송 실패', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            // 실패 로그 기록
            $this->recordMailLog([
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'shard_id' => $shardId,
                'email' => $user->email,
                'name' => $user->name,
                'subject' => $processedSubject ?? $subject,
                'message' => $processedMessage ?? $message,
                'admin_user_id' => $adminUserId,
                'admin_user_name' => $adminUserName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'sent_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // AJAX 요청인 경우 JSON 응답
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '메일 발송에 실패했습니다: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', '메일 발송에 실패했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 사용자의 메일 발송 히스토리 조회
     */
    protected function getMailLogs($userId, $userUuid = null, $shardId = null)
    {
        // 메일 로그 테이블이 없으면 빈 컬렉션 반환
        if (!DB::getSchemaBuilder()->hasTable('user_mail_logs')) {
            $this->createMailLogsTable();
        }

        $query = DB::table('user_mail_logs');

        if ($userUuid) {
            $query->where('user_uuid', $userUuid);
        } else {
            $query->where('user_id', $userId);
        }

        if ($shardId) {
            $query->where('shard_id', $shardId);
        }

        return $query->orderBy('sent_at', 'desc')->get();
    }

    /**
     * 메일 발송 통계
     */
    protected function getMailStats($userId, $userUuid = null, $shardId = null)
    {
        // 메일 로그 테이블이 없으면 기본값 반환
        if (!DB::getSchemaBuilder()->hasTable('user_mail_logs')) {
            return [
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
            ];
        }

        $query = DB::table('user_mail_logs');

        if ($userUuid) {
            $query->where('user_uuid', $userUuid);
        } else {
            $query->where('user_id', $userId);
        }

        if ($shardId) {
            $query->where('shard_id', $shardId);
        }

        $stats = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => array_sum($stats),
            'sent' => $stats['sent'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
        ];
    }

    /**
     * 메일 발송 로그 기록
     */
    protected function recordMailLog(array $logData)
    {
        try {
            // 메일 로그 테이블이 없으면 생성
            if (!DB::getSchemaBuilder()->hasTable('user_mail_logs')) {
                $this->createMailLogsTable();
            }

            DB::table('user_mail_logs')->insert([
                'user_id' => $logData['user_id'],
                'user_uuid' => $logData['user_uuid'],
                'shard_id' => $logData['shard_id'],
                'email' => $logData['email'],
                'name' => $logData['name'],
                'subject' => $logData['subject'],
                'message' => $logData['message'],
                'admin_user_id' => $logData['admin_user_id'],
                'admin_user_name' => $logData['admin_user_name'],
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'sent_at' => $logData['sent_at'],
                'status' => $logData['status'],
                'error_message' => $logData['error_message'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('메일 발송 로그 기록 성공', [
                'user_id' => $logData['user_id'],
                'email' => $logData['email'],
                'subject' => $logData['subject'],
                'status' => $logData['status'],
                'admin_user_name' => $logData['admin_user_name'],
            ]);

        } catch (\Exception $e) {
            \Log::error('메일 발송 로그 기록 실패', [
                'error' => $e->getMessage(),
                'log_data' => $logData,
            ]);
        }
    }

    /**
     * 메일 로그 테이블 생성
     */
    protected function createMailLogsTable()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS user_mail_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            user_uuid TEXT,
            shard_id INTEGER,
            email TEXT NOT NULL,
            name TEXT,
            subject TEXT NOT NULL,
            message TEXT NOT NULL,
            admin_user_id INTEGER,
            admin_user_name TEXT,
            ip_address TEXT,
            user_agent TEXT,
            sent_at TIMESTAMP,
            status TEXT NOT NULL DEFAULT "sent",
            error_message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }

    /**
     * HTML 메일 내용 생성
     */
    protected function generateEmailHtml($user, $message, $adminUser, $subject)
    {
        $appName = config('app.name', 'JinyPHP');
        $appUrl = config('app.url', 'http://localhost');
        $adminName = $adminUser->name ?? '시스템 관리자';

        $html = '<div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">';
        $html .= '<div style="background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">';

        // 헤더
        $html .= '<div style="text-align: center; border-bottom: 3px solid #007bff; padding-bottom: 20px; margin-bottom: 30px;">';
        $html .= '<div style="font-size: 24px; font-weight: bold; color: #007bff; margin-bottom: 10px;">🔐 ' . htmlspecialchars($appName) . '</div>';
        $html .= '<div style="color: #6c757d; font-size: 14px;">Authentication System</div>';
        $html .= '</div>';

        // 인사말
        $html .= '<div style="font-size: 18px; color: #495057; margin-bottom: 20px;">';
        $html .= '안녕하세요 <strong>' . htmlspecialchars($user->name) . '</strong>님,';
        $html .= '</div>';

        $html .= '<p>관리자로부터 메시지를 받으셨습니다.</p>';

        // 메시지 내용
        $html .= '<div style="background-color: #f8f9fa; border-left: 4px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 0 5px 5px 0; white-space: pre-wrap; word-wrap: break-word;">';
        $html .= htmlspecialchars($message);
        $html .= '</div>';

        // 사이트 방문 버튼
        $html .= '<div style="text-align: center; margin: 30px 0;">';
        $html .= '<a href="' . htmlspecialchars($appUrl) . '" style="display: inline-block; padding: 12px 25px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 10px 0; font-weight: bold;">사이트 방문하기</a>';
        $html .= '</div>';

        // 메시지 정보
        $html .= '<div style="background-color: #e9ecef; border-radius: 5px; padding: 15px; margin-top: 20px; font-size: 14px;">';
        $html .= '<strong>📋 메시지 정보</strong><br>';
        $html .= '<div style="margin-top: 10px;">';
        $html .= '<strong>발송자:</strong> ' . htmlspecialchars($adminName) . '<br>';
        $html .= '<strong>발송 일시:</strong> ' . now()->format('Y년 m월 d일 H:i') . '<br>';
        $html .= '<strong>수신자:</strong> ' . htmlspecialchars($user->email);
        $html .= '</div>';
        $html .= '</div>';

        // 푸터
        $html .= '<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; font-size: 14px;">';
        $html .= '<p>이 메일은 ' . htmlspecialchars($appName) . ' 관리자가 발송한 메시지입니다.<br>';
        $html .= '궁금한 사항이 있으시면 고객센터로 문의해 주세요.</p>';
        $html .= '<p style="margin-top: 15px;">';
        $html .= '<strong>' . htmlspecialchars($appName) . '</strong><br>';
        $html .= '<a href="' . htmlspecialchars($appUrl) . '" style="color: #007bff;">' . htmlspecialchars($appUrl) . '</a>';
        $html .= '</p>';
        $html .= '<p style="font-size: 12px; color: #adb5bd; margin-top: 20px;">';
        $html .= '이 메일을 받고 싶지 않으시면 관리자에게 문의해 주세요.';
        $html .= '</p>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * 저장된 Auth 메일 설정 로드
     */
    protected function loadAuthMailConfig()
    {
        // jiny/auth/config/mail.php 파일에서 직접 읽기
        $configPath = base_path('jiny/auth/config/mail.php');

        \Log::info('메일 설정 파일 경로 확인', [
            'config_path' => $configPath,
            'file_exists' => file_exists($configPath)
        ]);

        if (file_exists($configPath)) {
            $config = include $configPath;
            \Log::info('메일 설정 로드 성공', [
                'config' => $config
            ]);
            return $config;
        }

        \Log::warning('메일 설정 파일이 없어서 기본값 사용', [
            'config_path' => $configPath
        ]);

        // 파일이 없으면 기본 config 사용
        return [
            'mailer' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'from_name' => env('MAIL_FROM_NAME', 'Example'),
        ];
    }

    /**
     * Auth 메일 설정을 런타임에 적용
     */
    protected function applyAuthMailConfig($authMailConfig)
    {
        // 런타임 메일 설정 적용 - .env 값이 아닌 저장된 설정 사용
        config([
            'mail.default' => $authMailConfig['mailer'],
            'mail.mailers.smtp.host' => $authMailConfig['host'],
            'mail.mailers.smtp.port' => $authMailConfig['port'],
            'mail.mailers.smtp.username' => $authMailConfig['username'],
            'mail.mailers.smtp.password' => $authMailConfig['password'],
            'mail.mailers.smtp.encryption' => $authMailConfig['encryption'] === 'null' ? null : $authMailConfig['encryption'],
            'mail.from.address' => $authMailConfig['from_address'],
            'mail.from.name' => $authMailConfig['from_name'],
        ]);

        // 메일러가 smtp가 아닌 경우 추가 설정
        if ($authMailConfig['mailer'] !== 'smtp') {
            switch ($authMailConfig['mailer']) {
                case 'sendmail':
                    config(['mail.mailers.sendmail.path' => '/usr/sbin/sendmail -bs']);
                    break;
                case 'log':
                    config(['mail.mailers.log.channel' => env('MAIL_LOG_CHANNEL', 'mail')]);
                    break;
            }
        }

        \Log::info('Auth 메일 설정이 런타임에 적용되었습니다.', [
            'mailer' => $authMailConfig['mailer'],
            'host' => $authMailConfig['host'],
            'from_address' => $authMailConfig['from_address'],
        ]);
    }

    /**
     * 템플릿 변수를 실제 값으로 치환
     */
    protected function replaceTemplateVariables($content, $user)
    {
        // 1. 사용자 정의 템플릿 변수 치환
        $userReplacements = [
            '{{USER_NAME}}' => $user->name ?? '',
            '{{USER_EMAIL}}' => $user->email ?? '',
            '{{SITE_NAME}}' => config('app.name', 'JinyPHP'),
            '{{SITE_URL}}' => config('app.url', 'http://localhost'),
        ];

        foreach ($userReplacements as $variable => $value) {
            $content = str_replace($variable, $value, $content);
        }

        // 2. Laravel Blade 문법 치환
        $bladeReplacements = [
            '{{ config("app.name") }}' => config('app.name', 'JinyPHP'),
            '{{config("app.name")}}' => config('app.name', 'JinyPHP'),
            '{{ config(\'app.name\') }}' => config('app.name', 'JinyPHP'),
            '{{config(\'app.name\')}}' => config('app.name', 'JinyPHP'),
            '{{ config("app.url") }}' => config('app.url', 'http://localhost'),
            '{{config("app.url")}}' => config('app.url', 'http://localhost'),
            '{{ config(\'app.url\') }}' => config('app.url', 'http://localhost'),
            '{{config(\'app.url\')}}' => config('app.url', 'http://localhost'),
        ];

        foreach ($bladeReplacements as $pattern => $value) {
            $content = str_replace($pattern, $value, $content);
        }

        // 3. 정규식을 이용한 config() 함수 일반적 처리
        $content = preg_replace_callback(
            '/\{\{\s*config\(["\']([^"\']+)["\']\)\s*\}\}/',
            function ($matches) {
                $configKey = $matches[1];
                return config($configKey, '');
            },
            $content
        );

        // 4. 남은 특정 Blade 문법만 제거 (알려진 패턴만)
        $removePatterns = [
            '/\{\{\s*config\([^)]+\)\s*\}\}/',  // 처리되지 않은 config() 함수
            '/\{\{\s*env\([^)]+\)\s*\}\}/',     // env() 함수
            '/\{\{\s*url\([^)]+\)\s*\}\}/',     // url() 함수
            '/\{\{\s*asset\([^)]+\)\s*\}\}/',   // asset() 함수
        ];

        foreach ($removePatterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        return $content;
    }
}