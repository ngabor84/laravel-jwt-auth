<?php

namespace Middleware\Auth\Jwt\Tests\Unit;

use Middleware\Auth\Jwt\Exceptions\JwtTokenDecodeException;
use Middleware\Auth\Jwt\Exceptions\TokenEncoderInitializeException;
use Middleware\Auth\Jwt\Services\TokenEncoder;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

class TokenEncoderTest extends TestCase
{
    /**
     * @test
     */
    public function encode_WhenSecretIsEmpty_ShouldThrowException()
    {
        try {
            new TokenEncoder('', 'HS256', 10);

            $this->fail(TokenEncoderInitializeException::class . ' exception should have been raised');
        } catch (TokenEncoderInitializeException $e) {
            $this->assertEquals('Secret must not be empty', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function encode_WhenAlgoIsEmpty_ShouldThrowException()
    {
        try {
            new TokenEncoder('test_secret', '', 10);

            $this->fail(TokenEncoderInitializeException::class . ' exception should have been raised');
        } catch (TokenEncoderInitializeException $e) {
            $this->assertEquals('Algo must not be empty', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function encode_WhenExpirationIsZero_ShouldThrowException()
    {
        try {
            new TokenEncoder('test_secret', 'HS256', 0);

            $this->fail(TokenEncoderInitializeException::class . ' exception should have been raised');
        } catch (TokenEncoderInitializeException $e) {
            $this->assertEquals('Expiration must be greater than zero', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function encode_CalledWithPayload_ReturnsString()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t', 'HS256', 10);
        $token = $encoder->encode(['param' => 'value']);

        $this->assertIsString($token);
    }

    /**
     * @test
     */
    public function encode_receiveDifferentPayload_shouldReturnsDifferentToken()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t', 'HS256', 10);

        $firstToken = $encoder->encode(['first' => 'payload']);
        $secondToken = $encoder->encode(['second' => 'payload']);

        $this->assertNotEquals($firstToken, $secondToken);
    }

    /**
     * @test
     */
    public function decode_whenCalledWithProperParameters_shouldReturnDecodedPayload()
    {
        $payload = ['first' => 'payload'];

        $encoder = new TokenEncoder('s0m3s3cr3t', 'HS256', 10);

        $token = $encoder->encode($payload);

        $this->assertEquals($payload, $encoder->decode($token));
    }

    /**
     * @test
     */
    public function decode_whenCalledWithInvalidToken_shouldThrowException()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t', 'HS256', 10);

        $token = $encoder->encode(['first' => 'payload']);

        try {
            $encoder->decode($token . "this-has-been-tampered-with");

            $this->fail(JwtTokenDecodeException::class . ' exception should have been raised');
        } catch (JwtTokenDecodeException $e) {
            $this->assertEquals('Token decoding failed', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function decode_WhenTryingToDecodeWithOtherSecret_shouldThrowException()
    {
        $encoder = new TokenEncoder('s0m3s3cr3t', 'HS256', 10);
        $decoder = new TokenEncoder('0th3rs3cr3t', 'HS256', 10);

        $token = $encoder->encode(['first' => 'payload']);

        try {
            $decoder->decode($token);

            $this->fail(JwtTokenDecodeException::class . ' exception should have been raised');
        } catch (JwtTokenDecodeException $e) {
            $this->assertEquals('Token decoding failed', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function decode_WhenTokenIsExpired_ShouldThrowException()
    {
        $encoder = new TokenEncoder('s3cr3t', 'HS256', 10);
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
