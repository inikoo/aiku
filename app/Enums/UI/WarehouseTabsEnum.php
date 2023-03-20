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
    case DASHBOARD           = 'dashboard';
    case WAREHOUSE_AREAS     = 'warehouse_areas';
    case LOCATIONS           = 'locations';
    case DATA                = 'data';
    case CHANGELOG           = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            WarehouseTabsEnum::DASHBOARD             => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            WarehouseTabsEnum::WAREHOUSE_AREAS => [
                'title' => __('warehouse areas'),
                'icon'  => 'fal fa-map-signs',
            ],
            WarehouseTabsEnum::LOCATIONS     => [
                'title' => __('locations'),
                'icon'  => 'fal fa-inventory',
            ],
            WarehouseTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            WarehouseTabsEnum::CHANGELOG     => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
            ],
        };
    }
}
