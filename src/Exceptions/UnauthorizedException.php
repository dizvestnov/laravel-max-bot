<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct(array $responseBody = [])
    {
        parent::__construct(401, $responseBody, 'Unauthorized: invalid or missing MAX Bot token.');
    }
}
