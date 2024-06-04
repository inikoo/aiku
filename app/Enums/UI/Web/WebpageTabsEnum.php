<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:14:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Web;

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



    public function blueprint(): array
    {
        return match ($this) {
            WebpageTabsEnum::SHOWCASE => [
                'title' => __('showcase'),
                'icon'  => 'fas fa-info-circle',
            ],
            WebpageTabsEnum::WEBPAGES => [
                'title' => __('child webpages'),
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

            WebpageTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
