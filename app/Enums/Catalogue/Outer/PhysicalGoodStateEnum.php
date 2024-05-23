<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 May 2024 09:45:43 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\Outer;

use App\Enums\EnumHelperTrait;
use App\Models\Catalogue\Shop;

enum PhysicalGoodStateEnum: string
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
            'in-process'    => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-lime-500',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'active'        => [
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
            'discontinued'  => [
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

    public static function count(Shop $parent): array
    {
        $stats = $parent->stats;

        return [
            'in-process'    => $stats->number_physical_goods_state_in_process,
            'active'        => $stats->number_physical_goods_state_active,
            'discontinuing' => $stats->number_physical_goods_state_discontinuing,
            'discontinued'  => $stats->number_physical_goods_state_discontinued
        ];
    }


}
