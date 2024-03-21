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

    case PICKED       = 'picked';
    case NOT_PICKED   = 'not-picked';


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

            'picked'      => __('Picked'),
            'not-picked'  => __('Not Picked'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime'  // Color for box (Retina)
            ],
            'submitted'  => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo'
            ],
            'confirmed'  => [
                'tooltip' => __('Confirmed'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald'
            ],
            'received'   => [
                'tooltip' => __('Received'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-slate-500',
                'color'   => 'slate'
            ],
            'not-received' => [
                'tooltip' => __('not received'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'red'
            ],
            'booked-in'  => [
                'tooltip' => __('Booked in'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple'
            ],
            'settled'      => [
                'tooltip' => __('Settled'),
                'icon'    => 'fal fa-sign-out-alt',
                'class'   => 'text-gray-400',
                'color'   => 'gray'
            ],
            'picked'      => [
                'tooltip' => __('Picked'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-400',
                'color'   => 'green'
            ],
            'not-picked'      => [
                'tooltip' => __('Not Picked'),
                'icon'    => 'fal fa-cross',
                'class'   => 'text-red-400',
                'color'   => 'red'
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
            'settled'      => $stats->number_pallets_state_settled,
            'picked'       => 0,
            'not-picked'   => 0
        ];
    }

}
