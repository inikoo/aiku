<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\Pallet;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;

enum PalletStatusEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in-process';
    case STORING    = 'storing';
    case DAMAGED    = 'damaged';
    case LOST       = 'lost';
    case RETURNED   = 'returned';

    public static function labels($forElements = false): array
    {
        $labels = [
            'in-process' => __('In process'),
            'storing'    => __('Storing'),
            'damaged'    => __('Damaged'),
            'lost'       => __('Lost'),
            'returned'   => __('Returned'),
        ];

        if ($forElements) {
            unset($labels['in-process']);
        }

        return $labels;
    }

    public static function count(Organisation|FulfilmentCustomer|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent, $forElements = false): array
    {
        if ($parent instanceof FulfilmentCustomer) {
            $stats = $parent;
        } elseif ($parent instanceof Organisation) {
            $stats = $parent->fulfilmentStats;
        } else {
            $stats = $parent->stats;
        }

        $count = [
            'in-process' => $stats->number_pallets_status_in_process,
            'storing'    => $stats->number_pallets_status_storing,
            'damaged'    => $stats->number_pallets_status_damaged,
            'lost'       => $stats->number_pallets_status_lost,
            'returned'   => $stats->number_pallets_status_returned,
        ];

        if ($forElements) {
            unset($count['in-process']);
        }

        return $count;
    }

}
