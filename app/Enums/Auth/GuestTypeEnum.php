<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 23:47:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth;

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
            'contractor' => 'Contractor',
            'external_employee' => 'External Employee',
            'external_administrator' => 'External Administrator',
        ];
    }

}
