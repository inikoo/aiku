<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:17:42 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Dispatch;

use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Enums\EnumHelperTrait;
use App\Enums\HasTabsWithIndicator;
use App\Models\Dispatching\DeliveryNote;

enum DeliveryNoteTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabsWithIndicator;

    // case SHOWCASE               = 'showcase';
    case SKOS_ORDERED           = 'skos_ordered';
    // case UNITS                  = 'units';
    // case TARIFF_CODES_ORIGIN    = 'tariff_codes_origin';

    case PICKINGS                   = 'pickings';

    // case CHANGELOG              = 'changelog';





    public function blueprint(DeliveryNote $parent): array
    {
        $indicator = false;
        if ($parent->state == DeliveryNoteStateEnum::IN_QUEUE) {
            foreach ($parent->deliveryNoteItems as $deliveryNoteItem) {
                if (!$deliveryNoteItem->pickings || !$deliveryNoteItem->pickings->picker_id) {
                    $indicator = true;
                }
            }
        }

        if ($parent->state == DeliveryNoteStateEnum::PICKING) {
            foreach ($parent->deliveryNoteItems as $deliveryNoteItem) {
                if (!$deliveryNoteItem->pickings->state == PickingStateEnum::PICKED) {
                    $indicator = true;
                }
            }
        }

        if ($parent->state == DeliveryNoteStateEnum::PICKED) {
            foreach ($parent->deliveryNoteItems as $deliveryNoteItem) {
                if (!$deliveryNoteItem->pickings->state == PickingStateEnum::DONE) {
                    $indicator = true;
                }
            }
        }
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
                'title'     => __('pickings '),
                'icon'      => 'fal fa-box-full',
                'type'      => 'icon',
                'align'     => 'right',
                'indicator' => $indicator
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
