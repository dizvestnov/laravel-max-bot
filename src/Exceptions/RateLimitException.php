<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Exceptions;

class RateLimitException extends ApiException
{
    private ?int $retryAfter;

    public function __construct(array $responseBody = [], ?int $retryAfter = null)
    {
        $this->retryAfter = $retryAfter;

        $message = 'Rate limit exceeded (HTTP 429).';
        if ($retryAfter !== null) {
            $message .= sprintf(' Retry after %d seconds.', $retryAfter);
        }

        parent::__construct(429, $responseBody, $message);
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
