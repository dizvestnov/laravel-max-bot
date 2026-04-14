<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Tests\Unit\Messages;

use {YourVendor}\LaravelMaxBot\Buttons\CallbackButton;
use {YourVendor}\LaravelMaxBot\Keyboard;
use {YourVendor}\LaravelMaxBot\Messages\OutgoingMessage;
use {YourVendor}\LaravelMaxBot\Tests\TestCase;

class OutgoingMessageTest extends TestCase
{
    public function test_create_returns_new_instance(): void
    {
        $message = OutgoingMessage::create('Hello');

        $this->assertInstanceOf(OutgoingMessage::class, $message);
    }

    public function test_to_does_not_mutate_original(): void
    {
        $original = OutgoingMessage::create('Hello');
        $cloned   = $original->to(123);

        $arrayOriginal = $original->toArray();
        $arrayCloned   = $cloned->toArray();

        $this->assertArrayNotHasKey('recipient', $arrayOriginal);
        $this->assertSame(['user_id' => 123], $arrayCloned['recipient']);
    }

    public function test_in_chat_does_not_mutate_original(): void
    {
        $original = OutgoingMessage::create('Hello');
        $cloned   = $original->inChat(456);

        $arrayOriginal = $original->toArray();
        $arrayCloned   = $cloned->toArray();

        $this->assertArrayNotHasKey('recipient', $arrayOriginal);
        $this->assertSame(['chat_id' => 456], $arrayCloned['recipient']);
    }

    public function test_to_array_forms_correct_payload(): void
    {
        $message = OutgoingMessage::create('Hello World')
            ->to(42)
            ->markdown();

        $array = $message->toArray();

        $this->assertSame('Hello World', $array['text']);
        $this->assertSame(['user_id' => 42], $array['recipient']);
        $this->assertSame('markdown', $array['format']);
    }

    public function test_with_keyboard_includes_attachments(): void
    {
        $keyboard = Keyboard::make()
            ->row(CallbackButton::make('Yes', 'yes'));

        $message = OutgoingMessage::create('Choose')
            ->to(1)
            ->withKeyboard($keyboard);

        $array = $message->toArray();

        $this->assertArrayHasKey('attachments', $array);
        $this->assertCount(1, $array['attachments']);
        $this->assertSame('inline_keyboard', $array['attachments'][0]['type']);
    }

    public function test_reply_to_adds_link(): void
    {
        $message = OutgoingMessage::create('Reply')
            ->to(1)
            ->replyTo('msg-abc');

        $array = $message->toArray();

        $this->assertSame(['type' => 'reply', 'message_id' => 'msg-abc'], $array['link']);
    }

    public function test_html_format(): void
    {
        $message = OutgoingMessage::create('<b>Bold</b>')->to(1)->html();

        $this->assertSame('html', $message->toArray()['format']);
    }

    public function test_immutability_chain(): void
    {
        $base    = OutgoingMessage::create('Test');
        $step1   = $base->to(1);
        $step2   = $step1->markdown();
        $step3   = $step2->replyTo('msg-1');

        // Each step is a different object
        $this->assertNotSame($base, $step1);
        $this->assertNotSame($step1, $step2);
        $this->assertNotSame($step2, $step3);
    }
}
