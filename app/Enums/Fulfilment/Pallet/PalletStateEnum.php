<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 03:02:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\Pallet;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;

enum PalletStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS   = 'in-process';
    case SUBMITTED    = 'submitted';
    case CONFIRMED    = 'confirmed';
    case RECEIVED     = 'received';
    case NOT_RECEIVED = 'not-received';
    case BOOKED_IN    = 'booked-in';
    case SETTLED      = 'settled';


    public static function labels(): array
    {
        return [
            'in-process'   => __('In process'),
            'submitted'    => __('Submitted'),
            'confirmed'    => __('Confirmed'),
            'received'     => __('Received'),
            'booked-in'    => __('Booked in'),
            'not-received' => __('Not Received'),
            'settled'      => __('Settled'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process'   => [
                'tooltip' => __('in process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-emerald-500'
            ],
            'submitted'    => [
                'tooltip' => __('submitted'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-300'
            ],
            'confirmed'    => [
                'tooltip' => __('confirmed'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
            'received'     => [
                'tooltip' => __('received'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-gray-500'
            ],
            'not-received' => [
                'tooltip' => __('not received'),
                'icon'    => 'fas fa-time-square',
                'class'   => 'text-red-500'
            ],
            'booked-in'    => [
                'tooltip' => __('booked in'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-green-500'
            ],
            'settled'      => [
                'tooltip' => __('settled'),
                'icon'    => 'fal fa-sign-out-alt',
                'class'   => 'text-grey-400'
            ]
        ];
    }

    public static function count(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery $parent): array
    {
        if ($parent instanceof FulfilmentCustomer) {
            $stats = $parent;
        } else {
            $stats = $parent->stats;
        }

        return [
            'in-process'   => $stats->number_pallets_state_in_process,
            'submitted'    => $stats->number_pallets_state_submitted,
            'confirmed'    => $stats->number_pallets_state_confirmed,
            'not-received' => $stats->number_pallets_state_not_received,
            'received'     => $stats->number_pallets_state_received,
            'booked-in'    => $stats->number_pallets_state_booked_in,
            'settled'      => $stats->number_pallets_state_settled
        ];
    }

}
