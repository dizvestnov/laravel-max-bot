<?php

declare(strict_types=1);

namespace Dizvestnov\LaravelMaxBot\Enums;

final class ButtonType
{
    public const CALLBACK = 'callback';

    public const LINK = 'link';

    public const REQUEST_CONTACT = 'request_contact';

    public const REQUEST_GEO = 'request_geo_location';

    public const MESSAGE = 'message';

    public const CLIPBOARD = 'clipboard';

    public const OPEN_APP = 'open_app';
}
