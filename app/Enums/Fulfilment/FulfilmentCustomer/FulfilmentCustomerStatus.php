<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 15:19:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\FulfilmentCustomer;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;

enum FulfilmentCustomerStatus: string
{
    use EnumHelperTrait;

    case NO_RENTAL_AGREEMENT = 'no_rental_agreement';
    case ACTIVE              = 'active';
    case INACTIVE            = 'inactive';
    case LOST                = 'lost';

    public static function labels(): array
    {
        return [
            'no_rental_agreement' => __('No Rental Agreement'),
            'active'              => __('Active'),
            'inactive'            => __('Loosing'),
            'lost'                => __('Lost'),
        ];
    }

    public static function count(Fulfilment $parent): array
    {
        $stats = $parent->stats;


        return [
            'no_rental_agreement' => $stats->number_customers_status_no_rental_agreement,
            'active'              => $stats->number_customers_status_active,
            'inactive'            => $stats->number_customers_status_inactive,
            'lost'                => $stats->number_customers_status_lost,
        ];
    }

    public static function statusIcon(): array
    {
        return [
            'no_rental_agreement' => [
                'tooltip' => __('No Rental Agreement'),
                'icon'    => 'fal fa-seedling',
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'inactive'            => [
                'tooltip' => __('Inactive'),
                'icon'    => 'fal fa-share',
                'class'   => 'text-indigo-400',
                'color'   => 'grey',
                'app'     => [
                    'name' => 'share',
                    'type' => 'font-awesome-5'
                ]
            ],
            'active'              => [
                'tooltip' => __('Active'),
                'icon'    => 'fal fa-check',
                'class'   => 'text-green-500',
                'color'   => 'green',
                'app'     => [
                    'name' => 'check',
                    'type' => 'font-awesome-5'
                ]
            ],
            'lost'                => [
                'tooltip' => __('Lost'),
                'icon'    => 'fal fa-times',
                'class'   => 'text-red-500',
                'color'   => 'red',
                'app'     => [
                    'name' => 'times',
                    'type' => 'font-awesome-5'
                ]
            ]
        ];
    }

}
