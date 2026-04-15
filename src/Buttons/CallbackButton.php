<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Buttons;

use Dizvestnov\LaravelMaxBot\Contracts\ButtonInterface;

final class CallbackButton implements ButtonInterface
{
    private string $text;

    private string $payload;

    private function __construct(string $text, string $payload)
    {
        $this->text = $text;
        $this->payload = $payload;
    }

    public static function make(string $text, string $payload): self
    {
        return new self($text, $payload);
    }

    public function toArray(): array
    {
        return [
            'type' => 'callback',
            'text' => $this->text,
            'payload' => $this->payload,
        ];
    }
}
