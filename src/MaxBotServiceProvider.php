<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot;

use {YourVendor}\LaravelMaxBot\Api\MaxBotClient;
use {YourVendor}\LaravelMaxBot\Console\Commands\PollingCommand;
use {YourVendor}\LaravelMaxBot\Console\Commands\WebhookInfoCommand;
use {YourVendor}\LaravelMaxBot\Console\Commands\WebhookRemoveCommand;
use {YourVendor}\LaravelMaxBot\Console\Commands\WebhookSetCommand;
use {YourVendor}\LaravelMaxBot\Contracts\MaxBotClientInterface;
use {YourVendor}\LaravelMaxBot\Conversation\StateManager;
use {YourVendor}\LaravelMaxBot\Events\UpdateEventFactory;
use Illuminate\Support\ServiceProvider;

class MaxBotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/max-bot.php', 'max-bot');

        $this->app->singleton(MaxBotClient::class, function ($app) {
            return new MaxBotClient(
                $app['config']['max-bot.token'],
                $app['config']['max-bot.http']
            );
        });

        $this->app->bind(MaxBotClientInterface::class, MaxBotClient::class);
        $this->app->alias(MaxBotClientInterface::class, 'max-bot.client');

        $this->app->singleton(UpdateEventFactory::class);

        $this->app->singleton(StateManager::class, function ($app) {
            return new StateManager($app['cache.store']);
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
        $this->registerCommands();
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/max-bot.php' => config_path('max-bot.php'),
            ], 'max-bot-config');
        }
    }

    private function registerRoutes(): void
    {
        if (config('max-bot.webhook.route.enabled')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/webhook.php');
        }
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookSetCommand::class,
                WebhookInfoCommand::class,
                WebhookRemoveCommand::class,
                PollingCommand::class,
            ]);
        }
    }
}
