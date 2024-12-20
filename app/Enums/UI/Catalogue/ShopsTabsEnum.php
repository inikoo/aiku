<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:45:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Catalogue;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ShopsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOPS                       = 'shops';
    case DEPARTMENTS                 = 'departments';
    case FAMILIES                    = 'families';
    case PRODUCTS                    = 'products';

    public function blueprint(): array
    {
        return match ($this) {
            ShopsTabsEnum::SHOPS => [
                'title' => __('shops'),
                'icon'  => 'fal fa-store-alt',
            ],
            ShopsTabsEnum::DEPARTMENTS => [
                'title' => __('departments'),
                'icon'  => 'fal fa-folder-tree',
            ],
            ShopsTabsEnum::FAMILIES => [
                'title' => __('families'),
                'icon'  => 'fal fa-folder',
            ],
            ShopsTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],

        };
    }
}
