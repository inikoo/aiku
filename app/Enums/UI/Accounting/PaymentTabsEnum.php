<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 20:08:38 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Accounting;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PaymentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DATA          = 'data';
    case HISTORY_NOTES = 'history_notes';

    public function blueprint(): array
    {
        return match ($this) {
            PaymentTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],

            PaymentTabsEnum::HISTORY_NOTES => [
                'title' => __('history, notes'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
