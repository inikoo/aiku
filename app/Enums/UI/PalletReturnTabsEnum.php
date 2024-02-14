<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PalletReturnTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case PALLETS = 'pallets';
    case HISTORY = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            PalletReturnTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            PalletReturnTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-bars',
            ],
        };
    }
}
