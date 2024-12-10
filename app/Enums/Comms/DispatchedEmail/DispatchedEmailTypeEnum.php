<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\DispatchedEmail;

use App\Enums\Comms\Outbox\OutboxTypeEnum;
use App\Enums\EnumHelperTrait;

enum DispatchedEmailTypeEnum: string
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

}
