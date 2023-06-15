<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Jun 2023 08:28:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth\User;

use App\Enums\EnumHelperTrait;

enum UserAuthTypeEnum: string
{
    use EnumHelperTrait;

    case DEFAULT             = 'default';
    case AURORA              = 'aurora';
}
