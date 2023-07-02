<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 13:02:40 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth\WebUser;

use App\Enums\EnumHelperTrait;

enum WebUserAuthTypeEnum: string
{
    use EnumHelperTrait;

    case DEFAULT             = 'default';
    case AURORA              = 'aurora';
}
