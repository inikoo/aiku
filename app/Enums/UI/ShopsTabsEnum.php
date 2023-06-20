<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Jun 2023 20:10:15 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

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
                'icon'  => 'fal fa-folders',
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
