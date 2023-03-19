<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

enum WarehouseTabsEnum: string
{
    case STATS           = 'stats';
    case WAREHOUSE_AREAS = 'warehouse_areas';
    case LOCATIONS       = 'locations';
    case DATA            = 'data';
    case CHANGELOG       = 'changelog';
}
