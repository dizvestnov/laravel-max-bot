<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

final class UserRemoved extends MaxBotEvent
{
    public function getUser(): array
    {
        return $this->update['user'] ?? [];
    }

    public function getUserId(): ?int
    {
        return isset($this->update['user']['user_id'])
            ? (int) $this->update['user']['user_id']
            : null;
    }

    public function getAdminId(): ?int
    {
        return isset($this->update['admin_id'])
            ? (int) $this->update['admin_id']
            : null;
    }
}
