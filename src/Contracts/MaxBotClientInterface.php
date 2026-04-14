<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Contracts;

interface MaxBotClientInterface
{
    public function getBotInfo(): array;

    public function editBotInfo(array $data): array;

    public function sendMessage(array $params): array;

    public function editMessage(array $params): array;

    public function deleteMessage(string $messageId): array;

    public function getMessage(string $messageId): array;

    public function getMessages(array $params = []): array;

    public function answerOnCallback(array $params): array;

    public function getChats(array $params = []): array;

    public function getChat(int $chatId): array;

    public function editChat(int $chatId, array $data): array;

    public function deleteChat(int $chatId): array;

    public function sendAction(int $chatId, string $action): array;

    public function getPinnedMessage(int $chatId): array;

    public function pinMessage(int $chatId, array $params): array;

    public function unpinMessage(int $chatId): array;

    public function getMembership(int $chatId): array;

    public function leaveChat(int $chatId): array;

    public function getAdmins(int $chatId): array;

    public function addAdmins(int $chatId, array $userIds): array;

    public function deleteAdmin(int $chatId, int $userId): array;

    public function getMembers(int $chatId, array $params = []): array;

    public function addMembers(int $chatId, array $userIds): array;

    public function deleteMember(int $chatId, int $userId): array;

    public function getSubscriptions(): array;

    public function subscribe(array $params): array;

    public function unsubscribe(): array;

    public function getUpdates(array $params = []): array;

    public function getUploadUrl(string $type): array;

    public function getVideoDetails(string $videoToken): array;
}
