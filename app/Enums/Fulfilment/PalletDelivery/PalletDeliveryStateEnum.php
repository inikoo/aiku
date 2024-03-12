<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:25:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\PalletDelivery;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;

enum PalletDeliveryStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS   = 'in-process';
    case SUBMITTED    = 'submitted';
    case CONFIRMED    = 'confirmed';
    case RECEIVED     = 'received';
    case NOT_RECEIVED = 'not-received';
    case BOOKED_IN    = 'booked-in';

    public static function labels(): array
    {
        return [
            'in-process'     => __('In Process'),
            'submitted'      => __('Submitted'),
            'confirmed'      => __('Confirmed'),
            'received'       => __('Received'),
            'not-received'   => __('Not Received'),
            'booked-in'      => __('Done')
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
            'not-received'   => [
                'tooltip' => __('Not Received'),
                'icon'    => 'fal fa-cross',
                'class'   => 'text-slate-500',
                'color'   => 'slate'
            ],
            'booked-in'  => [
                'tooltip' => __('Booked in'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple'
            ],
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
            'in-process'   => $stats->number_pallet_deliveries_state_in_process,
            'submitted'    => $stats->number_pallet_deliveries_state_submitted,
            'confirmed'    => $stats->number_pallet_deliveries_state_confirmed,
            'received'     => $stats->number_pallet_deliveries_state_received,
            'not-received' => $stats->number_pallet_deliveries_state_not_received,
            'booked-in'    => $stats->number_pallet_deliveries_state_booked_in,
        ];
    }
}
