<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ClockingTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case SHOWCASE           = 'showcase';
    case PARTS              = 'parts';
    case STOCK_MOVEMENTS    = 'stock_movements';

    case HISTORY            = 'history';
    case DATA               = 'data';



    public function blueprint(): array
    {
        return match ($this) {
            ClockingTabsEnum::PARTS => [
                'title' => __('parts'),
                'icon'  => 'fal fa-box',
            ],
            ClockingTabsEnum::STOCK_MOVEMENTS => [
                'title' => __('stock movements'),
                'icon'  => 'fal fa-exchange',
            ],
            ClockingTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ClockingTabsEnum::DATA => [
                'type'  => 'icon',
                'align' => 'right',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            ClockingTabsEnum::SHOWCASE => [
                'title' => __('clocking'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
