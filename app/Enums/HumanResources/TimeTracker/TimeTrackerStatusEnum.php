<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:46:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\HumanResources\TimeTracker;

use App\Enums\EnumHelperTrait;

enum TimeTrackerStatusEnum: string
{
    use EnumHelperTrait;

    case OPEN = 'open';

    case CLOSED = 'closed';

    case ERROR = 'error';


}
