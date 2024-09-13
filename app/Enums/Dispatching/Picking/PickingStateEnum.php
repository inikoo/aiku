<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 05:07:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\Picking;

use App\Enums\EnumHelperTrait;

enum PickingStateEnum: string
{
    use EnumHelperTrait;

    case ON_HOLD         = 'on-hold';
    case ASSIGNED        = 'assigned';
    case PICKING         = 'picking';
    case QUERIED         = 'queried';
    case WAITING         = 'waiting';
    case PICKED          = 'picked';
    case PACKING         = 'packing';
    case DONE            = 'done';

    public static function labels($forElements = false): array
    {
        return [
            'on-hold'              => __('On Hold'),
            'assigned'             => __('Assigned'),
            'picking'              => __('Picking'),
            'queried'              => __('Queried'),
            'waiting'              => __('Waiting'),
            'picked'               => __('Picked'),
            'packing'              => __('Packing'),
            'done'                 => __('Done'),
        ];
    }
}
