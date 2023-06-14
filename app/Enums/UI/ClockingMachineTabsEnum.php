<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ClockingMachineTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE            = 'showcase';
    case CLOCKINGS           = 'clockings';

    case HISTORY             = 'history';
    case DATA                = 'data';


    public function blueprint(): array
    {
        return match ($this) {
            ClockingMachineTabsEnum::CLOCKINGS => [
                'title' => __('clockings'),
                'icon'  => 'fal fa-clock',
            ],
            ClockingMachineTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ClockingMachineTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ClockingMachineTabsEnum::SHOWCASE => [
                'title' => __('clocking machine'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
