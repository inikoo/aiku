<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:18:05 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StoredItemTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE = 'showcase';
    case PALLETS  = 'pallets';

    case DATA    = 'data';
    case HISTORY = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            StoredItemTabsEnum::SHOWCASE => [
                'title' => __('stored item'),
                'icon'  => 'fas fa-info-circle',
            ],
            StoredItemTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-pallet',
            ],
            StoredItemTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            StoredItemTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
