<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 09:29:02 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\DeliveryNotes;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum DeliveryNotesTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case STATS     = 'stats';
    case DELIVERY_NOTES    = 'notes';
    case HISTORY   = 'history';
    case TAGS      = 'tags';


    public function blueprint(): array
    {
        return match ($this) {

            DeliveryNotesTabsEnum::DELIVERY_NOTES => [
                'title' => __('notes'),
                'icon'  => 'fal fa-shopping-cart',
            ],
            DeliveryNotesTabsEnum::STATS => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-pie',
            ],
            DeliveryNotesTabsEnum::TAGS => [
                'title' => __('tags'),
                'icon'  => 'fal fa-tags',
                'type'  => 'icon',
                'align' => 'right'
            ],
            DeliveryNotesTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ]
        };
    }
}
