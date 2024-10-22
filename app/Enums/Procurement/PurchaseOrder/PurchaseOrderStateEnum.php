<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 17 Apr 2023 17:11:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrder;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS     = 'in_process';
    case SUBMITTED    = 'submitted';
    case CONFIRMED    = 'confirmed';
    case MANUFACTURED = 'manufactured';
    case DISPATCHED   = 'dispatched';
    case RECEIVED     = 'received';
    case CHECKED      = 'checked';
    case SETTLED      = 'settled';

    public static function labels(): array
    {
        return [
            'in_process'         => __('In process'),
            'submitted'        => __('Submitted'),
            'confirmed'        => __('Confirmed'),
            'manufactured'     => __('Manufactured'),
            'dispatched'       => __('Dispatched'),
            'received'         => __('Received'),
            'checked'          => __('Checked'),
            'settled'          => __('Settled'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in_process'   => [
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
                'icon'    => 'fal fa-paper-plane',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ],
            'confirmed'    => [
                'tooltip' => __('Confirmed'),
                'icon'    => 'fal fa-check-circle',
                'class'   => 'text-gray-500',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'spell-check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'manufactured'     => [
                'tooltip' => __('Manufactured'),
                'icon'    => 'fal fa-industry',
                'class'   => 'text-gray-500',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'dispatched' => [
                'tooltip' => __('Dispatched'),
                'icon'    => 'fal fa-shipping-fast',
                'class'   => 'text-gray-500',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'times',
                    'type' => 'font-awesome-5'
                ]
            ],
            'received'   => [
                'tooltip' => __('Received'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-gray-500',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'checked'    => [
                'tooltip' => __('Checked'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-gray-500',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
            'settled'    => [
                'tooltip' => __('Settled'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-gray-500',
                'color'   => 'gray',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }

}
