<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\PostRoom;

use App\Enums\EnumHelperTrait;

enum PostRoomCodeEnum: string
{
    use EnumHelperTrait;

    case NEWSLETTER = 'newsletter';
    case MARKETING = 'marketing';
    case MARKETING_NOTIFICATION = 'marketing-notification'; // halfway between marketing and transactional
    case CUSTOMER_NOTIFICATION = 'customer-notification'; // e.g. forgot email, welcome email, etc
    case COLD_EMAIL = 'cold-emails'; // send to prospects
    case USER_NOTIFICATION = 'user-notification'; // internal notifications
    case TEST = 'test';

    public function label(): string
    {
        return match ($this) {
            PostRoomCodeEnum::NEWSLETTER => 'Newsletters',
            PostRoomCodeEnum::MARKETING => 'Marketing',
            PostRoomCodeEnum::MARKETING_NOTIFICATION => 'Marketing notifications',
            PostRoomCodeEnum::CUSTOMER_NOTIFICATION => 'Customer notifications',
            PostRoomCodeEnum::COLD_EMAIL => 'Cold emails',
            PostRoomCodeEnum::USER_NOTIFICATION => 'User notifications',
            PostRoomCodeEnum::TEST => 'Tests',
        };
    }
}
