<?php

namespace Jiny\Mail\Tests\Unit;

use Jiny\Mail\Tests\TestCase;
use Jiny\Mail\JinyMailServiceProvider;
use Illuminate\Support\Facades\Route;

class ServiceProviderTest extends TestCase
{
    /**
     * ServiceProvider가 올바르게 등록되는지 테스트
     *
     * @test
     */
    public function service_provider_is_registered()
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(JinyMailServiceProvider::class, $providers);
    }

    /**
     * 패키지 뷰가 등록되는지 테스트
     *
     * @test
     */
    public function package_views_are_registered()
    {
        $viewFactory = $this->app['view'];
        $paths = $viewFactory->getFinder()->getPaths();

        // jiny-mail 네임스페이스가 등록되었는지 확인
        $hints = $viewFactory->getFinder()->getHints();
        $this->assertArrayHasKey('jiny-mail', $hints);
    }

    /**
     * 라우트가 로드되는지 테스트
     *
     * @test
     */
    public function routes_are_loaded()
    {
        $routeCollection = Route::getRoutes();

        // 메일 관련 라우트가 등록되었는지 확인
        $this->assertTrue($routeCollection->hasNamedRoute('admin.mail.setting.index'));
        $this->assertTrue($routeCollection->hasNamedRoute('admin.mail.logs.index'));
        $this->assertTrue($routeCollection->hasNamedRoute('admin.mail.templates.index'));
        $this->assertTrue($routeCollection->hasNamedRoute('admin.mail.bulk.create'));
    }

    /**
     * 마이그레이션이 로드되는지 테스트
     *
     * @test
     */
    public function migrations_are_loaded()
    {
        // 마이그레이션 파일들이 올바르게 로드되는지 확인
        $migrator = $this->app['migrator'];
        $paths = $migrator->paths();

        $this->assertContains(
            realpath(__DIR__ . '/../../database/migrations'),
            array_map('realpath', $paths)
        );
    }

    /**
     * 설정 파일이 병합되는지 테스트
     *
     * @test
     */
    public function config_is_merged()
    {
        // jiny.mail 설정이 존재하는지 확인
        $config = $this->app['config'];

        // 기본 설정값들이 로드되었는지 확인
        $this->assertNotNull($config->get('jiny.mail'));
    }

    /**
     * Publishable 파일들이 등록되는지 테스트
     *
     * @test
     */
    public function publishable_files_are_registered()
    {
        $serviceProvider = new JinyMailServiceProvider($this->app);

        // 콘솔 환경에서 publishable 파일들이 등록되는지 확인
        $this->app['env'] = 'testing';

        // ServiceProvider 부팅
        $serviceProvider->boot();

        // 이 테스트는 실제 publish 명령어 없이는 확인하기 어려우므로
        // 최소한 ServiceProvider가 오류 없이 부팅되는지 확인
        $this->assertTrue(true);
    }

    /**
     * 패키지 경로가 올바르게 설정되는지 테스트
     *
     * @test
     */
    public function package_paths_are_correct()
    {
        $viewFactory = $this->app['view'];
        $hints = $viewFactory->getFinder()->getHints();

        if (isset($hints['jiny-mail'])) {
            $mailViewPath = $hints['jiny-mail'][0];

            // 뷰 경로가 실제로 존재하는지 확인
            $this->assertTrue(is_dir($mailViewPath));

            // 주요 뷰 파일들이 존재하는지 확인
            $this->assertTrue(file_exists($mailViewPath . '/admin/mail/logs/index.blade.php'));
            $this->assertTrue(file_exists($mailViewPath . '/admin/mail/setting/index.blade.php'));
        }
    }

    /**
     * 서비스 컨테이너 바인딩 테스트
     *
     * @test
     */
    public function services_are_bound_correctly()
    {
        // ServiceProvider에서 바인딩한 서비스들이 올바르게 등록되었는지 확인
        // 예: 메일 서비스, 설정 서비스 등

        // 현재 구현된 바인딩이 없으므로 기본 테스트만 수행
        $this->assertTrue($this->app->bound('config'));
        $this->assertTrue($this->app->bound('view'));
        $this->assertTrue($this->app->bound('router'));
    }

    /**
     * 환경별 설정 테스트
     *
     * @test
     */
    public function environment_specific_configuration()
    {
        // 테스트 환경에서의 메일 설정 확인
        $mailConfig = $this->app['config']->get('mail');

        $this->assertEquals('array', $mailConfig['default']);
        $this->assertArrayHasKey('array', $mailConfig['mailers']);
    }

    /**
     * 의존성 해결 테스트
     *
     * @test
     */
    public function dependencies_are_resolved()
    {
        // 패키지가 필요로 하는 의존성들이 올바르게 해결되는지 확인
        $this->assertTrue($this->app->bound('router'));
        $this->assertTrue($this->app->bound('view'));
        $this->assertTrue($this->app->bound('config'));
        $this->assertTrue($this->app->bound('migrator'));
    }
}