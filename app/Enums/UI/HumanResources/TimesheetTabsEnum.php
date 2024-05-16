<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:14:46 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\HumanResources;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum TimesheetTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case TIME_TRACKERS = 'time_trackers';
    case HISTORY       = 'history';
    case CLOCKINGS     = 'clockings';


    public function blueprint(): array
    {
        return match ($this) {
            TimesheetTabsEnum::TIME_TRACKERS => [
                'title' => __('working periods'),
                'icon'  => 'fal fa-arrows-h',
            ],
            TimesheetTabsEnum::CLOCKINGS => [
                'title' => __('clockings'),
                'icon'  => 'fal fa-vote-yea',

            ],

            TimesheetTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
