<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:11:03 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\SysAdmin;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum GuestTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE                       = 'showcase';
    case HISTORY                        = 'history';


    public function blueprint(): array
    {
        return match ($this) {

            GuestTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            GuestTabsEnum::SHOWCASE => [
                'title' => __('guest'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
