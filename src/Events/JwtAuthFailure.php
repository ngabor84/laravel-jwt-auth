<?php declare(strict_types = 1);

namespace Middleware\Auth\Jwt\Events;

class JwtAuthFailure
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
