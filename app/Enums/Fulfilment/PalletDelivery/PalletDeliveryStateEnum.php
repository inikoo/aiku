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

    case IN_PROCESS  = 'in-process';
    case READY       = 'ready';
    case RECEIVED    = 'received';
    case DONE        = 'done';

    public function labels(): array
    {
        return [
            'in-process' => __('In Process'),
            'ready'      => __('Ready'),
            'received'   => __('Received'),
            'done'       => __('Done')
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-indigo-500'
            ],
            'ready' => [
                'tooltip' => __('Ready'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
            'received' => [
                'tooltip' => __('Received'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
            'done' => [
                'tooltip' => __('Done'),
                'icon'    => 'fal fa-spell-check',
                'class'   => 'text-green-500'
            ],
        ];
    }
}
