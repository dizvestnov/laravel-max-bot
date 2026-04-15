<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Http\Middleware;

use Closure;
use Dizvestnov\LaravelMaxBot\Exceptions\WebhookSignatureException;
use Illuminate\Http\Request;

class VerifyMaxBotSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('max-bot.webhook.secret');

        if ($secret === null || $secret === '') {
            return $next($request);
        }

        $signature = $request->header('X-Max-Signature');

        if ($signature === null || $signature === '') {
            throw new WebhookSignatureException('Missing webhook signature header.');
        }

        $rawBody = $request->getContent();
        $expected = 'sha256='.hash_hmac('sha256', $rawBody, $secret);

        if (! hash_equals($expected, $signature)) {
            throw new WebhookSignatureException('Invalid webhook signature.');
        }

        return $next($request);
    }
}
