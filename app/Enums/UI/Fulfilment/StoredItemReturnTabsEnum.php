<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:16:28 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StoredItemReturnTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case ITEMS   = 'items';
    case HISTORY = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            StoredItemReturnTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            StoredItemReturnTabsEnum::ITEMS => [
                'title' => __('items'),
                'icon'  => 'fal fa-bars',
            ],
        };
    }
}
