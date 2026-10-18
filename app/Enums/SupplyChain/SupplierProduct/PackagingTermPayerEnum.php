<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\SupplierProduct;

use App\Enums\EnumHelperTrait;

enum PackagingTermPayerEnum: string
{
    use EnumHelperTrait;

    case SUPPLIER = 'supplier';
    case US = 'us';
    case NOT_NEEDED = 'not_needed';

    public static function labels(): array
    {
        return [
            'supplier'   => __('Supplier pays'),
            'us'         => __('We pay'),
            'not_needed' => __('Not needed'),
        ];
    }
}
