<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ProformaTabsEnum: string
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
            ProformaTabsEnum::SHOWCASE => [
                'title' => __('stored item'),
                'icon'  => 'fas fa-info-circle',
            ],
            ProformaTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-pallet',
            ],
            ProformaTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            ProformaTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
