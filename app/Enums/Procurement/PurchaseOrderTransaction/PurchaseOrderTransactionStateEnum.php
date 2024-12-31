<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 09 May 2023 13:09:10 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrderTransaction;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderTransactionStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in_process';
    case SUBMITTED = 'submitted';
    case CONFIRMED = 'confirmed';
    case SETTLED = 'settled';
    case CANCELLED = 'cancelled';
    case NOT_RECEIVED = 'not_received';

    public static function labels(): array
    {
        return [
            'in_process' => __('In process'),
            'submitted'  => __('Submitted'),
            'confirmed'  => __('Confirmed'),
            'settled'    => __('Settled'),
            'cancelled'  => __('Cancelled'),
            'not_received' => __('Not Received')
        ];
    }

}
