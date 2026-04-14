# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-04-14

### Added
- Initial release for PHP 7.4/8.0 + Laravel 8/9
- `MaxBotClient` implementing full MAX Bot API
- Fluent `OutgoingMessage` builder with immutability via `clone`
- `Keyboard` builder with fluent row API
- Six button types: `CallbackButton`, `LinkButton`, `RequestContactButton`, `RequestGeoLocationButton`, `MessageButton`, `ClipboardButton`
- Ten event types mapped via `UpdateEventFactory`
- `ProcessWebhook` queued job
- `VerifyMaxBotSignature` middleware with HMAC-SHA256
- `WebhookController` with optional queue support
- Artisan commands: `max-bot:webhook:set`, `max-bot:webhook:info`, `max-bot:webhook:remove`, `max-bot:poll`
- `StateManager` for conversation state via cache
- `MaxBot` facade
- Full test suite (Unit + Feature)
