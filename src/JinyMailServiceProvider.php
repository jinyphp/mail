<?php

namespace Jiny\Mail;

use Illuminate\Support\ServiceProvider;

class JinyMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Config 등록
        $this->mergeConfigFrom(
            __DIR__.'/../config/mail.php', 'jiny.mail'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 라우트 등록
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // 뷰 등록
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'jiny-mail');

        // 마이그레이션 등록
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // 발행할 파일들 등록 (php artisan vendor:publish 시)
        if ($this->app->runningInConsole()) {
            // 뷰 발행
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/jiny-mail'),
            ], 'jiny-mail-views');

            // Config 발행
            $this->publishes([
                __DIR__.'/../config/mail.php' => config_path('jiny-mail.php'),
            ], 'jiny-mail-config');

            // 마이그레이션 발행
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'jiny-mail-migrations');
        }
    }
}