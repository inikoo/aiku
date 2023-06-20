<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Jun 2023 16:02:26 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ShopTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE        = 'showcase';
    case DASHBOARD       = 'dashboard';

    case DEPARTMENTS      = 'departments';
    case FAMILIES         = 'families';
    case PRODUCTS         = 'products';
    case HISTORY          = 'history';
    case DATA             = 'data';

    public function blueprint(): array
    {
        return match ($this) {

            ShopTabsEnum::DASHBOARD => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            ShopTabsEnum::DEPARTMENTS => [
                'title' => __('departments'),
                'icon'  => 'fal fa-folders',
            ],
            ShopTabsEnum::FAMILIES => [
                'title' => __('families'),
                'icon'  => 'fal fa-folder',
            ],
            ShopTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],
            ShopTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            ShopTabsEnum::HISTORY => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
            ],
            ShopTabsEnum::SHOWCASE => [
                'title' => __('shop'),
                'icon'  => 'fas fa-info-circle',
            ],

        };
    }
}
