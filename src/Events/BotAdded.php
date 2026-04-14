<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

final class BotAdded extends MaxBotEvent
{
    public function getUserId(): ?int
    {
        return isset($this->update['user']['user_id'])
            ? (int) $this->update['user']['user_id']
            : null;
    }

    public function isChannel(): bool
    {
        return ($this->update['chat_type'] ?? '') === 'channel';
    }
}
