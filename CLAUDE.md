# Laravel MAX Bot — Claude Code Init

## Project overview

Laravel package for MAX messenger Bot API (max.ru / platform-api.max.ru).
Цель: clean, testable, idiomatic Laravel package. Ориентир — spatie/* пакеты.

Vendor: TBD. Namespace: `YourVendor\LaravelMaxBot`.

---

## Versioning strategy (CRITICAL)

```
main (3.x)  ←── default branch, актуальный код
1.x         ←── legacy branch
```

| Ветка | PHP         | Laravel       | Testbench      |
|-------|-------------|---------------|----------------|
| `1.x` | ^7.4\|^8.0  | ^8.0\|^9.0    | ^6.0\|^7.0     |
| `3.x` | ^8.2        | ^12.0\|^13.0  | ^10.0\|^11.0   |

Ветка `2.x` намеренно пропущена — community PR welcome.

**Первый вопрос в каждой сессии:** ветка `1.x` или `3.x`?

---

## OOP Design Patterns (применять в обеих ветках)

### 1. Interface Segregation (ISP / SOLID)

Публичный контракт через интерфейс — пользователь мокает интерфейс, не класс:

```php
// src/Contracts/MaxBotClientInterface.php
interface MaxBotClientInterface
{
    public function getBotInfo(): array;
    public function sendMessage(array $params): array;
    public function getUpdates(array $params = []): array;
    // ...все публичные методы
}

// src/Api/MaxBotClient.php
class MaxBotClient implements MaxBotClientInterface { ... }
```

ServiceProvider биндит интерфейс:
```php
$this->app->bind(MaxBotClientInterface::class, MaxBotClient::class);
$this->app->alias(MaxBotClientInterface::class, 'max-bot.client');
```

Пользователь в тестах:
```php
$this->mock(MaxBotClientInterface::class, function ($mock) {
    $mock->shouldReceive('sendMessage')->once()->andReturn(['ok' => true]);
});
```

### 2. Fluent Builder / Method Chaining (как spatie)

Не передавать голый array — строить объект-сообщение:

```php
// src/Messages/OutgoingMessage.php
class OutgoingMessage
{
    private ?int $userId = null;
    private ?int $chatId = null;
    private string $text = '';
    private array $attachments = [];
    private ?string $format = null;

    public static function create(string $text = ''): self
    {
        $instance = new self();
        $instance->text = $text;
        return $instance;
    }

    public function to(int $userId): self
    {
        $clone = clone $this; // иммутабельность через clone
        $clone->userId = $userId;
        return $clone;
    }

    public function inChat(int $chatId): self
    {
        $clone = clone $this;
        $clone->chatId = $chatId;
        return $clone;
    }

    public function withButton(ButtonInterface $button): self
    {
        $clone = clone $this;
        $clone->attachments[] = $button;
        return $clone;
    }

    public function markdown(): self
    {
        $clone = clone $this;
        $clone->format = 'markdown';
        return $clone;
    }

    public function toArray(): array { ... }

    // Отправка через фасад (convenience)
    public function send(): array
    {
        return app(MaxBotClientInterface::class)->sendMessage($this->toArray());
    }
}
```

Использование:
```php
OutgoingMessage::create('Привет!')
    ->to($userId)
    ->withButton(CallbackButton::make('Нажми', 'btn_pressed'))
    ->markdown()
    ->send();
```

### 3. Value Objects для кнопок (иммутабельные)

```php
// src/Contracts/ButtonInterface.php
interface ButtonInterface
{
    public function toArray(): array;
}

// src/Buttons/CallbackButton.php
class CallbackButton implements ButtonInterface
{
    private function __construct(
        // 1.x: обычные свойства; 3.x: readonly
        private string $text,
        private string $payload,
    ) {}

    public static function make(string $text, string $payload): self
    {
        return new self($text, $payload);
    }

    public function toArray(): array
    {
        return [
            'type'    => 'callback',
            'text'    => $this->text,
            'payload' => $this->payload,
        ];
    }
}
```

Keyboard builder:
```php
// src/Keyboard.php
class Keyboard
{
    private array $rows = [];

    public static function make(): self
    {
        return new self();
    }

    public function row(ButtonInterface ...$buttons): self
    {
        $clone = clone $this;
        $clone->rows[] = array_map(fn($b) => $b->toArray(), $buttons);
        return $clone;
    }

    public function toArray(): array
    {
        return [
            'type'    => 'inline_keyboard',
            'payload' => ['buttons' => $this->rows],
        ];
    }
}

// Использование:
$keyboard = Keyboard::make()
    ->row(
        CallbackButton::make('Да', 'yes'),
        CallbackButton::make('Нет', 'no'),
    )
    ->row(
        LinkButton::make('Сайт', 'https://example.com'),
    );
```

### 4. Factory для парсинга входящих обновлений

Вместо switch/if в ProcessWebhook — отдельный фабричный класс:

```php
// src/Events/UpdateEventFactory.php
class UpdateEventFactory
{
    // В 1.x: array map + array_key_exists
    // В 3.x: enum + match
    public function make(array $update): ?object
    {
        $map = [
            'message_created'    => MessageReceived::class,
            'message_edited'     => MessageEdited::class,
            'message_removed'    => MessageRemoved::class,
            'bot_started'        => BotStarted::class,
            'bot_added'          => BotAdded::class,
            'bot_removed'        => BotRemoved::class,
            'user_added'         => UserAdded::class,
            'user_removed'       => UserRemoved::class,
            'chat_title_changed' => ChatTitleChanged::class,
            'message_callback'   => CallbackReceived::class,
        ];

        $type = $update['update_type'] ?? null;
        $eventClass = $map[$type] ?? null;

        return $eventClass ? new $eventClass($update) : null;
    }
}
```

### 5. Base Event класс (DRY)

Вместо повторения одного и того же в каждом Event:

```php
// src/Events/MaxBotEvent.php  (базовый)
abstract class MaxBotEvent
{
    public function __construct(public readonly array $update) {}  // 3.x
    // 1.x: public $update; + конструктор без readonly

    public function getUpdateType(): string
    {
        return $this->update['update_type'] ?? '';
    }

    public function getChatId(): ?int
    {
        return $this->update['chat_id'] ?? null;
    }

    public function getTimestamp(): int
    {
        return $this->update['timestamp'] ?? 0;
    }
}

// src/Events/MessageReceived.php
class MessageReceived extends MaxBotEvent
{
    public function getMessage(): array
    {
        return $this->update['message'] ?? [];
    }

    public function getText(): ?string
    {
        return $this->update['message']['body']['text'] ?? null;
    }

    public function getSenderId(): ?int
    {
        return $this->update['message']['sender']['user_id'] ?? null;
    }
}
```

### 6. Single Responsibility в ServiceProvider

ServiceProvider только регистрирует — не содержит логику:

```php
// register() — только биндинги
public function register(): void
{
    $this->mergeConfigFrom(__DIR__ . '/../config/max-bot.php', 'max-bot');

    $this->app->singleton(MaxBotClient::class, function ($app) {
        return new MaxBotClient(
            token: $app['config']['max-bot.token'],   // 3.x named args
            config: $app['config']['max-bot.http'],
        );
    });

    $this->app->bind(MaxBotClientInterface::class, MaxBotClient::class);
    $this->app->alias(MaxBotClientInterface::class, 'max-bot.client');
    $this->app->singleton(UpdateEventFactory::class);
    $this->app->singleton(StateManager::class);
}

// boot() — только публикации и регистрации
public function boot(): void
{
    $this->registerPublishing();
    $this->registerRoutes();
    $this->registerCommands();
}

private function registerPublishing(): void { ... }
private function registerRoutes(): void { ... }
private function registerCommands(): void { ... }
```

### 7. Dependency Injection везде (не фасады внутри пакета)

Внутри src/ не использовать фасады — только DI. Фасад только для пользователя пакета.

```php
// ПЛОХО — внутри пакета
class WebhookController
{
    public function handle(Request $request)
    {
        MaxBot::sendMessage(...);  // фасад внутри пакета = плохо
    }
}

// ХОРОШО
class WebhookController
{
    public function __construct(
        private MaxBotClientInterface $client,
        private UpdateEventFactory $factory,
    ) {}
}
```

---

## PHP version rules

### 1.x — PHP 7.4 FORBIDDEN syntax

- `match` — **только в 3.x**, в 1.x используй `switch` или array map
- `enum` — нет, используй class constants
- `readonly` — нет, обычные свойства
- Named arguments `foo(name: 'x')` — нет
- Union types `int|string` в сигнатурах — нет, docblock
- `str_contains/str_starts_with` — нет, `strpos !== false`
- Trailing comma в параметрах функции — нет
- `fibers`, `never` — нет
- Arrow functions `fn()` — **можно**, появились в PHP 7.4
- Typed properties — **можно**, PHP 7.4+
- Null coalescing assignment `??=` — **можно**, PHP 7.4+

### 3.x — PHP 8.2+ ENCOURAGED syntax

- `match` — **да**, с exhaustive enum не нужен default
- `enum` — **да** для UpdateType, ButtonType и т.д.
- `readonly` — **да** в DTO/Value Objects
- Named arguments — **да**
- Union/intersection types — **да**
- Constructor promotion — **да**
- `str_contains/str_starts_with` — **да**
- Fibers — только если реально нужны

---

## Package structure

```
src/
  MaxBotServiceProvider.php
  Contracts/
    MaxBotClientInterface.php    # ISP — основной контракт
    ButtonInterface.php          # контракт для кнопок
  Facades/
    MaxBot.php
  Api/
    MaxBotClient.php             # implements MaxBotClientInterface
  Messages/
    OutgoingMessage.php          # Fluent builder
  Keyboard.php                   # Fluent keyboard builder
  Buttons/                       # Value Objects
    CallbackButton.php
    LinkButton.php
    RequestContactButton.php
    RequestGeoLocationButton.php
    MessageButton.php
    ClipboardButton.php
  DTO/                           # Incoming data
    Message.php
    User.php
    Chat.php
    Update.php
    BotInfo.php
  Events/
    MaxBotEvent.php              # Abstract base (DRY)
    MessageReceived.php
    MessageEdited.php
    MessageRemoved.php
    BotStarted.php
    BotAdded.php
    BotRemoved.php
    UserAdded.php
    UserRemoved.php
    ChatTitleChanged.php
    CallbackReceived.php
  Events/
    UpdateEventFactory.php       # Factory pattern
  Jobs/
    ProcessWebhook.php
  Http/
    Controllers/
      WebhookController.php
    Middleware/
      VerifyMaxBotSignature.php
  Console/
    Commands/
      WebhookSetCommand.php
      WebhookInfoCommand.php
      WebhookRemoveCommand.php
      PollingCommand.php
  Conversation/
    StateManager.php
  Exceptions/
    MaxBotException.php
    ApiException.php
    UnauthorizedException.php
    RateLimitException.php
    WebhookSignatureException.php
config/
  max-bot.php
routes/
  webhook.php
tests/
  TestCase.php
  Unit/
    Api/MaxBotClientTest.php
    Messages/OutgoingMessageTest.php
    Buttons/CallbackButtonTest.php
    Keyboard/KeyboardTest.php
    Events/UpdateEventFactoryTest.php
    Conversation/StateManagerTest.php
  Feature/
    WebhookTest.php
    ServiceProviderTest.php
    FacadeTest.php
```

---

## MAX Bot API

Base URL: `https://platform-api.max.ru`
Auth: `Authorization: <token>` header (НЕ query param)
Rate limit: 30 rps
Retry: 3 попытки, exponential backoff для 429 и 503

---

## Testbench matrix

| Laravel | Testbench | PHP min |
|---------|-----------|---------|
| 8.x     | ^6.0      | 7.4     |
| 9.x     | ^7.0      | 8.0     |
| 10.x    | ^8.0      | 8.1     |
| 11.x    | ^9.0      | 8.2     |
| 12.x    | ^10.0     | 8.2     |
| 13.x    | ^11.0     | 8.3     |

---

## Coding standards (spatie-style)

- PSR-12 via Laravel Pint
- `declare(strict_types=1)` в каждом файле
- PHPDoc только там где типы неочевидны или нужна дополнительная документация
- `final` классы где нет намерения наследоваться (Value Objects, Buttons)
- Exceptions через специфические классы, не `new \Exception`
- Нет `var_dump`, `dd`, `dump` в src/
- Нет фасадов внутри src/ — только DI
- Конфиг через `config('max-bot.*')`, не хардкод

## Notes for Claude Code

- При добавлении API метода: интерфейс → клиент → фасад docblock → тест
- При добавлении нового типа кнопки: implements ButtonInterface, final class, static make()
- В 1.x: match = ЗАПРЕЩЁН → switch или array map
- В 3.x: match = приветствуется
- OutgoingMessage и Keyboard — иммутабельные через clone, не мутировать $this
- ProcessWebhook делегирует создание события UpdateEventFactory (не сам знает про маппинг)
