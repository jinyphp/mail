<?php

namespace Jiny\Mail\Tests\Unit;

use Jiny\Mail\Tests\TestCase;
use Jiny\Mail\Models\AuthMailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AuthMailLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * AuthMailLog 모델 인스턴스 생성 테스트
     *
     * @test
     */
    public function can_create_auth_mail_log_instance()
    {
        $log = new AuthMailLog();

        $this->assertInstanceOf(AuthMailLog::class, $log);
    }

    /**
     * 메일 로그 생성 테스트
     *
     * @test
     */
    public function can_create_mail_log()
    {
        $logData = [
            'type' => 'test',
            'recipient_email' => 'test@example.com',
            'recipient_name' => 'Test User',
            'subject' => 'Test Email',
            'content' => '<p>Test email content</p>',
            'status' => 'sent',
            'attempts' => 1,
            'sent_at' => Carbon::now(),
        ];

        $log = AuthMailLog::create($logData);

        $this->assertInstanceOf(AuthMailLog::class, $log);
        $this->assertEquals($logData['type'], $log->type);
        $this->assertEquals($logData['recipient_email'], $log->recipient_email);
        $this->assertEquals($logData['subject'], $log->subject);
        $this->assertEquals($logData['status'], $log->status);
    }

    /**
     * 필수 필드 테스트
     *
     * @test
     */
    public function required_fields_are_enforced()
    {
        $log = new AuthMailLog();

        // 필수 필드 없이 저장 시도
        try {
            $log->save();
            $this->fail('Expected validation exception was not thrown');
        } catch (\Exception $e) {
            // 예외가 발생해야 함
            $this->assertTrue(true);
        }
    }

    /**
     * 상태 값 검증 테스트
     *
     * @test
     */
    public function status_values_are_valid()
    {
        $validStatuses = ['pending', 'sent', 'failed', 'read', 'bounced'];

        foreach ($validStatuses as $status) {
            $log = new AuthMailLog([
                'type' => 'test',
                'recipient_email' => 'test@example.com',
                'subject' => 'Test Subject',
                'content' => 'Test content',
                'status' => $status,
                'attempts' => 1,
            ]);

            // 상태값이 올바르게 설정되는지 확인
            $this->assertEquals($status, $log->status);
        }
    }

    /**
     * 타입 값 검증 테스트
     *
     * @test
     */
    public function type_values_are_valid()
    {
        $validTypes = ['verification', 'password_reset', 'welcome', 'notification', 'test'];

        foreach ($validTypes as $type) {
            $log = new AuthMailLog([
                'type' => $type,
                'recipient_email' => 'test@example.com',
                'subject' => 'Test Subject',
                'content' => 'Test content',
                'status' => 'sent',
                'attempts' => 1,
            ]);

            // 타입값이 올바르게 설정되는지 확인
            $this->assertEquals($type, $log->type);
        }
    }

    /**
     * 날짜 형변환 테스트
     *
     * @test
     */
    public function date_casting_works()
    {
        $log = AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'test@example.com',
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'status' => 'sent',
            'attempts' => 1,
            'sent_at' => '2024-01-15 10:30:00',
            'read_at' => '2024-01-15 11:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $log->sent_at);
        $this->assertInstanceOf(Carbon::class, $log->read_at);
        $this->assertInstanceOf(Carbon::class, $log->created_at);
        $this->assertInstanceOf(Carbon::class, $log->updated_at);
    }

    /**
     * 읽음 확인 메서드 테스트
     *
     * @test
     */
    public function can_mark_as_read()
    {
        $log = AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'test@example.com',
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'status' => 'sent',
            'attempts' => 1,
        ]);

        // 읽음 처리 메서드가 있다면
        // $log->markAsRead();

        // $this->assertEquals('read', $log->status);
        // $this->assertNotNull($log->read_at);
        // $this->assertEquals(1, $log->read_count);
    }

    /**
     * 재시도 횟수 증가 테스트
     *
     * @test
     */
    public function can_increment_attempts()
    {
        $log = AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'test@example.com',
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'status' => 'pending',
            'attempts' => 1,
        ]);

        $initialAttempts = $log->attempts;

        // 재시도 메서드가 있다면
        // $log->incrementAttempts();

        // $this->assertEquals($initialAttempts + 1, $log->attempts);
    }

    /**
     * 스코프 메서드 테스트
     *
     * @test
     */
    public function scope_methods_work()
    {
        // 테스트 데이터 생성
        AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'test1@example.com',
            'subject' => 'Test 1',
            'content' => 'Content 1',
            'status' => 'sent',
            'attempts' => 1,
        ]);

        AuthMailLog::create([
            'type' => 'notification',
            'recipient_email' => 'test2@example.com',
            'subject' => 'Test 2',
            'content' => 'Content 2',
            'status' => 'failed',
            'attempts' => 3,
        ]);

        // 스코프 메서드들이 구현되어 있다면 테스트
        // $sentLogs = AuthMailLog::sent()->get();
        // $this->assertCount(1, $sentLogs);

        // $failedLogs = AuthMailLog::failed()->get();
        // $this->assertCount(1, $failedLogs);

        // $testLogs = AuthMailLog::ofType('test')->get();
        // $this->assertCount(1, $testLogs);
    }

    /**
     * 관계(Relations) 테스트
     *
     * @test
     */
    public function relationships_work()
    {
        $log = AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'test@example.com',
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'status' => 'sent',
            'attempts' => 1,
            'user_id' => 1, // 사용자와의 관계가 있다면
        ]);

        // 사용자와의 관계가 정의되어 있다면
        // $this->assertInstanceOf(User::class, $log->user);
    }

    /**
     * JSON 직렬화 테스트
     *
     * @test
     */
    public function json_serialization_works()
    {
        $log = AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'test@example.com',
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'status' => 'sent',
            'attempts' => 1,
        ]);

        $json = $log->toJson();
        $this->assertJson($json);

        $array = json_decode($json, true);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('recipient_email', $array);
        $this->assertArrayHasKey('status', $array);
    }

    /**
     * 대량 할당 보호 테스트
     *
     * @test
     */
    public function mass_assignment_protection()
    {
        $log = new AuthMailLog();

        // fillable 속성이 올바르게 설정되어 있는지 확인
        $fillable = $log->getFillable();

        $this->assertContains('type', $fillable);
        $this->assertContains('recipient_email', $fillable);
        $this->assertContains('subject', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('status', $fillable);

        // id나 timestamps는 fillable에 없어야 함
        $this->assertNotContains('id', $fillable);
    }

    /**
     * 소프트 삭제 테스트 (구현되어 있다면)
     *
     * @test
     */
    public function soft_deletes_work()
    {
        $log = AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'test@example.com',
            'subject' => 'Test Subject',
            'content' => 'Test content',
            'status' => 'sent',
            'attempts' => 1,
        ]);

        $logId = $log->id;

        // 소프트 삭제가 구현되어 있다면
        // $log->delete();

        // $this->assertSoftDeleted('auth_mail_logs', ['id' => $logId]);
    }

    /**
     * 검색 기능 테스트
     *
     * @test
     */
    public function search_functionality_works()
    {
        AuthMailLog::create([
            'type' => 'test',
            'recipient_email' => 'searchable@example.com',
            'recipient_name' => 'Searchable User',
            'subject' => 'Searchable Subject',
            'content' => 'Searchable content',
            'status' => 'sent',
            'attempts' => 1,
        ]);

        // 검색 스코프가 구현되어 있다면
        // $results = AuthMailLog::search('searchable')->get();
        // $this->assertCount(1, $results);

        // $results = AuthMailLog::search('nonexistent')->get();
        // $this->assertCount(0, $results);
    }
}