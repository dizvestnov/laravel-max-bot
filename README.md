# Laravel MAX Bot

[![Tests](https://github.com/dizvestnov/laravel-max-bot/actions/workflows/tests.yml/badge.svg)](https://github.com/dizvestnov/laravel-max-bot/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/packagist/php-v/dizvestnov/laravel-max-bot)](https://packagist.org/packages/dizvestnov/laravel-max-bot)
[![License](https://img.shields.io/github/license/dizvestnov/laravel-max-bot)](LICENSE)

Laravel-пакет для работы с [MAX messenger](https://max.ru) Bot API.

---

## Совместимость

| Ветка | Laravel  | PHP              | Testbench |
|-------|----------|------------------|-----------|
| 1.x   | 8, 9     | 7.4, 8.0         | 6, 7      |
| 3.x   | 12, 13   | 8.2, 8.3, 8.4    | 10, 11    |

---

## Установка

```bash
composer require dizvestnov/laravel-max-bot
```

Опубликовать конфиг:

```bash
php artisan vendor:publish --tag=max-bot-config
```

Добавить в `.env`:

```env
MAX_BOT_TOKEN=ваш_токен_бота
```

Токен выдаётся при создании бота через @MaxBotFather в MAX.

---

## Настройка

Файл `config/max-bot.php` после публикации:

```php
return [
    'token' => env('MAX_BOT_TOKEN', ''),

    'http' => [
        'base_uri' => env('MAX_BOT_BASE_URI', 'https://platform-api.max.ru'),
        'timeout'  => (int) env('MAX_BOT_TIMEOUT', 30),
        'retry'    => [
            'times' => 3,
            'sleep' => 100, // базовая задержка в мс (экспоненциальный backoff)
        ],
    ],

    'webhook' => [
        'secret'  => env('MAX_BOT_WEBHOOK_SECRET', null),
        'version' => env('MAX_BOT_WEBHOOK_VERSION', null),
        'route'   => [
            'enabled'    => true,
            'path'       => env('MAX_BOT_WEBHOOK_PATH', 'max-bot/webhook'),
            'middleware' => ['api'],
        ],
    ],

    'queue' => [
        'enabled'    => (bool) env('MAX_BOT_QUEUE_ENABLED', false),
        'connection' => env('MAX_BOT_QUEUE_CONNECTION', null),
        'queue'      => env('MAX_BOT_QUEUE_NAME', 'default'),
    ],
];
```

### Переменные окружения

| Переменная | По умолчанию | Описание |
|---|---|---|
| `MAX_BOT_TOKEN` | — | Токен бота (обязательно) |
| `MAX_BOT_BASE_URI` | `https://platform-api.max.ru` | Базовый URL API |
| `MAX_BOT_TIMEOUT` | `30` | Таймаут HTTP-запросов (сек) |
| `MAX_BOT_WEBHOOK_SECRET` | `null` | HMAC-секрет для проверки подписи |
| `MAX_BOT_WEBHOOK_PATH` | `max-bot/webhook` | URL-путь для вебхука |
| `MAX_BOT_QUEUE_ENABLED` | `false` | Обрабатывать обновления через очередь |
| `MAX_BOT_QUEUE_CONNECTION` | `null` | Соединение очереди (null = дефолтное) |
| `MAX_BOT_QUEUE_NAME` | `default` | Имя очереди |

---

## Отправка сообщений

### Fluent-builder (рекомендуется)

```php
use Dizvestnov\LaravelMaxBot\Messages\OutgoingMessage;

// Простое текстовое сообщение пользователю
OutgoingMessage::create('Привет!')
    ->to($userId)
    ->send();

// Сообщение в чат
OutgoingMessage::create('Всем привет!')
    ->inChat($chatId)
    ->send();

// Markdown-форматирование
OutgoingMessage::create('**Жирный** и _курсив_')
    ->to($userId)
    ->markdown()
    ->send();

// HTML-форматирование
OutgoingMessage::create('<b>Жирный</b>')
    ->to($userId)
    ->html()
    ->send();

// Ответ на конкретное сообщение
OutgoingMessage::create('Ответ')
    ->to($userId)
    ->replyTo($messageId)
    ->send();
```

### Через фасад (низкоуровневый доступ)

```php
use Dizvestnov\LaravelMaxBot\Facades\MaxBot;

MaxBot::sendMessage([
    'recipient' => ['user_id' => $userId],
    'text'      => 'Привет!',
]);
```

---

## Клавиатуры и кнопки

### Типы кнопок

| Класс | Описание |
|---|---|
| `CallbackButton` | Callback-кнопка с payload |
| `LinkButton` | Ссылка на URL |
| `RequestContactButton` | Запрос контакта пользователя |
| `RequestGeoLocationButton` | Запрос геолокации |
| `MessageButton` | Кнопка, отправляющая текст в чат |
| `ClipboardButton` | Копирует текст в буфер обмена |

### Пример клавиатуры

```php
use Dizvestnov\LaravelMaxBot\Keyboard;
use Dizvestnov\LaravelMaxBot\Buttons\CallbackButton;
use Dizvestnov\LaravelMaxBot\Buttons\LinkButton;
use Dizvestnov\LaravelMaxBot\Buttons\RequestContactButton;
use Dizvestnov\LaravelMaxBot\Buttons\RequestGeoLocationButton;

$keyboard = Keyboard::make()
    ->row(
        CallbackButton::make('Да', 'answer_yes'),
        CallbackButton::make('Нет', 'answer_no'),
    )
    ->row(
        LinkButton::make('Наш сайт', 'https://example.com'),
    )
    ->row(
        RequestContactButton::make('Поделиться контактом'),
        RequestGeoLocationButton::make('Отправить геолокацию'),
    );

OutgoingMessage::create('Выберите вариант:')
    ->to($userId)
    ->withKeyboard($keyboard)
    ->send();
```

---

## Вебхуки

### 1. Настройка маршрута

Маршрут регистрируется автоматически. По умолчанию: `POST /max-bot/webhook`.

Изменить путь через `.env`:

```env
MAX_BOT_WEBHOOK_PATH=my-bot/updates
```

### 2. Защита подписью (рекомендуется)

```env
MAX_BOT_WEBHOOK_SECRET=ваш_секрет
```

Middleware `VerifyMaxBotSignature` проверяет HMAC-подпись каждого запроса и возвращает `403` при несовпадении.

### 3. Установить URL вебхука

```bash
php artisan max-bot:webhook:set https://yourdomain.com/max-bot/webhook
```

### 4. Обработка событий

Зарегистрируйте слушатели в `EventServiceProvider`:

```php
use Dizvestnov\LaravelMaxBot\Events\MessageReceived;
use Dizvestnov\LaravelMaxBot\Events\CallbackReceived;
use Dizvestnov\LaravelMaxBot\Events\BotStarted;

protected $listen = [
    MessageReceived::class => [
        App\Listeners\HandleMessage::class,
    ],
    CallbackReceived::class => [
        App\Listeners\HandleCallback::class,
    ],
    BotStarted::class => [
        App\Listeners\WelcomeNewUser::class,
    ],
];
```

Пример слушателя:

```php
namespace App\Listeners;

use Dizvestnov\LaravelMaxBot\Events\MessageReceived;
use Dizvestnov\LaravelMaxBot\Messages\OutgoingMessage;

class HandleMessage
{
    public function handle(MessageReceived $event): void
    {
        $text   = $event->getText();
        $userId = $event->getSenderId();

        OutgoingMessage::create("Вы написали: {$text}")
            ->to($userId)
            ->send();
    }
}
```

### 5. Доступные события

| Класс | Когда срабатывает |
|---|---|
| `MessageReceived` | Получено новое сообщение |
| `MessageEdited` | Сообщение отредактировано |
| `MessageRemoved` | Сообщение удалено |
| `CallbackReceived` | Нажата callback-кнопка |
| `BotStarted` | Пользователь запустил бота |
| `BotAdded` | Бот добавлен в чат |
| `BotRemoved` | Бот удалён из чата |
| `UserAdded` | Пользователь добавлен в чат |
| `UserRemoved` | Пользователь удалён из чата |
| `ChatTitleChanged` | Изменено название чата |

Методы, доступные во всех событиях (наследуются от `MaxBotEvent`):

```php
$event->getUpdateType();  // тип обновления
$event->getChatId();      // ID чата (или null)
$event->getTimestamp();   // unix-timestamp
$event->update;           // исходный массив обновления
```

`MessageReceived` дополнительно:

```php
$event->getText();       // текст сообщения
$event->getSenderId();   // ID отправителя
$event->getMessage();    // полный массив сообщения
```

### 6. Обработка через очередь

```env
MAX_BOT_QUEUE_ENABLED=true
MAX_BOT_QUEUE_CONNECTION=redis
MAX_BOT_QUEUE_NAME=bot
```

---

## Long Polling

Для локальной разработки или простых сценариев без публичного URL:

```bash
php artisan max-bot:poll
```

Команда получает обновления циклически через API и диспатчит те же события, что и вебхук.

---

## Управление состоянием разговора

`StateManager` хранит состояние и данные пользователя в кэше Laravel.

```php
use Dizvestnov\LaravelMaxBot\Conversation\StateManager;

class RegistrationListener
{
    public function __construct(private StateManager $state) {}

    public function handle(MessageReceived $event): void
    {
        $userId = $event->getSenderId();
        $step   = $this->state->getState($userId);

        if ($step === null) {
            $this->state->setState($userId, 'ask_name');
            OutgoingMessage::create('Как вас зовут?')->to($userId)->send();
            return;
        }

        if ($step === 'ask_name') {
            $this->state->mergeData($userId, ['name' => $event->getText()]);
            $this->state->setState($userId, 'ask_email');
            OutgoingMessage::create('Ваш email?')->to($userId)->send();
            return;
        }

        if ($step === 'ask_email') {
            $data = $this->state->getData($userId);
            // $data['name'] и $event->getText() — готово
            $this->state->clearState($userId);
            OutgoingMessage::create('Регистрация завершена!')->to($userId)->send();
        }
    }
}
```

### Методы StateManager

```php
$state->getState(int $userId): ?string
$state->setState(int $userId, string $state, int $ttl = 3600): void
$state->clearState(int $userId): void

$state->getData(int $userId): array
$state->setData(int $userId, array $data, int $ttl = 3600): void
$state->mergeData(int $userId, array $data): void
```

---

## Artisan-команды

| Команда | Описание |
|---|---|
| `max-bot:webhook:set {url}` | Установить URL вебхука |
| `max-bot:webhook:info` | Показать текущий вебхук |
| `max-bot:webhook:remove` | Удалить вебхук |
| `max-bot:poll` | Запустить long polling |

---

## Полный API-клиент

Все методы доступны через фасад `MaxBot::` или через DI `MaxBotClientInterface`:

```php
use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;

class MyService
{
    public function __construct(private MaxBotClientInterface $bot) {}
}
```

**Бот**

```php
MaxBot::getBotInfo();
MaxBot::editBotInfo(['name' => 'Новое имя']);
```

**Сообщения**

```php
MaxBot::sendMessage([...]);
MaxBot::editMessage([...]);
MaxBot::deleteMessage($messageId);
MaxBot::getMessage($messageId);
MaxBot::getMessages(['chat_id' => $chatId]);
MaxBot::answerOnCallback(['callback_id' => $id, 'text' => 'OK']);
```

**Чаты**

```php
MaxBot::getChats();
MaxBot::getChat($chatId);
MaxBot::editChat($chatId, ['title' => 'Новое название']);
MaxBot::deleteChat($chatId);
MaxBot::sendAction($chatId, 'typing');
MaxBot::getPinnedMessage($chatId);
MaxBot::pinMessage($chatId, ['message_id' => $messageId]);
MaxBot::unpinMessage($chatId);
```

**Участники**

```php
MaxBot::getMembership($chatId);
MaxBot::leaveChat($chatId);
MaxBot::getAdmins($chatId);
MaxBot::addAdmins($chatId, [$userId]);
MaxBot::deleteAdmin($chatId, $userId);
MaxBot::getMembers($chatId);
MaxBot::addMembers($chatId, [$userId]);
MaxBot::deleteMember($chatId, $userId);
```

**Подписки и обновления**

```php
MaxBot::getSubscriptions();
MaxBot::subscribe(['url' => 'https://...', 'secret' => '...']);
MaxBot::unsubscribe();
MaxBot::getUpdates(['limit' => 10]);
```

**Медиа**

```php
MaxBot::getUploadUrl('image');
MaxBot::getVideoDetails($videoToken);
```

---

## Тестирование

Мокайте `MaxBotClientInterface`:

```php
use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;

public function test_bot_replies(): void
{
    $this->mock(MaxBotClientInterface::class, function ($mock) {
        $mock->shouldReceive('sendMessage')
            ->once()
            ->with(\Mockery::on(fn($p) => $p['text'] === 'Привет!'))
            ->andReturn(['message_id' => 'abc123']);
    });

    // вызвать логику, которая отправляет сообщение...
}
```

---

## Статус проекта

> **Пакет находится в активной разработке. Это первый публичный релиз.**
>
> API может изменяться до выхода стабильных версий 1.0.0 / 3.0.0.
> Используйте в production с осторожностью и закрепляйте конкретную версию в `composer.json`.

---

## Сообщить об ошибке

1. **Поищите в существующих issues** — возможно, проблема уже известна:
   [github.com/dizvestnov/laravel-max-bot/issues](https://github.com/dizvestnov/laravel-max-bot/issues)

2. **Создайте новый issue**, указав:
   - версию пакета (`composer show dizvestnov/laravel-max-bot`)
   - версию PHP и Laravel
   - минимальный воспроизводимый пример кода
   - ожидаемое поведение и что происходит на самом деле
   - полный текст ошибки / стектрейс

3. **Для вопросов** используйте
   [Discussions](https://github.com/dizvestnov/laravel-max-bot/discussions), а не Issues.

## Участие в разработке

1. Форкните репозиторий и создайте ветку от `main` (3.x) или `1.x`
2. Напишите или обновите тесты — покрытие обязательно
3. Убедитесь, что все проверки проходят локально:
   ```bash
   ./vendor/bin/phpunit          # тесты
   ./vendor/bin/pint             # стиль кода (3.x)
   ./vendor/bin/php-cs-fixer fix # стиль кода (1.x)
   ./vendor/bin/phpstan analyse  # статический анализ
   ```
4. Откройте Pull Request с описанием изменений

Ветка `2.x` намеренно пропущена — PR приветствуются.

---

## Лицензия

[MIT](LICENSE)
