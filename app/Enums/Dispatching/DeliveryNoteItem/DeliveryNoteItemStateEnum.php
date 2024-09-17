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

    
    public static function stateIcon(): array
    {
        return [
            'on-hold'   => [
                'tooltip' => __('on hold'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'handling'   => [
                'tooltip' => __('handling'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'packed'   => [
                'tooltip' => __('packed'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'finalised'   => [
                'tooltip' => __('finalised'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'settled'   => [
                'tooltip' => __('settled'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }
}
