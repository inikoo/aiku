<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 Mar 2023 12:14:08 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum CatalogueTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD        = 'dashboard';
    case DEPARTMENTS      = 'departments';
    case FAMILIES         = 'families';
    case PRODUCTS         = 'products';



    public function blueprint(): array
    {
        return match ($this) {
            CatalogueTabsEnum::DASHBOARD => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            CatalogueTabsEnum::DEPARTMENTS => [
                'title' => __('warehouse areas'),
                'icon'  => 'fal fa-map-signs',
            ],
            CatalogueTabsEnum::FAMILIES => [
                'title' => __('locations'),
                'icon'  => 'fal fa-inventory',
            ],
            CatalogueTabsEnum::PRODUCTS => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],
        };
    }
}
