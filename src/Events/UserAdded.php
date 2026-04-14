<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

final class UserAdded extends MaxBotEvent
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

    public function getInviterId(): ?int
    {
        return isset($this->update['inviter_id'])
            ? (int) $this->update['inviter_id']
            : null;
    }
}
