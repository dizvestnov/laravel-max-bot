<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Tests\Feature;

use {YourVendor}\LaravelMaxBot\Events\MessageReceived;
use {YourVendor}\LaravelMaxBot\Jobs\ProcessWebhook;
use {YourVendor}\LaravelMaxBot\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class WebhookTest extends TestCase
{
    private function makeUpdate(string $type = 'message_created'): array
    {
        return [
            'update_type' => $type,
            'timestamp'   => time(),
            'message'     => [
                'id'     => 'msg-1',
                'sender' => ['user_id' => 42],
                'body'   => ['text' => 'Hello'],
            ],
        ];
    }

    public function test_post_without_secret_returns_200(): void
    {
        $response = $this->postJson('/max-bot/webhook', $this->makeUpdate());

        $response->assertStatus(200)->assertJson(['ok' => true]);
    }

    public function test_post_with_invalid_signature_returns_403(): void
    {
        config(['max-bot.webhook.secret' => 'my-secret']);

        $response = $this->postJson(
            '/max-bot/webhook',
            $this->makeUpdate(),
            ['X-Max-Signature' => 'sha256=invalidsignature']
        );

        $response->assertStatus(403);
    }

    public function test_post_with_valid_signature_returns_200(): void
    {
        config(['max-bot.webhook.secret' => 'my-secret']);

        $body      = json_encode($this->makeUpdate());
        $signature = 'sha256=' . hash_hmac('sha256', $body, 'my-secret');

        $response = $this->call(
            'POST',
            '/max-bot/webhook',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-Max-Signature' => $signature],
            $body
        );

        $response->assertStatus(200);
    }

    public function test_message_received_event_is_fired(): void
    {
        Event::fake();

        $this->postJson('/max-bot/webhook', $this->makeUpdate('message_created'));

        Event::assertDispatched(MessageReceived::class);
    }

    public function test_process_webhook_job_dispatched_when_queue_enabled(): void
    {
        Queue::fake();
        config(['max-bot.queue.enabled' => true]);

        $this->postJson('/max-bot/webhook', $this->makeUpdate());

        Queue::assertPushed(ProcessWebhook::class);
    }

    public function test_unknown_update_type_does_not_fire_event(): void
    {
        Event::fake();

        $this->postJson('/max-bot/webhook', ['update_type' => 'unknown_type']);

        Event::assertNotDispatched(MessageReceived::class);
    }
}
