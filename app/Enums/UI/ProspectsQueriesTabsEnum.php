<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 03:22:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ProspectsQueriesTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case LISTS     = 'lists';

    case HISTORY   = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            ProspectsQueriesTabsEnum::LISTS => [
                'title' => __('lists'),
                'icon'  => 'fal fa-code-branch',
            ],
            ProspectsQueriesTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ]
        };
    }
}
