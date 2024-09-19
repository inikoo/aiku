<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 05:07:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Dispatching\DeliveryNote;

use App\Enums\EnumHelperTrait;

enum DeliveryNoteStateEnum: string
{
    use EnumHelperTrait;

    case SUBMITTED       = 'submitted';
    case IN_QUEUE        = 'in_queue';
    case PICKER_ASSIGNED = 'picker_assigned';
    case PICKING         = 'picking';
    case PICKED          = 'picked';
    case PACKING         = 'packing';
    case PACKED          = 'packed';
    case FINALISED       = 'finalised';
    case SETTLED         = 'settled';

    public static function labels($forElements = false): array
    {
        return [
            'submitted'            => __('Submitted'),
            'in_queue'             => __('In Queue'),
            'picker_assigned'      => __('Picker Assigned'),
            'picking'              => __('Picking'),
            'picked'               => __('Picked'),
            'packing'              => __('Packing'),
            'packed'               => __('Packed'),
            'finalised'            => __('Finalised'),
            'settled'              => __('Settled'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'submitted'   => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'in_queue'    => [
                'tooltip' => __('In Queue'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picker_assigned'    => [
                'tooltip' => __('Picker Assigned'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald',
                'app'     => [
                    'name' => 'spell-check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picking'     => [
                'tooltip' => __('Picking'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-slate-500',
                'color'   => 'slate',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'picked' => [
                'tooltip' => __('Picked'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'slate',
                'app'     => [
                    'name' => 'times',
                    'type' => 'font-awesome-5'
                ]
            ],
            'packing'   => [
                'tooltip' => __('Packing'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'settled'    => [
                'tooltip' => __('Settled'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
            'finalised'    => [
                'tooltip' => __('Finalised'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
            'finalised'    => [
                'tooltip' => __('Finalised'),
                'icon'    => 'fal fa-check-double',
                'class'   => 'text-purple-500',
                'color'   => 'purple',
                'app'     => [
                    'name' => 'check-double',
                    'type' => 'font-awesome-5'
                ]
            ],
        ];
    }
}
