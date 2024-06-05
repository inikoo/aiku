<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:45:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Dropshipping;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum AssetsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD = 'dashboard';

    // case DEPARTMENTS      = 'departments';
    // case FAMILIES         = 'families';
    // case PRODUCTS         = 'products';
    // case COLLECTIONS = 'collections';



    public function blueprint(): array
    {
        return match ($this) {
            AssetsTabsEnum::DASHBOARD => [
                'title' => __('dashboard'),
                'icon'  => 'fal fa-tachometer-alt-fast',
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



        };
    }
}
