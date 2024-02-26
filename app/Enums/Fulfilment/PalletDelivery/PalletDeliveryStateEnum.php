<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:25:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\PalletDelivery;

use App\Enums\EnumHelperTrait;

enum PalletDeliveryStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in-process';
    case SUBMITTED  = 'submitted';
    case CONFIRMED  = 'confirmed';
    case RECEIVED   = 'received';
    case BOOKED_IN  = 'booked-in';

    public function labels(): array
    {
        return [
            'in-process' => __('In Process'),
            'submitted'  => __('Submitted'),
            'confirmed'  => __('Confirmed'),
            'received'   => __('Received'),
            'booked-in'  => __('Done')
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-emerald-500',  // Color for normal icon (Aiku)
                'color'   => 'emerald'  // Color for box (Retina)
            ],
            'submitted'  => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-300',
                'color'   => 'indigo'
            ],
            'confirmed'  => [
                'tooltip' => __('Confirmed'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500',
                'color'   => 'green'
            ],
            'received'   => [
                'tooltip' => __('Received'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-gray-500',
                'color'   => 'slate'
            ],
            'booked-in'  => [
                'tooltip' => __('Booked in'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-green-500',
                'color'   => 'amber'
            ],
        ];
    }
}
