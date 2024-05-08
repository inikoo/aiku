<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 14:31:41 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\SysAdmin;

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
