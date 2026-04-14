<?php

declare(strict_types=1);

namespace {YourVendor}\LaravelMaxBot\Enums;

enum UpdateType: string
{
    case MessageCreated   = 'message_created';
    case MessageEdited    = 'message_edited';
    case MessageRemoved   = 'message_removed';
    case BotStarted       = 'bot_started';
    case BotAdded         = 'bot_added';
    case BotRemoved       = 'bot_removed';
    case UserAdded        = 'user_added';
    case UserRemoved      = 'user_removed';
    case ChatTitleChanged = 'chat_title_changed';
    case CallbackReceived = 'message_callback';
}
