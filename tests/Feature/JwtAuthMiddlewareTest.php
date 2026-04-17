<?php

namespace Middleware\Auth\Jwt\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Middleware\Auth\Jwt\Events\JwtAuthFailure;
use Middleware\Auth\Jwt\Http\Middlewares\JwtAuthMiddleware;
use Middleware\Auth\Jwt\Services\TokenEncoder;
use PHPUnit\Framework\Attributes\Test;

class JwtAuthMiddlewareTest extends BaseTestCase
{
    #[Test]
    public function unprotectedEndpointReturnSuccessfulResponseWhenRequestDoesNotHaveJwtToken(): void
    {
        $response = $this->get('api/unprotected');

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function protectedEndpointReturnWithStatus401WhenRequestDoesNotHaveJwtToken(): void
    {
        $response = $this->get('api/protected');

        $this->assertEquals(401, $response->getStatusCode());
    }

    #[Test]
    public function protectedEndpointDispatchJwtAuthFailureEventWithActualRequestWhenRequestDoesNotHaveJwtToken(): void
    {
        Event::fake();

        $this->get('api/protected');

        Event::assertDispatched(JwtAuthFailure::class, function(JwtAuthFailure $event) {
            return $event->getRequest() === request();
        });
    }

    #[Test]
    public function protectedEndpointReturnWithStatus401WhenRequestHasInValidJwtToken(): void
    {
        $response = $this->get('api/protected', ['Authorization' => 'Bearer invalid_token']);

        $this->assertEquals(401, $response->getStatusCode());
    }

    #[Test]
    public function protectedEndpointDispatchJwtAuthFailureEventWhenRequestAuthenticationFails(): void
    {
        Event::fake();

        $this->get('api/protected', ['Authorization' => 'Bearer invalid_token']);

        Event::assertDispatched(JwtAuthFailure::class);
    }

    #[Test]
    public function protectedEndpointDispatchJwtAuthFailureEventWithActualRequestWhenRequestAuthenticationFails(): void
    {
        Event::fake();

        $this->get('api/protected', ['Authorization' => 'Bearer invalid_token']);

        Event::assertDispatched(JwtAuthFailure::class, function(JwtAuthFailure $event) {
            return $event->getRequest() === request();
        });
    }

    #[Test]
    public function protectedEndpointReturnSuccessfulResponseWhenRequestHasValidJwtToken(): void
    {
        $token = app()->get(TokenEncoder::class)->encode(
            [
                'staff_id' => 543,
            ]
        );
        $response = $this->get('api/protected', ['Authorization' => 'Bearer ' . $token]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function handle_decorateAttribuesIsEnabledAndTokenHasPayload_decorateTokenPayloadToRequestAttributes(): void
    {
        config(['jwt.decorateRequestWithTokenPayload' => true]);
        $tokenEncoder = new TokenEncoder('secret-that-is-long-enough-32b!!', 'HS256', 10);
        $token = $tokenEncoder->encode(['staffId' => 1234]);
        $middleware = new JwtAuthMiddleware($tokenEncoder);
        $request = new Request();
        $request->headers->add(['Authorization' => "Bearer $token"]);
        $next = function () {
            $responseMock = $this->mock(Response::class);
            $responseMock->shouldReceive('header')->once();

            return $responseMock;
        };

        $middleware->handle($request, $next);

        $this->assertEquals(['staffId' => 1234], $request->get('tokenPayload'));
    }

    #[Test]
    public function handle_decorateAttribuesIsEnabledButTokenHasEmptyPayload_decorateEmptyTokenPayloadToRequestAttributes(): void
    {
        config(['jwt.decorateRequestWithTokenPayload' => true]);
        $tokenEncoder = new TokenEncoder('secret-that-is-long-enough-32b!!', 'HS256', 10);
        $token = $tokenEncoder->encode([]);
        $middleware = new JwtAuthMiddleware($tokenEncoder);
        $request = new Request();
        $request->headers->add(['Authorization' => "Bearer $token"]);
        $next = function () {
            $responseMock = $this->mock(Response::class);
            $responseMock->shouldReceive('header')->once();

            return $responseMock;
        };

        $middleware->handle($request, $next);

        $this->assertEquals([], $request->get('tokenPayload'));
    }

    #[Test]
    public function handle_decorateAttribuesIsDisabled_doesNotDecorateTokenPayloadToRequestAttributes(): void
    {
        config(['jwt.decorateRequestWithTokenPayload' => false]);
        $tokenEncoder = new TokenEncoder('secret-that-is-long-enough-32b!!', 'HS256', 10);
        $token = $tokenEncoder->encode(['staffId' => 1234]);
        $middleware = new JwtAuthMiddleware($tokenEncoder);
        $request = new Request();
        $request->headers->add(['Authorization' => "Bearer $token"]);
        $next = function () {
            $responseMock = $this->mock(Response::class);
            $responseMock->shouldReceive('header')->once();

            return $responseMock;
        };

        $middleware->handle($request, $next);

        $this->assertArrayNotHasKey('tokenPayload', $request->all());
    }
}
