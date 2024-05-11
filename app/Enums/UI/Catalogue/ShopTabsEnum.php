<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:45:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Catalogue;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ShopTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE         = 'showcase';
    case DASHBOARD        = 'dashboard';
    case HISTORY          = 'history';

    public function blueprint(): array
    {
        return match ($this) {

            ShopTabsEnum::DASHBOARD => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
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
