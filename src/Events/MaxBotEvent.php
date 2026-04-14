<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Events;

abstract class MaxBotEvent
{
    public array $update;

    public function __construct(array $update)
    {
        $this->update = $update;
    }

    public function getUpdateType(): string
    {
        return $this->update['update_type'] ?? '';
    }

    public function getChatId(): ?int
    {
        return isset($this->update['chat_id'])
            ? (int) $this->update['chat_id']
            : null;
    }

    public function getTimestamp(): int
    {
        return (int) ($this->update['timestamp'] ?? 0);
    }
}
