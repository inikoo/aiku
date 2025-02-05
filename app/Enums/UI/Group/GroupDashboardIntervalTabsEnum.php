<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 05-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Enums\UI\Group;

use App\Enums\EnumHelperTrait;

enum GroupDashboardIntervalTabsEnum: string
{
    use EnumHelperTrait;

    case SALES      = 'sales';
    case SHOPS      = 'shops';

}
