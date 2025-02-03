<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Enums\UI\CRM;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WebUserTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case WEB_USERS    = 'web_users';

    case REQUESTS        = 'requests';

    public function blueprint(): array
    {
        return match ($this) {
            WebUserTabsEnum::WEB_USERS => [
                'title' => __("Web Users"),
                'icon'  => 'fal fa-terminal',
            ],

            WebUserTabsEnum::REQUESTS => [
                'title' => __("Requests"),
                'icon'  => 'far fa-clock',
            ],

        };
    }
}
