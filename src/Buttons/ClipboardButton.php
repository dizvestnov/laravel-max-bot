<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Buttons;

use {YourVendor}\LaravelMaxBot\Contracts\ButtonInterface;

final class ClipboardButton implements ButtonInterface
{
    private string $text;
    private string $payload;

    private function __construct(string $text, string $payload)
    {
        $this->text    = $text;
        $this->payload = $payload;
    }

    public static function make(string $text, string $payload): self
    {
        return new self($text, $payload);
    }

    public function toArray(): array
    {
        return [
            'type'    => 'clipboard',
            'text'    => $this->text,
            'payload' => $this->payload,
        ];
    }
}
