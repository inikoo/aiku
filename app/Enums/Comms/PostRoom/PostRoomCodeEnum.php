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

    case MARKETING             = 'marketing';
    case LEADS                 = 'leads';
    case CUSTOMER_NOTIFICATION = 'customer-notification';
    case USER_NOTIFICATION     = 'user-notification';
    case TESTS                 = 'tests';

    public function label(): string
    {
        return match ($this) {
            PostRoomCodeEnum::NEWSLETTER => 'Newsletters',
            PostRoomCodeEnum::MARKETING => 'Marketing',
            PostRoomCodeEnum::MARKETING_NOTIFICATION => 'Marketing notifications',
            PostRoomCodeEnum::CUSTOMER_NOTIFICATION => 'Customer notifications',
            PostRoomCodeEnum::USER_NOTIFICATION     => 'User notifications',
            PostRoomCodeEnum::TESTS                 => 'tests',
        };
    }
}
