<?php

namespace Middleware\Auth\Jwt\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Middleware\Auth\Jwt\Events\JwtAuthFailure;
use Middleware\Auth\Jwt\Services\TokenEncoder;

class JwtAuthMiddlewareTest extends BaseTestCase
{
    /**
     * @test
     */
    public function unprotectedEndpointReturnSuccessfulResponseWhenRequestDoesNotHaveJwtToken(): void
    {
        $response = $this->get('api/unprotected');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function protectedEndpointReturnWithStatus401WhenRequestDoesNotHaveJwtToken(): void
    {
        $response = $this->get('api/protected');

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function protectedEndpointReturnWithStatus401WhenRequestHasInValidJwtToken(): void
    {
        $response = $this->get('api/protected', ['Authorization' => 'Bearer invalid_token']);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function protectedEndpointDispatchJwtAuthFailureEventWhenRequestAuthenticationFails(): void
    {
        Event::fake();

        $this->get('api/protected', ['Authorization' => 'Bearer invalid_token']);

        Event::assertDispatched(JwtAuthFailure::class);
    }

    /**
     * @test
     */
    public function protectedEndpointDispatchJwtAuthFailureEventWithActualRequestWhenRequestAuthenticationFails(): void
    {
        Event::fake();

        $this->get('api/protected', ['Authorization' => 'Bearer invalid_token']);

        Event::assertDispatched(JwtAuthFailure::class, function(JwtAuthFailure $event) {
            return $event->getRequest() === request();
        });
    }

    /**
     * @test
     */
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
}
