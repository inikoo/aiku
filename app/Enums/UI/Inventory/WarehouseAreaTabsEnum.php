<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 14:32:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Inventory;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WarehouseAreaTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE            = 'showcase';
    case LOCATIONS           = 'locations';
    case HISTORY             = 'history';



    public function blueprint(): array
    {
        return match ($this) {
            WarehouseAreaTabsEnum::LOCATIONS             => [
                'title' => __('locations'),
                'icon'  => 'fal fa-chart-line',
            ],
            WarehouseAreaTabsEnum::HISTORY     => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
            ],
            WarehouseAreaTabsEnum::SHOWCASE => [
                'title' => __('warehouse area'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
