<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Console\Commands;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Illuminate\Console\Command;

class WebhookSetCommand extends Command
{
    protected $signature = 'max-bot:webhook:set {url : The webhook URL}';

    protected $description = 'Set the MAX Bot webhook URL';

    private MaxBotClientInterface $client;

    public function __construct(MaxBotClientInterface $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle(): int
    {
        $url = (string) $this->argument('url');

        $params = ['url' => $url];

        $version = config('max-bot.webhook.version');

        if ($version !== null) {
            $params['version'] = $version;
        }

        $result = $this->client->subscribe($params);

        if (isset($result['ok']) && $result['ok'] === true) {
            $this->info('Webhook set successfully to: '.$url);

            return self::SUCCESS;
        }

        $this->error('Failed to set webhook: '.($result['message'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
