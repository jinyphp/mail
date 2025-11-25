<?php

namespace Jiny\Mail\Http\Controllers\Admin\BulkMail;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Models\AuthUser;
use Jiny\Mail\Models\AuthMailTemplate;
use Illuminate\Support\Facades\DB;
use Jiny\Mail\Facades\UserMail;

/**
 * 전체 메일 발송 처리 컨트롤러
 *
 * 실제 메일 발송 로직을 처리합니다.
 */
class SendController extends Controller
{
    /**
     * 전체 메일 발송 처리
     */
    public function __invoke(Request $request)
    {
        // 기본 유효성 검사
        $rules = [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:manual,group',
            'recipients' => 'required_if:recipient_type,manual|string',
            'group_type' => 'required_if:recipient_type,group|string',
        ];

        // group_type이 'all'이 아닌 경우에만 group_value 필수
        if ($request->input('recipient_type') === 'group' && $request->input('group_type') !== 'all') {
            $rules['group_value'] = 'required|string';
        }

        $request->validate($rules);

        $subject = $request->input('subject');
        $message = $request->input('message');
        $recipientType = $request->input('recipient_type');

        // 현재 로그인한 관리자 정보
        $adminUser = auth()->user();
        $adminUserId = $adminUser->id ?? null;
        $adminUserName = $adminUser->name ?? 'System';

        try {
            // UserMail 파사드를 통해 저장된 메일 설정 로드 및 적용
            $authMailConfig = UserMail::loadConfig();
            UserMail::applyConfig($authMailConfig);

            // 수신자 목록 생성
            $recipients = $this->getRecipients($request, $recipientType);

            if (empty($recipients)) {
                return back()->with('error', '발송할 수신자가 없습니다.');
            }

            \Log::info('일괄 메일 발송 시작', [
                'admin_user' => $adminUserName,
                'recipient_count' => count($recipients),
                'subject' => $subject,
                'mail_config' => [
                    'host' => $authMailConfig['host'] ?? 'not_set',
                    'port' => $authMailConfig['port'] ?? 'not_set',
                    'from_address' => $authMailConfig['from_address'] ?? 'not_set',
                ]
            ]);

            // 메일 발송 처리
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($recipients as $recipient) {
                try {
                    // 템플릿 변수 치환
                    $processedSubject = $this->replaceTemplateVariables($subject, $recipient);
                    $processedMessage = $this->replaceTemplateVariables($message, $recipient);

                    // HTML 메일 내용 생성
                    $htmlContent = $this->generateEmailHtml($recipient, $processedMessage, $adminUser, $processedSubject);

                    \Log::info('개별 메일 발송 시도', [
                        'to' => $recipient->email,
                        'subject' => $processedSubject,
                    ]);

                    // UserMail 파사드를 통해 메일 발송
                    $result = UserMail::sendByHtml(
                        $recipient->email,
                        $processedSubject,
                        $htmlContent,
                        $recipient->name,
                        $authMailConfig
                    );

                    if ($result['success'] ?? false) {
                        $successCount++;
                    } else {
                        throw new \Exception($result['message'] ?? '메일 발송 실패');
                    }

                    \Log::info('개별 메일 발송 성공', [
                        'to' => $recipient->email,
                        'subject' => $processedSubject,
                    ]);

                    // 성공 로그 기록
                    $this->recordMailLog([
                        'user_id' => $recipient->id,
                        'user_uuid' => $recipient->uuid ?? null,
                        'shard_id' => null,
                        'email' => $recipient->email,
                        'name' => $recipient->name,
                        'subject' => $processedSubject,
                        'message' => $processedMessage,
                        'admin_user_id' => $adminUserId,
                        'admin_user_name' => $adminUserName,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'sent_at' => now(),
                        'status' => 'sent',
                        'is_bulk' => true,
                    ]);

                } catch (\Exception $e) {
                    $failCount++;
                    $errors[] = "{$recipient->email}: " . $e->getMessage();

                    // 실패 로그 기록
                    $this->recordMailLog([
                        'user_id' => $recipient->id,
                        'user_uuid' => $recipient->uuid ?? null,
                        'shard_id' => null,
                        'email' => $recipient->email,
                        'name' => $recipient->name,
                        'subject' => $processedSubject ?? $subject,
                        'message' => $processedMessage ?? $message,
                        'admin_user_id' => $adminUserId,
                        'admin_user_name' => $adminUserName,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'sent_at' => now(),
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'is_bulk' => true,
                    ]);
                }
            }

            // 결과 메시지 생성
            $resultMessage = "메일 발송이 완료되었습니다. ";
            $resultMessage .= "성공: {$successCount}건, 실패: {$failCount}건";

            \Log::info('일괄 메일 발송 완료', [
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'admin_user' => $adminUserName,
            ]);

            if ($failCount > 0) {
                \Log::warning('일괄 메일 발송 중 일부 실패', [
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'errors' => array_slice($errors, 0, 5),
                ]);
                return redirect()->route('admin.mail.bulk.create')->with('warning', $resultMessage);
            }

            return redirect()->route('admin.mail.bulk.create')->with('success', $resultMessage);

        } catch (\Exception $e) {
            \Log::error('일괄 메일 발송 실패', [
                'error' => $e->getMessage(),
                'admin_user' => $adminUserName,
            ]);

            return redirect()->route('admin.mail.bulk.create')->with('error', '메일 발송 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    protected function getRecipients(Request $request, $recipientType)
    {
        if ($recipientType === 'manual') {
            $emailString = $request->input('recipients');
            if (empty($emailString)) {
                return [];
            }
            $emails = array_filter(array_map('trim', explode(';', $emailString)));
            $recipients = [];
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $user = DB::table('users')->where('email', $email)->first();
                    if ($user) {
                        $recipients[] = $user;
                    } else {
                        $recipients[] = (object) [
                            'id' => null,
                            'email' => $email,
                            'name' => $email,
                            'uuid' => null,
                        ];
                    }
                }
            }
            return $recipients;
        }

        if ($recipientType === 'group') {
            $groupType = $request->input('group_type');
            $groupValue = $request->input('group_value');
            if (empty($groupType)) {
                return [];
            }
            $query = DB::table('users');
            switch ($groupType) {
                case 'all':
                    break;
                case 'type':
                    if (empty($groupValue)) return [];
                    $query->where('utype', $groupValue);
                    break;
                case 'grade':
                    if (empty($groupValue)) return [];
                    $query->where('grade', $groupValue);
                    break;
                case 'verification':
                    if (empty($groupValue)) return [];
                    if ($groupValue === 'verified') {
                        $query->whereNotNull('email_verified_at');
                    } else {
                        $query->whereNull('email_verified_at');
                    }
                    break;
                case 'status':
                    if (empty($groupValue)) return [];
                    if ($groupValue === 'active') {
                        $query->where(function($q) {
                            $q->whereNull('suspended_until')
                              ->orWhere('suspended_until', '<', now());
                        });
                    } else {
                        $query->where('suspended_until', '>', now());
                    }
                    break;
                default:
                    return [];
            }
            return $query->whereNotNull('email')->get();
        }

        return [];
    }

    protected function replaceTemplateVariables($content, $user)
    {
        $replacements = [
            '{{USER_NAME}}' => $user->name ?? '',
            '{{USER_EMAIL}}' => $user->email ?? '',
            '{{SITE_NAME}}' => config('app.name', 'JinyPHP'),
            '{{SITE_URL}}' => config('app.url', 'http://localhost'),
        ];
        foreach ($replacements as $variable => $value) {
            $content = str_replace($variable, $value, $content);
        }
        return $content;
    }

    protected function generateEmailHtml($user, $message, $adminUser, $subject)
    {
        $appName = config('app.name', 'JinyPHP');
        $appUrl = config('app.url', 'http://localhost');
        $adminName = $adminUser->name ?? '시스템 관리자';
        $html = '<div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">';
        $html .= '<div style="background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">';
        $html .= '<div style="text-align: center; border-bottom: 3px solid #007bff; padding-bottom: 20px; margin-bottom: 30px;">';
        $html .= '<div style="font-size: 24px; font-weight: bold; color: #007bff; margin-bottom: 10px;">📧 ' . htmlspecialchars($appName) . '</div>';
        $html .= '<div style="color: #6c757d; font-size: 14px;">일괄 메일 발송</div>';
        $html .= '</div>';
        $html .= '<div style="font-size: 18px; color: #495057; margin-bottom: 20px;">';
        $html .= '안녕하세요 <strong>' . htmlspecialchars($user->name ?? $user->email) . '</strong>님,';
        $html .= '</div>';
        $html .= '<div style="background-color: #f8f9fa; border-left: 4px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 0 5px 5px 0; white-space: pre-wrap; word-wrap: break-word;">';
        $html .= htmlspecialchars($message);
        $html .= '</div>';
        $html .= '<div style="text-align: center; margin: 30px 0;">';
        $html .= '<a href="' . htmlspecialchars($appUrl) . '" style="display: inline-block; padding: 12px 25px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 10px 0; font-weight: bold;">사이트 방문하기</a>';
        $html .= '</div>';
        $html .= '<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; font-size: 14px;">';
        $html .= '<p>이 메일은 ' . htmlspecialchars($appName) . ' 관리자가 발송한 일괄 메시지입니다.</p>';
        $html .= '<p style="margin-top: 15px;">';
        $html .= '<strong>' . htmlspecialchars($appName) . '</strong><br>';
        $html .= '<a href="' . htmlspecialchars($appUrl) . '" style="color: #007bff;">' . htmlspecialchars($appUrl) . '</a>';
        $html .= '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    protected function recordMailLog(array $logData)
    {
        try {
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
                'is_bulk' => $logData['is_bulk'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('일괄 메일 발송 로그 기록 실패', [
                'error' => $e->getMessage(),
                'log_data' => $logData,
            ]);
        }
    }

    protected function createMailLogsTable()
    {
        DB::statement('CREATE TABLE IF NOT EXISTS user_mail_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
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
            is_bulk BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }

}


