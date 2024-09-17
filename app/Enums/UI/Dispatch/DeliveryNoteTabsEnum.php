<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:17:42 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Dispatch;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum DeliveryNoteTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    // case SHOWCASE               = 'showcase';
    case SKOS_ORDERED           = 'skos_ordered';
    // case UNITS                  = 'units';
    // case TARIFF_CODES_ORIGIN    = 'tariff_codes_origin';

    case PICKINGS                   = 'pickings';

    // case CHANGELOG              = 'changelog';





    public function blueprint(): array
    {
        return match ($this) {
            DeliveryNoteTabsEnum::SKOS_ORDERED     => [
                'title' => __('SKOs ordered'),
                'icon'  => 'fal fa-bars',
            ],
            // DeliveryNoteTabsEnum::UNITS     => [
            //     'title' => __('units'),
            //     'icon'  => 'fal fa-dot-circle',
            // ],
            // DeliveryNoteTabsEnum::TARIFF_CODES_ORIGIN             => [
            //     'title' => __('Tariff codes / Origin'),
            //     'icon'  => 'fal fa-compress-arrows-alt',
            // ],
            DeliveryNoteTabsEnum::PICKINGS => [
                'title' => __('pickings '),
                'icon'  => 'fal fa-bars',
                'type'  => 'icon',
                'align' => 'right',
            ],
            // DeliveryNoteTabsEnum::CHANGELOG     => [
            //     'title' => __('changelog'),
            //     'icon'  => 'fal fa-road',
            // ],
            // DeliveryNoteTabsEnum::SHOWCASE => [
            //     'title' => __('agent'),
            //     'icon'  => 'fas fa-info-circle',
            // ],
        };
    }
}
