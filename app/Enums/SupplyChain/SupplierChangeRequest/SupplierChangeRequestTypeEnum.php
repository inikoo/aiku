<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\SupplierChangeRequest;

use App\Enums\EnumHelperTrait;

enum SupplierChangeRequestTypeEnum: string
{
    use EnumHelperTrait;

    case DEACTIVATE = 'deactivate';
    case MERGE = 'merge';

    public static function labels(): array
    {
        return [
            'deactivate' => __('Deactivate'),
            'merge'      => __('Merge'),
        ];
    }
}
