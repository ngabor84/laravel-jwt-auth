<?php declare(strict_types=1);

namespace Middleware\Auth\Jwt\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Middleware\Auth\Jwt\Exceptions\JwtTokenDecodeException;
use Middleware\Auth\Jwt\Exceptions\TokenEncoderInitializeException;
use Throwable;

class TokenEncoder
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $algo;

    /**
     * @var int
     */
    private $expiration;

    public function __construct(string $secret, string $algo, int $expiration)
    {
        $this->validateSecret($secret);
        $this->validateAlgo($algo);
        $this->validateExpiration($expiration);

        $this->secret = $secret;
        $this->algo = $algo;
        $this->expiration = $expiration;
    }

    public function encode(array $payload): string
    {
        $payload['exp'] = strtotime(sprintf('+%d minutes', $this->expiration));

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function decode(string $token): array
    {
        try {
            $payload = (array) JWT::decode($token, new Key($this->secret, $this->algo));
            unset($payload['exp']);

            return $payload;
        } catch (Throwable $e) {
            throw new JwtTokenDecodeException('Token decoding failed');
        }
    }

    private function validateSecret(string $secret): void
    {
        if ($secret === '') {
            throw new TokenEncoderInitializeException('Secret must not be empty');
        }
    }

    private function validateAlgo(string $algo): void
    {
        if ($algo === '') {
            throw new TokenEncoderInitializeException('Algo must not be empty');
        }
    }

    private function validateExpiration(int $expiration): void
    {
        if ($expiration === 0) {
            throw new TokenEncoderInitializeException('Expiration must be greater than zero');
        }
    }
}
