<?php

namespace Jiny\Mail\Http\Controllers\Admin\MailLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Jiny\Mail\Models\AuthMailLog;
use Jiny\Admin\Mail\EmailMailable;

/**
 * MailLogs ResendController
 *
 * 실패한 메일을 재발송하는 기능을 제공합니다.
 */
class ResendController extends Controller
{
    /**
     * 메일 재발송
     */
    public function __invoke(Request $request, $id)
    {
        $mailLog = AuthMailLog::findOrFail($id);

        // 발송 가능한 상태인지 확인
        if ($mailLog->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => '실패한 메일만 재발송할 수 있습니다.'
            ], 400);
        }

        try {
            // Auth 메일 설정 불러오기
            $configPath = base_path('jiny/auth/config/mail.php');
            if (file_exists($configPath)) {
                $authMailConfig = include $configPath;
            } else {
                // 파일이 없으면 기본 config 사용
                $authMailConfig = config('admin.mail', [
                    'mailer' => 'smtp',
                    'host' => 'smtp.mailgun.org',
                    'port' => 587,
                    'username' => '',
                    'password' => '',
                    'encryption' => 'tls',
                    'from_address' => 'hello@example.com',
                    'from_name' => 'Example',
                ]);
            }

            // 런타임 메일 설정 적용
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

            // 메일 재발송
            Mail::to($mailLog->recipient_email)->send(new EmailMailable(
                $mailLog->subject,
                $mailLog->content ?: '메일 내용이 없습니다.',
                $authMailConfig['from_address'],
                $authMailConfig['from_name'],
                $mailLog->recipient_email
            ));

            // 새로운 로그 생성 (재발송 기록)
            $newLog = AuthMailLog::create([
                'type' => $mailLog->type,
                'status' => 'sent',
                'recipient_email' => $mailLog->recipient_email,
                'recipient_name' => $mailLog->recipient_name,
                'sender_email' => $authMailConfig['from_address'],
                'sender_name' => $authMailConfig['from_name'],
                'subject' => '[재발송] ' . $mailLog->subject,
                'content' => $mailLog->content,
                'user_id' => $mailLog->user_id,
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
                'attempts' => 1,
            ]);

            // 원본 로그에 재발송 정보 기록
            $mailLog->update([
                'attempts' => $mailLog->attempts + 1,
                'error_message' => '재발송 완료 (ID: ' . $newLog->id . ')',
            ]);

            return response()->json([
                'success' => true,
                'message' => '메일이 성공적으로 재발송되었습니다.',
                'new_log_id' => $newLog->id
            ]);

        } catch (\Exception $e) {
            // 재발송 실패 시 로그 업데이트
            $mailLog->update([
                'attempts' => $mailLog->attempts + 1,
                'error_message' => '재발송 실패: ' . $e->getMessage(),
            ]);

            \Log::error('메일 재발송 실패', [
                'log_id' => $mailLog->id,
                'exception' => $e->getMessage(),
                'recipient' => $mailLog->recipient_email
            ]);

            return response()->json([
                'success' => false,
                'message' => '메일 재발송에 실패했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}


