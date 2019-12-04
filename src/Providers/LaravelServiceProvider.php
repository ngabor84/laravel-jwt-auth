<?php declare(strict_types=1);

namespace Middleware\Auth\Jwt\Providers;

use Illuminate\Foundation\Application;

/**
 * Class LaravelServiceProvider
 *
 * @property Application $app
 */
class LaravelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([realpath($this->configPath()) => config_path('jwt.php')]);
        $this->mergeConfigFrom($this->configPath(), 'jwt');
        $this->setMiddlewares();
    }

    protected function setMiddlewares(): void
    {
        foreach ($this->middlewares as $alias => $middleware) {
            $this->app['router']->aliasMiddleware($alias, $middleware);
        }
    }
}
