<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Mar 2023 13:45:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\DeliveryNoteItem;

use App\Enums\EnumHelperTrait;

enum DeliveryNoteItemStateEnum: string
{
    use EnumHelperTrait;


    case ON_HOLD   = 'on-hold';
    case HANDLING  = 'handling';
    case PACKED    = 'packed';
    case FINALISED = 'finalised';
    case SETTLED   = 'settled';

    public static function labels(): array
    {
        return [
            'on-hold'               => __('On Hold'),
            'handling'              => __('Handling'),
            'packed'                => __('Packed'),
            'finalised'             => __('Finalised'),
            'settled'               => __('Settled'),
        ];
    }


    public static function stateIcon(): array
    {
        return [
            'on-hold'   => [
                'tooltip' => __('On Hold'),
                'icon'    => 'fal fa-pause-circle',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'handling'   => [
                'tooltip' => __('Handling'),
                'icon'    => 'fal fa-hands-helping',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'packed'   => [
                'tooltip' => __('Packed'),
                'icon'    => 'fal fa-box',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'finalised'   => [
                'tooltip' => __('Finalised'),
                'icon'    => 'fal fa-tasks',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'settled'   => [
                'tooltip' => __('Settled'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-indigo-500',  // Color for normal icon (Aiku)
                'color'   => 'indigo',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }
}
