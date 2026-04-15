<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Tests;

use Dizvestnov\LaravelMaxBot\Facades\MaxBot;
use Dizvestnov\LaravelMaxBot\MaxBotServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [MaxBotServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'MaxBot' => MaxBot::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('max-bot.token', 'test-token-12345');
        $app['config']->set('max-bot.webhook.secret', null);
        $app['config']->set('max-bot.queue.enabled', false);
        $app['config']->set('max-bot.webhook.route.enabled', true);
        $app['config']->set('max-bot.webhook.route.path', 'max-bot/webhook');
        $app['config']->set('max-bot.webhook.route.middleware', ['api']);
        $app['config']->set('max-bot.http', [
            'base_uri' => 'https://platform-api.max.ru',
            'timeout' => 30,
        ]);
    }
}
