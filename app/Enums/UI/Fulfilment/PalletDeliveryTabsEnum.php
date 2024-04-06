<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:17:32 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PalletDeliveryTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case PALLETS = 'pallets';
    case HISTORY = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            PalletDeliveryTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            PalletDeliveryTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-bars',
            ],
        };
    }
}
