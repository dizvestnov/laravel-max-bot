<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Http\Controllers;

use {YourVendor}\LaravelMaxBot\Events\UpdateEventFactory;
use {YourVendor}\LaravelMaxBot\Jobs\ProcessWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class WebhookController extends Controller
{
    private UpdateEventFactory $factory;

    public function __construct(UpdateEventFactory $factory)
    {
        $this->factory = $factory;
    }

    public function handle(Request $request): JsonResponse
    {
        $update = $request->json()->all();

        if (config('max-bot.queue.enabled')) {
            ProcessWebhook::dispatch($update)
                ->onConnection(config('max-bot.queue.connection'))
                ->onQueue(config('max-bot.queue.queue'));
        } else {
            $event = $this->factory->make($update);

            if ($event !== null) {
                event($event);
            }
        }

        return response()->json(['ok' => true]);
    }
}
