<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:24:56 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Ordering\Order;

use App\Enums\EnumHelperTrait;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\SysAdmin\Organisation;

enum OrderStateEnum: string
{
    use EnumHelperTrait;

    case CREATING     = 'creating';
    case SUBMITTED    = 'submitted';
    case IN_WAREHOUSE = 'in_warehouse';
    case HANDLING     = 'handling';
    case PACKED       = 'packed';
    case FINALISED    = 'finalised';
    case DISPATCHED   = 'dispatched';
    case CANCELLED    = 'cancelled';

    public static function labels($forElements = false): array
    {
        return [
            'creating'          => __('Creating'),
            'submitted'         => __('Submitted'),
            'in_warehouse'      => __('In Warehouse'),
            'handling'          => __('Handling'),
            'packed'            => __('Packed'),
            'finalised'         => __('Finalized'),
            'dispatched'        => __('Dispatched'),
            'cancelled'         => __('Cancelled'),
        ];
    }

    public static function stateIcon(): array
    {
        // Icon is imported in resources/js/Composables/Icon/PalletDeliveryStateEnum.ts
        return [
            'creating'   => [
                'tooltip' => __('Creating'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'submitted'    => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ],
            'in_warehouse'    => [
                'tooltip' => __('In Warehouse'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald',
                'app'     => [
                    'name' => 'spell-check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'handling'     => [
                'tooltip' => __('Handling'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-slate-500',
                'color'   => 'slate',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'packed' => [
                'tooltip' => __('Packed'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'slate',
                'app'     => [
                    'name' => 'times',
                    'type' => 'font-awesome-5'
                ]
            ],
            'finalised'   => [
                'tooltip' => __('Finalized'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'dispatched'    => [
                'tooltip' => __('Dispatched'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
            'cancelled'    => [
                'tooltip' => __('Cancelled'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }

    public static function count(
        Organisation|Shop|Customer|CustomerClient|Asset $parent,
        $forElements = false
    ): array {
        if ($parent instanceof Organisation || $parent instanceof Shop) {
            $stats = $parent->salesStats;
        } elseif ($parent instanceof CustomerClient) {
            $stats = $parent->customer->stats;
        } else {
            $stats = $parent->stats;
        }

        return [
            'creating'     => $stats->number_orders_state_creating,
            'submitted'    => $stats->number_orders_state_submitted,
            'in_warehouse'    => $stats->number_orders_state_in_warehouse,
            'handling'     => $stats->number_orders_state_handling,
            'packed' => $stats->number_orders_state_packed,
            'finalised'   => $stats->number_orders_state_finalised,
            'dispatched'    => $stats->number_orders_state_dispatched,
            'cancelled'    => $stats->number_orders_state_cancelled,
        ];
    }
}
