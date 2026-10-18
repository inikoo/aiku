<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\SupplierProduct;

use App\Enums\EnumHelperTrait;

enum SupplierProductAvailabilityStatusEnum: string
{
    use EnumHelperTrait;

    case AVAILABLE = 'available';
    case LIMITED = 'limited';
    case UNAVAILABLE = 'unavailable';

    public static function labels(): array
    {
        return [
            'available'   => __('Available'),
            'limited'     => __('Limited'),
            'unavailable' => __('Not available'),
        ];
    }
}
