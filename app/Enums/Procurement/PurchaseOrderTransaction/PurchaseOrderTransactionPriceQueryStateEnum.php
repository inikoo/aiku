<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrderTransaction;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderTransactionPriceQueryStateEnum: string
{
    use EnumHelperTrait;

    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case ADJUSTED = 'adjusted';

    public function label(): string
    {
        return match ($this) {
            self::PENDING  => __('Waiting for the organisation'),
            self::ACCEPTED => __('New price accepted'),
            self::REJECTED => __('New price rejected, old price kept'),
            self::ADJUSTED => __('Price adjusted by the organisation'),
        };
    }
}
