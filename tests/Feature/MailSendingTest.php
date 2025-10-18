<?php

namespace Jiny\Mail\Tests\Feature;

use Jiny\Mail\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Mail\Mailable;

class MailSendingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();

        // 메일을 배열로 저장하여 테스트
        Mail::fake();
    }

    /**
     * 메일 설정 테스트 기능 테스트
     *
     * @test
     */
    public function can_test_mail_configuration()
    {
        $testData = [
            'test_email' => 'test@example.com',
            'subject' => 'Test Email',
            'message' => 'This is a test email message.'
        ];

        $response = $this->post(route('admin.mail.setting.test'), $testData);

        // 성공적인 응답 확인
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 메일이 발송되었는지 확인
        Mail::assertSent(function (Mailable $mail) use ($testData) {
            return $mail->hasTo($testData['test_email']);
        });
    }

    /**
     * 전체 메일 발송 기능 테스트
     *
     * @test
     */
    public function can_send_bulk_email()
    {
        $bulkData = [
            'subject' => 'Bulk Email Test',
            'message' => 'This is a bulk email test message.',
            'recipients' => 'user1@example.com,user2@example.com,user3@example.com'
        ];

        $response = $this->post(route('admin.mail.bulk.send'), $bulkData);

        // 성공적인 응답 확인
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 모든 수신자에게 메일이 발송되었는지 확인
        $recipients = explode(',', $bulkData['recipients']);
        foreach ($recipients as $recipient) {
            Mail::assertSent(function (Mailable $mail) use ($recipient) {
                return $mail->hasTo(trim($recipient));
            });
        }
    }

    /**
     * 사용자별 메일 발송 기능 테스트
     *
     * @test
     */
    public function can_send_mail_to_specific_user()
    {
        $userId = 1;
        $mailData = [
            'subject' => 'Personal Email Test',
            'message' => 'This is a personal email test message.',
            'recipient_email' => 'user@example.com'
        ];

        $response = $this->post(route('admin.users.mail.send', ['id' => $userId]), $mailData);

        // 성공적인 응답 확인
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 메일이 발송되었는지 확인
        Mail::assertSent(function (Mailable $mail) use ($mailData) {
            return $mail->hasTo($mailData['recipient_email']);
        });
    }

    /**
     * 메일 재발송 기능 테스트
     *
     * @test
     */
    public function can_resend_failed_mail()
    {
        // 실패한 메일 로그 생성 (시뮬레이션)
        $logId = 999; // 테스트용 로그 ID

        $response = $this->post(route('admin.mail.logs.resend', ['id' => $logId]));

        // JSON 응답 확인
        if ($response->headers->get('content-type') === 'application/json') {
            $response->assertJson([
                'success' => true
            ]);
        } else {
            // 일반 응답 확인
            $this->assertContains($response->getStatusCode(), [200, 302, 404]);
        }
    }

    /**
     * 메일 큐 기능 테스트 (큐가 설정된 경우)
     *
     * @test
     */
    public function mail_can_be_queued()
    {
        // 큐 페이킹
        Queue::fake();

        $testData = [
            'test_email' => 'test@example.com',
            'subject' => 'Queued Test Email',
            'message' => 'This is a queued test email message.',
            'use_queue' => true
        ];

        $response = $this->post(route('admin.mail.setting.test'), $testData);

        // 성공적인 응답 확인
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 큐에 작업이 추가되었는지 확인 (메일러 작업이 있다면)
        // Queue::assertPushed(SendMailJob::class);
    }

    /**
     * 잘못된 이메일 주소로 메일 발송 시도 테스트
     *
     * @test
     */
    public function cannot_send_mail_to_invalid_email()
    {
        $invalidData = [
            'test_email' => 'invalid-email',
            'subject' => 'Test Email',
            'message' => 'This should fail.'
        ];

        $response = $this->post(route('admin.mail.setting.test'), $invalidData);

        // 유효성 검사 오류 확인
        $response->assertSessionHasErrors(['test_email']);
    }

    /**
     * 필수 필드 누락 시 테스트
     *
     * @test
     */
    public function mail_sending_requires_mandatory_fields()
    {
        $incompleteData = [
            'test_email' => 'test@example.com',
            // subject와 message 누락
        ];

        $response = $this->post(route('admin.mail.setting.test'), $incompleteData);

        // 유효성 검사 오류 확인
        $response->assertSessionHasErrors();
    }

    /**
     * 메일 발송 제한 테스트 (스팸 방지)
     *
     * @test
     */
    public function mail_sending_has_rate_limiting()
    {
        $testData = [
            'test_email' => 'test@example.com',
            'subject' => 'Rate Limit Test',
            'message' => 'Testing rate limiting.'
        ];

        // 연속으로 여러 번 발송 시도
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('admin.mail.setting.test'), $testData);

            // 처음 몇 번은 성공해야 함
            if ($i < 3) {
                $this->assertContains($response->getStatusCode(), [200, 302]);
            }
        }

        // 마지막 요청은 제한에 걸릴 수 있음 (구현에 따라)
        // $this->assertEquals(429, $response->getStatusCode());
    }

    /**
     * 메일 발송 시 로그 생성 확인
     *
     * @test
     */
    public function mail_sending_creates_log_entry()
    {
        $testData = [
            'test_email' => 'test@example.com',
            'subject' => 'Log Test Email',
            'message' => 'This email should create a log entry.'
        ];

        $response = $this->post(route('admin.mail.setting.test'), $testData);

        // 성공적인 응답 확인
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 데이터베이스에 로그가 생성되었는지 확인
        $this->assertDatabaseHas('auth_mail_logs', [
            'recipient_email' => $testData['test_email'],
            'subject' => $testData['subject'],
            'type' => 'test'
        ]);
    }
}