<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 12 Apr 2023 13:50:04 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WebpageTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE             = 'showcase';

    case WEBPAGES             = 'webpages';
    case ANALYTICS            = 'analytics';



    case SNAPSHOTS            = 'snapshots';
    case CHANGELOG            = 'changelog';

    case DATA                 = 'data';


    public function blueprint(): array
    {
        return match ($this) {
            WebpageTabsEnum::SHOWCASE => [
                'title' => __('showcase'),
                'icon'  => 'fas fa-info-circle',
            ],
            WebpageTabsEnum::WEBPAGES => [
                'title' => __('webpages'),
                'icon'  => 'fal fa-browser',
            ],
            WebpageTabsEnum::ANALYTICS => [
                'title' => __('analytics'),
                'icon'  => 'fal fa-analytics',
            ],
            WebpageTabsEnum::SNAPSHOTS => [
                'title' => __('Snapshots'),
                'icon'  => 'fal fa-layer-group',
            ],
            WebpageTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WebpageTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
