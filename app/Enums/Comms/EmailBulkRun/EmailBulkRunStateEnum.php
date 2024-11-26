<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 25 Nov 2024 11:09:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\EmailBulkRun;

use App\Enums\EnumHelperTrait;

enum EmailBulkRunStateEnum: string
{
    use EnumHelperTrait;


    case SCHEDULED  = 'scheduled';
    case SENDING    = 'sending';
    case SENT       = 'sent';
    case CANCELLED  = 'cancelled';
    case STOPPED    = 'stopped';

    public static function labels(): array
    {
        return [
            'scheduled'  => __('Scheduled'),
            'sending'    => __('Sending'),
            'sent'       => __('Sent'),
            'cancelled'  => __('Cancelled'),
            'stopped'    => __('Stopped'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'scheduled' => [

                'tooltip' => __('Scheduled'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-indigo-500'


            ],
            'sending'        => [

                'tooltip' => __('sending'),
                'icon'    => 'fal fa-paper-plane',
                'class'   => 'animate-pulse'

            ],
            'sent'     => [

                'tooltip' => __('sent'),
                'icon'    => 'fal fa-paper-plane',
                'class'   => 'text-green-600'
            ],
            'cancelled'     => [

                'tooltip' => __('cancelled'),
                'icon'    => 'fal fa-paper-plane',
                'class'   => 'text-red-600'
            ],
            'stopped'     => [

                'tooltip' => __('stopped'),
                'icon'    => 'fas fa-stop',
                'class'   => 'text-red-600'
            ],

        ];
    }
}
