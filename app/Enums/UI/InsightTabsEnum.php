<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum InsightTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case DATA         = 'data';
    case POOL_OPTIONS = 'pool_options';


    public function blueprint(): array
    {
        return match ($this) {
            InsightTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            InsightTabsEnum::POOL_OPTIONS => [
                'title' => __('pool options'),
                'icon'  => 'fal fa-dollar-sign',
            ],
        };
    }
}
