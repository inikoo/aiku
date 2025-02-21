<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:16:33 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\HumanResources;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WorkplaceTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE           = 'showcase';

    case HISTORY            = 'history';


    public function blueprint(): array
    {
        return match ($this) {


            WorkplaceTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WorkplaceTabsEnum::SHOWCASE => [
                'title' => __('overview'),
                'icon'  => 'fal fa-info-circle',
            ],
        };
    }
}
