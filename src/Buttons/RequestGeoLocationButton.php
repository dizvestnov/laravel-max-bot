<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Buttons;

use {YourVendor}\LaravelMaxBot\Contracts\ButtonInterface;

final class RequestGeoLocationButton implements ButtonInterface
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
            'type' => 'request_geo_location',
            'text' => $this->text,
        ];
    }
}
