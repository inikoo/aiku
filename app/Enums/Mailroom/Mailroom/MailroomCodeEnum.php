<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Mailroom\Mailroom;

use App\Enums\EnumHelperTrait;

enum MailroomCodeEnum: string
{
    use EnumHelperTrait;

    case MARKETING             = 'marketing';
    case CUSTOMER_NOTIFICATION = 'customer-notification';
    case USER_NOTIFICATION     = 'user-notification';

    public function label(): string
    {
        return match ($this) {
            MailroomCodeEnum::MARKETING             => 'Marketing',
            MailroomCodeEnum::CUSTOMER_NOTIFICATION => 'Customer notifications',
            MailroomCodeEnum::USER_NOTIFICATION     => 'User notifications',
        };
    }
}
