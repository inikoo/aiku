<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum GuestTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;




    case SHOWCASE                       = 'showcase';
    case HISTORY                        = 'history';
    case DATA                           = 'data';
    case TODAY_TIMESHEETS               = 'today_timesheets';
    case TIMESHEETS                     = 'timesheets';
    case ATTACHMENTS                    = 'attachments';
    case IMAGES                         = 'images';






    public function blueprint(): array
    {
        return match ($this) {
            GuestTabsEnum::TODAY_TIMESHEETS => [
                'title' => __('today timesheets'),
                'icon'  => 'fal fa-database',
            ],
            GuestTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],
            GuestTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
                'align' => 'right',
            ],
            GuestTabsEnum::TIMESHEETS => [
                'title' => __('timesheets'),
                'icon'  => 'fal fa-database',
            ],
            GuestTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            GuestTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            GuestTabsEnum::SHOWCASE => [
                'title' => __('employee'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
