<?php declare(strict_types=1);

namespace Middleware\Auth\Jwt\Events;

use Illuminate\Http\Request;

class JwtAuthFailure
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
