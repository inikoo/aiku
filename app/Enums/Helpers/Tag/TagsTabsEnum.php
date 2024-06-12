<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 19:39:58 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Helpers\Tag;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum TagsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case TAGS     = 'tags';

    case HISTORY   = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            TagsTabsEnum::TAGS => [
                'title' => __('tags'),
                'icon'  => 'fal fa-tags',
            ],
            TagsTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ]
        };
    }
}
