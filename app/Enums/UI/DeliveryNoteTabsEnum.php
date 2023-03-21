<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum DeliveryNoteTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SKOS_ORDERED           = 'skos_ordered';
    case UNITS                  = 'units';
    case TARIFF_CODES_ORIGIN    = 'tariff_codes_origin';

    case DATA                   = 'data';

    case CHANGELOG              = 'changelog';





    public function blueprint(): array
    {
        return match ($this) {
            DeliveryNoteTabsEnum::SKOS_ORDERED     => [
                'title' => __('SKOs ordered'),
                'icon'  => 'fal fa-bars',
            ],
            DeliveryNoteTabsEnum::UNITS     => [
                'title' => __('units'),
                'icon'  => 'fal fa-dot-circle',
            ],
            DeliveryNoteTabsEnum::TARIFF_CODES_ORIGIN             => [
                'title' => __('Tariff codes / Origin'),
                'icon'  => 'fal fa-compress-arrows-alt',
            ],
            DeliveryNoteTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            DeliveryNoteTabsEnum::CHANGELOG     => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
            ],
        };
    }
}
