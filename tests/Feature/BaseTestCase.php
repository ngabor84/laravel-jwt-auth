<?php

namespace Middleware\Auth\Jwt\Tests\Feature;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Router;
use Middleware\Auth\Jwt\Http\Middlewares\JwtAuthMiddleware;
use Middleware\Auth\Jwt\Providers\LaravelServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class BaseTestCase extends TestCase
{
    use ValidatesRequests;

    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['jwt'] = [
            'secret' => 'test_secret',
            'algo' => 'HS256',
            'expiration' => 10,
            'decorateRequestWithTokenPayload' => false,
        ];
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $router = $app['router'];
        $this->addRoutes($router);
    }

    protected function addRoutes(Router $router): void
    {
        $router->get('api/unprotected', [
            'as' => 'api.unprotected',
            'uses' => static function () {
                return 'pong';
            }
        ]);

        $router->group(['middleware' => JwtAuthMiddleware::class], static function () use ($router) {
            $router->get('api/protected', [
                'as' => 'api.protected',
                'uses' => static function () {
                    return 'pong';
                }
            ]);
        });
    }
}
