<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

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
