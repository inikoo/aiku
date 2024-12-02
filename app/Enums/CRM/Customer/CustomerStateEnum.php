<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:22:44 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\CRM\Customer;

use App\Enums\EnumHelperTrait;

enum CustomerStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in-process';
    case REGISTERED = 'registered';
    case ACTIVE = 'active';
    case LOSING = 'losing';
    case LOST = 'lost';

    public static function labels(): array
    {
        return [
            'in-process' => __('In Process'),
            'registered' => __('Registered'),
            'active'     => __('Active'),
            'losing'     => __('Losing'),
            'lost'       => __('Lost'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process'    => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-circle-notch',
                'class'   => 'text-lime-500',
                'color'   => 'lime'
            ],
            'registered'    => [
                'tooltip' => __('Registered'),
                'icon'    => 'fas fa-exclamation-circle',
                'class'   => 'text-orange-500',
                'color'   => 'orange'
            ],
            'active'        => [
                'tooltip' => __('Active'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald',
            ],
            'losing' => [
                'tooltip' => __('Losing'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-orange-500',
                'color'   => 'orange',
            ],
            'lost'  => [
                'tooltip' => __('Lost'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red',
            ],
        ];
    }

}
