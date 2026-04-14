<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Events;

final class ChatTitleChanged extends MaxBotEvent
{
    public function getTitle(): ?string
    {
        return $this->update['title'] ?? null;
    }

    public function getUserId(): ?int
    {
        return isset($this->update['user']['user_id'])
            ? (int) $this->update['user']['user_id']
            : null;
    }
}
