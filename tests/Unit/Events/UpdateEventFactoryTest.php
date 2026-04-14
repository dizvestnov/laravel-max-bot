<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests\Unit\Events;

use Dizvestnov\LaravelMaxBot\Events\BotAdded;
use Dizvestnov\LaravelMaxBot\Events\BotRemoved;
use Dizvestnov\LaravelMaxBot\Events\BotStarted;
use Dizvestnov\LaravelMaxBot\Events\CallbackReceived;
use Dizvestnov\LaravelMaxBot\Events\ChatTitleChanged;
use Dizvestnov\LaravelMaxBot\Events\MessageEdited;
use Dizvestnov\LaravelMaxBot\Events\MessageReceived;
use Dizvestnov\LaravelMaxBot\Events\MessageRemoved;
use Dizvestnov\LaravelMaxBot\Events\UpdateEventFactory;
use Dizvestnov\LaravelMaxBot\Events\UserAdded;
use Dizvestnov\LaravelMaxBot\Events\UserRemoved;
use Dizvestnov\LaravelMaxBot\Tests\TestCase;

class UpdateEventFactoryTest extends TestCase
{
    private UpdateEventFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new UpdateEventFactory();
    }

    public function test_message_created_returns_message_received(): void
    {
        $event = $this->factory->make(['update_type' => 'message_created', 'message' => []]);

        $this->assertInstanceOf(MessageReceived::class, $event);
    }

    public function test_message_edited_returns_message_edited(): void
    {
        $event = $this->factory->make(['update_type' => 'message_edited']);

        $this->assertInstanceOf(MessageEdited::class, $event);
    }

    public function test_message_removed_returns_message_removed(): void
    {
        $event = $this->factory->make(['update_type' => 'message_removed']);

        $this->assertInstanceOf(MessageRemoved::class, $event);
    }

    public function test_bot_started_returns_bot_started(): void
    {
        $event = $this->factory->make(['update_type' => 'bot_started']);

        $this->assertInstanceOf(BotStarted::class, $event);
    }

    public function test_bot_added_returns_bot_added(): void
    {
        $event = $this->factory->make(['update_type' => 'bot_added']);

        $this->assertInstanceOf(BotAdded::class, $event);
    }

    public function test_bot_removed_returns_bot_removed(): void
    {
        $event = $this->factory->make(['update_type' => 'bot_removed']);

        $this->assertInstanceOf(BotRemoved::class, $event);
    }

    public function test_user_added_returns_user_added(): void
    {
        $event = $this->factory->make(['update_type' => 'user_added']);

        $this->assertInstanceOf(UserAdded::class, $event);
    }

    public function test_user_removed_returns_user_removed(): void
    {
        $event = $this->factory->make(['update_type' => 'user_removed']);

        $this->assertInstanceOf(UserRemoved::class, $event);
    }

    public function test_chat_title_changed_returns_chat_title_changed(): void
    {
        $event = $this->factory->make(['update_type' => 'chat_title_changed']);

        $this->assertInstanceOf(ChatTitleChanged::class, $event);
    }

    public function test_message_callback_returns_callback_received(): void
    {
        $event = $this->factory->make(['update_type' => 'message_callback']);

        $this->assertInstanceOf(CallbackReceived::class, $event);
    }

    public function test_unknown_update_type_returns_null(): void
    {
        $event = $this->factory->make(['update_type' => 'unknown_type']);

        $this->assertNull($event);
    }

    public function test_missing_update_type_returns_null(): void
    {
        $event = $this->factory->make([]);

        $this->assertNull($event);
    }

    public function test_supports_known_type(): void
    {
        $this->assertTrue($this->factory->supports('message_created'));
        $this->assertTrue($this->factory->supports('bot_started'));
        $this->assertTrue($this->factory->supports('message_callback'));
    }

    public function test_supports_unknown_type_returns_false(): void
    {
        $this->assertFalse($this->factory->supports('unknown_type'));
        $this->assertFalse($this->factory->supports(''));
    }
}
