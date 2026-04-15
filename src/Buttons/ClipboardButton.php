<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Buttons;

use Dizvestnov\LaravelMaxBot\Contracts\ButtonInterface;

final class ClipboardButton implements ButtonInterface
{
    private function __construct(
        private readonly string $text,
        private readonly string $payload,
    ) {}

    public static function make(string $text, string $payload): self
    {
        return new self($text, $payload);
    }

    public function toArray(): array
    {
        return [
            'type' => 'clipboard',
            'text' => $this->text,
            'payload' => $this->payload,
        ];
    }
}
