<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Facades;

use Dizvestnov\LaravelMaxBot\Contracts\MaxBotClientInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array getBotInfo()
 * @method static array editBotInfo(array $data)
 * @method static array sendMessage(array $params)
 * @method static array editMessage(array $params)
 * @method static array deleteMessage(string $messageId)
 * @method static array getMessage(string $messageId)
 * @method static array getMessages(array $params = [])
 * @method static array answerOnCallback(array $params)
 * @method static array getChats(array $params = [])
 * @method static array getChat(int $chatId)
 * @method static array editChat(int $chatId, array $data)
 * @method static array deleteChat(int $chatId)
 * @method static array sendAction(int $chatId, string $action)
 * @method static array getPinnedMessage(int $chatId)
 * @method static array pinMessage(int $chatId, array $params)
 * @method static array unpinMessage(int $chatId)
 * @method static array getMembership(int $chatId)
 * @method static array leaveChat(int $chatId)
 * @method static array getAdmins(int $chatId)
 * @method static array addAdmins(int $chatId, array $userIds)
 * @method static array deleteAdmin(int $chatId, int $userId)
 * @method static array getMembers(int $chatId, array $params = [])
 * @method static array addMembers(int $chatId, array $userIds)
 * @method static array deleteMember(int $chatId, int $userId)
 * @method static array getSubscriptions()
 * @method static array subscribe(array $params)
 * @method static array unsubscribe()
 * @method static array getUpdates(array $params = [])
 * @method static array getUploadUrl(string $type)
 * @method static array getVideoDetails(string $videoToken)
 *
 * @see MaxBotClientInterface
 */
class MaxBot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'max-bot.client';
    }
}
