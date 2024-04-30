<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Jun 2023 23:19:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\HumanResources\TimeTracker;

use App\Enums\EnumHelperTrait;

enum TimeTrackerStatusEnum: string
{
    use EnumHelperTrait;

    case CREATING = 'creating';

    case OPEN = 'open';

    case CLOSED = 'closed';

    case ERROR = 'error';


}
