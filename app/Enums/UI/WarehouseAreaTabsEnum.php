<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WarehouseAreaTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;
    case LOCATIONS           = 'locations';
    case STATS               = 'stats';

    case CHANGELOG           = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            WarehouseAreaTabsEnum::LOCATIONS             => [
                'title' => __('locations'),
                'icon'  => 'fal fa-chart-line',
            ],
            WarehouseAreaTabsEnum::STATS => [
                'title' => __('stats'),
                'icon'  => 'fal fa-map-signs',
            ],
            WarehouseAreaTabsEnum::CHANGELOG     => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-inventory',
            ],
        };
    }
}
