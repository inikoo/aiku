<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\SupplierProduct;

use App\Enums\EnumHelperTrait;

enum ProductionCalendarMonthEnum: string
{
    use EnumHelperTrait;

    case BEST_TO_ORDER = 'best_to_order';
    case PEAK_OR_SLOW = 'peak_or_slow';
    case CANNOT_PRODUCE = 'cannot_produce';

    public static function labels(): array
    {
        return [
            'best_to_order'  => __('Best to order'),
            'peak_or_slow'   => __('Peak or slow'),
            'cannot_produce' => __('Cannot produce'),
        ];
    }
}
