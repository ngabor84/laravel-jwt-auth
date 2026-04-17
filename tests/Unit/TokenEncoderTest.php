<?php

namespace Middleware\Auth\Jwt\Tests\Unit;

use Middleware\Auth\Jwt\Exceptions\JwtTokenDecodeException;
use Middleware\Auth\Jwt\Exceptions\TokenEncoderInitializeException;
use Middleware\Auth\Jwt\Services\TokenEncoder;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TokenEncoderTest extends TestCase
{
    #[Test]
    public function encode_WhenSecretIsEmpty_ShouldThrowException()
    {
        try {
            new TokenEncoder('', 'HS256', 10);

            $this->fail(TokenEncoderInitializeException::class . ' exception should have been raised');
        } catch (TokenEncoderInitializeException $e) {
            $this->assertEquals('Secret must not be empty', $e->getMessage());
        }
    }

    #[Test]
    public function encode_WhenAlgoIsEmpty_ShouldThrowException()
    {
        try {
            new TokenEncoder('test_secret', '', 10);

            $this->fail(TokenEncoderInitializeException::class . ' exception should have been raised');
        } catch (TokenEncoderInitializeException $e) {
            $this->assertEquals('Algo must not be empty', $e->getMessage());
        }
    }

    #[Test]
    public function encode_WhenExpirationIsZero_ShouldThrowException()
    {
        try {
            new TokenEncoder('test_secret', 'HS256', 0);

            $this->fail(TokenEncoderInitializeException::class . ' exception should have been raised');
        } catch (TokenEncoderInitializeException $e) {
            $this->assertEquals('Expiration must be greater than zero', $e->getMessage());
        }
    }

    #[Test]
    public function encode_CalledWithPayload_ReturnsString()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t-that-is-long-enough-32', 'HS256', 10);
        $token = $encoder->encode(['param' => 'value']);

        $this->assertIsString($token);
    }

    #[Test]
    public function encode_receiveDifferentPayload_shouldReturnsDifferentToken()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t-that-is-long-enough-32', 'HS256', 10);

        $firstToken = $encoder->encode(['first' => 'payload']);
        $secondToken = $encoder->encode(['second' => 'payload']);

        $this->assertNotEquals($firstToken, $secondToken);
    }

    #[Test]
    public function decode_whenCalledWithProperParameters_shouldReturnDecodedPayload()
    {
        $payload = ['first' => 'payload'];

        $encoder = new TokenEncoder('s0m3s3cr3t-that-is-long-enough-32', 'HS256', 10);

        $token = $encoder->encode($payload);

        $this->assertEquals($payload, $encoder->decode($token));
    }

    #[Test]
    public function decode_whenCalledWithInvalidToken_shouldThrowException()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t-that-is-long-enough-32', 'HS256', 10);

        $token = $encoder->encode(['first' => 'payload']);

        try {
            $encoder->decode($token . "this-has-been-tampered-with");

            $this->fail(JwtTokenDecodeException::class . ' exception should have been raised');
        } catch (JwtTokenDecodeException $e) {
            $this->assertEquals('Token decoding failed', $e->getMessage());
        }
    }

    #[Test]
    public function decode_WhenTryingToDecodeWithOtherSecret_shouldThrowException()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t-that-is-long-enough-32', 'HS256', 10);
        $decoder = new TokenEncoder('0th3rs3cr3t-that-is-long-enough!', 'HS256', 10);

        $token = $encoder->encode(['first' => 'payload']);

        try {
            $decoder->decode($token);

            $this->fail(JwtTokenDecodeException::class . ' exception should have been raised');
        } catch (JwtTokenDecodeException $e) {
            $this->assertEquals('Token decoding failed', $e->getMessage());
        }
    }

    #[Test]
    public function decode_WhenTokenIsExpired_ShouldThrowException()
    {
        $encoder = new TokenEncoder('s3cr3t-that-is-long-enough-for-32', 'HS256', 10);
        $token = $encoder->encode(['something' => 'anything']);

        JWT::$timestamp = time() + 11 * 60;

        try {
            $encoder->decode($token);
            $this->fail(JwtTokenDecodeException::class . ' exception should have been raised');
        } catch (JwtTokenDecodeException $e) {
            $this->assertEquals('Token decoding failed', $e->getMessage());
        } finally {
            JWT::$timestamp = null;
        }

    }
}
