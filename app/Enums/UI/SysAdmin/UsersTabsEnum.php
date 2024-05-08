<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 14:31:41 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\SysAdmin;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum UsersTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case USERS                       = 'users';
    case USERS_REQUESTS              = 'users_requests';

    case USERS_HISTORIES             = 'users_histories';

    public function blueprint(): array
    {
        return match ($this) {
            UsersTabsEnum::USERS => [
                'title' => __('users'),
                'icon'  => 'fal fa-terminal',
            ],
            UsersTabsEnum::USERS_REQUESTS => [
                'title' => __('users requests'),
                'icon'  => 'fal fa-road',
            ],
            UsersTabsEnum::USERS_HISTORIES => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ],
        };
    }
}
