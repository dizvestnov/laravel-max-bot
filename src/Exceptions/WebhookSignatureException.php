<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Exceptions;

class WebhookSignatureException extends MaxBotException
{
    public function __construct(string $message = 'Invalid webhook signature.')
    {
        parent::__construct($message, 403);
    }
}
