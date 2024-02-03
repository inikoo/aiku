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

    case IN_PROCESS = 'in-process';
    case SUBMITTED  = 'submitted';
    case RECEIVED   = 'received';
    case BOOKED_IN  = 'booked-in';
    case SETTLED    = 'settled';


    public static function labels(): array
    {
        return [
            'in-process' => __('In process'),
            'submitted'  => __('Submitted'),
            'received'   => __('Received'),
            'booked-in'  => __('Booked in'),
            'settled'    => __('Settled'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('in process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-emerald-500'
            ],
            'submitted'  => [
                'tooltip' => __('submitted'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-500'
            ],
            'received'   => [
                'tooltip' => __('received'),
                'icon'    => 'fal fa-truck-loading',
                'class'   => 'text-blue-500'
            ],
            'booked-in'  => [
                'tooltip' => __('booked in'),
                'icon'    => 'fal fa-clipboard-check',
                'class'   => 'text-yellow-500'
            ],
            'settled'    => [
                'tooltip' => __('settled'),
                'icon'    => 'fal fa-sign-out-alt',
                'class'   => 'text-grey-400'
            ]
        ];
    }

    public static function count(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery $parent): array
    {
        $stats=$parent->humanResourcesStats;
        return [
            'hired'         => $stats->number_employees_state_hired,
            'working'       => $stats->number_employees_state_working,
            'left'          => $stats->number_employees_state_left,
        ];
    }

}
