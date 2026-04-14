<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests\Feature;

use Dizvestnov\LaravelMaxBot\Api\MaxBotClient;
use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Dizvestnov\LaravelMaxBot\Events\UpdateEventFactory;
use Dizvestnov\LaravelMaxBot\Facades\MaxBot;
use Dizvestnov\LaravelMaxBot\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_max_bot_client_interface_resolves_from_container(): void
    {
        $client = $this->app->make(MaxBotClientInterface::class);

        $this->assertInstanceOf(MaxBotClientInterface::class, $client);
    }

    public function test_max_bot_client_resolves_from_container(): void
    {
        $client = $this->app->make(MaxBotClient::class);

        $this->assertInstanceOf(MaxBotClient::class, $client);
    }

    public function test_update_event_factory_resolves_from_container(): void
    {
        $factory = $this->app->make(UpdateEventFactory::class);

        $this->assertInstanceOf(UpdateEventFactory::class, $factory);
    }

    public function test_facade_resolves_client(): void
    {
        $this->assertInstanceOf(MaxBotClientInterface::class, MaxBot::getFacadeRoot());
    }

    public function test_config_token_is_accessible(): void
    {
        $this->assertSame('test-token-12345', config('max-bot.token'));
    }

    public function test_webhook_route_is_registered(): void
    {
        $this->assertTrue($this->app['router']->has('max-bot.webhook'));
    }

    public function test_alias_resolves_client(): void
    {
        $client = $this->app->make('max-bot.client');

        $this->assertInstanceOf(MaxBotClientInterface::class, $client);
    }
}
