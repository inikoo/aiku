<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 03:02:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\StoredItem;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\SysAdmin\Organisation;

enum StoredItemStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS       = 'in-process';
    case RECEIVED         = 'received';
    case BOOKED_IN        = 'booked-in';
    case SETTLED          = 'settled';


    public static function labels($forElements = false): array
    {
        $labels = [
            'in-process'     => __('In Process'),
            'received'       => __('Received'),
            'booked-in'      => __('Booked In'),
            'settled'        => __('Settled')
        ];

        return $labels;
    }

    public static function count(
        Pallet|FulfilmentCustomer|Fulfilment|Organisation $parent,
        $forElements = false
    ): array {
        if ($parent instanceof FulfilmentCustomer) {
            $stats = $parent;
        } elseif ($parent instanceof Organisation) {
            $stats = $parent->fulfilmentStats;
        } elseif ($parent instanceof Pallet) {
            $stats = $parent->stats;
        } else {
            $stats = $parent->stats;
        }

        return [
            'in-process'     => $stats?->number_stored_items_in_process,
            'received'       => $stats?->number_stored_items_received,
            'booked-in'      => $stats?->number_stored_items_booked_in,
            'settled'        => $stats?->number_stored_items_settled
        ];
    }
}
