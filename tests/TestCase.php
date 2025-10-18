<?php

namespace Jiny\Mail\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpMiddleware();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Jiny\Mail\JinyMailServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // 테스트용 데이터베이스 설정
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // 메일 설정
        $app['config']->set('mail.default', 'array');
        $app['config']->set('mail.mailers.array', [
            'transport' => 'array',
        ]);

        // 세션 설정
        $app['config']->set('session.driver', 'array');

        // 캐시 설정
        $app['config']->set('cache.default', 'array');
    }

    protected function setUpDatabase()
    {
        // 마이그레이션 실행
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // 필요한 경우 시더 실행
        // $this->seed();
    }

    protected function setUpMiddleware()
    {
        // admin 미들웨어 페이크 처리
        Route::aliasMiddleware('admin', \Jiny\Mail\Tests\Middleware\FakeAdminMiddleware::class);

        // auth 미들웨어도 페이크로 처리 (필요한 경우)
        Route::aliasMiddleware('auth', \Jiny\Mail\Tests\Middleware\FakeAuthMiddleware::class);
    }

    /**
     * 관리자 사용자로 인증된 상태로 설정
     */
    protected function actingAsAdmin()
    {
        $user = new \stdClass();
        $user->id = 1;
        $user->email = 'admin@test.com';
        $user->name = 'Test Admin';
        $user->isAdmin = true;

        $this->be($user);

        return $this;
    }

    /**
     * 일반 사용자로 인증된 상태로 설정
     */
    protected function actingAsUser()
    {
        $user = new \stdClass();
        $user->id = 2;
        $user->email = 'user@test.com';
        $user->name = 'Test User';
        $user->isAdmin = false;

        $this->be($user);

        return $this;
    }
}