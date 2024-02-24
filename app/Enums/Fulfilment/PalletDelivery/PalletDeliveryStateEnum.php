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

    case IN_PROCESS      = 'in-process';
    case SUBMITTED       = 'submitted';
    case CONFIRMED       = 'confirmed';
    case RECEIVED        = 'received';
    case DONE            = 'done';

    public function labels(): array
    {
        return [
            'in-process'     => __('In Process'),
            'submitted'      => __('Submitted'),
            'confirmed'      => __('Confirmed'),
            'received'       => __('Received'),
            'done'           => __('Done')
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-emerald-500'
            ],
            'submitted' => [
                'tooltip' => __('Submitted'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
            'confirmed' => [
                'tooltip' => __('Confirmed'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
            'received' => [
                'tooltip' => __('Received'),
                'icon'    => 'fal fa-truck-loading',
                'class'   => 'text-blue-500'
            ],
            'done' => [
                'tooltip' => __('Done'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
        ];
    }
}
