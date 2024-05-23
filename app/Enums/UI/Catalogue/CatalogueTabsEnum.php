<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:45:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Catalogue;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum CatalogueTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD = 'dashboard';

    // case DEPARTMENTS      = 'departments';
    // case FAMILIES         = 'families';
    // case PRODUCTS         = 'products';
    // case COLLECTIONS = 'collections';
    case HISTORY = 'history';



    public function blueprint(): array
    {
        return match ($this) {
            CatalogueTabsEnum::DASHBOARD => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            /*
            CatalogueTabsEnum::DEPARTMENTS => [
                'title' => __('departments'),
                'icon'  => 'fal fa-folder-tree',
            ],
            CatalogueTabsEnum::FAMILIES => [
                'title' => __('families'),
                'icon'  => 'fal fa-folder',
            ],
            CatalogueTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],
               CatalogueTabsEnum::COLLECTIONS => [
                'title' => __('collections'),
                'icon'  => 'fal fa-clock',
            ],

            */

            CatalogueTabsEnum::HISTORY => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
            ],


        };
    }
}
