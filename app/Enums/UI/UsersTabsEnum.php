<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum UsersTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case USERS                       = 'users';
    case USERS_REQUESTS              = 'users_requests';

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
            ]
        };
    }
}
