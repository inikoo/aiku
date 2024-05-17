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
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;

enum PalletStateEnum: string
{
    use EnumHelperTrait;

    // Status: in-process
    case IN_PROCESS   = 'in-process';

    // Status: receiving
    case SUBMITTED    = 'submitted';
    case CONFIRMED    = 'confirmed';
    case RECEIVED     = 'received';
    case BOOKING_IN   = 'booking-in';
    case BOOKED_IN    = 'booked-in';

    // Status: not-received
    case NOT_RECEIVED = 'not-received';

    // Status: storing
    case STORING    = 'storing';

    // Status: returning
    case PICKING = 'picking';
    case PICKED  = 'picked';

    // Status: incident
    case DAMAGED    = 'damaged';
    case LOST       = 'lost';

    // Status: returned
    case DISPATCHED = 'dispatched';


    public static function labels(): array
    {
        return [
            'in-process'   => __('In process'),
            'submitted'    => __('Submitted'),
            'confirmed'    => __('Confirmed'),
            'not-received' => __('Not Received'),
            'received'     => __('Received'),
            'booking-in'   => __('Booking in'),
            'booked-in'    => __('Booked in'),
            'storing'      => __('Storing'),
            'picking'      => __('Picking'),
            'picked'       => __('Picked'),
            'dispatched'   => __('Dispatched'),
            'lost'         => __('Lost'),
            'damaged'      => __('Damaged'),
        ];
    }

    public static function stateIcon(): array
    {
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
            'submitted' => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ],
            'confirmed' => [
                'tooltip' => __('Confirmed'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald',
                'app'     => [
                    'name' => 'spell-check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'received' => [
                'tooltip' => __('Received'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-slate-500',
                'color'   => 'slate',
                'app'     => [
                    'name' => 'check',
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
            'booking-in' => [
                'tooltip' => __('Booking in'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'booked-in' => [
                'tooltip' => __('Booked in'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-300',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
            'storing' => [
                'tooltip' => __('Storing'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],

            'dispatched' => [
                'tooltip' => __('Dispatched'),
                'icon'    => 'fal fa-sign-out-alt',
                'class'   => 'text-gray-400',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'sign-out-alt',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picking' => [
                'tooltip' => __('Picking'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-400',
                'color'   => 'green',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picked' => [
                'tooltip' => __('Picked'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-400',
                'color'   => 'green',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'damaged' => [
                'tooltip' => __('Damaged'),
                'icon'    => 'fal fa-fragile',
                'class'   => 'text-red-400',
                'color'   => 'red',
                'app'     => [
                    'name' => 'glass-fragile',
                    'type' => 'material-community'
                ]
            ],
            'lost' => [
                'tooltip' => __('Not Picked'),
                'icon'    => 'fal fa-ghost',
                'class'   => 'text-red-400',
                'color'   => 'red',
                'app'     => [
                    'name' => 'ghost',
                    'type' => 'font-awesome-5'
                ]
            ]
        ];
    }

    public static function count(
        Organisation|FulfilmentCustomer|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent
    ): array {
        if ($parent instanceof FulfilmentCustomer) {
            $stats = $parent;
        } elseif ($parent instanceof Organisation) {
            $stats = $parent->fulfilmentStats;
        } else {
            $stats = $parent->stats;
        }

        return [
            'in-process'   => $stats->number_pallets_state_in_process,
            'submitted'    => $stats->number_pallets_state_submitted,
            'confirmed'    => $stats->number_pallets_state_confirmed,
            'not-received' => $stats->number_pallets_state_not_received,
            'received'     => $stats->number_pallets_state_received,
            'booking-in'   => $stats->number_pallets_state_booking_in,
            'booked-in'    => $stats->number_pallets_state_booked_in,
            'storing'      => $stats->number_pallets_state_storing,
            'picking'      => $stats->number_pallets_state_picking,
            'picked'       => $stats->number_pallets_state_picked,
            'dispatched'   => $stats->number_pallets_state_disoatched,
            'lost'         => $stats->number_pallets_state_lost,
            'damaged'      => $stats->number_pallets_state_damaged,

        ];
    }
}
