<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 14:52:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum FulfilmentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case DASHBOARD                     = 'dashboard';
    case PALLETS                       = 'pallets';
    case HISTORY                       = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            FulfilmentTabsEnum::DASHBOARD => [
                'title' => __('dashboard'),
                'icon'  => 'fas fa-info-circle',
            ],
            FulfilmentTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-bars',
            ],
            FulfilmentTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ]
        };
    }
}
