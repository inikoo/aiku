<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Production\ProductionMaterialRequest;

use App\Enums\EnumHelperTrait;

enum ProductionMaterialRequestStateEnum: string
{
    use EnumHelperTrait;

    case CHECKING  = 'checking';
    case SUBMITTED = 'submitted';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public static function labels($forElements = false): array
    {
        return [
            'checking'  => __('Being checked'),
            'submitted' => __('Sent to warehouse'),
            'delivered' => __('Delivered'),
            'cancelled' => __('Cancelled'),
        ];
    }
}
