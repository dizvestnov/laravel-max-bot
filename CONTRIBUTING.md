# Contributing

## Branches

- `1.x` — PHP 7.4/8.0, Laravel 8/9. All code must be PHP 7.4 compatible. No `match`, no `enum`, no `readonly`.
- `main` (3.x) — PHP 8.2+, Laravel 12/13. Modern syntax encouraged.

Missing `2.x` (Laravel 10–11) is intentional. PRs welcome.

## Requirements for PRs

- Tests for all new features
- `./vendor/bin/phpunit` passes
- `./vendor/bin/pint` passes
- `./vendor/bin/phpstan analyse` passes (level 5)
- PHP 7.4 compat in `1.x` branch

## New button type

1. Create `src/Buttons/YourButton.php` — `final class`, implements `ButtonInterface`, private constructor, static `make()`
2. Add test in `tests/Unit/Buttons/CallbackButtonTest.php`

## New API method

1. Add to `MaxBotClientInterface`
2. Implement in `MaxBotClient`
3. Add facade docblock `@method`
4. Add test in `tests/Unit/Api/MaxBotClientTest.php`

## New update type

1. Create event class in `src/Events/` extending `MaxBotEvent`
2. Register in `UpdateEventFactory::$map`
3. Add test in `tests/Unit/Events/UpdateEventFactoryTest.php`
