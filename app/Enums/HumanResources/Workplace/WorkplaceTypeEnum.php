<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Jun 2023 23:19:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\HumanResources\Workplace;

use App\Enums\EnumHelperTrait;

enum WorkplaceTypeEnum: string
{
    use EnumHelperTrait;

    case HQ     = 'hq';
    case BRANCH = 'branch';

    case HOME = 'home';

    case GROUP_PREMISSES = 'group-premisses';

    case CLIENT_PREMISES = 'client-premises';
    case ROAD            = 'road';

    case OTHER = 'other';

}
