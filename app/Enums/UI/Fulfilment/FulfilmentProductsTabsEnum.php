<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:19:39 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum FulfilmentProductsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case PRODUCTS                     = 'products';



    case HISTORY                       = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            FulfilmentProductsTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-bars',
            ],

            FulfilmentProductsTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ]
        };
    }
}
