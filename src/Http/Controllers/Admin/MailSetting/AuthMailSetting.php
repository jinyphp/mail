<?php

namespace Jiny\Mail\Http\Controllers\Admin\MailSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Jiny\Admin\Mail\EmailMailable;
use Jiny\Mail\Models\AuthMailLog;
use Jiny\Mail\Facades\UserMail;

/**
 * AuthMailSetting Controller
 *
 * jiny-auth 전용 메일 설정 관리 및 테스트 메일 발송 기능을 제공합니다.
 */
class AuthMailSetting extends Controller
{
    private $route;

    public function __construct()
    {
        $this->route = 'admin.mail.setting.index';
    }

    /**
     * 메일 설정 페이지 표시
     */
    public function __invoke(Request $request)
    {
        // JSON 설정 파일만 사용
        // 없으면 기본 템플릿을 생성하여 초기화합니다.
        $jsonConfigPath = base_path('jiny/mail/config/mail.json');
        if (!file_exists($jsonConfigPath) || trim((string) @File::get($jsonConfigPath)) === '') {
            if (!file_exists(dirname($jsonConfigPath))) {
                mkdir(dirname($jsonConfigPath), 0755, true);
            }
            $default = [
                'mailer' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
                'from_address' => 'hello@example.com',
                'from_name' => 'Example',
            ];
            File::put($jsonConfigPath, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // JSON 읽기
        $jsonString = File::get($jsonConfigPath);
        $mailSettings = json_decode($jsonString, true);
        if (!is_array($mailSettings)) {
            $mailSettings = [];
        }

        return view('jiny-mail::admin.mail.setting.index', [
            'mailSettings' => $mailSettings,
            'route' => $this->route,
            'title' => 'Auth 메일 설정',
            'subtitle' => 'jiny-auth 전용 SMTP 메일 서버 설정을 관리합니다',
        ]);
    }

    /**
     * 메일 설정 저장
     */
    public function update(Request $request)
    {
        $request->validate([
            'mailer' => 'required|string',
            'host' => 'required_if:mailer,smtp|nullable|string',
            'port' => 'required_if:mailer,smtp|nullable|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string|in:tls,ssl,null',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        // 입력값 정규화
        // - SMTP가 아닌 경우 port는 null 유지
        // - encryption은 'null' 또는 빈 값일 때 실제 null로 저장
        $mailer = $request->input('mailer');
        $inputPort = $request->input('port');
        $normalizedPort = $mailer === 'smtp' ? (int) $inputPort : null;
        $encryptionInput = $request->input('encryption');
        $normalizedEncryption = ($encryptionInput === 'null' || $encryptionInput === null || $encryptionInput === '')
            ? null
            : $encryptionInput;

        $data = [
            'mailer' => $mailer,
            'host' => $request->input('host'),
            'port' => $normalizedPort,
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'encryption' => $normalizedEncryption,
            'from_address' => $request->input('from_address'),
            'from_name' => $request->input('from_name'),
        ];

        // JSON 설정 파일로 저장
        $configPath = base_path('jiny/mail/config/mail.json');

        // 디렉토리가 없으면 생성
        if (!file_exists(dirname($configPath))) {
            mkdir(dirname($configPath), 0755, true);
        }

        // JSON으로 직렬화하여 저장
        // 주의: JSON은 주석을 허용하지 않으므로 메타 설명은 코드 주석으로만 남깁니다.
        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        File::put($configPath, $content);

        // 설정 캐시 클리어 (옵션)
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response()->json([
            'success' => true,
            'message' => 'Auth 메일 설정이 저장되었습니다.'
        ]);
    }

    /**
     * 메일 설정 테스트
     */
    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        $testEmail = $request->input('test_email');

        // JSON 설정만 사용. 없으면 기본 템플릿을 생성하여 초기화
        $jsonConfigPath = base_path('jiny/mail/config/mail.json');
        if (!file_exists($jsonConfigPath) || trim((string) @File::get($jsonConfigPath)) === '') {
            if (!file_exists(dirname($jsonConfigPath))) {
                mkdir(dirname($jsonConfigPath), 0755, true);
            }
            $default = [
                'mailer' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
                'from_address' => 'hello@example.com',
                'from_name' => 'Example',
            ];
            File::put($jsonConfigPath, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $jsonString = File::get($jsonConfigPath);
        $authMailConfig = json_decode($jsonString, true) ?: [];

        // UserMail 파사드를 통해 런타임 메일 설정 적용
        // - .env가 아닌 JSON 설정값을 그대로 적용합니다.
        UserMail::applyConfig($authMailConfig);

        // 메일 로그 기록 준비
        $subject = '[Jiny-Auth 테스트] 인증 메일 설정 테스트';
        $content = $this->getTestEmailContent($authMailConfig);

        // 메일 로그 초기 생성
        $mailLog = AuthMailLog::create([
            'type' => AuthMailLog::TYPE_TEST,
            'status' => AuthMailLog::STATUS_PENDING,
            'recipient_email' => $testEmail,
            'recipient_name' => null,
            'sender_email' => $authMailConfig['from_address'],
            'sender_name' => $authMailConfig['from_name'],
            'subject' => $subject,
            'content' => $content,
            'user_id' => auth()->id(),
            'user_agent' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
            'attempts' => 1,
        ]);

        // UserMail 파사드로 테스트 메일 발송
        $result = UserMail::sendByHtml($testEmail, $subject, $content, null, $authMailConfig);

        if ($result['success'] ?? false) {
            // 발송 성공 시 로그 업데이트
            $mailLog->update([
                'status' => AuthMailLog::STATUS_SENT,
            ]);

            return response()->json([
                'success' => true,
                'message' => "테스트 이메일이 {$testEmail}로 발송되었습니다. 수신함을 확인해주세요."
            ]);
        }

        // 발송 실패 시 로그 업데이트
        $errorMessage = $result['message'] ?? 'Unknown error';
        $mailLog->update([
            'status' => AuthMailLog::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);

        \Log::error('Auth 메일 테스트 실패: ' . $errorMessage, [
            'auth_mail_config' => $authMailConfig,
            'test_email' => $testEmail,
            'mail_log_id' => $mailLog->id
        ]);

        return response()->json([
            'success' => false,
            'message' => '테스트 이메일 발송 실패: ' . $errorMessage
        ], 500);
    }

    /**
     * 테스트 이메일 내용 생성
     */
    private function getTestEmailContent($config)
    {
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f5f5f5;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';

        // Jiny-Auth 전용 헤더
        $html .= '<div style="text-align: center; margin-bottom: 30px;">';
        $html .= '<h1 style="color: #2563eb; margin: 0; font-size: 28px;">🔐 Jiny-Auth</h1>';
        $html .= '<p style="color: #64748b; margin: 5px 0 0 0; font-size: 14px;">Authentication System</p>';
        $html .= '</div>';

        $html .= '<h2 style="color: #333; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">메일 설정 테스트</h2>';
        $html .= '<p style="color: #666; line-height: 1.6;">이것은 Jiny-Auth 인증 시스템의 메일 설정 테스트 이메일입니다.</p>';

        $html .= '<div style="background-color: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2563eb;">';
        $html .= '<h3 style="color: #1e40af; margin-top: 0; margin-bottom: 15px;">📧 설정 정보</h3>';
        $html .= '<table style="width: 100%; color: #374151; font-size: 14px; line-height: 1.6;">';
        $html .= '<tr><td style="padding: 4px 0; font-weight: bold; width: 120px;">발송 시간:</td><td>' . now()->format('Y-m-d H:i:s') . '</td></tr>';
        $html .= '<tr><td style="padding: 4px 0; font-weight: bold;">메일 드라이버:</td><td>' . ($config['mailer'] ?? 'unknown') . '</td></tr>';
        $html .= '<tr><td style="padding: 4px 0; font-weight: bold;">발신자:</td><td>' . ($config['from_address'] ?? 'unknown') . '</td></tr>';
        $html .= '<tr><td style="padding: 4px 0; font-weight: bold;">발신자명:</td><td>' . ($config['from_name'] ?? 'unknown') . '</td></tr>';

        if ($config['mailer'] === 'smtp') {
            $html .= '<tr><td style="padding: 4px 0; font-weight: bold;">SMTP 호스트:</td><td>' . ($config['host'] ?? 'unknown') . '</td></tr>';
            $html .= '<tr><td style="padding: 4px 0; font-weight: bold;">SMTP 포트:</td><td>' . ($config['port'] ?? 'unknown') . '</td></tr>';
            $html .= '<tr><td style="padding: 4px 0; font-weight: bold;">암호화:</td><td>' . ($config['encryption'] ?? 'none') . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        // 성공 메시지
        $html .= '<div style="background-color: #f0fdf4; padding: 20px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #16a34a;">';
        $html .= '<p style="color: #15803d; margin: 0; font-weight: bold;"><span style="font-size: 18px;">✅</span> 테스트 성공!</p>';
        $html .= '<p style="color: #16a34a; margin: 8px 0 0 0; font-size: 14px;">이 메일이 정상적으로 수신되면 Jiny-Auth 메일 설정이 올바르게 작동하고 있습니다.</p>';
        $html .= '</div>';

        // 인증 관련 기능 안내
        $html .= '<div style="background-color: #fefce8; padding: 20px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #eab308;">';
        $html .= '<h4 style="color: #a16207; margin: 0 0 10px 0;">📋 이 설정으로 사용 가능한 기능</h4>';
        $html .= '<ul style="color: #92400e; margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.6;">';
        $html .= '<li>회원가입 이메일 인증</li>';
        $html .= '<li>비밀번호 재설정 이메일</li>';
        $html .= '<li>계정 변경 알림 이메일</li>';
        $html .= '<li>보안 알림 이메일</li>';
        $html .= '</ul>';
        $html .= '</div>';

        // 푸터
        $html .= '<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">';
        $html .= '<p style="color: #9ca3af; font-size: 12px; margin: 0;">© ' . date('Y') . ' Jiny-Auth. 이 이메일은 자동으로 발송되었습니다.</p>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}


