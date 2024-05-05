<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum UserTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE     = 'showcase';
    case HISTORY      = 'history';
    case REQUEST_LOGS = 'request_logs';


    public function blueprint(): array
    {
        return match ($this) {
            UserTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            UserTabsEnum::SHOWCASE => [
                'title' => __('user'),
                'icon'  => 'fas fa-info-circle',
            ],
            UserTabsEnum::REQUEST_LOGS => [
                'title' => __('Visit logs'),
                'icon'  => 'fas fa-road',
            ],
        };
    }
}
