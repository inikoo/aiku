<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WebsiteTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE             = 'showcase';

    case WEBPAGES             = 'webpages';
    case ANALYTICS            = 'analytics';
    case USERS                = 'users';
    case WORKSHOP             = 'workshop';
    case SETTINGS             = 'settings';

    case CHANGELOG            = 'changelog';

    case DATA                 = 'data';


    public function blueprint(): array
    {
        return match ($this) {
            WebsiteTabsEnum::SHOWCASE => [
                'title' => __('showcase'),
                'icon'  => 'fas fa-info-circle',
            ],
            WebsiteTabsEnum::WEBPAGES => [
                'title' => __('webpages'),
                'icon'  => 'fal fa-browser',
            ],
            WebsiteTabsEnum::ANALYTICS => [
                'title' => __('analytics'),
                'icon'  => 'fal fa-analytics',
            ],
            WebsiteTabsEnum::USERS => [
                'title' => __('users'),
                'icon'  => 'fal fa-users-class',
            ],
            WebsiteTabsEnum::WORKSHOP => [
                'title' => __('workshop'),
                'icon'  => 'fal fa-drafting-compass',
            ],
            WebsiteTabsEnum::SETTINGS => [
                'title' => __('settings'),
                'icon'  => 'fal fa-sliders-h',
            ],
            WebsiteTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WebsiteTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
