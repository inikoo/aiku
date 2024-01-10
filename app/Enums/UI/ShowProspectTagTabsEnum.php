<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Nov 2023 10:36:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ShowProspectTagTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case PROSPECTS = 'prospects';
    case HISTORY   = 'history';

    public function blueprint(): array
    {
        return match ($this) {

            ShowProspectTagTabsEnum::PROSPECTS => [
                'title' => __('prospects'),
                'icon'  => 'fal fa-transporter',
            ],

            ShowProspectTagTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
