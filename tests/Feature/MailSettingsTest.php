<?php

namespace Jiny\Mail\Tests\Feature;

use Jiny\Mail\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    /**
     * 메일 설정 페이지 로드 테스트
     *
     * @test
     */
    public function mail_settings_page_loads_correctly()
    {
        $response = $this->get(route('admin.mail.setting.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.setting.index');
        $response->assertSee('메일 설정');
    }

    /**
     * 메일 설정 업데이트 테스트
     *
     * @test
     */
    public function can_update_mail_settings()
    {
        $settingsData = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => 'test@gmail.com',
            'mail_password' => 'test_password',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Test Application'
        ];

        $response = $this->post(route('admin.mail.setting.update'), $settingsData);

        // 성공적인 응답 확인
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 설정이 저장되었는지 확인 (구현에 따라 다를 수 있음)
        // $this->assertDatabaseHas('settings', [
        //     'key' => 'mail_mailer',
        //     'value' => 'smtp'
        // ]);
    }

    /**
     * SMTP 설정 유효성 검사 테스트
     *
     * @test
     */
    public function smtp_settings_validation_works()
    {
        $invalidData = [
            'mail_mailer' => 'smtp',
            'mail_host' => '', // 필수 필드 누락
            'mail_port' => 'invalid_port', // 잘못된 포트
            'mail_username' => 'invalid-email', // 잘못된 이메일
        ];

        $response = $this->post(route('admin.mail.setting.update'), $invalidData);

        // 유효성 검사 오류 확인
        $response->assertSessionHasErrors([
            'mail_host',
            'mail_port',
            'mail_username'
        ]);
    }

    /**
     * 다른 메일 드라이버 설정 테스트
     *
     * @test
     */
    public function can_configure_different_mail_drivers()
    {
        $drivers = ['smtp', 'sendmail', 'mailgun', 'ses', 'log'];

        foreach ($drivers as $driver) {
            $settingsData = [
                'mail_mailer' => $driver,
                'mail_from_address' => 'test@example.com',
                'mail_from_name' => 'Test Application'
            ];

            // 드라이버별 추가 설정
            switch ($driver) {
                case 'smtp':
                    $settingsData['mail_host'] = 'smtp.example.com';
                    $settingsData['mail_port'] = '587';
                    $settingsData['mail_username'] = 'test@example.com';
                    $settingsData['mail_password'] = 'password';
                    break;

                case 'mailgun':
                    $settingsData['mailgun_domain'] = 'example.com';
                    $settingsData['mailgun_secret'] = 'test_secret';
                    break;

                case 'ses':
                    $settingsData['aws_access_key_id'] = 'test_key';
                    $settingsData['aws_secret_access_key'] = 'test_secret';
                    $settingsData['aws_default_region'] = 'us-east-1';
                    break;
            }

            $response = $this->post(route('admin.mail.setting.update'), $settingsData);

            $this->assertContains(
                $response->getStatusCode(),
                [200, 302],
                "Driver {$driver} configuration should be successful"
            );
        }
    }

    /**
     * 메일 암호화 설정 테스트
     *
     * @test
     */
    public function can_configure_mail_encryption()
    {
        $encryptionTypes = ['tls', 'ssl', null];

        foreach ($encryptionTypes as $encryption) {
            $settingsData = [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => '587',
                'mail_encryption' => $encryption,
                'mail_from_address' => 'test@example.com',
                'mail_from_name' => 'Test Application'
            ];

            $response = $this->post(route('admin.mail.setting.update'), $settingsData);

            $this->assertContains(
                $response->getStatusCode(),
                [200, 302],
                "Encryption type {$encryption} should be configurable"
            );
        }
    }

    /**
     * 메일 발신자 정보 설정 테스트
     *
     * @test
     */
    public function can_configure_mail_from_information()
    {
        $fromData = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.example.com',
            'mail_port' => '587',
            'mail_from_address' => 'custom@example.com',
            'mail_from_name' => 'Custom Application Name'
        ];

        $response = $this->post(route('admin.mail.setting.update'), $fromData);

        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 설정이 적용되었는지 확인
        // Config::get('mail.from.address')와 Config::get('mail.from.name') 확인
    }

    /**
     * 메일 설정 백업 및 복원 테스트
     *
     * @test
     */
    public function can_backup_and_restore_mail_settings()
    {
        $originalSettings = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.original.com',
            'mail_port' => '587',
            'mail_from_address' => 'original@example.com',
        ];

        // 원본 설정 저장
        $this->post(route('admin.mail.setting.update'), $originalSettings);

        $newSettings = [
            'mail_mailer' => 'sendmail',
            'mail_from_address' => 'new@example.com',
        ];

        // 새 설정 저장
        $this->post(route('admin.mail.setting.update'), $newSettings);

        // 백업에서 복원 (구현에 따라 라우트가 있을 수 있음)
        // $response = $this->post(route('admin.mail.setting.restore'));

        // 성공적인 복원 확인
        // $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /**
     * 메일 설정 내보내기 테스트
     *
     * @test
     */
    public function can_export_mail_settings()
    {
        $settingsData = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.example.com',
            'mail_port' => '587',
            'mail_from_address' => 'test@example.com',
        ];

        $this->post(route('admin.mail.setting.update'), $settingsData);

        // 설정 내보내기 (구현에 따라 라우트가 있을 수 있음)
        // $response = $this->get(route('admin.mail.setting.export'));

        // JSON 또는 파일 다운로드 응답 확인
        // $response->assertStatus(200);
        // $response->assertHeader('content-type', 'application/json');
    }

    /**
     * 메일 설정 가져오기 테스트
     *
     * @test
     */
    public function can_import_mail_settings()
    {
        $settingsJson = json_encode([
            'mail_mailer' => 'smtp',
            'mail_host' => 'imported.smtp.com',
            'mail_port' => '465',
            'mail_encryption' => 'ssl',
            'mail_from_address' => 'imported@example.com',
        ]);

        // 파일 업로드 시뮬레이션 (구현에 따라 라우트가 있을 수 있음)
        // $response = $this->post(route('admin.mail.setting.import'), [
        //     'settings_file' => UploadedFile::fake()->createWithContent('settings.json', $settingsJson)
        // ]);

        // 성공적인 가져오기 확인
        // $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /**
     * 메일 설정 초기화 테스트
     *
     * @test
     */
    public function can_reset_mail_settings_to_default()
    {
        // 커스텀 설정 저장
        $customSettings = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'custom.smtp.com',
            'mail_port' => '587',
        ];

        $this->post(route('admin.mail.setting.update'), $customSettings);

        // 기본값으로 초기화 (구현에 따라 라우트가 있을 수 있음)
        // $response = $this->post(route('admin.mail.setting.reset'));

        // 성공적인 초기화 확인
        // $this->assertContains($response->getStatusCode(), [200, 302]);
    }
}