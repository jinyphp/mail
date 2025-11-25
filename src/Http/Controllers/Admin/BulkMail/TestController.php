<?php

namespace Jiny\Mail\Http\Controllers\Admin\BulkMail;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Mail\Facades\UserMail;

/**
 * 테스트 메일 발송 컨트롤러
 *
 * 실제 일괄 발송 전에 관리자 이메일로 테스트 메일을 발송합니다.
 */
class TestController extends Controller
{
    /**
     * 테스트 메일 발송
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'test_email' => 'required|email',
        ]);

        $subject = $request->input('subject');
        $message = $request->input('message');
        $testEmail = $request->input('test_email');

        // 현재 로그인한 관리자 정보
        $adminUser = auth()->user();
        $adminName = $adminUser->name ?? 'Admin';

        try {
            // UserMail 파사드를 통해 설정 로드 및 적용
            $authMailConfig = UserMail::loadConfig();
            UserMail::applyConfig($authMailConfig);

            // 테스트용 가상 사용자 데이터 생성
            $testUser = (object) [
                'name' => '테스트 사용자',
                'email' => $testEmail,
            ];

            // 템플릿 변수 치환
            $processedSubject = $this->replaceTemplateVariables($subject, $testUser);
            $processedMessage = $this->replaceTemplateVariables($message, $testUser);

            // HTML 이메일 내용 생성
            $htmlContent = $this->generateTestEmailHtml($testUser, $processedMessage, $adminUser, $processedSubject);

            \Log::info('테스트 메일 발송 시도', [
                'admin' => $adminName,
                'test_email' => $testEmail,
                'subject' => $processedSubject,
            ]);

            // UserMail 파사드를 통해 메일 발송
            $result = UserMail::sendByHtml(
                $testEmail,
                $processedSubject,
                $htmlContent,
                $testUser->name,
                $authMailConfig
            );

            if ($result['success'] ?? false) {
                \Log::info('테스트 메일 발송 성공', [
                    'test_email' => $testEmail,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "테스트 메일이 {$testEmail}로 발송되었습니다. 메일함을 확인해주세요."
                ]);
            }

            throw new \Exception($result['message'] ?? '메일 발송 실패');

        } catch (\Exception $e) {
            \Log::error('테스트 메일 발송 실패', [
                'error' => $e->getMessage(),
                'test_email' => $testEmail,
            ]);

            return response()->json([
                'success' => false,
                'message' => '테스트 메일 발송에 실패했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 템플릿 변수 치환
     */
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

    /**
     * 테스트 이메일 HTML 생성
     */
    protected function generateTestEmailHtml($user, $message, $adminUser, $subject)
    {
        $appName = config('app.name', 'JinyPHP');
        $appUrl = config('app.url', 'http://localhost');
        $adminName = $adminUser->name ?? '시스템 관리자';

        $html = '<div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">';
        
        // 테스트 배너
        $html .= '<div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 15px; margin-bottom: 20px; text-align: center;">';
        $html .= '<strong style="color: #856404;">🧪 테스트 메일</strong>';
        $html .= '<div style="font-size: 13px; color: #856404; margin-top: 5px;">이것은 실제 발송 전 미리보기용 테스트 메일입니다.</div>';
        $html .= '</div>';

        $html .= '<div style="background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">';
        $html .= '<div style="text-align: center; border-bottom: 3px solid #007bff; padding-bottom: 20px; margin-bottom: 30px;">';
        $html .= '<div style="font-size: 24px; font-weight: bold; color: #007bff; margin-bottom: 10px;">📧 ' . htmlspecialchars($appName) . '</div>';
        $html .= '<div style="color: #6c757d; font-size: 14px;">일괄 메일 발송 (테스트)</div>';
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
}
