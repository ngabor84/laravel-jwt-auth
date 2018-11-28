<?php declare(strict_types = 1);

namespace Middleware\Auth\Jwt\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Middleware\Auth\Jwt\Events\JwtAuthFailure;
use Middleware\Auth\Jwt\Exceptions\JwtTokenDecodeException;
use Middleware\Auth\Jwt\Services\TokenEncoder;

class JwtAuthMiddleware
{
    /**
     * @var TokenEncoder
     */
    private $encoder;

    public function __construct(TokenEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json(['error' => 'Jwt token is missing'], 401);
        }

        try {
            $session = $this->encoder->decode($token);
        } catch (JwtTokenDecodeException $e) {
            event(new JwtAuthFailure($request));

            return response()->json(['error' => 'Unable to decode jwt token'], 401);
        }

        $response = $next($request);

        $newToken = $this->encoder->encode($session);
        $response->header('Authorization', ["Bearer {$newToken}"]);

        return $response;
    }
}
