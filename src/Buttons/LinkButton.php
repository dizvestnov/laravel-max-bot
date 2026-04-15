<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Buttons;

use Dizvestnov\LaravelMaxBot\Contracts\ButtonInterface;

final class LinkButton implements ButtonInterface
{
    private string $text;

    private string $url;

    private function __construct(string $text, string $url)
    {
        $this->text = $text;
        $this->url = $url;
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
            'url' => $this->url,
        ];
    }
}
