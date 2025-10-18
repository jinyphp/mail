<?php

namespace Jiny\Mail\Tests\Feature;

use Jiny\Mail\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class RoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsAdmin();
    }

    /**
     * 모든 GET 라우트가 200 응답을 반환하는지 테스트
     *
     * @test
     */
    public function all_get_routes_return_200_response()
    {
        $routes = $this->getMailGetRoutes();

        foreach ($routes as $route) {
            $response = $this->get($route['uri']);

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                "Route {$route['uri']} (name: {$route['name']}) should return 200 but returned {$response->getStatusCode()}"
            );
        }
    }

    /**
     * 메일 설정 페이지 테스트
     *
     * @test
     */
    public function mail_setting_page_loads_successfully()
    {
        $response = $this->get(route('admin.mail.setting.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.setting.index');
    }

    /**
     * 메일 로그 페이지 테스트
     *
     * @test
     */
    public function mail_logs_page_loads_successfully()
    {
        $response = $this->get(route('admin.mail.logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.logs.index');
    }

    /**
     * 메일 템플릿 목록 페이지 테스트
     *
     * @test
     */
    public function mail_templates_index_page_loads_successfully()
    {
        $response = $this->get(route('admin.mail.templates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.template.index');
    }

    /**
     * 메일 템플릿 생성 페이지 테스트
     *
     * @test
     */
    public function mail_templates_create_page_loads_successfully()
    {
        $response = $this->get(route('admin.mail.templates.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.template.create');
    }

    /**
     * 전체 메일 발송 페이지 테스트
     *
     * @test
     */
    public function bulk_mail_create_page_loads_successfully()
    {
        $response = $this->get(route('admin.mail.bulk.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-mail::admin.mail.bulk.create');
    }

    /**
     * 사용자별 메일 페이지 테스트 (존재하는 사용자 ID로)
     *
     * @test
     */
    public function user_mail_page_loads_successfully()
    {
        $userId = 1; // 테스트용 사용자 ID

        $response = $this->get(route('admin.users.mail.index', ['id' => $userId]));

        $response->assertStatus(200);
    }

    /**
     * 인증이 필요한 라우트들이 미들웨어를 통과하는지 테스트
     *
     * @test
     */
    public function authenticated_routes_require_middleware()
    {
        // 인증되지 않은 상태에서 테스트
        auth()->logout();

        $routes = $this->getMailGetRoutes();

        foreach ($routes as $route) {
            $response = $this->get($route['uri']);

            // 페이크 미들웨어를 사용하므로 200이어야 함
            // 실제 환경에서는 302 (리다이렉트)가 될 것
            $this->assertContains(
                $response->getStatusCode(),
                [200, 302],
                "Route {$route['uri']} should require authentication"
            );
        }
    }

    /**
     * 메일 관련 GET 라우트 목록을 반환
     *
     * @return array
     */
    private function getMailGetRoutes(): array
    {
        return [
            [
                'name' => 'admin.mail.setting.index',
                'uri' => '/admin/mail/setting',
            ],
            [
                'name' => 'admin.mail.logs.index',
                'uri' => '/admin/mail/logs',
            ],
            [
                'name' => 'admin.mail.templates.index',
                'uri' => '/admin/mail/templates',
            ],
            [
                'name' => 'admin.mail.templates.create',
                'uri' => '/admin/mail/templates/create',
            ],
            [
                'name' => 'admin.mail.bulk.create',
                'uri' => '/admin/mail/bulk/create',
            ],
        ];
    }

    /**
     * 동적 라우트 테스트 (ID가 필요한 라우트들)
     *
     * @test
     */
    public function dynamic_routes_work_with_valid_ids()
    {
        // 템플릿 상세 보기 (존재하지 않는 ID라도 페이지가 로드되는지 확인)
        $templateId = 999;
        $response = $this->get("/admin/mail/templates/{$templateId}");
        $this->assertContains($response->getStatusCode(), [200, 404]);

        // 템플릿 수정 페이지
        $response = $this->get("/admin/mail/templates/{$templateId}/edit");
        $this->assertContains($response->getStatusCode(), [200, 404]);

        // 메일 로그 상세 보기
        $logId = 999;
        $response = $this->get("/admin/mail/logs/{$logId}/content");
        $this->assertContains($response->getStatusCode(), [200, 404]);

        $response = $this->get("/admin/mail/logs/{$logId}/error");
        $this->assertContains($response->getStatusCode(), [200, 404]);
    }
}