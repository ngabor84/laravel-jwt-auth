[![GitHub license](https://img.shields.io/github/license/ngabor84/laravel-jwt-auth.svg)](https://github.com/ngabor84/laravel-jwt-auth/blob/master/LICENSE)

# JWT Auth Middleware
JWT authentication middleware for the Laravel and Lumen framework.

## About
This package allows you to authenticate the incoming requests with JWT authentication.

## Installation
Require the ngabor84/laravel-jwt-auth package in your composer.json and update your dependencies:
```bash
composer require ngabor84/laravel-jwt-auth
```

## Usage with Laravel
Add the service provider to the providers array in the config/app.php config file as follows:
```php
'providers' => [
    ...
    \Middleware\Auth\Jwt\Providers\LaravelServiceProvider::class,
]
```
Run the following command to publish the package config file:
```bash
php artisan vendor:publish --provider="Middleware\Auth\Jwt\Providers\LaravelServiceProvider"
```
You should now have a config/jwt.php file that allows you to configure the basics of this package.

## Usage with Lumen
Add the following snippet to the bootstrap/app.php file under the providers section as follows:
```php
$app->register(\Middleware\Auth\Jwt\Providers\LumenServiceProvider::class);
...
$app->configure('jwt');
```

Create a config directory (if it's not exist), and create an jwt.php in it with the plugin configuration like this:
```php
return [
    'secret' => env('JWT_SECRET'),
    'algo' => 'HS256',
    'expiration' => 10, // 10 minutes
    'decorateRequestWithTokenPayload' => false, // if it's true then after authentication the request will contain a tokenPayload attribute with the payload of the JWT token
];
```
