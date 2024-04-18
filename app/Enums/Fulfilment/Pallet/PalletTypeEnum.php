<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:23:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\Pallet;

use App\Enums\EnumHelperTrait;

enum PalletTypeEnum: string
{
    use EnumHelperTrait;

    case PALLET   = 'pallet';
    case BOX      = 'box';
    case OVERSIZE = 'oversize';


    public static function typeIcon(): array
    {
        return [
            'pallet' => [
                'tooltip' => __('pallet'),
                'icon'    => 'fal fa-pallet',
                'class'   => 'text-lime-500',
                'color'   => 'lime',
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'box' => [
                'tooltip' => __('box'),
                'icon'    => 'fal fa-box',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ],
            'oversize' => [
                'tooltip' => __('oversize'),
                'icon'    => 'fal fa-sort-size-up',
                'class'   => 'text-indigo-400',
                'color'   => 'indigo',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ]
        ];
    }
}
