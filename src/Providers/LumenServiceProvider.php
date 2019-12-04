<?php declare(strict_types=1);

namespace Middleware\Auth\Jwt\Providers;

/**
 * @property \Laravel\Lumen\Application $app
 */
class LumenServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->configure('jwt');
        $this->mergeConfigFrom($this->configPath(), 'jwt');
        $this->app->routeMiddleware($this->middlewares);
    }
}
