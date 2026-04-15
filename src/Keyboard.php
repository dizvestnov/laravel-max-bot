<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot;

use Dizvestnov\LaravelMaxBot\Contracts\ButtonInterface;

final class Keyboard
{
    private array $rows = [];

    private function __construct() {}

    public static function make(): self
    {
        return new self;
    }

    public function row(ButtonInterface ...$buttons): self
    {
        $clone = clone $this;
        $clone->rows[] = array_map(fn ($b) => $b->toArray(), $buttons);

        return $clone;
    }

    public function toArray(): array
    {
        return [
            'type' => 'inline_keyboard',
            'payload' => ['buttons' => $this->rows],
        ];
    }
}
