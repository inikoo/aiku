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

    case RECEIVING    = 'receiving';
    case NOT_RECEIVED = 'not-received';
    case STORING      = 'storing';
    case RETURNING    = 'returning';
    case RETURNED     = 'returned';
    case INCIDENT     = 'incident';

    public static function labels($forElements = false): array
    {
        $labels = [
            'receiving'    => __('Receiving'),
            'not-received' => __('Not received'),
            'storing'      => __('Storing'),
            'returning'    => __('Returning'),
            'returned'     => __('Returned'),
            'incident'     => __('Incidents'),

        ];


        return $labels;
    }

    public static function statusIcon(): array
    {
        return [
            'receiving'   => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'not-received' => [
                'tooltip' => __('not received'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'red',
                'app'     => [
                    'name' => 'times',
                    'type' => 'font-awesome-5'
                ]
            ],
            'storing'    => [
                'tooltip' => __('Storing'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
            'incident'      => [
                'tooltip' => __('Incident'),
                'icon'    => 'fal fa-sad-cry',
                'class'   => 'text-red-600',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'sad-cry',
                    'type' => 'font-awesome-5'
                ]
            ],
            'returning'       => [
                'tooltip' => __('Returning'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-400',
                'color'   => 'green',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'returned'       => [
                'tooltip' => __('Returned'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-400',
                'color'   => 'green',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }

    public static function count(
        Organisation|FulfilmentCustomer|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent,
        $forElements = false
    ): array {
        if ($parent instanceof FulfilmentCustomer) {
            $stats = $parent;
        } elseif ($parent instanceof Organisation) {
            $stats = $parent->fulfilmentStats;
        } else {
            $stats = $parent->stats;
        }

        return [
            'receiving'    => $stats->number_pallets_status_receiving,
            'not-received' => $stats->number_pallets_status_not_received,
            'storing'      => $stats->number_pallets_status_storing,
            'returning'    => $stats->number_pallets_status_returning,
            'incident'     => $stats->number_pallets_status_incident,
            'returned'     => $stats->number_pallets_status_returned,
        ];
    }

}
