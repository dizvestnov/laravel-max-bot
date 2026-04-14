<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Events;

final class BotRemoved extends MaxBotEvent
{
    public function getUserId(): ?int
    {
        return isset($this->update['user']['user_id'])
            ? (int) $this->update['user']['user_id']
            : null;
    }
}
