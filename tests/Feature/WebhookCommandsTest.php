<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests\Feature;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Dizvestnov\LaravelMaxBot\Tests\TestCase;

class WebhookCommandsTest extends TestCase
{
    // --- WebhookSetCommand ---

    public function test_set_command_succeeds_when_api_returns_success_true(): void
    {
        $this->mock(MaxBotClientInterface::class, function ($mock) {
            $mock->shouldReceive('subscribe')
                ->once()
                ->andReturn(['success' => true]);
        });

        $this->artisan('max-bot:webhook:set', ['url' => 'https://example.com/hook'])
            ->assertSuccessful()
            ->expectsOutput('Webhook set successfully to: https://example.com/hook');
    }

    public function test_set_command_fails_when_api_returns_success_false(): void
    {
        $this->mock(MaxBotClientInterface::class, function ($mock) {
            $mock->shouldReceive('subscribe')
                ->once()
                ->andReturn(['success' => false, 'message' => 'Invalid URL']);
        });

        $this->artisan('max-bot:webhook:set', ['url' => 'https://bad'])
            ->assertFailed()
            ->expectsOutput('Failed to set webhook: Invalid URL');
    }

    public function test_set_command_shows_unknown_error_when_message_absent(): void
    {
        $this->mock(MaxBotClientInterface::class, function ($mock) {
            $mock->shouldReceive('subscribe')
                ->once()
                ->andReturn(['success' => false]);
        });

        $this->artisan('max-bot:webhook:set', ['url' => 'https://bad'])
            ->assertFailed()
            ->expectsOutput('Failed to set webhook: Unknown error');
    }

    // --- WebhookRemoveCommand ---

    public function test_remove_command_succeeds_when_api_returns_success_true(): void
    {
        $this->mock(MaxBotClientInterface::class, function ($mock) {
            $mock->shouldReceive('unsubscribe')
                ->once()
                ->andReturn(['success' => true]);
        });

        $this->artisan('max-bot:webhook:remove')
            ->assertSuccessful()
            ->expectsOutput('Webhook subscription removed successfully.');
    }

    public function test_remove_command_fails_when_api_returns_success_false(): void
    {
        $this->mock(MaxBotClientInterface::class, function ($mock) {
            $mock->shouldReceive('unsubscribe')
                ->once()
                ->andReturn(['success' => false, 'message' => 'No subscription found']);
        });

        $this->artisan('max-bot:webhook:remove')
            ->assertFailed()
            ->expectsOutput('Failed to remove webhook: No subscription found');
    }
}
