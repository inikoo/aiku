<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 05:07:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\DeliveryNote;

use App\Enums\EnumHelperTrait;

enum DeliveryNoteStateEnum: string
{
    use EnumHelperTrait;

    case SUBMITTED       = 'submitted';
    case IN_QUEUE        = 'in-queue';
    case PICKER_ASSIGNED = 'picker-assigned';
    case PICKING         = 'picking';
    case PICKED          = 'picked';
    case PACKING         = 'packing';
    case PACKED          = 'packed';
    case FINALISED       = 'finalised';
    case SETTLED         = 'settled';

    public static function labels($forElements = false): array
    {
        return [
            'submitted'            => __('Submitted'),
            'in-queue'             => __('In Queue'),
            'picker-assigned'      => __('Picker Assigned'),
            'picking'              => __('Picking'),
            'picked'               => __('Picked'),
            'packing'              => __('Packing'),
            'packed'               => __('Packed'),
            'finalised'            => __('Finalised'),
            'settled'              => __('Settled'),
        ];
    }
}
