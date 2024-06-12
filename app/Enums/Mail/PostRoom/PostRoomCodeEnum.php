<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\PostRoom;

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
            PostRoomCodeEnum::MARKETING             => 'Deals',
            PostRoomCodeEnum::LEADS                 => 'Leads',
            PostRoomCodeEnum::CUSTOMER_NOTIFICATION => 'Customer notifications',
            PostRoomCodeEnum::USER_NOTIFICATION     => 'User notifications',
            PostRoomCodeEnum::TESTS                 => 'tests',
        };
    }
}
