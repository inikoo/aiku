<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 05:07:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\Picking;

use App\Enums\EnumHelperTrait;

enum PickingStateEnum: string
{
    use EnumHelperTrait;

    case ON_HOLD         = 'on-hold';
    case ASSIGNED        = 'assigned';
    case PICKING         = 'picking';
    case QUERIED         = 'queried';
    case WAITING         = 'waiting';
    case PICKED          = 'picked';
    case PACKING         = 'packing';
    case DONE            = 'done';

    public static function labels($forElements = false): array
    {
        return [
            'on-hold'              => __('On Hold'),
            'assigned'             => __('Assigned'),
            'picking'              => __('Picking'),
            'queried'              => __('Queried'),
            'waiting'              => __('Waiting'),
            'picked'               => __('Picked'),
            'packing'              => __('Packing'),
            'done'                 => __('Done'),
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
            'assigned'   => [
                'tooltip' => __('Assigned'),
                'icon'    => 'fal fa-user-check',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picking'   => [
                'tooltip' => __('Picking'),
                'icon'    => 'fal fa-hand-paper',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'queried'   => [
                'tooltip' => __('Queried'),
                'icon'    => 'fal fa-question-circle',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'waiting'   => [
                'tooltip' => __('Waiting'),
                'icon'    => 'fal fa-hourglass-half',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picked'   => [
                'tooltip' => __('Picked'),
                'icon'    => 'fal fa-box-check',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'packing'   => [
                'tooltip' => __('Packing'),
                'icon'    => 'fal fa-box-open',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'done'   => [
                'tooltip' => __('Done'),
                'icon'    => 'fal fa-flag-checkered',
                'class'   => 'text-gray-500',  // Color for normal icon (Aiku)
                'color'   => 'gray',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }

}
