<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests\Unit\Buttons;

use Dizvestnov\LaravelMaxBot\Buttons\CallbackButton;
use Dizvestnov\LaravelMaxBot\Buttons\ClipboardButton;
use Dizvestnov\LaravelMaxBot\Buttons\LinkButton;
use Dizvestnov\LaravelMaxBot\Buttons\MessageButton;
use Dizvestnov\LaravelMaxBot\Buttons\RequestContactButton;
use Dizvestnov\LaravelMaxBot\Buttons\RequestGeoLocationButton;
use Dizvestnov\LaravelMaxBot\Tests\TestCase;

class CallbackButtonTest extends TestCase
{
    public function test_callback_button_to_array(): void
    {
        $button = CallbackButton::make('Click Me', 'btn_click');

        $this->assertSame([
            'type' => 'callback',
            'text' => 'Click Me',
            'payload' => 'btn_click',
        ], $button->toArray());
    }

    public function test_link_button_to_array(): void
    {
        $button = LinkButton::make('Visit Site', 'https://example.com');

        $this->assertSame([
            'type' => 'link',
            'text' => 'Visit Site',
            'url' => 'https://example.com',
        ], $button->toArray());
    }

    public function test_request_contact_button_to_array(): void
    {
        $button = RequestContactButton::make('Share Contact');

        $this->assertSame([
            'type' => 'request_contact',
            'text' => 'Share Contact',
        ], $button->toArray());
    }

    public function test_request_geo_location_button_to_array(): void
    {
        $button = RequestGeoLocationButton::make('Share Location');

        $this->assertSame([
            'type' => 'request_geo_location',
            'text' => 'Share Location',
        ], $button->toArray());
    }

    public function test_message_button_to_array(): void
    {
        $button = MessageButton::make('Send Message', 'payload_msg');

        $this->assertSame([
            'type' => 'message',
            'text' => 'Send Message',
            'payload' => 'payload_msg',
        ], $button->toArray());
    }

    public function test_clipboard_button_to_array(): void
    {
        $button = ClipboardButton::make('Copy', 'copy_text');

        $this->assertSame([
            'type' => 'clipboard',
            'text' => 'Copy',
            'payload' => 'copy_text',
        ], $button->toArray());
    }
}
