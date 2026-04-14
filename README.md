# Laravel MAX Bot

[![Tests](https://github.com/dizvestnov/laravel-max-bot/actions/workflows/tests.yml/badge.svg)]()
[![PHP](https://img.shields.io/packagist/php-v/dizvestnov/laravel-max-bot)]()
[![License](https://img.shields.io/github/license/dizvestnov/laravel-max-bot)]()

Laravel пакет для [MAX messenger](https://max.ru) Bot API.

## Version compatibility

| Package | Laravel | PHP            |
|---------|---------|----------------|
| 1.x     | 8, 9    | 7.4, 8.0       |
| 3.x     | 12, 13  | 8.2, 8.3, 8.4  |

## Installation

```bash
composer require dizvestnov/laravel-max-bot
```

Опубликовать конфиг:

```bash
php artisan vendor:publish --tag=max-bot-config
```

Добавить в `.env`:

```
MAX_BOT_TOKEN=your_bot_token_here
```

## Configuration

Основные параметры `config/max-bot.php`:

| Key | Default | Description |
|-----|---------|-------------|
| `token` | `''` | Bot API token |
| `http.base_uri` | `https://platform-api.max.ru` | API base URL |
| `http.timeout` | `30` | HTTP timeout |
| `webhook.secret` | `null` | HMAC secret (optional) |
| `webhook.route.path` | `max-bot/webhook` | Webhook route path |
| `queue.enabled` | `false` | Process webhooks via queue |

## Usage — sending a message

```php
use Dizvestnov\LaravelMaxBot\Messages\OutgoingMessage;
use Dizvestnov\LaravelMaxBot\Keyboard;
use Dizvestnov\LaravelMaxBot\Buttons\CallbackButton;

OutgoingMessage::create('Привет!')
    ->to($userId)
    ->withKeyboard(
        Keyboard::make()
            ->row(
                CallbackButton::make('Да', 'yes'),
                CallbackButton::make('Нет', 'no'),
            )
    )
    ->markdown()
    ->send();
```

Or via facade:

```php
use Dizvestnov\LaravelMaxBot\Facades\MaxBot;

MaxBot::sendMessage([
    'recipient' => ['user_id' => $userId],
    'text'      => 'Hello!',
]);
```

## Usage — webhook

Add to `.env`:

```
MAX_BOT_WEBHOOK_PATH=max-bot/webhook
MAX_BOT_WEBHOOK_SECRET=your_hmac_secret
```

Set webhook via Artisan:

```bash
php artisan max-bot:webhook:set https://yourdomain.com/max-bot/webhook
```

Listen for events in `EventServiceProvider`:

```php
protected $listen = [
    \Dizvestnov\LaravelMaxBot\Events\MessageReceived::class => [
        \App\Listeners\HandleIncomingMessage::class,
    ],
];
```

```php
// App\Listeners\HandleIncomingMessage
public function handle(MessageReceived $event): void
{
    $text   = $event->getText();
    $userId = $event->getSenderId();

    OutgoingMessage::create('You said: ' . $text)->to($userId)->send();
}
```

## Usage — long polling

```bash
php artisan max-bot:poll
```

## Usage — conversation state

```php
use Dizvestnov\LaravelMaxBot\Conversation\StateManager;

class RegistrationFlow
{
    public function __construct(private StateManager $state) {}

    public function handle(MessageReceived $event): void
    {
        $userId  = $event->getSenderId();
        $current = $this->state->getState($userId);

        if ($current === null) {
            $this->state->setState($userId, 'waiting_name');
            OutgoingMessage::create('Как вас зовут?')->to($userId)->send();
            return;
        }

        if ($current === 'waiting_name') {
            $this->state->mergeData($userId, ['name' => $event->getText()]);
            $this->state->setState($userId, 'waiting_email');
            OutgoingMessage::create('Ваш email?')->to($userId)->send();
        }
    }
}
```

## Usage — keyboard buttons

```php
use Dizvestnov\LaravelMaxBot\Keyboard;
use Dizvestnov\LaravelMaxBot\Buttons\CallbackButton;
use Dizvestnov\LaravelMaxBot\Buttons\LinkButton;
use Dizvestnov\LaravelMaxBot\Buttons\RequestContactButton;

$keyboard = Keyboard::make()
    ->row(
        CallbackButton::make('Callback', 'payload'),
        LinkButton::make('Website', 'https://example.com'),
    )
    ->row(
        RequestContactButton::make('Share Contact'),
    );
```

## Artisan commands

| Command | Description |
|---------|-------------|
| `max-bot:webhook:set {url}` | Set webhook URL |
| `max-bot:webhook:info` | Show current webhook |
| `max-bot:webhook:remove` | Remove webhook |
| `max-bot:poll` | Start long polling |

## Testing

Mock `MaxBotClientInterface` in your tests:

```php
use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;

$this->mock(MaxBotClientInterface::class, function ($mock) {
    $mock->shouldReceive('sendMessage')
        ->once()
        ->andReturn(['message_id' => 'test-id']);
});
```

## License

MIT
