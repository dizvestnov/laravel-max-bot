<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Buttons;

use Dizvestnov\LaravelMaxBot\Contracts\ButtonInterface;

final class RequestContactButton implements ButtonInterface
{
    private function __construct(
        private readonly string $text,
    ) {
    }

    public static function make(string $text): self
    {
        return new self($text);
    }

    public function toArray(): array
    {
        return [
            'type' => 'request_contact',
            'text' => $this->text,
        ];
    }
}
