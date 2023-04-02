<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum UserTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;




    case USERNAME               = 'username';

    case EMAIL                  = 'email';
    case ABOUT                  = 'about';
    case PASSWORD               = 'password';
    case HISTORY                = 'history';
    case DATA                   = 'data';
    case REMEMBER_TOKEN         = 'remember_token';








    public function blueprint(): array
    {
        return match ($this) {
            UserTabsEnum::USERNAME => [
                'title' => __('username'),
                'icon'  => 'fal fa-user',
            ],
            UserTabsEnum::EMAIL => [
                'title' => __('email'),
                'icon'  => 'fal fa-envelope',
            ],
            UserTabsEnum::ABOUT => [
                'title' => __('about'),
                'icon'  => 'fal fa-file',
            ],
            UserTabsEnum::REMEMBER_TOKEN => [
                'title' => __('remember token'),
                'icon'  => 'fal fa-hexagon',
                'type'  => 'icon',
                'align' => 'right',
            ]
            ,UserTabsEnum::PASSWORD => [
                'title' => __('password'),
                'icon'  => 'fal fa-id-card',
            ],UserTabsEnum::HISTORY => [
                'title' => __('date of birth'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],UserTabsEnum::DATA => [
                'title' => __('gender'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
