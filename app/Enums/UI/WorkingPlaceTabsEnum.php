<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WorkingPlaceTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE           = 'showcase';
    case HISTORY            = 'history';
    case DATA               = 'data';
    case CLOCKING_MACHINES  = 'clocking_machines';
    case CLOCKINGS          = 'clockings';
    case ATTACHMENTS        = 'attachments';
    case IMAGES             = 'images';


    public function blueprint(): array
    {
        return match ($this) {
            WorkingPlaceTabsEnum::CLOCKING_MACHINES => [
                'title' => __('clocking machines'),
                'icon'  => 'fal fa-database',
            ],
            WorkingPlaceTabsEnum::CLOCKINGS => [
                'title' => __('clocking'),
                'icon'  => 'fal fa-database',
            ],
            WorkingPlaceTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WorkingPlaceTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WorkingPlaceTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WorkingPlaceTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WorkingPlaceTabsEnum::SHOWCASE => [
                'title' => __('workplace'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
