<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Events;

final class UpdateEventFactory
{
    /**
     * @var array<string, class-string<MaxBotEvent>>
     */
    private array $map = [
        'message_created' => MessageReceived::class,
        'message_edited' => MessageEdited::class,
        'message_removed' => MessageRemoved::class,
        'bot_started' => BotStarted::class,
        'bot_added' => BotAdded::class,
        'bot_removed' => BotRemoved::class,
        'user_added' => UserAdded::class,
        'user_removed' => UserRemoved::class,
        'chat_title_changed' => ChatTitleChanged::class,
        'message_callback' => CallbackReceived::class,
    ];

    public function make(array $update): ?MaxBotEvent
    {
        $type = $update['update_type'] ?? null;
        $eventClass = $this->map[$type] ?? null;

        if ($eventClass === null) {
            return null;
        }

        return new $eventClass($update);
    }

    public function supports(string $updateType): bool
    {
        return isset($this->map[$updateType]);
    }
}
