<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Exceptions;

class ApiException extends MaxBotException
{
    private int $statusCode;

    private array $responseBody;

    public function __construct(int $statusCode, array $responseBody, string $message = '')
    {
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;

        if ($message === '') {
            $message = sprintf(
                'MAX Bot API error: HTTP %d — %s',
                $statusCode,
                $responseBody['message'] ?? 'Unknown error'
            );
        }

        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }
}
