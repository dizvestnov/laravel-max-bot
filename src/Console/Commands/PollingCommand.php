<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Console\Commands;

use {YourVendor}\LaravelMaxBot\Contracts\MaxBotClientInterface;
use {YourVendor}\LaravelMaxBot\Events\UpdateEventFactory;
use {YourVendor}\LaravelMaxBot\Exceptions\MaxBotException;
use Illuminate\Console\Command;

class PollingCommand extends Command
{
    protected $signature   = 'max-bot:poll {--timeout=30 : Long polling timeout in seconds} {--limit=100 : Max updates per request}';
    protected $description = 'Start MAX Bot long polling';

    private MaxBotClientInterface $client;
    private UpdateEventFactory $factory;
    private bool $running = true;

    public function __construct(MaxBotClientInterface $client, UpdateEventFactory $factory)
    {
        parent::__construct();
        $this->client  = $client;
        $this->factory = $factory;
    }

    public function handle(): int
    {
        $this->info('Starting MAX Bot long polling. Press Ctrl+C to stop.');

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, function (): void {
                $this->running = false;
            });
            pcntl_signal(SIGINT, function (): void {
                $this->running = false;
            });
        }

        $marker  = null;
        $timeout = (int) $this->option('timeout');
        $limit   = (int) $this->option('limit');

        while ($this->running) {
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            try {
                $params = [
                    'timeout' => $timeout,
                    'limit'   => $limit,
                ];

                if ($marker !== null) {
                    $params['marker'] = $marker;
                }

                $response = $this->client->getUpdates($params);
                $updates  = $response['updates'] ?? [];

                foreach ($updates as $update) {
                    $event = $this->factory->make($update);

                    if ($event !== null) {
                        event($event);
                    }
                }

                if (isset($response['marker'])) {
                    $marker = $response['marker'];
                }
            } catch (MaxBotException $e) {
                $this->error('MAX Bot API error: ' . $e->getMessage());
                sleep(5);
            }
        }

        $this->info('Polling stopped.');

        return self::SUCCESS;
    }
}
