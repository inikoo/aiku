<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 05:07:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\Picking;

use App\Enums\EnumHelperTrait;

enum PickingOutcomeEnum: string
{
    use EnumHelperTrait;

    case HANDLING             = 'handling';
    case PACKED               = 'packed';
    case PARTIALLY_PACKED     = 'partially-packed';
    case OUT_OF_STOCK         = 'out-of-stock';
    case CANCELLED            = 'cancelled';

    public static function labels($forElements = false): array
    {
        return [
            'handling'            => __('Handling'),
            'packed'              => __('Packed'),
            'partially-packed'    => __('Partially Packed'),
            'out-of-stock'        => __('Out Of Stock'),
            'cancelled'           => __('Cancelled'),
        ];
    }
}
