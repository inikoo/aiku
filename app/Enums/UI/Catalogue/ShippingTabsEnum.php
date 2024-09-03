<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 09:29:02 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Catalogue;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ShippingTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case CURRENT      = 'current';
    case OFFER        = 'offer';
    case SCHEMAS      = 'schemas';
    case HISTORY      = 'history';


    public function blueprint(): array
    {
        return match ($this) {

            ShippingTabsEnum::CURRENT => [
                'title' => __('current'),
                'icon'  => 'fal fa-shopping-cart',
            ],
            ShippingTabsEnum::OFFER => [
                'title' => __('offer'),
                'icon'  => 'fal fa-chart-pie',
            ],
            ShippingTabsEnum::SCHEMAS => [
                'title' => __('schemas'),
                'icon'  => 'fal fa-tags',
                'type'  => 'icon',
                'align' => 'right'
            ],
            ShippingTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ],
        };
    }
}
