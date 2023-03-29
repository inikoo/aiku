<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WarehouseTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD       = 'dashboard';
    case WAREHOUSE_AREAS = 'warehouse_areas';
    case LOCATIONS       = 'locations';
    case HISTORY         = 'history';
    case DATA            = 'data';



    public function blueprint(): array
    {
        return match ($this) {
            WarehouseTabsEnum::DASHBOARD => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            WarehouseTabsEnum::WAREHOUSE_AREAS => [
                'title' => __('warehouse areas'),
                'icon'  => 'fal fa-map-signs',
            ],
            WarehouseTabsEnum::LOCATIONS => [
                'title' => __('locations'),
                'icon'  => 'fal fa-inventory',
            ],
            WarehouseTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            WarehouseTabsEnum::HISTORY => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
            ],
        };
    }
}
