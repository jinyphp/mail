<?php

namespace Jiny\Mail\Tests\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * 테스트용 페이크 Auth 미들웨어
 *
 * Laravel의 auth 미들웨어를 페이크로 처리하여
 * 테스트에서 인증 검증을 우회할 수 있도록 합니다.
 */
class FakeAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 테스트 환경에서는 항상 통과
        // 필요한 경우 여기서 추가적인 테스트 설정 가능

        return $next($request);
    }
}