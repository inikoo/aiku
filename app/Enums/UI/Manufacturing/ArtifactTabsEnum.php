<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 14:32:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Manufacturing;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ArtifactTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE                = 'showcase';
    case DASHBOARD               = 'dashboard';
    case MANUFACTURE_TASKS       = 'manufacture_taskS';
    case HISTORY                 = 'history';
    case DATA                    = 'data';



    public function blueprint(): array
    {
        return match ($this) {
            ArtifactTabsEnum::DASHBOARD => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            ArtifactTabsEnum::MANUFACTURE_TASKS => [
                'title' => __('manufacture tasks'),
                'icon'  => 'fal fa-hamsa',
            ],
            ArtifactTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            ArtifactTabsEnum::HISTORY => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
            ],
            ArtifactTabsEnum::SHOWCASE => [
                'title' => __('warehouse'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
