<?php declare(strict_types=1);

namespace Middleware\Auth\Jwt\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Middleware\Auth\Jwt\Events\JwtAuthFailure;
use Middleware\Auth\Jwt\Exceptions\JwtTokenDecodeException;
use Middleware\Auth\Jwt\Services\TokenEncoder;

class JwtAuthMiddleware
{
    private TokenEncoder $encoder;

    private bool $decorateRequestWithTokenPayload;

    public function __construct(TokenEncoder $encoder)
    {
        $this->encoder = $encoder;
        $this->decorateRequestWithTokenPayload = config('jwt.decorateRequestWithTokenPayload', false);
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();
            $this->validateToken($request, $token);
            $session = $this->encoder->decode($token);
        } catch (JwtTokenDecodeException $e) {
            event(new JwtAuthFailure($request));
            return response()->json(['error' => 'Unable to decode jwt token'], 401);
        }

        if ($this->decorateRequestWithTokenPayload) {
            $request->merge(['tokenPayload' => $session]);
        }

        $response = $next($request);

        $newToken = $this->encoder->encode($session);
        $response->header('Authorization', ["Bearer {$newToken}"]);

        return $response;
    }

    private function validateToken(Request $request, ?string $token): void
    {
        if ($token === null) {
            event(new JwtAuthFailure($request));

            throw new JwtTokenDecodeException('Jwt token is missing');
        }
    }
}
