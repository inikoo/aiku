<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 May 2023 11:13:40 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth;

use App\Enums\EnumHelperTrait;

enum SynchronisableUserFields: string
{
    use EnumHelperTrait;

    case USERNAME             = 'username';
    case PASSWORD             = 'password';
    case EMAIL                = 'email';
}
