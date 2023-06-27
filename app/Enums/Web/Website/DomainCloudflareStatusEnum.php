<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 00:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Web\Website;

use App\Enums\EnumHelperTrait;

enum DomainCloudflareStatusEnum: string
{
    use EnumHelperTrait;

    case PENDING = 'pending';
    case ACTIVE  = 'active';
}
