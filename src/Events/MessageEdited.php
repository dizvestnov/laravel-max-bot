<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Events;

final class MessageEdited extends MaxBotEvent
{
    public function getMessageId(): ?string
    {
        return $this->update['message']['id'] ?? null;
    }

    public function getText(): ?string
    {
        return $this->update['message']['body']['text'] ?? null;
    }

    public function getMessage(): array
    {
        return $this->update['message'] ?? [];
    }
}
