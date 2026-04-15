<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Messages;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Dizvestnov\LaravelMaxBot\Keyboard;

final class OutgoingMessage
{
    private ?int $userId = null;

    private ?int $chatId = null;

    private string $text = '';

    private array $attachments = [];

    private ?string $format = null;

    private ?string $replyToMessageId = null;

    private function __construct() {}

    public static function create(string $text = ''): self
    {
        $instance = new self();
        $instance->text = $text;

        return $instance;
    }

    public function to(int $userId): self
    {
        $clone = clone $this;
        $clone->userId = $userId;

        return $clone;
    }

    public function inChat(int $chatId): self
    {
        $clone = clone $this;
        $clone->chatId = $chatId;

        return $clone;
    }

    public function withKeyboard(Keyboard $keyboard): self
    {
        $clone = clone $this;
        $clone->attachments[] = $keyboard->toArray();

        return $clone;
    }

    public function replyTo(string $messageId): self
    {
        $clone = clone $this;
        $clone->replyToMessageId = $messageId;

        return $clone;
    }

    public function markdown(): self
    {
        $clone = clone $this;
        $clone->format = 'markdown';

        return $clone;
    }

    public function html(): self
    {
        $clone = clone $this;
        $clone->format = 'html';

        return $clone;
    }

    public function toArray(): array
    {
        $payload = [];

        if ($this->userId !== null) {
            $payload['recipient'] = ['user_id' => $this->userId];
        } elseif ($this->chatId !== null) {
            $payload['recipient'] = ['chat_id' => $this->chatId];
        }

        $payload['text'] = $this->text;

        if ($this->format !== null) {
            $payload['format'] = $this->format;
        }

        if (! empty($this->attachments)) {
            $payload['attachments'] = $this->attachments;
        }

        if ($this->replyToMessageId !== null) {
            $payload['link'] = [
                'type' => 'reply',
                'message_id' => $this->replyToMessageId,
            ];
        }

        return $payload;
    }

    public function send(): array
    {
        return app(MaxBotClientInterface::class)->sendMessage($this->toArray());
    }
}
