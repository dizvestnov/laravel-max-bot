<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

final class BotStarted extends MaxBotEvent
{
    public function getUserId(): ?int
    {
        return isset($this->update['user']['user_id'])
            ? (int) $this->update['user']['user_id']
            : null;
    }

    public function getPayload(): ?string
    {
        return $this->update['payload'] ?? null;
    }
}
