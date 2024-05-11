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
    case CLOCKING_MACHINES  = 'clocking_machines';
    case CLOCKINGS          = 'clockings';
    case HISTORY            = 'history';
    case DATA               = 'data';


    public function blueprint(): array
    {
        return match ($this) {
            WorkplaceTabsEnum::CLOCKING_MACHINES => [
                'title' => __('clocking machines'),
                'icon'  => 'fal fa-chess-clock',
            ],
            WorkplaceTabsEnum::CLOCKINGS => [
                'title' => __('clocking'),
                'icon'  => 'fal fa-clock',
            ],
            WorkplaceTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WorkplaceTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            WorkplaceTabsEnum::SHOWCASE => [
                'title' => __('workplace'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
