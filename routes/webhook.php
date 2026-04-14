<?php

declare(strict_types=1);

use {YourVendor}\LaravelMaxBot\Http\Controllers\WebhookController;
use {YourVendor}\LaravelMaxBot\Http\Middleware\VerifyMaxBotSignature;
use Illuminate\Support\Facades\Route;

Route::post(
    config('max-bot.webhook.route.path', 'max-bot/webhook'),
    [WebhookController::class, 'handle']
)
    ->middleware(array_merge(
        config('max-bot.webhook.route.middleware', ['api']),
        [VerifyMaxBotSignature::class]
    ))
    ->name('max-bot.webhook');
