<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 22 Nov 2024 12:15:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Outbox;

use App\Enums\EnumHelperTrait;

enum OutboxTypeEnum: string
{
    use EnumHelperTrait;

    case NEWSLETTER = 'newsletter';
    case MARKETING  = 'marketing';
    case NOTIFICATION = 'notification'; // halfway between marketing and transactional
    case TRANSACTIONAL = 'transactional';
    case COLD_EMAIL = 'cold-email';
    case APP_COMMS = 'app-comms';
    case TEST = 'test';

    public function stateIcon(): array
    {
        return [
            OutboxTypeEnum::NEWSLETTER->value => [
                'tooltip' => __('In process'),
                'icon'    => 'fal fa-circle-notch',
                'class'   => 'text-lime-500',
                'color'   => 'lime'
            ],
            OutboxTypeEnum::MARKETING->value    => [
                'tooltip' => __('Registered'),
                'icon'    => 'fas fa-exclamation-circle',
                'class'   => 'text-orange-500',
                'color'   => 'orange'
            ],
            OutboxTypeEnum::NOTIFICATION->value        => [
                'tooltip' => __('Active'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald',
            ],
            OutboxTypeEnum::TRANSACTIONAL->value  => [
                'tooltip' => __('Lost'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red',
            ],
            OutboxTypeEnum::COLD_EMAIL->value  => [
                'tooltip' => __('Lost'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red',
            ],
            OutboxTypeEnum::APP_COMMS->value  => [
                'tooltip' => __('Lost'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red',
            ],
            OutboxTypeEnum::TEST->value  => [
                'tooltip' => __('Lost'),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red',
            ]
        ];
    }
}
