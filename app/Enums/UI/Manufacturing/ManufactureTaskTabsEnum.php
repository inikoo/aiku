<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 May 2024 11:21:10 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Manufacturing;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ManufactureTaskTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE            = 'showcase';
    case HISTORY             = 'history';
    case ARTEFACT            = 'artefact';

    public function blueprint(): array
    {
        return match ($this) {
            ManufactureTaskTabsEnum::SHOWCASE => [
                'title' => __('showcase'),
                'icon'  => 'fal fa-drone',
            ],
            ManufactureTaskTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ],
            ManufactureTaskTabsEnum::ARTEFACT => [
                'title' => __('artefact'),
                'icon'  => 'fal fa-drone',
            ]
        };
    }
}
