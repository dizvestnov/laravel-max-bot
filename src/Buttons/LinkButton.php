<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Buttons;

use {YourVendor}\LaravelMaxBot\Contracts\ButtonInterface;

final class LinkButton implements ButtonInterface
{
    private function __construct(
        private readonly string $text,
        private readonly string $url,
    ) {
    }

    public static function make(string $text, string $url): self
    {
        return new self($text, $url);
    }

    public function toArray(): array
    {
        return [
            'type' => 'link',
            'text' => $this->text,
            'url'  => $this->url,
        ];
    }
}
