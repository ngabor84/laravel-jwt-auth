<?php declare(strict_types = 1);

namespace Middleware\Auth\Jwt\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Middleware\Auth\Jwt\Console\SecretGenerateCommand;
use Middleware\Auth\Jwt\Http\Middlewares\JwtAuthMiddleware;
use Middleware\Auth\Jwt\Services\TokenEncoder;

abstract class ServiceProvider extends BaseServiceProvider
{
    protected $middlewares = [
        'jwt.auth' => JwtAuthMiddleware::class,
    ];

    abstract public function boot(): void;

    public function register(): void
    {
        $this->registerBindings();
        $this->registerCommands();
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(
            TokenEncoder::class,
            static function ($app) {
                $config = $app['config']->get('jwt');

                return new TokenEncoder($config['secret'], $config['algo'], (int)$config['expiration']);
            }
        );
    }

    protected function registerCommands(): void
    {
        $this->commands(SecretGenerateCommand::class);
    }

    protected function configPath(): string
    {
        return dirname(__DIR__, 2) . '/config/jwt.php';
    }
}
