<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

abstract class MaxBotEvent
{
    public function __construct(public readonly array $update)
    {
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
