<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 12:57:15 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Auth\WebUser;

use App\Enums\EnumHelperTrait;

enum WebUserTypeEnum: string
{
    use EnumHelperTrait;

    case WEB = 'web';
    case API = 'api';
}
