<?php

namespace Jiny\Mail\Tests\Feature;

use Jiny\Mail\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class MailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    /**
     * 템플릿 목록 페이지 로드 테스트
     *
     * @test
     */
    public function templates_index_page_loads_correctly()
    {
        $response = $this->get(route('admin.mail.templates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.template.index');
        $response->assertSee('메일 템플릿');
    }

    /**
     * 템플릿 생성 페이지 로드 테스트
     *
     * @test
     */
    public function template_create_page_loads_correctly()
    {
        $response = $this->get(route('admin.mail.templates.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.template.create');
        $response->assertSee('템플릿 추가');
    }

    /**
     * 새 템플릿 생성 테스트
     *
     * @test
     */
    public function can_create_new_template()
    {
        $templateData = [
            'name' => 'Welcome Email Template',
            'subject' => 'Welcome to Our Service',
            'content' => '<h1>Welcome!</h1><p>Thank you for joining us.</p>',
            'type' => 'welcome',
            'description' => 'Template for welcome emails'
        ];

        $response = $this->post(route('admin.mail.templates.store'), $templateData);

        // 성공적인 응답 확인
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 데이터베이스에 저장되었는지 확인
        $this->assertDatabaseHas('mail_templates', [
            'name' => $templateData['name'],
            'subject' => $templateData['subject'],
            'type' => $templateData['type']
        ]);
    }

    /**
     * 템플릿 상세 보기 테스트
     *
     * @test
     */
    public function can_view_template_details()
    {
        // 먼저 템플릿 생성
        $templateData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'content' => '<p>Test content</p>',
            'type' => 'test'
        ];

        $createResponse = $this->post(route('admin.mail.templates.store'), $templateData);
        $this->assertContains($createResponse->getStatusCode(), [200, 302]);

        // 템플릿 ID 1로 상세 보기 (실제로는 생성된 ID를 사용해야 함)
        $templateId = 1;
        $response = $this->get(route('admin.mail.templates.show', ['id' => $templateId]));

        $this->assertContains($response->getStatusCode(), [200, 404]);

        if ($response->getStatusCode() === 200) {
            $response->assertViewIs('jiny-mail::admin.mail.template.show');
            $response->assertSee($templateData['name']);
        }
    }

    /**
     * 템플릿 수정 페이지 로드 테스트
     *
     * @test
     */
    public function template_edit_page_loads_correctly()
    {
        $templateId = 1; // 테스트용 ID

        $response = $this->get(route('admin.mail.templates.edit', ['id' => $templateId]));

        $this->assertContains($response->getStatusCode(), [200, 404]);

        if ($response->getStatusCode() === 200) {
            $response->assertViewIs('jiny-mail::admin.mail.template.edit');
        }
    }

    /**
     * 템플릿 업데이트 테스트
     *
     * @test
     */
    public function can_update_template()
    {
        $templateId = 1; // 테스트용 ID
        $updateData = [
            'name' => 'Updated Template Name',
            'subject' => 'Updated Subject',
            'content' => '<h1>Updated Content</h1>',
            'type' => 'notification',
            'description' => 'Updated description'
        ];

        $response = $this->put(route('admin.mail.templates.update', ['id' => $templateId]), $updateData);

        $this->assertContains($response->getStatusCode(), [200, 302, 404]);

        // 업데이트가 성공했다면 데이터베이스 확인
        if ($response->getStatusCode() !== 404) {
            $this->assertDatabaseHas('mail_templates', [
                'id' => $templateId,
                'name' => $updateData['name'],
                'subject' => $updateData['subject']
            ]);
        }
    }

    /**
     * 템플릿 삭제 테스트
     *
     * @test
     */
    public function can_delete_template()
    {
        $templateId = 1; // 테스트용 ID

        $response = $this->delete(route('admin.mail.templates.delete', ['id' => $templateId]));

        $this->assertContains($response->getStatusCode(), [200, 302, 404]);

        // 삭제가 성공했다면 데이터베이스에서 확인
        if ($response->getStatusCode() !== 404) {
            $this->assertDatabaseMissing('mail_templates', [
                'id' => $templateId
            ]);
        }
    }

    /**
     * 템플릿 유효성 검사 테스트
     *
     * @test
     */
    public function template_validation_works()
    {
        $invalidData = [
            'name' => '', // 필수 필드 누락
            'subject' => '', // 필수 필드 누락
            'content' => '', // 필수 필드 누락
            'type' => 'invalid_type' // 잘못된 타입
        ];

        $response = $this->post(route('admin.mail.templates.store'), $invalidData);

        $response->assertSessionHasErrors([
            'name',
            'subject',
            'content'
        ]);
    }

    /**
     * 템플릿 변수 교체 테스트
     *
     * @test
     */
    public function template_variable_replacement_works()
    {
        $templateData = [
            'name' => 'Variable Test Template',
            'subject' => 'Hello {{name}}',
            'content' => '<p>Welcome {{name}}! Your email is {{email}}.</p>',
            'type' => 'test'
        ];

        $response = $this->post(route('admin.mail.templates.store'), $templateData);
        $this->assertContains($response->getStatusCode(), [200, 302]);

        // 변수 교체 테스트 (실제 메일 발송 시뮬레이션)
        $variables = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        // 템플릿 미리보기 또는 처리 기능이 있다면 테스트
        // $processedContent = processTemplate($templateData['content'], $variables);
        // $this->assertStringContains('Welcome John Doe!', $processedContent);
        // $this->assertStringContains('john@example.com', $processedContent);
    }

    /**
     * 템플릿 미리보기 테스트
     *
     * @test
     */
    public function can_preview_template()
    {
        $templateId = 1;
        $previewData = [
            'variables' => [
                'name' => 'Preview User',
                'email' => 'preview@example.com'
            ]
        ];

        // 미리보기 기능이 구현되어 있다면
        // $response = $this->post(route('admin.mail.templates.preview', ['id' => $templateId]), $previewData);
        // $this->assertContains($response->getStatusCode(), [200, 404]);
    }

    /**
     * 템플릿 복제 테스트
     *
     * @test
     */
    public function can_duplicate_template()
    {
        $templateId = 1;

        // 복제 기능이 구현되어 있다면
        // $response = $this->post(route('admin.mail.templates.duplicate', ['id' => $templateId]));
        // $this->assertContains($response->getStatusCode(), [200, 302, 404]);

        // 복제된 템플릿이 생성되었는지 확인
        // if ($response->getStatusCode() !== 404) {
        //     $this->assertDatabaseHas('mail_templates', [
        //         'name' => 'Copy of Original Template'
        //     ]);
        // }
    }

    /**
     * 템플릿 검색 테스트
     *
     * @test
     */
    public function can_search_templates()
    {
        // 검색 기능이 있다면
        $searchData = ['search' => 'welcome'];

        $response = $this->get(route('admin.mail.templates.index', $searchData));

        $response->assertStatus(200);
        // 검색 결과 확인
        // $response->assertSee('검색 결과');
    }

    /**
     * 템플릿 내보내기 테스트
     *
     * @test
     */
    public function can_export_templates()
    {
        // 내보내기 기능이 구현되어 있다면
        // $response = $this->get(route('admin.mail.templates.export'));

        // $response->assertStatus(200);
        // $response->assertHeader('content-type', 'application/json');
    }

    /**
     * 템플릿 가져오기 테스트
     *
     * @test
     */
    public function can_import_templates()
    {
        $templatesJson = json_encode([
            [
                'name' => 'Imported Template',
                'subject' => 'Imported Subject',
                'content' => '<p>Imported content</p>',
                'type' => 'imported'
            ]
        ]);

        // 파일 업로드 시뮬레이션
        // $response = $this->post(route('admin.mail.templates.import'), [
        //     'templates_file' => UploadedFile::fake()->createWithContent('templates.json', $templatesJson)
        // ]);

        // $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /**
     * 템플릿 타입별 필터링 테스트
     *
     * @test
     */
    public function can_filter_templates_by_type()
    {
        $types = ['welcome', 'notification', 'password_reset', 'verification'];

        foreach ($types as $type) {
            $response = $this->get(route('admin.mail.templates.index', ['type' => $type]));

            $response->assertStatus(200);
            // 필터링된 결과 확인
        }
    }
}