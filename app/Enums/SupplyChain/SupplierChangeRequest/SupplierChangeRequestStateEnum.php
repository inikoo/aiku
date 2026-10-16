<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\SupplierChangeRequest;

use App\Enums\EnumHelperTrait;

enum SupplierChangeRequestStateEnum: string
{
    use EnumHelperTrait;

    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public static function labels(): array
    {
        return [
            'pending'  => __('Waiting for directors'),
            'approved' => __('Approved'),
            'rejected' => __('Kept'),
        ];
    }
}
