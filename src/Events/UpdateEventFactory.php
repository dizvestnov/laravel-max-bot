<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

use Dizvestnov\LaravelMaxBot\Enums\UpdateType;

final class UpdateEventFactory
{
    public function make(array $update): ?MaxBotEvent
    {
        $type = UpdateType::tryFrom($update['update_type'] ?? '');

        if ($type === null) {
            return null;
        }

        $eventClass = match ($type) {
            UpdateType::MessageCreated => MessageReceived::class,
            UpdateType::MessageEdited => MessageEdited::class,
            UpdateType::MessageRemoved => MessageRemoved::class,
            UpdateType::BotStarted => BotStarted::class,
            UpdateType::BotAdded => BotAdded::class,
            UpdateType::BotRemoved => BotRemoved::class,
            UpdateType::UserAdded => UserAdded::class,
            UpdateType::UserRemoved => UserRemoved::class,
            UpdateType::ChatTitleChanged => ChatTitleChanged::class,
            UpdateType::CallbackReceived => CallbackReceived::class,
        };

        return new $eventClass($update);
    }

    public function supports(string $updateType): bool
    {
        return UpdateType::tryFrom($updateType) !== null;
    }
}
