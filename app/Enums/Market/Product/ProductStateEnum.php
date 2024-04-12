<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 01:42:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Market\Product;

use App\Enums\EnumHelperTrait;

enum ProductStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS    = 'in-process';
    case ACTIVE        = 'active';
    case DISCONTINUING = 'discontinuing';
    case DISCONTINUED  = 'discontinued';

    public static function labels(): array
    {
        return [
            'in-process'    => __('In Process'),
            'active'        => __('Active'),
            'discontinuing' => __('Discontinuing'),
            'discontinued'  => __('Discontinued'),
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
            'active' => [
                'tooltip' => __('Active'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-500',
                'color'   => 'green',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'discontinuing' => [
                'tooltip' => __('Discontinuing'),
                'icon'    => 'fal fa-exclamation-triangle',
                'class'   => 'text-orange-500',
                'color'   => 'orange',
                'app'     => [
                    'name' => 'exclamation-triangle',
                    'type' => 'font-awesome-5'
                ]
            ],
            'discontinued' => [
                'tooltip' => __('Discontinued'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'red',
                'app'     => [
                    'name' => 'times',
                ]
            ],
        ];
    }


}
