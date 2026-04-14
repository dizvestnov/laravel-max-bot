<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Enums;

final class UpdateType
{
    public const MESSAGE_CREATED   = 'message_created';
    public const MESSAGE_EDITED    = 'message_edited';
    public const MESSAGE_REMOVED   = 'message_removed';
    public const BOT_STARTED       = 'bot_started';
    public const BOT_ADDED         = 'bot_added';
    public const BOT_REMOVED       = 'bot_removed';
    public const USER_ADDED        = 'user_added';
    public const USER_REMOVED      = 'user_removed';
    public const CHAT_TITLE_CHANGED = 'chat_title_changed';
    public const CALLBACK_RECEIVED = 'message_callback';
}
