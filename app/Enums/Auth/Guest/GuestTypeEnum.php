<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Jun 2023 18:49:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth\Guest;

use App\Enums\EnumHelperTrait;

enum GuestTypeEnum: string
{
    use EnumHelperTrait;

    case CONTRACTOR             = 'contractor';
    case EXTERNAL_EMPLOYEE      = 'external_employee';
    case EXTERNAL_ADMINISTRATOR = 'external_administrator';


    public static function labels(): array
    {
        return [
            'contractor'             => 'Contractor',
            'external_employee'      => 'External Employee',
            'external_administrator' => 'External Administrator',
        ];
    }

}
