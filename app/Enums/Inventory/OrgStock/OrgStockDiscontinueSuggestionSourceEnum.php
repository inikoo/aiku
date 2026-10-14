<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\OrgStock;

use App\Enums\EnumHelperTrait;

enum OrgStockDiscontinueSuggestionSourceEnum: string
{
    use EnumHelperTrait;

    case BUYING = 'buying';
    case CUSTOMER_SERVICES = 'customer_services';
    case GOODS_IN = 'goods_in';

    public static function labels(): array
    {
        return [
            'buying'            => __('Buying'),
            'customer_services' => __('Customer services'),
            'goods_in'          => __('Goods in'),
        ];
    }
}
