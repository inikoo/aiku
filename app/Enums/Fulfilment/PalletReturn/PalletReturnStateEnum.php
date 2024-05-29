<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:25:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\PalletReturn;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;

enum PalletReturnStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS   = 'in-process';
    case SUBMITTED    = 'submitted';
    case CONFIRMED    = 'confirmed';
    case PICKING      = 'picking';
    case PICKED       = 'picked';
    case DISPATCHED   = 'dispatched';
    case CONSOLIDATED = 'consolidated';
    case CANCEL       = 'cancel';

    public static function labels($forElements = false): array
    {
        return [
            'in-process' => __('In Process'),
            'submitted'  => __('Submitted'),
            'confirmed'  => __('Confirmed'),
            'picking'    => __('Picking'),
            'picked'     => __('Picked'),
            'dispatched' => __('Dispatched'),
            'cancel'     => __('Cancel')
        ];
    }

    public static function stateIcon(): array
    {
        // Icon is imported in resources/js/Composables/Icon/PalletReturnStateEnum.ts
        return [
            'in-process' => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'submitted'  => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ],
            'confirmed'  => [
                'tooltip' => __('Confirmed'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald',
                'app'     => [
                    'name' => 'spell-check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picking'    => [
                'tooltip' => __('Picking'),
                'icon'    => 'fal fa-truck',
                'class'   => 'text-orange-500',
                'color'   => 'orange',
                'app'     => [
                    'name' => 'truck',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picked'     => [
                'tooltip' => __('Picked'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-slate-500',
                'color'   => 'slate',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'dispatched' => [
                'tooltip' => __('Dispatched'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
            'cancel'     => [
                'tooltip' => __('Cancel'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'red',
                'app'     => [
                    'name' => 'times',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }

    public static function count(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery $parent, $forElements = false): array
    {
        if ($parent instanceof FulfilmentCustomer) {
            $stats = $parent;
        } else {
            $stats = $parent->stats;
        }

        return [
            'in-process' => $stats->number_pallet_returns_state_in_process,
            'submitted'  => $stats->number_pallet_returns_state_submitted,
            'confirmed'  => $stats->number_pallet_returns_state_confirmed,
            'picking'    => $stats->number_pallet_returns_state_picking,
            'picked'     => $stats->number_pallet_returns_state_picked,
            'dispatched' => $stats->number_pallet_returns_state_dispatched,
            'cancel'     => $stats->number_pallet_returns_state_cancel
        ];
    }

    public static function notifications(string $reference): array
    {
        return [
            'in-process' => [
                'title'    => __("Pallet Return $reference Created"),
                'subtitle' => __('Pallet return has been created')
            ],
            'submitted'  => [
                'title'    => __("Pallet Return $reference Submitted"),
                'subtitle' => __('Pallet return has been submitted')
            ],
            'confirmed'  => [
                'title'    => __("Pallet Return $reference Confirmed"),
                'subtitle' => __('Pallet return has been confirmed')
            ],
            'picking'    => [
                'title'    => __("Pallet Return $reference Picking"),
                'subtitle' => __('Pallet return is picking')
            ],
            'picked'     => [
                'title'    => __("Pallet Return $reference Picked"),
                'subtitle' => __('Pallet return has been picked')
            ],
            'dispatched' => [
                'title'    => __("Pallet Return $reference Dispatched"),
                'subtitle' => __('Pallet return has been dispatched')
            ],
            'cancel'     => [
                'title'    => __("Pallet Return $reference Cancelled"),
                'subtitle' => __('Pallet return has been cancelled')
            ],
        ];
    }
}
