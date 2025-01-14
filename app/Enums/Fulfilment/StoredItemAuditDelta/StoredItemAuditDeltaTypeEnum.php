<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 17:08:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\StoredItemAuditDelta;

use App\Enums\EnumHelperTrait;

enum StoredItemAuditDeltaTypeEnum: string
{
    use EnumHelperTrait;

    case ADDITION   = 'addition';
    case SUBTRACTION = 'subtraction';
    case CHECK        = 'check';
    case SET_UP       = 'set_up';
    case NO_CHANGE       = 'no_change';

    public static function labels(): array
    {
        return [
            'addition'   => __('Addition'),
            'subtraction'    => __('Subtraction'),
            'check'    => __('Addition'),
            'set_up'    => __('Set Up'),
            'no_change'    => __('No Change'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'addition' => [
                'tooltip' => __('Addition'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'subtraction' => [
                'tooltip' => __('Subtraction'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'check' => [
                'tooltip' => __('Check'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'set_up' => [
                'tooltip' => __('Set Up'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'no_change' => [
                'tooltip' => __('No Change'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }
}
