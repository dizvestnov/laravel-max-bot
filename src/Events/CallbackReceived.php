<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Events;

final class CallbackReceived extends MaxBotEvent
{
    public function getCallbackId(): string
    {
        return (string) ($this->update['callback']['callback_id'] ?? '');
    }

    public function getCallbackPayload(): string
    {
        return (string) ($this->update['callback']['payload'] ?? '');
    }

    public function getUserId(): ?int
    {
        return isset($this->update['callback']['user']['user_id'])
            ? (int) $this->update['callback']['user']['user_id']
            : null;
    }

    public function getMessageId(): ?string
    {
        return $this->update['callback']['message']['id'] ?? null;
    }
}
