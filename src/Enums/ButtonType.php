<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Enums;

enum ButtonType: string
{
    case Callback = 'callback';
    case Link = 'link';
    case RequestContact = 'request_contact';
    case RequestGeo = 'request_geo_location';
    case Message = 'message';
    case Clipboard = 'clipboard';
    case OpenApp = 'open_app';
}
