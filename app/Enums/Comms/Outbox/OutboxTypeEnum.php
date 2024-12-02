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
    case MARKETING_NOTIFICATION = 'marketing-notification'; // halfway between marketing and transactional
    case CUSTOMER_NOTIFICATION = 'customer-notification'; // e.g. forgot email, welcome email, etc
    case COLD_EMAIL = 'cold-emails'; // send to prospects
    case USER_NOTIFICATION =  'user-notification'; // internal notifications
    case TEST = 'test';

    public function label(): string
    {
        return match ($this) {
            OutboxTypeEnum::NEWSLETTER => 'Newsletters',
            OutboxTypeEnum::MARKETING => 'Marketing',
            OutboxTypeEnum::MARKETING_NOTIFICATION => 'Marketing notifications',
            OutboxTypeEnum::CUSTOMER_NOTIFICATION => 'Customer notifications',
            OutboxTypeEnum::COLD_EMAIL => 'Cold emails',
            OutboxTypeEnum::USER_NOTIFICATION => 'User notifications',
            OutboxTypeEnum::TEST => 'Tests',
        };
    }


    public function stateIcon(): array
    {
        return [
            OutboxTypeEnum::NEWSLETTER->value => [
                'tooltip' => __(OutboxTypeEnum::NEWSLETTER->value),
                'icon'    => 'fal fa-circle-notch',
                'class'   => 'text-lime-500',
                'color'   => 'lime'
            ],
            OutboxTypeEnum::MARKETING->value    => [
                'tooltip' => __(OutboxTypeEnum::MARKETING->value),
                'icon'    => 'fas fa-exclamation-circle',
                'class'   => 'text-orange-500',
                'color'   => 'orange'
            ],
            OutboxTypeEnum::MARKETING_NOTIFICATION->value        => [
                'tooltip' => __(OutboxTypeEnum::MARKETING_NOTIFICATION->value),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-emerald-500',
                'color'   => 'emerald'
            ],
            OutboxTypeEnum::CUSTOMER_NOTIFICATION->value  => [
                'tooltip' => __(OutboxTypeEnum::CUSTOMER_NOTIFICATION->value),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red'
            ],
            OutboxTypeEnum::COLD_EMAIL->value  => [
                'tooltip' => __(OutboxTypeEnum::COLD_EMAIL->value),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red'
            ],
            OutboxTypeEnum::USER_NOTIFICATION->value  => [
                'tooltip' => __(OutboxTypeEnum::USER_NOTIFICATION->value),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red'
            ],
            OutboxTypeEnum::TEST->value  => [
                'tooltip' => __(OutboxTypeEnum::TEST->value),
                'icon'    => 'fas fa-circle',
                'class'   => 'text-red-500',
                'color'   => 'red'
            ]
        ];
    }
}
