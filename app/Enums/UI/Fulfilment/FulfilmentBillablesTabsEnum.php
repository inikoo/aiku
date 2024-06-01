<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:19:39 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum FulfilmentBillablesTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD                       = 'dashboard';
    case HISTORY                         = 'history';
    case BILLABLES                       = 'billables';

    public function blueprint(): array
    {
        return match ($this) {
            FulfilmentBillablesTabsEnum::DASHBOARD => [
                'title' => __('dashboard'),
                'icon'  => 'fal fa-tachometer-alt-fast',
            ],
            FulfilmentBillablesTabsEnum::BILLABLES => [
                'title' => __('products list'),
                'icon'  => 'fal fa-bars',
                'type'  => 'icon',
                'align' => 'right',
            ],

            FulfilmentBillablesTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ]
        };
    }
}
