<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Console\Commands;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Illuminate\Console\Command;

class WebhookInfoCommand extends Command
{
    protected $signature   = 'max-bot:webhook:info';
    protected $description = 'Display current MAX Bot webhook subscription info';

    private MaxBotClientInterface $client;

    public function __construct(MaxBotClientInterface $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle(): int
    {
        $result = $this->client->getSubscriptions();

        if (empty($result['subscriptions'])) {
            $this->warn('No active webhook subscriptions.');

            return self::SUCCESS;
        }

        foreach ($result['subscriptions'] as $subscription) {
            $this->line('URL     : ' . ($subscription['url'] ?? 'N/A'));
            $this->line('Time    : ' . ($subscription['time'] ?? 'N/A'));
        }

        return self::SUCCESS;
    }
}
