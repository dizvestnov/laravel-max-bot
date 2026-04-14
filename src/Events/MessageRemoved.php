<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

final class MessageRemoved extends MaxBotEvent
{
    public function getMessageId(): ?string
    {
        return $this->update['message_id'] ?? null;
    }

    public function getUserId(): ?int
    {
        return isset($this->update['user_id'])
            ? (int) $this->update['user_id']
            : null;
    }
}
