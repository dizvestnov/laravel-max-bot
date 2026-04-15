<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Console\Commands;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Illuminate\Console\Command;

class WebhookRemoveCommand extends Command
{
    protected $signature = 'max-bot:webhook:remove';

    protected $description = 'Remove the MAX Bot webhook subscription';

    private MaxBotClientInterface $client;

    public function __construct(MaxBotClientInterface $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle(): int
    {
        $result = $this->client->unsubscribe();

        if (isset($result['ok']) && $result['ok'] === true) {
            $this->info('Webhook subscription removed successfully.');

            return self::SUCCESS;
        }

        $this->error('Failed to remove webhook: '.($result['message'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
