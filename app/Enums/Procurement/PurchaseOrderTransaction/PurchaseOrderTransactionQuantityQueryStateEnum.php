<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrderTransaction;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderTransactionQuantityQueryStateEnum: string
{
    use EnumHelperTrait;

    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING  => __('Waiting for the organisation'),
            self::ACCEPTED => __('New quantity accepted'),
            self::REJECTED => __('New quantity rejected, old quantity kept'),
        };
    }
}
