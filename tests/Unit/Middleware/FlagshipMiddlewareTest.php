<?php

namespace Tests\Unit\Middleware;

use Flagship\Facades\Flagship;
use Flagship\Middleware\FlagshipMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class FlagshipMiddlewareTest extends TestCase
{
    protected FlagshipMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new FlagshipMiddleware();
    }

    public function test_allows_request_if_flag_is_enabled()
    {
        $request = Request::create('/test', 'GET');
        $flagName = 'enabled_feature';

        Flagship::shouldReceive('isEnabled')->once()->with($flagName, null)->andReturn(true);

        $response = $this->middleware->handle($request, function () {
            return new Response('Allowed', 200);
        }, $flagName);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Allowed', $response->getContent());
    }


    public function test_aborts_request_if_flag_is_disabled()
    {
        $request = Request::create('/test', 'GET');
        $flagName = 'disabled_feature';

        Flagship::shouldReceive('isEnabled')->once()->with($flagName, null)->andReturn(false);

        $this->expectException(NotFoundHttpException::class);

        $this->middleware->handle($request, function () {
            return new Response('Should not reach here', 200);
        }, $flagName);
    }


    public function test_passes_user_to_flagship_service_if_available()
    {
        $user = new \Illuminate\Foundation\Auth\User();
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        $flagName = 'user_feature';

        Flagship::shouldReceive('isEnabled')->once()->with($flagName, $user)->andReturn(true);

        $response = $this->middleware->handle($request, function () {
            return new Response('Allowed', 200);
        }, $flagName);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
