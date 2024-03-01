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

    case IN_PROCESS      = 'in-process';
    case SUBMITTED       = 'submitted';
    case CONFIRMED       = 'confirmed';
    case IN_DELIVERY     = 'in-delivery';
    case RECEIVED        = 'received';
    case DONE            = 'done';

    public static function labels(): array
    {
        return [
            'in-process'     => __('In Process'),
            'submitted'      => __('Submitted'),
            'confirmed'      => __('Confirmed'),
            'in-delivery'    => __('In Delivery'),
            'received'       => __('Received'),
            'done'           => __('Done')
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-emerald-500'
            ],
            'submitted' => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-500'
            ],
            'confirmed' => [
                'tooltip' => __('Confirmed'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
            'in-delivery' => [
                'tooltip' => __('In Delivery'),
                'icon'    => 'fal fa-truck',
                'class'   => 'text-green-500'
            ],
            'received' => [
                'tooltip' => __('Received'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-slate-500'
            ],
            'done' => [
                'tooltip' => __('Done'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500'
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
            'in-process'   => $stats->number_pallet_returns_state_in_process,
            'submitted'    => $stats->number_pallet_returns_state_submitted,
            'confirmed'    => $stats->number_pallet_returns_state_confirmed,
            'in-delivery'  => $stats->number_pallet_returns_state_in_delivery,
            'received'     => $stats->number_pallet_returns_state_received,
            'done'         => $stats->number_pallet_returns_state_done
        ];
    }
}
