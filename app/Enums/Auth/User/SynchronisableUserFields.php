<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Jun 2023 18:49:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth\User;

use App\Enums\EnumHelperTrait;

enum SynchronisableUserFields: string
{
    use EnumHelperTrait;

    case USERNAME             = 'username';
    case PASSWORD             = 'password';
    case EMAIL                = 'email';
}
