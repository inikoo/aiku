<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 15:06:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Web\WebUser;

use App\Enums\EnumHelperTrait;

enum WebUserLoginVersionEnum: string
{
    use EnumHelperTrait;

    case AURORA = 'aurora';
    case AIKU   = 'aiku';
}
